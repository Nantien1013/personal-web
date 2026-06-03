<?php

namespace Tests\Feature;

use App\Models\StudyVocabulary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VocabularyTest extends TestCase
{
    use RefreshDatabase;

    private function adminToken(): string
    {
        $user = User::factory()->create(['role' => 'admin']);
        return $user->createToken('test')->plainTextToken;
    }

    public function test_can_list_vocabulary(): void
    {
        StudyVocabulary::factory()->count(3)->create();

        $response = $this->getJson('/api/vocabulary');

        $response->assertOk()->assertJsonCount(3);
    }

    public function test_can_get_stats(): void
    {
        StudyVocabulary::factory()->count(2)->create();

        $response = $this->getJson('/api/vocabulary/stats');

        $response->assertOk()
                 ->assertJsonPath('total', 2)
                 ->assertJsonStructure(['total', 'added_this_week', 'pending_review', 'avg_familiarity']);
    }

    public function test_admin_can_create_vocabulary(): void
    {
        $token = $this->adminToken();

        $response = $this->withToken($token)->postJson('/api/vocabulary', [
            'word'    => 'ephemeral',
            'meaning' => '短暫的',
        ]);

        $response->assertCreated()->assertJsonPath('word', 'ephemeral');
        $this->assertDatabaseHas('study_vocabulary', ['word' => 'ephemeral']);
    }

    public function test_duplicate_word_returns_422(): void
    {
        $token = $this->adminToken();
        StudyVocabulary::factory()->create(['word' => 'ephemeral']);

        $response = $this->withToken($token)->postJson('/api/vocabulary', [
            'word'    => 'ephemeral',
            'meaning' => '短暫的',
        ]);

        $response->assertStatus(422);
    }

    public function test_review_forgot_decreases_familiarity(): void
    {
        $token = $this->adminToken();
        $word = StudyVocabulary::factory()->create([
            'familiarity'      => 3,
            'last_reviewed_at' => now()->subDays(2),
            'next_review_at'   => now()->subDay(),
        ]);

        $response = $this->withToken($token)->putJson("/api/vocabulary/{$word->id}/review", [
            'result' => 'forgot',
        ]);

        $response->assertOk()->assertJsonPath('familiarity', 2);
    }

    public function test_review_mastered_increases_familiarity(): void
    {
        $token = $this->adminToken();
        $word = StudyVocabulary::factory()->create([
            'familiarity'      => 2,
            'last_reviewed_at' => now()->subDays(3),
            'next_review_at'   => now()->subDay(),
        ]);

        $response = $this->withToken($token)->putJson("/api/vocabulary/{$word->id}/review", [
            'result' => 'mastered',
        ]);

        $response->assertOk()->assertJsonPath('familiarity', 3);
    }

    public function test_can_get_review_queue(): void
    {
        StudyVocabulary::factory()->create(['next_review_at' => now()->subHour()]);
        StudyVocabulary::factory()->create(['next_review_at' => now()->addDays(5)]);

        $response = $this->getJson('/api/vocabulary/review-queue');

        $response->assertOk()->assertJsonCount(1);
    }
}
