<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\TenantAuditLogger;
use App\Support\Tenancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        private readonly TenantAuditLogger $tenantAuditLogger,
    ) {}

    public function create(Request $request): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $guard = Tenancy::isCentralHost($request->getHost()) ? 'web' : 'tenant';

        $request->authenticate($guard);

        $request->session()->regenerate();

        if ($guard === 'tenant') {
            $tenantUser = $request->user('tenant');
            if ($tenantUser instanceof User) {
                $this->tenantAuditLogger->log(
                    $request,
                    'auth.login',
                    User::class,
                    (int) $tenantUser->id,
                    [
                        'actor_role' => is_object($tenantUser->role) && isset($tenantUser->role->value)
                            ? (string) $tenantUser->role->value
                            : (string) $tenantUser->role,
                    ]
                );
            }
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $guard = Tenancy::isCentralHost($request->getHost()) ? 'web' : 'tenant';
        $tenantUser = $guard === 'tenant' ? $request->user('tenant') : null;

        if ($tenantUser instanceof User) {
            $this->tenantAuditLogger->log(
                $request,
                'auth.logout',
                User::class,
                (int) $tenantUser->id,
                [
                    'actor_role' => is_object($tenantUser->role) && isset($tenantUser->role->value)
                        ? (string) $tenantUser->role->value
                        : (string) $tenantUser->role,
                ]
            );
        }

        Auth::guard($guard)->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
