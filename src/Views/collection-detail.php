<?php
/** @var bool $notFound */
/** @var array|null $page */
$e  = static fn ($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$wa = 'https://wa.me/213512345678';
$L   = \App\Core\I18n::lang();
$DIR = \App\Core\I18n::dir();
?>
<!DOCTYPE html>
<html lang="<?= $e($L) ?>" dir="<?= $e($DIR) ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $notFound ? $e(t('coll_detail.not_found_t')) : $e($page['name']) ?> — PORTES</title>
  <?php if (!$notFound): ?>
  <meta name="description" content="<?= $e($page['name']) ?> — PORTES." />
  <?php endif; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Inter:wght@300;400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/door-showroom/assets/css/home.css" />
  <link rel="stylesheet" href="/door-showroom/assets/css/collection-detail.css" />
  <link rel="stylesheet" href="/door-showroom/assets/css/i18n.css" />
</head>
<body>

<!-- ░ NAV ░ -->
<header class="nav is-scrolled" id="nav">
  <a href="/door-showroom" class="nav-logo">
    <img src="/door-showroom/assets/images/logo-adk.png" alt="ADK — Algerian Doors &amp; Kitchens" class="nav-logo-img" />
  </a>
  <nav class="nav-links" id="navLinks" aria-label="Primary">
    <a href="/door-showroom/collections" class="is-current"><?= $e(t('nav.collections')) ?></a>
    <a href="/door-showroom#process"><?= $e(t('nav.configurator')) ?></a>
    <a href="/door-showroom#colours"><?= $e(t('colours.eyebrow')) ?></a>
    <a href="/door-showroom#featured"><?= $e(t('nav.doors')) ?></a>
    <a href="/door-showroom#why"><?= $e(t('nav.about')) ?></a>
    <?php $variant = 'mobile'; include APP_ROOT . '/src/Views/partials/lang-switch.php'; ?>
  </nav>
  <?php $variant = ''; include APP_ROOT . '/src/Views/partials/lang-switch.php'; ?>
  <a href="/door-showroom#quote" class="nav-cta"><?= $e(t('nav.request_quote')) ?></a>
  <button class="nav-burger" id="navBurger" aria-label="<?= $e(t('nav.menu_open')) ?>" aria-expanded="false"><span></span><span></span><span></span></button>
</header>

<?php if ($notFound): ?>
  <section class="cd-404">
    <p class="eyebrow">404</p>
    <h1><?= $e(t('coll_detail.not_found_t')) ?></h1>
    <p><?= $e(t('coll_detail.not_found_d')) ?></p>
    <a href="/door-showroom/collections" class="btn btn--gold"><?= $e(t('common.view_all_coll')) ?></a>
  </section>
<?php else: ?>

<!-- ░ HERO ░ -->
<section class="cd-hero<?= $page['hero'] ? '' : ' cd-hero--plain' ?>">
  <?php if ($page['hero']): ?>
  <div class="cd-hero-bg">
    <img src="<?= $e($page['hero']) ?>" alt="" role="presentation" class="cd-hero-img" />
    <div class="cd-hero-overlay"></div>
  </div>
  <?php endif; ?>
  <div class="cd-hero-inner">
    <nav class="cd-crumb" aria-label="Breadcrumb">
      <a href="/door-showroom"><?= $e(t('common.home')) ?></a><span>/</span><a href="/door-showroom/collections"><?= $e(t('common.collections')) ?></a><span>/</span><span><?= $e($page['name']) ?></span>
    </nav>
    <p class="cd-hero-label"><?= $e(t('coll_detail.the_coll')) ?></p>
    <h1 class="cd-hero-name"><?= $e($page['name']) ?></h1>
    <?php if ($page['description'] !== ''): ?>
      <p class="cd-hero-desc"><?= $e($page['description']) ?></p>
    <?php endif; ?>
    <div class="cd-hero-actions">
      <?php if (!empty($page['models'])): ?>
        <a href="#models" class="btn btn--gold btn--lg"><?= $e(t('coll_detail.explore_prod')) ?>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
      <?php endif; ?>
      <a href="/door-showroom/configure?collection=<?= $e($page['slug']) ?>" class="btn btn--outline-light btn--lg"><?= $e(t('coll_detail.configure_door')) ?></a>
    </div>
  </div>
  <?php if ($page['hero']): ?><span class="cd-hero-scroll" aria-hidden="true"></span><?php endif; ?>
</section>

<!-- ░ FEATURES (only if DB rows exist) ░ -->
<?php if (!empty($page['features'])): ?>
<section class="cd-features">
  <div class="sec-intro sec-intro--center reveal">
    <p class="eyebrow"><?= $e(t('coll_detail.feat_eyebrow')) ?></p>
    <h2 class="cd-h2"><?= $e(t('coll_detail.feat_h_1')) ?><br /><em><?= $e(t('coll_detail.feat_h_2')) ?></em></h2>
  </div>
  <div class="cd-features-grid reveal">
    <?php foreach ($page['features'] as $i => $f): ?>
      <div class="cd-feature">
        <span class="cd-feature-num"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
        <h3><?= $e($f['title']) ?></h3>
        <?php if (!empty($f['description'])): ?><p><?= $e($f['description']) ?></p><?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- ░ MODELS / EMPTY STATE ░ -->
<section class="cd-models" id="models">
  <div class="sec-intro reveal">
    <p class="eyebrow"><?= $e(t('coll_detail.models_eyebrow')) ?></p>
    <h2 class="cd-h2"><?= $e(t('coll_detail.models_h_1')) ?><br /><em><?= $e(t('coll_detail.models_h_2', ['name' => $page['name']])) ?></em></h2>
  </div>

  <?php if (!empty($page['models'])): ?>
    <div class="cd-models-grid">
      <?php foreach ($page['models'] as $i => $m): ?>
        <article class="cd-model reveal<?= $i ? ' reveal-d' . min($i, 3) : '' ?>">
          <div class="cd-model-media<?= $m['file'] ? '' : ' cd-model-media--empty' ?>">
            <?php if ($m['file']): ?>
              <img src="<?= $e($m['file']) ?>" alt="<?= $e($m['name']) ?>" loading="lazy" />
            <?php else: ?>
              <span class="cd-model-placeholder" aria-hidden="true">
                <svg viewBox="0 0 48 64" fill="none" stroke="currentColor" stroke-width="1"><rect x="6" y="2" width="36" height="60" rx="1"/><circle cx="34" cy="32" r="1.5" fill="currentColor"/></svg>
              </span>
            <?php endif; ?>
          </div>
          <div class="cd-model-body">
            <h3 class="cd-model-name"><?= $e($m['name']) ?></h3>
            <?php if (!empty($m['dimensions'])): ?>
              <div class="cd-model-spec"><span><em><?= $e(t('coll_detail.dimensions')) ?></em><?= $e($m['dimensions']) ?></span></div>
            <?php endif; ?>
            <?php if ($m['desc']): ?><p class="cd-model-desc"><?= $e($m['desc']) ?></p><?php endif; ?>
            <div class="cd-model-actions">
              <?php if (!empty($m['slug'])): ?>
                <a href="/door-showroom/product/<?= $e($m['slug']) ?>" class="btn btn--outline btn--sm"><?= $e(t('coll_detail.view_details')) ?></a>
              <?php endif; ?>
              <a href="/door-showroom/configure?collection=<?= $e($page['slug']) ?>" class="btn btn--gold btn--sm"><?= $e(t('coll_detail.configure')) ?></a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="cd-empty reveal">
      <span class="cd-empty-icon" aria-hidden="true">
        <svg viewBox="0 0 48 64" fill="none" stroke="currentColor" stroke-width="1"><rect x="6" y="2" width="36" height="60" rx="1"/><circle cx="34" cy="32" r="1.5" fill="currentColor"/></svg>
      </span>
      <h3><?= $e(t('coll_detail.soon_t')) ?></h3>
      <p><?= $e(t('coll_detail.soon_d', ['name' => $page['name']])) ?></p>
      <a href="/door-showroom/configure?collection=<?= $e($page['slug']) ?>" class="btn btn--gold"><?= $e(t('coll_detail.configure_door')) ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>
  <?php endif; ?>
</section>

<!-- ░ COLORS (only if this collection has colors) ░ -->
<?php if (!empty($page['colors'])): ?>
<section class="cd-finishes">
  <div class="sec-intro reveal">
    <p class="eyebrow"><?= $e(t('coll_detail.colors_eyebrow')) ?></p>
    <h2 class="cd-h2"><?= $e(t('coll_detail.palette_1', ['name' => $page['name']])) ?><br /><em><?= $e(t('coll_detail.palette_2', ['name' => $page['name']])) ?></em></h2>
  </div>
  <div class="cd-finishes-grid reveal">
    <?php foreach ($page['colors'] as $f): ?>
      <div class="cd-finish">
        <?php if (!empty($f['image_url'])): ?>
          <span class="cd-finish-swatch" style="background-image:url('<?= $e($f['image_url']) ?>')"></span>
        <?php elseif (!empty($f['hex'])): ?>
          <span class="cd-finish-swatch" style="background-color:<?= $e($f['hex']) ?>"></span>
        <?php else: ?>
          <span class="cd-finish-swatch cd-finish-swatch--blank" aria-hidden="true"></span>
        <?php endif; ?>
        <div class="cd-finish-text">
          <h3><?= $e($f['name']) ?></h3>
          <?php if (!empty($f['description'])): ?><p><?= $e($f['description']) ?></p><?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- ░ PROJECTS (only if a projects system exists — currently none, so hidden) ░ -->
<?php if (!empty($page['projects'])): ?>
<section class="cd-projects">
  <div class="sec-intro reveal">
    <p class="eyebrow">Projects</p>
    <h2 class="cd-h2"><?= $e($page['name']) ?> in<br /><em>the world.</em></h2>
  </div>
  <div class="cd-projects-grid reveal">
    <?php foreach ($page['projects'] as $i => $p): ?>
      <figure class="cd-project<?= $i === 0 ? ' cd-project--lead' : '' ?>">
        <img src="<?= $e($p['img']) ?>" alt="<?= $e($p['name']) ?>" loading="lazy" />
        <figcaption>
          <span class="cd-project-name"><?= $e($p['name']) ?></span>
          <span class="cd-project-loc"><?= $e($p['location']) ?> · <?= $e($page['name']) ?></span>
        </figcaption>
      </figure>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- ░ CTA ░ -->
<section class="quote<?= $page['hero'] ? '' : ' quote--plain' ?>" id="quote">
  <?php if ($page['hero']): ?>
  <div class="quote-bg"><img src="<?= $e($page['hero']) ?>" alt="" role="presentation" loading="lazy" /><div class="quote-overlay"></div></div>
  <?php endif; ?>
  <div class="quote-inner reveal">
    <h2 class="quote-title"><?= $e(t('coll_detail.cta_title_1')) ?><br /><em><?= $e(t('coll_detail.cta_title_2')) ?></em></h2>
    <p class="quote-sub"><?= $e(t('coll_detail.cta_sub', ['name' => $page['name']])) ?></p>
    <div class="quote-actions">
      <a href="/door-showroom/configure?collection=<?= $e($page['slug']) ?>" class="btn btn--gold btn--lg"><?= $e(t('coll_detail.cta_btn')) ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
      <a href="<?= $e($wa) ?>" target="_blank" rel="noopener" class="btn btn--outline-light btn--lg"><?= $e(t('common.request_quote')) ?></a>
    </div>
  </div>
</section>

<?php endif; /* notFound */ ?>

<!-- ░ FOOTER ░ -->
<footer class="footer" id="contact">
  <div class="footer-grid">
    <div class="footer-brand">
      <div class="footer-logo"><img src="/door-showroom/assets/images/logo-adk.png" alt="ADK — Algerian Doors &amp; Kitchens" class="footer-logo-img" /></div>
      <p class="footer-tag"><?= $e(t('footer.tag')) ?></p>
      <div class="footer-social">
        <a href="#" aria-label="Facebook"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 9h3V6h-3c-2 0-3.5 1.5-3.5 3.5V11H8v3h2.5v7h3v-7H16l.5-3h-3V9.5c0-.3.2-.5.5-.5z"/></svg></a>
        <a href="#" aria-label="Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".6" fill="currentColor"/></svg></a>
        <a href="<?= $e($wa) ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 00-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1012 2zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-1.7-.1-.4-.1-.9-.3-1.6-.6-2.8-1.2-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8s.7-2 .9-2.2c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.1.1.3 0 .5l-.4.5-.3.3c-.2.2-.3.4-.2.6.2.4.8 1.3 1.6 2 .9.8 1.7 1.1 2.1 1.3.2.1.5.1.6-.1l.7-.8c.2-.2.4-.2.6-.1l1.9.9c.2.1.4.2.4.3.1.1.1.6-.1 1.2z"/></svg></a>
      </div>
    </div>
    <div class="footer-col"><h4><?= $e(t('footer.collections')) ?></h4><ul>
      <li><a href="/door-showroom/collections/heritage">Heritage</a></li>
      <li><a href="/door-showroom/collections/moderne">Moderne</a></li>
      <li><a href="/door-showroom/collections/prestige">Prestige</a></li>
    </ul></div>
    <div class="footer-col"><h4><?= $e(t('footer.services')) ?></h4><ul>
      <li><a href="/door-showroom/configure"><?= $e(t('footer.configurator')) ?></a></li>
      <li><a href="/door-showroom#quote"><?= $e(t('footer.made')) ?></a></li>
      <li><a href="/door-showroom#quote"><?= $e(t('footer.install')) ?></a></li>
    </ul></div>
    <div class="footer-col"><h4><?= $e(t('footer.company')) ?></h4><ul>
      <li><a href="/door-showroom#why"><?= $e(t('footer.about')) ?></a></li>
      <li><a href="/door-showroom#featured"><?= $e(t('footer.our_doors')) ?></a></li>
      <li><a href="/door-showroom/collections"><?= $e(t('footer.projects')) ?></a></li>
    </ul></div>
    <div class="footer-col"><h4><?= $e(t('footer.contact')) ?></h4><ul>
      <li><a href="tel:+213512345678">+213 5 12 34 56 78</a></li>
      <li><a href="mailto:contact@portes.dz">contact@portes.dz</a></li>
      <li><span><?= $e(t('footer.tagline')) ?></span></li>
      <li><a href="<?= $e($wa) ?>" target="_blank" rel="noopener">WhatsApp</a></li>
    </ul></div>
  </div>
  <div class="footer-bottom">
    <span>&copy; <?= date('Y') ?> PORTES. <?= $e(t('footer.rights')) ?></span>
    <span><?= $e(t('footer.tagline')) ?></span>
  </div>
</footer>

<a href="<?= $e($wa) ?>" target="_blank" rel="noopener" class="wa-float" aria-label="<?= $e(t('wa.chat')) ?>">
  <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 00-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1012 2zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-1.7-.1-.4-.1-.9-.3-1.6-.6-2.8-1.2-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8s.7-2 .9-2.2c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.1.1.3 0 .5l-.4.5-.3.3c-.2.2-.3.4-.2.6.2.4.8 1.3 1.6 2 .9.8 1.7 1.1 2.1 1.3.2.1.5.1.6-.1l.7-.8c.2-.2.4-.2.6-.1l1.9.9c.2.1.4.2.4.3.1.1.1.6-.1 1.2z"/></svg>
</a>

<script src="/door-showroom/assets/js/home.js" defer></script>
<?php if (!$notFound && $page['hero']): ?>
<script>
  (function () {
    var img = document.querySelector('.cd-hero-img');
    if (!img) return;
    var go = function () { requestAnimationFrame(function () { img.classList.add('is-in'); }); };
    if (img.complete) go(); else img.addEventListener('load', go);
  }());
</script>
<?php endif; ?>
</body>
</html>
