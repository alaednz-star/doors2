<?php
/** @var array $colors */
/** @var array $collections */
/** @var array $featured */
$e   = static fn ($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$img = static fn ($f) => '/door-showroom/assets/images/' . $f;
$wa  = 'https://wa.me/213512345678';
$ver = static fn ($p) => @filemtime(APP_ROOT . '/public' . $p) ?: '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Luxury architectural doors, made to measure. Discover collections, explore finishes, configure your door and request a quote — PORTES, Algiers." />
  <title>PORTES — Luxury Architectural Doors</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Inter:wght@300;400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/door-showroom/assets/css/home.css?v=<?= $ver('/assets/css/home.css') ?>" />
</head>
<body>

<!-- ░ NAV ░ -->
<header class="nav" id="nav">
  <a href="/door-showroom" class="nav-logo">
    <span class="nav-logo-mark">PORTES</span>
    <span class="nav-logo-sub">Architectural Doors</span>
  </a>
  <nav class="nav-links" id="navLinks" aria-label="Primary">
    <a href="#collections">Collections</a>
    <a href="/door-showroom/configure">Configurator</a>
    <a href="#featured">Doors</a>
    <a href="#why">About</a>
  </nav>
  <a href="#quote" class="nav-cta">Request Quote</a>
  <button class="nav-burger" id="navBurger" aria-label="Open menu" aria-expanded="false">
    <span></span><span></span><span></span>
  </button>
</header>

<!-- ░ 1 · HERO ░ -->
<section class="hero" id="top">
  <div class="hero-bg">
    <img src="<?= $img('bghero.png') ?>" alt="" role="presentation" class="hero-img" />
    <div class="hero-overlay"></div>
  </div>
  <div class="hero-inner">
    <p class="hero-eyebrow">Luxury Architectural Doors</p>
    <h1 class="hero-title">Designed by you.<br /><em>Engineered to last.</em></h1>
    <p class="hero-sub">Made-to-measure doors for spaces that demand permanence — configured by you, crafted by us.</p>
    <div class="hero-actions">
      <a href="/door-showroom/configure" class="btn btn--gold btn--lg">Configure Your Door
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
      <a href="#collections" class="btn btn--outline btn--lg">View Collections</a>
    </div>
  </div>
  <div class="hero-strip">
    <span>Made to Measure</span>
    <span>Premium Materials</span>
    <span>Expert Installation</span>
    <span>Durable &amp; Reliable</span>
  </div>
  <a href="#collections" class="hero-scroll" aria-label="Scroll down">
    <span class="hero-scroll-line"></span>
  </a>
</section>

<!-- ░ 2 · COLLECTIONS ░ -->
<section class="collections" id="collections">
  <div class="sec-intro reveal">
    <p class="eyebrow">The Collections</p>
    <h2 class="sec-title">Three worlds,<br /><em>one standard.</em></h2>
  </div>

  <div class="coll-grid reveal">
    <?php foreach ($collections as $i => $c): ?>
      <a class="coll-card" href="/door-showroom/collections" aria-label="<?= $e($c['name']) ?>">
        <span class="coll-card-img" style="background-image:url('<?= $img($c['file']) ?>')"></span>
        <span class="coll-card-shade"></span>
        <span class="coll-card-num">Collection <?= $e($c['num']) ?></span>
        <span class="coll-card-body">
          <span class="coll-card-name"><?= $e($c['name']) ?></span>
          <span class="coll-card-line"><?= $e($c['line']) ?></span>
          <span class="coll-card-specs">
            <span>Made to measure</span>
            <span>Exclusive finishes</span>
            <span>Crafted locally</span>
          </span>
          <span class="coll-card-cta">Explore
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </span>
        </span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- ░ 3 · FEATURED DOORS ░ -->
<section class="featured" id="featured">
  <div class="sec-intro reveal">
    <p class="eyebrow">Selected Doors</p>
    <h2 class="sec-title">Crafted to be<br /><em>chosen.</em></h2>
  </div>
  <div class="featured-grid">
    <?php foreach ($featured as $i => $d): ?>
      <article class="fdoor reveal<?= $i ? ' reveal-d' . min($i, 3) : '' ?>">
        <div class="fdoor-media">
          <img src="<?= $img($d['file']) ?>" alt="<?= $e($d['name']) ?>" loading="lazy" />
          <div class="fdoor-actions">
            <a href="/door-showroom/configure" class="btn btn--gold btn--sm">Configure</a>
            <a href="#quote" class="btn btn--outline-light btn--sm">Request Quote</a>
          </div>
        </div>
        <div class="fdoor-meta">
          <span class="fdoor-collection"><?= $e($d['collection']) ?></span>
          <h3 class="fdoor-name"><?= $e($d['name']) ?></h3>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<!-- ░ 6 · WHY ░ -->
<section class="why" id="why">
  <div class="sec-intro sec-intro--center reveal">
    <p class="eyebrow">Why PORTES</p>
    <h2 class="sec-title">The difference<br /><em>is in the detail.</em></h2>
  </div>
  <div class="why-grid reveal">
    <div class="why-item">
      <span class="why-icon"><svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 12h36M6 24h24M6 36h12"/><path d="M40 22l4 4-4 4"/></svg></span>
      <h3>Made to Measure</h3>
      <p>Every door engineered to your exact opening.</p>
    </div>
    <div class="why-item">
      <span class="why-icon"><svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M24 4l5 14h15l-12 9 5 15-13-9-13 9 5-15-12-9h15z"/></svg></span>
      <h3>Premium Quality</h3>
      <p>Solid timber, aircraft-grade aluminium, European hardware.</p>
    </div>
    <div class="why-item">
      <span class="why-icon"><svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M24 6C14 6 6 14 6 24s8 18 18 18 18-8 18-18S34 6 24 6z"/><path d="M16 24l6 6 10-12"/></svg></span>
      <h3>Expert Installation</h3>
      <p>Factory-trained teams. We never subcontract what matters.</p>
    </div>
    <div class="why-item">
      <span class="why-icon"><svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M24 4l16 6v12c0 11-7 18-16 22-9-4-16-11-16-22V10z"/><path d="M17 24l5 5 9-11"/></svg></span>
      <h3>Durable &amp; Reliable</h3>
      <p>A 25-year structural guarantee.</p>
    </div>
  </div>
</section>

<!-- ░ 7 · QUOTE CTA ░ -->
<section class="quote" id="quote">
  <div class="quote-bg"><img src="<?= $img('portes-madera.jpg') ?>" alt="" role="presentation" loading="lazy" /><div class="quote-overlay"></div></div>
  <div class="quote-inner reveal">
    <h2 class="quote-title">Ready to bring your<br /><em>vision to life?</em></h2>
    <p class="quote-sub">Configure your door and request a personal quote in under five minutes. No obligation.</p>
    <div class="quote-actions">
      <a href="/door-showroom/configure" class="btn btn--gold btn--lg">Configure &amp; Request Quote
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
      <a href="<?= $e($wa) ?>" target="_blank" rel="noopener" class="btn btn--outline-light btn--lg">Talk to Us
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 00-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1012 2zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-1.7-.1-.4-.1-.9-.3-1.6-.6-2.8-1.2-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8s.7-2 .9-2.2c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.1.1.3 0 .5l-.4.5-.3.3c-.2.2-.3.4-.2.6.2.4.8 1.3 1.6 2 .9.8 1.7 1.1 2.1 1.3.2.1.5.1.6-.1l.7-.8c.2-.2.4-.2.6-.1l1.9.9c.2.1.4.2.4.3.1.1.1.6-.1 1.2z"/></svg>
      </a>
    </div>
  </div>
</section>

<!-- ░ 8 · FOOTER ░ -->
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
        <li><a href="/door-showroom/collections">Prestige</a></li>
        <li><a href="/door-showroom/collections">Moderne</a></li>
        <li><a href="/door-showroom/collections">Heritage</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Services</h4>
      <ul>
        <li><a href="/door-showroom/configure">Configurator</a></li>
        <li><a href="#quote">Made to Measure</a></li>
        <li><a href="#quote">Installation</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Company</h4>
      <ul>
        <li><a href="#why">About Us</a></li>
        <li><a href="#featured">Our Doors</a></li>
        <li><a href="#collections">Projects</a></li>
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

<script src="/door-showroom/assets/js/home.js?v=<?= $ver('/assets/js/home.js') ?>" defer></script>
</body>
</html>
