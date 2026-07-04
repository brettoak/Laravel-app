<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Recent articles</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Latest content activity in the workspace.</p>
                </div>

                <x-filament::icon
                    icon="heroicon-o-clock"
                    class="h-5 w-5 text-gray-400"
                />
            </div>

            @if ($articles->isNotEmpty())
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($articles as $article)
                        <div class="py-4 first:pt-0 last:pb-0">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <h3 class="truncate text-sm font-semibold text-gray-950 dark:text-white">{{ $article->title }}</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $article->user?->name ?? 'Unknown author' }} · {{ $article->updated_at?->diffForHumans() }}
                                    </p>
                                </div>

                                <x-filament::badge color="gray">
                                    {{ number_format($article->views) }} views
                                </x-filament::badge>
                            </div>

                            <div class="mt-3 flex items-center gap-4 text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                                <span>{{ number_format($article->comments_count) }} comments</span>
                                <span>{{ $article->created_at?->format('M j, Y') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-lg border border-dashed border-gray-300 p-6 text-center dark:border-gray-700">
                    <x-filament::icon
                        icon="heroicon-o-document-plus"
                        class="mx-auto h-8 w-8 text-gray-400"
                    />
                    <h3 class="mt-3 text-sm font-semibold text-gray-950 dark:text-white">No articles yet</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">New content will appear here once it is created.</p>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
