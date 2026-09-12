<?php
declare(strict_types=1);

/*
 * Copy this complete file to /home/YOUR_CPANEL_USERNAME/tis-private/config.php.
 * Production uses this one private file for every runtime setting and secret.
 * Never place the real file inside public_html and never commit its secrets.
 */
return [
    'environment' => 'test',
    'site_url' => 'https://www.theintellectualsummit.com',

    // Use sk_test_... during testing. Replace both the key and split code for live mode.
    'paystack_secret_key' => 'sk_test_REPLACE_ME',
    'paystack_split_code' => 'SPL_eeAuKjdcsJ',
    'currency' => 'NGN',
    'secondary_allocation_kobo' => 10000,

    'checkout_rate_limits' => [
        'test' => ['email_attempts' => 50, 'client_attempts' => 100],
        'live' => ['email_attempts' => 5, 'client_attempts' => 20],
    ],

    'paystack_fee' => [
        'percentage_basis_points' => 150,
        'fixed_kobo' => 10000,
        'fixed_waiver_threshold_kobo' => 250000,
        'cap_kobo' => 200000,
    ],

    'ticket_tiers' => [
        'standard' => ['label' => 'Standard Access', 'ticket_price_kobo' => 500000, 'ticket_prefix' => 'STND'],
        'premium' => ['label' => 'Premium Access', 'ticket_price_kobo' => 1000000, 'ticket_prefix' => 'PREM'],
        'vip' => ['label' => 'VIP Access', 'ticket_price_kobo' => 1500000, 'ticket_prefix' => 'VIP'],
    ],

    'event' => [
        'name' => 'The Intellectual Summit 2026',
        'date' => '14 November 2026',
        'venue' => 'Okunozee Hall, Okada',
    ],

    'database' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'CPANEL_DATABASE_NAME',
        'user' => 'CPANEL_DATABASE_USER',
        'password' => 'REPLACE_ME',
        'charset' => 'utf8mb4',
    ],

    'mail' => [
        'transport' => 'smtp',
        'from_email' => 'tickets@theintellectualsummit.com',
        'from_name' => 'The Intellectual Summit',
        'reply_to' => 'tickets@theintellectualsummit.com',
        'smtp' => [
            'host' => 'theintellectualsummit.com',
            'port' => 465,
            'encryption' => 'ssl',
            'username' => 'tickets@theintellectualsummit.com',
            'password' => 'REPLACE_ME',
        ],
    ],
];
