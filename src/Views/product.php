<?php
/** @var array $product, $images, $colors, $related, $doorTypes */
/** @var string|null $construction */
/** @var float|null $basePrice */
$e = static fn ($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$wa = 'https://wa.me/213512345678';
$asset = static fn ($f) => '/door-showroom/assets/images/' . $f;

/* A curated fallback image per collection, used when the product has no uploads. */
$collectionImage = [
    'heritage' => 'chene.jpg',
    'moderne'  => 'gris-prestige.jpg',
    'prestige' => 'marron-prestige.jpg',
];
$fallback = $asset($collectionImage[$product['collection_slug'] ?? ''] ?? 'porte-scuro.jpg');

/* Build the gallery from real product images; fall back to a single curated shot. */
$gallery = [];
foreach ($images as $img) {
    $gallery[] = '/door-showroom/uploads/products/' . $img['filename'];
}
if (!$gallery) {
    $gallery = [$fallback];
}
$cover = $gallery[0];

/* Colors come from the database (product-linked, or collection colors as fallback). */
$colorUrl = static function (array $c): string {
    return !empty($c['image_filename']) ? '/door-showroom/uploads/colors/' . $c['image_filename'] : '';
};
$money = static fn ($n) => number_format((float)$n, 0, '.', ' ') . ' DZD';
$dimText = '';
if (!empty($product['width_mm']) && !empty($product['height_mm'])) {
    $dimText = (int)round($product['width_mm'] / 10) . ' × ' . (int)round($product['height_mm'] / 10) . ' cm';
} elseif (!empty($product['dimensions'])) {
    $dimText = $product['dimensions'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="<?= $e($product['name']) ?> — <?= $e($product['collection_name'] ?? 'PORTES') ?> collection. Luxury architectural door, made to measure." />
  <title><?= $e($product['name']) ?> — PORTES</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Inter:wght@300;400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/door-showroom/assets/css/home.css" />
  <link rel="stylesheet" href="/door-showroom/assets/css/product.css" />
</head>
<body>

<!-- ░ NAV ░ -->
<header class="nav is-scrolled" id="nav">
  <a href="/door-showroom" class="nav-logo">
    <span class="nav-logo-mark">PORTES</span>
    <span class="nav-logo-sub">Architectural Doors</span>
  </a>
  <nav class="nav-links" id="navLinks" aria-label="Primary">
    <a href="/door-showroom/collections">Collections</a>
    <a href="/door-showroom/configure">Configurator</a>
    <a href="/door-showroom/collections">Collections</a>
    <a href="/door-showroom#featured">Doors</a>
    <a href="/door-showroom#why">About</a>
  </nav>
  <a href="/door-showroom#quote" class="nav-cta">Request Quote</a>
  <button class="nav-burger" id="navBurger" aria-label="Open menu" aria-expanded="false"><span></span><span></span><span></span></button>
</header>

<!-- ░ 1 · HERO PRODUCT ░ -->
<section class="pd-hero">
  <div class="pd-gallery">
    <div class="pd-stage" id="pdStage">
      <img src="<?= $e($cover) ?>" alt="<?= $e($product['name']) ?>" id="pdMain" class="pd-stage-img" />
    </div>
    <?php if (count($gallery) > 1): ?>
    <div class="pd-thumbs" id="pdThumbs">
      <?php foreach ($gallery as $i => $g): ?>
        <button class="pd-thumb<?= $i === 0 ? ' is-active' : '' ?>" data-src="<?= $e($g) ?>" aria-label="View image <?= $i + 1 ?>">
          <img src="<?= $e($g) ?>" alt="" loading="lazy" />
        </button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <div class="pd-info">
    <nav class="pd-crumb" aria-label="Breadcrumb">
      <a href="/door-showroom">Home</a><span>/</span>
      <a href="/door-showroom/collections">Collections</a><span>/</span>
      <?php if (!empty($product['collection_slug'])): ?>
        <a href="/door-showroom/collections/<?= $e($product['collection_slug']) ?>"><?= $e($product['collection_name']) ?></a><span>/</span>
      <?php endif; ?>
      <span><?= $e($product['name']) ?></span>
    </nav>

    <?php if (!empty($product['collection_name'])): ?>
      <p class="pd-collection"><?= $e($product['collection_name']) ?> Collection</p>
    <?php endif; ?>
    <h1 class="pd-name"><?= $e($product['name']) ?></h1>
    <?php if (!empty($product['description'])): ?>
      <p class="pd-desc"><?= $e($product['description']) ?></p>
    <?php endif; ?>

    <?php if (!empty($colors)): ?>
    <div class="pd-block">
      <div class="pd-block-head"><span>Available Colors</span><strong id="pdColorName"><?= $e($colors[0]['name']) ?></strong></div>
      <div class="pd-swatches" id="pdSwatches" role="listbox" aria-label="Color">
        <?php foreach ($colors as $i => $c): $cu = $colorUrl($c); ?>
          <button class="pd-swatch<?= $i === 0 ? ' is-active' : '' ?>"
                  data-name="<?= $e($c['name']) ?>"
                  style="<?= $cu ? "--tex:url('" . $e($cu) . "');" : '' ?><?= !empty($c['hex']) ? '--sw:' . $e($c['hex']) : '' ?>"
                  role="option" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"
                  title="<?= $e($c['name']) ?>" aria-label="<?= $e($c['name']) ?>"></button>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($doorTypes)): ?>
    <div class="pd-block">
      <div class="pd-block-head"><span>Door Usages</span></div>
      <div class="pd-pills">
        <?php foreach ($doorTypes as $d): ?><span class="pd-pill"><?= $e($d['name']) ?></span><?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="pd-meta">
      <?php if (!empty($product['collection_name'])): ?>
        <div class="pd-meta-row"><span>Collection</span><strong><?= $e($product['collection_name']) ?></strong></div>
      <?php endif; ?>
      <?php if (!empty($construction)): ?>
        <div class="pd-meta-row"><span>Construction</span><strong><?= $e($construction) ?></strong></div>
      <?php endif; ?>
      <?php if ($dimText !== ''): ?>
        <div class="pd-meta-row"><span>Dimensions</span><strong><?= $e($dimText) ?></strong></div>
      <?php endif; ?>
      <?php if ($basePrice !== null && $basePrice > 0): ?>
        <div class="pd-meta-row pd-meta-row--price"><span>Starting from</span><strong><?= $e($money($basePrice)) ?></strong></div>
      <?php endif; ?>
    </div>

    <a href="/door-showroom/configure?product=<?= $e($product['slug']) ?>" class="btn btn--gold btn--lg btn--block">Configure This Door
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </a>
  </div>
</section>

<!-- ░ 2 · PRODUCT STORY ░ -->
<section class="pd-story">
  <div class="pd-story-inner reveal">
    <p class="eyebrow">The Craft</p>
    <h2 class="pd-h2">More than a door.<br /><em>A threshold.</em></h2>
    <div class="pd-story-cols">
      <p><?= $e($product['description'] ?: 'Every PORTES door begins as raw material and a measured opening. It is engineered to last decades and designed to define the first impression of a space.') ?></p>
      <p>Each <?= $e($product['name']) ?> is built at the intersection of craftsmanship, classical proportion and contemporary restraint — and made to your exact specification.</p>
    </div>
  </div>
</section>

<!-- ░ 3 · AVAILABLE COLORS (homepage-style circles) ░ -->
<?php if (!empty($colors)): ?>
<section class="pd-finishes">
  <div class="sec-intro sec-intro--center reveal">
    <p class="eyebrow">Available Colors</p>
    <h2 class="pd-h2">Choose your<br /><em>character.</em></h2>
  </div>
  <div class="pd-finishes-ring reveal" id="pdColorRing">
    <?php foreach ($colors as $i => $c): $cu = $colorUrl($c); ?>
      <button class="pd-finish<?= $i === 0 ? ' is-active' : '' ?>" data-name="<?= $e($c['name']) ?>" aria-label="<?= $e($c['name']) ?>">
        <span class="pd-finish-dot" style="<?= $cu ? "background-image:url('" . $e($cu) . "');" : '' ?><?= !empty($c['hex']) ? 'background-color:' . $e($c['hex']) : '' ?>"></span>
        <span class="pd-finish-name"><?= $e($c['name']) ?></span>
      </button>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- ░ 4 · SPECIFICATIONS ░ -->
<section class="pd-specs">
  <div class="sec-intro reveal">
    <p class="eyebrow">Specifications</p>
    <h2 class="pd-h2">Built to<br /><em>exact standard.</em></h2>
  </div>
  <div class="pd-specs-grid reveal">
    <div class="pd-spec"><span>Width Range</span><strong>500 – 2000 mm</strong><small>Bespoke widths available</small></div>
    <div class="pd-spec"><span>Height Range</span><strong>1500 – 3000 mm</strong><small>Made to your opening</small></div>
    <div class="pd-spec"><span>Collection</span><strong><?= $e($product['collection_name'] ?? 'Signature') ?></strong><small>Design line</small></div>
    <div class="pd-spec"><span>Construction</span><strong><?= $construction ? $e($construction) : 'Made to order' ?></strong><small>Build type</small></div>
    <div class="pd-spec"><span>Colors</span><strong><?= $e(count($colors)) ?> option<?= count($colors) === 1 ? '' : 's' ?></strong><small>Collection palette</small></div>
    <div class="pd-spec"><span>Warranty</span><strong>25 Years</strong><small>Structural</small></div>
  </div>
</section>

<!-- ░ 5 · RELATED PRODUCTS ░ -->
<?php if (!empty($related)): ?>
<section class="pd-related">
  <div class="sec-intro reveal">
    <p class="eyebrow">You May Also Like</p>
    <h2 class="pd-h2">From the same<br /><em>collection.</em></h2>
  </div>
  <div class="pd-related-grid">
    <?php foreach ($related as $i => $r):
      $rImg = $r['cover'] ? '/door-showroom/uploads/products/' . $r['cover'] : $fallback;
    ?>
      <article class="pd-rel reveal<?= $i ? ' reveal-d' . min($i, 3) : '' ?>">
        <a href="/door-showroom/products/<?= $e($r['slug']) ?>" class="pd-rel-link">
          <div class="pd-rel-media"><img src="<?= $e($rImg) ?>" alt="<?= $e($r['name']) ?>" loading="lazy" /></div>
          <div class="pd-rel-body">
            <?php if (!empty($r['collection_name'])): ?><span class="pd-rel-collection"><?= $e($r['collection_name']) ?></span><?php endif; ?>
            <h3 class="pd-rel-name"><?= $e($r['name']) ?></h3>
            <span class="pd-rel-cta">View Details
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </span>
          </div>
        </a>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- ░ 6 · CTA ░ -->
<section class="quote" id="quote">
  <div class="quote-bg"><img src="<?= $e($fallback) ?>" alt="" role="presentation" loading="lazy" /><div class="quote-overlay"></div></div>
  <div class="quote-inner reveal">
    <h2 class="quote-title">Ready to<br /><em>design yours?</em></h2>
    <p class="quote-sub">Configure this door to your exact specification — color, usage, construction and dimensions — and request a personal quote in minutes.</p>
    <div class="quote-actions">
      <a href="/door-showroom/configure?product=<?= $e($product['slug']) ?>" class="btn btn--gold btn--lg">Configure This Door
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
      <a href="<?= $e($wa) ?>" target="_blank" rel="noopener" class="btn btn--outline-light btn--lg">Request Quote</a>
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
    <div class="footer-col"><h4>Collections</h4><ul>
      <li><a href="/door-showroom/collections/heritage">Heritage</a></li>
      <li><a href="/door-showroom/collections/moderne">Moderne</a></li>
      <li><a href="/door-showroom/collections/prestige">Prestige</a></li>
    </ul></div>
    <div class="footer-col"><h4>Services</h4><ul>
      <li><a href="/door-showroom/configure">Configurator</a></li>
      <li><a href="/door-showroom#quote">Made to Measure</a></li>
      <li><a href="/door-showroom#quote">Installation</a></li>
    </ul></div>
    <div class="footer-col"><h4>Company</h4><ul>
      <li><a href="/door-showroom#why">About Us</a></li>
      <li><a href="/door-showroom#featured">Our Doors</a></li>
      <li><a href="/door-showroom/collections">Projects</a></li>
    </ul></div>
    <div class="footer-col"><h4>Contact</h4><ul>
      <li><a href="tel:+213512345678">+213 5 12 34 56 78</a></li>
      <li><a href="mailto:contact@portes.dz">contact@portes.dz</a></li>
      <li><span>Algiers, Algeria</span></li>
      <li><a href="<?= $e($wa) ?>" target="_blank" rel="noopener">WhatsApp</a></li>
    </ul></div>
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
<script src="/door-showroom/assets/js/product.js" defer></script>
</body>
</html>
