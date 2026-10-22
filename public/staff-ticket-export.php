<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

tis_require_staff('admin');
$query = tis_db()->prepare(
    'SELECT code, event_name, COALESCE(vendor_name, \'\') AS vendor_name, status, sold_at, checked_in_at, created_at ' .
    'FROM tis_physical_tickets WHERE event_key = ? ORDER BY id ASC'
);
$query->execute([tis_staff_event_key()]);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="tis-physical-tickets-' . date('Y-m-d') . '.csv"');
header('Cache-Control: no-store, private');
$output = fopen('php://output', 'wb');
fwrite($output, "\xEF\xBB\xBF");
fputcsv($output, ['Code', 'Event', 'Vendor', 'Status', 'Sold at', 'Checked in at', 'Generated at']);
while ($ticket = $query->fetch()) {
    $row = [$ticket['code'], $ticket['event_name'], $ticket['vendor_name'], $ticket['status'], $ticket['sold_at'], $ticket['checked_in_at'], $ticket['created_at']];
    $row = array_map(static function (mixed $value): string {
        $value = (string) $value;
        return preg_match('/^[=+\-@]/', $value) ? "'" . $value : $value;
    }, $row);
    fputcsv($output, $row);
}
fclose($output);
exit;
