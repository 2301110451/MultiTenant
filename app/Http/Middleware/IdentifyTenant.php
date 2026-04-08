<?php

namespace App\Http\Middleware;

use App\Models\Domain;
use App\Models\Tenant;
use App\Support\TenantSuspendedView;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());

        if (\App\Support\Tenancy::isCentralHost($host)) {
            app()->instance('currentTenant', null);

            return $next($request);
        }

        $domain = Domain::query()->where('domain', $host)->first();

        if (! $domain) {
            abort(404, 'Unknown barangay domain.');
        }

        /** @var Tenant $tenant */
        $tenant = $domain->tenant()->with(['subscription.plan', 'plan'])->firstOrFail();

        if ($tenant->status !== 'active') {
            return TenantSuspendedView::response($tenant);
        }

        $tenant->configureTenantConnection();
        app()->instance('currentTenant', $tenant);

        return $next($request);
    }
}
