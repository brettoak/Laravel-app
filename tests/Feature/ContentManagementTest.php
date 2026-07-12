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
            ->assertSessionHas('content-created', 'Content was added successfully.');

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
}
