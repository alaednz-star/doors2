<?php
declare(strict_types=1);
/** @var array $colorsData, $collectionsData, $usages, $constructions, $matrix */
/** @var int|null $preColorId, $preCollectionId */
/** @var string $token */
$e = static fn ($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

use App\Core\I18n;
$L   = I18n::lang();
$DIR = I18n::dir();

$steps = I18n::group('cfg.steps');

// i18n strings the JS needs at runtime (labels it renders dynamically).
$jsI18n = [
    'na'            => t('cfg.na'),
    'next'          => t('cfg.next'),
    'submit_quote'  => t('cfg.submit_quote'),
    'back'          => t('cfg.back'),
    'step_x_y'      => t('cfg.step_x_y'),
    'price_hint'    => t('cfg.price_hint'),
    'saved'         => t('cfg.saved'),
    'save'          => t('cfg.save'),
    'save_cfg'      => t('cfg.save_cfg'),
    'preview_empty' => t('cfg.preview_empty'),
    'collection'    => t('cfg.collection'),
    'colour'        => t('cfg.colour'),
    'usage'         => t('cfg.usage'),
    'construction'  => t('cfg.construction'),
    'dimensions'    => t('cfg.dimensions'),
    'quantity'      => t('cfg.quantity'),
    'err_review'    => t('cfg.err_review'),
    'err_required'  => t('cfg.err_required'),
    'err_no_door'   => t('cfg.err_no_door'),
    'err_submit'    => t('cfg.err_submit'),
    'err_network'   => t('cfg.err_network'),
    'coll_desc'     => I18n::group('cfg.coll_desc'),
];

$cfgData = [
    'csrf'      => $token,
    'priceUrl'  => '/door-showroom/configure/price',
    'quoteUrl'  => '/door-showroom/configure/quote',
    'saveUrl'   => '/door-showroom/configure/save',
    'lang'      => $L,
    'dir'       => $DIR,
    'i18n'      => $jsI18n,
    'preColorId'      => $preColorId,
    'preCollectionId' => $preCollectionId,
    'colors'        => $colorsData,
    'collections'   => $collectionsData,
    'usages'        => array_map(static fn ($u) => ['id' => (int)$u['id'], 'name' => $u['name']], $usages),
    'constructions' => array_map(static fn ($c) => ['id' => (int)$c['id'], 'name' => $c['name']], $constructions),
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
<html lang="<?= $e($L) ?>" dir="<?= $e($DIR) ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $e(t('cfg.title')) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Inter:wght@300;400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/door-showroom/assets/css/home.css?v=<?= @filemtime(APP_ROOT . '/public/assets/css/home.css') ?>" />
  <link rel="stylesheet" href="/door-showroom/assets/css/configurator.css?v=<?= @filemtime(APP_ROOT . '/public/assets/css/configurator.css') ?>" />
  <link rel="stylesheet" href="/door-showroom/assets/css/i18n.css?v=<?= @filemtime(APP_ROOT . '/public/assets/css/i18n.css') ?>" />
</head>
<body class="cfg-body">

<header class="cfg-top">
  <a href="/door-showroom" class="cfg-logo">
    <img src="/door-showroom/assets/images/logo-adk.png" alt="ADK — Algerian Doors &amp; Kitchens" class="cfg-logo-img" />
    <span class="cfg-logo-sub"><?= $e(t('cfg.logo_sub')) ?></span>
  </a>
  <nav class="cfg-progress" id="cfgProgress" aria-label="<?= $e(t('cfg.steps_aria')) ?>">
    <?php foreach ($steps as $i => $s): ?>
      <button class="cfg-progress-step<?= $i === 0 ? ' is-active' : '' ?>" data-step="<?= $i ?>" type="button" aria-label="<?= $e($s) ?>">
        <span class="cfg-progress-num"><?= $i + 1 ?></span>
        <span class="cfg-progress-name"><?= $e($s) ?></span>
      </button>
      <?php if ($i < count($steps) - 1): ?><span class="cfg-progress-line"></span><?php endif; ?>
    <?php endforeach; ?>
  </nav>
  <?php $variant = ''; include APP_ROOT . '/src/Views/partials/lang-switch.php'; ?>
  <a href="/door-showroom" class="cfg-close" aria-label="<?= $e(t('cfg.close')) ?>">&times;</a>
</header>

<main class="cfg-main">

  <!-- cfgStageDim — used by JS setSummary(), never visible -->
  <span id="cfgStageDim" hidden>90 × 210 cm</span>

  <section class="cfg-panel">
    <div class="cfg-panel-scroll">
      <div class="cfg-page">

        <!-- ════════════════════════════════════════
             LEFT — step content
             ════════════════════════════════════════ -->
        <div class="cfg-content">

          <!-- ═══ STEP 0 · COLLECTION ═══ -->
          <div class="cfg-step is-active" data-step="0">
            <p class="cfg-step-eyebrow"><?= $e(t('cfg.s0_eyebrow')) ?></p>
            <h2 class="cfg-step-title"><?= t('cfg.s0_title') ?></h2>
            <p class="cfg-step-lead"><?= $e(t('cfg.s0_lead')) ?></p>
            <div class="cfg-list" id="cfgCollections"><!-- built by JS --></div>
          </div>

          <!-- ═══ STEP 1 · COULEUR ═══ -->
          <div class="cfg-step" data-step="1">
            <p class="cfg-step-eyebrow"><?= $e(t('cfg.s1_eyebrow')) ?></p>
            <h2 class="cfg-step-title"><?= t('cfg.s1_title') ?></h2>
            <p class="cfg-step-lead"><?= $e(t('cfg.s1_lead')) ?></p>
            <div class="cfg-swatch-grid" id="cfgColors"><!-- built by JS --></div>
            <p class="cfg-empty-hint" id="cfgColorsEmpty" hidden><?= $e(t('cfg.s1_empty')) ?></p>
          </div>

          <!-- ═══ STEP 2 · USAGE ═══ -->
          <div class="cfg-step" data-step="2">
            <p class="cfg-step-eyebrow"><?= $e(t('cfg.s2_eyebrow')) ?></p>
            <h2 class="cfg-step-title"><?= t('cfg.s2_title') ?></h2>
            <p class="cfg-step-lead"><?= $e(t('cfg.s2_lead')) ?></p>
            <div class="cfg-list" id="cfgUsages">
              <?php
              $usageVisuals = [
                  'Chambre'          => 'chambre',
                  'Sanitaire'        => 'sanitaire',
                  'Salon'            => 'salon',
                  "Porte d'Entrée"   => 'entree',
              ];
              foreach ($usages as $u):
                  $slug = $usageVisuals[$u['name']] ?? 'chambre';
              ?>
              <button class="cfg-opt" type="button" data-id="<?= $e($u['id']) ?>" data-name="<?= $e($u['name']) ?>">
                <div class="cfg-usage-visual cfg-usage-visual--<?= $e($slug) ?><?= !empty($u['img']) ? ' has-img' : '' ?>"<?= !empty($u['img']) ? ' style="background-image:url(\'' . $e($u['img']) . '\')"' : '' ?>>
                  <div class="cfg-usage-overlay"></div>
                  <span class="cfg-usage-label"><?= $e($u['name']) ?></span>
                </div>
                <span class="cfg-opt-na"><?= $e(t('cfg.na')) ?></span>
                <span class="cfg-opt-check" aria-hidden="true">
                  <svg viewBox="0 0 20 20" fill="none"><polyline points="4,10 8,14 16,6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
              </button>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- ═══ STEP 3 · CONSTRUCTION ═══ -->
          <div class="cfg-step" data-step="3">
            <p class="cfg-step-eyebrow"><?= $e(t('cfg.s3_eyebrow')) ?></p>
            <h2 class="cfg-step-title"><?= t('cfg.s3_title') ?></h2>
            <p class="cfg-step-lead"><?= $e(t('cfg.s3_lead')) ?></p>
            <div class="cfg-list" id="cfgConstructions">
              <?php
              $constFeatures = I18n::group('cfg.const_features');
              foreach ($constructions as $c):
                  $feats = $constFeatures[$c['name']] ?? ($constFeatures['default'] ?? []);
              ?>
              <button class="cfg-opt" type="button" data-id="<?= $e($c['id']) ?>" data-name="<?= $e($c['name']) ?>">
                <div class="cfg-const-visual">
                  <?php if (!empty($c['img'])): ?>
                    <img class="cfg-const-img" src="<?= $e($c['img']) ?>" alt="<?= $e($c['name']) ?>" loading="lazy" />
                  <?php else: ?>
                    <div class="cfg-const-ph"></div>
                  <?php endif; ?>
                </div>
                <div class="cfg-const-body">
                  <span class="cfg-const-name"><?= $e($c['name']) ?></span>
                  <ul class="cfg-const-features">
                    <?php foreach ($feats as $f): ?>
                      <li><?= $e($f) ?></li>
                    <?php endforeach; ?>
                  </ul>
                  <span class="cfg-opt-na"><?= $e(t('cfg.na')) ?></span>
                </div>
                <span class="cfg-opt-check" aria-hidden="true">
                  <svg viewBox="0 0 20 20" fill="none"><polyline points="4,10 8,14 16,6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
              </button>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- ═══ STEP 4 · DIMENSIONS ═══ -->
          <div class="cfg-step" data-step="4">
            <p class="cfg-step-eyebrow"><?= $e(t('cfg.s4_eyebrow')) ?></p>
            <h2 class="cfg-step-title"><?= t('cfg.s4_title') ?></h2>
            <p class="cfg-step-lead"><?= $e(t('cfg.s4_lead')) ?></p>

            <!-- Hidden range inputs (mm) — kept as the JS state bridge -->
            <div class="cfg-dim" hidden>
              <input type="range" id="cfgWidth"  min="500"  max="2000" step="10" value="900" />
              <input type="range" id="cfgHeight" min="1500" max="3000" step="10" value="2100" />
              <input type="number" id="cfgQty" value="1" />
            </div>

            <div class="cfg-dim2">
              <!-- LEFT: architectural door illustration with dimension arrows -->
              <figure class="cfg-dim2-visual">
                <div class="cfg-dim2-frame">
                  <!-- vertical (height) measurement -->
                  <div class="cfg-dim2-vmeasure">
                    <span class="cfg-dim2-tick"></span>
                    <span class="cfg-dim2-vline"></span>
                    <span class="cfg-dim2-tick"></span>
                    <span class="cfg-dim2-vcap" id="cfgDimHViz">210<small>cm</small></span>
                  </div>
                  <!-- the door -->
                  <div class="cfg-dim2-door" id="cfgDimDoor"></div>
                  <!-- horizontal (width) measurement -->
                  <div class="cfg-dim2-hmeasure">
                    <span class="cfg-dim2-tick"></span>
                    <span class="cfg-dim2-hline"></span>
                    <span class="cfg-dim2-tick"></span>
                    <span class="cfg-dim2-hcap" id="cfgDimWViz">90<small>cm</small></span>
                  </div>
                </div>
              </figure>

              <!-- RIGHT: numeric stepper controls + selection summary -->
              <div class="cfg-dim2-panel">
                <div class="cfg-dim2-controls">
                  <div class="cfg-dim2-row">
                    <label for="cfgWidthCm"><?= $e(t('cfg.width')) ?> <span><?= $e(t('cfg.unit_cm')) ?></span></label>
                    <div class="cfg-stepper">
                      <input type="number" id="cfgWidthCm" inputmode="numeric" min="50" max="200" step="1" value="90" />
                      <button type="button" class="cfg-stepper-btn" id="cfgWidthMinus" aria-label="<?= $e(t('cfg.width_dec')) ?>">−</button>
                      <button type="button" class="cfg-stepper-btn" id="cfgWidthPlus" aria-label="<?= $e(t('cfg.width_inc')) ?>">+</button>
                    </div>
                  </div>
                  <div class="cfg-dim2-row">
                    <label for="cfgHeightCm"><?= $e(t('cfg.height')) ?> <span><?= $e(t('cfg.unit_cm')) ?></span></label>
                    <div class="cfg-stepper">
                      <input type="number" id="cfgHeightCm" inputmode="numeric" min="150" max="300" step="1" value="210" />
                      <button type="button" class="cfg-stepper-btn" id="cfgHeightMinus" aria-label="<?= $e(t('cfg.height_dec')) ?>">−</button>
                      <button type="button" class="cfg-stepper-btn" id="cfgHeightPlus" aria-label="<?= $e(t('cfg.height_inc')) ?>">+</button>
                    </div>
                  </div>
                </div>

                <div class="cfg-dim2-summary">
                  <p class="cfg-dim2-summary-label"><?= $e(t('cfg.dim_sel')) ?></p>
                  <p class="cfg-dim2-summary-value" id="cfgDimSummary">90 × 210 cm</p>
                  <p class="cfg-dim2-summary-sub"><?= $e(t('cfg.dim_std')) ?></p>
                </div>
              </div>
            </div>
          </div>

          <!-- ═══ STEP 5 · RÉSUMÉ ═══ -->
          <div class="cfg-step" data-step="5">
            <p class="cfg-step-eyebrow"><?= $e(t('cfg.s5_eyebrow')) ?></p>
            <h2 class="cfg-step-title"><?= t('cfg.s5_title') ?></h2>
            <p class="cfg-step-lead"><?= $e(t('cfg.s5_lead')) ?></p>
            <div class="cfg-review-layout">
              <!-- Left: door preview -->
              <div class="cfg-review-visual">
                <div class="cfg-review-img" id="cfgReviewImg"></div>
              </div>
              <!-- Right: complete configuration + price + actions -->
              <div class="cfg-review-details">
                <dl class="cfg-review" id="cfgReview"></dl>
                <div class="cfg-review-price">
                  <span><?= $e(t('cfg.est_price')) ?></span>
                  <strong id="cfgReviewPrice">—</strong>
                </div>
                <div class="cfg-review-actions">
                  <button type="button" class="btn btn--gold btn--block" id="cfgToDetails">
                    <?= $e(t('cfg.ask_quote')) ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" style="width:16px;height:16px;vertical-align:middle;margin-left:.4rem;"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                  </button>
                  <button type="button" class="btn btn--outline btn--block" id="cfgReviewSave"><?= $e(t('cfg.save_cfg')) ?></button>
                  <button type="button" class="btn btn--outline btn--block cfg-review-add" id="cfgAddAnother"><?= $e(t('cfg.add_door')) ?></button>
                </div>
              </div>
            </div>
          </div>

          <!-- ═══ STEP 6 · DEVIS ═══ -->
          <div class="cfg-step" data-step="6">
            <div class="cfg-quote2">
              <!-- LEFT: intro + compact configuration recap -->
              <div class="cfg-quote2-intro">
                <p class="cfg-step-eyebrow"><?= $e(t('cfg.s6_eyebrow')) ?></p>
                <h2 class="cfg-step-title"><?= t('cfg.s6_title') ?></h2>
                <p class="cfg-step-lead"><?= $e(t('cfg.s6_lead')) ?></p>

                <div class="cfg-quote2-recap">
                  <span class="cfg-quote2-recap-name" id="cfgQuoteName">—</span>
                  <dl class="cfg-quote2-recap-list" id="cfgQuoteReview"></dl>
                  <div class="cfg-quote2-recap-price">
                    <span><?= $e(t('cfg.est_price')) ?></span>
                    <strong id="cfgQuotePrice">—</strong>
                  </div>
                </div>
              </div>

              <!-- RIGHT: contact form -->
              <form class="cfg-quote-form cfg-quote2-form" id="cfgQuoteForm" novalidate>
                <input type="text" name="company_website" id="cfgHoneypot" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true" />
                <div class="cfg-field"><label for="qName"><?= $e(t('cfg.f_name')) ?></label><input type="text" id="qName" name="full_name" maxlength="120" required placeholder="<?= $e(t('cfg.f_name_ph')) ?>" /></div>
                <div class="cfg-field-row">
                  <div class="cfg-field"><label for="qEmail"><?= $e(t('cfg.f_email')) ?></label><input type="email" id="qEmail" name="email" maxlength="180" required placeholder="<?= $e(t('cfg.f_email_ph')) ?>" /></div>
                  <div class="cfg-field"><label for="qPhone"><?= $e(t('cfg.f_phone')) ?></label><input type="tel" id="qPhone" name="phone" maxlength="30" required placeholder="<?= $e(t('cfg.f_phone_ph')) ?>" /></div>
                </div>
                <div class="cfg-field-row">
                  <div class="cfg-field"><label for="qCity"><?= $e(t('cfg.f_city')) ?></label><input type="text" id="qCity" name="city" maxlength="100" required placeholder="<?= $e(t('cfg.f_city_ph')) ?>" /></div>
                  <div class="cfg-field"><label for="qCountry"><?= $e(t('cfg.f_country')) ?></label><input type="text" id="qCountry" name="country" maxlength="100" required placeholder="<?= $e(t('cfg.f_country_ph')) ?>" /></div>
                </div>
                <div class="cfg-field"><label for="qNotes"><?= $e(t('cfg.f_notes')) ?></label><textarea id="qNotes" name="notes" rows="2" maxlength="3000" placeholder="<?= $e(t('cfg.f_notes_ph')) ?>"></textarea></div>
                <p class="cfg-form-error" id="cfgFormError" hidden></p>
              </form>
            </div>
          </div>

          <!-- Navigation -->
          <div class="cfg-nav">
            <button class="btn btn--outline" id="cfgBack" type="button" disabled><?= $e(t('cfg.back')) ?></button>
            <span class="cfg-nav-step" id="cfgNavStep"><?= $e(t('cfg.step_x_y', ['n' => 1, 'total' => count($steps)])) ?></span>
            <button class="btn btn--gold" id="cfgNext" type="button">
              <?= $e(t('cfg.next')) ?>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" style="width:16px;height:16px;vertical-align:middle;margin-left:.4rem;"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </button>
          </div>

        </div><!-- /cfg-content -->

        <!-- ════════════════════════════════════════
             RIGHT — sticky preview + summary sidebar
             id="cfgSummary" used by JS mobile toggle
             ════════════════════════════════════════ -->
        <aside class="cfg-sidebar" id="cfgSummary">

          <!-- Door render -->
          <div class="cfg-sidebar-render">
            <div class="cfg-render is-empty" id="cfgRender">
              <span class="cfg-render-door" id="cfgRenderDoor"></span>
              <span class="cfg-render-tint" id="cfgRenderTint"></span>
              <span class="cfg-render-empty" id="cfgRenderEmpty">
                <svg viewBox="0 0 48 64" fill="none" stroke="currentColor" stroke-width="1"><rect x="6" y="2" width="36" height="60" rx="1"/><circle cx="34" cy="32" r="1.5" fill="currentColor"/></svg>
                <em><?= $e(t('cfg.preview_empty')) ?></em>
              </span>
              <span class="cfg-render-chip" id="cfgRenderChip"><span class="cfg-render-chip-dot" id="cfgRenderChipDot"></span><span id="cfgRenderChipName"></span></span>
            </div>
          </div>

          <!-- Summary body -->
          <div class="cfg-sidebar-body">
            <h3 class="cfg-summary-title"><?= $e(t('cfg.quick')) ?></h3>

            <dl class="cfg-summary-list" id="cfgCurrentSummary">
              <div><dt><?= $e(t('cfg.collection')) ?></dt><dd id="sumCollection">—</dd></div>
              <div><dt><?= $e(t('cfg.colour')) ?></dt><dd id="sumColor">—</dd></div>
              <div><dt><?= $e(t('cfg.usage')) ?></dt><dd id="sumUsage">—</dd></div>
              <div><dt><?= $e(t('cfg.construction')) ?></dt><dd id="sumConstruction">—</dd></div>
              <div><dt><?= $e(t('cfg.dimensions')) ?></dt><dd id="sumDim">90 × 210 cm</dd></div>
            </dl>

            <div class="cfg-summary-price">
              <span class="cfg-summary-price-label"><?= $e(t('cfg.est_price')) ?></span>
              <strong class="cfg-summary-price-value" id="sumPrice">—</strong>
            </div>

            <div class="cfg-cart" id="cfgCart" hidden>
              <h4 class="cfg-cart-title"><?= $e(t('cfg.cart_title')) ?></h4>
              <ul class="cfg-cart-list" id="cfgCartList"></ul>
              <div class="cfg-summary-price">
                <span class="cfg-summary-price-label"><?= $e(t('cfg.total')) ?></span>
                <strong class="cfg-summary-price-value" id="cfgCartTotal">—</strong>
              </div>
            </div>

            <div class="cfg-summary-actions">
              <button class="btn btn--gold btn--block" id="cfgQuote" type="button"><?= $e(t('cfg.ask_quote')) ?></button>
              <button class="btn btn--outline btn--block" id="cfgSave" type="button"><?= $e(t('cfg.save')) ?></button>
            </div>
          </div>

        </aside><!-- /cfg-sidebar -->

      </div><!-- /cfg-page -->
    </div><!-- /cfg-panel-scroll -->
  </section><!-- /cfg-panel -->

</main>

<!-- CONFIRMATION (printable) -->
<div class="cfg-confirm" id="cfgConfirm" hidden>
  <div class="cfg-confirm-card" id="cfgConfirmCard">
    <div class="cfg-confirm-head">
      <img src="/door-showroom/assets/images/logo-adk.png" alt="ADK — Algerian Doors &amp; Kitchens" class="cfg-confirm-logo" />
      <span class="cfg-confirm-sub"><?= $e(t('cfg.confirm_sub')) ?></span>
    </div>
    <div class="cfg-confirm-check" aria-hidden="true">
      <svg viewBox="0 0 48 48" fill="none"><circle cx="24" cy="24" r="22" stroke="currentColor" stroke-width="1.5"/><polyline points="15,25 21,31 33,18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </div>
    <h2 class="cfg-confirm-title"><?= $e(t('cfg.confirm_thanks')) ?></h2>
    <p class="cfg-confirm-ref"><?= $e(t('cfg.confirm_ref')) ?> <strong id="cfgConfirmRef">—</strong></p>
    <div class="cfg-confirm-customer" id="cfgConfirmCustomer"></div>
    <table class="cfg-confirm-table" id="cfgConfirmItems"></table>
    <div class="cfg-confirm-total"><span><?= $e(t('cfg.total')) ?></span><strong id="cfgConfirmTotal">—</strong></div>
    <p class="cfg-confirm-note"><?= $e(t('cfg.confirm_note')) ?></p>
    <div class="cfg-confirm-actions">
      <button type="button" class="btn btn--gold" id="cfgPrint"><?= $e(t('cfg.print')) ?></button>
      <button type="button" class="btn btn--outline" id="cfgAnother"><?= $e(t('cfg.another')) ?></button>
      <a href="/door-showroom" class="btn btn--outline"><?= $e(t('cfg.home')) ?></a>
    </div>
  </div>
</div>

<script type="application/json" id="cfgData"><?= json_encode($cfgData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
<script src="/door-showroom/assets/js/configurator2.js?v=<?= @filemtime(APP_ROOT . '/public/assets/js/configurator2.js') ?>"></script>
</body>
</html>
