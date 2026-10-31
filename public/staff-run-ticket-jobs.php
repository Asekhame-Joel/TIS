<?php
declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

use TIS\Payment\PaystackClient;
use TIS\Payment\TicketService;

tis_require_staff('admin');
tis_staff_verify_csrf();
tis_staff_session_start();

$lastRun = (int) ($_SESSION['tis_ticket_jobs_last_run'] ?? 0);
if ($lastRun > time() - 30) {
    tis_staff_flash('error', 'Please wait a few seconds before running the ticket jobs again.');
    tis_staff_redirect('/staff-online-tickets');
}

try {
    tis_assert_payment_config();
    $config = tis_config();
    $service = new TicketService(tis_db(), $config);
    $paystack = new PaystackClient((string) $config['paystack_secret_key']);
    $summary = $service->processQueuedJobs($paystack, 5);
    $_SESSION['tis_ticket_jobs_last_run'] = time();
    tis_staff_flash(
        'success',
        sprintf('Ticket jobs completed: %d processed, %d completed, %d requeued.', $summary['processed'], $summary['completed'], $summary['requeued'])
    );
} catch (Throwable $error) {
    tis_log('Manual ticket job run failed: ' . $error->getMessage());
    tis_staff_flash('error', 'Ticket jobs could not run. Check the server error log and try again.');
}

tis_staff_redirect('/staff-online-tickets');
