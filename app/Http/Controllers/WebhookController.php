<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class WebhookController extends Controller
{
    /**
     * Handle Stripe webhook.
     */
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (SignatureVerificationException $e) {
            Log::error('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'customer.subscription.created':
            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event->data->object);
                break;

            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event->data->object);
                break;

            case 'invoice.payment_succeeded':
                $this->handlePaymentSucceeded($event->data->object);
                break;

            case 'invoice.payment_failed':
                $this->handlePaymentFailed($event->data->object);
                break;

            default:
                Log::info('Unhandled Stripe webhook event', ['type' => $event->type]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle subscription created/updated events.
     */
    protected function handleSubscriptionUpdated($subscription)
    {
        $user = User::where('stripe_customer_id', $subscription->customer)->first();

        if ($user) {
            $user->update([
                'stripe_subscription_id' => $subscription->id,
                'stripe_subscription_status' => $subscription->status,
                'subscription_ends_at' => $subscription->cancel_at
                    ? now()->addSeconds($subscription->cancel_at - time())
                    : null,
            ]);

            Log::info('Subscription updated', [
                'user_id' => $user->id,
                'status' => $subscription->status,
            ]);
        }
    }

    /**
     * Handle subscription deleted event.
     */
    protected function handleSubscriptionDeleted($subscription)
    {
        $user = User::where('stripe_subscription_id', $subscription->id)->first();

        if ($user) {
            $user->update([
                'stripe_subscription_status' => 'canceled',
                'subscription_ends_at' => now(),
            ]);

            Log::info('Subscription deleted', ['user_id' => $user->id]);
        }
    }

    /**
     * Handle successful payment.
     */
    protected function handlePaymentSucceeded($invoice)
    {
        $user = User::where('stripe_customer_id', $invoice->customer)->first();

        if ($user && $user->stripe_subscription_status !== 'active') {
            $user->update(['stripe_subscription_status' => 'active']);

            Log::info('Payment succeeded', [
                'user_id' => $user->id,
                'amount' => $invoice->amount_paid,
            ]);
        }
    }

    /**
     * Handle failed payment.
     */
    protected function handlePaymentFailed($invoice)
    {
        $user = User::where('stripe_customer_id', $invoice->customer)->first();

        if ($user) {
            $user->update(['stripe_subscription_status' => 'past_due']);

            Log::warning('Payment failed', [
                'user_id' => $user->id,
                'invoice_id' => $invoice->id,
            ]);
        }
    }
}
