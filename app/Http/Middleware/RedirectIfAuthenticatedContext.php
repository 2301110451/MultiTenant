<?php

namespace App\Http\Middleware;

use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticatedContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Tenancy::isCentralHost($request->getHost()) ? 'web' : 'tenant';

        if (Auth::guard($guard)->check()) {
            // redirect()->route() does not support $absolute; build URL via route() then redirect.
            return redirect()->to(route('dashboard', [], false));
        }

        return $next($request);
    }
}
