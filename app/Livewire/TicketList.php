<?php

namespace App\Livewire;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TicketList extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $priority = '';

    #[Url(except: '')]
    public string $assignee = '';

    #[Url(except: 'updated_at')]
    public string $sortField = 'updated_at';

    #[Url(except: 'desc')]
    public string $sortDirection = 'desc';

    #[Url(except: 10)]
    public int $perPage = 10;

    private const SORTABLE_FIELDS = [
        'ticket_number',
        'title',
        'status',
        'priority',
        'due_at',
        'updated_at',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedPriority(): void
    {
        $this->resetPage();
    }

    public function updatedAssignee(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, self::SORTABLE_FIELDS, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'priority', 'assignee']);
        $this->resetPage();
    }

    public function render(): View
    {
        $perPage = in_array($this->perPage, [10, 25, 50], true) ? $this->perPage : 10;

        return view('livewire.ticket-list', [
            'tickets' => $this->ticketsQuery()->paginate($perPage),
            'assignees' => User::query()
                ->whereHas('assignedTickets')
                ->orderBy('name')
                ->get(['id', 'name']),
            'statuses' => Ticket::STATUSES,
            'priorities' => Ticket::PRIORITIES,
        ]);
    }

    private function ticketsQuery(): Builder
    {
        $search = trim($this->search);
        $sortField = in_array($this->sortField, self::SORTABLE_FIELDS, true)
            ? $this->sortField
            : 'updated_at';
        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return Ticket::query()
            ->with(['requester:id,name', 'assignee:id,name'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('ticket_number', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('requester', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('assignee', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when(array_key_exists($this->status, Ticket::STATUSES), fn (Builder $query) => $query->where('status', $this->status))
            ->when(array_key_exists($this->priority, Ticket::PRIORITIES), fn (Builder $query) => $query->where('priority', $this->priority))
            ->when(ctype_digit($this->assignee), fn (Builder $query) => $query->where('assignee_id', (int) $this->assignee))
            ->orderBy($sortField, $sortDirection)
            ->orderBy('id', $sortDirection);
    }
}
