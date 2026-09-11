<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use TIS\Payment\PaystackClient;
use TIS\Payment\TicketService;

try {
    tis_assert_payment_config();
    $limit = isset($argv[1]) ? (int) $argv[1] : 5;
    $config = tis_config();
    $service = new TicketService(tis_db(), $config);
    $paystack = new PaystackClient((string) $config['paystack_secret_key']);
    $summary = $service->processQueuedJobs($paystack, $limit);

    fwrite(STDOUT, sprintf(
        "processed=%d completed=%d requeued=%d\n",
        $summary['processed'],
        $summary['completed'],
        $summary['requeued']
    ));
} catch (Throwable $error) {
    tis_log('Ticket job worker failed: ' . $error->getMessage());
    fwrite(STDERR, "Ticket job worker failed. Check the PHP error log.\n");
    exit(1);
}
