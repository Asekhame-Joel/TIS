<?php
declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

use TIS\Payment\OnlineTicketCheckInService;

$staff = tis_require_staff('admin');
tis_staff_verify_csrf();

$orderId = filter_var($_POST['order_id'] ?? null, FILTER_VALIDATE_INT);
$source = (string) ($_POST['source'] ?? 'detail');
$returnTo = $source === 'list' ? '/staff-online-tickets' : '/staff-online-ticket?id=' . (int) $orderId;

if ($orderId === false || $orderId === null || $orderId < 1) {
    tis_staff_flash('error', 'Choose a valid online ticket before checking in.');
    tis_staff_redirect('/staff-online-tickets');
}

try {
    $result = (new OnlineTicketCheckInService(tis_db()))->checkIn($orderId, (int) $staff['id']);
    $order = $result['order'];
    if ($order === null) {
        tis_staff_flash('error', 'That online ticket could not be found.');
    } elseif ($result['checked_in_now']) {
        tis_staff_flash('success', $order['full_name'] . ' has been checked in.');
    } elseif ($order['checked_in_at'] !== null) {
        tis_staff_flash('error', 'This online ticket was already checked in at ' . $order['checked_in_at'] . '.');
    } else {
        tis_staff_flash('error', 'Only successful orders with an issued ticket can be checked in.');
    }
} catch (Throwable $error) {
    tis_log('Online ticket check-in failed: ' . $error->getMessage());
    tis_staff_flash('error', 'The online ticket could not be checked in. Please try again.');
}

tis_staff_redirect($returnTo);
