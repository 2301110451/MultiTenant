<?php

namespace App\Http\Controllers;

use App\Support\Tenancy;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        if (Tenancy::isCentralHost($request->getHost())) {
            return view('central.welcome');
        }

        return view('tenant.welcome');
    }
}
