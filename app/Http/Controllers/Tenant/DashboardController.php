<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\TenantAnnouncement;
use App\Support\Tenancy;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user('tenant');
        $tenant = Tenancy::currentTenant()?->load(['plan', 'subscription.plan']);

        $pendingQuery = Reservation::query()->where('status', 'pending');
        if ($user?->isResident()) {
            $pendingQuery->where('user_id', $user->id);
        }
        $pendingApprovals = $pendingQuery->count();

        $facilitiesCount = Facility::query()->where('is_active', true)->count();
        $recentTenantAnnouncements = TenantAnnouncement::query()
            ->where('is_active', true)
            ->latest('published_at')
            ->take(5)
            ->get();

        return view('tenant.dashboard', [
            'user' => $user,
            'tenant' => $tenant,
            'pendingApprovals' => $pendingApprovals,
            'facilitiesCount' => $facilitiesCount,
            'recentTenantAnnouncements' => $recentTenantAnnouncements,
        ]);
    }
}
