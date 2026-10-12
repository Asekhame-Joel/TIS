<?php
declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

use TIS\Payment\TicketService;

$token = trim((string) ($_GET['token'] ?? ''));
$order = null;
$tier = null;

if (preg_match('/^[a-f0-9]{48}$/', $token)) {
    try {
        $config = tis_config();
        $order = (new TicketService(tis_db(), $config))->findByToken($token);
        if (is_array($order)) {
            $tier = tis_ticket_tier((string) $order['tier'], $config);
        }
    } catch (Throwable $error) {
        tis_log($error->getMessage());
    }
}

if (!is_array($order) || !is_array($tier)) {
    http_response_code(404);
}

$safe = static fn (mixed $value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);
$downloadUrl = '/api/ticket/download.php?token=' . rawurlencode($token) . '&download=1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= $order ? 'Your Summit 2026 Ticket' : 'Ticket Not Found' ?> | The Intellectual Summit</title>
  <meta name="theme-color" content="#1b2a4a">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..700;1,9..144,400..700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/responsive.css">
  <link rel="stylesheet" href="/assets/css/payment.css">
</head>
<body class="payment-status-page">
  <a class="skip-link" href="#main">Skip to content</a>
  <?php tis_render_header('summit-2026', ['minimal' => true]); ?>

  <main id="main" class="section-cream">
    <div class="container">
      <?php if (is_array($order) && is_array($tier)): ?>
        <section class="form-card status-card is-success" aria-labelledby="ticket-page-title">
          <div class="status-visual" aria-hidden="true"><div class="status-symbol">&#10003;</div></div>
          <span class="eyebrow">Verified Summit 2026 ticket</span>
          <h1 id="ticket-page-title"><?= $safe($tier['label']) ?></h1>
          <p class="status-message">Your ticket is ready. Download the PDF below and keep it private.</p>

          <dl class="status-details is-visible">
            <div><dt>Attendee</dt><dd><?= $safe($order['full_name']) ?></dd></div>
            <div><dt>Ticket number</dt><dd><?= $safe($order['ticket_number']) ?></dd></div>
            <div><dt>Event date</dt><dd><?= $safe($config['event']['date']) ?></dd></div>
            <div><dt>Venue</dt><dd><?= $safe($config['event']['venue']) ?></dd></div>
            <div><dt>Amount paid</dt><dd><?= $safe(tis_money((int) $order['amount_kobo'])) ?></dd></div>
            <div><dt>Payment reference</dt><dd><?= $safe($order['reference']) ?></dd></div>
          </dl>

          <p class="status-assurance">
            <span aria-hidden="true">&#9670;</span>
            <span>If the browser does not show the downloaded file immediately, check its Downloads folder. The original ticket email also contains this PDF as an attachment.</span>
          </p>

          <div class="status-actions">
            <a class="btn btn-primary is-visible" href="<?= $safe($downloadUrl) ?>" download="<?= $safe($order['ticket_number']) ?>.pdf">Download ticket PDF</a>
            <a class="btn btn-outline is-visible" href="/summit-2026">Return to Summit 2026</a>
          </div>
        </section>
      <?php else: ?>
        <section class="form-card status-card is-error" aria-labelledby="ticket-page-title">
          <div class="status-visual" aria-hidden="true"><div class="status-symbol">!</div></div>
          <span class="eyebrow">Summit 2026 ticket</span>
          <h1 id="ticket-page-title">Ticket not found</h1>
          <p class="status-message">This ticket link is invalid or the payment has not been confirmed.</p>
          <div class="status-actions">
            <a class="btn btn-primary is-visible" href="/summit-2026">Return to Summit 2026</a>
            <a class="btn btn-outline is-visible" href="mailto:tickets@theintellectualsummit.com">Contact the ticket team</a>
          </div>
        </section>
      <?php endif; ?>
    </div>
  </main>

  <?php tis_render_footer(['minimal' => true]); ?>
</body>
</html>
