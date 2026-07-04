<?php // backend/tests/Feature/CollectionIndexTest.php
namespace Tests\Feature;

use App\Livewire\CollectionIndex;
use App\Models\{CollectionCategory, CollectionWork};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CollectionIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_renders_component(): void
    {
        $this->withoutVite();
        $this->get('/collection')->assertOk()->assertSeeLivewire(CollectionIndex::class);
    }

    public function test_lists_and_filters_by_type(): void
    {
        CollectionWork::factory()->create(['type' => 'anime', 'title' => 'A']);
        CollectionWork::factory()->create(['type' => 'manga', 'title' => 'M']);

        Livewire::test(CollectionIndex::class)
            ->assertSee('A')->assertSee('M')
            ->set('type', 'anime')
            ->assertSee('A')->assertDontSee('M');
    }

    public function test_search_matches_title_and_original(): void
    {
        CollectionWork::factory()->create(['title' => '進擊的巨人', 'title_original' => 'Shingeki']);
        CollectionWork::factory()->create(['title' => '孤獨搖滾', 'title_original' => 'Bocchi']);

        Livewire::test(CollectionIndex::class)
            ->set('search', 'Shingeki')
            ->assertSee('進擊的巨人')->assertDontSee('孤獨搖滾');
    }

    public function test_category_and_mode_requires_all_selected(): void
    {
        $love = CollectionCategory::factory()->create(['name' => '戀愛']);
        $school = CollectionCategory::factory()->create(['name' => '校園']);
        $both = CollectionWork::factory()->create(['title' => 'Both']);
        $both->categories()->sync([$love->id, $school->id]);
        $one = CollectionWork::factory()->create(['title' => 'One']);
        $one->categories()->sync([$love->id]);

        Livewire::test(CollectionIndex::class)
            ->set('categoryIds', [$love->id, $school->id])
            ->set('categoryMode', 'and')
            ->assertSee('Both')->assertDontSee('One');
    }

    public function test_stats_reflect_data(): void
    {
        CollectionWork::factory()->count(2)->create(['status' => 'completed']);
        CollectionWork::factory()->create(['status' => 'watching', 'is_favorite' => true]);

        Livewire::test(CollectionIndex::class)
            ->assertViewHas('stats', fn ($s) => $s['total'] === 3 && $s['completed'] === 2 && $s['favorites'] === 1);
    }
}
