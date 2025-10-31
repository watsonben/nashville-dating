<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenderSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_gender_selection_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/gender-selection');

        $response->assertStatus(200);
    }

    public function test_user_can_select_gender(): void
    {
        $user = User::factory()->create(['gender' => null]);

        $response = $this
            ->actingAs($user)
            ->post('/gender-selection', [
                'gender' => 'female',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertSame('female', $user->gender);
    }

    public function test_gender_selection_requires_valid_gender(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/gender-selection')
            ->post('/gender-selection', [
                'gender' => 'invalid',
            ]);

        $response
            ->assertSessionHasErrors('gender')
            ->assertRedirect('/gender-selection');
    }

    public function test_gender_selection_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/gender-selection')
            ->post('/gender-selection', [
                'gender' => '',
            ]);

        $response
            ->assertSessionHasErrors('gender')
            ->assertRedirect('/gender-selection');
    }

    public function test_new_user_registration_redirects_to_gender_selection(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('gender.create'));
    }

    public function test_user_can_update_gender_in_profile(): void
    {
        $user = User::factory()->create(['gender' => 'male']);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'gender' => 'female',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();
        $this->assertSame('female', $user->gender);
    }
}
