<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $today }}</p>
                    <h2 class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">Welcome back, {{ filament()->getUserName(filament()->auth()->user()) }}</h2>
                </div>

                <x-filament::badge color="primary">
                    Workspace
                </x-filament::badge>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($shortcuts as $shortcut)
                    <a
                        href="{{ $shortcut['url'] }}"
                        class="group rounded-lg border border-gray-200 bg-white p-4 transition hover:border-primary-500 hover:bg-primary-50/40 dark:border-gray-700 dark:bg-gray-900 dark:hover:border-primary-500 dark:hover:bg-primary-950/20"
                    >
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-600 transition group-hover:bg-primary-100 group-hover:text-primary-600 dark:bg-gray-800 dark:text-gray-300 dark:group-hover:bg-primary-950 dark:group-hover:text-primary-400">
                                <x-filament::icon
                                    :icon="$shortcut['icon']"
                                    class="h-5 w-5"
                                />
                            </div>

                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-950 dark:text-white">{{ $shortcut['label'] }}</p>
                                <p class="mt-1 text-sm leading-5 text-gray-500 dark:text-gray-400">{{ $shortcut['description'] }}</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/60">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Queue status</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Current database queue snapshot.</p>
                    </div>

                    <x-filament::icon
                        icon="heroicon-o-command-line"
                        class="h-5 w-5 text-gray-400"
                    />
                </div>

                <div class="mt-4 grid grid-cols-3 gap-3">
                    <div>
                        <p class="text-xl font-semibold text-gray-950 dark:text-white">{{ number_format($queueStatus['pending']) }}</p>
                        <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Pending</p>
                    </div>

                    <div>
                        <p class="text-xl font-semibold {{ $queueStatus['failed'] > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-gray-950 dark:text-white' }}">{{ number_format($queueStatus['failed']) }}</p>
                        <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Failed</p>
                    </div>

                    <div>
                        <p class="text-xl font-semibold text-gray-950 dark:text-white">{{ number_format($queueStatus['batches']) }}</p>
                        <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Batches</p>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
