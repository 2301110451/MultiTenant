<?php

namespace App\Providers;

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\Role;
use App\Models\SupportTicket;
use App\Models\TenantSetting;
use App\Models\User;
use App\Policies\FacilityPolicy;
use App\Policies\ReservationPolicy;
use App\Policies\RolePolicy;
use App\Policies\SupportTicketPolicy;
use App\Policies\TenantSettingPolicy;
use App\Policies\TenantUserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Facility::class, FacilityPolicy::class);
        Gate::policy(Reservation::class, ReservationPolicy::class);
        Gate::policy(User::class, TenantUserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(TenantSetting::class, TenantSettingPolicy::class);
        Gate::policy(SupportTicket::class, SupportTicketPolicy::class);

        Gate::define('tenant.reports.view', fn (User $user): bool => $user->hasPermission('reports.view'));
        Gate::define('tenant.updates.view', fn (User $user): bool => $user->is_active && $user->hasPermission('updates.view'));
    }
}
