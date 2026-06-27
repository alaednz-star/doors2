<?php
declare(strict_types=1);
/** @var array $colorsData, $collectionsData, $usages, $constructions, $matrix, $productsData */
/** @var int|null $preColorId, $preCollectionId */
/** @var string $token */
$e = static fn ($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

$steps = ['Collection', 'Color', 'Usage', 'Construction', 'Door Design', 'Dimensions', 'Review'];

$cfgData = [
    'csrf'      => $token,
    'priceUrl'  => '/door-showroom/configure/price',
    'quoteUrl'  => '/door-showroom/configure/quote',
    'saveUrl'   => '/door-showroom/configure/save',
    'preColorId'      => $preColorId,
    'preCollectionId' => $preCollectionId,
    'colors'        => $colorsData,
    'collections'   => $collectionsData,
    'usages'        => array_map(static fn ($u) => ['id' => (int)$u['id'], 'name' => $u['name']], $usages),
    'constructions' => array_map(static fn ($c) => ['id' => (int)$c['id'], 'name' => $c['name']], $constructions),
    'products'      => $productsData,
    'matrix' => array_map(static fn ($r) => [
        'collection_id'   => (int)$r['collection_id'],
        'usage_id'        => (int)$r['door_type_id'],
        'construction_id' => (int)$r['construction_type_id'],
        'price'           => (float)$r['base_price'],
        'available'       => (int)$r['is_available'] === 1,
    ], $matrix),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Configure Your Door — PORTES</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Inter:wght@300;400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/door-showroom/assets/css/home.css?v=<?= @filemtime(APP_ROOT . '/public/assets/css/home.css') ?>" />
  <link rel="stylesheet" href="/door-showroom/assets/css/configurator.css?v=<?= @filemtime(APP_ROOT . '/public/assets/css/configurator.css') ?>" />
</head>
<body class="cfg-body">

<header class="cfg-top">
  <a href="/door-showroom" class="cfg-logo">
    <span class="cfg-logo-mark">PORTES</span>
    <span class="cfg-logo-sub">Configurator</span>
  </a>
  <div class="cfg-progress" id="cfgProgress">
    <?php foreach ($steps as $i => $s): ?>
      <button class="cfg-progress-step<?= $i === 0 ? ' is-active' : '' ?>" data-step="<?= $i ?>" type="button">
        <span class="cfg-progress-num"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
        <span class="cfg-progress-name"><?= $e($s) ?></span>
      </button>
      <?php if ($i < count($steps) - 1): ?><span class="cfg-progress-line"></span><?php endif; ?>
    <?php endforeach; ?>
  </div>
  <a href="/door-showroom" class="cfg-close" aria-label="Exit configurator">&times;</a>
</header>

<main class="cfg-main">

  <!-- STAGE -->
  <section class="cfg-stage">
    <div class="cfg-stage-inner">
      <div class="cfg-render is-empty" id="cfgRender">
        <span class="cfg-render-door" id="cfgRenderDoor"></span>
        <span class="cfg-render-tint" id="cfgRenderTint"></span>
        <span class="cfg-render-empty" id="cfgRenderEmpty">
          <svg viewBox="0 0 48 64" fill="none" stroke="currentColor" stroke-width="1"><rect x="6" y="2" width="36" height="60" rx="1"/><circle cx="34" cy="32" r="1.5" fill="currentColor"/></svg>
          <em>Choose a collection to begin</em>
        </span>
      </div>
      <div class="cfg-stage-meta">
        <span class="cfg-stage-dim" id="cfgStageDim">90 × 210 cm</span>
      </div>
    </div>
  </section>

  <!-- PANEL -->
  <section class="cfg-panel">
    <div class="cfg-panel-scroll">

      <!-- STEP 0 · COLLECTION -->
      <div class="cfg-step is-active" data-step="0">
        <p class="cfg-step-eyebrow">Step 01 — Collection</p>
        <h2 class="cfg-step-title">Choose a <em>Collection</em></h2>
        <p class="cfg-step-lead">Each collection embodies a distinct design philosophy.</p>
        <div class="cfg-list" id="cfgCollections"><!-- built by JS --></div>
      </div>

      <!-- STEP 1 · COLOR -->
      <div class="cfg-step" data-step="1">
        <p class="cfg-step-eyebrow">Step 02 — Colour</p>
        <h2 class="cfg-step-title">Choose your <em>Colour</em></h2>
        <p class="cfg-step-lead">Only colours from your collection are shown.</p>
        <div class="cfg-swatches cfg-swatches--lg" id="cfgColors"><!-- built by JS --></div>
        <p class="cfg-empty-hint" id="cfgColorsEmpty" hidden>Pick a collection first.</p>
      </div>

      <!-- STEP 2 · USAGE -->
      <div class="cfg-step" data-step="2">
        <p class="cfg-step-eyebrow">Step 03 — Usage</p>
        <h2 class="cfg-step-title">Where will it <em>be used?</em></h2>
        <p class="cfg-step-lead">Unavailable combinations show “Non disponible”.</p>
        <div class="cfg-list" id="cfgUsages">
          <?php foreach ($usages as $u): ?>
            <button class="cfg-opt" type="button" data-id="<?= $e($u['id']) ?>" data-name="<?= $e($u['name']) ?>">
              <span class="cfg-opt-name"><?= $e($u['name']) ?></span>
              <span class="cfg-opt-na">Non disponible</span>
              <span class="cfg-opt-check" aria-hidden="true"><svg viewBox="0 0 20 20" fill="none"><polyline points="4,10 8,14 16,6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
            </button>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- STEP 3 · CONSTRUCTION -->
      <div class="cfg-step" data-step="3">
        <p class="cfg-step-eyebrow">Step 04 — Construction</p>
        <h2 class="cfg-step-title">Select a <em>Construction</em></h2>
        <p class="cfg-step-lead">Unavailable options are disabled.</p>
        <div class="cfg-list" id="cfgConstructions">
          <?php foreach ($constructions as $c): ?>
            <button class="cfg-opt" type="button" data-id="<?= $e($c['id']) ?>" data-name="<?= $e($c['name']) ?>">
              <span class="cfg-opt-name"><?= $e($c['name']) ?></span>
              <span class="cfg-opt-na">Non disponible</span>
              <span class="cfg-opt-check" aria-hidden="true"><svg viewBox="0 0 20 20" fill="none"><polyline points="4,10 8,14 16,6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
            </button>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- STEP 4 · DOOR DESIGN (matching products) -->
      <div class="cfg-step" data-step="4">
        <p class="cfg-step-eyebrow">Step 05 — Door Design</p>
        <h2 class="cfg-step-title">Choose your <em>Door</em></h2>
        <p class="cfg-step-lead">Designs matching your collection, colour, usage and construction.</p>
        <div class="cfg-cards" id="cfgProducts"><!-- built by JS --></div>
        <p class="cfg-empty-hint" id="cfgProductsEmpty" hidden>No matching design yet.</p>
      </div>

      <!-- STEP 5 · DIMENSIONS -->
      <div class="cfg-step" data-step="5">
        <p class="cfg-step-eyebrow">Step 06 — Dimensions</p>
        <h2 class="cfg-step-title">Set your <em>Dimensions</em></h2>
        <p class="cfg-step-lead">Made to your exact opening. Price updates live.</p>
        <div class="cfg-dim">
          <div class="cfg-dim-head"><label for="cfgWidth">Width</label><span class="cfg-dim-val" id="cfgWidthVal">90 cm</span></div>
          <input type="range" id="cfgWidth" min="500" max="2000" step="10" value="900" />
          <div class="cfg-dim-scale"><span>50</span><span>200 cm</span></div>
        </div>
        <div class="cfg-dim">
          <div class="cfg-dim-head"><label for="cfgHeight">Height</label><span class="cfg-dim-val" id="cfgHeightVal">210 cm</span></div>
          <input type="range" id="cfgHeight" min="1500" max="3000" step="10" value="2100" />
          <div class="cfg-dim-scale"><span>150</span><span>300 cm</span></div>
        </div>
      </div>

      <!-- STEP 6 · REVIEW & QUOTE -->
      <div class="cfg-step" data-step="6">
        <p class="cfg-step-eyebrow">Step 07 — Review &amp; Quote</p>
        <h2 class="cfg-step-title">Review your <em>Door</em></h2>
        <p class="cfg-step-lead">Confirm your configuration, then request a personal quote.</p>
        <dl class="cfg-review" id="cfgReview"></dl>
        <div class="cfg-review-price"><span>Final Price</span><strong id="cfgReviewPrice">—</strong></div>
        <form class="cfg-quote-form" id="cfgQuoteForm" novalidate>
          <input type="text" name="company_website" id="cfgHoneypot" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true" />
          <div class="cfg-field"><label for="qName">Full name *</label><input type="text" id="qName" name="full_name" maxlength="120" required /></div>
          <div class="cfg-field"><label for="qEmail">Email *</label><input type="email" id="qEmail" name="email" maxlength="180" required /></div>
          <div class="cfg-field"><label for="qPhone">Phone *</label><input type="tel" id="qPhone" name="phone" maxlength="30" required /></div>
          <div class="cfg-field-row">
            <div class="cfg-field"><label for="qCountry">Country *</label><input type="text" id="qCountry" name="country" maxlength="100" required /></div>
            <div class="cfg-field"><label for="qCity">City *</label><input type="text" id="qCity" name="city" maxlength="100" required /></div>
          </div>
          <div class="cfg-field"><label for="qNotes">Notes</label><textarea id="qNotes" name="notes" rows="3" maxlength="3000"></textarea></div>
          <p class="cfg-form-error" id="cfgFormError" hidden></p>
          <p class="cfg-form-success" id="cfgFormSuccess" hidden></p>
        </form>
      </div>

    </div>

    <div class="cfg-nav">
      <button class="btn btn--outline" id="cfgBack" type="button" disabled>Back</button>
      <span class="cfg-nav-step" id="cfgNavStep">Step 1 of 7</span>
      <button class="btn btn--gold" id="cfgNext" type="button">Next
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </button>
    </div>
  </section>

  <!-- STICKY SUMMARY -->
  <aside class="cfg-summary" id="cfgSummary">
    <h3 class="cfg-summary-title">Your Configuration</h3>
    <dl class="cfg-summary-list">
      <div><dt>Collection</dt><dd id="sumCollection">—</dd></div>
      <div><dt>Colour</dt><dd id="sumColor">—</dd></div>
      <div><dt>Usage</dt><dd id="sumUsage">—</dd></div>
      <div><dt>Construction</dt><dd id="sumConstruction">—</dd></div>
      <div><dt>Design</dt><dd id="sumProduct">—</dd></div>
      <div><dt>Dimensions</dt><dd id="sumDim">90 × 210 cm</dd></div>
    </dl>
    <div class="cfg-summary-price">
      <span class="cfg-summary-price-label">Estimated Price</span>
      <strong class="cfg-summary-price-value" id="sumPrice">Configure to see price</strong>
    </div>
    <div class="cfg-summary-actions">
      <button class="btn btn--gold btn--block" id="cfgQuote" type="button">Request Quote</button>
      <button class="btn btn--outline btn--block" id="cfgSave" type="button">Save Configuration</button>
    </div>
  </aside>

</main>

<button class="cfg-summary-fab" id="cfgSummaryFab" type="button" aria-label="View summary">
  <span class="cfg-summary-fab-price" id="cfgFabPrice">—</span>
  <span>Summary</span>
</button>

<script type="application/json" id="cfgData"><?= json_encode($cfgData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
<script src="/door-showroom/assets/js/configurator2.js?v=<?= @filemtime(APP_ROOT . '/public/assets/js/configurator2.js') ?>"></script>
</body>
</html>
