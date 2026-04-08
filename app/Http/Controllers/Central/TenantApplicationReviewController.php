<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Mail\TenantApplicationRejectedMail;
use App\Models\TenantApplication;
use App\Services\BarangayOfficerNotifier;
use App\Services\TenantProvisioningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class TenantApplicationReviewController extends Controller
{
    public function __construct(
        private TenantProvisioningService $provisioning,
        private BarangayOfficerNotifier $officerNotifier,
    ) {}

    public function index(): View
    {
        $applications = TenantApplication::query()
            ->with(['plan', 'reviewer', 'provisionedTenant'])
            ->latest()
            ->paginate(20);

        return view('central.tenant-applications.index', [
            'applications' => $applications,
            'pendingCount' => TenantApplication::query()->where('status', 'pending')->count(),
        ]);
    }

    public function approve(Request $request, TenantApplication $application): RedirectResponse
    {
        if ($application->status !== 'pending') {
            return back()->with('warning', 'This application was already reviewed.');
        }

        try {
            $secretaryPassword = Crypt::decryptString($application->secretary_password_encrypted);
            $captainPassword = Crypt::decryptString($application->captain_password_encrypted);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('warning', 'Could not decrypt stored officer passwords for this application.');
        }

        $tenant = null;
        try {
            $domain = $this->provisioning->generateUniqueTenantDomain($application->barangay_name);
            $tenant = $this->provisioning->provisionTenant(
                $application->barangay_name,
                $domain,
                $application->plan_id,
                $application->secretary_email,
                $secretaryPassword,
                $application->captain_email,
                $captainPassword,
            );
        } catch (\Throwable $e) {
            report($e);

            return back()->with('warning', 'Approval failed while provisioning tenant: '.$e->getMessage());
        }

        DB::connection('mysql')->transaction(function () use ($request, $application, $tenant): void {
            $application->update([
                'status' => 'approved',
                'reviewed_at' => now(),
                'reviewed_by' => $request->user()->id,
                'provisioned_tenant_id' => $tenant->id,
                'secretary_password_encrypted' => '',
                'captain_password_encrypted' => '',
            ]);
        });

        $mailWarning = null;
        $domain = strtolower((string) $tenant->domains()->first()?->domain);
        if ($domain !== '') {
            try {
                $this->officerNotifier->notifyApproval(
                    $application->barangay_name,
                    $domain,
                    [$application->secretary_email, $application->captain_email],
                );
            } catch (\Throwable $e) {
                report($e);
                $mailWarning = 'Tenant was approved, but approval email could not be sent: '.$e->getMessage();
            }
        }

        $redirect = redirect()
            ->route('central.tenant-applications.index');

        if ($mailWarning) {
            return $redirect
                ->with('success', 'Application approved and tenant provisioned.')
                ->with('warning', $mailWarning);
        }

        $mailNotice = BarangayOfficerNotifier::mailNotDeliveredToInboxNotice();
        if ($mailNotice) {
            return $redirect
                ->with('success', 'Application approved and tenant provisioned. Approval email was generated.')
                ->with('warning', $mailNotice);
        }

        return $redirect->with('success', 'Application approved and tenant provisioned. Approval email was sent to Secretary and Captain addresses.');
    }

    public function reject(Request $request, TenantApplication $application): RedirectResponse
    {
        if ($application->status !== 'pending') {
            return back()->with('warning', 'This application was already reviewed.');
        }

        $data = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $application->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
            'rejection_reason' => $data['rejection_reason'] ?? null,
            'secretary_password_encrypted' => '',
            'captain_password_encrypted' => '',
        ]);

        $portalHint = strtolower($application->barangay_name);
        $portalHint = preg_replace('/[^a-z0-9]+/i', '-', $portalHint ?? '') ?: 'barangay';
        $portalHint = trim($portalHint, '-');
        $portalHint = $portalHint.'.'.config('tenancy.tenant_domain_suffix', 'localhost');

        $recipients = array_values(array_unique([
            strtolower($application->secretary_email),
            strtolower($application->captain_email),
        ]));

        $mailWarning = null;
        try {
            foreach ($recipients as $email) {
                Mail::to($email)->send(new TenantApplicationRejectedMail(
                    $application->barangay_name,
                    $portalHint,
                    $application->rejection_reason,
                ));
            }
        } catch (\Throwable $e) {
            report($e);
            $mailWarning = 'Application was rejected, but rejection email could not be sent: '.$e->getMessage();
        }

        $redirect = redirect()
            ->route('central.tenant-applications.index');

        if ($mailWarning) {
            return $redirect
                ->with('success', 'Application rejected successfully.')
                ->with('warning', $mailWarning);
        }

        $mailNotice = BarangayOfficerNotifier::mailNotDeliveredToInboxNotice();
        if ($mailNotice) {
            return $redirect
                ->with('success', 'Application rejected successfully. Rejection email was generated.')
                ->with('warning', $mailNotice);
        }

        return $redirect->with('success', 'Application rejected. Rejection email was sent to Secretary and Captain addresses.');
    }
}
