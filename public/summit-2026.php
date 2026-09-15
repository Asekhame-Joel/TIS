<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';
$ticketTiers = tis_ticket_tiers();
$checkoutTiers = [];
foreach ($ticketTiers as $slug => $tier) {
    $checkoutTiers[$slug] = [
        'label' => (string) $tier['label'],
        'ticket_price' => tis_money((int) $tier['ticket_price_kobo']),
        'checkout_total' => tis_money((int) $tier['checkout_amount_kobo']),
    ];
}
$standard = $ticketTiers['standard'];
$premium = $ticketTiers['premium'];
$vip = $ticketTiers['vip'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Summit 2026 | The Intellectual Summit</title>
  <meta name="description" content="Book Early Bird-priced Standard, Premium or Deluxe tickets for The Intellectual Summit 2026 on 14 November at Okunozee Hall, Okada.">
  <meta name="theme-color" content="#1b2a4a">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="The Intellectual Summit">
  <meta property="og:title" content="Summit 2026 | The Intellectual Summit">
  <meta property="og:description" content="Book Early Bird-priced Standard, Premium or Deluxe tickets for The Intellectual Summit 2026 on 14 November at Okunozee Hall, Okada.">
  <link rel="canonical" href="/summit-2026">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..700;1,9..144,400..700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/responsive.css">
  <link rel="stylesheet" href="/assets/css/payment.css">
  <style>
    /* Summit 2026 page-specific styles */
    .summit-hero {
      position: relative;
      background:
        radial-gradient(900px 420px at 88% 0%, rgba(47, 91, 215, 0.09), transparent 60%),
        linear-gradient(180deg, var(--white), var(--paper));
      border-bottom: 1px solid var(--line);
      padding-block: clamp(3rem, 6vw, 5rem);
    }
    .summit-hero h1 { font-size: clamp(2.2rem, 4.6vw, 3.5rem); margin-bottom: 1rem; }
    .summit-hero .lead { max-width: 42rem; font-size: clamp(1.05rem, 1.6vw, 1.25rem); }

    .breadcrumb { display: flex; gap: 0.5rem; font-size: 0.82rem; color: var(--muted); margin-bottom: 1.25rem; flex-wrap: wrap; }
    .breadcrumb a { color: var(--muted); font-weight: 600; }
    .breadcrumb a:hover { color: var(--accent); }
    .breadcrumb span { color: var(--navy); font-weight: 600; }

    .details-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 1.25rem;
      margin-top: 2.5rem;
    }
    .detail-card {
      background: var(--white);
      border: 1px solid var(--line);
      border-radius: var(--radius);
      padding: 1.5rem;
      box-shadow: var(--shadow-sm);
      transition: transform 0.3s var(--ease), box-shadow 0.3s var(--ease);
    }
    .detail-card:hover { transform: translateY(-5px); box-shadow: var(--shadow); }
    .detail-card dt {
      font-size: 0.7rem;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      font-weight: 700;
      color: var(--muted);
      margin-bottom: 0.4rem;
    }
    .detail-card dd {
      margin: 0;
      font-family: var(--font-display);
      font-size: 1.15rem;
      font-weight: 600;
      color: var(--ink);
      line-height: 1.3;
    }

    .ticket-section {
      position: relative;
      overflow: hidden;
      background:
        radial-gradient(circle at 7% 12%, rgba(201, 164, 76, 0.11), transparent 24rem),
        linear-gradient(180deg, #fff 0%, var(--paper) 100%);
    }
    .ticket-section::before {
      content: "";
      position: absolute;
      top: -16rem;
      right: -9rem;
      width: 44rem;
      height: 25rem;
      border: 2.2rem solid rgba(201, 164, 76, 0.13);
      border-radius: 50%;
      pointer-events: none;
      rotate: 8deg;
    }
    .ticket-showcase {
      position: relative;
      overflow: hidden;
      border: 1px solid rgba(201, 164, 76, 0.45);
      border-radius: clamp(1.5rem, 4vw, 2.6rem);
      background: linear-gradient(180deg, #f8f3e8 0%, #fdfbf6 100%);
      box-shadow: 0 32px 80px -42px rgba(16, 28, 51, 0.52);
      isolation: isolate;
    }
    .ticket-showcase::after {
      content: "";
      position: absolute;
      inset: 0;
      border-radius: inherit;
      box-shadow: inset 0 0 0 6px rgba(255, 255, 255, 0.38);
      pointer-events: none;
      z-index: 5;
    }
    .tier-announcement {
      position: relative;
      min-height: 15rem;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 2rem;
      padding: clamp(2.3rem, 5vw, 4.5rem);
      text-align: left;
      color: rgba(255, 255, 255, 0.74);
      background:
        radial-gradient(circle at 78% 28%, rgba(230, 188, 92, 0.18), transparent 18rem),
        linear-gradient(135deg, #101c33 0%, #1b2a4a 72%, #253a62 100%);
      border: 0;
      border-radius: 0;
    }
    .tier-announcement::before,
    .tier-announcement::after {
      content: "";
      position: absolute;
      right: -8rem;
      width: 42rem;
      height: 15rem;
      border-radius: 50%;
      rotate: 7deg;
      pointer-events: none;
    }
    .tier-announcement::before {
      bottom: -12rem;
      border: 1.35rem solid rgba(230, 188, 92, 0.82);
    }
    .tier-announcement::after {
      bottom: -13.65rem;
      border: 1.6rem solid rgba(251, 250, 245, 0.98);
    }
    .tier-announcement-copy { position: relative; z-index: 2; max-width: 42rem; }
    .tier-announcement .eyebrow { color: var(--gold-bright); margin-bottom: 0.9rem; }
    .tier-announcement .eyebrow::before { background: var(--gold-bright); }
    .tier-announcement h2 {
      max-width: 38rem;
      margin: 0;
      color: var(--white);
      font-size: clamp(2rem, 4vw, 3.35rem);
      text-wrap: balance;
    }
    .ticket-event-seal {
      position: relative;
      z-index: 2;
      flex: 0 0 9rem;
      width: 9rem;
      aspect-ratio: 1;
      display: grid;
      place-content: center;
      text-align: center;
      border: 1px solid rgba(230, 188, 92, 0.72);
      border-radius: 50%;
      background: rgba(16, 28, 51, 0.58);
      box-shadow: inset 0 0 0 0.45rem rgba(255, 255, 255, 0.04), 0 18px 45px rgba(0, 0, 0, 0.18);
    }
    .ticket-event-seal span,
    .ticket-event-seal small {
      font-size: 0.61rem;
      font-weight: 800;
      letter-spacing: 0.18em;
      text-transform: uppercase;
    }
    .ticket-event-seal span { color: var(--gold-bright); }
    .ticket-event-seal strong {
      margin-block: 0.25rem 0.15rem;
      color: var(--white);
      font-family: var(--font-display);
      font-size: 1.75rem;
      font-weight: 600;
      line-height: 1;
    }
    .ticket-event-seal small { color: rgba(255, 255, 255, 0.66); }

    .tier-grid {
      position: relative;
      z-index: 2;
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: clamp(1rem, 2vw, 1.5rem);
      margin: 0;
      padding: clamp(1.2rem, 3.4vw, 3rem);
    }
    .tier-card {
      --tier-accent: var(--navy);
      position: relative;
      min-width: 0;
      min-height: 31rem;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      padding: 0;
      color: var(--body);
      background: rgba(255, 255, 255, 0.94);
      border: 1px solid rgba(27, 42, 74, 0.14);
      border-radius: 1.45rem;
      box-shadow: 0 22px 44px -34px rgba(16, 28, 51, 0.55);
      isolation: isolate;
      transition: transform 0.32s var(--ease), box-shadow 0.32s var(--ease), border-color 0.32s var(--ease);
    }
    .tier-card::before {
      content: "";
      position: absolute;
      inset: 0 auto 0 0;
      width: 0.32rem;
      background: var(--tier-accent);
      z-index: 3;
    }
    .tier-card::after {
      content: "";
      position: absolute;
      top: -7rem;
      right: -7rem;
      width: 15rem;
      aspect-ratio: 1;
      border: 1px solid rgba(201, 164, 76, 0.22);
      border-radius: 50%;
      box-shadow: 0 0 0 1.4rem rgba(201, 164, 76, 0.045), 0 0 0 2.8rem rgba(201, 164, 76, 0.035);
      pointer-events: none;
    }
    .tier-card.standard { --tier-accent: var(--navy-soft); }
    .tier-card.featured {
      --tier-accent: var(--gold-bright);
      color: rgba(255, 255, 255, 0.76);
      background:
        radial-gradient(circle at 100% 0%, rgba(230, 188, 92, 0.16), transparent 16rem),
        linear-gradient(155deg, var(--navy) 0%, var(--navy-deep) 100%);
      border-color: rgba(230, 188, 92, 0.72);
      box-shadow: 0 34px 65px -38px rgba(16, 28, 51, 0.9), 0 18px 40px -28px rgba(201, 164, 76, 0.72);
    }
    .tier-card.vip {
      --tier-accent: var(--gold);
      background:
        radial-gradient(circle at 100% 0%, rgba(201, 164, 76, 0.22), transparent 16rem),
        linear-gradient(145deg, #fffdf8 0%, #f5ead1 100%);
      border-color: rgba(201, 164, 76, 0.58);
    }
    .tier-card-header {
      position: relative;
      z-index: 2;
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 0.8rem;
      min-height: 4.4rem;
      padding: 1.25rem 1.35rem 1rem 1.55rem;
      border-bottom: 1px solid rgba(27, 42, 74, 0.1);
    }
    .featured .tier-card-header { border-bottom-color: rgba(255, 255, 255, 0.13); }
    .tier-badges { display: flex; flex-wrap: wrap; gap: 0.45rem; align-items: center; }
    .tier-card .tier-flag,
    .tier-popular {
      position: static;
      inset: auto;
      translate: none;
      display: inline-flex;
      align-items: center;
      min-height: 1.8rem;
      padding: 0.38rem 0.7rem;
      border-radius: var(--radius-pill);
      font-family: var(--font-sans);
      font-size: 0.58rem;
      font-weight: 800;
      letter-spacing: 0.15em;
      line-height: 1;
      text-transform: uppercase;
      white-space: nowrap;
    }
    .tier-card .tier-flag {
      color: var(--navy-deep);
      background: linear-gradient(120deg, #efd37f, var(--gold));
      box-shadow: 0 8px 18px -10px rgba(201, 164, 76, 0.8);
    }
    .tier-popular {
      color: var(--gold-bright);
      background: rgba(230, 188, 92, 0.1);
      border: 1px solid rgba(230, 188, 92, 0.36);
    }
    .tier-number {
      color: rgba(27, 42, 74, 0.25);
      font-family: var(--font-display);
      font-size: 1rem;
      font-weight: 600;
      letter-spacing: 0.08em;
    }
    .featured .tier-number { color: rgba(255, 255, 255, 0.34); }
    .tier-card-body {
      position: relative;
      z-index: 2;
      flex: 1;
      padding: 1.65rem 1.55rem 1.4rem;
    }
    .tier-brand-mark {
      width: 3.35rem;
      height: 3.35rem;
      display: flex;
      align-items: flex-end;
      gap: 0.2rem;
      margin-bottom: 1.35rem;
      padding: 0.72rem;
      border: 1px solid rgba(27, 42, 74, 0.1);
      border-radius: 1rem;
      color: var(--tier-accent);
      background: rgba(255, 255, 255, 0.76);
      box-shadow: 0 12px 24px -20px rgba(16, 28, 51, 0.7);
    }
    .featured .tier-brand-mark {
      color: var(--gold-bright);
      background: rgba(255, 255, 255, 0.07);
      border-color: rgba(255, 255, 255, 0.14);
    }
    .tier-brand-mark i {
      display: block;
      flex: 1;
      border-radius: 0.12rem 0.12rem 0 0;
    }
    .tier-brand-mark i:nth-child(1) { height: 42%; opacity: 0.72; }
    .tier-brand-mark i:nth-child(2) { height: 68%; opacity: 0.86; }
    .tier-brand-mark i:nth-child(3) { height: 100%; }
    .tier-brand-mark i:nth-child(1) { background: #737b8e; opacity: 1; }
    .tier-brand-mark i:nth-child(2) { background: #102746; opacity: 1; }
    .tier-brand-mark i:nth-child(3) { background: linear-gradient(180deg, #ecd986, #bd9333); opacity: 1; }
    .tier-kicker {
      display: block;
      margin-bottom: 0.5rem;
      color: var(--tier-accent);
      font-size: 0.65rem;
      font-weight: 800;
      letter-spacing: 0.16em;
      text-transform: uppercase;
    }
    .tier-card h3 {
      margin-bottom: 0.72rem;
      color: var(--navy-deep);
      font-size: clamp(1.55rem, 2vw, 1.9rem);
      letter-spacing: -0.035em;
    }
    .tier-card.featured h3 { color: var(--white); }
    .tier-card .tier-description { margin: 0; font-size: 0.93rem; line-height: 1.68; }
    .tier-benefits {
      display: grid;
      gap: 0.5rem;
      margin: 1.1rem 0 0;
      font-size: 0.84rem;
      line-height: 1.4;
    }
    .tier-benefits li {
      display: flex;
      align-items: flex-start;
      gap: 0.55rem;
    }
    .tier-benefits li::before {
      content: "✓";
      flex: 0 0 1.15rem;
      width: 1.15rem;
      height: 1.15rem;
      border-radius: 50%;
      color: var(--navy-deep);
      background: var(--tier-accent);
      font-size: 0.7rem;
      font-weight: 800;
      line-height: 1.15rem;
      text-align: center;
    }
    .featured .tier-benefits li::before { background: var(--gold-bright); }
    .tier-card-footer {
      position: relative;
      z-index: 2;
      padding: 1.35rem 1.55rem 1.55rem;
      border-top: 1px dashed rgba(27, 42, 74, 0.19);
    }
    .tier-card-footer::before,
    .tier-card-footer::after {
      content: "";
      position: absolute;
      top: -0.52rem;
      width: 1rem;
      height: 1rem;
      border-radius: 50%;
      background: #f9f5eb;
    }
    .tier-card-footer::before { left: -0.58rem; }
    .tier-card-footer::after { right: -0.58rem; }
    .featured .tier-card-footer { border-top-color: rgba(255, 255, 255, 0.19); }
    .featured .tier-card-footer::before,
    .featured .tier-card-footer::after { background: #f9f5eb; }
    .ticket-price-label {
      display: block;
      margin-bottom: 0.55rem;
      color: var(--muted);
      font-size: 0.62rem;
      font-weight: 800;
      letter-spacing: 0.14em;
      text-transform: uppercase;
    }
    .featured .ticket-price-label { color: rgba(255, 255, 255, 0.53); }
    .ticket-price-display { margin: 0; gap: 0.45rem; }
    .ticket-price-display span { color: var(--tier-accent); font-size: 0.7rem; }
    .ticket-price-display strong {
      color: var(--navy-deep);
      font-size: clamp(2.35rem, 4vw, 3.05rem);
      font-weight: 600;
      letter-spacing: -0.045em;
    }
    .featured .ticket-price-display strong { color: var(--white); }
    .ticket-price-meta {
      display: block;
      margin-top: 0.45rem;
      color: var(--muted);
      font-size: 0.69rem;
      line-height: 1.45;
    }
    .featured .ticket-price-meta { color: rgba(255, 255, 255, 0.52); }
    .tier-buy-button {
      justify-content: space-between;
      margin-top: 1.15rem;
      min-height: 3.3rem;
      padding-inline: 1.15rem;
    }
    .tier-buy-button .arrow { font-size: 1.05rem; }
    .featured .tier-buy-button {
      color: var(--navy-deep);
      border-color: transparent;
      background: linear-gradient(120deg, var(--gold-bright), var(--gold));
    }
    .vip .tier-buy-button {
      color: var(--white);
      border-color: var(--navy);
      background: var(--navy);
      box-shadow: 0 14px 28px -18px rgba(16, 28, 51, 0.8);
    }
    @media (hover: hover) {
      .tier-card:hover {
        transform: translateY(-0.55rem);
        border-color: rgba(201, 164, 76, 0.76);
        box-shadow: 0 34px 58px -36px rgba(16, 28, 51, 0.7);
      }
      .tier-card.featured:hover { box-shadow: 0 40px 70px -38px rgba(16, 28, 51, 0.95); }
    }

    .day-covers {
      display: grid;
      grid-template-columns: 1.3fr 0.7fr;
      gap: clamp(2rem, 5vw, 4rem);
      align-items: start;
    }
    .reservation-card {
      background: var(--navy);
      color: rgba(255, 255, 255, 0.78);
      border-radius: var(--radius);
      padding: clamp(1.75rem, 3vw, 2.5rem);
      position: sticky;
      top: 110px;
    }
    .reservation-card h3 { color: var(--white); margin-bottom: 0.75rem; }
    .reservation-card p { color: rgba(255, 255, 255, 0.75); margin-bottom: 1.5rem; }
    .reservation-card .btn-primary { width: 100%; }

    .cta-section {
      background: linear-gradient(150deg, var(--navy) 0%, var(--navy-deep) 100%);
      color: rgba(255, 255, 255, 0.78);
    }
    .cta-section h2 { color: var(--white); margin-bottom: 0.75rem; }
    .cta-section .lead { color: rgba(255, 255, 255, 0.8); max-width: 44rem; margin-bottom: 1.75rem; }

    @media (max-width: 900px) {
      .details-grid { grid-template-columns: 1fr; }
      .tier-announcement { min-height: auto; }
      .tier-grid { grid-template-columns: 1fr; max-width: 42rem; margin-inline: auto; }
      .tier-card { min-height: auto; }
      .day-covers { grid-template-columns: 1fr; }
      .reservation-card { position: static; }
    }
    @media (max-width: 620px) {
      .ticket-section .container { padding-inline: 0.85rem; }
      .ticket-showcase { border-radius: 1.5rem; }
      .tier-announcement { padding: 2.25rem 1.4rem 3rem; align-items: flex-start; }
      .tier-announcement::before,
      .tier-announcement::after { right: -22rem; }
      .ticket-event-seal { display: none; }
      .tier-grid { padding: 0.85rem; gap: 0.9rem; }
      .tier-card-header { padding: 1.1rem 1.1rem 0.9rem 1.3rem; }
      .tier-card-body { padding: 1.4rem 1.3rem 1.2rem; }
      .tier-card-footer { padding: 1.2rem 1.3rem 1.3rem; }
      .tier-badges { gap: 0.35rem; }
      .tier-card .tier-flag,
      .tier-popular { font-size: 0.54rem; padding-inline: 0.58rem; }
    }
    @media (prefers-reduced-motion: reduce) {
      .tier-card { transition: none; }
    }
  </style>
</head>
<body>
  <a class="skip-link" href="#main">Skip to content</a>
  <?php tis_render_header('summit-2026'); ?>
  <main id="main">

    <section class="summit-hero">
      <div class="container">
        <nav class="breadcrumb reveal" aria-label="Breadcrumb">
          <a href="/">Home</a>
          <span aria-hidden="true">/</span>
          <span>Summit 2026</span>
        </nav>
        <div class="reveal">
          <span class="eyebrow">Main event, 2026</span>
          <h1>The Intellectual Summit, live in Okada.</h1>
          <p class="lead">Choose the ticket package that includes the Summit essentials you need.</p>
        </div>
        <dl class="details-grid">
          <div class="detail-card reveal">
            <dt>Date</dt>
            <dd>14 November 2026</dd>
          </div>
          <div class="detail-card reveal reveal-delay-1">
            <dt>Venue</dt>
            <dd>Okunozee Hall, Okada</dd>
          </div>
          <div class="detail-card reveal reveal-delay-2">
            <dt>Format</dt>
            <dd>In person, one day</dd>
          </div>
        </dl>
      </div>
    </section>

    <section class="section ticket-section" id="tiers">
      <div class="container">
        <div class="ticket-showcase reveal">
          <div class="tier-announcement">
            <div class="tier-announcement-copy">
              <span class="eyebrow">Early Bird pricing now available</span>
              <h2>Choose your access and book securely.</h2>
            </div>
            <div class="ticket-event-seal" aria-label="Summit 2026, 14 November, Okada">
              <span>Summit 2026</span>
              <strong>14 Nov</strong>
              <small>Okada</small>
            </div>
          </div>

          <div class="tier-grid" id="ticket-options">
            <article class="tier-card standard" aria-labelledby="standard-tier-title">
              <div class="tier-card-header">
                <div class="tier-badges"><span class="tier-flag">Early Bird</span></div>
                <span class="tier-number" aria-hidden="true">01</span>
              </div>
              <div class="tier-card-body">
                <div class="tier-brand-mark" aria-hidden="true"><i></i><i></i><i></i></div>
                <span class="tier-kicker">Essential admission</span>
                <h3 id="standard-tier-title"><?= htmlspecialchars((string) $standard['label']) ?></h3>
                <p class="tier-description">Your essential Summit package.</p>
                <ul class="tier-benefits"><li>Event access</li><li>TIS-IUO curated food pack</li><li>Branded lanyard</li></ul>
              </div>
              <div class="tier-card-footer">
                <span class="ticket-price-label">Early Bird ticket price</span>
                <div class="ticket-price-display" aria-label="Standard costs 5,000 naira before checkout fees">
                  <span>NGN</span>
                  <strong><?= number_format(((int) $standard['ticket_price_kobo']) / 100, 0) ?></strong>
                </div>
                <small class="ticket-price-meta">Per attendee · checkout charges shown before payment</small>
                <button class="btn btn-outline btn-block tier-buy-button" type="button" data-open-checkout data-tier="standard"><span>Choose Standard</span><span class="arrow" aria-hidden="true">&rarr;</span></button>
              </div>
            </article>

            <article class="tier-card featured" aria-labelledby="premium-tier-title">
              <div class="tier-card-header">
                <div class="tier-badges">
                  <span class="tier-flag">Early Bird</span>
                  <span class="tier-popular">Most chosen</span>
                </div>
                <span class="tier-number" aria-hidden="true">02</span>
              </div>
              <div class="tier-card-body">
                <div class="tier-brand-mark" aria-hidden="true"><i></i><i></i><i></i></div>
                <span class="tier-kicker">Elevated experience</span>
                <h3 id="premium-tier-title"><?= htmlspecialchars((string) $premium['label']) ?></h3>
                <p class="tier-description">Everything in Standard, plus a TIS T-shirt.</p>
                <ul class="tier-benefits"><li>Event access</li><li>TIS-IUO curated food pack</li><li>Branded lanyard</li><li>TIS T-shirt</li></ul>
              </div>
              <div class="tier-card-footer">
                <span class="ticket-price-label">Early Bird ticket price</span>
                <div class="ticket-price-display" aria-label="Premium costs 10,000 naira before checkout fees">
                  <span>NGN</span>
                  <strong><?= number_format(((int) $premium['ticket_price_kobo']) / 100, 0) ?></strong>
                </div>
                <small class="ticket-price-meta">Per attendee · checkout charges shown before payment</small>
                <button class="btn btn-primary btn-block tier-buy-button" type="button" data-open-checkout data-tier="premium"><span>Choose Premium</span><span class="arrow" aria-hidden="true">&rarr;</span></button>
              </div>
            </article>

            <article class="tier-card vip" aria-labelledby="vip-tier-title">
              <div class="tier-card-header">
                <div class="tier-badges"><span class="tier-flag">Early Bird</span></div>
                <span class="tier-number" aria-hidden="true">03</span>
              </div>
              <div class="tier-card-body">
                <div class="tier-brand-mark" aria-hidden="true"><i></i><i></i><i></i></div>
                <span class="tier-kicker">Signature access</span>
                <h3 id="vip-tier-title"><?= htmlspecialchars((string) $vip['label']) ?></h3>
                <p class="tier-description">The complete package, including priority seating.</p>
                <ul class="tier-benefits"><li>Event access</li><li>TIS-IUO curated food pack</li><li>Branded lanyard</li><li>Souvenir item</li><li>TIS T-shirt</li><li>Priority seating</li></ul>
              </div>
              <div class="tier-card-footer">
                <span class="ticket-price-label">Early Bird ticket price</span>
                <div class="ticket-price-display" aria-label="Deluxe costs 15,000 naira before checkout fees">
                  <span>NGN</span>
                  <strong><?= number_format(((int) $vip['ticket_price_kobo']) / 100, 0) ?></strong>
                </div>
                <small class="ticket-price-meta">Per attendee · checkout charges shown before payment</small>
                <button class="btn btn-outline btn-block tier-buy-button" type="button" data-open-checkout data-tier="vip"><span>Choose Deluxe</span><span class="arrow" aria-hidden="true">&rarr;</span></button>
              </div>
            </article>
          </div>
        </div>
      </div>
    </section>

    <section class="section section-cream">
      <div class="container day-covers">
        <div class="reveal">
          <span class="eyebrow">What the day covers</span>
          <h2>Every ticket includes thoughtful Summit essentials.</h2>
          <p class="lead">All tiers include event access, a TIS-IUO curated food pack, and a branded lanyard. Premium adds a TIS T-shirt; Deluxe also includes a souvenir item and priority seating.</p>
          <ul class="check-list" style="margin-top:1.75rem">
            <li>Standard: event access, food pack, and branded lanyard</li>
            <li>Premium: all Standard items plus a TIS T-shirt</li>
            <li>Deluxe: all Premium items plus a souvenir item</li>
            <li>Deluxe includes priority seating</li>
          </ul>
        </div>
        <aside class="reservation-card reveal reveal-delay-1" aria-label="Reserve a seat">
          <span class="eyebrow" style="color:var(--gold-bright)">Reserving a seat</span>
          <h3>Seats are confirmed only after payment. Every attendee receives a unique ticket by email.</h3>
          <a class="btn btn-primary" href="#ticket-options">Choose your ticket <span class="arrow" aria-hidden="true">&rarr;</span></a>
        </aside>
      </div>
    </section>

  </main>

  <dialog class="checkout-dialog" id="ticket-checkout" aria-labelledby="checkout-title">
    <div class="checkout-shell">
      <button class="checkout-close" type="button" data-close-checkout aria-label="Close checkout">&times;</button>
      <div class="checkout-heading">
        <span class="eyebrow">Secure checkout</span>
        <h2 id="checkout-title">Choose a ticket</h2>
        <p>14 November 2026 &middot; Okunozee Hall, Okada</p>
      </div>

      <div class="checkout-layout">
        <form id="ticket-checkout-form" class="checkout-form" novalidate>
          <input id="checkout-tier" name="tier" type="hidden" value="premium">
          <div class="field">
            <label for="checkout-name">Full name</label>
            <input id="checkout-name" name="full_name" type="text" autocomplete="name" maxlength="100" required>
          </div>
          <div class="field">
            <label for="checkout-email">Email address</label>
            <input id="checkout-email" name="email" type="email" autocomplete="email" maxlength="190" required>
            <span class="field-hint">Your ticket will be sent to this address.</span>
          </div>
          <div class="field">
            <label for="checkout-phone">Phone number</label>
            <input id="checkout-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" maxlength="24" placeholder="0801 234 5678" required>
          </div>
          <div class="checkout-honeypot" aria-hidden="true">
            <label for="checkout-website">Website</label>
            <input id="checkout-website" name="website" type="text" tabindex="-1" autocomplete="off">
          </div>

          <fieldset class="payment-options">
            <legend>Choose how to pay</legend>
            <button class="payment-option" type="submit" name="payment_method" value="online">
              <span class="payment-option-icon" aria-hidden="true">&#9670;</span>
              <span><strong>Pay with Card or USSD</strong><small>Continue to secure Paystack checkout</small></span>
              <span class="payment-option-arrow" aria-hidden="true">&rarr;</span>
            </button>
            <button class="payment-option" type="submit" name="payment_method" value="bank_transfer">
              <span class="payment-option-icon" aria-hidden="true">&#8644;</span>
              <span><strong>Pay by Bank Transfer</strong><small>Generate a temporary Paystack account</small></span>
              <span class="payment-option-arrow" aria-hidden="true">&rarr;</span>
            </button>
          </fieldset>
          <p class="checkout-status" id="checkout-status" role="status" aria-live="polite"></p>
          <p class="payment-security">Payments are processed by Paystack. A ticket is issued only after payment is verified.</p>
        </form>

        <aside class="order-summary" aria-label="Order summary">
          <h3>Order summary</h3>
          <dl>
            <div><dt id="summary-tier"><?= htmlspecialchars((string) $premium['label']) ?></dt><dd id="summary-price"><?= tis_money((int) $premium['ticket_price_kobo']) ?></dd></div>
            <div class="order-total"><dt>Total + Charges</dt><dd id="summary-total"><?= tis_money((int) $premium['checkout_amount_kobo']) ?></dd></div>
          </dl>
          <p id="summary-note">Use a valid email address to receive your <?= htmlspecialchars((string) $premium['label']) ?> ticket after successful payment.</p>
        </aside>
      </div>
    </div>
  </dialog>

  <?php tis_render_footer(); ?>


  <script id="ticket-tier-data" type="application/json"><?= json_encode($checkoutTiers, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?></script>
  <script src="/assets/js/main.js"></script>
  <script src="/assets/js/payment.js"></script>
</body>
</html>
