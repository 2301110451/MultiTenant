<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Http\Response;

final class TenantSuspendedView
{
    /**
     * Full-page 503 for inactive tenant portals (subscription summary + messaging).
     */
    public static function response(Tenant $tenant, string $tenantStatus = 'suspended'): Response
    {
        $tenant->loadMissing(['subscription.plan', 'plan', 'domains']);

        app()->instance('currentTenant', $tenant);

        $subscriptionActionUrl = null;
        if ($tenantStatus !== 'unsubscribed') {
            $subscriptionActionUrl = CentralUrl::temporarySignedRoute(
                'central.subscription-intent.show',
                now()->addDays(30),
                ['tenant' => $tenant->id]
            );
        }

        return response()->view('tenant.suspended', [
            'domainHost' => strtolower((string) request()->getHost()),
            'tenantStatus' => $tenantStatus,
            'subscriptionActionUrl' => $subscriptionActionUrl,
        ], 503);
    }
}
