<?php

namespace Tests\Feature;

use App\Exports\ContentExport;
use App\Livewire\ContentManagement;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
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

    public function test_article_content_can_be_viewed_and_the_modal_can_be_closed(): void
    {
        $author = User::factory()->create(['name' => 'Article Author']);
        $article = Article::factory()->for($author)->create([
            'title' => 'A detailed article',
            'content' => "The complete article body.\nIt keeps its line breaks.",
            'slug' => 'a-detailed-article',
            'views' => 1234,
        ]);

        Livewire::actingAs(User::factory()->create())
            ->test(ContentManagement::class)
            ->call('openViewModal', $article->id)
            ->assertSet('showViewModal', true)
            ->assertSet('viewingArticleId', $article->id)
            ->assertSet('viewTitle', 'A detailed article')
            ->assertSet('viewContent', "The complete article body.\nIt keeps its line breaks.")
            ->assertSet('viewSlug', 'a-detailed-article')
            ->assertSet('viewAuthor', 'Article Author')
            ->assertSet('viewViews', 1234)
            ->assertSee('The complete article body.')
            ->assertSee('Article Author')
            ->assertSee('1,234')
            ->call('closeViewModal')
            ->assertSet('showViewModal', false)
            ->assertSet('viewingArticleId', null)
            ->assertSet('viewContent', '');
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

    public function test_content_can_be_searched_by_title_slug_or_body(): void
    {
        $user = User::factory()->create();

        Article::factory()->for($user)->create([
            'title' => 'Launch Plan',
            'slug' => 'product-roadmap',
            'content' => 'The release timeline is ready.',
        ]);
        Article::factory()->for($user)->create([
            'title' => 'Marketing Notes',
            'slug' => 'launch-campaign',
            'content' => 'Campaign details are ready.',
        ]);
        Article::factory()->for($user)->create([
            'title' => 'Security Brief',
            'slug' => 'security-brief',
            'content' => 'Checklist for launch day.',
        ]);
        Article::factory()->for($user)->create([
            'title' => 'Unrelated Article',
            'slug' => 'unrelated-article',
            'content' => 'This entry should be hidden.',
        ]);

        Livewire::actingAs($user)
            ->test(ContentManagement::class)
            ->set('search', 'launch')
            ->assertSee('Launch Plan')
            ->assertSee('Marketing Notes')
            ->assertSee('Security Brief')
            ->assertDontSee('Unrelated Article');
    }

    public function test_search_can_be_cleared(): void
    {
        $user = User::factory()->create();
        Article::factory()->for($user)->create(['title' => 'Visible Article']);

        Livewire::actingAs($user)
            ->test(ContentManagement::class)
            ->set('search', 'missing')
            ->assertSee('No matching content')
            ->call('clearSearch')
            ->assertSet('search', '')
            ->assertSee('Visible Article');
    }

    public function test_content_can_be_exported_to_excel_using_the_current_search(): void
    {
        Carbon::setTestNow('2026-08-19 14:30:45');
        $path = null;

        try {
            $user = User::factory()->create(['name' => 'Export Author']);

            Article::factory()->for($user)->create([
                'title' => 'Export this article',
                'slug' => 'export-this-article',
                'content' => 'Matching export content.',
                'views' => 25,
            ]);
            Article::factory()->for($user)->create(['title' => 'Leave this article out']);

            $component = Livewire::actingAs($user)
                ->test(ContentManagement::class)
                ->set('search', 'Matching export')
                ->call('export')
                ->assertFileDownloaded(
                    'content-export-2026-08-19-143045.xlsx',
                    contentType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                );

            $temporaryPath = tempnam(sys_get_temp_dir(), 'downloaded-content-');
            $path = $temporaryPath.'.xlsx';
            rename($temporaryPath, $path);

            $download = base64_decode(data_get($component->effects, 'download.content'), true);
            $this->assertNotFalse($download);
            file_put_contents($path, $download);

            $archive = new \PharData($path);
            $sheet = $archive['xl/worksheets/sheet1.xml']->getContent();

            $this->assertStringContainsString('Export this article', $sheet);
            $this->assertStringNotContainsString('Leave this article out', $sheet);
        } finally {
            unset($archive);

            if ($path !== null) {
                @unlink($path);
            }

            Carbon::setTestNow();
        }
    }

    public function test_excel_export_contains_article_data_and_workbook_formatting(): void
    {
        $author = User::factory()->create(['name' => 'Spreadsheet Author']);
        $article = Article::factory()->for($author)->create([
            'title' => 'Quarterly <Review>',
            'slug' => 'quarterly-review',
            'content' => "A detailed report.\nSecond line.",
            'views' => 1200,
        ]);

        $path = app(ContentExport::class)->create(collect([$article->load('user')]));

        try {
            $archive = new \PharData($path);
            $sheet = $archive['xl/worksheets/sheet1.xml']->getContent();

            $this->assertStringContainsString('name="Content"', $archive['xl/workbook.xml']->getContent());
            $this->assertStringContainsString('state="frozen"', $sheet);
            $this->assertStringContainsString('<autoFilter ref="A1:H2"/>', $sheet);
            $this->assertStringContainsString('Quarterly &lt;Review&gt;', $sheet);
            $this->assertStringContainsString('Spreadsheet Author', $sheet);
            $this->assertStringContainsString('A detailed report.', $sheet);
            $this->assertStringContainsString('<v>1200</v>', $sheet);
        } finally {
            unset($archive);
            @unlink($path);
        }
    }
}
