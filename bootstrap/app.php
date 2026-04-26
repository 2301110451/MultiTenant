<?php

use App\Http\Middleware\AuthenticateContext;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\EnsureTenantHost;
use App\Http\Middleware\EnsureTenantPermission;
use App\Http\Middleware\EnsureTenantPlanFeature;
use App\Http\Middleware\EnsureTenantRole;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Middleware\RedirectIfAuthenticatedContext;
use App\Http\Middleware\TriggerApprovedReleaseSyncOnWebRequest;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Laravel sorts route middleware by priority; Authenticate runs before SubstituteBindings.
        // Without this, auth:tenant can run before IdentifyTenant and hits tenant_placeholder.
        $middleware->prependToPriorityList(AuthenticatesRequests::class, IdentifyTenant::class);

        $middleware->alias([
            'auth.context' => AuthenticateContext::class,
            'guest.context' => RedirectIfAuthenticatedContext::class,
            'tenant.host' => EnsureTenantHost::class,
            'tenant.role' => EnsureTenantRole::class,
            'tenant.permission' => EnsureTenantPermission::class,
            'tenant.plan' => EnsureTenantPlanFeature::class,
            'super.admin' => EnsureSuperAdmin::class,
        ]);

        $middleware->appendToGroup('web', TriggerApprovedReleaseSyncOnWebRequest::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
