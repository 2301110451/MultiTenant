<?php

namespace App\Http\Middleware;

use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Tenancy::isCentralHost($request->getHost()) ? 'web' : 'tenant';

        if (Auth::guard($guard)->guest()) {
            return redirect()->guest(route('login', absolute: false));
        }

        return $next($request);
    }
}
