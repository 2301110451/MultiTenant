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

        // Same entry point for all roles; sidebar and permissions reflect TenantAdmin / Staff / Resident.
        return match ($role) {
            TenantRole::TenantAdmin,
            TenantRole::Staff,
            TenantRole::Resident => route('dashboard', [], false),
        };
    }
}
