<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Mail\ReservationApprovedMail;
use App\Models\Facility;
use App\Models\Reservation;
use App\Services\ReservationService;
use App\Support\Tenancy;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function __construct(
        private ReservationService $reservations,
    ) {}

    public function index(Request $request): View
    {
        $query = Reservation::query()->with(['facility', 'user'])->latest();

        if ($request->user('tenant')->isResident()) {
            $query->where('user_id', $request->user('tenant')->id);
        }

        $reservations = $query->paginate(20);

        return view('tenant.reservations.index', compact('reservations'));
    }

    public function create(): View
    {
        $facilities = Facility::query()->where('is_active', true)->orderBy('name')->get();

        return view('tenant.reservations.create', compact('facilities'));
    }

    public function store(Request $request): RedirectResponse
    {
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

        Reservation::query()->create([
            'user_id' => $request->user('tenant')->id,
            'facility_id' => $data['facility_id'],
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => ReservationStatus::Pending,
            'purpose' => $data['purpose'] ?? null,
            'is_special_request' => (bool) ($data['is_special_request'] ?? false),
            'qr_token' => $qr,
        ]);

        return redirect()->route('tenant.reservations.index')->with('status', 'reservation-created');
    }

    public function show(Reservation $reservation): View
    {
        $user = auth('tenant')->user();
        if ($user?->isResident() && (int) $reservation->user_id !== (int) $user->id) {
            abort(403);
        }

        $reservation->load(['facility', 'user', 'equipment']);

        return view('tenant.reservations.show', compact('reservation'));
    }

    public function update(Request $request, Reservation $reservation): RedirectResponse
    {
        $user = $request->user('tenant');

        abort_unless($user && ($user->isSecretary() || $user->isCaptain()), 403);

        $data = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected,completed'],
        ]);
        $previousStatus = $reservation->status->value;

        if ($data['status'] === 'approved') {
            $reservation->approved_by = $user->id;
        }

        $reservation->status = ReservationStatus::from($data['status']);
        $reservation->save();

        // Notify resident when an officer approves a reservation.
        if ($data['status'] === 'approved' && $previousStatus !== 'approved') {
            $reservation->loadMissing(['facility', 'user', 'approver']);
            if ($reservation->user?->email) {
                Mail::to($reservation->user->email)->send(new ReservationApprovedMail($reservation));
            }
        }

        return redirect()->route('tenant.reservations.show', $reservation)->with('status', 'reservation-updated');
    }

    public function destroy(Request $request, Reservation $reservation): RedirectResponse
    {
        $user = $request->user('tenant');
        abort_unless($user && ($user->isSecretary() || $user->isCaptain()), 403);

        $reservation->delete();

        return redirect()->route('tenant.reservations.index')->with('status', 'reservation-deleted');
    }

    public function calendar(): View
    {
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
}
