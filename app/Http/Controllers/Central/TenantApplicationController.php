<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\TenantApplication;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class TenantApplicationController extends Controller
{
    public function create(Request $request): View
    {
        return view('central.apply', [
            'plans' => Plan::query()->orderBy('name')->get(),
            'domainSuffix' => config('tenancy.tenant_domain_suffix'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'barangay_name' => ['required', 'string', 'max:255'],
            'plan_id' => ['nullable', 'exists:plans,id'],
            'secretary_email' => ['required', 'email', 'max:255', 'different:captain_email'],
            'secretary_password' => ['required', 'string', 'confirmed', Password::defaults()],
            'captain_email' => ['required', 'email', 'max:255', 'different:secretary_email'],
            'captain_password' => ['required', 'string', 'confirmed', Password::defaults()],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        TenantApplication::query()->create([
            'barangay_name' => $data['barangay_name'],
            'plan_id' => $data['plan_id'] ?? null,
            'secretary_email' => strtolower($data['secretary_email']),
            'secretary_password_encrypted' => Crypt::encryptString($data['secretary_password']),
            'captain_email' => strtolower($data['captain_email']),
            'captain_password_encrypted' => Crypt::encryptString($data['captain_password']),
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('central.apply')
            ->with('success', 'Application submitted. The super admin will review and email the Secretary and Captain addresses once approved or rejected.');
    }
}
