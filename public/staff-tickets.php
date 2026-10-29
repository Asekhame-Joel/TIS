<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

use TIS\PhysicalTickets\PhysicalTicketService;
use TIS\PhysicalTickets\ManualTicketService;
use TIS\Payment\TicketService;

$staff = tis_require_staff('admin');
$db = tis_db();
$service = new PhysicalTicketService($db);
$config = tis_config();
$eventKey = tis_staff_event_key();
$eventName = (string) $config['event']['name'];
$parseNaira = static function (mixed $value): int {
    $normalized = preg_replace('/[\s,₦]/u', '', trim((string) $value));
    if (!is_string($normalized) || !preg_match('/^(\d{1,9})(?:\.(\d{1,2}))?$/', $normalized, $matches)) {
        throw new RuntimeException('Enter a valid amount received.');
    }
    return ((int) $matches[1] * 100) + (int) str_pad($matches[2] ?? '', 2, '0');
};

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    tis_staff_verify_csrf();
    $action = (string) ($_POST['action'] ?? '');
    try {
        if ($action === 'record_manual_payment') {
            $manualService = new ManualTicketService($db, $config);
            $order = $manualService->create([
                'full_name' => $_POST['full_name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'referrer_name' => $_POST['referrer_name'] ?? '',
                'tier' => $_POST['tier'] ?? '',
                'amount_kobo' => $parseNaira($_POST['amount_naira'] ?? ''),
                'channel' => $_POST['channel'] ?? '',
                'manual_payment_reference' => $_POST['manual_payment_reference'] ?? '',
            ], $eventKey, $eventName, (int) $staff['id']);
            tis_staff_flash('success', 'Manual payment recorded. Ticket ' . $order['ticket_number'] . ' was assigned to ' . $order['full_name'] . '.');
            tis_staff_redirect('/staff-tickets?manual_ticket=' . rawurlencode((string) $order['ticket_token']));
        } elseif ($action === 'generate') {
            $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT);
            if ($quantity === false) {
                throw new RuntimeException('Enter a valid quantity.');
            }
            $codes = $service->generate((int) $quantity, $eventKey, $eventName, (int) $staff['id']);
            tis_staff_flash('success', count($codes) . ' unique physical ticket codes generated.');
        } elseif (in_array($action, ['assign_vendor', 'mark_sold', 'mark_unsold', 'check_in', 'undo_check_in'], true)) {
            $ids = array_values(array_unique(array_filter(
                array_map('intval', (array) ($_POST['ticket_ids'] ?? [])),
                static fn (int $id): bool => $id > 0
            )));
            if ($ids === [] || count($ids) > 1000) {
                throw new RuntimeException('Select at least one ticket.');
            }
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            if ($action === 'assign_vendor') {
                $vendor = trim((string) ($_POST['vendor_name'] ?? ''));
                $length = function_exists('mb_strlen') ? mb_strlen($vendor) : strlen($vendor);
                if ($length < 2 || $length > 100) {
                    throw new RuntimeException('Enter a vendor name between 2 and 100 characters.');
                }
                $update = $db->prepare(
                    "UPDATE tis_physical_tickets SET vendor_name = ?, updated_at = CURRENT_TIMESTAMP " .
                    "WHERE event_key = ? AND id IN ({$placeholders})"
                );
                $update->execute(array_merge([$vendor, $eventKey], $ids));
                tis_staff_flash('success', $update->rowCount() . ' ticket(s) assigned to ' . $vendor . '.');
            } else {
                $changed = $service->transitionSelected($ids, $eventKey, $action, (int) $staff['id']);
                $messages = [
                    'mark_sold' => ' ticket(s) marked sold.',
                    'mark_unsold' => ' ticket(s) marked unsold.',
                    'check_in' => ' ticket(s) checked in.',
                    'undo_check_in' => ' ticket check-in(s) undone and returned to Sold.',
                ];
                tis_staff_flash('success', $changed . $messages[$action]);
            }
        } else {
            throw new RuntimeException('Unknown ticket action.');
        }
    } catch (Throwable $error) {
        tis_staff_flash('error', $error->getMessage());
    }
    tis_staff_redirect('/staff-tickets');
}

$totalsQuery = $db->prepare(
    "SELECT COUNT(*) AS generated, SUM(status = 'unsold') AS unsold, SUM(status = 'sold') AS sold, " .
    "SUM(status = 'checked_in') AS checked_in FROM tis_physical_tickets WHERE event_key = ?"
);
$totalsQuery->execute([$eventKey]);
$totals = $totalsQuery->fetch() ?: [];

$search = trim((string) ($_GET['q'] ?? ''));
$statusFilter = strtolower(trim((string) ($_GET['status'] ?? '')));
if (!in_array($statusFilter, ['', 'unsold', 'sold', 'checked_in'], true)) {
    $statusFilter = '';
}
$where = ['t.event_key = ?'];
$params = [$eventKey];
if ($search !== '') {
    $where[] = '(t.code LIKE ? OR t.vendor_name LIKE ? OR o.full_name LIKE ? OR o.email LIKE ? OR t.manual_payment_reference LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
if ($statusFilter !== '') {
    $where[] = 't.status = ?';
    $params[] = $statusFilter;
}
$ticketsQuery = $db->prepare(
    'SELECT t.*, u.display_name AS checked_in_by_name, seller.display_name AS sold_by_name, ' .
    'o.full_name AS attendee_name, o.email AS attendee_email, o.tier AS attendee_tier ' .
    'FROM tis_physical_tickets t LEFT JOIN tis_staff_users u ON u.id = t.checked_in_by ' .
    'LEFT JOIN tis_staff_users seller ON seller.id = t.sold_by LEFT JOIN tis_orders o ON o.id = t.order_id WHERE ' . implode(' AND ', $where) .
    ' ORDER BY t.id DESC LIMIT 500'
);
$ticketsQuery->execute($params);
$tickets = $ticketsQuery->fetchAll();
$tierOptions = tis_ticket_tiers($config);
$manualOrder = null;
$manualToken = trim((string) ($_GET['manual_ticket'] ?? ''));
if (preg_match('/^[a-f0-9]{48}$/', $manualToken)) {
    $candidate = (new TicketService($db, $config))->findByToken($manualToken);
    if (is_array($candidate) && ($candidate['payment_method'] ?? '') === 'manual') {
        $manualOrder = $candidate;
    }
}
$flash = tis_staff_take_flash();
$csrf = tis_staff_csrf_token();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Physical tickets | TIS Staff</title><link rel="stylesheet" href="<?= tis_asset('/assets/css/staff.css') ?>"></head>
<body><header class="staff-header"><a class="staff-brand" href="/staff-tickets"><span class="staff-mark" aria-hidden="true"><i></i><i></i><i></i></span><span>TIS Staff</span></a>
<nav><a aria-current="page" href="/staff-tickets">Manage</a><a href="/staff-verify">Verify</a><form method="post" action="/staff-logout"><input type="hidden" name="csrf_token" value="<?= tis_staff_escape($csrf) ?>"><button type="submit">Sign out</button></form></nav></header>
<main class="staff-container">
  <div class="staff-title"><div><p class="staff-eyebrow">Physical ticket control</p><h1><?= tis_staff_escape($eventName) ?></h1><p class="staff-muted">Signed in as <?= tis_staff_escape($staff['display_name']) ?>.</p></div>
  <div class="staff-actions"><a class="staff-button secondary" href="/staff-ticket-print" target="_blank">Printable list</a><a class="staff-button primary" href="/staff-ticket-export">Export CSV</a></div></div>
  <?php if ($flash): ?><div class="staff-alert <?= $flash['type'] === 'success' ? 'success' : 'error' ?>" role="alert"><?= tis_staff_escape($flash['message']) ?></div><?php endif; ?>
  <?php if (is_array($manualOrder)): ?><section class="staff-panel manual-ticket-ready"><div><p class="staff-eyebrow">Ticket ready</p><h2><?= tis_staff_escape($manualOrder['full_name']) ?></h2><p class="staff-muted"><strong><?= tis_staff_escape($manualOrder['ticket_number']) ?></strong> has been assigned and marked Sold. You can download it now; email delivery uses the existing ticket worker.</p></div><div class="staff-actions"><a class="staff-button secondary" href="/ticket?token=<?= rawurlencode((string) $manualOrder['ticket_token']) ?>" target="_blank">View ticket</a><a class="staff-button primary" href="/api/ticket/download.php?token=<?= rawurlencode((string) $manualOrder['ticket_token']) ?>&amp;download=1">Download PDF</a></div></section><?php endif; ?>
  <section class="stat-grid" aria-label="Ticket totals">
    <article><span>Generated</span><strong><?= (int) ($totals['generated'] ?? 0) ?></strong></article>
    <article><span>Unsold</span><strong><?= (int) ($totals['unsold'] ?? 0) ?></strong></article>
    <article><span>Sold, awaiting entry</span><strong><?= (int) ($totals['sold'] ?? 0) ?></strong></article>
    <article><span>Checked in</span><strong><?= (int) ($totals['checked_in'] ?? 0) ?></strong></article>
  </section>
  <section class="staff-panel generate-panel"><div><p class="staff-eyebrow">Generate inventory</p><h2>Create unique ticket codes</h2><p class="staff-muted">New codes begin as Unsold and are attached to this event.</p></div>
    <form method="post" class="inline-form"><input type="hidden" name="csrf_token" value="<?= tis_staff_escape($csrf) ?>"><input type="hidden" name="action" value="generate"><label>Quantity<input name="quantity" type="number" min="1" max="1000" value="50" required></label><button class="staff-button primary" type="submit">Generate codes</button></form>
  </section>
  <section class="staff-panel"><div class="panel-heading"><div><p class="staff-eyebrow">Manual payment</p><h2>Assign a named ticket</h2><p class="staff-muted">After confirming payment, the system securely assigns one random Unsold code and creates the attendee's PDF ticket.</p></div></div>
    <form method="post" class="manual-payment-form"><input type="hidden" name="csrf_token" value="<?= tis_staff_escape($csrf) ?>"><input type="hidden" name="action" value="record_manual_payment">
      <label>Full name<input name="full_name" maxlength="100" autocomplete="name" required></label>
      <label>Email address<input name="email" type="email" maxlength="190" autocomplete="email" required></label>
      <label>Phone number<input name="phone" type="tel" maxlength="24" autocomplete="tel" required></label>
      <label>Referrer's name <small>Optional</small><input name="referrer_name" maxlength="100"></label>
      <label>Ticket category<select name="tier" data-manual-tier required><?php foreach ($tierOptions as $slug => $tier): ?><option value="<?= tis_staff_escape($slug) ?>" data-price="<?= number_format(((int) $tier['ticket_price_kobo']) / 100, 2, '.', '') ?>"><?= tis_staff_escape($tier['label']) ?> — <?= tis_staff_escape(tis_money((int) $tier['ticket_price_kobo'])) ?></option><?php endforeach; ?></select></label>
      <label>Amount received (NGN)<input name="amount_naira" data-manual-amount inputmode="decimal" required></label>
      <label>Payment method<select name="channel" required><option value="bank_transfer">Bank transfer</option><option value="cash">Cash</option><option value="pos">POS</option><option value="other">Other</option></select></label>
      <label>Payment reference <small>Optional</small><input name="manual_payment_reference" maxlength="100" placeholder="Bank narration or receipt number"></label>
      <div class="manual-payment-submit"><p class="staff-muted">Only submit this form after independently confirming the payment.</p><button class="staff-button success" type="submit">Assign ticket and create PDF</button></div>
    </form>
  </section>
  <section class="staff-panel">
    <div class="panel-heading"><div><p class="staff-eyebrow">Inventory</p><h2>Tickets</h2></div>
      <form method="get" class="filter-form"><input name="q" value="<?= tis_staff_escape($search) ?>" placeholder="Search code, attendee or vendor"><select name="status"><option value="">All statuses</option><option value="unsold"<?= $statusFilter === 'unsold' ? ' selected' : '' ?>>Unsold</option><option value="sold"<?= $statusFilter === 'sold' ? ' selected' : '' ?>>Sold</option><option value="checked_in"<?= $statusFilter === 'checked_in' ? ' selected' : '' ?>>Checked in</option></select><button class="staff-button secondary" type="submit">Search</button></form>
    </div>
    <form method="post" id="bulk-form"><input type="hidden" name="csrf_token" value="<?= tis_staff_escape($csrf) ?>">
      <div class="bulk-bar"><label>Vendor name<input name="vendor_name" maxlength="100" placeholder="e.g. Campus Team A"></label><button class="staff-button secondary" name="action" value="assign_vendor" type="submit">Assign selected</button><button class="staff-button primary" name="action" value="mark_sold" type="submit">Mark sold</button><button class="staff-button secondary" name="action" value="mark_unsold" type="submit">Mark unsold</button><button class="staff-button success" name="action" value="check_in" type="submit">Check in</button><button class="staff-button warning" name="action" value="undo_check_in" type="submit">Undo check-in</button></div>
      <div class="table-scroll"><table class="staff-table"><thead><tr><th><input type="checkbox" data-select-all aria-label="Select all visible tickets"></th><th>Code</th><th>Attendee</th><th>Vendor</th><th>Status</th><th>Payment reference</th><th>Sold</th><th>Check-in</th></tr></thead><tbody>
      <?php if ($tickets === []): ?><tr><td colspan="8" class="empty-state">No physical tickets match this view.</td></tr><?php endif; ?>
      <?php foreach ($tickets as $ticket): ?><tr>
        <td><input type="checkbox" name="ticket_ids[]" value="<?= (int) $ticket['id'] ?>" aria-label="Select <?= tis_staff_escape($ticket['code']) ?>"></td>
        <td><strong class="ticket-code"><?= tis_staff_escape($ticket['code']) ?></strong></td><td><?php if ($ticket['attendee_name']): ?><strong><?= tis_staff_escape($ticket['attendee_name']) ?></strong><small><?= tis_staff_escape($ticket['attendee_email']) ?></small><?php else: ?>—<?php endif; ?></td><td><?= tis_staff_escape($ticket['vendor_name'] ?: '—') ?></td>
        <td><span class="status-badge <?= tis_staff_escape($ticket['status']) ?>"><?= tis_staff_escape(str_replace('_', ' ', ucfirst($ticket['status']))) ?></span></td>
        <td><?= tis_staff_escape($ticket['manual_payment_reference'] ?: '—') ?></td><td><?= tis_staff_escape($ticket['sold_at'] ?: '—') ?><?php if ($ticket['sold_by_name']): ?><small><?= tis_staff_escape($ticket['sold_by_name']) ?></small><?php endif; ?></td><td><?php if ($ticket['checked_in_at']): ?><?= tis_staff_escape($ticket['checked_in_at']) ?><small><?= tis_staff_escape($ticket['checked_in_by_name'] ?: 'Unknown staff') ?></small><?php else: ?>—<?php endif; ?></td>
      </tr><?php endforeach; ?>
      </tbody></table></div>
    </form>
    <?php if (count($tickets) === 500): ?><p class="staff-muted table-note">Showing the latest 500 matching tickets. Use search to narrow the list.</p><?php endif; ?>
  </section>
</main><script>document.querySelector('[data-select-all]')?.addEventListener('change',function(){document.querySelectorAll('input[name="ticket_ids[]"]').forEach((box)=>box.checked=this.checked);});const manualTier=document.querySelector('[data-manual-tier]');const manualAmount=document.querySelector('[data-manual-amount]');const syncManualAmount=()=>{const option=manualTier?.selectedOptions[0];if(option&&manualAmount)manualAmount.value=option.dataset.price||'';};manualTier?.addEventListener('change',syncManualAmount);syncManualAmount();</script></body></html>
