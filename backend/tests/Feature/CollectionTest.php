<?php

namespace Tests\Feature;

use App\Models\CollectionCategory;
use App\Models\CollectionWork;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionTest extends TestCase
{
    use RefreshDatabase;

    private function adminToken(): string
    {
        $user = User::factory()->create(['role' => 'admin']);
        return $user->createToken('test')->plainTextToken;
    }

    public function test_can_list_collection(): void
    {
        CollectionWork::factory()->count(3)->create();

        $response = $this->getJson('/api/collection');

        $response->assertOk()->assertJsonCount(3);
    }

    public function test_can_filter_by_type(): void
    {
        CollectionWork::factory()->create(['type' => 'anime']);
        CollectionWork::factory()->create(['type' => 'manga']);

        $response = $this->getJson('/api/collection?type=anime');

        $response->assertOk()->assertJsonCount(1);
    }

    public function test_can_get_stats(): void
    {
        CollectionWork::factory()->count(2)->create(['status' => 'completed']);
        CollectionWork::factory()->create(['status' => 'watching']);

        $response = $this->getJson('/api/collection/stats');

        $response->assertOk()
                 ->assertJsonPath('total', 3)
                 ->assertJsonPath('completed', 2)
                 ->assertJsonPath('watching', 1);
    }

    public function test_admin_can_create_work(): void
    {
        $token = $this->adminToken();
        $category = CollectionCategory::factory()->create();

        $response = $this->withToken($token)->postJson('/api/collection', [
            'type' => 'anime',
            'title' => '進擊的巨人',
            'status' => 'completed',
            'category_ids' => [$category->id],
        ]);

        $response->assertCreated()
                 ->assertJsonPath('title', '進擊的巨人')
                 ->assertJsonCount(1, 'categories');
    }

    public function test_admin_can_update_work(): void
    {
        $token = $this->adminToken();
        $work = CollectionWork::factory()->create(['title' => 'Old Title']);

        $response = $this->withToken($token)->putJson("/api/collection/{$work->id}", [
            'title' => 'New Title',
        ]);

        $response->assertOk()->assertJsonPath('title', 'New Title');
    }

    public function test_admin_can_delete_work(): void
    {
        $token = $this->adminToken();
        $work = CollectionWork::factory()->create();

        $response = $this->withToken($token)->deleteJson("/api/collection/{$work->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('collection_works', ['id' => $work->id]);
    }

    public function test_unauthenticated_cannot_create_work(): void
    {
        $response = $this->postJson('/api/collection', [
            'type' => 'anime',
            'title' => 'Test',
            'status' => 'plan',
        ]);

        $response->assertStatus(401);
    }
}
