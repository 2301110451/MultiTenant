<?php

namespace App\View\Components;

use App\Models\User;
use App\Support\Tenancy;
use App\Support\TenantAppearance;
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
        /** @var User|null $tenantUser */
        $tenantUser = auth('tenant')->user();

        return view('layouts.tenant', [
            'theme' => TenantAppearance::theme($tenantUser),
            'tenant' => Tenancy::currentTenant(),
            'plan' => Tenancy::tenantPlan(),
            'planAllowsReports' => TenantAppearance::planAllowsReports(),
        ]);
    }
}
