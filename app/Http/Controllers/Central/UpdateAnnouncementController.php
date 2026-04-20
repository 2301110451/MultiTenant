<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\UpdateAnnouncement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UpdateAnnouncementController extends Controller
{
    public function index(): View
    {
        $updates = UpdateAnnouncement::query()->latest('published_at')->latest()->paginate(20);
        $tenants = Tenant::query()->orderBy('name')->get(['id', 'name', 'status']);

        return view('central/update-announcements/index', compact('updates', 'tenants'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'target_tenant_id' => ['nullable', 'integer', 'exists:tenants,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $targetTenantId = (int) ($data['target_tenant_id'] ?? 0);
        $targetTenantIds = $targetTenantId > 0 ? [$targetTenantId] : null;
        unset($data['target_tenant_id']);

        UpdateAnnouncement::query()->create([
            ...$data,
            'audience' => 'all',
            'targeted_tenant_ids' => $targetTenantIds,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'published_at' => now(),
            'published_by' => $request->user()->id,
        ]);

        return redirect()->route('central.update-announcements.index')->with('success', 'Update announcement published.');
    }
}
