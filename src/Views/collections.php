<?php
/** @var array $collections */
/** @var string $q */
$e   = static fn ($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$wa  = 'https://wa.me/213512345678';
$totalDoors = 0;
foreach ($collections as $c) { $totalDoors += (int) $c['door_count']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="The PORTES collections — Heritage, Moderne and Prestige. Luxury architectural doors, each a complete design language of the threshold." />
  <title>Collections — PORTES</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Inter:wght@300;400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/door-showroom/assets/css/home.css" />
  <link rel="stylesheet" href="/door-showroom/assets/css/collections.css" />
</head>
<body>

<!-- ░ NAV ░ -->
<header class="nav is-scrolled" id="nav">
  <a href="/door-showroom" class="nav-logo">
    <span class="nav-logo-mark">PORTES</span>
    <span class="nav-logo-sub">Architectural Doors</span>
  </a>
  <nav class="nav-links" id="navLinks" aria-label="Primary">
    <a href="/door-showroom/collections" class="is-current">Collections</a>
    <a href="/door-showroom#configurator">Configurator</a>
    <a href="/door-showroom#colors">Colours</a>
    <a href="/door-showroom#featured">Doors</a>
    <a href="/door-showroom#why">About</a>
  </nav>
  <a href="/door-showroom#quote" class="nav-cta">Request Quote</a>
  <button class="nav-burger" id="navBurger" aria-label="Open menu" aria-expanded="false">
    <span></span><span></span><span></span>
  </button>
</header>

<!-- ░ PAGE HEADER ░ -->
<section class="col-head">
  <div class="col-head-inner">
    <nav class="col-crumb" aria-label="Breadcrumb">
      <a href="/door-showroom">Home</a><span>/</span><span>Collections</span>
    </nav>
    <p class="eyebrow">The Collections</p>
    <h1 class="col-head-title">Three worlds,<br /><em>one standard.</em></h1>
    <p class="col-head-sub">Each PORTES collection is a complete design language of the threshold — material, proportion, hardware and finish, resolved into a single point of view.</p>
    <div class="col-head-stats">
      <div class="col-stat"><strong><?= count($collections) ?></strong><span>Collections</span></div>
      <div class="col-stat-sep"></div>
      <div class="col-stat"><strong><?= $totalDoors ?></strong><span>Door designs</span></div>
      <div class="col-stat-sep"></div>
      <div class="col-stat"><strong>∞</strong><span>Bespoke options</span></div>
    </div>
  </div>
</section>

<!-- ░ FILTER BAR ░ -->
<div class="col-bar" id="colBar">
  <div class="col-bar-inner">
    <div class="col-filters" role="tablist" aria-label="Filter collections">
      <button class="col-filter is-active" data-filter="all" role="tab" aria-selected="true">All</button>
      <button class="col-filter" data-filter="heritage" role="tab" aria-selected="false">Heritage</button>
      <button class="col-filter" data-filter="moderne" role="tab" aria-selected="false">Moderne</button>
      <button class="col-filter" data-filter="prestige" role="tab" aria-selected="false">Prestige</button>
    </div>
    <form class="col-search" method="get" action="/door-showroom/collections" role="search">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
      <input type="search" name="q" id="colSearch" value="<?= $e($q) ?>" placeholder="Search collections…" autocomplete="off" aria-label="Search collections" />
    </form>
  </div>
</div>

<!-- ░ EDITORIAL STORIES ░ -->
<main class="col-stories" id="colStories">
  <?php if (empty($collections)): ?>
    <div class="col-empty">
      <h2>No collections found</h2>
      <p><?= $q !== '' ? 'Nothing matches “' . $e($q) . '”.' : 'The catalogue is being prepared.' ?></p>
      <?php if ($q !== ''): ?><a href="/door-showroom/collections" class="btn btn--gold">View all collections</a><?php endif; ?>
    </div>
  <?php else: ?>
    <?php foreach ($collections as $i => $c): ?>
      <article class="col-story<?= $i % 2 ? ' col-story--reverse' : '' ?> reveal"
               data-slug="<?= $e($c['slug']) ?>"
               data-name="<?= $e(strtolower($c['name'])) ?>">
        <div class="col-story-media">
          <img src="<?= $e($c['cover_url']) ?>" alt="<?= $e($c['name']) ?> collection" loading="<?= $i === 0 ? 'eager' : 'lazy' ?>" />
          <span class="col-story-count"><?= (int) $c['door_count'] ?> <?= (int) $c['door_count'] === 1 ? 'Door' : 'Doors' ?></span>
          <?php if ($c['tone'] !== ''): ?><span class="col-story-tone"><?= $e($c['tone']) ?></span><?php endif; ?>
        </div>
        <div class="col-story-body">
          <span class="col-story-num">Collection <?= $e($c['num']) ?></span>
          <h2 class="col-story-name"><?= $e($c['name']) ?></h2>
          <p class="col-story-tagline"><?= $e($c['tagline']) ?></p>
          <p class="col-story-desc"><?= $e($c['story']) ?></p>
          <div class="col-story-meta">
            <span><strong><?= (int) $c['door_count'] ?></strong> Door <?= (int) $c['door_count'] === 1 ? 'design' : 'designs' ?></span>
            <span><strong>Made</strong> to measure</span>
          </div>
          <a href="/door-showroom/collections/<?= $e($c['slug']) ?>" class="btn btn--gold">Explore <?= $e($c['name']) ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </a>
        </div>
      </article>
    <?php endforeach; ?>

    <div class="col-noresults" id="colNoResults" hidden>
      <h2>No matches</h2>
      <p>No collection matches the current filter.</p>
      <button class="btn btn--outline" id="colReset">Reset filter</button>
    </div>
  <?php endif; ?>
</main>

<!-- ░ QUOTE CTA ░ -->
<section class="quote" id="quote">
  <div class="quote-bg"><img src="/door-showroom/assets/images/portes-madera.jpg" alt="" role="presentation" loading="lazy" /><div class="quote-overlay"></div></div>
  <div class="quote-inner reveal">
    <h2 class="quote-title">Found your<br /><em>collection?</em></h2>
    <p class="quote-sub">Open the configurator to make it yours — material, finish, dimensions and hardware, with a personal quote in minutes.</p>
    <div class="quote-actions">
      <a href="/door-showroom/configure" class="btn btn--gold btn--lg">Configure &amp; Request Quote
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
      <a href="<?= $e($wa) ?>" target="_blank" rel="noopener" class="btn btn--outline-light btn--lg">Talk to Us</a>
    </div>
  </div>
</section>

<!-- ░ FOOTER ░ -->
<footer class="footer" id="contact">
  <div class="footer-grid">
    <div class="footer-brand">
      <div class="footer-logo">PORTES</div>
      <p class="footer-tag">Luxury architectural doors,<br />designed by you. Engineered to last.</p>
      <div class="footer-social">
        <a href="#" aria-label="Facebook"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 9h3V6h-3c-2 0-3.5 1.5-3.5 3.5V11H8v3h2.5v7h3v-7H16l.5-3h-3V9.5c0-.3.2-.5.5-.5z"/></svg></a>
        <a href="#" aria-label="Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".6" fill="currentColor"/></svg></a>
        <a href="<?= $e($wa) ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 00-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1012 2zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-1.7-.1-.4-.1-.9-.3-1.6-.6-2.8-1.2-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8s.7-2 .9-2.2c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.1.1.3 0 .5l-.4.5-.3.3c-.2.2-.3.4-.2.6.2.4.8 1.3 1.6 2 .9.8 1.7 1.1 2.1 1.3.2.1.5.1.6-.1l.7-.8c.2-.2.4-.2.6-.1l1.9.9c.2.1.4.2.4.3.1.1.1.6-.1 1.2z"/></svg></a>
      </div>
    </div>
    <div class="footer-col">
      <h4>Collections</h4>
      <ul>
        <li><a href="/door-showroom/collections">Heritage</a></li>
        <li><a href="/door-showroom/collections">Moderne</a></li>
        <li><a href="/door-showroom/collections">Prestige</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Services</h4>
      <ul>
        <li><a href="/door-showroom/configure">Configurator</a></li>
        <li><a href="/door-showroom#quote">Made to Measure</a></li>
        <li><a href="/door-showroom#quote">Installation</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Company</h4>
      <ul>
        <li><a href="/door-showroom#why">About Us</a></li>
        <li><a href="/door-showroom#featured">Our Doors</a></li>
        <li><a href="/door-showroom/collections">Projects</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Contact</h4>
      <ul>
        <li><a href="tel:+213512345678">+213 5 12 34 56 78</a></li>
        <li><a href="mailto:contact@portes.dz">contact@portes.dz</a></li>
        <li><span>Algiers, Algeria</span></li>
        <li><a href="<?= $e($wa) ?>" target="_blank" rel="noopener">WhatsApp</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <span>&copy; <?= date('Y') ?> PORTES. All rights reserved.</span>
    <span>Luxury Architectural Doors — Algiers</span>
  </div>
</footer>

<a href="<?= $e($wa) ?>" target="_blank" rel="noopener" class="wa-float" aria-label="Chat on WhatsApp">
  <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 00-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1012 2zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-1.7-.1-.4-.1-.9-.3-1.6-.6-2.8-1.2-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8s.7-2 .9-2.2c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.1.1.3 0 .5l-.4.5-.3.3c-.2.2-.3.4-.2.6.2.4.8 1.3 1.6 2 .9.8 1.7 1.1 2.1 1.3.2.1.5.1.6-.1l.7-.8c.2-.2.4-.2.6-.1l1.9.9c.2.1.4.2.4.3.1.1.1.6-.1 1.2z"/></svg>
</a>

<script src="/door-showroom/assets/js/home.js" defer></script>
<script src="/door-showroom/assets/js/collections.js" defer></script>
</body>
</html>
