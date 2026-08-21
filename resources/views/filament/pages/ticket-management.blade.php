<x-filament-panels::page>
    @push('scripts')
        @vite(['resources/js/app.js'])
    @endpush

    @livewire('ticket-list')
</x-filament-panels::page>
