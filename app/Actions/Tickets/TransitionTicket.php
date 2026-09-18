<?php

namespace App\Actions\Tickets;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class TransitionTicket
{
    public function handle(int $ticketId, string $targetStatus, User $actor): void
    {
        DB::transaction(function () use ($ticketId, $targetStatus, $actor): void {
            $ticket = Ticket::query()->lockForUpdate()->findOrFail($ticketId);
            Gate::forUser($actor)->authorize('transition', $ticket);

            if ($ticket->nextStatus() !== $targetStatus) {
                throw ValidationException::withMessages([
                    'ticketStatus' => 'This transition is no longer available. Check the current status and try again.',
                ]);
            }

            $previousStatus = $ticket->status;
            $ticket->status = $targetStatus;

            if ($targetStatus === 'resolved') {
                $ticket->resolved_at = now();
            }

            if ($targetStatus === 'closed') {
                $ticket->closed_at = now();
            }

            $ticket->save();
            $ticket->activities()->create([
                'user_id' => $actor->id,
                'event' => 'status_changed',
                'description' => Ticket::STATUSES[$previousStatus].' → '.Ticket::STATUSES[$targetStatus],
                'old_values' => ['status' => $previousStatus],
                'new_values' => ['status' => $targetStatus],
            ]);
        });
    }
}
