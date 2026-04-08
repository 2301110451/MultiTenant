<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Reservation;
use App\Support\Tenancy;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user('tenant');
        $tenant = Tenancy::currentTenant()?->load(['plan', 'subscription.plan']);

        $pendingApprovals = Reservation::query()
            ->where('status', 'pending')
            ->count();

        $facilitiesCount = Facility::query()->where('is_active', true)->count();

        return view('tenant.dashboard', [
            'user' => $user,
            'tenant' => $tenant,
            'pendingApprovals' => $pendingApprovals,
            'facilitiesCount' => $facilitiesCount,
        ]);
    }
}
