# TIS PHP website and Summit 2026 ticket payments

The whole website runs on PHP 8.1+ with shared layouts and clean URLs. All ticket prices come from the single `ticket_tiers` catalogue in `config/defaults.php`. Checkout totals are calculated automatically in whole kobo from the shared Paystack fee configuration, including upward percentage rounding, the flat charge, waiver threshold and fee cap.

| Tier | Ticket price | Customer pays | Expected Paystack fee | OPay split | Main/Zenith settlement |
| --- | ---: | ---: | ---: | ---: | ---: |
| Standard Access | NGN 5,000.00 | NGN 5,279.19 | NGN 179.19 | NGN 100.00 | NGN 5,000.00 |
| Premium Access | NGN 10,000.00 | NGN 10,355.33 | NGN 255.33 | NGN 100.00 | NGN 10,000.00 |
| VIP Access | NGN 15,000.00 | NGN 15,431.48 | NGN 331.48 | NGN 100.00 | NGN 15,000.00 |

These are the current **Early Bird prices across all three tiers**; Early Bird is not a separate ticket. The checkout totals gross up Nigeria's current local transaction fee of 1.5% + NGN 100. Keep international payments disabled if the main account must receive the exact listed ticket price, because international card fees are different. If Paystack changes its fees or gives the account custom pricing, update the single `paystack_fee` section and retest all tiers.

## Project layout

```text
app/                 PHP application, payment services and shared templates
config/              Safe default configuration
database/            Ticket database schema
public/              The only web-accessible pages, APIs and assets
scripts/router.php   Local clean-URL development router
composer.json        PHP library requirements
composer.lock        Locked, reproducible library versions
```

Composer installs Dompdf for ticket PDFs, PHPMailer for authenticated email and PHP dotenv for local environment loading. Run this after cloning:

```text
composer install
```

## 1. Create the private server configuration

In cPanel File Manager, create this folder outside `public_html`:

```text
/home/YOUR_CPANEL_USERNAME/tis-private/
```

Copy `config.example.php` to that folder and rename it `config.php`. Enter the real Test Mode Paystack secret key, database credentials, and ticket-email SMTP password there. The private file must never be committed to Git or copied into `public_html`.

In cPanel **Select PHP Version**, use PHP 8.1 or newer. Enable cURL, DOM, JSON, mbstring, OpenSSL and PDO MySQL.

The supplied Test Mode split code is `SPL_eeAuKjdcsJ`. Live Mode requires a separate live split code and a live secret key. The project does not use a Paystack public key because checkout initialization happens securely on the PHP server.

## 2. Create the ticket database

In cPanel:

1. Open **MySQL Databases**.
2. Create a database and database user.
3. Give the user all privileges on that database.
4. Open **phpMyAdmin**, choose the database, and import `database/schema.sql`.
5. Put the resulting database name, user and password in the private configuration file.

## 3. Configure ticket email

Create `tickets@theintellectualsummit.com` in cPanel or Namecheap Private Email. Put its authenticated SMTP settings in the private configuration file. The example uses port 465 with SSL; use the exact server details shown by Namecheap if they differ.

Ensure the domain has valid SPF, DKIM and DMARC records before live ticket sales so ticket messages are less likely to enter spam folders.

## 4. Enable the ticket worker cron job

Paystack webhooks only validate and queue a payment reference, then return `200 OK` immediately. A cron worker performs the slower Paystack verification, PDF generation and SMTP delivery outside the webhook and customer confirmation page.

In cPanel **Cron Jobs**, create this job to run every minute. Replace `YOUR_CPANEL_USERNAME` with the cPanel account username and confirm the PHP path in cPanel Terminal with `which php` if `/usr/local/bin/php` is unavailable:

```text
* * * * * TIS_CONFIG_PATH=/home/YOUR_CPANEL_USERNAME/tis-private/config.php /usr/local/bin/php /home/YOUR_CPANEL_USERNAME/tis-app/scripts/process-ticket-jobs.php 5 >/dev/null 2>&1
```

Before enabling the cron job, test it once in cPanel Terminal:

```text
TIS_CONFIG_PATH=/home/YOUR_CPANEL_USERNAME/tis-private/config.php /usr/local/bin/php /home/YOUR_CPANEL_USERNAME/tis-app/scripts/process-ticket-jobs.php 5
```

It should return a line such as `processed=0 completed=0 requeued=0`. The deployment file copies `scripts/` to `~/tis-app/scripts/` specifically for this worker.

## 5. Configure Paystack Test Mode

You do **not** need Paystack Products or Plans. Tickets are one-time payments, and the PHP backend supplies the selected tier's amount when it initializes each transaction. Plans are only for recurring subscriptions and must not be attached to these ticket transactions.

Use one reusable Test Mode transaction split for every tier:

1. Make the Paystack integration's main payout account the Zenith account.
2. Create or verify the OPay subaccount using the correct bank and account number.
3. Create a **Flat** transaction split named `TIS Summit 2026 Tickets - Test`.
4. Add the OPay subaccount with a flat share of **NGN 100**.
5. Set **Deduct transaction fee from** to **Your account** (the main integration account).
6. Put the resulting Test Mode `SPL_...` code in the private server configuration as `paystack_split_code`.

The split group name is for dashboard clarity only. Renaming the existing Test Mode group does not require a code change as long as its split code stays the same.

In Paystack Test Mode, set the webhook URL to:

```text
https://www.theintellectualsummit.com/api/payment/webhook.php
```

The code supplies this callback URL for every initialized transaction:

```text
https://www.theintellectualsummit.com/payment-status
```

The webhook validates Paystack's SHA-512 signature and queues the transaction reference immediately. The cron worker then verifies the transaction again from the server, checks the amount, currency, customer and split code, issues the ticket, and sends or retries its email. This prevents slow SMTP delivery from delaying the customer or causing webhook retries.

Ensure **Pay with Transfer** is enabled if the generated temporary bank-account option should appear. Card and USSD use the first checkout option; bank transfer uses the second.

Do not enable live sales until all of these tests pass for Standard, Premium and VIP:

- A successful card payment creates one ticket and sends one email.
- Reopening the callback URL does not create a second ticket.
- A generated-account transfer remains pending until Paystack confirms it.
- A confirmed transfer sends the email and enables the PDF download.
- A failed or abandoned payment does not issue a ticket.
- The Paystack transaction shows NGN 100 in the secondary account split.
- The Paystack transaction amount matches the tier's **Customer pays** amount in the table above.
- The generated email and PDF show the tier actually purchased.

## 6. Go live

Create the same flat split group in Paystack Live Mode, named `TIS Summit 2026 Tickets - Live`. Live and Test Mode resources are separate, so use the Live Mode OPay subaccount and copy the new Live Mode split code. Then update the private configuration together:

```php
'environment' => 'live',
'paystack_secret_key' => 'sk_live_...',
'paystack_split_code' => 'SPL_LIVE_CODE',
```

Never combine a Test Mode key with a Live Mode split code.

No Product code or Plan code should be added to the PHP. If you later change a tier price, update only its `ticket_price_kobo` in `config/defaults.php`; the fee-inclusive checkout amount is calculated automatically. Test the split again before deploying the price change.

## Namecheap deployment

The cPanel Git deployment file installs the locked Composer packages, copies private PHP application code to `~/tis-app`, and copies only `public/` into `public_html`. The real secrets remain separately in `~/tis-private/config.php`.

If Git deployment reports that `composer` is unavailable, enable SSH and run `composer install --no-dev --optimize-autoloader` in the repository once, then redeploy. Do not copy the real private configuration into the repository.

For a local preview with clean URLs:

```text
php -S 127.0.0.1:8080 -t public scripts/router.php
```

Before deploying a ticket-price or Paystack-fee change, run:

```text
php scripts/verify-ticket-pricing.php
```

It fails unless every customer total is the smallest whole-kobo amount that leaves the configured ticket price in the main account after the Paystack fee and NGN 100 secondary split.

Checkout abuse protection remains strict in Live Mode: five attempts per email or twenty attempts per client within fifteen minutes. Test Mode allows repeated QA runs with higher limits. These thresholds are defined in `checkout_rate_limits` in `config/defaults.php`.

During local email testing, ticket links contain `127.0.0.1` and therefore work only on the computer running the PHP server. The PDF is also attached to every ticket email. After deployment, setting `site_url` to the live HTTPS domain makes the email buttons use the public ticket URL.
