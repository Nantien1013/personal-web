<?php // backend/tests/Feature/FlashcardTest.php
namespace Tests\Feature;

use App\Livewire\Flashcard;
use App\Models\{StudyVocabulary, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class FlashcardTest extends TestCase
{
    use RefreshDatabase;

    public function test_loads_due_queue_only(): void
    {
        StudyVocabulary::factory()->create(['word' => 'due', 'next_review_at' => now()->subDay()]);
        StudyVocabulary::factory()->create(['word' => 'later', 'next_review_at' => now()->addWeek()]);
        Livewire::test(Flashcard::class)->assertSet('queue.0.word', 'due')->assertCount('queue', 1);
    }

    public function test_admin_rating_updates_schedule_and_advances(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $w = StudyVocabulary::factory()->create(['word' => 'x', 'familiarity' => 1, 'next_review_at' => now()->subDay()]);

        Livewire::actingAs($admin)->test(Flashcard::class)
            ->call('rate', 'remembered')
            ->assertSet('reviewedToday', 1);

        $w->refresh();
        $this->assertSame(2, $w->familiarity);
        $this->assertTrue($w->next_review_at->greaterThan(Carbon::now()));
        $this->assertSame(1, $w->review_count);
        $this->assertSame(1, $w->correct_count);
    }

    public function test_non_admin_cannot_rate(): void
    {
        StudyVocabulary::factory()->create(['next_review_at' => now()->subDay()]);
        Livewire::test(Flashcard::class)->call('rate', 'remembered')->assertForbidden();
    }
}
