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
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function __construct(
        private TenantProvisioningService $provisioning,
        private BarangayOfficerNotifier $officerNotifier,
    ) {}

    public function index(Request $request): View
    {
        $tenants = Tenant::query()->with(['domains', 'plan', 'subscription.plan'])->latest()->paginate(15);
        $plans = Plan::query()->orderBy('name')->get();

        $editTenantId = (int) ($request->query('edit') ?? old('edit_tenant_id', 0));
        $editTenantPayload = null;
        if ($editTenantId > 0) {
            $t = Tenant::query()->with(['domains', 'plan'])->find($editTenantId);
            if ($t) {
                $planOld = old('plan_id', $t->plan_id);
                $planForForm = ($planOld === '' || $planOld === null) ? '' : (int) $planOld;

                $editTenantPayload = [
                    'id' => $t->id,
                    'name' => (string) old('name', $t->name),
                    'domain' => (string) old('domain', $t->domains->first()?->domain ?? ''),
                    'plan_id' => $planForForm,
                    'status' => (string) old('status', $t->status ?? 'active'),
                ];
            }
        }

        return view('central.tenants.index', compact('tenants', 'plans', 'editTenantPayload'));
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
            'tenant_admin_email' => ['required', 'email', 'max:255', 'different:staff_email'],
            'tenant_admin_password' => ['required', 'string', 'confirmed', Password::defaults()],
            'staff_email' => ['nullable', 'email', 'max:255', 'different:tenant_admin_email'],
            'staff_password' => ['nullable', 'required_with:staff_email', 'string', 'confirmed', Password::defaults()],
        ]);

        $domain = $this->provisioning->generateUniqueTenantDomain($data['name']);

        try {
            $this->provisioning->provisionTenant(
                $data['name'],
                $domain,
                $data['plan_id'] ?? null,
                $data['tenant_admin_email'],
                $data['tenant_admin_password'],
                $data['staff_email'] ?? null,
                $data['staff_password'] ?? null,
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
                array_values(array_filter([$data['tenant_admin_email'], $data['staff_email'] ?? null]))
            );
            $mailNotice = BarangayOfficerNotifier::mailNotDeliveredToInboxNotice();
        } catch (\Throwable $e) {
            report($e);
            $mailNotice = 'The barangay was created, but notification emails could not be sent: '.$e->getMessage();
        }

        $redirect = redirect()->route('central.tenants.index')
            ->with('success', 'Barangay tenant provisioned with Tenant Admin account. Domain `'.$domain.'` is ready.')
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

        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'status' => ['required', 'in:active,suspended,unsubscribed'],
                'plan_id' => ['nullable', 'exists:plans,id'],
                'domain' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('domains', 'domain')->ignore($domain?->id),
                ],
            ]);
        } catch (ValidationException $e) {
            $query = ['edit' => $tenant->id];
            $page = $request->input('page') ?? $request->query('page');
            if ($page !== null && $page !== '') {
                $query['page'] = $page;
            }

            if ($request->input('redirect_to') === 'dashboard') {
                return redirect()->route('dashboard', $query)
                    ->withErrors($e->validator)
                    ->withInput();
            }

            return redirect()->route('central.tenants.index', $query)
                ->withErrors($e->validator)
                ->withInput();
        }

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

        if ($request->input('redirect_to') === 'dashboard') {
            $redirect = redirect()->route('dashboard')->with('status', 'tenant-updated');
        } else {
            $redirect = redirect()->route('central.tenants.index')->with('status', 'tenant-updated');
        }

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
