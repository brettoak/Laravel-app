<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\TicketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_one_thousand_tickets_using_a_shared_user_pool(): void
    {
        User::factory()->count(2)->create();

        $this->seed(TicketSeeder::class);

        $this->assertDatabaseCount('tickets', 1000);
        $this->assertDatabaseCount('users', 20);
        $this->assertSame(0, Ticket::query()->whereNull('requester_id')->count());
        $this->assertSame(
            0,
            Ticket::query()
                ->whereIn('status', ['resolved', 'closed'])
                ->whereNull('resolved_at')
                ->count()
        );
        $this->assertSame(
            0,
            Ticket::query()
                ->where('status', 'closed')
                ->whereNull('closed_at')
                ->count()
        );
    }
}
