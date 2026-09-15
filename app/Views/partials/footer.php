<?php
declare(strict_types=1);
?>
<?php if ($minimalFooter): ?>
<footer class="site-footer">
  <div class="container">
    <div class="footer-bottom" style="margin-top:0">
      <p>&copy; 2026 The Intellectual Summit. Global Relevance.</p>
      <span>Questions? <a href="mailto:tickets@theintellectualsummit.com">tickets@theintellectualsummit.com</a></span>
    </div>
  </div>
</footer>
<?php else: ?>
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <a class="brand" href="/"><span class="brand-mark" aria-hidden="true"><i></i><i></i><i></i></span><span class="brand-text"><span class="brand-name">The Intellectual Summit</span><span class="brand-tag">Global Relevance</span></span></a>
        <p style="margin-top:1.1rem;max-width:32ch">A Platform that trains young people to think clearly, speak with conviction, and lead beyond their immediate circle.</p>
        <div class="social-row" style="margin-top:1.4rem">
          <a href="/contact" aria-label="Contact The Intellectual Summit"><svg viewBox="0 0 24 24"><path d="M2 6.5A2.5 2.5 0 0 1 4.5 4h15A2.5 2.5 0 0 1 22 6.5v11a2.5 2.5 0 0 1-2.5 2.5h-15A2.5 2.5 0 0 1 2 17.5zM4.6 6l7.4 5.4L19.4 6z"/></svg></a>
          <a href="/summit-2026" aria-label="The Summit 2026 event page"><svg viewBox="0 0 24 24"><path d="M7 2h2v2h6V2h2v2h1.5A2.5 2.5 0 0 1 21 6.5v13A2.5 2.5 0 0 1 18.5 22h-13A2.5 2.5 0 0 1 3 19.5v-13A2.5 2.5 0 0 1 5.5 4H7zM5 10v9.5c0 .3.2.5.5.5h13a.5.5 0 0 0 .5-.5V10z"/></svg></a>
          <a href="/faq" aria-label="Frequently asked questions"><svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm.1 15.5a1.2 1.2 0 1 1 0-2.4 1.2 1.2 0 0 1 0 2.4zM13 13.3v.5h-2v-1.2c0-.6.3-1.1.9-1.5l.9-.6c.4-.3.6-.6.6-1 0-.7-.5-1.2-1.3-1.2-.8 0-1.4.5-1.5 1.3H8.5C8.6 7.8 10 6.5 12 6.5c2 0 3.4 1.2 3.4 2.9 0 1-.5 1.8-1.5 2.4l-.6.4c-.2.2-.3.4-.3.6z"/></svg></a>
        </div>
      </div>
      <div>
        <h4>Explore</h4>
        <ul class="footer-links">
          <li><a href="/">Home</a></li>
          <li><a href="/about">About TIS</a></li>
          <li><a href="/why-us">Why choose us</a></li>
          <li><a href="/process">How it works</a></li>
          <li><a href="/faq">FAQ</a></li>
        </ul>
      </div>
      <div>
        <h4>Programmes</h4>
        <ul class="footer-links">
          <li><a href="/programme-purpose">Purpose</a></li>
          <li><a href="/programme-voice">Voice</a></li>
          <li><a href="/programme-reach">Reach</a></li>
          <li><a href="/programmes">All programmes</a></li>
          <li><a href="/summit-2026">Summit 2026</a></li>
          <li><a href="/speakers">Speakers &amp; panelists</a></li>
        </ul>
      </div>
      <div>
        <h4>The 2026 Summit</h4>
        <ul class="footer-links">
          <li>14 November 2026</li>
          <li>Okunozee Hall, Okada</li>
          <li>Okada, Nigeria &middot; 6.9&deg;N, 5.8&deg;E</li>
          <li>In person, one day</li>
        </ul>
        <a class="btn btn-primary" style="margin-top:1.25rem" href="/summit-2026#tiers">Reserve a seat <span class="arrow" aria-hidden="true">&rarr;</span></a>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2026 The Intellectual Summit. Global Relevance.</p>
<?php if ($founderCredit): ?>
      <p>Founded by Dr Jochebed Emuveyan, Founder, The Intellectual Summit.</p>
<?php else: ?>
      <span>Crafted with intention and love by <a href="https://jotechtotech.com">JoTechToTech</a>.</span>
<?php endif; ?>
    </div>
  </div>
</footer>
<?php endif; ?>
