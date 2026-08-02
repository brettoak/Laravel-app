<div class="max-w-10xl mx-auto p-6">
    <div class="relative rounded-2xl shadow-2xl backdrop-blur-xl bg-gradient-to-br from-white/95 to-gray-50/95 dark:from-gray-800/95 dark:to-gray-900/95 border border-gray-200/50 dark:border-gray-700/50 p-8">
        <!-- Decorative gradient overlay -->
        <div class="absolute inset-0 overflow-hidden rounded-2xl pointer-events-none -z-10">
            <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-br from-blue-400/10 to-purple-400/10 dark:from-blue-500/20 dark:to-purple-500/20 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-gradient-to-tr from-emerald-400/10 to-teal-400/10 dark:from-emerald-500/20 dark:to-teal-500/20 rounded-full blur-3xl"></div>
        </div>

        <!-- Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Content Library</h2>
                <p class="mt-1 text-gray-500 dark:text-gray-400">Create, review, and manage the articles published on your platform.</p>
            </div>

            <div class="flex gap-3">
                <button
                    type="button"
                    wire:click="openCreateModal"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Content
                </button>
            </div>
        </div>

        <!-- Search -->
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative w-full sm:max-w-md">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.1-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z"></path>
                    </svg>
                </div>
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search title, slug, or content..."
                    aria-label="Search content"
                    class="block w-full rounded-xl border border-gray-200 bg-white/80 py-3 pl-11 pr-11 text-sm text-gray-900 shadow-sm outline-none transition placeholder:text-gray-400 hover:border-gray-300 focus:border-primary-400 focus:ring-4 focus:ring-primary-500/10 dark:border-gray-700 dark:bg-gray-900/70 dark:text-white dark:placeholder:text-gray-500 dark:hover:border-gray-600 dark:focus:border-primary-500 dark:focus:ring-primary-500/15"
                >
                @if ($search !== '')
                    <button
                        type="button"
                        wire:click="clearSearch"
                        title="Clear search"
                        aria-label="Clear search"
                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 transition hover:text-gray-700 focus:outline-none dark:hover:text-gray-200"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                @endif
            </div>

            @if (trim($search) !== '')
                <p class="text-sm text-gray-500 dark:text-gray-400" role="status">
                    <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $articles->total() }}</span>
                    {{ Str::plural('result', $articles->total()) }} for
                    <span class="font-medium text-primary-600 dark:text-primary-400">“{{ trim($search) }}”</span>
                </p>
            @endif
        </div>

        @if (session()->has('content-created') || session()->has('content-updated') || session()->has('content-deleted'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/30 dark:text-green-300" role="status">
                {{ session('content-created') ?? session('content-updated') ?? session('content-deleted') }}
            </div>
        @endif

        <!-- Table Container -->
        <div class="bg-white/50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-inner flex flex-col">
             <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50/50 dark:bg-gray-900/50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Title</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Author</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Views</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created At</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-transparent">
                        @forelse($articles as $article)
                            <tr wire:key="article-{{ $article->id }}" class="hover:bg-blue-50/30 dark:hover:bg-blue-900/20 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $article->title }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $article->slug }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-400 to-indigo-500 flex items-center justify-center text-white font-bold text-xs">
                                            {{ substr($article->user->name ?? 'U', 0, 1) }}
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm text-gray-900 dark:text-white">{{ $article->user->name ?? 'Unknown' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                        {{ number_format($article->views) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $article->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            type="button"
                                            wire:click="openEditModal({{ $article->id }})"
                                            title="Edit {{ $article->title }}"
                                            aria-label="Edit {{ $article->title }}"
                                            class="group inline-flex items-center gap-1.5 rounded-lg border border-primary-200 bg-primary-50 px-3 py-2 text-xs font-semibold text-primary-700 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-primary-300 hover:bg-primary-100 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:border-primary-500/30 dark:bg-primary-500/10 dark:text-primary-300 dark:hover:border-primary-400/50 dark:hover:bg-primary-500/20 dark:focus:ring-offset-gray-900"
                                        >
                                            <svg class="h-4 w-4 transition-transform duration-200 group-hover:rotate-[-6deg]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 3.487 1.651-1.651a2.121 2.121 0 1 1 3 3L10.582 15.768a4.5 4.5 0 0 1-1.897 1.13l-3.19.957.957-3.19a4.5 4.5 0 0 1 1.13-1.897L16.862 3.487ZM16.862 3.487 19.85 6.475M18 14.25V19.5A1.5 1.5 0 0 1 16.5 21h-12A1.5 1.5 0 0 1 3 19.5v-12A1.5 1.5 0 0 1 4.5 6H9.75"></path>
                                            </svg>
                                            Edit
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="delete({{ $article->id }})"
                                            wire:confirm="Are you sure you want to delete this article?"
                                            title="Delete {{ $article->title }}"
                                            aria-label="Delete {{ $article->title }}"
                                            class="group inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-red-300 hover:bg-red-100 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300 dark:hover:border-red-400/50 dark:hover:bg-red-500/20 dark:focus:ring-offset-gray-900"
                                        >
                                            <svg class="h-4 w-4 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673A2.25 2.25 0 0 1 15.916 21H8.084a2.25 2.25 0 0 1-2.244-2.327L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0V4.477c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"></path>
                                            </svg>
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                        </svg>
                                        @if (trim($search) !== '')
                                            <span class="text-lg font-medium">No matching content</span>
                                            <span class="text-sm">Try another title, slug, or keyword.</span>
                                        @else
                                            <span class="text-lg font-medium">No content yet</span>
                                            <span class="text-sm">Select Add Content to publish your first article.</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($articles->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                    {{ $articles->links() }}
                </div>
            @endif
        </div>
    </div>

    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="create-content-title">
            <button type="button" wire:click="closeCreateModal" class="absolute inset-0 bg-gray-950/60 backdrop-blur-sm" aria-label="Close add content form"></button>

            <div class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800 sm:p-8">
                <div class="mb-6">
                    <h3 id="create-content-title" class="text-xl font-semibold text-gray-900 dark:text-white">Add Content</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter the article details below. A URL slug will be generated automatically.</p>
                </div>

                <form wire:submit="create" class="space-y-5">
                    <div>
                        <label for="content-title" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Title</label>
                        <input
                            id="content-title"
                            type="text"
                            wire:model="title"
                            maxlength="255"
                            autofocus
                            placeholder="Enter a clear, descriptive title"
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-3 text-gray-900 shadow-sm outline-none transition duration-200 placeholder:text-gray-400 hover:border-gray-300 hover:bg-white focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-0 focus:shadow-[0_0_0_3px_rgba(99,102,241,0.10)] dark:border-gray-700 dark:bg-gray-900/70 dark:text-white dark:placeholder:text-gray-500 dark:hover:border-gray-600 dark:hover:bg-gray-900 dark:focus:border-primary-500 dark:focus:bg-gray-900 dark:focus:shadow-[0_0_0_3px_rgba(99,102,241,0.16)]"
                        >
                        @error('title') <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="content-body" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Content</label>
                        <textarea
                            id="content-body"
                            wire:model="content"
                            rows="9"
                            placeholder="Write the article content here"
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-3 text-gray-900 shadow-sm outline-none transition duration-200 placeholder:text-gray-400 hover:border-gray-300 hover:bg-white focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-0 focus:shadow-[0_0_0_3px_rgba(99,102,241,0.10)] dark:border-gray-700 dark:bg-gray-900/70 dark:text-white dark:placeholder:text-gray-500 dark:hover:border-gray-600 dark:hover:bg-gray-900 dark:focus:border-primary-500 dark:focus:bg-gray-900 dark:focus:shadow-[0_0_0_3px_rgba(99,102,241,0.16)]"
                        ></textarea>
                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Content must be at least 10 characters.</p>
                        @error('content') <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-3 border-t border-gray-200 pt-5 dark:border-gray-700">
                        <button type="button" wire:click="closeCreateModal" class="rounded-lg px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="create" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500 disabled:cursor-wait disabled:opacity-60">
                            <span wire:loading.remove wire:target="create">Add Content</span>
                            <span wire:loading wire:target="create">Adding...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="edit-content-title">
            <button type="button" wire:click="closeEditModal" class="absolute inset-0 bg-gray-950/60 backdrop-blur-sm" aria-label="Close edit content form"></button>

            <div class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800 sm:p-8">
                <div class="mb-6">
                    <h3 id="edit-content-title" class="text-xl font-semibold text-gray-900 dark:text-white">Edit Content</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Update the article details below. The URL slug will follow the title automatically.</p>
                </div>

                <form wire:submit="update" class="space-y-5">
                    <div>
                        <label for="edit-content-title-input" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Title</label>
                        <input
                            id="edit-content-title-input"
                            type="text"
                            wire:model="editTitle"
                            maxlength="255"
                            autofocus
                            placeholder="Enter a clear, descriptive title"
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-3 text-gray-900 shadow-sm outline-none transition duration-200 placeholder:text-gray-400 hover:border-gray-300 hover:bg-white focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-0 focus:shadow-[0_0_0_3px_rgba(99,102,241,0.10)] dark:border-gray-700 dark:bg-gray-900/70 dark:text-white dark:placeholder:text-gray-500 dark:hover:border-gray-600 dark:hover:bg-gray-900 dark:focus:border-primary-500 dark:focus:bg-gray-900 dark:focus:shadow-[0_0_0_3px_rgba(99,102,241,0.16)]"
                        >
                        @error('editTitle') <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="edit-content-body" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Content</label>
                        <textarea
                            id="edit-content-body"
                            wire:model="editContent"
                            rows="9"
                            placeholder="Write the article content here"
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-3 text-gray-900 shadow-sm outline-none transition duration-200 placeholder:text-gray-400 hover:border-gray-300 hover:bg-white focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-0 focus:shadow-[0_0_0_3px_rgba(99,102,241,0.10)] dark:border-gray-700 dark:bg-gray-900/70 dark:text-white dark:placeholder:text-gray-500 dark:hover:border-gray-600 dark:hover:bg-gray-900 dark:focus:border-primary-500 dark:focus:bg-gray-900 dark:focus:shadow-[0_0_0_3px_rgba(99,102,241,0.16)]"
                        ></textarea>
                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Content must be at least 10 characters.</p>
                        @error('editContent') <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-3 border-t border-gray-200 pt-5 dark:border-gray-700">
                        <button type="button" wire:click="closeEditModal" class="rounded-lg px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="update" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500 disabled:cursor-wait disabled:opacity-60">
                            <span wire:loading.remove wire:target="update">Save Changes</span>
                            <span wire:loading wire:target="update">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
