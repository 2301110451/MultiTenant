<?php

namespace App\Services;

use App\Enums\TenantRole;
use App\Models\User;

/**
 * Single place for post–Google-login redirects by tenant role (barangay portal only).
 */
final class TenantGoogleOAuthRedirectService
{
    public function pathAfterLogin(User $user): string
    {
        $role = $user->role instanceof TenantRole ? $user->role : TenantRole::Resident;

        return match ($role) {
            TenantRole::Captain => route('tenant.captain.dashboard', [], false),
            TenantRole::Secretary => route('tenant.secretary.dashboard', [], false),
            default => route('dashboard', [], false),
        };
    }
}
