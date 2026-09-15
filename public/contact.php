<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Contact | The Intellectual Summit</title>
  <meta name="description" content="Contact The Intellectual Summit to reserve a seat for the 2026 Summit at Okunozee Hall, Okada, or to ask about the Purpose, Voice and Reach programmes.">
  <meta name="theme-color" content="#1b2a4a">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="The Intellectual Summit">
  <meta property="og:title" content="Contact | The Intellectual Summit">
  <meta property="og:description" content="Contact The Intellectual Summit to reserve a seat for the 2026 Summit at Okunozee Hall, Okada, or to ask about the Purpose, Voice and Reach programmes.">
  <link rel="canonical" href="/contact">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..700;1,9..144,400..700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/responsive.css">
</head>
<body>
  <a class="skip-link" href="#main">Skip to content</a>
  <?php tis_render_header('contact'); ?>
  <main id="main">
    <section class="hero hero-page">
      <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb"><a href="/">Home</a> <span aria-hidden="true">/</span> <span>Contact</span></nav>
        <span class="eyebrow">Contact</span>
        <h1>Reserve a seat or ask a question.</h1>
        <p class="lead">Tell us what you need and which tier you are considering, and we will come back to you.</p>
      </div>
    </section>

    <section class="section section-white">
      <div class="container split" style="align-items:start">
        <div class="reveal">
          <form
  id="enquiry-form"
  class="form-card"
  method="POST"
  action="https://api.web3forms.com/submit"
>
  <input
    type="hidden"
    name="access_key"
    value="5b734283-020e-4ff4-9a90-dd0743acae91"
  >

  <input
    type="hidden"
    name="subject"
    value="New 2026 Summit enquiry"
  >

  <!-- Spam protection -->
  <input
    type="checkbox"
    name="botcheck"
    class="hidden"
    style="display:none"
    tabindex="-1"
    autocomplete="off"
  >

  <h2 style="font-size:1.6rem">Enquiry form</h2>

  <p style="margin-bottom:1.75rem">
    All fields marked required help us answer properly the first time.
  </p>

  <div class="form-row">
    <div class="field">
      <label for="name">Full name</label>
      <input
        id="name"
        name="name"
        type="text"
        required
        placeholder="Your name"
      >
    </div>

    <div class="field">
      <label for="email">Email</label>
      <input
        id="email"
        name="email"
        type="email"
        required
        placeholder="you@example.com"
      >
    </div>
  </div>

  <div class="form-row">
    <div class="field">
      <label for="phone">Phone (optional)</label>
      <input
        id="phone"
        name="phone"
        type="tel"
        placeholder="Best number to reach you"
      >
    </div>

    <div class="field">
      <label for="tier">Tier of interest</label>
      <select id="tier" name="tier">
        <option value="Standard">Standard</option>
        <option value="Premium">Premium</option>
        <option value="Deluxe">Deluxe</option>
        <option value="Not sure yet">Not sure yet</option>
      </select>
    </div>
  </div>

  <div class="field">
    <label for="topic">What is this about?</label>
    <select id="topic" name="topic">
      <option value="Reserving a seat for the 2026 Summit">
        Reserving a seat for the 2026 Summit
      </option>
      <option value="Programme information (Purpose, Voice, Reach)">
        Programme information (Purpose, Voice, Reach)
      </option>
      <option value="Speaking or mentoring at the Summit">
        Speaking or mentoring at the Summit
      </option>
      <option value="Something else">
        Something else
      </option>
    </select>
  </div>

  <div class="field">
    <label for="message">Your message</label>
    <textarea
      id="message"
      name="message"
      required
      placeholder="Tell us where you are right now and what you want from the day."
    ></textarea>
  </div>

  <button class="btn btn-primary btn-block" type="submit">
    Send enquiry
    <span class="arrow" aria-hidden="true">&rarr;</span>
  </button>

  <p
  id="form-status"
  role="status"
  aria-live="polite"
  style="display:none; margin-top:1rem;"
></p>
</form>
        </div>
        <div class="reveal reveal-delay-1">
          <span class="eyebrow">Event details</span>
          <h2>Where to find us.</h2>
          <div class="info-list" style="margin-top:1.5rem">
            <div class="info-item"><div class="icon-badge" aria-hidden="true"><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="3"/><path d="M3 10h18M8 3v4M16 3v4"/></svg></div><div><h4>Summit date</h4><p>14 November 2026</p></div></div>
            <div class="info-item"><div class="icon-badge blue" aria-hidden="true"><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s7-6.2 7-11a7 7 0 1 0-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.6"/></svg></div><div><h4>Venue</h4><p>Okunozee Hall, Okada</p></div></div>
            <div class="info-item"><div class="icon-badge teal" aria-hidden="true"><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18a15 15 0 0 1 0-18"/></svg></div><div><h4>Location</h4><p>Okada, Nigeria &middot; 6.9&deg;N, 5.8&deg;E</p></div></div>
            <div class="info-item"><div class="icon-badge" aria-hidden="true"><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15.5 14"/></svg></div><div><h4>Format</h4><p>In person, one day</p></div></div>
          </div>
          <div class="card" style="margin-top:2rem">
            <h3>Reserving a seat</h3>
            <p>Early Bird pricing is available across Standard, Premium and Deluxe. For immediate confirmation, book on the Summit 2026 tickets page.</p>
            <div class="card-foot"><a class="btn btn-outline btn-block" href="/summit-2026#tiers">Compare the tiers</a></div>
          </div>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="cta-banner reveal">
          <div class="cta-inner">
            <div>
              <span class="eyebrow">Next step</span>
              <h2>The room fills before the door opens.</h2>
              <p class="lead">Reserve early: seating for 14 November 2026 is limited in every tier.</p>
            </div>
            <div class="btn-row" style="flex-direction:column;align-items:stretch">
              <a class="btn btn-primary btn-block" href="/summit-2026#tiers">Reserve a seat <span class="arrow" aria-hidden="true">&rarr;</span></a>
              <a class="btn btn-outline btn-block" href="/faq">Read the FAQ</a>
            </div>
          </div>
        </div>
      </div>
    </section>


  </main>

  <?php tis_render_footer(); ?>
  <script src="/assets/js/main.js"></script>
</body>
</html>
