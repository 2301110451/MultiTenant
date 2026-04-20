<?php

namespace App\Http\Middleware;

use App\Support\Pricing;
use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantPlanFeature
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (! Pricing::enforcementEnabled()) {
            return $next($request);
        }

        if (Tenancy::isCentralHost($request->getHost())) {
            abort(404);
        }

        $plan = Tenancy::tenantPlan();
        $allowed = $plan ? Pricing::allows($feature, $plan) : false;

        abort_unless(
            $allowed,
            403,
            'Your subscription plan does not include this feature.'
        );

        return $next($request);
    }
}
