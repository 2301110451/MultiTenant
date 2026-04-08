<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Central\DashboardController as CentralDashboardController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;
use App\Support\Tenancy;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        if (Tenancy::isCentralHost($request->getHost())) {
            return app(CentralDashboardController::class)->index($request);
        }

        return app(TenantDashboardController::class)->index($request);
    }
}
