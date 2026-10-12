<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reach — A wider standard | The Intellectual Summit</title>
  <meta name="description" content="Exposure to ideas, mentors, and conversations that stretch ambition past the local and toward the global.">
  <meta name="theme-color" content="#1b2a4a">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="The Intellectual Summit">
  <meta property="og:title" content="Reach — A wider standard | The Intellectual Summit">
  <meta property="og:description" content="Exposure to ideas, mentors, and conversations that stretch ambition past the local and toward the global.">
  <link rel="canonical" href="/programme-reach">
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
        <nav class="breadcrumb" aria-label="Breadcrumb"><a href="/">Home</a> <span aria-hidden="true">/</span> <span>Reach</span></nav>
        <span class="eyebrow">Programme &middot; Reach</span>
        <h1>A wider standard</h1>
        <p class="lead">Exposure to ideas, mentors, and conversations that stretch ambition past the local and toward the global.</p>
      </div>
    </section>

    <section class="section section-white">
      <div class="container split">
        <div class="reveal">
          <span class="eyebrow">What it is</span>
          <h2>Reach: why it exists</h2>
          <p>Reach exists because ambition is shaped by the standard you are measured against. TIS was built so that a young person's postcode does not decide the size of that standard.</p>
          <p>The Reach track brings students, young professionals and mentors into the same conversation, so that the benchmark in the room is not the local average but a global one.</p><p>This is where talks, workshops and mentorship overlap: participants meet people working to a wider standard and see, concretely, what that standard asks of them.</p>
        </div>
        <div class="reveal reveal-delay-1">
          <div class="card">
            <div class="icon-badge teal" aria-hidden="true"><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18a15 15 0 0 1 0-18"/></svg></div>
            <h3>What you get</h3>
            <ul class="check-list" style="margin-top:1rem"><li>Measure your work against a global standard, not a local one</li><li>Sit in conversation with mentors and working professionals</li><li>Encounter ideas outside your immediate circle</li><li>Build relationships that outlast a single session</li></ul>
            <div class="card-foot"><a class="btn btn-primary btn-block" href="/summit-2026#tiers">Reserve a seat <span class="arrow" aria-hidden="true">&rarr;</span></a></div>
          </div>
        </div>
      </div>
    </section>

    <section class="section section-cream">
      <div class="container container-narrow" style="text-align:center">
        <span class="eyebrow" style="justify-content:center">Who it is for</span>
        <p class="highlight" style="border-left:0;border-top:3px solid var(--gold);padding:1.25rem 0 0">Young people ready to lead rather than observe, and mentors who want to spend their experience where it compounds.</p>
      </div>
    </section>

    <section class="section section-white">
      <div class="container">
        <div class="section-head reveal"><span class="eyebrow">Also at TIS</span><h2>The other two tracks.</h2></div>
        <div class="grid grid-2">
          <article class="card reveal">
            <div class="icon-badge" aria-hidden="true"><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polygon points="16 8 14 14 8 16 10 10 16 8"/></svg></div>
            <span class="card-tag">Purpose</span>
            <h3>Clarity of direction</h3>
            <p>Guided sessions that help young people name what they actually want, instead of borrowing someone else's definition of success.</p>
            <div class="card-foot"><a class="btn btn-ghost" href="/programme-purpose">Explore Purpose <span class="arrow" aria-hidden="true">&rarr;</span></a></div>
          </article>
          <article class="card reveal">
            <div class="icon-badge blue" aria-hidden="true"><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="3" width="6" height="11" rx="3"/><path d="M5 11a7 7 0 0 0 14 0"/><line x1="12" y1="18" x2="12" y2="21"/></svg></div>
            <span class="card-tag">Voice</span>
            <h3>The courage to speak</h3>
            <p>Practical public speaking and communication training, tested in front of a real room, not a mirror.</p>
            <div class="card-foot"><a class="btn btn-ghost" href="/programme-voice">Explore Voice <span class="arrow" aria-hidden="true">&rarr;</span></a></div>
          </article>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="cta-banner reveal">
          <div class="cta-inner">
            <div>
              <span class="eyebrow">Next step</span>
              <h2>Ready to work on reach?</h2>
              <p class="lead">The 2026 Summit runs all three tracks in one day, in one hall, in Okada.</p>
            </div>
            <div class="btn-row" style="flex-direction:column;align-items:stretch">
              <a class="btn btn-primary btn-block" href="/summit-2026#tiers">Reserve a seat <span class="arrow" aria-hidden="true">&rarr;</span></a>
              <a class="btn btn-outline btn-block" href="/contact">Ask a question</a>
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
