<?php

namespace App\Support;

use App\Models\Tenant;

/**
 * Batched queries for central admin dashboards (fewer round-trips than N separate count() calls).
 */
final class CentralDashboardMetrics
{
    /**
     * @return array{tenantCount: int, activeTenants: int, suspendedTenants: int, subscribedTenants: int}
     */
    public static function mainDashboardStats(): array
    {
        $base = self::aggregateStatusCounts();

        return array_merge($base, [
            'subscribedTenants' => self::subscribedTenantsCount(),
        ]);
    }

    /**
     * @return array{tenantCount: int, activeTenants: int, suspendedTenants: int}
     */
    public static function aggregateStatusCounts(): array
    {
        $row = Tenant::query()
            ->selectRaw('count(*) as total')
            ->selectRaw("coalesce(sum(case when status = 'active' then 1 else 0 end), 0) as active")
            ->selectRaw("coalesce(sum(case when status = 'suspended' then 1 else 0 end), 0) as suspended")
            ->first();

        return [
            'tenantCount' => (int) ($row->total ?? 0),
            'activeTenants' => (int) ($row->active ?? 0),
            'suspendedTenants' => (int) ($row->suspended ?? 0),
        ];
    }

    /**
     * Same logic as prior whereHas('subscription') on the latest subscription relation.
     */
    public static function subscribedTenantsCount(): int
    {
        return (int) Tenant::query()
            ->where('status', '!=', 'unsubscribed')
            ->where(function ($query) {
                $query->whereNotNull('plan_id')
                    ->orWhereHas('subscription', fn ($subscription) => $subscription->where('status', 'active'));
            })
            ->count();
    }

    /**
     * Realtime payload for central tenants index (includes unsubscribed breakdown).
     *
     * @return array{tenantCount: int, activeTenants: int, suspendedTenants: int, unsubscribedTenants: int}
     */
    public static function tenantsTabStats(): array
    {
        $row = Tenant::query()
            ->selectRaw('count(*) as total')
            ->selectRaw("coalesce(sum(case when status = 'active' then 1 else 0 end), 0) as active")
            ->selectRaw("coalesce(sum(case when status = 'suspended' then 1 else 0 end), 0) as suspended")
            ->selectRaw("coalesce(sum(case when status = 'unsubscribed' then 1 else 0 end), 0) as unsubscribed")
            ->first();

        return [
            'tenantCount' => (int) ($row->total ?? 0),
            'activeTenants' => (int) ($row->active ?? 0),
            'suspendedTenants' => (int) ($row->suspended ?? 0),
            'unsubscribedTenants' => (int) ($row->unsubscribed ?? 0),
        ];
    }
}
