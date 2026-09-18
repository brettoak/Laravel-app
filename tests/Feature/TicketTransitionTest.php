<?php

namespace Tests\Feature;

use App\Actions\Tickets\TransitionTicket;
use App\Livewire\TicketList;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TicketTransitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignee_can_complete_the_workflow_with_timestamps_and_audit_records(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['assignee_id' => $user->id, 'status' => 'open', 'resolved_at' => null, 'closed_at' => null]);
        $component = Livewire::actingAs($user)->test(TicketList::class)
            ->set('status', 'open')->call('openTicket', $ticket->id);

        foreach (['in_progress', 'resolved', 'closed'] as $status) {
            $this->travel(1)->minutes();
            $previous = $ticket->status;
            $component->call('transitionTicket', $status)->assertHasNoErrors()
                ->assertSee('Status updated to '.Ticket::STATUSES[$status].'.')
                ->assertSet('showTicketDetails', true)->assertSet('status', 'open');
            $this->assertSame($status, $ticket->refresh()->status);
            $activity = $ticket->activities()->latest('id')->first();
            $this->assertSame($user->id, $activity->user_id);
            $this->assertSame(['status' => $previous], $activity->old_values);
            $this->assertSame(['status' => $status], $activity->new_values);
            if ($status === 'in_progress') {
                $this->assertNull($ticket->resolved_at);
                $this->assertNull($ticket->closed_at);
            } elseif ($status === 'resolved') {
                $this->assertTrue($ticket->resolved_at->equalTo(now()->startOfSecond()));
                $resolvedAt = $ticket->resolved_at->copy();
            } else {
                $this->assertTrue($ticket->resolved_at->equalTo($resolvedAt));
                $this->assertTrue($ticket->closed_at->equalTo(now()->startOfSecond()));
            }
        }
        $component->assertSee('No further transitions are available.')
            ->assertViewHas('tickets', fn ($tickets) => $tickets->total() === 0);
        $this->assertSame(3, $ticket->activities()->count());
    }

    public function test_invalid_backward_skipped_and_duplicate_transitions_are_rejected(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['assignee_id' => $user->id, 'status' => 'open', 'resolved_at' => null, 'closed_at' => null]);
        $component = Livewire::actingAs($user)->test(TicketList::class)->call('openTicket', $ticket->id);
        foreach (['open', 'resolved', 'closed', 'invalid'] as $target) {
            $component->call('transitionTicket', $target)->assertHasErrors('ticketStatus');
            $this->assertSame('open', $ticket->refresh()->status);
        }
        $component->call('transitionTicket', 'in_progress')->assertHasNoErrors()
            ->call('transitionTicket', 'in_progress')->assertHasErrors('ticketStatus')
            ->call('transitionTicket', 'open')->assertHasErrors('ticketStatus');
        $this->assertSame(1, $ticket->activities()->count());
        $component->call('closeTicket')->assertHasNoErrors()->assertSet('statusMessage', null);
    }

    public function test_other_users_and_guests_cannot_transition_tickets(): void
    {
        $user = User::factory()->create();
        foreach ([null, User::factory()->create()->id] as $assignee) {
            $ticket = Ticket::factory()->create(['assignee_id' => $assignee, 'status' => 'open']);
            Livewire::actingAs($user)->test(TicketList::class)->call('openTicket', $ticket->id)
                ->assertDontSee('Start processing')
                ->call('transitionTicket', 'in_progress')->assertForbidden();
            $this->assertSame('open', $ticket->refresh()->status);
        }
        auth()->logout();
        Livewire::test(TicketList::class)->call('transitionTicket', 'in_progress')->assertForbidden();
        $this->assertDatabaseCount('ticket_activities', 0);
    }

    public function test_super_admin_can_process_an_unassigned_ticket(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('super_admin', 'web'));
        $ticket = Ticket::factory()->create(['assignee_id' => null, 'status' => 'open']);
        Livewire::actingAs($admin)->test(TicketList::class)->call('openTicket', $ticket->id)
            ->assertSee('Start processing')->call('transitionTicket', 'in_progress')->assertHasNoErrors();
        $this->assertSame('in_progress', $ticket->refresh()->status);
    }

    public function test_stale_drawers_cannot_advance_a_changed_ticket_or_use_an_old_assignment(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['assignee_id' => $user->id, 'status' => 'open', 'resolved_at' => null, 'closed_at' => null]);
        $component = Livewire::actingAs($user)->test(TicketList::class)->call('openTicket', $ticket->id);
        $ticket->update(['status' => 'in_progress']);
        $component->call('transitionTicket', 'in_progress')->assertHasErrors('ticketStatus')
            ->assertSee('Mark as resolved');
        $ticket->update(['assignee_id' => null]);
        $component->call('transitionTicket', 'resolved')->assertForbidden();
        $this->assertDatabaseCount('ticket_activities', 0);
    }

    public function test_closed_tickets_are_terminal(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['assignee_id' => $user->id, 'status' => 'closed']);
        Livewire::actingAs($user)->test(TicketList::class)->call('openTicket', $ticket->id)
            ->call('transitionTicket', 'open')->assertHasErrors('ticketStatus');
        $this->assertSame('closed', $ticket->refresh()->status);
        $this->assertDatabaseCount('ticket_activities', 0);
    }

    public function test_status_update_rolls_back_if_activity_record_cannot_be_saved(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['assignee_id' => $user->id, 'status' => 'open', 'resolved_at' => null, 'closed_at' => null]);
        TicketActivity::creating(fn () => throw new \RuntimeException('Audit write failed'));
        try {
            app(TransitionTicket::class)->handle($ticket->id, 'in_progress', $user);
            $this->fail('Expected audit write failure.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Audit write failed', $exception->getMessage());
            $this->assertSame('open', $ticket->refresh()->status);
        } finally {
            TicketActivity::flushEventListeners();
        }
    }
}
