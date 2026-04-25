<?php

namespace App\Models;

use App\Casts\TenantRoleCast;
use App\Enums\TenantRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'role',
    'phone',
    'is_active',
    'appearance_accent_color',
    'appearance_background_color',
    'appearance_sidebar_background_color',
])]
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
            'is_active' => 'boolean',
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

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function directPermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    /**
     * Align role_user pivot with users.role (same as User Management). Required for hasPermission() for non-admins.
     */
    public function syncRbacRoleFromColumn(): void
    {
        $roleName = $this->role instanceof TenantRole ? $this->role->value : TenantRole::Resident->value;
        $role = Role::query()->where('name', $roleName)->first();
        if ($role === null) {
            return;
        }

        $this->roles()->sync([$role->id]);
    }

    public function isTenantAdmin(): bool
    {
        return $this->role === TenantRole::TenantAdmin;
    }

    public function isStaff(): bool
    {
        return $this->role === TenantRole::Staff;
    }

    public function isViewer(): bool
    {
        return false;
    }

    public function isResident(): bool
    {
        return $this->role === TenantRole::Resident;
    }

    public function canManageTenant(): bool
    {
        return $this->isTenantAdmin() || $this->isStaff();
    }

    public function hasPermission(string $permission): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->isTenantAdmin()) {
            return true;
        }

        $direct = $this->directPermissions()->where('name', $permission)->exists();
        if ($direct) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', fn ($query) => $query->where('name', $permission))
            ->exists();
    }
}
