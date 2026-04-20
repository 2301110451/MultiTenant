<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('reservations.view');
    }

    public function view(User $user, Reservation $reservation): bool
    {
        if (! $user->hasPermission('reservations.view')) {
            return false;
        }

        return ! $user->isResident() || (int) $reservation->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('reservations.create');
    }

    public function update(User $user, Reservation $reservation): bool
    {
        if (! $user->hasPermission('reservations.update')) {
            return false;
        }

        return ! $user->isResident() || (int) $reservation->user_id === (int) $user->id;
    }

    public function delete(User $user, Reservation $reservation): bool
    {
        return $user->hasPermission('reservations.delete');
    }
}
