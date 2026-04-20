<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantSubscriptionIntent;
use App\Services\BarangayOfficerNotifier;
use App\Support\CentralUrl;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantSubscriptionIntentController extends Controller
{
    public function __construct(
        private BarangayOfficerNotifier $officerNotifier,
    ) {}

    public function show(Request $request, Tenant $tenant): View
    {
        $tenant->loadMissing(['subscription.plan', 'plan']);

        $expiry = Carbon::createFromTimestamp((int) $request->query('expires'));
        $postUrl = CentralUrl::temporarySignedRoute(
            'central.subscription-intent.store',
            $expiry,
            ['tenant' => $tenant->id]
        );

        return view('central.subscription-intent', [
            'tenant' => $tenant,
            'postUrl' => $postUrl,
        ]);
    }

    public function store(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'intent' => ['required', 'in:unsubscribe,extend'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $intent = TenantSubscriptionIntent::query()->create([
            'tenant_id' => $tenant->id,
            'intent_type' => $data['intent'],
            'message' => $data['message'] ?? null,
            'status' => 'pending',
        ]);

        if ($intent->intent_type === 'unsubscribe') {
            try {
                $this->officerNotifier->notifyCentralAdminsOfUnsubscribeIntent($tenant->fresh(), $intent->message);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()
            ->route('central.subscription-intent.thanks')
            ->with('tenant_name', $tenant->name);
    }

    public function thanks(): View
    {
        return view('central.subscription-intent-thanks');
    }
}
