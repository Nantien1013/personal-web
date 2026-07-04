<?php // backend/tests/Feature/CollectionFormTest.php
namespace Tests\Feature;

use App\Livewire\CollectionForm;
use App\Models\{CollectionCategory, CollectionWork, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CollectionFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_work_with_categories(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cat = CollectionCategory::factory()->create();

        Livewire::actingAs($admin)->test(CollectionForm::class)
            ->set('type', 'anime')->set('title', '進擊的巨人')->set('status', 'completed')
            ->set('categoryIds', [$cat->id])
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('collection-saved');

        $this->assertDatabaseHas('collection_works', ['title' => '進擊的巨人', 'type' => 'anime']);
        $work = CollectionWork::first();
        $this->assertEqualsCanonicalizing([$cat->id], $work->categories->pluck('id')->all());
    }

    public function test_validation_rejects_bad_rating(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Livewire::actingAs($admin)->test(CollectionForm::class)
            ->set('type','anime')->set('title','X')->set('status','plan')->set('rating', 9)
            ->call('save')->assertHasErrors('rating');
    }

    public function test_non_admin_cannot_save(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Livewire::actingAs($user)->test(CollectionForm::class)
            ->set('type','anime')->set('title','X')->set('status','plan')
            ->call('save')->assertForbidden();
    }

    public function test_guest_cannot_save(): void
    {
        Livewire::test(CollectionForm::class)
            ->set('type','anime')->set('title','X')->set('status','plan')
            ->call('save')->assertForbidden();
    }

    public function test_admin_can_edit_existing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $work = CollectionWork::factory()->create(['title' => 'Old']);
        Livewire::actingAs($admin)->test(CollectionForm::class)
            ->call('open', $work->id)
            ->assertSet('title', 'Old')
            ->set('title', 'New')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('collection_works', ['id' => $work->id, 'title' => 'New']);
    }
}
