<?php // backend/tests/Feature/VocabularyIndexTest.php
namespace Tests\Feature;

use App\Livewire\VocabularyIndex;
use App\Models\{StudyVocabulary, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VocabularyIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_renders(): void
    {
        $this->withoutVite();
        $this->get('/vocabulary')->assertOk()->assertSeeLivewire(VocabularyIndex::class);
    }

    public function test_search_and_familiarity_filters_combine_correctly(): void
    {
        // Regression for the where()->orWhere() grouping bug in the old API.
        StudyVocabulary::factory()->create(['word' => 'alpha', 'meaning' => 'x', 'familiarity' => 5]);
        StudyVocabulary::factory()->create(['word' => 'alphabet', 'meaning' => 'y', 'familiarity' => 1]);
        StudyVocabulary::factory()->create(['word' => 'beta', 'meaning' => 'alpha soup', 'familiarity' => 5]);

        Livewire::test(VocabularyIndex::class)
            ->set('search', 'alpha')     // matches alpha, alphabet, beta(meaning)
            ->set('familiarity', 5)      // must AND with search -> alpha, beta
            ->assertSee('alpha')->assertSee('beta')->assertDontSee('alphabet');
    }

    public function test_add_tab_hidden_from_non_admins(): void
    {
        // The add tab/form is admin-only (writes are policy-gated); guests must not see it.
        // escape=false so the raw wire:click attribute is matched literally.
        Livewire::test(VocabularyIndex::class)->assertDontSee("setMode('add')", false);
    }

    public function test_add_tab_visible_to_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Livewire::actingAs($admin)->test(VocabularyIndex::class)->assertSee("setMode('add')", false);
    }
}
