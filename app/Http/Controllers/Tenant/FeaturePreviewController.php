<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class FeaturePreviewController extends Controller
{
    public function index(): View
    {
        return view('tenant.feature-preview.index', [
            'featureName' => 'Quick Reservation Summary',
            'featureVersion' => 'v1.3.0',
            'status' => 'enabled',
        ]);
    }
}
