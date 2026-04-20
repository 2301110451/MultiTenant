<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\Tenant;
use App\Support\Pricing;
use App\Support\Tenancy;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    public function assertNoDoubleBooking(
        int $facilityId,
        Carbon $startsAt,
        Carbon $endsAt,
        ?int $excludeReservationId = null,
    ): void {
        if ($endsAt->lte($startsAt)) {
            throw ValidationException::withMessages([
                'ends_at' => ['End time must be after start time.'],
            ]);
        }

        $conflict = Reservation::query()
            ->where('facility_id', $facilityId)
            ->whereIn('status', [
                ReservationStatus::Pending->value,
                ReservationStatus::Approved->value,
            ])
            ->where(function ($q) use ($startsAt, $endsAt) {
                $q->where(function ($q2) use ($startsAt, $endsAt) {
                    $q2->where('starts_at', '<', $endsAt)
                        ->where('ends_at', '>', $startsAt);
                });
            })
            ->when($excludeReservationId, fn ($q) => $q->where('id', '!=', $excludeReservationId))
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'facility_id' => ['This facility is already booked for the selected time range.'],
            ]);
        }
    }

    public function assertWithinPlanLimits(Tenant $tenant): void
    {
        if (! Pricing::enforcementEnabled()) {
            return;
        }

        $subscription = $tenant->subscription;
        if (! $subscription || ! $subscription->plan) {
            return;
        }

        $limit = Pricing::monthlyReservationLimit($subscription->plan);
        if ($limit === null) {
            return;
        }

        $count = Reservation::query()
            ->whereYear('starts_at', now()->year)
            ->whereMonth('starts_at', now()->month)
            ->whereIn('status', [
                ReservationStatus::Pending->value,
                ReservationStatus::Approved->value,
                ReservationStatus::Completed->value,
            ])
            ->count();

        if ($count >= $limit) {
            throw ValidationException::withMessages([
                'plan' => ['Monthly reservation limit reached for your subscription plan.'],
            ]);
        }
    }

    public function generateQrTokenIfPremium(): ?string
    {
        if (! Pricing::enforcementEnabled()) {
            return null;
        }

        $tenant = Tenancy::currentTenant();
        if (! $tenant?->subscription?->plan?->allows('qr_checkin')) {
            return null;
        }

        return Str::uuid()->toString();
    }
}
