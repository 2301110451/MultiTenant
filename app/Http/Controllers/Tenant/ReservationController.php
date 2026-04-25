<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Mail\ReservationApprovedMail;
use App\Models\Facility;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationService;
use App\Services\TenantAuditLogger;
use App\Support\Pricing;
use App\Support\Tenancy;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function __construct(
        private ReservationService $reservations,
        private TenantAuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $actor = $request->user('tenant');
        Gate::forUser($actor)->authorize('viewAny', Reservation::class);

        $query = Reservation::query()->with(['facility', 'user'])->latest();

        if ($actor->isResident()) {
            $query->where('user_id', $actor->id);
        }

        $reservations = $query->paginate(20);

        $canCreate = Gate::forUser($actor)->allows('create', Reservation::class);
        $modal = (string) old('_modal_context', (string) $request->query('modal', ''));
        $facilities = collect();
        $supportsIntegratedPayments = false;
        $preselectFacilityId = null;

        if ($canCreate) {
            $tenant = Tenancy::currentTenant();
            $plan = $tenant?->subscription?->plan ?? $tenant?->plan;
            $supportsIntegratedPayments = (bool) ($plan && Pricing::allows('integrated_payments', $plan));
            $facilities = Facility::query()->where('is_active', true)->orderBy('name')->get();

            $rawPre = old('facility_id', $request->query('facility_id'));
            if ($rawPre !== null && $rawPre !== '') {
                $candidate = (int) $rawPre;
                if ($candidate > 0 && $facilities->firstWhere('id', $candidate)) {
                    $preselectFacilityId = $candidate;
                }
            }
        }

        return view('tenant.reservations.index', compact(
            'reservations',
            'canCreate',
            'modal',
            'facilities',
            'supportsIntegratedPayments',
            'preselectFacilityId'
        ));
    }

    public function create(Request $request): View
    {
        Gate::forUser($request->user('tenant'))->authorize('create', Reservation::class);

        $tenant = Tenancy::currentTenant();
        $plan = $tenant?->subscription?->plan ?? $tenant?->plan;
        $supportsIntegratedPayments = $plan && Pricing::allows('integrated_payments', $plan);

        $facilities = Facility::query()->where('is_active', true)->orderBy('name')->get();

        $preselectFacilityId = null;
        $rawPre = $request->query('facility_id');
        if ($rawPre !== null && $rawPre !== '') {
            $candidate = (int) $rawPre;
            if ($candidate > 0 && $facilities->firstWhere('id', $candidate)) {
                $preselectFacilityId = $candidate;
            }
        }

        return view('tenant.reservations.create', compact('facilities', 'preselectFacilityId', 'supportsIntegratedPayments'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::forUser($request->user('tenant'))->authorize('create', Reservation::class);

        $tenant = Tenancy::currentTenant();
        if ($tenant) {
            $this->reservations->assertWithinPlanLimits($tenant);
        }

        $data = $request->validate([
            // Force tenant DB for existence check to avoid central DB lookup.
            'facility_id' => ['required', 'exists:tenant.facilities,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'purpose' => ['nullable', 'string', 'max:2000'],
            'payment_option' => ['nullable', 'string', 'in:cash,gcash,paymaya,bank_transfer,stripe,paypal'],
            'is_special_request' => ['boolean'],
        ]);

        $startsAt = Carbon::parse($data['starts_at']);
        $endsAt = Carbon::parse($data['ends_at']);

        $this->reservations->assertNoDoubleBooking(
            (int) $data['facility_id'],
            $startsAt,
            $endsAt,
        );

        $qr = $this->reservations->generateQrTokenIfPremium();

        $plan = $tenant?->subscription?->plan ?? $tenant?->plan;
        $supportsIntegratedPayments = $plan && Pricing::allows('integrated_payments', $plan);
        $paymentOption = $supportsIntegratedPayments ? ($data['payment_option'] ?? null) : null;

        $reservation = Reservation::query()->create([
            'user_id' => $request->user('tenant')->id,
            'facility_id' => $data['facility_id'],
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => ReservationStatus::Pending,
            'purpose' => $data['purpose'] ?? null,
            'payment_option' => $paymentOption,
            'is_special_request' => (bool) ($data['is_special_request'] ?? false),
            'qr_token' => $qr,
        ]);

        $reservation->loadMissing(['facility', 'user']);

        $this->audit->log($request, 'tenant_reservation.created', Reservation::class, (int) $reservation->id, [
            'target_label' => 'Reservation #'.$reservation->id,
            'status' => 'success',
            'after_values' => [
                'id' => (int) $reservation->id,
                'facility_id' => (int) $reservation->facility_id,
                'facility_name' => (string) ($reservation->facility?->name ?? ''),
                'user_id' => (int) $reservation->user_id,
                'status' => (string) $reservation->status->value,
                'starts_at' => optional($reservation->starts_at)->toIso8601String(),
                'ends_at' => optional($reservation->ends_at)->toIso8601String(),
                'payment_option' => $reservation->payment_option,
            ],
        ]);

        return redirect()->route('tenant.reservations.index')->with('status', 'reservation-created');
    }

    public function show(Request $request, Reservation $reservation): View
    {
        Gate::forUser($request->user('tenant'))->authorize('view', $reservation);

        $reservation->load(['facility', 'user', 'equipment', 'payments']);

        return view('tenant.reservations.show', compact('reservation'));
    }

    public function update(Request $request, Reservation $reservation): RedirectResponse
    {
        $user = $request->user('tenant');
        Gate::forUser($user)->authorize('update', $reservation);
        $before = [
            'status' => (string) $reservation->status->value,
            'approved_by' => $reservation->approved_by,
            'revenue_amount' => $reservation->revenue_amount,
        ];

        $data = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected,completed'],
        ]);
        $previousStatus = $reservation->status->value;

        if ($data['status'] === 'approved') {
            $reservation->approved_by = $user->id;
        }

        $reservation->status = ReservationStatus::from($data['status']);
        if ($reservation->status === ReservationStatus::Completed) {
            $reservation->revenue_amount = $this->calculateReservationRevenue($reservation);
        }
        $reservation->save();

        // Notify resident when an officer approves a reservation.
        if ($data['status'] === 'approved' && $previousStatus !== 'approved') {
            $reservation->loadMissing(['facility', 'user', 'approver']);
            if ($reservation->user?->email) {
                Mail::to($reservation->user->email)->send(new ReservationApprovedMail($reservation));
            }
        }

        $after = [
            'status' => (string) $reservation->status->value,
            'approved_by' => $reservation->approved_by,
            'revenue_amount' => $reservation->revenue_amount,
        ];

        $this->audit->log($request, 'tenant_reservation.updated', Reservation::class, (int) $reservation->id, [
            'target_label' => 'Reservation #'.$reservation->id,
            'status' => 'success',
            'before_values' => $before,
            'after_values' => $after,
        ]);

        return redirect()->route('tenant.reservations.show', $reservation)->with('status', 'reservation-updated');
    }

    public function destroy(Request $request, Reservation $reservation): RedirectResponse
    {
        $user = $request->user('tenant');
        Gate::forUser($user)->authorize('delete', $reservation);
        $before = [
            'id' => (int) $reservation->id,
            'status' => (string) $reservation->status->value,
            'facility_id' => (int) $reservation->facility_id,
            'user_id' => (int) $reservation->user_id,
        ];
        $reservationId = (int) $reservation->id;

        $reservation->delete();

        $this->audit->log($request, 'tenant_reservation.deleted', Reservation::class, $reservationId, [
            'target_label' => 'Reservation #'.$reservationId,
            'status' => 'success',
            'before_values' => $before,
        ]);

        return redirect()->route('tenant.reservations.index')->with('status', 'reservation-deleted');
    }

    public function calendar(Request $request): View
    {
        Gate::forUser($request->user('tenant'))->authorize('viewAny', Reservation::class);

        $events = Reservation::query()
            ->with('facility')
            ->whereIn('status', ['pending', 'approved'])
            ->where('starts_at', '>=', now()->subMonth())
            ->get()
            ->map(fn (Reservation $r) => [
                'title' => $r->facility->name.' — '.$r->status->value,
                'start' => $r->starts_at->toIso8601String(),
                'end' => $r->ends_at->toIso8601String(),
            ]);

        return view('tenant.reservations.calendar', [
            'events' => $events,
        ]);
    }

    public function markReturned(Request $request, Reservation $reservation): RedirectResponse
    {
        $user = $request->user('tenant');
        abort_unless(
            $user && $user->isResident() && $user->hasPermission('reservations.update') && (int) $reservation->user_id === (int) $user->id,
            403
        );

        if (! in_array($reservation->status->value, ['approved', 'completed'], true)) {
            return redirect()
                ->route('tenant.reservations.show', $reservation)
                ->with('status', 'Only approved reservations can be marked as returned.');
        }

        if (! $reservation->checked_out_at) {
            $reservation->checked_out_at = now();
        }

        if ($reservation->status->value === 'approved') {
            $reservation->status = ReservationStatus::Completed;
        }
        if ($reservation->status === ReservationStatus::Completed) {
            $reservation->revenue_amount = $this->calculateReservationRevenue($reservation);
        }
        $reservation->save();

        $reservation->loadMissing(['facility', 'user']);
        $this->notifyTenantOfficers(
            $reservation,
            'Equipment/facility returned',
            "Resident {$reservation->user->name} marked reservation #{$reservation->id} ({$reservation->facility->name}) as returned."
        );

        return redirect()
            ->route('tenant.reservations.show', $reservation)
            ->with('status', 'Marked as returned. Tenant officers were notified.');
    }

    public function markDamage(Request $request, Reservation $reservation): RedirectResponse
    {
        $user = $request->user('tenant');
        abort_unless($user && $user->hasPermission('reservations.update'), 403);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $payment = Payment::query()->create([
            'reservation_id' => $reservation->id,
            'amount' => $data['amount'],
            'method' => 'damage',
            'external_ref' => $data['description'] ?? null,
            'status' => 'pending',
        ]);

        if (! $reservation->checked_out_at) {
            $reservation->checked_out_at = now();
        }
        if ($reservation->status->value === 'approved') {
            $reservation->status = ReservationStatus::Completed;
        }
        if ($reservation->status === ReservationStatus::Completed) {
            $reservation->revenue_amount = $this->calculateReservationRevenue($reservation);
        }
        $reservation->save();

        $reservation->loadMissing(['facility', 'user']);
        if ($reservation->user?->email) {
            try {
                Mail::raw(
                    "A damage charge was added to your reservation #{$reservation->id} ({$reservation->facility->name}). "
                    .'Amount due: PHP '.number_format((float) $payment->amount, 2).'. '
                    .'Please open your reservation and mark as paid after payment.',
                    fn ($message) => $message
                        ->to($reservation->user->email)
                        ->subject('Damage charge added to your reservation')
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()
            ->route('tenant.reservations.show', $reservation)
            ->with('status', 'Damage amount saved. The renter was notified.');
    }

    public function markPaymentPaid(Request $request, Reservation $reservation, Payment $payment): RedirectResponse
    {
        $user = $request->user('tenant');
        abort_unless($user, 403);

        $isOwnerResident = $user->isResident() && (int) $reservation->user_id === (int) $user->id;
        $isTenantManager = $user->canManageTenant() && $user->hasPermission('reservations.update');
        abort_unless($isOwnerResident || $isTenantManager, 403);
        abort_unless((int) $payment->reservation_id === (int) $reservation->id, 404);

        if ($payment->status === 'paid') {
            return redirect()
                ->route('tenant.reservations.show', $reservation)
                ->with('status', 'This payment is already marked as paid.');
        }

        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $reservation->loadMissing(['facility', 'user']);
        $this->notifyTenantOfficers(
            $reservation,
            'Damage payment marked as paid',
            "Resident {$reservation->user->name} marked a payment as paid for reservation #{$reservation->id} ({$reservation->facility->name}). "
            .'Amount: PHP '.number_format((float) $payment->amount, 2).'.'
        );

        return redirect()
            ->route('tenant.reservations.show', $reservation)
            ->with('status', 'Payment marked as paid. Tenant officers were notified.');
    }

    private function notifyTenantOfficers(Reservation $reservation, string $subject, string $body): void
    {
        try {
            $emails = User::query()
                ->whereIn('role', ['tenant_admin', 'staff'])
                ->pluck('email')
                ->filter()
                ->unique()
                ->values()
                ->all();

            foreach ($emails as $email) {
                Mail::raw($body, fn ($message) => $message->to($email)->subject($subject));
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function calculateReservationRevenue(Reservation $reservation): float
    {
        $reservation->loadMissing('facility');

        $minutes = max(0, (int) $reservation->starts_at?->diffInMinutes($reservation->ends_at ?? $reservation->starts_at));
        if ($minutes === 0) {
            return 0.0;
        }

        $hours = $minutes / 60;
        $rate = (float) ($reservation->facility?->hourly_rate ?? 0);

        return round($hours * $rate, 2);
    }
}
