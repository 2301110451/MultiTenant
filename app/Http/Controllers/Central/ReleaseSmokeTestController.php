<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ReleaseSmokeTestController extends Controller
{
    public function index(): View
    {
        return view('central.release-smoke-test.index', [
            'title' => 'Central Release Smoke Test',
            'status' => 'ready',
        ]);
    }
}
