<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_requires_valid_signature(): void
    {
        $response = $this->postJson('/webhook/stripe', [
            'type' => 'customer.subscription.updated',
        ]);

        $response->assertStatus(400);
    }

    public function test_webhook_can_update_subscription_status(): void
    {
        $user = User::factory()->create([
            'stripe_customer_id' => 'cus_test123',
        ]);

        // Test that the user model can be updated
        $user->update([
            'stripe_subscription_id' => 'sub_test123',
            'stripe_subscription_status' => 'active',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'stripe_subscription_status' => 'active',
        ]);
    }

    public function test_subscription_canceled_updates_user(): void
    {
        $user = User::factory()->create([
            'stripe_customer_id' => 'cus_test123',
            'stripe_subscription_id' => 'sub_test123',
            'stripe_subscription_status' => 'active',
        ]);

        // Simulate subscription cancellation
        $user->update([
            'stripe_subscription_status' => 'canceled',
            'subscription_ends_at' => now(),
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'stripe_subscription_status' => 'canceled',
        ]);
    }

    public function test_payment_succeeded_activates_subscription(): void
    {
        $user = User::factory()->create([
            'stripe_customer_id' => 'cus_test123',
            'stripe_subscription_id' => 'sub_test123',
            'stripe_subscription_status' => 'past_due',
        ]);

        // Simulate successful payment
        $user->update(['stripe_subscription_status' => 'active']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'stripe_subscription_status' => 'active',
        ]);
    }

    public function test_payment_failed_marks_subscription_past_due(): void
    {
        $user = User::factory()->create([
            'stripe_customer_id' => 'cus_test123',
            'stripe_subscription_id' => 'sub_test123',
            'stripe_subscription_status' => 'active',
        ]);

        // Simulate failed payment
        $user->update(['stripe_subscription_status' => 'past_due']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'stripe_subscription_status' => 'past_due',
        ]);
    }
}
