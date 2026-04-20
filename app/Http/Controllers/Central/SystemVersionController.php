<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\SystemVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemVersionController extends Controller
{
    public function index(): View
    {
        $versions = SystemVersion::query()->latest('released_at')->latest()->paginate(20);

        return view('central/system-versions/index', compact('versions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'version' => ['required', 'string', 'max:50', 'unique:system_versions,version'],
            'release_type' => ['required', 'in:feature,security,hotfix,maintenance'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'migration_batch' => ['nullable', 'string', 'max:100'],
        ]);

        SystemVersion::query()->create([
            ...$data,
            'released_at' => now(),
            'released_by' => $request->user()->id,
        ]);

        return redirect()->route('central.system-versions.index')->with('success', 'System version logged.');
    }
}
