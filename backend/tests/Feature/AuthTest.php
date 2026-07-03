<?php // backend/tests/Feature/AuthTest.php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_renders(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_admin_can_authenticate_with_session(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'password' => bcrypt('password123')]);
        $this->post('/login', ['email' => $user->email, 'password' => 'password123'])
             ->assertRedirect();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->fresh()->isAdmin());
    }

    public function test_registration_route_is_disabled(): void
    {
        $this->get('/register')->assertNotFound();
    }
}
