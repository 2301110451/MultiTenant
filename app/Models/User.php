<?php

namespace App\Models;

use App\Casts\TenantRoleCast;
use App\Enums\TenantRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'phone'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $connection = 'tenant';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => TenantRoleCast::class,
        ];
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function damageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class, 'reported_by');
    }

    public function isSecretary(): bool
    {
        return $this->role === TenantRole::Secretary;
    }

    public function isCaptain(): bool
    {
        return $this->role === TenantRole::Captain;
    }

    public function isCustodian(): bool
    {
        return $this->role === TenantRole::Custodian;
    }

    public function isResident(): bool
    {
        return $this->role === TenantRole::Resident;
    }
}
