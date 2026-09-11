<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title>Payment Status | The Intellectual Summit</title>
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
      <section class="form-card status-card is-loading" aria-live="polite" aria-busy="true" id="payment-status-card">
        <div class="status-visual" aria-hidden="true">
          <div class="status-loader" id="status-loader"><span>TIS</span></div>
          <div class="status-symbol" id="status-symbol">&#10003;</div>
        </div>
        <span class="eyebrow">Summit 2026 ticket</span>
        <h1 id="status-title">Confirming your payment</h1>
        <p class="status-message" id="status-message">Please wait while we securely verify your transaction with Paystack.</p>

        <div class="status-progress" id="status-progress" aria-hidden="true">
          <div class="status-progress-copy">
            <span><i></i><strong id="status-progress-label">Checking transaction</strong></span>
            <small id="status-progress-hint">Securely connected to Paystack</small>
          </div>
          <div class="status-progress-track"><span></span></div>
        </div>

        <p class="status-assurance" id="status-assurance">
          <span aria-hidden="true">&#9670;</span>
          <span id="status-assurance-text">Keep this page open. Bank transfers can take a little longer to confirm.</span>
        </p>

        <dl class="status-details" id="status-details">
          <div><dt>Ticket number</dt><dd id="status-ticket">&mdash;</dd></div>
          <div><dt>Tier</dt><dd id="status-tier">&mdash;</dd></div>
          <div><dt>Amount paid</dt><dd id="status-amount">&mdash;</dd></div>
          <div><dt>Payment reference</dt><dd id="status-reference">&mdash;</dd></div>
        </dl>

        <div class="status-actions">
          <a class="btn btn-primary" id="view-ticket" href="#">View or download ticket</a>
          <a class="btn btn-outline" id="download-ticket" href="#">Download PDF</a>
          <button class="btn btn-navy" id="check-again" type="button">Check again</button>
          <a class="btn btn-outline is-visible" href="/summit-2026">Return to Summit 2026</a>
        </div>
      </section>
    </div>
  </main>

  <?php tis_render_footer(['minimal' => true]); ?>

  <script src="/assets/js/payment-status.js"></script>
</body>
</html>
