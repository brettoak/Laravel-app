<?php

namespace Tests\Feature;

use App\Livewire\ContentManagement;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_add_content(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ContentManagement::class)
            ->set('title', 'A New Article')
            ->set('content', 'This is the body of the new article.')
            ->call('create')
            ->assertHasNoErrors()
            ->assertSet('showCreateModal', false)
            ->assertSee('Content was added successfully.');

        $this->assertDatabaseHas('articles', [
            'user_id' => $user->id,
            'title' => 'A New Article',
            'content' => 'This is the body of the new article.',
            'slug' => 'a-new-article',
            'views' => 0,
        ]);
    }

    public function test_content_requires_a_title_and_body(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(ContentManagement::class)
            ->set('title', '')
            ->set('content', 'Short')
            ->call('create')
            ->assertHasErrors(['title' => 'required', 'content' => 'min']);
    }

    public function test_duplicate_titles_receive_unique_slugs(): void
    {
        $user = User::factory()->create();
        Article::create([
            'user_id' => $user->id,
            'title' => 'Release Notes',
            'content' => 'The original release notes.',
            'slug' => 'release-notes',
        ]);

        Livewire::actingAs($user)
            ->test(ContentManagement::class)
            ->set('title', 'Release Notes')
            ->set('content', 'The latest release notes are here.')
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('articles', ['slug' => 'release-notes-2']);
    }

    public function test_edit_modal_is_prefilled_with_article_content(): void
    {
        $article = Article::factory()->create([
            'title' => 'Original title',
            'content' => 'Original article content.',
        ]);

        Livewire::actingAs(User::factory()->create())
            ->test(ContentManagement::class)
            ->call('openEditModal', $article->id)
            ->assertSet('showEditModal', true)
            ->assertSet('editingArticleId', $article->id)
            ->assertSet('editTitle', 'Original title')
            ->assertSet('editContent', 'Original article content.');
    }

    public function test_authenticated_user_can_edit_content(): void
    {
        $article = Article::factory()->create([
            'title' => 'Original title',
            'content' => 'Original article content.',
            'slug' => 'original-title',
            'views' => 42,
        ]);

        Livewire::actingAs(User::factory()->create())
            ->test(ContentManagement::class)
            ->call('openEditModal', $article->id)
            ->set('editTitle', 'Updated title')
            ->set('editContent', 'This article has updated content.')
            ->call('update')
            ->assertHasNoErrors()
            ->assertSet('showEditModal', false)
            ->assertSee('Content was updated successfully.');

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'title' => 'Updated title',
            'content' => 'This article has updated content.',
            'slug' => 'updated-title',
            'views' => 42,
        ]);
    }

    public function test_edited_content_requires_a_title_and_body(): void
    {
        $article = Article::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(ContentManagement::class)
            ->call('openEditModal', $article->id)
            ->set('editTitle', '')
            ->set('editContent', 'Short')
            ->call('update')
            ->assertHasErrors(['editTitle' => 'required', 'editContent' => 'min']);
    }

    public function test_editing_a_title_keeps_the_slug_unique(): void
    {
        $user = User::factory()->create();
        Article::factory()->for($user)->create(['slug' => 'release-notes']);
        $article = Article::factory()->for($user)->create(['slug' => 'draft']);

        Livewire::actingAs($user)
            ->test(ContentManagement::class)
            ->call('openEditModal', $article->id)
            ->set('editTitle', 'Release Notes')
            ->set('editContent', 'The updated release notes are here.')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'slug' => 'release-notes-2',
        ]);
    }
}
