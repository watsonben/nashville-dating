<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laragear\WebAuthn\WebAuthnAuthentication;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, WebAuthnAuthentication;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'stripe_customer_id',
        'stripe_subscription_id',
        'stripe_subscription_status',
        'subscription_ends_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'subscription_ends_at' => 'datetime',
        ];
    }

    /**
     * Check if user has an active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->stripe_subscription_status === 'active' ||
               $this->stripe_subscription_status === 'trialing';
    }

    /**
     * Check if user's subscription is canceled but still active until period end.
     */
    public function onGracePeriod(): bool
    {
        return $this->stripe_subscription_status === 'canceled' &&
               $this->subscription_ends_at &&
               $this->subscription_ends_at->isFuture();
    }

    /**
     * Check if user can access subscription features.
     */
    public function subscribed(): bool
    {
        return $this->hasActiveSubscription() || $this->onGracePeriod();
    }
}
