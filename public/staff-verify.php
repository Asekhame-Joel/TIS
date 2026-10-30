<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

use TIS\PhysicalTickets\PhysicalTicketService;

try {
    $staff = tis_require_staff();
} catch (Throwable $exception) {
    tis_log('Staff authentication database failure: ' . $exception->getMessage());
    http_response_code(503);
    ?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Verification unavailable | TIS</title><link rel="stylesheet" href="<?= tis_asset('/assets/css/staff.css') ?>"></head><body><main class="verify-container"><section class="verify-result error" role="alert"><span>Connection error</span><h1>Verification unavailable</h1><p>The ticket database could not be reached. Do not admit a ticket until the connection is restored and verification succeeds.</p></section></main></body></html><?php
    exit;
}
$eventKey = tis_staff_event_key();
$service = new PhysicalTicketService(tis_db());
$ticket = null;
$error = '';
$checkedInNow = false;
$uncheckedNow = false;
$code = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    tis_staff_verify_csrf();
    $code = PhysicalTicketService::normalizeCode((string) ($_POST['code'] ?? ''));
    if (!preg_match('/^TIS-[0-9]{8}$/', $code)) {
        $ticket = null;
    } else {
        try {
            if (($_POST['action'] ?? '') === 'check_in') {
                $before = $service->findByCode($code, $eventKey);
                if ($before !== null && $before['status'] === 'sold') {
                    $ticket = $service->checkIn($code, $eventKey, (int) $staff['id']);
                    $checkedInNow = $ticket !== null && !empty($ticket['_checked_in_now']);
                } else {
                    $ticket = $before;
                }
            } elseif (($_POST['action'] ?? '') === 'undo_check_in' && $staff['role'] === 'admin') {
                $ticket = $service->undoCheckIn($code, $eventKey);
                $uncheckedNow = $ticket !== null && !empty($ticket['_unchecked_now']);
            } else {
                $ticket = $service->findByCode($code, $eventKey);
            }
        } catch (Throwable $exception) {
            $error = 'The ticket database could not be reached. Do not admit this ticket until verification succeeds.';
            tis_log('Physical ticket verification failed: ' . $exception->getMessage());
        }
    }
}
$csrf = tis_staff_csrf_token();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Verify ticket | TIS Staff</title><link rel="stylesheet" href="<?= tis_asset('/assets/css/staff.css') ?>"></head>
<body><header class="staff-header"><a class="staff-brand" href="/staff-verify"><span class="staff-mark" aria-hidden="true"><i></i><i></i><i></i></span><span>TIS Entrance</span></a><nav><?php if ($staff['role'] === 'admin'): ?><a href="/staff-tickets">Physical</a><a href="/staff-online-tickets">Online sales</a><?php endif; ?><a aria-current="page" href="/staff-verify">Verify</a><form method="post" action="/staff-logout"><input type="hidden" name="csrf_token" value="<?= tis_staff_escape($csrf) ?>"><button type="submit">Sign out</button></form></nav></header>
<main class="verify-container"><p class="staff-eyebrow">Entrance verification</p><h1>Check a physical ticket</h1><p class="staff-muted">Checking a code does not use it. Only the Check In button admits the guest.</p>
  <form method="post" class="verify-form"><input type="hidden" name="csrf_token" value="<?= tis_staff_escape($csrf) ?>"><input type="hidden" name="action" value="lookup"><label for="ticket-code">Ticket code</label><div><input id="ticket-code" name="code" value="<?= tis_staff_escape($code) ?>" placeholder="TIS-74928163" autocomplete="off" autocapitalize="characters" spellcheck="false" required autofocus><button class="staff-button primary" type="submit">Verify</button></div></form>
  <?php if ($error !== ''): ?><section class="verify-result error" role="alert"><span>Connection error</span><h2>Verification unavailable</h2><p><?= tis_staff_escape($error) ?></p></section>
  <?php elseif (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $ticket === null): ?><section class="verify-result unknown" role="status"><span>Unknown code</span><h2>Invalid ticket</h2><p>This code does not exist for the current event.</p></section>
  <?php elseif (is_array($ticket)):
    $status = (string) $ticket['status']; ?>
    <section class="verify-result <?= tis_staff_escape($status) ?>" role="status">
      <?php if ($status === 'sold'): ?><span><?= $uncheckedNow ? 'Check-in undone' : 'Sold' ?></span><h2><?= $uncheckedNow ? 'Ticket returned to sold' : 'Valid ticket, ready for check-in' ?></h2><?php if ($ticket['attendee_name']): ?><p><strong><?= tis_staff_escape($ticket['attendee_name']) ?></strong></p><?php endif; ?><p><?= tis_staff_escape($ticket['code']) ?><?= $ticket['vendor_name'] ? ' · ' . tis_staff_escape($ticket['vendor_name']) : '' ?></p>
        <form method="post"><input type="hidden" name="csrf_token" value="<?= tis_staff_escape($csrf) ?>"><input type="hidden" name="action" value="check_in"><input type="hidden" name="code" value="<?= tis_staff_escape($ticket['code']) ?>"><button class="staff-button check-in" type="submit">Check In</button></form>
      <?php elseif ($status === 'checked_in'): ?><span><?= $checkedInNow ? 'Check-in complete' : 'Checked in' ?></span><h2><?= $checkedInNow ? 'Guest admitted' : 'Already used' ?></h2><?php if ($ticket['attendee_name']): ?><p><strong><?= tis_staff_escape($ticket['attendee_name']) ?></strong> · <?= tis_staff_escape($ticket['code']) ?></p><?php endif; ?><p>Previously checked in at <strong><?= tis_staff_escape($ticket['checked_in_at']) ?></strong><?php if ($ticket['checked_in_by_name']): ?> by <?= tis_staff_escape($ticket['checked_in_by_name']) ?><?php endif; ?>.</p><?php if ($staff['role'] === 'admin'): ?><form method="post"><input type="hidden" name="csrf_token" value="<?= tis_staff_escape($csrf) ?>"><input type="hidden" name="action" value="undo_check_in"><input type="hidden" name="code" value="<?= tis_staff_escape($ticket['code']) ?>"><button class="staff-button undo-check-in" type="submit">Undo Check-in</button></form><?php endif; ?>
      <?php else: ?><span>Unsold</span><h2>Sale needs confirmation</h2><p>The ticket exists, but it has not been marked sold. Do not admit it yet.</p><?php endif; ?>
    </section>
  <?php endif; ?>
  <p class="signed-in-note">Signed in as <?= tis_staff_escape($staff['display_name']) ?>.</p>
</main></body></html>
