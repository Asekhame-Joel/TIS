<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/_bootstrap.php';

use TIS\Payment\PaystackClient;
use TIS\Payment\PaystackException;

tis_require_method('POST');

try {
    tis_assert_payment_config();
    $config = tis_config();
    $db = tis_db();
    $input = tis_json_input();

    if (!empty($input['website'])) {
        tis_json_response(['success' => false, 'message' => 'Checkout could not be started.'], 400);
    }

    $fullName = trim((string) ($input['full_name'] ?? ''));
    $email = strtolower(trim((string) ($input['email'] ?? '')));
    $phone = trim((string) ($input['phone'] ?? ''));
    $method = (string) ($input['payment_method'] ?? '');
    $tierSlug = strtolower(trim((string) ($input['tier'] ?? '')));
    $tier = tis_ticket_tier($tierSlug, $config);

    $nameLength = function_exists('mb_strlen') ? mb_strlen($fullName) : strlen($fullName);
    if ($nameLength < 2 || $nameLength > 100) {
        tis_json_response(['success' => false, 'message' => 'Enter the attendee’s full name.'], 422);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
        tis_json_response(['success' => false, 'message' => 'Enter a valid email address for the ticket.'], 422);
    }
    if (!preg_match('/^[0-9+() .-]{7,24}$/', $phone)) {
        tis_json_response(['success' => false, 'message' => 'Enter a valid phone number.'], 422);
    }
    if (!in_array($method, ['online', 'bank_transfer'], true)) {
        tis_json_response(['success' => false, 'message' => 'Choose a valid payment method.'], 422);
    }
    if ($tier === null) {
        tis_json_response(['success' => false, 'message' => 'Choose a valid Summit 2026 ticket tier.'], 422);
    }

    $clientHash = tis_client_hash();
    $environment = (string) ($config['environment'] ?? 'live');
    $rateLimits = $config['checkout_rate_limits'][$environment] ?? ['email_attempts' => 5, 'client_attempts' => 20];
    $emailLimit = (int) ($rateLimits['email_attempts'] ?? 5);
    $clientLimit = (int) ($rateLimits['client_attempts'] ?? 20);
    if ($emailLimit < 1 || $clientLimit < 1) {
        throw new RuntimeException('Checkout rate-limit configuration is invalid.');
    }

    $rate = $db->prepare(
        'SELECT COUNT(CASE WHEN email = ? THEN 1 END) AS email_attempts, ' .
        'COUNT(CASE WHEN client_hash = ? THEN 1 END) AS client_attempts ' .
        'FROM tis_orders WHERE created_at >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 15 MINUTE)'
    );
    $rate->execute([$email, $clientHash]);
    $attempts = $rate->fetch();
    if (
        (int) ($attempts['email_attempts'] ?? 0) >= $emailLimit ||
        (int) ($attempts['client_attempts'] ?? 0) >= $clientLimit
    ) {
        tis_json_response(['success' => false, 'message' => 'Too many checkout attempts. Please wait a few minutes and try again.'], 429);
    }

    $reference = 'TIS26-' . strtoupper(bin2hex(random_bytes(10)));
    $amount = (int) $tier['checkout_amount_kobo'];
    $insert = $db->prepare(
        "INSERT INTO tis_orders (reference, full_name, email, phone, tier, amount_kobo, ticket_price_kobo, " .
        "secondary_allocation_kobo, payment_method, status, client_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)"
    );
    $insert->execute([
        $reference,
        $fullName,
        $email,
        $phone,
        $tierSlug,
        $amount,
        (int) $tier['ticket_price_kobo'],
        (int) $tier['secondary_allocation_kobo'],
        $method,
        $clientHash,
    ]);

    $channels = $method === 'bank_transfer' ? ['bank_transfer'] : ['card', 'ussd'];
    $payload = [
        'email' => $email,
        'amount' => (string) $amount,
        'currency' => (string) $config['currency'],
        'reference' => $reference,
        'callback_url' => tis_site_url('/payment-status'),
        'split_code' => (string) $config['paystack_split_code'],
        'channels' => $channels,
        'metadata' => json_encode([
            'order_reference' => $reference,
            'attendee_name' => $fullName,
            'phone' => $phone,
            'tier' => (string) $tier['label'],
            'tier_slug' => $tierSlug,
            'ticket_price_kobo' => (int) $tier['ticket_price_kobo'],
        ], JSON_THROW_ON_ERROR),
    ];

    $paystack = new PaystackClient((string) $config['paystack_secret_key']);
    $response = $paystack->initialize($payload);
    $paystackData = $response['data'] ?? [];
    if (empty($paystackData['authorization_url']) || empty($paystackData['access_code'])) {
        throw new RuntimeException('Paystack did not return a checkout link.');
    }

    $update = $db->prepare('UPDATE tis_orders SET access_code = ?, updated_at = CURRENT_TIMESTAMP WHERE reference = ?');
    $update->execute([(string) $paystackData['access_code'], $reference]);

    tis_json_response([
        'success' => true,
        'reference' => $reference,
        'authorization_url' => (string) $paystackData['authorization_url'],
    ]);
} catch (PaystackException $error) {
    if (isset($db, $reference)) {
        $failed = $db->prepare("UPDATE tis_orders SET status = 'failed', failure_reason = ?, updated_at = CURRENT_TIMESTAMP WHERE reference = ?");
        $failed->execute([substr($error->getMessage(), 0, 250), $reference]);
    }
    $message = $method === 'bank_transfer'
        ? 'A temporary transfer account could not be created. Confirm that Pay with Transfer is enabled, then try again.'
        : 'Paystack checkout could not be started. Please try again.';
    tis_log($error->getMessage());
    tis_json_response(['success' => false, 'message' => $message], 502);
} catch (Throwable $error) {
    tis_log($error->getMessage());
    tis_json_response(['success' => false, 'message' => 'Payment setup is not ready. Please contact the ticket team.'], 500);
}
