<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function transition(User $user, Ticket $ticket): bool
    {
        return (int) $ticket->assignee_id === (int) $user->id
            || (config('filament-shield.super_admin.enabled')
                && $user->hasRole(config('filament-shield.super_admin.name', 'super_admin')));
    }
}
