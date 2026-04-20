<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Role;
use App\Models\SupportTicket;
use App\Models\TenantAnnouncement;
use App\Models\UpdateAnnouncement;
use App\Models\User;
use App\Support\Tenancy;
use App\Support\TenantAppearance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RealtimeController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user('tenant');

        $pendingQuery = Reservation::query()->where('status', 'pending');
        if ($user?->isResident()) {
            $pendingQuery->where('user_id', $user->id);
        }

        return response()->json([
            'pendingApprovals' => $pendingQuery->count(),
            'facilitiesCount' => Facility::query()->where('is_active', true)->count(),
            'version' => now()->timestamp,
        ]);
    }

    public function reports(Request $request): JsonResponse
    {
        $user = $request->user('tenant');
        abort_unless($user, 403);
        Gate::forUser($user)->authorize('tenant.reports.view');
        abort_unless(
            TenantAppearance::planAllowsReports(),
            403,
            'Reports and analytics are not included in your barangay subscription plan.'
        );

        $paidRevenue = (float) Payment::query()->where('status', 'paid')->sum('amount');
        $completedReservationRevenue = Reservation::query()
            ->with('facility:id,hourly_rate')
            ->where('status', 'completed')
            ->get()
            ->sum(function (Reservation $reservation): float {
                if ($reservation->revenue_amount !== null) {
                    return (float) $reservation->revenue_amount;
                }

                $minutes = max(0, (int) $reservation->starts_at?->diffInMinutes($reservation->ends_at ?? $reservation->starts_at));
                if ($minutes === 0) {
                    return 0.0;
                }

                $hours = $minutes / 60;
                $rate = (float) ($reservation->facility?->hourly_rate ?? 0);

                return round($hours * $rate, 2);
            });
        $revenue = $paidRevenue + (float) $completedReservationRevenue;
        $damageChargeCount = Payment::query()->where('method', 'damage')->count();
        $pendingDamagePayments = Payment::query()
            ->where('method', 'damage')
            ->where('status', 'pending')
            ->count();

        return response()->json([
            'revenue' => number_format((float) $revenue, 2, '.', ''),
            'damageCount' => $damageChargeCount,
            'damageChargeCount' => $damageChargeCount,
            'pendingDamagePayments' => $pendingDamagePayments,
            'version' => now()->timestamp,
        ]);
    }

    public function reservations(Request $request): JsonResponse
    {
        $user = $request->user('tenant');

        $base = Reservation::query();
        if ($user?->isResident()) {
            $base->where('user_id', $user->id);
        }

        $counts = $base
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json([
            'pending' => (int) ($counts['pending'] ?? 0),
            'approved' => (int) ($counts['approved'] ?? 0),
            'completed' => (int) ($counts['completed'] ?? 0),
            'rejected' => (int) ($counts['rejected'] ?? 0),
            'version' => now()->timestamp,
        ]);
    }

    public function users(): JsonResponse
    {
        return response()->json([
            'totalUsers' => User::query()->count(),
            'activeUsers' => User::query()->where('is_active', true)->count(),
            'inactiveUsers' => User::query()->where('is_active', false)->count(),
            'version' => now()->timestamp,
        ]);
    }

    public function roles(): JsonResponse
    {
        return response()->json([
            'totalRoles' => Role::query()->count(),
            'customRoles' => Role::query()
                ->whereNotIn('name', ['tenant_admin', 'staff', 'viewer', 'resident'])
                ->count(),
            'version' => now()->timestamp,
        ]);
    }

    public function support(): JsonResponse
    {
        $tenantId = (int) (Tenancy::currentTenant()?->id ?? 0);

        return response()->json([
            'openCount' => SupportTicket::query()->where('tenant_id', $tenantId)->where('status', 'open')->count(),
            'inProgressCount' => SupportTicket::query()->where('tenant_id', $tenantId)->where('status', 'in_progress')->count(),
            'resolvedCount' => SupportTicket::query()->where('tenant_id', $tenantId)->whereIn('status', ['resolved', 'closed'])->count(),
            'version' => now()->timestamp,
        ]);
    }

    public function updates(Request $request): JsonResponse
    {
        $tenant = Tenancy::currentTenant();

        $systemQuery = UpdateAnnouncement::query()
            ->where('is_active', true)
            ->where('audience', 'all')
            ->where(function ($builder) use ($tenant) {
                $tenantId = (int) ($tenant?->id ?? 0);
                $builder->whereNull('targeted_tenant_ids');
                if ($tenantId > 0) {
                    $builder->orWhereJsonContains('targeted_tenant_ids', $tenantId);
                }
            });

        $tenantQuery = TenantAnnouncement::query()->where('is_active', true);
        $systemLatest = (clone $systemQuery)->latest('published_at')->value('published_at');
        $tenantLatest = (clone $tenantQuery)->latest('published_at')->value('published_at');
        $latest = collect([$systemLatest, $tenantLatest])->filter()->sortDesc()->first();

        return response()->json([
            'updateCount' => (clone $systemQuery)->count() + (clone $tenantQuery)->count(),
            'latestPublishedAt' => $latest ? Carbon::parse((string) $latest)->format('M d, Y H:i') : 'N/A',
            'version' => now()->timestamp,
        ]);
    }
}
