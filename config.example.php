<?php
declare(strict_types=1);

/*
 * Copy this file to /home/YOUR_CPANEL_USERNAME/tis-private/config.php.
 * Never place the real file inside public_html and never commit its secrets.
 */
return [
    'environment' => 'test',
    'site_url' => 'https://www.theintellectualsummit.com',

    // Use sk_test_... during testing. Replace both the key and split code for live mode.
    'paystack_secret_key' => 'sk_test_REPLACE_ME',
    'paystack_split_code' => 'SPL_eeAuKjdcsJ',

    // Ticket prices and the shared Paystack fee rules live in config/defaults.php.
    // Fee-inclusive checkout totals are calculated automatically in whole kobo.

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
