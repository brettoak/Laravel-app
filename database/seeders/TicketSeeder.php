<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Seed tickets for the ticket management page.
     */
    public function run(): void
    {
        $users = User::query()->get();

        if ($users->count() < 20) {
            $users = $users->merge(
                User::factory()->count(20 - $users->count())->create()
            );
        }

        $userIds = $users->modelKeys();
        $titles = [
            'Cannot sign in to my account',
            'Password reset email has not arrived',
            'Unable to upload an attachment',
            'Dashboard is loading slowly',
            'Billing details need to be updated',
            'Export contains incorrect data',
            'Email notifications are not being sent',
            'Request for access to a workspace',
            'Two-factor authentication is not working',
            'Unable to update profile information',
            'Report page returns an error',
            'Duplicate charge on latest invoice',
            'Need help configuring team permissions',
            'Search results are incomplete',
            'Account should be transferred to a new owner',
        ];

        Ticket::factory()
            ->count(1000)
            ->state(function () use ($userIds, $titles): array {
                $status = fake()->randomElement([
                    'open', 'open', 'open', 'open',
                    'in_progress', 'in_progress', 'in_progress',
                    'resolved', 'resolved',
                    'closed',
                ]);
                $priority = fake()->randomElement([
                    'low', 'low',
                    'medium', 'medium', 'medium', 'medium', 'medium',
                    'high', 'high',
                    'urgent',
                ]);
                $createdAt = fake()->dateTimeBetween('-6 months', '-1 hour');
                $updatedAt = fake()->dateTimeBetween($createdAt, 'now');
                $resolvedAt = in_array($status, ['resolved', 'closed'], true)
                    ? fake()->dateTimeBetween($createdAt, $updatedAt)
                    : null;
                $closedAt = $status === 'closed'
                    ? fake()->dateTimeBetween($resolvedAt, $updatedAt)
                    : null;

                return [
                    'requester_id' => fake()->randomElement($userIds),
                    'assignee_id' => fake()->boolean(80) ? fake()->randomElement($userIds) : null,
                    'title' => fake()->randomElement($titles),
                    'status' => $status,
                    'priority' => $priority,
                    'due_at' => fake()->boolean(75)
                        ? fake()->dateTimeBetween($createdAt, (clone $createdAt)->modify('+30 days'))
                        : null,
                    'resolved_at' => $resolvedAt,
                    'closed_at' => $closedAt,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ];
            })
            ->create();
    }
}
