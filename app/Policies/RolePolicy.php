<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('users.update');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('users.update');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->hasPermission('users.update');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->hasPermission('users.update');
    }
}
