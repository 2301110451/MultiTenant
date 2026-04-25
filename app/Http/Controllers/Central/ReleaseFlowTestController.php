<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ReleaseFlowTestController extends Controller
{
    public function index(): View
    {
        return view('central.release-flow-test.index', [
            'featureName' => 'Central Release Flow Test',
            'featureStatus' => 'enabled',
        ]);
    }
}
