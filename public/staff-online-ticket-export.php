<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

tis_require_staff('admin');
$filters = tis_online_ticket_filters($_GET, tis_config());
$query = tis_db()->prepare(
    'SELECT o.reference, o.full_name, o.email, o.phone, o.referrer_name, o.tier, o.amount_kobo, o.payment_method, ' .
    'o.status, o.channel, o.paystack_id, o.paid_at, o.ticket_number, o.email_sent_at, o.failure_reason, o.created_at ' .
    'FROM tis_orders o WHERE ' . implode(' AND ', $filters['where']) . ' ORDER BY o.created_at DESC, o.id DESC'
);
$query->execute($filters['params']);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="tis-online-tickets-' . date('Y-m-d') . '.csv"');
header('Cache-Control: no-store, private');
$output = fopen('php://output', 'wb');
fwrite($output, "\xEF\xBB\xBF");
fputcsv($output, ['Reference', 'Full name', 'Email', 'Phone', 'Referrer', 'Category', 'Amount (NGN)', 'Checkout method', 'Status', 'Payment channel', 'Gateway transaction ID', 'Paid at', 'Ticket number', 'Ticket email sent at', 'Failure reason', 'Created at']);
while ($order = $query->fetch()) {
    $row = [$order['reference'], $order['full_name'], $order['email'], $order['phone'], $order['referrer_name'], $order['tier'], number_format(((int) $order['amount_kobo']) / 100, 2, '.', ''), $order['payment_method'], $order['status'], $order['channel'], $order['paystack_id'], $order['paid_at'], $order['ticket_number'], $order['email_sent_at'], $order['failure_reason'], $order['created_at']];
    fputcsv($output, array_map(static function (mixed $value): string { $value = (string) $value; return preg_match('/^[=+\-@]/', $value) ? "'" . $value : $value; }, $row));
}
fclose($output);
exit;
