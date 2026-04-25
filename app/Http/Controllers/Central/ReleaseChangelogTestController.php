<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ReleaseChangelogTestController extends Controller
{
    public function index(): View
    {
        return view('central.release-changelog-test.index', [
            'title' => 'Release Changelog Test',
            'items' => [
                'Added changelog preview card',
                'Added version label for admins',
                'Added test-only route for release tracking',
            ],
            'version' => 'v-test-1',
        ]);
    }
}
