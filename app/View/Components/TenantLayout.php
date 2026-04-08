<?php

namespace App\View\Components;

use App\Support\TenantAppearance;
use App\Support\Tenancy;
use Illuminate\View\Component;
use Illuminate\View\View;

class TenantLayout extends Component
{
    public function __construct(
        public string $title = '',
        public string $breadcrumb = '',
    ) {}

    public function render(): View
    {
        return view('layouts.tenant', [
            'theme' => TenantAppearance::theme(),
            'tenant' => Tenancy::currentTenant(),
            'plan' => Tenancy::tenantPlan(),
            'planAllowsReports' => TenantAppearance::planAllowsReports(),
        ]);
    }
}
