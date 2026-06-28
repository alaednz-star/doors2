<?php
/** @var array $colors */
/** @var array $colorGroups */
/** @var array $collections */
/** @var array $featured */
/** @var array $process */
/** @var array $inspiration */
$e   = static fn ($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$img = static fn ($f) => '/door-showroom/assets/images/' . $f;
$wa  = 'https://wa.me/213512345678';
$ver = static fn ($p) => @filemtime(APP_ROOT . '/public' . $p) ?: '1';
$L   = \App\Core\I18n::lang();
$DIR = \App\Core\I18n::dir();
?>
<!DOCTYPE html>
<html lang="<?= $e($L) ?>" dir="<?= $e($DIR) ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="<?= $e(t('hero.sub')) ?>" />
  <title>PORTES — Luxury Architectural Doors</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Inter:wght@300;400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/door-showroom/assets/css/home.css?v=<?= $ver('/assets/css/home.css') ?>" />
  <link rel="stylesheet" href="/door-showroom/assets/css/i18n.css?v=<?= $ver('/assets/css/i18n.css') ?>" />
</head>
<body>

<!-- ░ NAV ░ -->
<header class="nav" id="nav">
  <a href="/door-showroom" class="nav-logo">
    <img src="<?= $img('logo-adk.png') ?>" alt="ADK — Algerian Doors &amp; Kitchens" class="nav-logo-img" />
  </a>
  <nav class="nav-links" id="navLinks" aria-label="Primary">
    <a href="#collections"><?= $e(t('nav.collections')) ?></a>
    <a href="#process"><?= $e(t('nav.configurator')) ?></a>
    <a href="#featured"><?= $e(t('nav.doors')) ?></a>
    <a href="#inspiration"><?= $e(t('nav.inspiration')) ?></a>
    <a href="#why"><?= $e(t('nav.about')) ?></a>
    <?php $variant = 'mobile'; include APP_ROOT . '/src/Views/partials/lang-switch.php'; ?>
  </nav>
  <?php $variant = ''; include APP_ROOT . '/src/Views/partials/lang-switch.php'; ?>
  <a href="#quote" class="nav-cta"><?= $e(t('nav.request_quote')) ?></a>
  <button class="nav-burger" id="navBurger" aria-label="<?= $e(t('nav.menu_open')) ?>" aria-expanded="false">
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
    <p class="hero-eyebrow"><?= $e(t('hero.eyebrow')) ?></p>
    <h1 class="hero-title"><?= $e(t('hero.title_1')) ?><br /><em><?= $e(t('hero.title_2')) ?></em></h1>
    <p class="hero-sub"><?= $e(t('hero.sub')) ?></p>
    <div class="hero-actions">
      <a href="/door-showroom/configure" class="btn btn--gold btn--lg"><?= $e(t('hero.cta_config')) ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
      <a href="#collections" class="btn btn--outline btn--lg"><?= $e(t('hero.cta_coll')) ?></a>
    </div>
  </div>
  <div class="hero-strip">
    <span><?= $e(t('strip.made')) ?></span>
    <span><?= $e(t('strip.premium')) ?></span>
    <span><?= $e(t('strip.install')) ?></span>
    <span><?= $e(t('strip.durable')) ?></span>
  </div>
  <a href="#collections" class="hero-scroll" aria-label="<?= $e(t('hero.scroll')) ?>">
    <span class="hero-scroll-line"></span>
  </a>
</section>

<!-- ░ 1b · VALUE STRIP ░ -->
<section class="values" aria-label="<?= $e(t('values.aria')) ?>">
  <div class="values-inner">
    <div class="value reveal">
      <span class="value-icon"><svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M24 5l5.6 11.3 12.4 1.8-9 8.8 2.1 12.4L24 35.4 12.9 39.3 15 26.9l-9-8.8 12.4-1.8z"/></svg></span>
      <div class="value-text"><h3><?= $e(t('values.quality')) ?></h3><p><?= $e(t('values.quality_sub')) ?></p></div>
    </div>
    <div class="value reveal reveal-d1">
      <span class="value-icon"><svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 40L40 8M14 8h-6v6M40 34v6h-6"/><path d="M30 8h10v10"/></svg></span>
      <div class="value-text"><h3><?= $e(t('values.measure')) ?></h3><p><?= $e(t('values.measure_sub')) ?></p></div>
    </div>
    <div class="value reveal reveal-d2">
      <span class="value-icon"><svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M24 5l16 6v11c0 11-7 17-16 21-9-4-16-10-16-21V11z"/><path d="M17 24l5 5 9-11"/></svg></span>
      <div class="value-text"><h3><?= $e(t('values.last')) ?></h3><p><?= $e(t('values.last_sub')) ?></p></div>
    </div>
    <div class="value reveal reveal-d3">
      <span class="value-icon"><svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="24" cy="16" r="8"/><path d="M8 42c0-8 7-13 16-13s16 5 16 13"/></svg></span>
      <div class="value-text"><h3><?= $e(t('values.support')) ?></h3><p><?= $e(t('values.support_sub')) ?></p></div>
    </div>
  </div>
</section>

<!-- ░ 2 · COLLECTIONS ░ -->
<section class="collections" id="collections">
  <div class="sec-intro sec-intro--center reveal">
    <p class="eyebrow"><?= $e(t('collections.eyebrow')) ?></p>
    <h2 class="sec-title"><?= $e(t('collections.title_1')) ?><br /><em><?= $e(t('collections.title_2')) ?></em></h2>
  </div>

  <div class="coll-grid reveal">
    <?php foreach ($collections as $i => $c): ?>
      <a class="coll-card" href="/door-showroom/collections" aria-label="<?= $e($c['name']) ?>">
        <span class="coll-card-img" style="background-image:url('<?= $img($c['file']) ?>')"></span>
        <span class="coll-card-shade"></span>
        <span class="coll-card-num"><?= $e(t('cfg.collection')) ?> <?= $e($c['num']) ?></span>
        <span class="coll-card-body">
          <span class="coll-card-name"><?= $e($c['name']) ?></span>
          <span class="coll-card-line"><?= $e(t('collections.line.' . $c['name'])) ?></span>
          <span class="coll-card-specs">
            <span><?= $e(t('collections.spec_measure')) ?></span>
            <span><?= $e(t('collections.spec_finish')) ?></span>
            <span><?= $e(t('collections.spec_local')) ?></span>
          </span>
          <span class="coll-card-cta"><?= $e(t('collections.explore')) ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </span>
        </span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- ░ 2b · PROCESS / CONFIGURE IN 7 STEPS ░ -->
<section class="process" id="process">
  <div class="process-bg" aria-hidden="true">
    <img src="<?= $img('configurator-tablet.png') ?>" alt="" role="presentation" loading="lazy" />
    <div class="process-overlay"></div>
  </div>
  <div class="process-inner">
    <div class="process-copy reveal">
      <p class="eyebrow"><?= $e(t('process.eyebrow')) ?></p>
      <h2 class="sec-title"><?= $e(t('process.title_1')) ?><br /><em><?= $e(t('process.title_2')) ?></em></h2>
      <p class="process-lead"><?= $e(t('process.lead')) ?></p>
      <?php $procSteps = \App\Core\I18n::group('process.steps'); ?>
      <ol class="process-steps">
        <?php foreach ($process as $i => $s): $ts = $procSteps[$i] ?? null; ?>
          <li class="process-step reveal<?= $i ? ' reveal-d' . min($i, 3) : '' ?>">
            <span class="process-step-num"><?= $e($s['num']) ?></span>
            <span class="process-step-body">
              <strong><?= $e($ts['name'] ?? $s['name']) ?></strong>
              <em><?= $e($ts['desc'] ?? $s['desc']) ?></em>
            </span>
          </li>
        <?php endforeach; ?>
      </ol>
      <a href="/door-showroom/configure" class="btn btn--gold btn--lg"><?= $e(t('process.cta')) ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>
  </div>
</section>

<!-- ░ 3 · FEATURED DOORS ░ -->
<section class="featured" id="featured">
  <div class="sec-intro reveal">
    <p class="eyebrow"><?= $e(t('featured.eyebrow')) ?></p>
    <h2 class="sec-title"><?= $e(t('featured.title_1')) ?><br /><em><?= $e(t('featured.title_2')) ?></em></h2>
  </div>
  <div class="featured-grid">
    <?php foreach ($featured as $i => $d): ?>
      <article class="fdoor reveal<?= $i ? ' reveal-d' . min($i, 3) : '' ?>">
        <div class="fdoor-media">
          <img src="<?= $img($d['file']) ?>" alt="<?= $e($d['name']) ?>" loading="lazy" />
          <div class="fdoor-actions">
            <a href="/door-showroom/product/<?= $e($d['slug']) ?>" class="btn btn--gold btn--sm"><?= $e(t('featured.view')) ?></a>
            <a href="#quote" class="btn btn--outline-light btn--sm"><?= $e(t('featured.quote')) ?></a>
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

<!-- ░ 5 · INSPIRATION GALLERY ░ -->
<section class="inspo" id="inspiration">
  <div class="inspo-band reveal">
    <div class="inspo-copy">
      <p class="eyebrow"><?= $e(t('inspo.eyebrow')) ?></p>
      <h2 class="inspo-title"><?= $e(t('inspo.title_1')) ?><br /><em><?= $e(t('inspo.title_2')) ?></em></h2>
      <p class="inspo-lead"><?= $e(t('inspo.lead')) ?></p>
      <a href="/door-showroom/collections" class="btn btn--gold"><?= $e(t('inspo.cta')) ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>
    <div class="inspo-strip">
      <?php foreach ($inspiration as $p): ?>
        <figure class="inspo-card">
          <img src="<?= $img($p['file']) ?>" alt="<?= $e($p['caption']) ?>" loading="lazy" />
          <figcaption><?= $e($p['caption']) ?></figcaption>
        </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ░ 5b · SIGNATURE COLOURS ░ -->
<?php if (!empty($colors)):
  $cBgs = ['interior-bedroom-black.png', 'interior-grey-kitchen.png', 'interior-entry-hall.png'];
?>
<section class="colours" id="colours">
  <div class="colours-bg" aria-hidden="true">
    <?php foreach ($cBgs as $bi => $bg): ?>
      <span class="colours-bg-img" data-bg="<?= $bi ?>" style="background-image:url('<?= $img($bg) ?>')"></span>
    <?php endforeach; ?>
  </div>
  <div class="colours-inner reveal">
    <div class="colours-head">
      <p class="eyebrow"><?= $e(t('colours.eyebrow')) ?></p>
      <h2 class="sec-title"><?= $e(t('colours.title_1')) ?><br /><em><?= $e(t('colours.title_2')) ?></em></h2>
    </div>
    <div class="colours-row" id="coloursRow">
      <?php foreach (array_slice($colors, 0, 9) as $ci => $c): ?>
        <span class="colour-chip" data-bg="<?= $ci % count($cBgs) ?>"
              title="<?= $e($c['name'] . ($c['collection'] ? ' · ' . $c['collection'] : '')) ?>"
              style="<?= $c['tex'] ? "background-image:url('" . $e($c['tex']) . "');" : '' ?>background-color:<?= $e($c['hex']) ?>;">
          <span class="colour-chip-name"><?= $e($c['name']) ?></span>
        </span>
      <?php endforeach; ?>
    </div>
    <a href="/door-showroom/configure" class="colours-link"><?= $e(t('colours.cta')) ?>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </a>
  </div>
</section>
<?php endif; ?>

<!-- ░ 6 · WHY ░ -->
<section class="why" id="why">
  <div class="sec-intro sec-intro--center reveal">
    <p class="eyebrow"><?= $e(t('why.eyebrow')) ?></p>
    <h2 class="sec-title"><?= $e(t('why.title_1')) ?><br /><em><?= $e(t('why.title_2')) ?></em></h2>
  </div>
  <div class="why-grid reveal">
    <div class="why-item">
      <span class="why-icon"><svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 12h36M6 24h24M6 36h12"/><path d="M40 22l4 4-4 4"/></svg></span>
      <h3><?= $e(t('why.measure')) ?></h3>
      <p><?= $e(t('why.measure_sub')) ?></p>
    </div>
    <div class="why-item">
      <span class="why-icon"><svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M24 4l5 14h15l-12 9 5 15-13-9-13 9 5-15-12-9h15z"/></svg></span>
      <h3><?= $e(t('why.quality')) ?></h3>
      <p><?= $e(t('why.quality_sub')) ?></p>
    </div>
    <div class="why-item">
      <span class="why-icon"><svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M24 6C14 6 6 14 6 24s8 18 18 18 18-8 18-18S34 6 24 6z"/><path d="M16 24l6 6 10-12"/></svg></span>
      <h3><?= $e(t('why.install')) ?></h3>
      <p><?= $e(t('why.install_sub')) ?></p>
    </div>
    <div class="why-item">
      <span class="why-icon"><svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M24 4l16 6v12c0 11-7 18-16 22-9-4-16-11-16-22V10z"/><path d="M17 24l5 5 9-11"/></svg></span>
      <h3><?= $e(t('why.durable')) ?></h3>
      <p><?= $e(t('why.durable_sub')) ?></p>
    </div>
  </div>
</section>

<!-- ░ 7 · QUOTE CTA ░ -->
<section class="quote" id="quote">
  <div class="quote-bg"><img src="<?= $img('interior-entry-hall.png') ?>" alt="" role="presentation" loading="lazy" /><div class="quote-overlay"></div></div>
  <div class="quote-inner reveal">
    <h2 class="quote-title"><?= $e(t('quote_cta.title_1')) ?><br /><em><?= $e(t('quote_cta.title_2')) ?></em></h2>
    <p class="quote-sub"><?= $e(t('quote_cta.sub')) ?></p>
    <div class="quote-actions">
      <a href="/door-showroom/configure" class="btn btn--gold btn--lg"><?= $e(t('quote_cta.cta')) ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
      <a href="<?= $e($wa) ?>" target="_blank" rel="noopener" class="btn btn--outline-light btn--lg"><?= $e(t('quote_cta.talk')) ?>
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 00-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1012 2zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-1.7-.1-.4-.1-.9-.3-1.6-.6-2.8-1.2-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8s.7-2 .9-2.2c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.1.1.3 0 .5l-.4.5-.3.3c-.2.2-.3.4-.2.6.2.4.8 1.3 1.6 2 .9.8 1.7 1.1 2.1 1.3.2.1.5.1.6-.1l.7-.8c.2-.2.4-.2.6-.1l1.9.9c.2.1.4.2.4.3.1.1.1.6-.1 1.2z"/></svg>
      </a>
    </div>
  </div>
</section>

<!-- ░ 8 · FOOTER ░ -->
<footer class="footer" id="contact">
  <div class="footer-grid">
    <div class="footer-brand">
      <div class="footer-logo"><img src="<?= $img('logo-adk.png') ?>" alt="ADK — Algerian Doors &amp; Kitchens" class="footer-logo-img" /></div>
      <p class="footer-tag"><?= $e(t('footer.tag')) ?></p>
      <div class="footer-social">
        <a href="#" aria-label="Facebook"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 9h3V6h-3c-2 0-3.5 1.5-3.5 3.5V11H8v3h2.5v7h3v-7H16l.5-3h-3V9.5c0-.3.2-.5.5-.5z"/></svg></a>
        <a href="#" aria-label="Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".6" fill="currentColor"/></svg></a>
        <a href="<?= $e($wa) ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 00-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1012 2zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-1.7-.1-.4-.1-.9-.3-1.6-.6-2.8-1.2-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8s.7-2 .9-2.2c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.1.1.3 0 .5l-.4.5-.3.3c-.2.2-.3.4-.2.6.2.4.8 1.3 1.6 2 .9.8 1.7 1.1 2.1 1.3.2.1.5.1.6-.1l.7-.8c.2-.2.4-.2.6-.1l1.9.9c.2.1.4.2.4.3.1.1.1.6-.1 1.2z"/></svg></a>
      </div>
    </div>
    <div class="footer-col">
      <h4><?= $e(t('footer.collections')) ?></h4>
      <ul>
        <li><a href="/door-showroom/collections">Prestige</a></li>
        <li><a href="/door-showroom/collections">Moderne</a></li>
        <li><a href="/door-showroom/collections">Heritage</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4><?= $e(t('footer.services')) ?></h4>
      <ul>
        <li><a href="/door-showroom/configure"><?= $e(t('footer.configurator')) ?></a></li>
        <li><a href="#quote"><?= $e(t('footer.made')) ?></a></li>
        <li><a href="#quote"><?= $e(t('footer.install')) ?></a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4><?= $e(t('footer.company')) ?></h4>
      <ul>
        <li><a href="#why"><?= $e(t('footer.about')) ?></a></li>
        <li><a href="#featured"><?= $e(t('footer.our_doors')) ?></a></li>
        <li><a href="#collections"><?= $e(t('footer.projects')) ?></a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4><?= $e(t('footer.contact')) ?></h4>
      <ul>
        <li><a href="tel:+213512345678">+213 5 12 34 56 78</a></li>
        <li><a href="mailto:contact@portes.dz">contact@portes.dz</a></li>
        <li><span><?= $e(t('footer.tagline')) ?></span></li>
        <li><a href="<?= $e($wa) ?>" target="_blank" rel="noopener">WhatsApp</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <span>&copy; <?= date('Y') ?> PORTES. <?= $e(t('footer.rights')) ?></span>
    <span><?= $e(t('footer.tagline')) ?></span>
  </div>
</footer>

<a href="<?= $e($wa) ?>" target="_blank" rel="noopener" class="wa-float" aria-label="<?= $e(t('wa.chat')) ?>">
  <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 00-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1012 2zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-1.7-.1-.4-.1-.9-.3-1.6-.6-2.8-1.2-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8s.7-2 .9-2.2c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.1.1.3 0 .5l-.4.5-.3.3c-.2.2-.3.4-.2.6.2.4.8 1.3 1.6 2 .9.8 1.7 1.1 2.1 1.3.2.1.5.1.6-.1l.7-.8c.2-.2.4-.2.6-.1l1.9.9c.2.1.4.2.4.3.1.1.1.6-.1 1.2z"/></svg>
</a>

<script src="/door-showroom/assets/js/home.js?v=<?= $ver('/assets/js/home.js') ?>" defer></script>
</body>
</html>
