<?php

namespace App\Http\Middleware;

use App\Enums\TenantRole;
use App\Models\User;
use App\Services\TenantGoogleOAuthRedirectService;
use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantRole
{
    public function __construct(
        private TenantGoogleOAuthRedirectService $redirects,
    ) {}

    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (Tenancy::isCentralHost($request->getHost())) {
            abort(404);
        }

        /** @var User|null $user */
        $user = Auth::guard('tenant')->user();
        if ($user === null) {
            return redirect()->guest(route('login', absolute: false));
        }

        $expected = TenantRole::tryFrom(strtolower(trim($role)));
        if ($expected === null) {
            $expected = TenantRole::resolveFromStored($role);
        }

        if ($user->role !== $expected) {
            return redirect()->to($this->redirects->pathAfterLogin($user));
        }

        return $next($request);
    }
}
