<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

$staff = tis_require_staff('admin');
$db = tis_db();
$config = tis_config();
$filters = tis_online_ticket_filters($_GET, $config);
$whereSql = implode(' AND ', $filters['where']);

$totalsQuery = $db->query(
    "SELECT COUNT(*) AS total, SUM(status = 'success') AS successful, SUM(status = 'pending') AS pending, " .
    "SUM(status IN ('failed', 'abandoned', 'cancelled')) AS unsuccessful, " .
    "SUM(CASE WHEN status = 'success' THEN amount_kobo ELSE 0 END) AS received_kobo " .
    "FROM tis_orders WHERE payment_method IN ('online', 'bank_transfer')"
);
$totals = $totalsQuery->fetch() ?: [];

$count = $db->prepare('SELECT COUNT(*) FROM tis_orders o WHERE ' . $whereSql);
$count->execute($filters['params']);
$totalMatches = (int) $count->fetchColumn();
$perPage = 100;
$page = max(1, (int) ($_GET['page'] ?? 1));
$pages = max(1, (int) ceil($totalMatches / $perPage));
$page = min($page, $pages);
$offset = ($page - 1) * $perPage;

$orders = $db->prepare(
    'SELECT o.*, staff.display_name AS checked_in_by_name FROM tis_orders o ' .
    'LEFT JOIN tis_staff_users staff ON staff.id = o.checked_in_by WHERE ' . $whereSql .
    ' ORDER BY o.created_at DESC, o.id DESC LIMIT ' . $perPage . ' OFFSET ' . $offset
);
$orders->execute($filters['params']);
$orders = $orders->fetchAll();
$tierOptions = tis_ticket_tiers($config);
$queryBase = $_GET;
unset($queryBase['page']);
$buildUrl = static function (array $values) use ($queryBase): string {
    $query = array_filter(array_merge($queryBase, $values), static fn (mixed $value): bool => $value !== '');
    return '/staff-online-tickets' . ($query === [] ? '' : '?' . http_build_query($query));
};
$csrf = tis_staff_csrf_token();
$flash = tis_staff_take_flash();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Online tickets | TIS Staff</title><link rel="stylesheet" href="<?= tis_asset('/assets/css/staff.css') ?>"></head>
<body><header class="staff-header"><a class="staff-brand" href="/staff-online-tickets"><span class="staff-mark" aria-hidden="true"><i></i><i></i><i></i></span><span>TIS Staff</span></a>
<nav><a href="/staff-tickets">Physical</a><a aria-current="page" href="/staff-online-tickets">Online sales</a><a href="/staff-verify">Verify</a><form method="post" action="/staff-logout"><input type="hidden" name="csrf_token" value="<?= tis_staff_escape($csrf) ?>"><button type="submit">Sign out</button></form></nav></header>
<main class="staff-container">
  <div class="staff-title"><div><p class="staff-eyebrow">Online purchase records</p><h1>Online tickets</h1><p class="staff-muted">Search payment records, attendee details and ticket delivery information. Signed in as <?= tis_staff_escape($staff['display_name']) ?>.</p></div><div class="staff-actions"><form method="post" action="/staff-run-ticket-jobs.php"><input type="hidden" name="csrf_token" value="<?= tis_staff_escape($csrf) ?>"><button class="staff-button secondary" type="submit">Run ticket jobs now</button></form><a class="staff-button primary" href="/staff-online-ticket-export?<?= http_build_query($queryBase) ?>">Export CSV</a></div></div>
  <?php if ($flash): ?><div class="staff-alert <?= tis_staff_escape($flash['type']) ?>" role="status"><?= tis_staff_escape($flash['message']) ?></div><?php endif; ?>
  <section class="stat-grid online-stat-grid" aria-label="Online ticket totals">
    <article><span>All online orders</span><strong><?= (int) ($totals['total'] ?? 0) ?></strong></article>
    <article><span>Paid / successful</span><strong><?= (int) ($totals['successful'] ?? 0) ?></strong></article>
    <article><span>Pending</span><strong><?= (int) ($totals['pending'] ?? 0) ?></strong></article>
    <article><span>Unsuccessful</span><strong><?= (int) ($totals['unsuccessful'] ?? 0) ?></strong></article>
    <article><span>Successful revenue</span><strong class="money-total"><?= tis_staff_escape(tis_money((int) ($totals['received_kobo'] ?? 0))) ?></strong></article>
  </section>
  <section class="staff-panel"><div class="panel-heading"><div><p class="staff-eyebrow">Find a purchase</p><h2>Search and filter</h2></div></div>
    <label class="live-search-label" for="live-attendee-search">Quick attendee search <small>Starts automatically while you type</small><input id="live-attendee-search" form="online-filter-form" name="q" value="<?= tis_staff_escape($filters['q']) ?>" placeholder="Search by name, email, phone, reference or ticket number" autocomplete="off"></label>
    <form id="online-filter-form" method="get" class="online-filter-form"><label>Status<select name="status"><option value="">All statuses</option><?php foreach (tis_online_ticket_statuses() as $status): ?><option value="<?= tis_staff_escape($status) ?>"<?= $filters['status'] === $status ? ' selected' : '' ?>><?= tis_staff_escape(ucfirst($status)) ?></option><?php endforeach; ?></select></label><label>Category<select name="tier"><option value="">All categories</option><?php foreach ($tierOptions as $slug => $tier): ?><option value="<?= tis_staff_escape($slug) ?>"<?= $filters['tier'] === $slug ? ' selected' : '' ?>><?= tis_staff_escape($tier['label']) ?></option><?php endforeach; ?></select></label><label>Checkout method<select name="method"><option value="">All methods</option><option value="online"<?= $filters['method'] === 'online' ? ' selected' : '' ?>>Online payment</option><option value="bank_transfer"<?= $filters['method'] === 'bank_transfer' ? ' selected' : '' ?>>Bank transfer</option></select></label><label>From<input name="from" type="date" value="<?= tis_staff_escape($filters['from']) ?>"></label><label>To<input name="to" type="date" value="<?= tis_staff_escape($filters['to']) ?>"></label><div class="online-filter-actions"><button class="staff-button secondary" type="submit">Apply filters</button><a class="staff-button secondary" href="/staff-online-tickets">Clear</a></div></form>
  </section>
  <section class="staff-panel"><div class="panel-heading"><div><p class="staff-eyebrow">Results</p><h2><?= number_format($totalMatches) ?> matching order<?= $totalMatches === 1 ? '' : 's' ?></h2></div></div>
    <div class="table-scroll"><table class="staff-table online-orders-table"><thead><tr><th>Attendee</th><th>Category</th><th>Amount</th><th>Status</th><th>Admission</th><th>Method / channel</th><th>Ticket</th><th>Purchased</th><th></th></tr></thead><tbody>
      <?php if ($orders === []): ?><tr><td colspan="9" class="empty-state">No online ticket purchases match this view.</td></tr><?php endif; ?>
      <?php foreach ($orders as $order): ?><?php $searchable = strtolower(implode(' ', [(string) $order['full_name'], (string) $order['email'], (string) $order['phone'], (string) $order['reference'], (string) $order['ticket_number']])); ?><tr data-order-search="<?= tis_staff_escape($searchable) ?>"><td><strong><?= tis_staff_escape($order['full_name']) ?></strong><small><?= tis_staff_escape($order['email']) ?></small><small><?= tis_staff_escape($order['phone']) ?></small></td><td><?= tis_staff_escape(($tierOptions[$order['tier']]['label'] ?? $order['tier'])) ?></td><td><?= tis_staff_escape(tis_money((int) $order['amount_kobo'])) ?></td><td><span class="status-badge online-status <?= tis_staff_escape($order['status']) ?>"><?= tis_staff_escape($order['status']) ?></span></td><td><?php if ($order['checked_in_at']): ?><span class="status-badge checked_in">Checked in</span><small><?= tis_staff_escape($order['checked_in_at']) ?></small><?php elseif ($order['status'] === 'success' && $order['ticket_number']): ?><form method="post" action="/staff-online-ticket-checkin.php"><input type="hidden" name="csrf_token" value="<?= tis_staff_escape($csrf) ?>"><input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>"><input type="hidden" name="source" value="list"><button class="staff-button success table-action" type="submit">Check in</button></form><?php else: ?>—<?php endif; ?></td><td><?= tis_staff_escape($order['payment_method'] === 'bank_transfer' ? 'Bank transfer' : 'Online payment') ?><small><?= tis_staff_escape($order['channel'] ?: '—') ?></small></td><td><?php if ($order['ticket_number']): ?><strong class="ticket-code"><?= tis_staff_escape($order['ticket_number']) ?></strong><?php else: ?>—<?php endif; ?></td><td><?= tis_staff_escape($order['paid_at'] ?: $order['created_at']) ?><small><?= $order['paid_at'] ? 'Paid' : 'Created' ?></small></td><td><a class="staff-button secondary table-action" href="/staff-online-ticket?id=<?= (int) $order['id'] ?>">Details</a></td></tr><?php endforeach; ?>
    </tbody></table></div>
    <?php if ($pages > 1): ?><nav class="pagination" aria-label="Online ticket pages"><?php if ($page > 1): ?><a class="staff-button secondary" href="<?= tis_staff_escape($buildUrl(['page' => $page - 1])) ?>">Previous</a><?php endif; ?><span>Page <?= $page ?> of <?= $pages ?></span><?php if ($page < $pages): ?><a class="staff-button secondary" href="<?= tis_staff_escape($buildUrl(['page' => $page + 1])) ?>">Next</a><?php endif; ?></nav><?php endif; ?>
  </section>
</main><script>
(() => {
  const input = document.getElementById('live-attendee-search');
  const form = document.getElementById('online-filter-form');
  const rows = Array.from(document.querySelectorAll('[data-order-search]'));
  if (!input || !form) return;
  let timer;
  const filterRows = () => {
    const term = input.value.trim().toLowerCase();
    rows.forEach((row) => { row.hidden = term.length > 0 && !row.dataset.orderSearch.includes(term); });
  };
  input.addEventListener('input', () => {
    filterRows();
    window.clearTimeout(timer);
    timer = window.setTimeout(() => form.requestSubmit(), 500);
  });
})();
</script></body></html>
