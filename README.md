# Nashville Dating

A modern dating application built for the Nashville community, featuring secure authentication and subscription-based access.

## About

Nashville Dating is a Laravel-based dating platform that provides users with a secure, subscription-powered dating experience. The application emphasizes user privacy and security through modern authentication methods including passwordless magic links and WebAuthn (biometric) authentication.

## Key Features

### 🔐 Advanced Authentication
- **Magic Link Login**: Passwordless authentication via email links
- **WebAuthn Support**: Biometric authentication (fingerprint, Face ID, etc.)
- **Traditional Email/Password**: Standard authentication option
- **Gender Selection**: Required during registration for profile matching

### 💳 Subscription Management
- **Monthly Plans**: $9.99/month subscription powered by Stripe
- **Flexible Management**: Subscribe, cancel, or resume subscriptions at any time
- **Grace Period**: Canceled subscriptions remain active until the end of the billing period
- **Real-time Updates**: Stripe webhooks for instant subscription status updates
- **Secure Payments**: PCI-compliant payment processing through Stripe

### 🛠 Technical Stack
- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Vue 3 with Inertia.js
- **Styling**: Tailwind CSS
- **Database**: PostgreSQL/MySQL/SQLite
- **Payment Processing**: Stripe
- **Build Tool**: Vite

## Getting Started

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 22.12.0 or higher
- npm
- Database (PostgreSQL, MySQL, or SQLite)
- Stripe account (for subscription features)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/watsonben/nashville-dating.git
   cd nashville-dating
   ```

2. **Install dependencies and set up the application**
   ```bash
   composer run setup
   ```
   
   This command will:
   - Install PHP dependencies
   - Create `.env` file from `.env.example`
   - Generate application key
   - Run database migrations
   - Install Node.js dependencies
   - Build frontend assets

3. **Configure your environment**
   
   Edit the `.env` file and configure:
   - Database connection settings
   - Mail server settings (for magic links)
   - Stripe API keys (see [STRIPE_SETUP.md](STRIPE_SETUP.md))

4. **Set up Stripe** (required for subscriptions)
   
   Follow the detailed instructions in [STRIPE_SETUP.md](STRIPE_SETUP.md) to:
   - Configure Stripe API keys
   - Set up webhook endpoints
   - Test subscription flows

### Development

Start the development environment with all services running:

```bash
composer run dev
```

This starts:
- PHP development server (port 8000)
- Queue worker for background jobs
- Application logs (Laravel Pail)
- Vite dev server with hot module replacement

Access the application at `http://localhost:8000`

### Testing

Run the test suite:

```bash
composer run test
```

Or use PHPUnit directly:

```bash
php artisan test
```

Test coverage includes:
- Authentication flows (magic link, WebAuthn, traditional)
- Subscription lifecycle (create, cancel, resume)
- Stripe webhook handling
- User profile management

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/              # Authentication controllers
│   │   │   ├── MagicLinkController.php
│   │   │   ├── WebAuthn/
│   │   ├── SubscriptionController.php
│   │   ├── WebhookController.php
│   │   └── ProfileController.php
│   └── Middleware/
├── Models/
│   └── User.php               # User model with subscription methods
resources/
├── js/
│   ├── Components/            # Reusable Vue components
│   ├── Layouts/               # Page layouts
│   └── Pages/                 # Inertia.js pages
│       ├── Auth/              # Authentication pages
│       ├── Subscription/      # Subscription management
│       ├── Dashboard.vue
│       └── Welcome.vue
routes/
├── web.php                    # Main application routes
└── auth.php                   # Authentication routes
```

## User Flow

1. **Registration**: Users sign up with email, password, and gender selection
2. **Authentication**: Users can log in via:
   - Traditional email/password
   - Magic link (passwordless)
   - WebAuthn (biometric)
3. **Subscription**: After logging in, users can subscribe to access premium features
4. **Profile Management**: Users can update their profile and manage subscriptions

## Subscription Features

The application includes a comprehensive subscription system:

- **User Methods**:
  - `hasActiveSubscription()`: Check if user has an active subscription
  - `onGracePeriod()`: Check if canceled but still active
  - `subscribed()`: Check if user can access subscription features

- **Subscription Statuses**:
  - `active`: Subscription is active and current
  - `trialing`: User is in trial period
  - `canceled`: Canceled but still active until period end
  - `past_due`: Payment failed
  - `unpaid`: Multiple payment failures

For detailed subscription setup and management, see [STRIPE_SETUP.md](STRIPE_SETUP.md).

## Configuration

Key configuration files:

- `.env`: Environment variables (database, mail, Stripe keys)
- `config/database.php`: Database connections
- `config/services.php`: Third-party service configuration
- `config/mail.php`: Email service configuration

## Deployment

Before deploying to production:

1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false` in `.env`
3. Configure production database credentials
4. Set up production mail server
5. Replace Stripe test keys with production keys
6. Configure webhook endpoint for production URL
7. Run migrations: `php artisan migrate --force`
8. Build assets: `npm run build`
9. Optimize application: `php artisan optimize`

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Security

If you discover any security vulnerabilities, please email the maintainer directly instead of using the issue tracker.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

Built with [Laravel](https://laravel.com), the PHP framework for web artisans.
