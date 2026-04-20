<?php

namespace App\Policies;

use App\Models\User;

class TenantUserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('users.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('users.create');
    }

    public function update(User $user, User $target): bool
    {
        return $user->hasPermission('users.update');
    }

    public function delete(User $user, User $target): bool
    {
        return $user->hasPermission('users.delete') && (int) $user->id !== (int) $target->id;
    }
}
