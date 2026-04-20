<?php

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\User;
use App\Support\Tenancy;

class SupportTicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->activeTenantUser($user) && $user->hasPermission('support.view');
    }

    public function create(User $user): bool
    {
        return $this->activeTenantUser($user) && $user->hasPermission('support.view');
    }

    public function view(User $user, SupportTicket $ticket): bool
    {
        if (! $this->activeTenantUser($user)) {
            return false;
        }

        $tenant = Tenancy::currentTenant();

        return $tenant !== null && (int) $ticket->tenant_id === (int) $tenant->id;
    }

    private function activeTenantUser(User $user): bool
    {
        return $user->is_active;
    }
}
