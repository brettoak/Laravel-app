<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        $status = fake()->randomElement(array_keys(Ticket::STATUSES));

        return [
            'ticket_number' => 'TKT-'.fake()->unique()->numerify('########'),
            'requester_id' => User::factory(),
            'assignee_id' => fake()->boolean(75) ? User::factory() : null,
            'title' => fake()->sentence(6),
            'description' => fake()->paragraphs(2, true),
            'status' => $status,
            'priority' => fake()->randomElement(array_keys(Ticket::PRIORITIES)),
            'due_at' => fake()->optional()->dateTimeBetween('-2 days', '+14 days'),
            'resolved_at' => in_array($status, ['resolved', 'closed'], true) ? fake()->dateTimeBetween('-7 days') : null,
            'closed_at' => $status === 'closed' ? fake()->dateTimeBetween('-3 days') : null,
        ];
    }
}
