<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Speakers &amp; Panelists | The Intellectual Summit</title>
  <meta name="description" content="Meet the speakers and panelists bringing insight, experience and fresh perspectives to The Intellectual Summit 2026.">
  <meta name="theme-color" content="#1b2a4a">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="The Intellectual Summit">
  <meta property="og:title" content="Speakers &amp; Panelists | The Intellectual Summit">
  <meta property="og:description" content="Meet the voices shaping The Intellectual Summit 2026.">
  <link rel="canonical" href="/speakers">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..700;1,9..144,400..700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= tis_asset('/assets/css/style.css') ?>">
  <link rel="stylesheet" href="<?= tis_asset('/assets/css/responsive.css') ?>">
</head>
<body class="speakers-page">
  <a class="skip-link" href="#main">Skip to content</a>
  <?php tis_render_header('speakers'); ?>

  <main id="main">
    <section class="speaker-hero">
      <div class="container speaker-hero-inner">
        <div class="speaker-hero-copy reveal">
          <nav class="breadcrumb" aria-label="Breadcrumb"><a href="/">Home</a> <span aria-hidden="true">/</span> <span>Speakers &amp; Panelists</span></nav>
          <span class="eyebrow">Summit 2026 &middot; The line-up</span>
          <h1>Meet the voices shaping <em>the room</em>.</h1>
          <p class="lead">A carefully selected company of speakers and panelists bringing lived experience, bold ideas and practical insight to The Intellectual Summit 2026.</p>
          <div class="speaker-hero-stats" aria-label="Event line-up summary">
            <div><strong>03</strong><span>Speakers</span></div>
            <div><strong>02</strong><span>Panelists</span></div>
            <div><strong>01</strong><span>Keynote host</span></div>
          </div>
        </div>
        <div class="speaker-hero-art reveal reveal-delay-1" aria-hidden="true">
          <span class="speaker-orbit orbit-one"></span>
          <span class="speaker-orbit orbit-two"></span>
          <div class="speaker-theme-mark"><small>2026 theme</small><strong>The student,<br>the journey,<br>the destination.</strong></div>
          <span class="speaker-hero-number">06</span>
        </div>
      </div>
    </section>

    <section class="section speaker-keynote-section">
      <div class="container">
        <article class="keynote-feature reveal">
          <div class="keynote-portrait">
            <img src="<?= tis_asset('/assets/images/gold.png') ?>" alt="Dr Jochebed Emuveyan, founder and host of The Intellectual Summit" width="900" height="1125">
            <span class="profile-number" aria-hidden="true">01</span>
          </div>
          <div class="keynote-copy">
            <span class="profile-kicker">Keynote &middot; Founder &amp; Host</span>
            <h2>Dr Jochebed Emuveyan</h2>
            <p class="profile-title">The Intellectual Summit &middot; Igbinedion University</p>
            <p class="profile-intro">Scholar, leadership coach and founder of TIS. She holds a PhD in International Relations from Igbinedion University, where she also lectures and mentors students.</p>
            <p>She builds spaces where young people learn to think critically, lead confidently and live with purpose.</p>
            <a class="profile-link" href="/about">Discover the story behind TIS <span aria-hidden="true">&rarr;</span></a>
          </div>
        </article>
      </div>
    </section>

    <section class="section speaker-lineup-section" id="speakers">
      <div class="container">
        <header class="lineup-heading reveal">
          <div>
            <span class="eyebrow">On the main stage</span>
            <h2>Featured speakers</h2>
          </div>
          <p>Distinct voices, one shared commitment to helping young people move from potential to purposeful impact.</p>
        </header>

        <div class="editorial-lineup">
          <article class="editorial-profile is-featured reveal">
            <div class="editorial-photo">
              <img src="<?= tis_asset('/assets/images/speakers/victory-ashaka.webp') ?>" alt="Victory Ashaka" width="1080" height="1350" loading="lazy">
              <span class="profile-number" aria-hidden="true">02</span>
            </div>
            <div class="editorial-copy">
              <span class="profile-kicker">Speaker</span>
              <h3>Victory Ashaka</h3>
              <p class="profile-title">Spoken-word poet, youth advisor and Global Youth Ambassador</p>
              <p class="profile-intro"><strong>Victory Ashaka</strong> is a multi-award-winning spoken word poet, youth advisor to the European Union, poetry slam champion, Global Youth Ambassador of Theirworld and an erstwhile member of the Young People’s Action Team of UNICEF Generation Unlimited (YPAT).</p>
              <details class="profile-details">
                <summary>Read full profile <span aria-hidden="true">+</span></summary>
                <div class="profile-details-body">
                  <p>Victory is a graduate of English Literature from Lagos State University and a certified Leadership Fellow from the Management Institute of Finland in Helsinki.</p>
                  <p>He is a Diana Award Recipient, recipient of the Lord’s Achievers Award for Literature and the Future Awards Africa Prize for Young Person of the Year. He has been featured as a poet laureate and spoken-word artist on platforms including the United Nations, UNICEF, FAO, TEDx, TRT World Forum, Italian Consulate, British Deputy High Commission and the Aso Rock Presidential Villa.</p>
                  <p>In 2026, Victory released his debut spoken-word EP, <em>The Moving Man</em>, following an intimate Global Listening Party that brought together guests and supporters to experience the project before its official release.</p>
                  <p>The project marks a new chapter in his artistic journey, blending storytelling, purpose, becoming and lived experiences through spoken word.</p>
                </div>
              </details>
            </div>
          </article>

          <article class="editorial-profile is-reversed reveal">
            <div class="editorial-photo king-ken-photo">
              <img src="<?= tis_asset('/assets/images/speakers/king-ken-event.png') ?>" alt="King Ken, speaker at The Intellectual Summit 2026" width="1620" height="2025" loading="lazy">
              <span class="profile-number" aria-hidden="true">03</span>
            </div>
            <div class="editorial-copy">
              <span class="profile-kicker">Speaker</span>
              <h3>King Ken</h3>
              <p class="profile-title">Leadership Architect, Brand Strategist and Creative Director</p>
              <p class="profile-intro"><strong>King Ken</strong> is a seasoned Leadership Architect, Brand Strategist, and Creative Director/Art Director, and the visionary behind Grafixlodge Studios and Elevatia Academy. With over 22 years of experience, he has built a reputation for shaping high-performance environments and driving transformational outcomes through strategy, influence, and elevated thinking.</p>
              <details class="profile-details">
                <summary>Read full profile <span aria-hidden="true">+</span></summary>
                <div class="profile-details-body">
                  <p>His expertise lies in Disruptive Thinking—a refined approach to challenging outdated paradigms, dismantling limiting structures, and developing forward-focused mindsets. He combines strategic clarity, creative insight, and disciplined execution to help individuals and organizations unlock greater capacity and achieve lasting results.</p>
                  <p>As a leader and facilitator, King Ken is known for building high-performing teams, implementing structured systems, and cultivating cultures of accountability, precision, speed, and excellence. He works with corporate professionals and leaders to navigate paradigm shifts, strengthen performance, and translate bold ideas into measurable outcomes.</p>
                  <p>His mission is clear: to transform minds, elevate standards, and engineer influence that creates distinction and lasting impact.</p>
                </div>
              </details>
            </div>
          </article>

          <article class="editorial-profile reveal">
            <div class="editorial-photo">
              <img src="<?= tis_asset('/assets/images/speakers/obi.webp') ?>" alt="Obi, Osuchukwu Kenechukwu Obiagboso" width="1080" height="1350" loading="lazy">
              <span class="profile-number" aria-hidden="true">04</span>
            </div>
            <div class="editorial-copy">
              <span class="profile-kicker">Speaker</span>
              <h3>Obi</h3>
              <p class="profile-title">Final-year medical student, student leader and leadership advocate</p>
              <p class="profile-intro"><strong>Osuchukwu Kenechukwu Obiagboso</strong> currently serves as the 15th President of the Igbinedion University Medical Students Association (IUMSA), where he champions student engagement, academic excellence, inclusivity, leadership development and institutional growth.</p>
              <details class="profile-details">
                <summary>Read full profile <span aria-hidden="true">+</span></summary>
                <div class="profile-details-body">
                  <p>His leadership experience includes serving as Assistant General Secretary and Vice President of IUMSA, Assistant National Secretary of the NiMSA Directorate of Climate Awareness and Advocacy Programme, and Deputy Regional Coordinator for the South-South Region of the Nigerian Medical Students’ Association, supporting the welfare of over 5,000 medical students across 12 medical schools.</p>
                  <p>He is also the pioneer IUMSA Liaison Officer to FAMSA and pioneer licensee of TEDx Igbinedion University. Through leadership and service, he advocates for discipline, adaptability, collaboration and intentional personal growth—encouraging students to look beyond titles and certificates and become individuals who create opportunities, empower others and leave their communities better than they found them.</p>
                </div>
              </details>
            </div>
          </article>
        </div>
      </div>
    </section>

    <section class="section panelists-section" id="panelists">
      <div class="container">
        <header class="lineup-heading lineup-heading-light reveal">
          <div>
            <span class="eyebrow">In conversation</span>
            <h2>Meet the panelists</h2>
          </div>
          <p>Leaders working across youth development, education, healthcare, research and community impact.</p>
        </header>

        <div class="panelist-grid">
          <article class="panelist-profile reveal">
            <div class="panelist-photo">
              <img src="<?= tis_asset('/assets/images/speakers/divine-chukwuemeka.webp') ?>" alt="Divine M. Chukwuemeka" width="1080" height="1350" loading="lazy">
              <span class="panelist-label">Panelist</span>
            </div>
            <div class="panelist-copy">
              <span class="profile-number" aria-hidden="true">05</span>
              <h3>Divine M. Chukwuemeka</h3>
              <p class="profile-title">Migration researcher, TEDx speaker and founder</p>
              <p class="profile-intro"><strong>Divine Mmachukwu Chukwuemeka</strong> is passionate about global mobility, youth development and access to opportunities. She holds a First-Class degree in History and International Studies from Anchor University and serves as a Graduate Research Assistant at CEPDeR, Covenant University.</p>
              <details class="profile-details profile-details-dark">
                <summary>Read full profile <span aria-hidden="true">+</span></summary>
                <div class="profile-details-body">
                  <p>She contributes to migration and development research and has spoken at TEDx events at Anchor University, the University of Nigeria, Nsukka, and Adekunle Ajasin University. She also participated as a discussant at HistoCON, hosted by Germany's Federal Agency for Civic Education.</p>
                  <p>Beyond research, Divine leads youth-focused initiatives, including Beyond Borders and the AheadGen Mentorship Program. Through Linked On LinkedIn, she has equipped hundreds of students and young professionals with digital visibility and career advancement skills.</p>
                  <p>As founder of EmigrateEase, she provides strategic advisory support for migration decisions, helping individuals navigate global mobility with clarity and confidence. Her work bridges research, advocacy, mentorship and practical opportunities to empower young people to make informed decisions about their futures.</p>
                </div>
              </details>
            </div>
          </article>

          <article class="panelist-profile reveal reveal-delay-1">
            <div class="panelist-photo">
              <img src="<?= tis_asset('/assets/images/speakers/dr-harmony-ibezim.webp') ?>" alt="Dr Harmony U. Ibezim" width="1080" height="1350" loading="lazy">
              <span class="panelist-label">Panelist</span>
            </div>
            <div class="panelist-copy">
              <span class="profile-number" aria-hidden="true">06</span>
              <h3>Dr Harmony U. Ibezim</h3>
              <p class="profile-title">Medical doctor, clinical biochemist, lecturer and researcher</p>
              <p class="profile-intro"><strong>Dr Harmony Uchenna Ibezim</strong> is a mentor and youth development advocate with a strong commitment to empowering young people to become confident, informed and impactful leaders.</p>
              <details class="profile-details profile-details-dark">
                <summary>Read full profile <span aria-hidden="true">+</span></summary>
                <div class="profile-details-body">
                  <p>He is a Lecturer in the Department of Biochemistry at Igbinedion University, Okada, and a Senior Medical Officer in the Department of Medicine at Igbinedion University Teaching Hospital. His professional work sits at the intersection of healthcare, education, research and community development, with research interests spanning metabolic medicine, public health, toxicology and emerging health challenges in underserved communities.</p>
                  <p>Beyond his professional career, Dr Ibezim is deeply passionate about youth empowerment, mentorship, leadership, education, public speaking and personal development. He has worked with students and young people through mentorship, leadership initiatives, academic programmes and youth-focused development projects, helping them build confidence, communicate effectively and recognise their capacity to influence their communities.</p>
                </div>
              </details>
            </div>
          </article>
        </div>
      </div>
    </section>

    <section class="section section-white speakers-cta-section">
      <div class="container">
        <div class="cta-banner speakers-cta reveal">
          <div>
            <span class="eyebrow" style="color:var(--gold-bright)">Be in the room</span>
            <h2>Hear the ideas. Meet the people. Join the conversation.</h2>
          </div>
          <div class="speakers-cta-actions">
            <a class="btn btn-primary" href="/summit-2026#tiers">Reserve your seat <span class="arrow" aria-hidden="true">&rarr;</span></a>
            <a class="btn btn-outline" href="/summit-2026">Explore Summit 2026</a>
          </div>
        </div>
      </div>
    </section>
  </main>

  <?php tis_render_footer(); ?>
  <script src="<?= tis_asset('/assets/js/main.js') ?>"></script>
</body>
</html>
