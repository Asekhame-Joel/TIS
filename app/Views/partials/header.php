<?php
declare(strict_types=1);

$current = static fn (string $page): string => $activePage === $page ? ' aria-current="page"' : '';
?>
<header class="site-header">
  <div class="container header-inner">
    <a class="brand" href="/"><span class="brand-mark" aria-hidden="true">TIS</span><span class="brand-text"><span class="brand-name">The Intellectual Summit</span><span class="brand-tag">Global Relevance</span></span></a>
<?php if (!$minimalHeader): ?>
    <nav class="nav" aria-label="Primary">
      <ul style="display:contents">
        <li><a class="nav-link" href="/"<?= $current('home') ?>>Home</a></li>
        <li><a class="nav-link" href="/about"<?= $current('about') ?>>About</a></li>
        <li class="has-dropdown"><a class="nav-link dropdown-toggle" href="/programmes"<?= $current('programmes') ?>>Programmes<svg class="chev" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg></a><ul class="dropdown"><li><a href="/programme-purpose">Purpose<span>Clarity of direction</span></a></li><li><a href="/programme-voice">Voice<span>The courage to speak</span></a></li><li><a href="/programme-reach">Reach<span>A wider standard</span></a></li><li><a href="/programmes">All programmes<span>Overview of the three tracks</span></a></li></ul></li>
        <li><a class="nav-link" href="/why-us"<?= $current('why-us') ?>>Why Us</a></li>
        <li><a class="nav-link" href="/process"<?= $current('process') ?>>Process</a></li>
        <li><a class="nav-link" href="/summit-2026"<?= $current('summit-2026') ?>>Summit 2026</a></li>
        <li><a class="nav-link" href="/speakers"<?= $current('speakers') ?>>Speakers</a></li>
        <li><a class="nav-link" href="/faq"<?= $current('faq') ?>>FAQ</a></li>
        <li><a class="nav-link" href="/contact"<?= $current('contact') ?>>Contact</a></li>
        <li class="nav-cta-mobile"><a class="btn btn-primary" href="/summit-2026#tiers">Get tickets</a></li>
      </ul>
    </nav>
<?php endif; ?>
    <div class="header-cta">
<?php if ($minimalHeader): ?>
      <a class="btn btn-outline" href="/summit-2026">Back to Summit 2026</a>
<?php else: ?>
      <a class="btn btn-outline" href="/contact">Enquire</a>
      <a class="btn btn-primary" href="/summit-2026#tiers">Get tickets</a>
      <button class="nav-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
<?php endif; ?>
    </div>
  </div>
</header>
