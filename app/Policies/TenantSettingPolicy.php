<?php

namespace App\Policies;

use App\Models\TenantSetting;
use App\Models\User;

class TenantSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('settings.view');
    }

    public function update(User $user, TenantSetting $setting): bool
    {
        return $user->hasPermission('settings.update');
    }
}
