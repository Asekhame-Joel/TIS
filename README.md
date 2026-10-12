# The Intellectual Summit

PHP 8.1+ website and multi-tier ticket checkout for The Intellectual Summit 2026.

## Local setup

```text
composer install
cp config.example.php config.local.php
php -S 127.0.0.1:8080 -t public scripts/router.php
php scripts/verify-ticket-pricing.php
```

Keep real Paystack, database and SMTP credentials out of Git. On Namecheap, the production configuration belongs at `~/tis-private/config.php`.

For production ticket delivery, enable the cPanel cron command in `PAYMENTS_SETUP.md`. The worker verifies queued Paystack payments and sends ticket emails outside the webhook response.

## Application structure

```text
app/        Shared PHP templates and payment services
config/     Non-secret defaults
database/   MySQL schema
public/     Web pages, API endpoints and static assets
scripts/    Local development router and ticket-pricing regression check
```

See `PAYMENTS_SETUP.md` for the cPanel, database, email, split-payment and go-live checklist.
