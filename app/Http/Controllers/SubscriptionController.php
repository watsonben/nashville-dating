<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\StripeClient;

class SubscriptionController extends Controller
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Show subscription status page.
     */
    public function index()
    {
        $user = Auth::user();

        return inertia('Subscription/Index', [
            'subscription' => [
                'status' => $user->stripe_subscription_status,
                'subscribed' => $user->subscribed(),
                'ends_at' => $user->subscription_ends_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Create a new subscription.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Create Stripe customer if doesn't exist
        if (! $user->stripe_customer_id) {
            $customer = $this->stripe->customers->create([
                'email' => $user->email,
                'name' => $user->name,
            ]);

            $user->update(['stripe_customer_id' => $customer->id]);
        }

        // Create a checkout session
        $checkoutSession = $this->stripe->checkout->sessions->create([
            'customer' => $user->stripe_customer_id,
            'mode' => 'subscription',
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Monthly Subscription',
                    ],
                    'unit_amount' => 999, // $9.99
                    'recurring' => [
                        'interval' => 'month',
                    ],
                ],
                'quantity' => 1,
            ]],
            'success_url' => route('subscription.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('subscription.index'),
        ]);

        return response()->json([
            'url' => $checkoutSession->url,
        ]);
    }

    /**
     * Handle successful subscription.
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        if ($sessionId) {
            $session = $this->stripe->checkout->sessions->retrieve($sessionId);
            $user = Auth::user();

            if ($session->customer === $user->stripe_customer_id) {
                $user->update([
                    'stripe_subscription_id' => $session->subscription,
                    'stripe_subscription_status' => 'active',
                ]);
            }
        }

        return redirect()->route('subscription.index')
            ->with('success', 'Subscription activated successfully!');
    }

    /**
     * Cancel subscription.
     */
    public function destroy()
    {
        $user = Auth::user();

        if ($user->stripe_subscription_id) {
            $subscription = $this->stripe->subscriptions->update(
                $user->stripe_subscription_id,
                ['cancel_at_period_end' => true]
            );

            $user->update([
                'stripe_subscription_status' => 'canceled',
                'subscription_ends_at' => now()->addSeconds($subscription->current_period_end - time()),
            ]);
        }

        return redirect()->route('subscription.index')
            ->with('success', 'Subscription will be canceled at the end of the billing period.');
    }

    /**
     * Resume canceled subscription.
     */
    public function resume()
    {
        $user = Auth::user();

        if ($user->stripe_subscription_id) {
            $this->stripe->subscriptions->update(
                $user->stripe_subscription_id,
                ['cancel_at_period_end' => false]
            );

            $user->update([
                'stripe_subscription_status' => 'active',
                'subscription_ends_at' => null,
            ]);
        }

        return redirect()->route('subscription.index')
            ->with('success', 'Subscription resumed successfully!');
    }
}
