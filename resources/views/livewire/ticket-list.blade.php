<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 px-6 py-6 dark:border-gray-700">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Ticket management</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Search, filter and prioritise customer requests.</p>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400" aria-live="polite">
                    <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($tickets->total()) }}</span>
                    {{ Str::plural('ticket', $tickets->total()) }}
                </p>
            </div>

            <div class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-12">
                <div class="relative xl:col-span-4">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.1-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
                        </svg>
                    </div>
                    <input
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search number, title or person..."
                        aria-label="Search tickets"
                        class="block w-full rounded-lg border-gray-300 py-2.5 pl-10 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500"
                    >
                </div>

                <select wire:model.live="status" aria-label="Filter by status" class="rounded-lg border-gray-300 py-2.5 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white xl:col-span-2">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

                <select wire:model.live="priority" aria-label="Filter by priority" class="rounded-lg border-gray-300 py-2.5 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white xl:col-span-2">
                    <option value="">All priorities</option>
                    @foreach ($priorities as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

                <select wire:model.live="assignee" aria-label="Filter by assignee" class="rounded-lg border-gray-300 py-2.5 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white xl:col-span-2">
                    <option value="">All assignees</option>
                    @foreach ($assignees as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>

                <div class="flex gap-2 xl:col-span-2">
                    <select wire:model.live="perPage" aria-label="Tickets per page" class="min-w-0 flex-1 rounded-lg border-gray-300 py-2.5 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                        <option value="10">10 / page</option>
                        <option value="25">25 / page</option>
                        <option value="50">50 / page</option>
                    </select>

                    @if (trim($search) !== '' || $status !== '' || $priority !== '' || $assignee !== '')
                        <button type="button" wire:click="clearFilters" class="rounded-lg border border-gray-300 px-3 text-sm font-medium text-gray-600 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700" title="Clear filters">
                            Clear
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        @php
                            $columns = [
                                'ticket_number' => 'Ticket',
                                'title' => 'Subject',
                                'status' => 'Status',
                                'priority' => 'Priority',
                                'due_at' => 'Due date',
                                'updated_at' => 'Updated',
                            ];
                        @endphp

                        @foreach ($columns as $field => $label)
                            <th scope="col" @class([
                                'px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400',
                                'min-w-80' => $field === 'title',
                            ])>
                                <button type="button" wire:click="sortBy('{{ $field }}')" class="group inline-flex items-center gap-1.5 hover:text-gray-900 focus:outline-none dark:hover:text-white">
                                    {{ $label }}
                                    <span @class([
                                        'text-gray-300 group-hover:text-gray-500 dark:text-gray-600',
                                        '!text-primary-600 dark:!text-primary-400' => $sortField === $field,
                                    ]) aria-hidden="true">
                                        @if ($sortField === $field)
                                            {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                        @else
                                            ↕
                                        @endif
                                    </span>
                                </button>
                            </th>
                        @endforeach

                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Requester</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Assignee</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    @forelse ($tickets as $ticket)
                        <tr wire:key="ticket-{{ $ticket->id }}" class="transition hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-primary-600 dark:text-primary-400">
                                {{ $ticket->ticket_number }}
                            </td>
                            <td class="px-6 py-4">
                                <p class="max-w-sm truncate text-sm font-medium text-gray-900 dark:text-white" title="{{ $ticket->title }}">{{ $ticket->title }}</p>
                                <p class="mt-1 max-w-sm truncate text-xs text-gray-500 dark:text-gray-400">{{ $ticket->description }}</p>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span @class([
                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                                    'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300' => $ticket->status === 'open',
                                    'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300' => $ticket->status === 'in_progress',
                                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' => $ticket->status === 'resolved',
                                    'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' => $ticket->status === 'closed',
                                ])>
                                    {{ $statuses[$ticket->status] ?? Str::headline($ticket->status) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span @class([
                                    'inline-flex items-center gap-1.5 text-sm font-medium',
                                    'text-gray-500 dark:text-gray-400' => $ticket->priority === 'low',
                                    'text-blue-600 dark:text-blue-400' => $ticket->priority === 'medium',
                                    'text-orange-600 dark:text-orange-400' => $ticket->priority === 'high',
                                    'text-red-600 dark:text-red-400' => $ticket->priority === 'urgent',
                                ])>
                                    <span class="h-2 w-2 rounded-full bg-current"></span>
                                    {{ $priorities[$ticket->priority] ?? Str::headline($ticket->priority) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                @if ($ticket->due_at)
                                    <span @class(['font-semibold text-red-600 dark:text-red-400' => $ticket->due_at->isPast() && ! in_array($ticket->status, ['resolved', 'closed'], true)])>
                                        {{ $ticket->due_at->format('M d, Y') }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400" title="{{ $ticket->updated_at->format('Y-m-d H:i:s') }}">
                                {{ $ticket->updated_at->diffForHumans() }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ $ticket->requester?->name ?? 'Deleted user' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                @if ($ticket->assignee)
                                    <span class="inline-flex items-center gap-2">
                                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-primary-100 text-xs font-bold text-primary-700 dark:bg-primary-500/20 dark:text-primary-300">
                                            {{ Str::upper(Str::substr($ticket->assignee->name, 0, 1)) }}
                                        </span>
                                        {{ $ticket->assignee->name }}
                                    </span>
                                @else
                                    <span class="text-gray-400">Unassigned</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-700">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </div>
                                <h3 class="mt-3 text-sm font-semibold text-gray-900 dark:text-white">No matching tickets</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try changing or clearing the current filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($tickets->hasPages())
            <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>
