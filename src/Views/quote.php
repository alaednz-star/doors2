<?php
declare(strict_types=1);
/** @var array $collections, $materials, $colors, $doorTypes, $features */
/** @var string $token */
$e   = static fn ($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$ci  = contact_info();
$wa  = $ci['whatsapp_url'];

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
$L   = \App\Core\I18n::lang();
$DIR = \App\Core\I18n::dir();
?>
<!DOCTYPE html>
<html lang="<?= $e($L) ?>" dir="<?= $e($DIR) ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="<?= $e(t('quote_page.meta')) ?>" />
  <title><?= $e(t('quote_page.title')) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Inter:wght@300;400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/door-showroom/assets/css/home.css?v=<?= $ver('/assets/css/home.css') ?>" />
  <link rel="stylesheet" href="/door-showroom/assets/css/quote.css?v=<?= $ver('/assets/css/quote.css') ?>" />
  <link rel="stylesheet" href="/door-showroom/assets/css/i18n.css?v=<?= $ver('/assets/css/i18n.css') ?>" />
</head>
<body class="q-body">

<!-- ░ TOP BAR ░ -->
<header class="q-top">
  <a href="/door-showroom" class="q-logo">
    <span class="q-logo-mark">PORTES</span>
    <span class="q-logo-sub"><?= $e(t('quote_page.logo_sub')) ?></span>
  </a>
  <div style="display:flex;align-items:center;gap:1.25rem">
    <?php $variant = ''; include APP_ROOT . '/src/Views/partials/lang-switch.php'; ?>
    <a href="/door-showroom/configure" class="q-back-link"><?= $e(t('quote_page.back')) ?></a>
  </div>
</header>

<!-- ░ PROGRESS ░ -->
<div class="q-steps" id="qSteps">
  <button class="q-step is-active" data-step="0" type="button"><span>01</span> <?= $e(t('quote_page.step_review')) ?></button>
  <span class="q-step-line"></span>
  <button class="q-step" data-step="1" type="button"><span>02</span> <?= $e(t('quote_page.step_details')) ?></button>
  <span class="q-step-line"></span>
  <button class="q-step" data-step="2" type="button"><span>03</span> <?= $e(t('quote_page.step_confirm')) ?></button>
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
        <p class="eyebrow"><?= $e(t('quote_page.r_eyebrow')) ?></p>
        <h1 class="q-h1"><?= $e(t('quote_page.r_h_1')) ?><br /><em><?= $e(t('quote_page.r_h_2')) ?></em></h1>
        <p class="q-lead"><?= $e(t('quote_page.r_lead')) ?></p>

        <dl class="q-spec" id="qSpec">
          <div><dt><?= $e(t('quote_page.room')) ?></dt><dd data-spec="room">—</dd></div>
          <div><dt><?= $e(t('quote_page.collection')) ?></dt><dd data-spec="collection">—</dd></div>
          <div><dt><?= $e(t('quote_page.colour')) ?></dt><dd data-spec="color">—</dd></div>
          <div><dt><?= $e(t('quote_page.usage')) ?></dt><dd data-spec="doorType">—</dd></div>
          <div><dt><?= $e(t('quote_page.dimensions')) ?></dt><dd data-spec="dim">—</dd></div>
          <div class="q-spec-features" data-features-row hidden><dt><?= $e(t('quote_page.options')) ?></dt><dd data-spec="features">—</dd></div>
        </dl>

        <div class="q-price">
          <span class="q-price-label"><?= $e(t('quote_page.est_price')) ?></span>
          <strong class="q-price-value" id="qPrice">—</strong>
          <span class="q-price-note"><?= $e(t('quote_page.price_note')) ?></span>
        </div>

        <div class="q-review-actions">
          <button class="btn btn--gold btn--lg" id="qToDetails" type="button"><?= $e(t('quote_page.continue')) ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </button>
          <a href="/door-showroom/configure" class="btn btn--outline btn--lg"><?= $e(t('quote_page.edit')) ?></a>
        </div>
      </div>
    </div>
  </section>

  <!-- ══ STEP 2 — PROJECT INFORMATION ══ -->
  <section class="q-panel" data-panel="1">
    <div class="q-form-wrap">
      <p class="eyebrow"><?= $e(t('quote_page.d_eyebrow')) ?></p>
      <h2 class="q-h2"><?= $e(t('quote_page.d_h_1')) ?><br /><em><?= $e(t('quote_page.d_h_2')) ?></em></h2>
      <p class="q-lead"><?= $e(t('quote_page.d_lead')) ?></p>

      <form class="q-form" id="qForm" novalidate>
        <div class="q-field-grid">
          <div class="q-field">
            <label for="qName"><?= $e(t('quote_page.f_name')) ?></label>
            <input type="text" id="qName" name="full_name" autocomplete="name" maxlength="120" required />
            <span class="q-err" data-err="full_name"></span>
          </div>
          <div class="q-field">
            <label for="qEmail"><?= $e(t('quote_page.f_email')) ?></label>
            <input type="email" id="qEmail" name="email" autocomplete="email" maxlength="180" required />
            <span class="q-err" data-err="email"></span>
          </div>
          <div class="q-field">
            <label for="qPhone"><?= $e(t('quote_page.f_phone')) ?></label>
            <input type="tel" id="qPhone" name="phone" autocomplete="tel" maxlength="30" required />
            <span class="q-err" data-err="phone"></span>
          </div>
          <div class="q-field">
            <label for="qCompany"><?= $e(t('quote_page.f_company')) ?> <span class="q-opt"><?= $e(t('common.optional')) ?></span></label>
            <input type="text" id="qCompany" name="company" autocomplete="organization" maxlength="160" />
            <span class="q-err" data-err="company"></span>
          </div>
          <div class="q-field">
            <label for="qCountry"><?= $e(t('quote_page.f_country')) ?></label>
            <input type="text" id="qCountry" name="country" autocomplete="country-name" maxlength="100" value="Algeria" required />
            <span class="q-err" data-err="country"></span>
          </div>
          <div class="q-field">
            <label for="qCity"><?= $e(t('quote_page.f_city')) ?></label>
            <input type="text" id="qCity" name="city" autocomplete="address-level2" maxlength="100" required />
            <span class="q-err" data-err="city"></span>
          </div>
        </div>

        <div class="q-field-grid">
          <div class="q-field">
            <label for="qInstallDate"><?= $e(t('quote_page.f_date')) ?> <span class="q-opt"><?= $e(t('common.optional')) ?></span></label>
            <input type="date" id="qInstallDate" name="install_date" />
            <span class="q-err" data-err="install_date"></span>
          </div>
          <div class="q-field">
            <label for="qQuantity"><?= $e(t('quote_page.f_qty')) ?></label>
            <input type="number" id="qQuantity" name="quantity" min="1" max="9999" value="1" />
            <span class="q-err" data-err="quantity"></span>
          </div>
        </div>

        <div class="q-field">
          <label for="qNotes"><?= $e(t('quote_page.f_notes')) ?> <span class="q-opt"><?= $e(t('common.optional')) ?></span></label>
          <textarea id="qNotes" name="notes" rows="4" maxlength="3000" placeholder="<?= $e(t('quote_page.f_notes_ph')) ?>"></textarea>
          <span class="q-err" data-err="notes"></span>
        </div>

        <!-- honeypot: hidden from humans, catches bots -->
        <div class="q-hp" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden">
          <label>Company website<input type="text" name="company_website" tabindex="-1" autocomplete="off" /></label>
        </div>

        <div class="q-form-actions">
          <button type="button" class="btn btn--outline btn--lg" id="qBackToReview"><?= $e(t('quote_page.back_btn')) ?></button>
          <button type="submit" class="btn btn--gold btn--lg" id="qSubmit"><?= $e(t('quote_page.submit')) ?>
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
      <p class="eyebrow"><?= $e(t('quote_page.s_eyebrow')) ?></p>
      <h2 class="q-success-title"><?= $e(t('quote_page.s_title_1')) ?><br /><em><?= $e(t('quote_page.s_title_2')) ?></em></h2>
      <div class="q-success-ref">
        <span><?= $e(t('quote_page.s_ref')) ?></span>
        <strong id="qRefOut">—</strong>
      </div>
      <p class="q-success-text"><?= t('quote_page.s_text') ?></p>
      <div class="q-success-actions">
        <button class="btn btn--outline-light btn--lg" id="qDownloadPdf" type="button"><?= $e(t('quote_page.s_download')) ?></button>
        <a href="/door-showroom/configure" class="btn btn--gold btn--lg"><?= $e(t('quote_page.s_another')) ?></a>
        <a href="/door-showroom" class="btn btn--outline btn--lg"><?= $e(t('quote_page.s_home')) ?></a>
      </div>
    </div>
  </section>

</main>

<!-- ░ empty-config state (no configuration found) ░ -->
<section class="q-empty" id="qEmpty" hidden>
  <p class="eyebrow"><?= $e(t('quote_page.empty_eyebrow')) ?></p>
  <h2 class="q-h2"><?= $e(t('quote_page.empty_h')) ?></h2>
  <p class="q-lead"><?= $e(t('quote_page.empty_lead')) ?></p>
  <a href="/door-showroom/configure" class="btn btn--gold btn--lg"><?= $e(t('quote_page.empty_btn')) ?>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
  </a>
</section>

<script type="application/json" id="qData"><?= json_encode($qData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
<script src="/door-showroom/assets/js/quote.js?v=<?= $ver('/assets/js/quote.js') ?>"></script>
</body>
</html>
