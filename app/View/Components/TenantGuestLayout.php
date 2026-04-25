<?php

namespace App\View\Components;

use App\Support\Tenancy;
use App\Support\TenantAppearance;
use Illuminate\View\Component;
use Illuminate\View\View;

class TenantGuestLayout extends Component
{
    public function render(): View
    {
        return view('layouts.tenant-guest', [
            'theme' => TenantAppearance::theme(),
            'tenant' => Tenancy::currentTenant(),
            'plan' => Tenancy::tenantPlan(),
        ]);
    }
}
