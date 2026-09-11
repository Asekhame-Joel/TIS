<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/_bootstrap.php';

use TIS\Payment\PaystackClient;
use TIS\Payment\TicketService;

tis_require_method('GET');

try {
    tis_assert_payment_config();
    $reference = trim((string) ($_GET['reference'] ?? ''));
    if (!preg_match('/^TIS26-[A-F0-9]{20}$/', $reference)) {
        tis_json_response(['success' => false, 'message' => 'The payment reference is invalid.'], 400);
    }

    $config = tis_config();
    $service = new TicketService(tis_db(), $config);
    $existing = $service->findByReference($reference);
    if ($existing === null) {
        tis_json_response(['success' => false, 'message' => 'This payment reference was not created by the ticket website.'], 404);
    }

    if ($existing['status'] === 'success') {
        $service->queueTicketProcessing($reference);
        tis_json_response(['success' => true, 'status' => 'success', 'ticket' => $service->publicTicket($existing)]);
    }

    $paystack = new PaystackClient((string) $config['paystack_secret_key']);
    $result = $service->verifyAndFinalize($paystack, $reference);
    if ($result['status'] !== 'success' || !is_array($result['order'])) {
        if (in_array($result['status'], ['failed', 'abandoned', 'reversed'], true)) {
            $service->markUnsuccessful($reference, $result['status']);
            tis_json_response([
                'success' => false,
                'status' => 'failed',
                'message' => 'This payment was not completed. No ticket has been issued.',
            ], 400);
        }
        tis_json_response([
            'success' => false,
            'status' => 'pending',
            'message' => 'We are still waiting for Paystack to confirm this payment. Your ticket will be emailed automatically after confirmation.',
        ], 202);
    }

    $service->queueTicketProcessing($reference);
    tis_json_response([
        'success' => true,
        'status' => 'success',
        'ticket' => $service->publicTicket($result['order']),
    ]);
} catch (Throwable $error) {
    tis_log($error->getMessage());
    tis_json_response(['success' => false, 'message' => 'We could not verify this payment right now. Please try again shortly.'], 502);
}
