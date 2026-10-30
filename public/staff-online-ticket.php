<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

$staff = tis_require_staff('admin');
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$order = null;
if ($id !== false && $id !== null && $id > 0) {
    $query = tis_db()->prepare("SELECT * FROM tis_orders WHERE id = ? AND payment_method IN ('online', 'bank_transfer') LIMIT 1");
    $query->execute([$id]);
    $candidate = $query->fetch();
    $order = is_array($candidate) ? $candidate : null;
}
if ($order === null) {
    http_response_code(404);
}
$config = tis_config();
$tier = is_array($order) ? tis_ticket_tier((string) $order['tier'], $config) : null;
$csrf = tis_staff_csrf_token();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= $order ? 'Online ticket details' : 'Order not found' ?> | TIS Staff</title><link rel="stylesheet" href="<?= tis_asset('/assets/css/staff.css') ?>"></head>
<body><header class="staff-header"><a class="staff-brand" href="/staff-online-tickets"><span class="staff-mark" aria-hidden="true"><i></i><i></i><i></i></span><span>TIS Staff</span></a><nav><a href="/staff-tickets">Physical</a><a aria-current="page" href="/staff-online-tickets">Online sales</a><a href="/staff-verify">Verify</a><form method="post" action="/staff-logout"><input type="hidden" name="csrf_token" value="<?= tis_staff_escape($csrf) ?>"><button type="submit">Sign out</button></form></nav></header>
<main class="staff-container"><?php if (!is_array($order)): ?><section class="staff-panel"><p class="staff-eyebrow">Online ticket</p><h1>Order not found</h1><p class="staff-muted">This record does not exist or is not an online purchase.</p><a class="staff-button primary" href="/staff-online-tickets">Return to online tickets</a></section><?php else: ?><div class="staff-title"><div><p class="staff-eyebrow">Online ticket details</p><h1><?= tis_staff_escape($order['full_name']) ?></h1><p class="staff-muted">Order <?= tis_staff_escape($order['reference']) ?></p></div><div class="staff-actions"><a class="staff-button secondary" href="/staff-online-tickets">Back to list</a><?php if ($order['status'] === 'success' && $order['ticket_token']): ?><a class="staff-button primary" href="/api/ticket/download.php?token=<?= rawurlencode((string) $order['ticket_token']) ?>&amp;download=1">Download PDF</a><?php endif; ?></div></div>
  <section class="detail-grid"><article class="staff-panel"><p class="staff-eyebrow">Attendee</p><h2>Contact details</h2><dl class="order-details"><div><dt>Full name</dt><dd><?= tis_staff_escape($order['full_name']) ?></dd></div><div><dt>Email</dt><dd><a href="mailto:<?= rawurlencode((string) $order['email']) ?>"><?= tis_staff_escape($order['email']) ?></a></dd></div><div><dt>Phone</dt><dd><?= tis_staff_escape($order['phone']) ?></dd></div><div><dt>Referrer</dt><dd><?= tis_staff_escape($order['referrer_name'] ?: '—') ?></dd></div></dl></article><article class="staff-panel"><p class="staff-eyebrow">Ticket</p><h2>Purchase details</h2><dl class="order-details"><div><dt>Status</dt><dd><span class="status-badge online-status <?= tis_staff_escape($order['status']) ?>"><?= tis_staff_escape($order['status']) ?></span></dd></div><div><dt>Category</dt><dd><?= tis_staff_escape($tier['label'] ?? $order['tier']) ?></dd></div><div><dt>Amount</dt><dd><?= tis_staff_escape(tis_money((int) $order['amount_kobo'])) ?></dd></div><div><dt>Ticket number</dt><dd><?= tis_staff_escape($order['ticket_number'] ?: 'Not issued') ?></dd></div><div><dt>Ticket email</dt><dd><?= tis_staff_escape($order['email_sent_at'] ?: 'Not yet recorded') ?></dd></div></dl></article></section>
  <section class="staff-panel"><p class="staff-eyebrow">Payment record</p><h2>Payment information</h2><dl class="order-details wide-details"><div><dt>Checkout method</dt><dd><?= tis_staff_escape($order['payment_method'] === 'bank_transfer' ? 'Bank transfer' : 'Online payment') ?></dd></div><div><dt>Payment channel</dt><dd><?= tis_staff_escape($order['channel'] ?: '—') ?></dd></div><div><dt>Payment reference</dt><dd class="ticket-code"><?= tis_staff_escape($order['reference']) ?></dd></div><div><dt>Gateway transaction ID</dt><dd><?= tis_staff_escape($order['paystack_id'] ?: '—') ?></dd></div><div><dt>Paid at</dt><dd><?= tis_staff_escape($order['paid_at'] ?: 'Not paid') ?></dd></div><div><dt>Created at</dt><dd><?= tis_staff_escape($order['created_at']) ?></dd></div><?php if ($order['failure_reason']): ?><div><dt>Failure reason</dt><dd><?= tis_staff_escape($order['failure_reason']) ?></dd></div><?php endif; ?></dl>
  </section><?php endif; ?></main></body></html>
