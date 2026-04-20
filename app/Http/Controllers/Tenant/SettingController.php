<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantSetting;
use App\Services\TenantAuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(
        private TenantAuditLogger $audit,
    ) {}

    public function edit(Request $request): View
    {
        Gate::forUser($request->user('tenant'))->authorize('viewAny', TenantSetting::class);

        $settings = TenantSetting::query()->first();
        $user = $request->user('tenant');
        $canUpdateSettings = Gate::forUser($user)->allows('update', TenantSetting::query()->firstOrNew());

        return view('tenant.settings.edit', compact('settings', 'canUpdateSettings'));
    }

    public function update(Request $request): RedirectResponse
    {
        Gate::forUser($request->user('tenant'))->authorize('update', TenantSetting::query()->firstOrNew());

        $data = $request->validate([
            'branding_name' => ['nullable', 'string', 'max:255'],
            'accent_source' => ['required', 'in:plan,custom'],
            'accent_color' => [
                'exclude_if:accent_source,plan',
                'required',
                'string',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
            'bg_source' => ['required', 'in:plan,custom'],
            'background_color' => [
                'exclude_if:bg_source,plan',
                'required',
                'string',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
            'sidebar_source' => ['required', 'in:plan,custom'],
            'sidebar_background_color' => [
                'exclude_if:sidebar_source,plan',
                'required',
                'string',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
            'compact_layout' => ['nullable', 'boolean'],
            'module_reports' => ['nullable', 'boolean'],
            'module_facilities' => ['nullable', 'boolean'],
            'module_reservations' => ['nullable', 'boolean'],
        ]);

        $usePlanAccent = $data['accent_source'] === 'plan';
        $usePlanBg = $data['bg_source'] === 'plan';
        $usePlanSidebar = $data['sidebar_source'] === 'plan';

        $settings = TenantSetting::query()->firstOrNew();
        $settings->fill([
            'branding_name' => $data['branding_name'] ?? null,
            'accent_color' => $usePlanAccent ? null : ($data['accent_color'] ?? null),
            'background_color' => $usePlanBg ? null : ($data['background_color'] ?? null),
            'sidebar_background_color' => $usePlanSidebar ? null : ($data['sidebar_background_color'] ?? null),
            'compact_layout' => (bool) ($data['compact_layout'] ?? false),
            'module_toggles' => [
                'reports' => (bool) ($data['module_reports'] ?? true),
                'facilities' => (bool) ($data['module_facilities'] ?? true),
                'reservations' => (bool) ($data['module_reservations'] ?? true),
            ],
        ]);
        $settings->save();

        $this->audit->log($request, 'tenant_settings.updated', TenantSetting::class, (int) $settings->id, [
            'branding_name' => $settings->branding_name,
            'accent_color' => $settings->accent_color,
            'background_color' => $settings->background_color,
            'sidebar_background_color' => $settings->sidebar_background_color,
            'compact_layout' => $settings->compact_layout,
        ]);

        return redirect()->route('tenant.settings.edit')->with('status', 'Settings updated.');
    }
}
