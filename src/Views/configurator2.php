<?php
declare(strict_types=1);
/** @var array $colorsData, $collectionsData, $usages, $constructions, $matrix */
/** @var int|null $preColorId, $preCollectionId */
/** @var string $token */
$e = static fn ($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

$steps = ['Collection', 'Couleur', 'Usage', 'Construction', 'Dimensions', 'Résumé', 'Devis'];

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
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Configurez votre porte — PORTES</title>
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
    <span class="cfg-logo-sub">Configurateur</span>
  </a>
  <nav class="cfg-progress" id="cfgProgress" aria-label="Étapes">
    <?php foreach ($steps as $i => $s): ?>
      <button class="cfg-progress-step<?= $i === 0 ? ' is-active' : '' ?>" data-step="<?= $i ?>" type="button" aria-label="<?= $e($s) ?>">
        <span class="cfg-progress-num"><?= $i + 1 ?></span>
        <span class="cfg-progress-name"><?= $e($s) ?></span>
      </button>
      <?php if ($i < count($steps) - 1): ?><span class="cfg-progress-line"></span><?php endif; ?>
    <?php endforeach; ?>
  </nav>
  <a href="/door-showroom" class="cfg-close" aria-label="Quitter le configurateur">&times;</a>
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
            <p class="cfg-step-eyebrow">1 — Collection</p>
            <h2 class="cfg-step-title">Choisissez votre <em>Collection</em></h2>
            <p class="cfg-step-lead">Chaque collection incarne une philosophie de design distincte. Sélectionnez celle qui vous correspond.</p>
            <div class="cfg-list" id="cfgCollections"><!-- built by JS --></div>
          </div>

          <!-- ═══ STEP 1 · COULEUR ═══ -->
          <div class="cfg-step" data-step="1">
            <p class="cfg-step-eyebrow">2 — Couleur</p>
            <h2 class="cfg-step-title">Choisissez la <em>Couleur</em></h2>
            <p class="cfg-step-lead">Seules les couleurs disponibles dans votre collection sont affichées.</p>
            <div class="cfg-swatch-grid" id="cfgColors"><!-- built by JS --></div>
            <p class="cfg-empty-hint" id="cfgColorsEmpty" hidden>Sélectionnez d'abord une collection.</p>
          </div>

          <!-- ═══ STEP 2 · USAGE ═══ -->
          <div class="cfg-step" data-step="2">
            <p class="cfg-step-eyebrow">3 — Usage</p>
            <h2 class="cfg-step-title">Où sera-t-elle <em>installée ?</em></h2>
            <p class="cfg-step-lead">Les combinaisons non disponibles sont affichées mais désactivées.</p>
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
                <div class="cfg-usage-visual cfg-usage-visual--<?= $e($slug) ?>">
                  <div class="cfg-usage-overlay"></div>
                  <span class="cfg-usage-label"><?= $e($u['name']) ?></span>
                </div>
                <span class="cfg-opt-na">Non disponible</span>
                <span class="cfg-opt-check" aria-hidden="true">
                  <svg viewBox="0 0 20 20" fill="none"><polyline points="4,10 8,14 16,6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
              </button>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- ═══ STEP 3 · CONSTRUCTION ═══ -->
          <div class="cfg-step" data-step="3">
            <p class="cfg-step-eyebrow">4 — Construction</p>
            <h2 class="cfg-step-title">Type de <em>Construction</em></h2>
            <p class="cfg-step-lead">Les options non disponibles pour votre sélection sont désactivées.</p>
            <div class="cfg-list" id="cfgConstructions">
              <?php
              $constFeatures = [
                  'Nédabaile' => ['Structure bois massif', 'Finition premium', 'Isolation phonique'],
                  'Tebelaire' => ['Construction hybride', 'Grande résistance', 'Design épuré'],
              ];
              foreach ($constructions as $c):
                  $feats = $constFeatures[$c['name']] ?? ['Qualité supérieure', 'Fabrication locale'];
              ?>
              <button class="cfg-opt" type="button" data-id="<?= $e($c['id']) ?>" data-name="<?= $e($c['name']) ?>">
                <div class="cfg-const-visual">
                  <div class="cfg-const-ph"></div>
                </div>
                <div class="cfg-const-body">
                  <span class="cfg-const-name"><?= $e($c['name']) ?></span>
                  <ul class="cfg-const-features">
                    <?php foreach ($feats as $f): ?>
                      <li><?= $e($f) ?></li>
                    <?php endforeach; ?>
                  </ul>
                  <span class="cfg-opt-na">Non disponible</span>
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
            <p class="cfg-step-eyebrow">5 — Dimensions</p>
            <h2 class="cfg-step-title">Vos <em>Dimensions</em></h2>
            <p class="cfg-step-lead">Entrez les dimensions souhaitées en centimètres.</p>

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
                    <label for="cfgWidthCm">Largeur <span>(cm)</span></label>
                    <div class="cfg-stepper">
                      <input type="number" id="cfgWidthCm" inputmode="numeric" min="50" max="200" step="1" value="90" />
                      <button type="button" class="cfg-stepper-btn" id="cfgWidthMinus" aria-label="Réduire la largeur">−</button>
                      <button type="button" class="cfg-stepper-btn" id="cfgWidthPlus" aria-label="Augmenter la largeur">+</button>
                    </div>
                  </div>
                  <div class="cfg-dim2-row">
                    <label for="cfgHeightCm">Hauteur <span>(cm)</span></label>
                    <div class="cfg-stepper">
                      <input type="number" id="cfgHeightCm" inputmode="numeric" min="150" max="300" step="1" value="210" />
                      <button type="button" class="cfg-stepper-btn" id="cfgHeightMinus" aria-label="Réduire la hauteur">−</button>
                      <button type="button" class="cfg-stepper-btn" id="cfgHeightPlus" aria-label="Augmenter la hauteur">+</button>
                    </div>
                  </div>
                </div>

                <div class="cfg-dim2-summary">
                  <p class="cfg-dim2-summary-label">Dimensions sélectionnées</p>
                  <p class="cfg-dim2-summary-value" id="cfgDimSummary">90 × 210 cm</p>
                  <p class="cfg-dim2-summary-sub">Porte intérieure standard</p>
                </div>
              </div>
            </div>
          </div>

          <!-- ═══ STEP 5 · RÉSUMÉ ═══ -->
          <div class="cfg-step" data-step="5">
            <p class="cfg-step-eyebrow">6 — Résumé</p>
            <h2 class="cfg-step-title">Votre <em>Porte</em></h2>
            <p class="cfg-step-lead">Vérifiez votre configuration complète, puis demandez votre devis ou sauvegardez-la pour plus tard.</p>
            <div class="cfg-review-layout">
              <!-- Left: door preview -->
              <div class="cfg-review-visual">
                <div class="cfg-review-img" id="cfgReviewImg"></div>
              </div>
              <!-- Right: complete configuration + price + actions -->
              <div class="cfg-review-details">
                <dl class="cfg-review" id="cfgReview"></dl>
                <div class="cfg-review-price">
                  <span>Prix estimé</span>
                  <strong id="cfgReviewPrice">—</strong>
                </div>
                <div class="cfg-review-actions">
                  <button type="button" class="btn btn--gold btn--block" id="cfgToDetails">
                    Demander un Devis
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" style="width:16px;height:16px;vertical-align:middle;margin-left:.4rem;"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                  </button>
                  <button type="button" class="btn btn--outline btn--block" id="cfgReviewSave">Sauvegarder la configuration</button>
                  <button type="button" class="btn btn--outline btn--block cfg-review-add" id="cfgAddAnother">+ Ajouter une autre porte</button>
                </div>
              </div>
            </div>
          </div>

          <!-- ═══ STEP 6 · DEVIS ═══ -->
          <div class="cfg-step" data-step="6">
            <div class="cfg-quote2">
              <!-- LEFT: intro + compact configuration recap -->
              <div class="cfg-quote2-intro">
                <p class="cfg-step-eyebrow">7 — Devis</p>
                <h2 class="cfg-step-title">Demandez votre <em>devis</em></h2>
                <p class="cfg-step-lead">Vérifiez votre porte sur mesure, notre équipe vous répond dans les plus brefs délais.</p>

                <div class="cfg-quote2-recap">
                  <span class="cfg-quote2-recap-name" id="cfgQuoteName">—</span>
                  <dl class="cfg-quote2-recap-list" id="cfgQuoteReview"></dl>
                  <div class="cfg-quote2-recap-price">
                    <span>Prix estimé</span>
                    <strong id="cfgQuotePrice">—</strong>
                  </div>
                </div>
              </div>

              <!-- RIGHT: contact form -->
              <form class="cfg-quote-form cfg-quote2-form" id="cfgQuoteForm" novalidate>
                <input type="text" name="company_website" id="cfgHoneypot" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true" />
                <div class="cfg-field"><label for="qName">Nom complet *</label><input type="text" id="qName" name="full_name" maxlength="120" required placeholder="Votre nom" /></div>
                <div class="cfg-field-row">
                  <div class="cfg-field"><label for="qEmail">Email *</label><input type="email" id="qEmail" name="email" maxlength="180" required placeholder="votre@email.com" /></div>
                  <div class="cfg-field"><label for="qPhone">Téléphone *</label><input type="tel" id="qPhone" name="phone" maxlength="30" required placeholder="+213 …" /></div>
                </div>
                <div class="cfg-field-row">
                  <div class="cfg-field"><label for="qCity">Ville *</label><input type="text" id="qCity" name="city" maxlength="100" required placeholder="Alger" /></div>
                  <div class="cfg-field"><label for="qCountry">Pays *</label><input type="text" id="qCountry" name="country" maxlength="100" required placeholder="Algérie" /></div>
                </div>
                <div class="cfg-field"><label for="qNotes">Notes</label><textarea id="qNotes" name="notes" rows="2" maxlength="3000" placeholder="Informations complémentaires…"></textarea></div>
                <p class="cfg-form-error" id="cfgFormError" hidden></p>
              </form>
            </div>
          </div>

          <!-- Navigation -->
          <div class="cfg-nav">
            <button class="btn btn--outline" id="cfgBack" type="button" disabled>Retour</button>
            <span class="cfg-nav-step" id="cfgNavStep">Étape 1 / 7</span>
            <button class="btn btn--gold" id="cfgNext" type="button">
              Suivant
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
                <em>Sélectionnez une couleur</em>
              </span>
              <span class="cfg-render-chip" id="cfgRenderChip"><span class="cfg-render-chip-dot" id="cfgRenderChipDot"></span><span id="cfgRenderChipName"></span></span>
            </div>
          </div>

          <!-- Summary body -->
          <div class="cfg-sidebar-body">
            <h3 class="cfg-summary-title">Configuration</h3>

            <dl class="cfg-summary-list" id="cfgCurrentSummary">
              <div><dt>Collection</dt><dd id="sumCollection">—</dd></div>
              <div><dt>Couleur</dt><dd id="sumColor">—</dd></div>
              <div><dt>Usage</dt><dd id="sumUsage">—</dd></div>
              <div><dt>Construction</dt><dd id="sumConstruction">—</dd></div>
              <div><dt>Dimensions</dt><dd id="sumDim">90 × 210 cm</dd></div>
            </dl>

            <div class="cfg-summary-price">
              <span class="cfg-summary-price-label">Prix estimé</span>
              <strong class="cfg-summary-price-value" id="sumPrice">—</strong>
            </div>

            <div class="cfg-cart" id="cfgCart" hidden>
              <h4 class="cfg-cart-title">Portes dans votre demande</h4>
              <ul class="cfg-cart-list" id="cfgCartList"></ul>
              <div class="cfg-summary-price">
                <span class="cfg-summary-price-label">Total</span>
                <strong class="cfg-summary-price-value" id="cfgCartTotal">—</strong>
              </div>
            </div>

            <div class="cfg-summary-actions">
              <button class="btn btn--gold btn--block" id="cfgQuote" type="button">Demander un Devis</button>
              <button class="btn btn--outline btn--block" id="cfgSave" type="button">Sauvegarder</button>
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
      <span class="cfg-confirm-mark">PORTES</span>
      <span class="cfg-confirm-sub">Demande de Devis</span>
    </div>
    <div class="cfg-confirm-check" aria-hidden="true">
      <svg viewBox="0 0 48 48" fill="none"><circle cx="24" cy="24" r="22" stroke="currentColor" stroke-width="1.5"/><polyline points="15,25 21,31 33,18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </div>
    <h2 class="cfg-confirm-title">Merci</h2>
    <p class="cfg-confirm-ref">Référence <strong id="cfgConfirmRef">—</strong></p>
    <div class="cfg-confirm-customer" id="cfgConfirmCustomer"></div>
    <table class="cfg-confirm-table" id="cfgConfirmItems"></table>
    <div class="cfg-confirm-total"><span>Total</span><strong id="cfgConfirmTotal">—</strong></div>
    <p class="cfg-confirm-note">Votre demande a bien été reçue. Nous vous contacterons sous 24h avec un devis personnalisé.</p>
    <div class="cfg-confirm-actions">
      <button type="button" class="btn btn--gold" id="cfgPrint">Imprimer / PDF</button>
      <button type="button" class="btn btn--outline" id="cfgAnother">Configurer une autre porte</button>
      <a href="/door-showroom" class="btn btn--outline">Retour à l'accueil</a>
    </div>
  </div>
</div>

<script type="application/json" id="cfgData"><?= json_encode($cfgData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
<script src="/door-showroom/assets/js/configurator2.js?v=<?= @filemtime(APP_ROOT . '/public/assets/js/configurator2.js') ?>"></script>
</body>
</html>
