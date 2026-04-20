<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\TenantApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
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
            'tenant_admin_email' => ['required', 'email', 'max:255', 'different:staff_email'],
            'tenant_admin_password' => ['required', 'string', 'confirmed', Password::defaults()],
            'staff_email' => ['nullable', 'email', 'max:255', 'different:tenant_admin_email'],
            'staff_password' => ['nullable', 'required_with:staff_email', 'string', 'confirmed', Password::defaults()],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        TenantApplication::query()->create([
            'barangay_name' => $data['barangay_name'],
            'plan_id' => $data['plan_id'] ?? null,
            'tenant_admin_email' => strtolower($data['tenant_admin_email']),
            'tenant_admin_password_encrypted' => Crypt::encryptString($data['tenant_admin_password']),
            'staff_email' => ! empty($data['staff_email']) ? strtolower($data['staff_email']) : null,
            'staff_password_encrypted' => ! empty($data['staff_password']) ? Crypt::encryptString($data['staff_password']) : null,
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('central.apply')
            ->with('success', 'Application submitted. The super admin will review and email Tenant Admin/Staff addresses once approved or rejected.');
    }
}
