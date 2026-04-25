<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantAnnouncement;
use App\Models\UpdateAnnouncement;
use App\Support\Tenancy;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UpdateFeedController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = Tenancy::currentTenant();
        $tenantUser = $request->user('tenant');

        $systemUpdates = UpdateAnnouncement::query()
            ->where('is_active', true)
            ->whereIn('audience', ['all', 'selected'])
            ->where(function ($query) use ($tenant) {
                $tenantId = (int) ($tenant?->id ?? 0);
                $query->whereNull('targeted_tenant_ids');
                if ($tenantId > 0) {
                    $query->orWhereJsonContains('targeted_tenant_ids', $tenantId);
                }
            })
            ->latest('published_at')
            ->take(20)
            ->get();

        $tenantAnnouncements = TenantAnnouncement::query()
            ->where('is_active', true)
            ->latest('published_at')
            ->take(20)
            ->get();

        $updateCount = $systemUpdates->count() + $tenantAnnouncements->count();

        $latestPublishedAt = $systemUpdates
            ->pluck('published_at')
            ->merge($tenantAnnouncements->pluck('published_at'))
            ->filter()
            ->sortDesc()
            ->first();

        return view('tenant.updates.index', [
            'systemUpdates' => $systemUpdates,
            'tenantAnnouncements' => $tenantAnnouncements,
            'updateCount' => $updateCount,
            'latestPublishedAt' => $latestPublishedAt,
            'canManageAnnouncements' => (bool) ($tenantUser?->hasPermission('updates.manage')),
        ]);
    }
}
