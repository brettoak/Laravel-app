<div
    x-data="{ open: $wire.entangle('showTicketDetails').live }"
    x-show="open"
    x-on:keydown.escape.window="if (open) $wire.closeTicket()"
    x-trap.inert.noscroll="open"
    class="fixed inset-0 z-50"
    style="display: none;"
>
    <div class="absolute inset-0 bg-gray-900/50" x-on:click="$wire.closeTicket()" aria-hidden="true"></div>

    <section role="dialog" aria-modal="true" aria-labelledby="ticket-detail-heading" class="absolute inset-y-0 right-0 flex w-full max-w-xl flex-col bg-white shadow-xl dark:bg-gray-800">
        <header class="flex items-center justify-between gap-4 border-b border-gray-200 px-6 py-5 dark:border-gray-700">
            <div>
                <h2 id="ticket-detail-heading" class="text-lg font-semibold text-gray-900 dark:text-white">Ticket details</h2>
                @if ($selectedTicket)
                    <p class="mt-1 text-sm font-medium text-primary-600 dark:text-primary-400">{{ $selectedTicket->ticket_number }}</p>
                @endif
            </div>
            <button type="button" x-on:click="$wire.closeTicket()" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:text-gray-300 dark:hover:bg-gray-700" aria-label="Close ticket details">Close</button>
        </header>

        <div class="min-h-0 flex-1 overflow-y-auto px-6 py-6">
            @if ($selectedTicket)
                <dl class="space-y-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Title</dt>
                        <dd class="mt-2 break-words text-xl font-semibold text-gray-900 dark:text-white">{{ $selectedTicket->title }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                        <dd class="mt-2 whitespace-pre-wrap break-words text-sm leading-6 text-gray-700 dark:text-gray-300">{{ filled($selectedTicket->description) ? $selectedTicket->description : 'No description provided.' }}</dd>
                    </div>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Priority</dt>
                            <dd class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ $priorities[$selectedTicket->priority] ?? Str::headline($selectedTicket->priority) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Assignee</dt>
                            <dd class="mt-2 break-words text-sm text-gray-900 dark:text-white">{{ $selectedTicket->assignee?->name ?? 'Unassigned' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Due date</dt>
                            <dd class="mt-2 text-sm text-gray-900 dark:text-white">
                                @if ($selectedTicket->due_at)
                                    <time datetime="{{ $selectedTicket->due_at->toIso8601String() }}">{{ $selectedTicket->due_at->format('M d, Y H:i T') }}</time>
                                @else
                                    No deadline
                                @endif
                            </dd>
                        </div>
                    </div>
                </dl>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">This ticket is no longer available.</p>
            @endif
        </div>
    </section>
</div>
