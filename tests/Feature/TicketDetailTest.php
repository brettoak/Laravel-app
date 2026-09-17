<?php

namespace Tests\Feature;

use App\Livewire\TicketList;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TicketDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_details_show_the_selected_ticket_and_preserve_list_state_when_closed(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create([
            'assignee_id' => $user->id,
            'description' => "First line\n<script>alert('unsafe')</script>",
            'priority' => 'urgent',
            'due_at' => '2026-10-01 15:30:00',
        ]);

        Livewire::actingAs($user)->test(TicketList::class)
            ->set('search', 'no matching results')
            ->call('gotoPage', 2)
            ->call('openTicket', $ticket->id)
            ->assertSet('showTicketDetails', true)
            ->assertSee($ticket->title)
            ->assertSee($ticket->description)
            ->assertDontSee("<script>alert('unsafe')</script>", false)
            ->assertSee('Urgent')
            ->assertSee($user->name)
            ->assertSee('Oct 01, 2026 15:30')
            ->call('closeTicket')
            ->assertSet('selectedTicketId', null)
            ->assertSet('showTicketDetails', false)
            ->assertSet('search', 'no matching results')
            ->assertSet('paginators.page', 2)
            ->assertDontSee($ticket->title);
    }

    public function test_details_handle_unassigned_tickets_without_a_deadline(): void
    {
        $ticket = Ticket::factory()->create(['assignee_id' => null, 'due_at' => null]);

        Livewire::actingAs(User::factory()->create())->test(TicketList::class)
            ->call('openTicket', $ticket->id)
            ->assertSee('Unassigned')
            ->assertSee('No deadline');
    }

    public function test_deleted_and_missing_tickets_cannot_be_opened(): void
    {
        $ticket = Ticket::factory()->create();
        $ticket->delete();

        foreach ([$ticket->id, $ticket->id + 100] as $id) {
            try {
                Livewire::actingAs(User::factory()->create())->test(TicketList::class)
                    ->call('openTicket', $id);
                $this->fail('Unavailable tickets must not be opened.');
            } catch (ModelNotFoundException $exception) {
                $this->assertSame(Ticket::class, $exception->getModel());
            }
        }
    }

    public function test_guests_cannot_open_ticket_details(): void
    {
        $ticket = Ticket::factory()->create();

        Livewire::test(TicketList::class)
            ->call('openTicket', $ticket->id)
            ->assertForbidden();
    }
}
