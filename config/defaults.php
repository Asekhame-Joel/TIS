<?php
declare(strict_types=1);

return [
    'environment' => 'test',
    'site_url' => 'https://www.theintellectualsummit.com',
    'paystack_secret_key' => '',
    'paystack_split_codes' => [
        'standard' => '',
        'premium' => '',
        'vip' => '',
    ],
    // Legacy fallback for old deployments. New ticket payments use the per-tier codes above.
    'paystack_split_code' => '',
    'currency' => 'NGN',
    'secondary_allocation_kobo' => 10000,
    'checkout_rate_limits' => [
        'test' => [
            'email_attempts' => 50,
            'client_attempts' => 100,
        ],
        'live' => [
            'email_attempts' => 5,
            'client_attempts' => 20,
        ],
    ],
    'paystack_fee' => [
        'percentage_basis_points' => 150,
        'fixed_kobo' => 10000,
        'fixed_waiver_threshold_kobo' => 250000,
        'cap_kobo' => 200000,
    ],
    
    'ticket_tiers' => [
        'standard' => [
            // Internal key remains `standard`; customers see Regular.
            'label' => 'Regular',
            'ticket_price_kobo' => 1000000,
            'ticket_prefix' => 'REG',
        ],
        'premium' => [
            // Internal key remains `premium`; customers see VIP.
            'label' => 'VIP',
            'ticket_price_kobo' => 2000000,
            'ticket_prefix' => 'VIP',
        ],
        'vip' => [
            // Keep the `vip` key for existing orders and Paystack split-code compatibility.
            'label' => 'VVIP (Special Session)',
            'ticket_price_kobo' => 5000000,
            'ticket_prefix' => 'VVIP',
        ],
    ],
    'event' => [
        'name' => 'The Intellectual Summit 2026',
        'date' => '14 November 2026',
        'venue' => 'Okunozee Hall, Okada',
    ],
    'database' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => '',
        'user' => '',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    'mail' => [
        'transport' => 'mail',
        'from_email' => 'tickets@theintellectualsummit.com',
        'from_name' => 'The Intellectual Summit',
        'reply_to' => 'tickets@theintellectualsummit.com',
        'smtp' => [
            'host' => '',
            'port' => 465,
            'encryption' => 'ssl',
            'username' => '',
            'password' => '',
        ],
    ],
];
