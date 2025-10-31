<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscription_page_is_displayed(): void
    {
        $user = User::factory()->create();

        // Set a fake Stripe secret for testing
        config(['services.stripe.secret' => 'sk_test_fake_key_for_testing']);

        $response = $this
            ->actingAs($user)
            ->get('/subscription');

        $response->assertOk();
    }

    public function test_user_can_check_subscription_status(): void
    {
        $user = User::factory()->create([
            'stripe_customer_id' => 'cus_test123',
            'stripe_subscription_id' => 'sub_test123',
            'stripe_subscription_status' => 'active',
        ]);

        $this->assertTrue($user->hasActiveSubscription());
        $this->assertTrue($user->subscribed());
    }

    public function test_user_can_check_grace_period_status(): void
    {
        $user = User::factory()->create([
            'stripe_customer_id' => 'cus_test123',
            'stripe_subscription_id' => 'sub_test123',
            'stripe_subscription_status' => 'canceled',
            'subscription_ends_at' => now()->addDays(7),
        ]);

        $this->assertFalse($user->hasActiveSubscription());
        $this->assertTrue($user->onGracePeriod());
        $this->assertTrue($user->subscribed());
    }

    public function test_user_without_subscription_is_not_subscribed(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->hasActiveSubscription());
        $this->assertFalse($user->onGracePeriod());
        $this->assertFalse($user->subscribed());
    }

    public function test_expired_subscription_is_not_active(): void
    {
        $user = User::factory()->create([
            'stripe_customer_id' => 'cus_test123',
            'stripe_subscription_id' => 'sub_test123',
            'stripe_subscription_status' => 'canceled',
            'subscription_ends_at' => now()->subDays(1),
        ]);

        $this->assertFalse($user->hasActiveSubscription());
        $this->assertFalse($user->onGracePeriod());
        $this->assertFalse($user->subscribed());
    }

    public function test_trialing_user_has_active_subscription(): void
    {
        $user = User::factory()->create([
            'stripe_customer_id' => 'cus_test123',
            'stripe_subscription_id' => 'sub_test123',
            'stripe_subscription_status' => 'trialing',
        ]);

        $this->assertTrue($user->hasActiveSubscription());
        $this->assertTrue($user->subscribed());
    }

    public function test_past_due_subscription_is_not_active(): void
    {
        $user = User::factory()->create([
            'stripe_customer_id' => 'cus_test123',
            'stripe_subscription_id' => 'sub_test123',
            'stripe_subscription_status' => 'past_due',
        ]);

        $this->assertFalse($user->hasActiveSubscription());
        $this->assertFalse($user->subscribed());
    }
}
