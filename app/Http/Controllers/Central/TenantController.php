<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\BarangayOfficerNotifier;
use App\Services\TenantProvisioningService;
use App\Support\Tenancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function __construct(
        private TenantProvisioningService $provisioning,
        private BarangayOfficerNotifier $officerNotifier,
    ) {}

    public function index(): View
    {
        $tenants = Tenant::query()->with(['domains', 'plan', 'subscription.plan'])->latest()->paginate(15);

        return view('central.tenants.index', compact('tenants'));
    }

    public function create(): View
    {
        return view('central.tenants.create', [
            'plans' => Plan::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'plan_id' => ['nullable', 'exists:plans,id'],
            'secretary_email' => ['required', 'email', 'max:255', 'different:captain_email'],
            'secretary_password' => ['required', 'string', 'confirmed', Password::defaults()],
            'captain_email' => ['required', 'email', 'max:255', 'different:secretary_email'],
            'captain_password' => ['required', 'string', 'confirmed', Password::defaults()],
        ]);

        $domain = $this->provisioning->generateUniqueTenantDomain($data['name']);

        try {
            $this->provisioning->provisionTenant(
                $data['name'],
                $domain,
                $data['plan_id'] ?? null,
                $data['secretary_email'],
                $data['secretary_password'],
                $data['captain_email'],
                $data['captain_password'],
            );
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->withErrors(['provisioning' => 'Provisioning failed: '.$e->getMessage()]);
        }

        $portalUrl = Tenancy::tenantPortalUrl($domain);

        $mailNotice = null;
        try {
            $this->officerNotifier->notifyApproval(
                $data['name'],
                $domain,
                [$data['secretary_email'], $data['captain_email']]
            );
            $mailNotice = BarangayOfficerNotifier::mailNotDeliveredToInboxNotice();
        } catch (\Throwable $e) {
            report($e);
            $mailNotice = 'The barangay was created, but notification emails could not be sent: '.$e->getMessage();
        }

        $redirect = redirect()->route('central.tenants.index')
            ->with('success', 'Barangay tenant provisioned with Secretary and Punong Barangay accounts. Domain `'.$domain.'` is ready.')
            ->with('portal_url', $portalUrl);

        if ($mailNotice !== null) {
            $redirect->with('mail_config_notice', $mailNotice);
        }

        return $redirect;
    }

    public function edit(Tenant $tenant): View
    {
        return view('central.tenants.edit', [
            'tenant' => $tenant->load('domains', 'plan'),
            'plans' => Plan::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $previousStatus = $tenant->status;
        $domain = $tenant->domains()->first();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,suspended'],
            'plan_id' => ['nullable', 'exists:plans,id'],
            'domain' => [
                'required',
                'string',
                'max:255',
                Rule::unique('domains', 'domain')->ignore($domain?->id),
            ],
        ]);

        $tenant->update([
            'name' => $data['name'],
            'status' => $data['status'],
            'plan_id' => $data['plan_id'] ?? null,
        ]);

        if ($domain) {
            $domain->update(['domain' => strtolower($data['domain'])]);
        } else {
            Domain::query()->create([
                'tenant_id' => $tenant->id,
                'domain' => strtolower($data['domain']),
            ]);
        }

        $domainForMail = strtolower($data['domain']);
        $mailNotice = null;

        if ($previousStatus === 'active' && $data['status'] === 'suspended') {
            try {
                $this->officerNotifier->notifyPortalSuspended($tenant->fresh(), $domainForMail);
                $mailNotice = BarangayOfficerNotifier::mailNotDeliveredToInboxNotice();
            } catch (\Throwable $e) {
                report($e);
                $mailNotice = 'The barangay was suspended, but notification emails could not be sent: '.$e->getMessage();
            }
        }

        if ($previousStatus === 'suspended' && $data['status'] === 'active') {
            try {
                $this->officerNotifier->notifyOfficersFromTenantDatabase($tenant->fresh(), $domainForMail);
                $mailNotice = BarangayOfficerNotifier::mailNotDeliveredToInboxNotice();
            } catch (\Throwable $e) {
                report($e);
                $mailNotice = 'The barangay was updated, but notification emails could not be sent: '.$e->getMessage();
            }
        }

        $redirect = redirect()->route('central.tenants.index')->with('status', 'tenant-updated');

        if ($mailNotice !== null) {
            $redirect->with('mail_config_notice', $mailNotice);
        }

        return $redirect;
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $database = $tenant->database;

        $tenant->delete();

        try {
            $this->provisioning->dropDatabase($database);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('central.tenants.index')
                ->with('warning', 'Tenant was removed from the registry, but the MySQL database `'.$database.'` could not be dropped automatically. Remove it manually in MySQL if it still exists.');
        }

        return redirect()->route('central.tenants.index')
            ->with('success', 'Barangay tenant and its database have been deleted.');
    }
}
