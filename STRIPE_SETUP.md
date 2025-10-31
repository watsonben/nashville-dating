# Stripe Subscription Integration

This guide explains how to set up and use the Stripe subscription integration for the Nashville Dating application.

## Overview

The application supports monthly subscriptions at $9.99/month through Stripe. Users can subscribe, cancel, and resume their subscriptions through a dedicated subscription management page.

## Features

- **Monthly Subscription**: $9.99/month recurring subscription
- **Subscription Management**: Users can subscribe, cancel, and resume subscriptions
- **Grace Period**: Canceled subscriptions remain active until the end of the billing period
- **Webhook Support**: Automatic subscription status updates via Stripe webhooks
- **Secure Payment**: All payments processed securely through Stripe

## Setup Instructions

### 1. Create a Stripe Account

1. Sign up for a Stripe account at [https://stripe.com](https://stripe.com)
2. Get your API keys from the Stripe Dashboard

### 2. Configure Environment Variables

Add the following to your `.env` file:

```env
STRIPE_KEY=pk_test_your_publishable_key
STRIPE_SECRET=sk_test_your_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret
```

**Note**: Use test keys (starting with `pk_test_` and `sk_test_`) during development.

### 3. Set Up Webhooks

1. Go to the Stripe Dashboard → Developers → Webhooks
2. Click "Add endpoint"
3. Set the endpoint URL to: `https://yourdomain.com/webhook/stripe`
4. Select the following events to listen to:
   - `customer.subscription.created`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
   - `invoice.payment_succeeded`
   - `invoice.payment_failed`
5. Copy the webhook signing secret and add it to your `.env` file as `STRIPE_WEBHOOK_SECRET`

### 4. Run Database Migrations

```bash
php artisan migrate
```

This will add the necessary subscription fields to the users table:
- `stripe_customer_id`
- `stripe_subscription_id`
- `stripe_subscription_status`
- `subscription_ends_at`

### 5. Test the Integration

1. Start your development server: `composer dev`
2. Log in to your application
3. Navigate to `/subscription`
4. Click "Subscribe Now" to test the checkout flow
5. Use Stripe's test card numbers: `4242 4242 4242 4242`

## Usage

### Subscription Page

Users can access the subscription management page at `/subscription`. From this page, they can:

- View their current subscription status
- Subscribe to the monthly plan
- Cancel their subscription (remains active until period end)
- Resume a canceled subscription before it expires

### User Model Methods

The `User` model includes several helpful methods for checking subscription status:

```php
// Check if user has an active subscription
$user->hasActiveSubscription(); // Returns true for 'active' or 'trialing' status

// Check if user is in grace period (canceled but still active)
$user->onGracePeriod(); // Returns true if canceled and subscription_ends_at is in the future

// Check if user can access subscription features
$user->subscribed(); // Returns true if hasActiveSubscription() or onGracePeriod()
```

### Subscription Statuses

- `active`: Subscription is active and payments are current
- `trialing`: User is in a trial period (if trials are enabled)
- `canceled`: Subscription is canceled but still active until period end
- `past_due`: Payment failed, subscription is in grace period
- `unpaid`: Multiple payment failures, subscription may be canceled soon
- `incomplete`: Initial payment is pending
- `incomplete_expired`: Initial payment was not completed

## API Routes

### Authenticated Routes (require login)

- `GET /subscription` - View subscription status page
- `POST /subscription` - Create a new subscription (returns Stripe Checkout URL)
- `GET /subscription/success` - Handle successful subscription
- `DELETE /subscription` - Cancel subscription (end at period end)
- `POST /subscription/resume` - Resume a canceled subscription

### Public Routes

- `POST /webhook/stripe` - Stripe webhook handler (no CSRF protection)

## Webhook Events

The application handles the following Stripe webhook events:

### `customer.subscription.created` / `customer.subscription.updated`
Updates the user's subscription status, ID, and end date.

### `customer.subscription.deleted`
Marks the subscription as canceled and sets the end date to now.

### `invoice.payment_succeeded`
Ensures the subscription status is set to 'active' after successful payment.

### `invoice.payment_failed`
Marks the subscription as 'past_due' when a payment fails.

## Testing

The application includes comprehensive tests for subscription functionality:

```bash
# Run all tests
php artisan test

# Run only subscription tests
php artisan test --filter=SubscriptionTest

# Run only webhook tests
php artisan test --filter=WebhookTest
```

### Test Coverage

- Subscription status checks (active, trialing, canceled, expired)
- Grace period functionality
- Webhook signature verification
- Subscription lifecycle events

## Security Considerations

1. **Webhook Signature Verification**: All webhooks are verified using the webhook signing secret
2. **CSRF Protection**: The webhook endpoint is excluded from CSRF verification
3. **Secure API Keys**: Never commit API keys to version control
4. **Test vs Production Keys**: Always use test keys during development

## Troubleshooting

### Webhook not receiving events

1. Check that your webhook URL is publicly accessible
2. Verify the webhook secret in your `.env` file matches Stripe Dashboard
3. Check the webhook logs in Stripe Dashboard for error messages

### Subscription status not updating

1. Ensure webhooks are properly configured
2. Check application logs for webhook processing errors
3. Verify the user has a `stripe_customer_id` in the database

### Test payments failing

1. Make sure you're using test API keys (starting with `pk_test_` and `sk_test_`)
2. Use valid Stripe test card numbers
3. Check the Stripe Dashboard for payment attempt logs

## Production Checklist

Before going live with subscriptions:

- [ ] Replace test API keys with production keys
- [ ] Update webhook endpoint to production URL
- [ ] Configure webhook secret for production
- [ ] Test the complete subscription flow
- [ ] Verify webhook events are being received
- [ ] Set up monitoring and alerts for failed payments
- [ ] Review Stripe Dashboard settings (business info, branding, etc.)
- [ ] Test cancellation and refund workflows

## Resources

- [Stripe API Documentation](https://stripe.com/docs/api)
- [Stripe PHP Library](https://github.com/stripe/stripe-php)
- [Stripe Testing](https://stripe.com/docs/testing)
- [Stripe Webhooks](https://stripe.com/docs/webhooks)
