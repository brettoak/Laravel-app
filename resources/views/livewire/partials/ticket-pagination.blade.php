<nav aria-label="Ticket pagination" class="flex min-w-0 flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-gray-500 dark:text-gray-400" role="status" aria-live="polite" aria-atomic="true">
        Page <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($paginator->currentPage()) }}</span>
        of <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($paginator->lastPage()) }}</span>
    </p>

    <div class="flex min-w-0 flex-wrap items-center gap-1">
        <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" @disabled($paginator->onFirstPage()) class="inline-flex h-10 shrink-0 items-center gap-1 rounded-md border border-gray-300 px-3 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
            <x-heroicon-o-chevron-left class="h-4 w-4" aria-hidden="true" />
            Previous
        </button>

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="inline-flex h-10 w-8 shrink-0 items-center justify-center text-sm text-gray-400" aria-hidden="true">{{ $element }}</span>
            @else
                @foreach ($element as $page => $url)
                    @if ($page === $paginator->currentPage())
                        <span wire:key="ticket-page-{{ $page }}" aria-current="page" aria-label="Page {{ $page }}" class="inline-flex h-10 min-w-10 shrink-0 items-center justify-center rounded-md border border-primary-600 bg-primary-600 px-2 text-sm font-semibold text-white">{{ $page }}</span>
                    @else
                        <button wire:key="ticket-page-{{ $page }}" type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" aria-label="Go to page {{ $page }}" class="inline-flex h-10 min-w-10 shrink-0 items-center justify-center rounded-md border border-gray-300 px-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:opacity-40 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">{{ $page }}</button>
                    @endif
                @endforeach
            @endif
        @endforeach

        <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" @disabled(! $paginator->hasMorePages()) class="inline-flex h-10 shrink-0 items-center gap-1 rounded-md border border-gray-300 px-3 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
            Next
            <x-heroicon-o-chevron-right class="h-4 w-4" aria-hidden="true" />
        </button>
    </div>
</nav>
