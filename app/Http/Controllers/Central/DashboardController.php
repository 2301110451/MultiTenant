<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use App\Support\CentralDashboardMetrics;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $stats = CentralDashboardMetrics::mainDashboardStats();
        $tenantCount = $stats['tenantCount'];
        $activeTenants = $stats['activeTenants'];
        $suspendedTenants = $stats['suspendedTenants'];
        $subscribedTenants = $stats['subscribedTenants'];
        $recentTenants = Tenant::query()
            ->with(['subscription.plan', 'domains'])
            ->latest()
            ->take(8)
            ->get();

        $plans = Plan::query()->orderBy('name')->get();

        $editTenantId = (int) ($request->query('edit') ?? old('edit_tenant_id', 0));
        $editTenantPayload = null;
        if ($editTenantId > 0) {
            $t = Tenant::query()->with(['domains', 'plan'])->find($editTenantId);
            if ($t) {
                $planOld = old('plan_id', $t->plan_id);
                $planForForm = ($planOld === '' || $planOld === null) ? '' : (int) $planOld;

                $editTenantPayload = [
                    'id' => $t->id,
                    'name' => (string) old('name', $t->name),
                    'domain' => (string) old('domain', $t->domains->first()?->domain ?? ''),
                    'plan_id' => $planForForm,
                    'status' => (string) old('status', $t->status ?? 'active'),
                ];
            }
        }

        return view('central.dashboard', [
            'tenantCount' => $tenantCount,
            'activeTenants' => $activeTenants,
            'suspendedTenants' => $suspendedTenants,
            'subscribedTenants' => $subscribedTenants,
            'recentTenants' => $recentTenants,
            'plans' => $plans,
            'editTenantPayload' => $editTenantPayload,
        ]);
    }
}
