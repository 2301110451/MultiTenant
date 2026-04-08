<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $tenantCount      = Tenant::query()->count();
        $activeTenants    = Tenant::query()->where('status', 'active')->count();
        $suspendedTenants = Tenant::query()->where('status', 'suspended')->count();
        $totalPlans       = Plan::query()->count();

        $recentTenants = Tenant::query()
            ->with(['subscription.plan'])
            ->latest()
            ->take(8)
            ->get();

        return view('central.dashboard', [
            'tenantCount'      => $tenantCount,
            'activeTenants'    => $activeTenants,
            'suspendedTenants' => $suspendedTenants,
            'totalPlans'       => $totalPlans,
            'recentTenants'    => $recentTenants,
        ]);
    }
}
