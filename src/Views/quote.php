<?php
declare(strict_types=1);
/** @var array $collections, $materials, $colors, $doorTypes, $features */
/** @var string $token */
$e   = static fn ($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$wa  = 'https://wa.me/213512345678';

// Lookup data so the JS can label the config and resolve the preview image.
$collectionImages = [
    'heritage' => 'chene.jpg', 'moderne' => 'gris.jpg', 'prestige' => 'gris-prestige.jpg',
];
$qData = [
    'csrf'       => $token,
    'quoteUrl'   => '/door-showroom/configure/quote',
    'priceUrl'   => '/door-showroom/configure/price',
    'configureUrl' => '/door-showroom/configure',
    'collections'=> array_map(static fn ($c) => [
        'id' => (int)$c['id'], 'name' => $c['name'],
        'img' => '/door-showroom/assets/images/' . ($collectionImages[$c['slug']] ?? 'marron-prestige.jpg'),
    ], $collections),
    'materials'  => array_map(static fn ($m) => ['id' => (int)$m['id'], 'name' => $m['name']], $materials),
    'colors'     => array_map(static fn ($c) => ['id' => (int)$c['id'], 'name' => $c['name'], 'hex' => $c['hex']], $colors),
    'doorTypes'  => array_map(static fn ($d) => ['id' => (int)$d['id'], 'name' => $d['name']], $doorTypes),
    'roomTypes'  => array_map(static fn ($r) => ['id' => (int)$r['id'], 'name' => $r['name']], $roomTypes ?? []),
    'features'   => array_map(static fn ($f) => ['id' => (int)$f['id'], 'name' => $f['name']], $features),
];
$ver = static fn ($p) => @filemtime(APP_ROOT . '/public' . $p) ?: '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Request a tailored quotation for your bespoke PORTES door. Our specialists respond within 24–48 hours." />
  <title>Request a Quote — PORTES</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Inter:wght@300;400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/door-showroom/assets/css/home.css?v=<?= $ver('/assets/css/home.css') ?>" />
  <link rel="stylesheet" href="/door-showroom/assets/css/quote.css?v=<?= $ver('/assets/css/quote.css') ?>" />
</head>
<body class="q-body">

<!-- ░ TOP BAR ░ -->
<header class="q-top">
  <a href="/door-showroom" class="q-logo">
    <span class="q-logo-mark">PORTES</span>
    <span class="q-logo-sub">Request a Quote</span>
  </a>
  <a href="/door-showroom/configure" class="q-back-link">&larr; Back to Configurator</a>
</header>

<!-- ░ PROGRESS ░ -->
<div class="q-steps" id="qSteps">
  <button class="q-step is-active" data-step="0" type="button"><span>01</span> Review</button>
  <span class="q-step-line"></span>
  <button class="q-step" data-step="1" type="button"><span>02</span> Your Details</button>
  <span class="q-step-line"></span>
  <button class="q-step" data-step="2" type="button"><span>03</span> Confirmation</button>
</div>

<main class="q-main">

  <!-- ══ STEP 1 — REVIEW CONFIGURATION ══ -->
  <section class="q-panel is-active" data-panel="0">
    <div class="q-review">
      <div class="q-review-visual">
        <div class="q-render" id="qRender"><span class="q-render-tint" id="qRenderTint"></span></div>
        <span class="q-render-dim" id="qDim">—</span>
      </div>
      <div class="q-review-body">
        <p class="eyebrow">Step 01 — Review</p>
        <h1 class="q-h1">Your door is<br /><em>ready.</em></h1>
        <p class="q-lead">Confirm your configuration below. Submit your project details and our specialists will prepare a tailored quotation within 24–48 hours.</p>

        <dl class="q-spec" id="qSpec">
          <div><dt>Room Type</dt><dd data-spec="room">—</dd></div>
          <div><dt>Collection</dt><dd data-spec="collection">—</dd></div>
          <div><dt>Colour</dt><dd data-spec="color">—</dd></div>
          <div><dt>Usage</dt><dd data-spec="doorType">—</dd></div>
          <div><dt>Dimensions</dt><dd data-spec="dim">—</dd></div>
          <div class="q-spec-features" data-features-row hidden><dt>Options</dt><dd data-spec="features">—</dd></div>
        </dl>

        <div class="q-price">
          <span class="q-price-label">Estimated Price</span>
          <strong class="q-price-value" id="qPrice">—</strong>
          <span class="q-price-note">Indicative — your final quotation is confirmed by our team.</span>
        </div>

        <div class="q-review-actions">
          <button class="btn btn--gold btn--lg" id="qToDetails" type="button">Continue to Details
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </button>
          <a href="/door-showroom/configure" class="btn btn--outline btn--lg">Edit Configuration</a>
        </div>
      </div>
    </div>
  </section>

  <!-- ══ STEP 2 — PROJECT INFORMATION ══ -->
  <section class="q-panel" data-panel="1">
    <div class="q-form-wrap">
      <p class="eyebrow">Step 02 — Your Details</p>
      <h2 class="q-h2">Project<br /><em>information.</em></h2>
      <p class="q-lead">Tell us where this door will live. Fields marked * are required.</p>

      <form class="q-form" id="qForm" novalidate>
        <div class="q-field-grid">
          <div class="q-field">
            <label for="qName">Full Name *</label>
            <input type="text" id="qName" name="full_name" autocomplete="name" maxlength="120" required />
            <span class="q-err" data-err="full_name"></span>
          </div>
          <div class="q-field">
            <label for="qEmail">Email *</label>
            <input type="email" id="qEmail" name="email" autocomplete="email" maxlength="180" required />
            <span class="q-err" data-err="email"></span>
          </div>
          <div class="q-field">
            <label for="qPhone">Phone *</label>
            <input type="tel" id="qPhone" name="phone" autocomplete="tel" maxlength="30" required />
            <span class="q-err" data-err="phone"></span>
          </div>
          <div class="q-field">
            <label for="qCompany">Company <span class="q-opt">(optional)</span></label>
            <input type="text" id="qCompany" name="company" autocomplete="organization" maxlength="160" />
            <span class="q-err" data-err="company"></span>
          </div>
          <div class="q-field">
            <label for="qCountry">Installation Country *</label>
            <input type="text" id="qCountry" name="country" autocomplete="country-name" maxlength="100" value="Algeria" required />
            <span class="q-err" data-err="country"></span>
          </div>
          <div class="q-field">
            <label for="qCity">Installation City *</label>
            <input type="text" id="qCity" name="city" autocomplete="address-level2" maxlength="100" required />
            <span class="q-err" data-err="city"></span>
          </div>
        </div>

        <div class="q-field-grid">
          <div class="q-field">
            <label for="qInstallDate">Desired Installation Date <span class="q-opt">(optional)</span></label>
            <input type="date" id="qInstallDate" name="install_date" />
            <span class="q-err" data-err="install_date"></span>
          </div>
          <div class="q-field">
            <label for="qQuantity">Quantity of Doors</label>
            <input type="number" id="qQuantity" name="quantity" min="1" max="9999" value="1" />
            <span class="q-err" data-err="quantity"></span>
          </div>
        </div>

        <div class="q-field">
          <label for="qNotes">Additional Notes <span class="q-opt">(optional)</span></label>
          <textarea id="qNotes" name="notes" rows="4" maxlength="3000" placeholder="Anything our specialists should know — opening direction, site constraints, timeline…"></textarea>
          <span class="q-err" data-err="notes"></span>
        </div>

        <!-- honeypot: hidden from humans, catches bots -->
        <div class="q-hp" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden">
          <label>Company website<input type="text" name="company_website" tabindex="-1" autocomplete="off" /></label>
        </div>

        <div class="q-form-actions">
          <button type="button" class="btn btn--outline btn--lg" id="qBackToReview">&larr; Back</button>
          <button type="submit" class="btn btn--gold btn--lg" id="qSubmit">Request My Quote
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </button>
        </div>
        <p class="q-form-error" id="qFormError" hidden></p>
      </form>
    </div>
  </section>

  <!-- ══ STEP 3 — SUCCESS ══ -->
  <section class="q-panel" data-panel="2">
    <div class="q-success">
      <span class="q-success-mark" aria-hidden="true">
        <svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="32" cy="32" r="29"/><path d="M20 33l8 8 16-18" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </span>
      <p class="eyebrow">Request Received</p>
      <h2 class="q-success-title">Thank you.<br /><em>Your project has been received.</em></h2>
      <div class="q-success-ref">
        <span>Your reference</span>
        <strong id="qRefOut">—</strong>
      </div>
      <p class="q-success-text">Our specialists will review your requirements and contact you within <strong>24–48 hours</strong> with a tailored quotation.</p>
      <div class="q-success-actions">
        <button class="btn btn--outline-light btn--lg" id="qDownloadPdf" type="button">Download Configuration</button>
        <a href="/door-showroom/configure" class="btn btn--gold btn--lg">Configure Another Door</a>
        <a href="/door-showroom" class="btn btn--outline btn--lg">Return Home</a>
      </div>
    </div>
  </section>

</main>

<!-- ░ empty-config state (no configuration found) ░ -->
<section class="q-empty" id="qEmpty" hidden>
  <p class="eyebrow">No Configuration</p>
  <h2 class="q-h2">Start with a door.</h2>
  <p class="q-lead">There’s no configuration to quote yet. Build your door first, then request a tailored quotation.</p>
  <a href="/door-showroom/configure" class="btn btn--gold btn--lg">Open the Configurator
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
  </a>
</section>

<script type="application/json" id="qData"><?= json_encode($qData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
<script src="/door-showroom/assets/js/quote.js?v=<?= $ver('/assets/js/quote.js') ?>"></script>
</body>
</html>
