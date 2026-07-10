<?php // backend/tests/Feature/VocabularyFormTest.php
namespace Tests\Feature;

use App\Livewire\VocabularyForm;
use App\Models\{StudyVocabulary, User};
use App\Services\VocabularyLookup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class VocabularyFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_lookup_prefills_fields(): void
    {
        Http::fake([
            'api.dictionaryapi.dev/*' => Http::response([[
                'phonetic' => '/wɜːrd/', 'phonetics' => [['audio' => 'https://a/word.mp3']],
                'meanings' => [['partOfSpeech' => 'noun', 'definitions' => [['example' => 'a word']]]],
            ]], 200),
            'api.mymemory.translated.net/*' => Http::response(['responseData' => ['translatedText' => '單字']], 200),
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)->test(VocabularyForm::class)
            ->set('word', 'word')->call('lookup')
            ->assertSet('meaning', '單字')->assertSet('part_of_speech', 'noun')
            ->assertSet('auto_filled', true);
    }

    public function test_admin_can_save_new_word(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Livewire::actingAs($admin)->test(VocabularyForm::class)
            ->set('word', 'serendipity')->set('meaning', '機緣')
            ->call('save')->assertHasNoErrors()->assertDispatched('vocabulary-saved');
        $this->assertDatabaseHas('study_vocabulary', ['word' => 'serendipity']);
        $this->assertNotNull(StudyVocabulary::first()->next_review_at); // scheduled +1 day
    }

    public function test_duplicate_word_is_flagged(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        StudyVocabulary::factory()->create(['word' => 'dup', 'meaning' => 'a']);
        Livewire::actingAs($admin)->test(VocabularyForm::class)
            ->set('word', 'dup')->set('meaning', 'b')
            ->call('save')->assertHasErrors('word');
    }

    public function test_non_admin_cannot_save(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Livewire::actingAs($user)->test(VocabularyForm::class)
            ->set('word','x')->set('meaning','y')->call('save')->assertForbidden();
    }
}
