<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantSubscriptionIntent;
use App\Services\BarangayOfficerNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SubscriptionIntentReviewController extends Controller
{
    public function __construct(
        private BarangayOfficerNotifier $officerNotifier,
    ) {}

    public function index(): View
    {
        $intents = TenantSubscriptionIntent::query()
            ->with(['tenant.domains', 'tenant.subscription.plan', 'tenant.plan', 'reviewer'])
            ->latest()
            ->paginate(20);

        $pendingCount = TenantSubscriptionIntent::query()->where('status', 'pending')->count();

        return view('central.subscription-intents.index', [
            'intents' => $intents,
            'pendingCount' => $pendingCount,
        ]);
    }

    /**
     * Approve: extension → reactivate if suspended, then email officers; unsubscribe → mark tenant fully unsubscribed.
     */
    public function approve(Request $request, TenantSubscriptionIntent $intent): RedirectResponse
    {
        if (! $intent->isPending()) {
            return redirect()
                ->route('central.subscription-intents.index')
                ->with('warning', 'This request was already processed.');
        }

        $wasExtend = $intent->intent_type === 'extend';
        $mailNotice = null;

        DB::connection('mysql')->transaction(function () use ($request, $intent, $wasExtend) {
            $tenant = Tenant::query()->whereKey($intent->tenant_id)->lockForUpdate()->firstOrFail();

            $intent->update([
                'status' => 'approved',
                'reviewed_at' => now(),
                'reviewed_by' => $request->user()->id,
            ]);

            if (! $wasExtend) {
                $tenant->update(['status' => 'unsubscribed']);

                return;
            }

            if ($tenant->status === 'suspended') {
                $tenant->update(['status' => 'active']);
            }
        });

        if ($wasExtend) {
            $intent->refresh();
            $intent->load('tenant.domains');
            $tenant = $intent->tenant;
            $domain = $tenant->domains()->first()?->domain;
            if ($domain) {
                try {
                    $this->officerNotifier->notifySubscriptionExtensionApproved($tenant->fresh(), strtolower($domain));
                    $mailNotice = BarangayOfficerNotifier::mailNotDeliveredToInboxNotice();
                } catch (\Throwable $e) {
                    report($e);
                    $mailNotice = 'Request was saved, but tenant email could not be sent: '.$e->getMessage();
                }
            }
        }

        $redirect = redirect()
            ->route('central.subscription-intents.index')
            ->with(
                'success',
                $wasExtend
                    ? 'Extension approved. Officers were emailed when SMTP is configured.'
                    : 'Unsubscribe request approved. Tenant access is now fully disabled for this barangay.'
            );

        if ($mailNotice !== null) {
            $redirect->with('mail_config_notice', $mailNotice);
        }

        return $redirect;
    }

    public function reject(Request $request, TenantSubscriptionIntent $intent): RedirectResponse
    {
        if (! $intent->isPending()) {
            return redirect()
                ->route('central.subscription-intents.index')
                ->with('warning', 'This request was already processed.');
        }

        $intentType = $intent->intent_type;

        $intent->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
        ]);

        $mailNotice = null;
        $intent->load('tenant.domains');
        $tenant = $intent->tenant;
        $domain = $tenant->domains()->first()?->domain;
        if ($domain) {
            try {
                $this->officerNotifier->notifySubscriptionIntentRejected($tenant->fresh(), strtolower($domain), $intentType);
                $mailNotice = BarangayOfficerNotifier::mailNotDeliveredToInboxNotice();
            } catch (\Throwable $e) {
                report($e);
                $mailNotice = 'Request was saved, but tenant email could not be sent: '.$e->getMessage();
            }
        }

        $redirect = redirect()
            ->route('central.subscription-intents.index')
            ->with('success', 'Request rejected. Officers were notified when SMTP is configured.');

        if ($mailNotice !== null) {
            $redirect->with('mail_config_notice', $mailNotice);
        }

        return $redirect;
    }
}
