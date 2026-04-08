<?php

namespace App\Http\Middleware;

use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCentralHost
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Tenancy::isCentralHost($request->getHost())) {
            abort(404);
        }

        return $next($request);
    }
}
