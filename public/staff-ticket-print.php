<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

tis_require_staff('admin');
$event = tis_config()['event'];
$query = tis_db()->prepare(
    'SELECT t.code, t.vendor_name, t.status, o.full_name AS attendee_name FROM tis_physical_tickets t ' .
    'LEFT JOIN tis_orders o ON o.id = t.order_id WHERE t.event_key = ? ORDER BY t.id ASC'
);
$query->execute([tis_staff_event_key()]);
$tickets = $query->fetchAll();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Physical ticket codes</title><link rel="stylesheet" href="<?= tis_asset('/assets/css/staff.css') ?>"></head>
<body class="print-page"><header><div><h1><?= tis_staff_escape($event['name']) ?></h1><p><?= tis_staff_escape($event['date']) ?> · <?= tis_staff_escape($event['venue']) ?></p></div><button class="staff-button primary no-print" onclick="window.print()">Print list</button></header>
<main class="print-grid"><?php foreach ($tickets as $ticket): ?><article><strong><?= tis_staff_escape($ticket['code']) ?></strong><span><?= tis_staff_escape($ticket['attendee_name'] ?: ($ticket['vendor_name'] ?: 'Unassigned')) ?></span><small><?= tis_staff_escape(str_replace('_', ' ', ucfirst($ticket['status']))) ?></small></article><?php endforeach; ?></main>
<?php if ($tickets === []): ?><p>No physical ticket codes have been generated.</p><?php endif; ?></body></html>
