<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class MagicLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_magic_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/magic-link');

        $response->assertStatus(200);
    }

    public function test_magic_link_can_be_requested(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/magic-link', [
            'email' => $user->email,
        ]);

        $response->assertSessionHas('status');
    }

    public function test_magic_link_can_authenticate_user(): void
    {
        $user = User::factory()->create();
        $token = Str::random(64);

        // Insert token directly
        DB::table('magic_login_tokens')->insert([
            'email' => $user->email,
            'token' => hash('sha256', $token),
            'expires_at' => now()->addMinutes(15),
            'created_at' => now(),
        ]);

        $response = $this->get('/magic-link/verify?token='.$token.'&email='.urlencode($user->email));

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_magic_link_fails_with_expired_token(): void
    {
        $user = User::factory()->create();
        $token = Str::random(64);

        // Insert expired token
        DB::table('magic_login_tokens')->insert([
            'email' => $user->email,
            'token' => hash('sha256', $token),
            'expires_at' => now()->subMinutes(1),
            'created_at' => now()->subMinutes(16),
        ]);

        $response = $this->get('/magic-link/verify?token='.$token.'&email='.urlencode($user->email));

        $this->assertGuest();
        $response->assertSessionHasErrors(['email']);
    }

    public function test_magic_link_fails_with_invalid_token(): void
    {
        $user = User::factory()->create();

        $response = $this->get('/magic-link/verify?token=invalid-token&email='.urlencode($user->email));

        $this->assertGuest();
        $response->assertSessionHasErrors(['email']);
    }
}
