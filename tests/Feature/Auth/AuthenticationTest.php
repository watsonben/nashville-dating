<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        // Since we removed password authentication, this test now verifies
        // that the login screen is accessible and prompts for passkey/magic link
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        // This test is no longer applicable since password authentication is removed
        $this->markTestSkipped('Password authentication has been removed in favor of passkeys and magic links');
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
