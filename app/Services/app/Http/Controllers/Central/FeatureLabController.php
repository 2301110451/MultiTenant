<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class FeatureLabController extends Controller
{
    public function index(): View
    {
        return view('central.feature-lab.index', [
            'featureName' => 'Admin Feature Lab',
            'status' => 'enabled',
        ]);
    }
}
