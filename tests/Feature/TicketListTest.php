<?php

namespace Tests\Feature;

use App\Livewire\TicketList;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TicketListTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_page_requires_authentication(): void
    {
        $this->get(route('tickets.index'))
            ->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('tickets.index'))
            ->assertOk()
            ->assertSee('Ticket management');
    }

    public function test_ticket_page_is_available_in_the_filament_admin_panel(): void
    {
        $this->get('/admin/ticket-management')
            ->assertRedirect('/admin/login');

        $this->actingAs(User::factory()->create())
            ->get('/admin/ticket-management')
            ->assertOk()
            ->assertSee('Ticket Management')
            ->assertSee('Search, filter and prioritise customer requests.');
    }

    public function test_tickets_can_be_searched_by_ticket_details_and_people(): void
    {
        $viewer = User::factory()->create();
        $matchingRequester = User::factory()->create(['name' => 'Acme Customer']);
        $otherRequester = User::factory()->create(['name' => 'Other Customer']);

        Ticket::factory()->for($matchingRequester, 'requester')->create([
            'assignee_id' => null,
            'ticket_number' => 'TKT-10000001',
            'title' => 'Printer is offline',
            'description' => 'The office printer stopped responding.',
        ]);
        Ticket::factory()->for($otherRequester, 'requester')->create([
            'assignee_id' => null,
            'ticket_number' => 'TKT-10000002',
            'title' => 'Password reset',
            'description' => 'Cannot access the account.',
        ]);

        Livewire::actingAs($viewer)
            ->test(TicketList::class)
            ->set('search', 'Acme')
            ->assertSee('Printer is offline')
            ->assertDontSee('Password reset')
            ->set('search', '10000002')
            ->assertSee('Password reset')
            ->assertDontSee('Printer is offline');
    }

    public function test_tickets_can_be_filtered_by_status_priority_and_assignee(): void
    {
        $viewer = User::factory()->create();
        $requester = User::factory()->create();
        $alex = User::factory()->create(['name' => 'Alex Agent']);
        $sam = User::factory()->create(['name' => 'Sam Agent']);

        Ticket::factory()->for($requester, 'requester')->for($alex, 'assignee')->create([
            'title' => 'Matching urgent ticket',
            'status' => 'open',
            'priority' => 'urgent',
        ]);
        Ticket::factory()->for($requester, 'requester')->for($alex, 'assignee')->create([
            'title' => 'Wrong priority ticket',
            'status' => 'open',
            'priority' => 'low',
        ]);
        Ticket::factory()->for($requester, 'requester')->for($sam, 'assignee')->create([
            'title' => 'Wrong assignee ticket',
            'status' => 'open',
            'priority' => 'urgent',
        ]);
        Ticket::factory()->for($requester, 'requester')->for($alex, 'assignee')->create([
            'title' => 'Wrong status ticket',
            'status' => 'closed',
            'priority' => 'urgent',
        ]);

        Livewire::actingAs($viewer)
            ->test(TicketList::class)
            ->set('status', 'open')
            ->set('priority', 'urgent')
            ->set('assignee', (string) $alex->id)
            ->assertSee('Matching urgent ticket')
            ->assertDontSee('Wrong priority ticket')
            ->assertDontSee('Wrong assignee ticket')
            ->assertDontSee('Wrong status ticket');
    }

    public function test_tickets_can_be_sorted_in_both_directions(): void
    {
        $viewer = User::factory()->create();
        $requester = User::factory()->create();

        Ticket::factory()->for($requester, 'requester')->create([
            'assignee_id' => null,
            'title' => 'Zulu issue',
        ]);
        Ticket::factory()->for($requester, 'requester')->create([
            'assignee_id' => null,
            'title' => 'Alpha issue',
        ]);

        Livewire::actingAs($viewer)
            ->test(TicketList::class)
            ->call('sortBy', 'title')
            ->assertSet('sortField', 'title')
            ->assertSet('sortDirection', 'asc')
            ->assertSeeInOrder(['Alpha issue', 'Zulu issue'])
            ->call('sortBy', 'title')
            ->assertSet('sortDirection', 'desc')
            ->assertSeeInOrder(['Zulu issue', 'Alpha issue']);
    }

    public function test_ticket_list_is_paginated(): void
    {
        $viewer = User::factory()->create();
        $requester = User::factory()->create();

        Ticket::factory()
            ->count(11)
            ->for($requester, 'requester')
            ->state(['assignee_id' => null])
            ->create();

        Livewire::actingAs($viewer)
            ->test(TicketList::class)
            ->assertViewHas('tickets', fn ($tickets) => $tickets->count() === 10 && $tickets->total() === 11)
            ->set('perPage', 25)
            ->assertViewHas('tickets', fn ($tickets) => $tickets->count() === 11 && $tickets->total() === 11);
    }

    public function test_all_filters_can_be_cleared(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(TicketList::class)
            ->set('search', 'printer')
            ->set('status', 'open')
            ->set('priority', 'high')
            ->set('assignee', '99')
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('status', '')
            ->assertSet('priority', '')
            ->assertSet('assignee', '');
    }
}
