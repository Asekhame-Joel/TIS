<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

tis_require_staff('admin');
$query = tis_db()->prepare(
    'SELECT t.code, t.event_name, COALESCE(t.vendor_name, \'\') AS vendor_name, t.status, ' .
    'COALESCE(o.full_name, \'\') AS attendee_name, COALESCE(o.email, \'\') AS attendee_email, ' .
    'COALESCE(o.phone, \'\') AS attendee_phone, COALESCE(o.tier, \'\') AS tier, ' .
    'COALESCE(t.manual_payment_reference, \'\') AS manual_payment_reference, t.sold_at, t.checked_in_at, t.created_at ' .
    'FROM tis_physical_tickets t LEFT JOIN tis_orders o ON o.id = t.order_id WHERE t.event_key = ? ORDER BY t.id ASC'
);
$query->execute([tis_staff_event_key()]);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="tis-physical-tickets-' . date('Y-m-d') . '.csv"');
header('Cache-Control: no-store, private');
$output = fopen('php://output', 'wb');
fwrite($output, "\xEF\xBB\xBF");
fputcsv($output, ['Code', 'Event', 'Vendor', 'Status', 'Attendee', 'Email', 'Phone', 'Tier', 'Manual payment reference', 'Sold at', 'Checked in at', 'Generated at']);
while ($ticket = $query->fetch()) {
    $row = [$ticket['code'], $ticket['event_name'], $ticket['vendor_name'], $ticket['status'], $ticket['attendee_name'], $ticket['attendee_email'], $ticket['attendee_phone'], $ticket['tier'], $ticket['manual_payment_reference'], $ticket['sold_at'], $ticket['checked_in_at'], $ticket['created_at']];
    $row = array_map(static function (mixed $value): string {
        $value = (string) $value;
        return preg_match('/^[=+\-@]/', $value) ? "'" . $value : $value;
    }, $row);
    fputcsv($output, $row);
}
fclose($output);
exit;
