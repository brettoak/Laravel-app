<?php

namespace App\Livewire;

use App\Models\Article;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class ContentManagement extends Component
{
    use WithPagination;

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public string $title = '';

    public string $content = '';

    public ?int $editingArticleId = null;

    public string $editTitle = '';

    public string $editContent = '';

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->reset(['title', 'content']);
        $this->resetValidation();
    }

    public function create(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'min:10'],
        ]);

        Article::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'content' => $validated['content'],
            'slug' => $this->uniqueSlug($validated['title']),
            'views' => 0,
        ]);

        $this->closeCreateModal();
        $this->resetPage();
        session()->flash('content-created', 'Content was added successfully.');
    }

    public function openEditModal(int $id): void
    {
        $article = Article::findOrFail($id);

        $this->resetValidation();
        $this->editingArticleId = $article->id;
        $this->editTitle = $article->title;
        $this->editContent = $article->content;
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->reset(['editingArticleId', 'editTitle', 'editContent']);
        $this->resetValidation();
    }

    public function update(): void
    {
        $validated = $this->validate([
            'editTitle' => ['required', 'string', 'max:255'],
            'editContent' => ['required', 'string', 'min:10'],
        ]);

        $article = Article::findOrFail($this->editingArticleId);

        $article->update([
            'title' => $validated['editTitle'],
            'content' => $validated['editContent'],
            'slug' => $this->uniqueSlug($validated['editTitle'], $article->id),
        ]);

        $this->closeEditModal();
        session()->flash('content-updated', 'Content was updated successfully.');
    }

    public function delete(int $id): void
    {
        $article = Article::find($id);

        if ($article) {
            $article->delete();
            session()->flash('content-deleted', 'Content was deleted successfully.');
        }
    }

    private function uniqueSlug(string $title, ?int $ignoreArticleId = null): string
    {
        $baseSlug = Str::slug($title) ?: 'content';
        $slug = $baseSlug;
        $suffix = 2;

        while (Article::where('slug', $slug)
            ->when($ignoreArticleId, fn ($query) => $query->whereKeyNot($ignoreArticleId))
            ->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    public function render()
    {
        return view('livewire.content-management', [
            'articles' => Article::with('user')->latest()->paginate(10),
        ]);
    }
}
