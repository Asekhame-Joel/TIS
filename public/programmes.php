<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Programmes | The Intellectual Summit</title>
  <meta name="description" content="Purpose, Voice and Reach: the three outcome driven tracks behind every talk, workshop and mentorship session at The Intellectual Summit.">
  <meta name="theme-color" content="#1b2a4a">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="The Intellectual Summit">
  <meta property="og:title" content="Programmes | The Intellectual Summit">
  <meta property="og:description" content="Purpose, Voice and Reach: the three outcome driven tracks behind every talk, workshop and mentorship session at The Intellectual Summit.">
  <link rel="canonical" href="/programmes">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..700;1,9..144,400..700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/responsive.css">
</head>
<body>
  <a class="skip-link" href="#main">Skip to content</a>
  <?php tis_render_header('programmes'); ?>
  <main id="main">
    <section class="hero hero-page">
      <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb"><a href="/">Home</a> <span aria-hidden="true">/</span> <span>Programmes</span></nav>
        <span class="eyebrow">What we build</span>
        <h1>Programmes designed around three outcomes.</h1>
        <p class="lead">Every talk, workshop, and mentorship track at TIS is built around purpose, voice and reach.</p>
      </div>
    </section>

    <section class="section section-white">
      <div class="container">
        <div class="grid grid-3">
          <article class="card reveal reveal-delay-0">
            <div class="icon-badge" aria-hidden="true"><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polygon points="16 8 14 14 8 16 10 10 16 8"/></svg></div>
            <span class="card-tag">Purpose</span>
            <h3>Clarity of direction</h3>
            <p>Guided sessions that help young people name what they actually want, instead of borrowing someone else's definition of success.</p>
            <div class="card-foot"><a class="btn btn-ghost" href="/programme-purpose">Explore Purpose <span class="arrow" aria-hidden="true">&rarr;</span></a></div>
          </article>
          <article class="card reveal reveal-delay-1">
            <div class="icon-badge blue" aria-hidden="true"><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="3" width="6" height="11" rx="3"/><path d="M5 11a7 7 0 0 0 14 0"/><line x1="12" y1="18" x2="12" y2="21"/></svg></div>
            <span class="card-tag">Voice</span>
            <h3>The courage to speak</h3>
            <p>Practical public speaking and communication training, tested in front of a real room, not a mirror.</p>
            <div class="card-foot"><a class="btn btn-ghost" href="/programme-voice">Explore Voice <span class="arrow" aria-hidden="true">&rarr;</span></a></div>
          </article>
          <article class="card reveal reveal-delay-2">
            <div class="icon-badge teal" aria-hidden="true"><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18a15 15 0 0 1 0-18"/></svg></div>
            <span class="card-tag">Reach</span>
            <h3>A wider standard</h3>
            <p>Exposure to ideas, mentors, and conversations that stretch ambition past the local and toward the global.</p>
            <div class="card-foot"><a class="btn btn-ghost" href="/programme-reach">Explore Reach <span class="arrow" aria-hidden="true">&rarr;</span></a></div>
          </article>
        </div>
      </div>
    </section>

    <section class="section section-cream">
      <div class="container">
        <div class="section-head reveal">
          <span class="eyebrow">How the tracks fit together</span>
          <h2>Direction, then delivery, then a wider standard.</h2>
          <p class="lead">The three tracks are sequential in effect, even when experienced in a single day.</p>
        </div>
        <div class="steps">
          <div class="step reveal"><h3>Purpose sets the direction</h3><p>You cannot argue well for something you have not decided on. Purpose comes first because it makes everything after it specific.</p></div>
          <div class="step reveal"><h3>Voice makes it public</h3><p>A direction stated out loud, defended in front of a room, and refined by feedback is far harder to abandon quietly.</p></div>
          <div class="step reveal"><h3>Reach raises the bar</h3><p>Placed next to mentors and ideas from beyond your circle, the same ambition is measured against a wider standard.</p></div>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="cta-banner reveal">
          <div class="cta-inner">
            <div>
              <span class="eyebrow">Next step</span>
              <h2>Not sure which track fits you?</h2>
              <p class="lead">Tell us where you are right now and we will point you to the part of the day built for it.</p>
            </div>
            <div class="btn-row" style="flex-direction:column;align-items:stretch">
              <a class="btn btn-primary btn-block" href="/contact">Request more information <span class="arrow" aria-hidden="true">&rarr;</span></a>
              <a class="btn btn-outline btn-block" href="/summit-2026#tiers">See the tiers</a>
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
