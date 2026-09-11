<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/_bootstrap.php';

use TIS\Payment\TicketService;

tis_require_method('POST');

try {
    tis_assert_payment_config();
    $config = tis_config();
    $raw = file_get_contents('php://input');
    $signature = (string) ($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '');
    $expected = hash_hmac('sha512', (string) $raw, (string) $config['paystack_secret_key']);

    if ($signature === '' || !hash_equals($expected, $signature)) {
        tis_json_response(['success' => false, 'message' => 'Invalid webhook signature.'], 401);
    }

    $event = json_decode((string) $raw, true);
    if (!is_array($event)) {
        tis_json_response(['success' => false, 'message' => 'Invalid webhook data.'], 400);
    }

    if (($event['event'] ?? '') !== 'charge.success') {
        tis_json_response(['success' => true, 'message' => 'Event acknowledged.']);
    }

    $reference = (string) ($event['data']['reference'] ?? '');
    if (!preg_match('/^TIS26-[A-F0-9]{20}$/', $reference)) {
        tis_json_response(['success' => true, 'message' => 'Unrelated transaction ignored.']);
    }

    $service = new TicketService(tis_db(), $config);
    if ($service->findByReference($reference) === null) {
        tis_json_response(['success' => true, 'message' => 'Unknown transaction ignored.']);
    }

    $service->queueTicketProcessing($reference);
    tis_json_response(['success' => true, 'message' => 'Event queued.']);
} catch (Throwable $error) {
    tis_log($error->getMessage());
    tis_json_response(['success' => false, 'message' => 'Webhook processing will be retried.'], 500);
}
