(function () {
  'use strict';

  var CFG = {};
  try {
    var _el = document.getElementById('cfgData');
    if (_el) CFG = JSON.parse(_el.textContent || '{}');
  } catch (e) { CFG = window.CFG || {}; }

  // i18n strings supplied by PHP (single source of truth). Falls back to the
  // key so the UI never shows blanks if a string is missing.
  var I18N = CFG.i18n || {};
  function T(key, args) {
    var s = (I18N[key] != null) ? I18N[key] : key;
    if (args) { for (var k in args) { if (args.hasOwnProperty(k)) s = s.replace(':' + k, args[k]); } }
    return s;
  }
  // Number formatting locale tracks the chosen language.
  var NUM_LOCALE = (CFG.lang === 'ar') ? 'ar-DZ' : (CFG.lang === 'fr' ? 'fr-DZ' : 'en-US');

  var STEPS = 7;
  var S_COLLECTION = 0, S_COLOR = 1, S_USAGE = 2, S_CONSTRUCTION = 3,
      S_DIMENSIONS = 4, S_REVIEW = 5, S_DETAILS = 6;

  function freshState() {
    return {
      collection_id: null, collection_name: '',
      color_id: null, color_name: '', color_hex: '', color_img: '', color_door: '',
      usage_id: null, usage_name: '',
      construction_id: null, construction_name: '',
      product_id: null, product_name: '', product_img: '',
      width_mm: 900, height_mm: 2100, quantity: 1,
    };
  }
  var state = freshState();
  var cart = [];            // [{ ...config snapshot, unit_price, line_total, label }]
  var step = 0;
  var lastPricing = null;

  var $steps = qa('.cfg-step'), $prog = qa('.cfg-progress-step'), $lines = qa('.cfg-progress-line');
  var $back = id('cfgBack'), $next = id('cfgNext'), $navStep = id('cfgNavStep');
  var $render = id('cfgRender'), $renderDoor = id('cfgRenderDoor'), $renderTint = id('cfgRenderTint');
  var $renderWrap = $render && $render.parentElement;
  var $renderChip = id('cfgRenderChip'), $renderChipDot = id('cfgRenderChipDot'), $renderChipName = id('cfgRenderChipName');
  var $stageDim = id('cfgStageDim');
  var $sumColor = id('sumColor'), $sumColl = id('sumCollection'), $sumUsage = id('sumUsage'),
      $sumCon = id('sumConstruction'),
      $sumDim = id('sumDim'), $sumPrice = id('sumPrice');
  var $cart = id('cfgCart'), $cartList = id('cfgCartList'), $cartTotal = id('cfgCartTotal');
  var $sumPriceBlock = $sumPrice && $sumPrice.closest('.cfg-summary-price');
  var $sumActions = document.querySelector('.cfg-summary-actions');

  function id(x){ return document.getElementById(x); }
  function qa(s){ return Array.prototype.slice.call(document.querySelectorAll(s)); }
  function findById(list, i){ list = list || []; for (var k=0;k<list.length;k++){ if(list[k].id===i) return list[k]; } return null; }
  function escapeHtml(s){ return String(s==null?'':s).replace(/[&<>"']/g,function(c){return({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c];}); }
  // Display the colour name WITHOUT the collection it belongs to — the
  // collection is already chosen (e.g. "Gris Prestige" → "Gris"). Data is
  // untouched; this only affects what the user reads on the Colour step.
  function colorLabel(name){
    if (!name) return name;
    var coll = state.collection_name || '';
    if (coll) {
      var re = new RegExp('\\s*' + coll.replace(/[.*+?^${}()|[\]\\]/g,'\\$&') + '\\s*', 'i');
      var stripped = name.replace(re, ' ').replace(/\s+/g,' ').trim();
      if (stripped) return stripped;
    }
    return name;
  }

  /* ── matrix helpers ── */
  function matrixRow(usageId, constructionId) {
    if (!state.collection_id || !usageId || !constructionId) return null;
    var m = CFG.matrix || [];
    for (var k=0;k<m.length;k++) {
      if (m[k].collection_id===state.collection_id && m[k].usage_id===usageId && m[k].construction_id===constructionId) return m[k];
    }
    return null;
  }
  function usageHasAny(usageId) {
    var m = CFG.matrix || [];
    for (var k=0;k<m.length;k++) if (m[k].collection_id===state.collection_id && m[k].usage_id===usageId && m[k].available) return true;
    return false;
  }
  function constructionAvailable(constructionId) {
    if (!state.usage_id) return false;
    var row = matrixRow(state.usage_id, constructionId);
    return !!(row && row.available);
  }
  // Colours that belong to the chosen collection.
  function colorsForCollection(collectionId) {
    return (CFG.colors || []).filter(function (c) { return c.collection_id === collectionId; });
  }
  // Collection descriptions come from PHP i18n (CFG.i18n.coll_desc).
  var COLL_DESCS = I18N.coll_desc || {};
  var COLL_SLUGS = {
    'Heritage': 'heritage',
    'Moderne':  'moderne',
    'Prestige': 'prestige',
  };
  var COLL_ART = {
    'heritage':
      '<svg class="cfg-col-art-svg" viewBox="0 0 120 200" fill="none" xmlns="http://www.w3.org/2000/svg">' +
        '<rect x="16" y="14" width="88" height="180" rx="1" stroke="rgba(255,255,255,.32)" stroke-width="1.5"/>' +
        '<path d="M16,68 Q60,24 104,68" stroke="rgba(255,255,255,.24)" stroke-width="1.3" fill="none"/>' +
        '<rect x="24" y="76" width="32" height="44" rx="1" stroke="rgba(255,255,255,.2)" stroke-width="1"/>' +
        '<rect x="64" y="76" width="32" height="44" rx="1" stroke="rgba(255,255,255,.2)" stroke-width="1"/>' +
        '<rect x="24" y="130" width="32" height="52" rx="1" stroke="rgba(255,255,255,.2)" stroke-width="1"/>' +
        '<rect x="64" y="130" width="32" height="52" rx="1" stroke="rgba(255,255,255,.2)" stroke-width="1"/>' +
        '<circle cx="74" cy="157" r="3.5" stroke="rgba(196,168,120,.65)" stroke-width="1.3"/>' +
      '</svg>',
    'moderne':
      '<svg class="cfg-col-art-svg" viewBox="0 0 120 200" fill="none" xmlns="http://www.w3.org/2000/svg">' +
        '<rect x="22" y="18" width="76" height="170" rx="1" stroke="rgba(255,255,255,.3)" stroke-width="1.5"/>' +
        '<line x1="22" y1="103" x2="98" y2="103" stroke="rgba(255,255,255,.16)" stroke-width="1"/>' +
        '<line x1="22" y1="60" x2="98" y2="60" stroke="rgba(255,255,255,.1)" stroke-width="1"/>' +
        '<rect x="74" y="116" width="3" height="22" rx="1.5" fill="rgba(255,255,255,.5)"/>' +
      '</svg>',
    'prestige':
      '<svg class="cfg-col-art-svg" viewBox="0 0 140 210" fill="none" xmlns="http://www.w3.org/2000/svg">' +
        '<path d="M16,88 Q70,26 124,88 L124,200 L16,200 Z" stroke="rgba(255,255,255,.3)" stroke-width="1.5" fill="none"/>' +
        '<line x1="70" y1="88" x2="70" y2="200" stroke="rgba(255,255,255,.2)" stroke-width="1"/>' +
        '<rect x="23" y="100" width="38" height="32" rx="1" stroke="rgba(255,255,255,.18)" stroke-width=".9"/>' +
        '<rect x="23" y="142" width="38" height="48" rx="1" stroke="rgba(255,255,255,.18)" stroke-width=".9"/>' +
        '<rect x="79" y="100" width="38" height="32" rx="1" stroke="rgba(255,255,255,.18)" stroke-width=".9"/>' +
        '<rect x="79" y="142" width="38" height="48" rx="1" stroke="rgba(255,255,255,.18)" stroke-width=".9"/>' +
        '<circle cx="62" cy="170" r="3.5" stroke="rgba(196,168,120,.65)" stroke-width="1.3"/>' +
        '<circle cx="78" cy="170" r="3.5" stroke="rgba(196,168,120,.65)" stroke-width="1.3"/>' +
      '</svg>',
  };

  /* ── builders ── */
  function buildCollections() {
    var wrap = id('cfgCollections'); if (!wrap) return;
    wrap.innerHTML = '';
    (CFG.collections || []).forEach(function (col, i) {
      var slug = COLL_SLUGS[col.name] || col.name.toLowerCase().replace(/\s+/g, '-');
      var desc = COLL_DESCS[col.name] || '';
      var num  = String(i + 1).padStart(2, '0');
      var art  = COLL_ART[slug] || COLL_ART['moderne'];
      var hasImg   = !!col.img;
      var imgClass = hasImg ? ' has-img' : '';
      var imgStyle = hasImg ? ' style="background-image:url(\'' + col.img + '\')"' : '';
      var b = document.createElement('button');
      b.type = 'button'; b.className = 'cfg-opt'; b.dataset.id = col.id; b.dataset.name = col.name;
      b.innerHTML =
        '<div class="cfg-col-visual cfg-col-visual--' + slug + imgClass + '"' + imgStyle + '>' +
          art +
          '<div class="cfg-col-overlay"></div>' +
          '<span class="cfg-col-num">' + num + '</span>' +
          '<span class="cfg-col-tag">Collection</span>' +
        '</div>' +
        '<div class="cfg-col-footer">' +
          '<div class="cfg-col-sep"></div>' +
          '<span class="cfg-col-name">' + escapeHtml(col.name) + '</span>' +
          (desc ? '<span class="cfg-col-desc">' + escapeHtml(desc) + '</span>' : '') +
        '</div>' +
        '<div class="cfg-col-bar"></div>' +
        '<span class="cfg-col-check" aria-hidden="true"><svg viewBox="0 0 20 20" fill="none"><polyline points="4,10 8,14 16,6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>';
      b.addEventListener('click', function () { selectCollection(col, b, wrap); });
      wrap.appendChild(b);
    });
  }

  function buildColors() {
    var wrap = id('cfgColors'); var empty = id('cfgColorsEmpty'); if (!wrap) return;
    wrap.innerHTML = '';
    if (!state.collection_id) { if (empty) empty.hidden = false; return; }
    var list = colorsForCollection(state.collection_id);
    if (empty) empty.hidden = list.length > 0;
    list.forEach(function (c) {
      var b = document.createElement('button');
      b.type = 'button'; b.className = 'cfg-swatch';
      b.dataset.id = c.id; b.dataset.name = c.name; b.dataset.hex = c.hex || ''; b.dataset.img = c.img || '';
      b.title = c.name; b.setAttribute('aria-label', c.name);
      b.style.setProperty('--sw', c.hex || '#ccc');
      var tileStyle = c.img
        ? "background-image:url('" + c.img + "')"
        : 'background-color:' + (c.hex || '#ccc');
      b.innerHTML =
        '<div class="cfg-swatch-tile" style="' + tileStyle + '">' +
          '<span class="cfg-swatch-chk" aria-hidden="true"><svg viewBox="0 0 20 20" fill="none"><polyline points="4,10 8,14 16,6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>' +
        '</div>' +
        '<span class="cfg-swatch-name">' + escapeHtml(colorLabel(c.name)) + '</span>';
      b.addEventListener('click', function () { selectColor(c, b, wrap); });
      wrap.appendChild(b);
    });
  }

  function refreshUsages() {
    qa('#cfgUsages .cfg-opt').forEach(function (b) {
      var uid = +b.dataset.id;
      var ok = state.collection_id ? usageHasAny(uid) : false;
      b.classList.toggle('is-disabled', !ok); b.disabled = !ok;
      if (!ok && state.usage_id === uid) clearUsage();
    });
  }
  function refreshConstructions() {
    qa('#cfgConstructions .cfg-opt').forEach(function (b) {
      var cid = +b.dataset.id;
      var ok = state.collection_id && state.usage_id ? constructionAvailable(cid) : false;
      b.classList.toggle('is-disabled', !ok); b.disabled = !ok;
      if (!ok && state.construction_id === cid) clearConstruction();
    });
  }

  /* ── selections ── */
  function selectCollection(col, btn, wrap) {
    activate(wrap, btn);
    if (state.collection_id !== col.id) {
      state.color_id = null; state.color_name = ''; state.color_hex = ''; state.color_img = ''; state.color_door = '';
      clearUsage(); clearConstruction(); clearProduct();
    }
    state.collection_id = col.id; state.collection_name = col.name;
    buildColors(); refreshUsages(); refreshConstructions();
    setRender(); setSummary(); requestPrice();
  }
  function selectColor(c, btn, wrap) {
    activate(wrap, btn);
    state.color_id = c.id; state.color_name = c.name; state.color_hex = c.hex || '';
    state.color_img = c.img || '';      // flat swatch (for the chip)
    state.color_door = c.door || '';    // door photo (for the preview)
    clearProduct();
    setRender(); setSummary(); requestPrice();
    pulsePreview();
  }
  // Brief gold pulse on the preview so colour changes feel responsive.
  function pulsePreview() {
    if (!$renderWrap) return;
    $renderWrap.classList.remove('cfg-color-pulse');
    void $renderWrap.offsetWidth; // reflow to restart the animation
    $renderWrap.classList.add('cfg-color-pulse');
  }
  function clearUsage(){ state.usage_id=null; state.usage_name=''; deactivate('#cfgUsages'); }
  function clearConstruction(){ state.construction_id=null; state.construction_name=''; deactivate('#cfgConstructions'); }
  function clearProduct(){ state.product_id=null; state.product_name=''; state.product_img=''; }

  var $usages = id('cfgUsages');
  if ($usages) $usages.addEventListener('click', function (e) {
    var b = e.target.closest('.cfg-opt'); if (!b || b.disabled) return;
    activate($usages, b);
    state.usage_id = +b.dataset.id; state.usage_name = b.dataset.name;
    clearProduct(); refreshConstructions(); setRender(); setSummary(); requestPrice();
  });
  var $cons = id('cfgConstructions');
  if ($cons) $cons.addEventListener('click', function (e) {
    var b = e.target.closest('.cfg-opt'); if (!b || b.disabled) return;
    activate($cons, b);
    state.construction_id = +b.dataset.id; state.construction_name = b.dataset.name;
    clearProduct(); setRender(); setSummary(); requestPrice();
  });

  /* ── preview: product image > colour photo. A full uploaded photo fills
        the card (cover); a bare door asset is shown whole (contain). ── */
  function setRender() {
    if (!$render || !$renderDoor) return;
    var url = state.product_img || state.color_door || '';
    // Uploaded photos (in /uploads/) are real scenes → fill the card, no tint.
    var isPhoto = /\/uploads\//.test(url);
    if (url) {
      $renderDoor.style.backgroundImage = "url('" + url + "')";
      $renderDoor.style.filter = isPhoto ? 'none' : 'drop-shadow(0 30px 56px rgba(0,0,0,.55))';
      $render.classList.remove('is-empty');
    } else {
      $renderDoor.style.backgroundImage = ''; $renderDoor.style.filter = 'none';
      $render.classList.add('is-empty');
    }
    $render.classList.toggle('is-photo', !!url && isPhoto);
    // Tint only the bare door asset to the chosen colour; never a full photo.
    var mask = (url && !isPhoto) ? "url('" + url + "')" : 'none';
    if ($renderTint) {
      $renderTint.style.background = state.color_hex || 'transparent';
      $renderTint.style.opacity = (url && !isPhoto && state.color_hex && !state.product_img) ? '0.35' : '0';
      $renderTint.style.webkitMaskImage = mask; $renderTint.style.maskImage = mask;
    }
    // Preview reacts to the chosen colour: gold ring + confirming chip.
    if ($renderWrap) {
      var hasColor = !!state.color_id;
      $renderWrap.classList.toggle('has-color', hasColor);
      if (hasColor) {
        if ($renderChipDot) $renderChipDot.style.setProperty('--chip',
          state.color_img ? "url('" + state.color_img + "')" : (state.color_hex || '#ccc'));
        if ($renderChipName) $renderChipName.textContent = state.color_name || '';
      }
    }
  }

  function setSummary() {
    txt($sumColl, state.collection_name); txt($sumColor, state.color_name);
    txt($sumUsage, state.usage_name); txt($sumCon, state.construction_name);
    var dim = (state.width_mm/10) + ' × ' + (state.height_mm/10) + ' cm';
    if ($sumDim) $sumDim.textContent = dim;
    if ($stageDim) $stageDim.textContent = dim;
    // Only show a summary row once it has a value, so early steps stay clean
    // (e.g. on the Colour step the panel shows just Collection + Couleur).
    rowToggle($sumColl, state.collection_name);
    rowToggle($sumColor, state.color_name);
    rowToggle($sumUsage, state.usage_name);
    rowToggle($sumCon, state.construction_name);
    rowToggle($sumDim, (state.usage_name || state.construction_name) ? dim : '');
    // Price + actions only appear once a full combination is chosen.
    var ready = !!(state.collection_id && state.color_id && state.usage_id && state.construction_id);
    if ($sumPriceBlock) $sumPriceBlock.style.display = ready ? '' : 'none';
    if ($sumActions)    $sumActions.style.display    = ready ? '' : 'none';
  }
  function txt(el,v){ if (el) el.textContent = v || '—'; }
  // Show/hide a summary row (the <div> wrapping the <dt>/<dd>) by value.
  function rowToggle(dd, value){
    if (!dd) return;
    var row = dd.parentElement;
    if (row) row.style.display = value ? '' : 'none';
  }

  // Shared configuration rows (Collection → Quantité) for the summary panels.
  function summaryRowsHtml() {
    var rows = [
      [T('collection'), state.collection_name],
      [T('colour'), state.color_name], [T('usage'), state.usage_name],
      [T('construction'), state.construction_name],
      [T('dimensions'), (state.width_mm/10) + ' × ' + (state.height_mm/10) + ' cm'],
      [T('quantity'), String(state.quantity)],
    ];
    return rows.map(function (r) { return '<div><dt>'+r[0]+'</dt><dd>'+escapeHtml(r[1]||'—')+'</dd></div>'; }).join('');
  }
  function doorImagePh() {
    return '<div class="cfg-review-img-ph"><svg viewBox="0 0 48 64" fill="none" stroke="currentColor" stroke-width="1"><rect x="6" y="2" width="36" height="60" rx="1"/><circle cx="34" cy="32" r="1.5" fill="currentColor"/></svg></div>';
  }
  function paintDoorImg(el) {
    if (!el) return;
    // Use the door photo (product image or per-colour door), never the flat
    // swatch texture — a swatch stretched to fill the card looks like a blur.
    var imgUrl = state.product_img || state.color_door || '';
    if (imgUrl) { el.style.backgroundImage = "url('" + imgUrl + "')"; el.innerHTML = ''; }
    else { el.style.backgroundImage = ''; el.innerHTML = doorImagePh(); }
  }

  function buildReview() {
    var dl = id('cfgReview'); if (!dl) return;

    // Whether the current screen holds a valid, priced door not yet in the cart.
    var curUnit = unitPrice();
    var hasCurrent = !!(state.collection_id && state.color_id &&
                        state.usage_id && state.construction_id && curUnit > 0);
    var multi = (cart.length + (hasCurrent ? 1 : 0)) > 1;

    var single = document.querySelector('.cfg-review-visual');
    var layout = document.querySelector('.cfg-review-layout');
    var priceBlock = id('cfgReviewPrice') ? id('cfgReviewPrice').closest('.cfg-review-price') : null;
    if (layout) layout.classList.toggle('cfg-review-layout--list', multi);

    if (multi) {
      // Multiple doors → each door is shown as its own card (with image) in the
      // order list, so the single big preview + spec table + single price are
      // hidden (they'd only show one door and be misleading).
      if (single) single.style.display = 'none';
      if (dl) dl.innerHTML = '';
      if (priceBlock) priceBlock.style.display = 'none';
    } else {
      // Single door → keep the rich layout: big preview, spec table, single price.
      if (single) single.style.display = '';
      paintDoorImg(id('cfgReviewImg'));
      if (priceBlock) priceBlock.style.display = '';
      if (dl) dl.innerHTML = summaryRowsHtml();
      var rp = id('cfgReviewPrice'); if (rp) rp.textContent = lineLabel();
    }
    renderReviewCart(hasCurrent, curUnit, multi);
  }

  // Build one professional door card for the order list.
  function orderCardHtml(d, idx, isCurrent) {
    var img = d.product_img || d.color_door || '';
    var thumb = img
      ? '<span class="cfg-order-card-img" style="background-image:url(\'' + img + '\')"></span>'
      : '<span class="cfg-order-card-img cfg-order-card-img--ph"></span>';
    var remove = !isCurrent
      ? '<button type="button" class="cfg-order-card-remove" data-i="' + idx + '" aria-label="Remove">×</button>'
      : '';
    return '<li class="cfg-order-card' + (isCurrent ? ' cfg-order-card--current' : '') + '">' +
      '<span class="cfg-order-card-num">' + (idx + 1) + '</span>' +
      thumb +
      '<span class="cfg-order-card-body">' +
        '<span class="cfg-order-card-name">' + escapeHtml(d.label) + '</span>' +
        '<span class="cfg-order-card-meta">' + escapeHtml(d.usage_name || '') + ' · ' + escapeHtml(d.construction_name || '') + '</span>' +
        '<span class="cfg-order-card-meta">' + (d.width_mm/10) + ' × ' + (d.height_mm/10) + ' cm · ×' + d.quantity + '</span>' +
        '<span class="cfg-order-card-price">' + money(d.line_total) + '</span>' +
      '</span>' +
      remove +
      '</li>';
  }

  function renderReviewCart(hasCurrent, curUnit, multi) {
    var box = id('cfgReviewCart'); if (!box) return;
    if (!multi) { box.hidden = true; box.innerHTML = ''; return; }
    renderOrderList(box, hasCurrent, curUnit, buildReview);
  }

  // Shared itemized order list (all cart doors + the current door). Used on both
  // the Review and the final Quote step so the full order is always visible.
  function renderOrderList(box, hasCurrent, curUnit, rebuild) {
    if (!box) return;
    box.hidden = false;

    var n = 0;
    var cards = cart.map(function (it) { return orderCardHtml(it, n++, false); }).join('');

    var curCard = '';
    if (hasCurrent) {
      curCard = orderCardHtml(snapshotCurrent(), n, true);
    }

    var grand = cartTotal() + (hasCurrent ? curUnit * state.quantity : 0);
    box.innerHTML =
      '<h4 class="cfg-cart-title">' + T('cart_title') + '</h4>' +
      '<ul class="cfg-order-cards">' + cards + curCard + '</ul>' +
      '<div class="cfg-review-price"><span>' + T('cart_total') + '</span><strong>' + money(grand) + '</strong></div>';

    // Allow removing saved doors from this list (not the current/pending one).
    Array.prototype.forEach.call(box.querySelectorAll('.cfg-order-card-remove'), function (b) {
      b.addEventListener('click', function () { cart.splice(+b.dataset.i, 1); if (rebuild) rebuild(); });
    });
  }

  // Final quote (Devis) step: compact recap + price.
  function buildQuote() {
    var curUnit = unitPrice();
    var hasCurrent = currentIsValid();
    var multi = (cart.length + (hasCurrent ? 1 : 0)) > 1;

    var nm = id('cfgQuoteName');
    var dl = id('cfgQuoteReview');
    var priceBlock = id('cfgQuotePriceBlock');

    if (multi) {
      // Multiple doors → itemized order list, hide the single recap.
      if (nm) nm.style.display = 'none';
      if (dl) { dl.innerHTML = ''; dl.style.display = 'none'; }
      if (priceBlock) priceBlock.style.display = 'none';
      renderOrderList(id('cfgQuoteCart'), hasCurrent, curUnit, buildQuote);
    } else {
      if (nm) { nm.style.display = ''; nm.textContent = ((state.collection_name||'') + ' ' + (state.color_name||'')).trim() || '—'; }
      if (dl) { dl.style.display = ''; dl.innerHTML = summaryRowsHtml(); }
      if (priceBlock) priceBlock.style.display = '';
      var qp = id('cfgQuotePrice'); if (qp) qp.textContent = lineLabel();
      var box = id('cfgQuoteCart'); if (box) { box.hidden = true; box.innerHTML = ''; }
    }
  }

  /* ── cart of doors ── */
  function unitPrice() { return (lastPricing && lastPricing.available !== false) ? Number(lastPricing.total_price||0) : 0; }
  function lineLabel() {
    if (!lastPricing || lastPricing.available === false) return T('na');
    var line = unitPrice() * state.quantity;
    return state.quantity > 1
      ? (priceLabel(lastPricing) + ' × ' + state.quantity + ' = ' + money(line))
      : priceLabel(lastPricing);
  }
  function money(n) {
    // Whole DZD only — no decimals. Group thousands with a thin space and never
    // let a locale decimal separator leak through.
    var v = Math.round(Number(n) || 0);
    return v.toLocaleString('en-US', { maximumFractionDigits: 0 }).replace(/,/g, ' ') + ' DZD';
  }

  function snapshotCurrent() {
    var unit = unitPrice();
    return {
      collection_id: state.collection_id, collection_name: state.collection_name,
      color_id: state.color_id, color_name: state.color_name, color_img: state.color_img, color_door: state.color_door,
      usage_id: state.usage_id, usage_name: state.usage_name,
      construction_id: state.construction_id, construction_name: state.construction_name,
      product_id: state.product_id, product_name: state.product_name, product_img: state.product_img,
      width_mm: state.width_mm, height_mm: state.height_mm, quantity: state.quantity,
      unit_price: unit, line_total: unit * state.quantity,
      label: (state.product_name || (state.collection_name + ' ' + state.color_name)),
    };
  }
  function addCurrentToCart() {
    if (!state.collection_id || !state.color_id || !state.usage_id || !state.construction_id) { nudge(); return false; }
    cart.push(snapshotCurrent());
    renderCart();
    return true;
  }
  function cartTotal() { return cart.reduce(function (s, i) { return s + i.line_total; }, 0); }
  function renderCart() {
    if (!$cart) return;
    if (!cart.length) { $cart.hidden = true; if ($cartList) $cartList.innerHTML = ''; return; }
    $cart.hidden = false;
    $cartList.innerHTML = cart.map(function (it, i) {
      return '<li class="cfg-cart-item">' +
        '<span class="cfg-cart-thumb"' + (it.product_img || it.color_img ? ' style="background-image:url(\''+(it.product_img||it.color_img)+'\')"' : '') + '></span>' +
        '<span class="cfg-cart-info"><span class="cfg-cart-name">' + escapeHtml(it.label) + '</span>' +
        '<span class="cfg-cart-meta">' + escapeHtml(it.usage_name) + ' · ' + escapeHtml(it.construction_name) + ' · ' + (it.width_mm/10) + '×' + (it.height_mm/10) + 'cm × ' + it.quantity + '</span></span>' +
        '<span class="cfg-cart-price">' + money(it.line_total) + '</span>' +
        '<button type="button" class="cfg-cart-remove" data-i="' + i + '" aria-label="Remove">×</button>' +
        '</li>';
    }).join('');
    if ($cartTotal) $cartTotal.textContent = money(cartTotal());
    qa('#cfgCartList .cfg-cart-remove').forEach(function (b) {
      b.addEventListener('click', function () { cart.splice(+b.dataset.i, 1); renderCart(); });
    });
  }

  /* ── navigation ── */
  function showStep(n) {
    n = Math.max(0, Math.min(STEPS - 1, n)); step = n;
    $steps.forEach(function (s) { s.classList.toggle('is-active', +s.dataset.step === n); });
    $prog.forEach(function (p, i) { p.classList.toggle('is-active', i===n); p.classList.toggle('is-done', i<n); });
    $lines.forEach(function (l, i) { l.classList.toggle('is-done', i<n); });
    if (n === S_REVIEW) buildReview();
    if (n === S_DETAILS) buildQuote();
    var $page = document.querySelector('.cfg-page');
    if ($page) $page.classList.toggle('is-collection-step', n === S_COLLECTION);
    document.body.classList.toggle('cfg-on-collection', n === S_COLLECTION);
    // Preview sidebar is shown only on the Colour step (Collection has its own
    // immersive layout; Résumé has its own inline review). Hidden elsewhere.
    document.body.classList.toggle('cfg-on-color', n === S_COLOR);
    if ($back) $back.disabled = (n === 0);
    if ($next) {
      // Review has its own buttons; Details submits; everything else advances.
      $next.style.display = (n === S_REVIEW) ? 'none' : '';
      var label = (n === S_DETAILS) ? T('submit_quote') : T('next');
      $next.innerHTML = label + '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>';
    }
    if ($navStep) $navStep.textContent = T('step_x_y', { n: Math.min(n+1, STEPS-1), total: (STEPS-1) });
  }
  function canAdvance() {
    switch (step) {
      case S_COLLECTION:   return !!state.collection_id;
      case S_COLOR:        return !!state.color_id;
      case S_USAGE:        return !!state.usage_id;
      case S_CONSTRUCTION: return !!state.construction_id && !!(matrixRow(state.usage_id, state.construction_id) || {}).available;
      default: return true;
    }
  }
  function nudge(){ var a=$steps[step]; if(!a)return; a.classList.remove('cfg-nudge'); void a.offsetWidth; a.classList.add('cfg-nudge'); }

  // Brief confirmation toast (e.g. after adding a door to the request).
  var toastTimer;
  function toast(msg) {
    var el = id('cfgToast');
    if (!el) {
      el = document.createElement('div');
      el.id = 'cfgToast';
      el.className = 'cfg-toast';
      document.body.appendChild(el);
    }
    el.textContent = msg;
    el.classList.add('is-show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { el.classList.remove('is-show'); }, 2600);
  }

  if ($next) $next.addEventListener('click', function () {
    if (step === S_DETAILS) { submitQuote(); return; }
    if (!canAdvance()) { nudge(); return; }
    showStep(step + 1);
  });
  if ($back) $back.addEventListener('click', function () { showStep(step - 1); });
  $prog.forEach(function (p) { p.addEventListener('click', function () { var t=+p.dataset.step; if (t <= step) showStep(t); }); });

  /* ── review actions: add another / continue ── */
  if (id('cfgAddAnother')) id('cfgAddAnother').addEventListener('click', function () {
    if (!addCurrentToCart()) return;
    var added = cart.length;
    state = freshState();
    deactivate('#cfgCollections'); deactivate('#cfgColors'); deactivate('#cfgUsages'); deactivate('#cfgConstructions');
    setQty(1);
    if ($w) $w.value = 900; if ($h) $h.value = 2100;
    syncVisibleSliders();
    buildColors(); refreshUsages(); refreshConstructions();
    lastPricing = null; paintPrice(T('price_hint'));
    setRender(); setSummary();
    showStep(S_COLLECTION);
    toast(T('door_added', { n: added }));
  });
  if (id('cfgToDetails')) id('cfgToDetails').addEventListener('click', function () {
    // Need at least one door: either something in the cart or a valid current door.
    if (!cart.length && !currentIsValid()) { nudge(); return; }
    showStep(S_DETAILS);
  });

  /* ── dimensions (cm steppers → mm state) ── */
  var $w = id('cfgWidth'), $h = id('cfgHeight'), dimTimer;
  var $wcm = id('cfgWidthCm'), $hcm = id('cfgHeightCm');
  // cm bounds mirror the hidden mm range inputs (500–2000 / 1500–3000 mm).
  var W_MIN = 50, W_MAX = 200, H_MIN = 150, H_MAX = 300;

  function clampCm(v, lo, hi){ v = parseInt(v,10); if (isNaN(v)) v = lo; return Math.max(lo, Math.min(hi, v)); }

  // Refresh the on-diagram captions, the summary card, and the door proportions.
  function paintDim() {
    var wcm = state.width_mm/10, hcm = state.height_mm/10;
    var wv = id('cfgDimWViz'), hv = id('cfgDimHViz');
    if (wv) wv.innerHTML = wcm + '<small>cm</small>';
    if (hv) hv.innerHTML = hcm + '<small>cm</small>';
    var sum = id('cfgDimSummary'); if (sum) sum.textContent = wcm + ' × ' + hcm + ' cm';
    // Scale the door figure proportionally to the chosen dimensions.
    var door = id('cfgDimDoor');
    if (door) {
      var wRatio = (state.width_mm - 500) / (2000 - 500);   // 0..1
      var hRatio = (state.height_mm - 1500) / (3000 - 1500); // 0..1
      door.style.width  = (46 + wRatio * 26) + '%';
      door.style.height = (78 + hRatio * 18) + '%';
    }
  }

  function onDim() {
    state.width_mm = parseInt($w.value,10); state.height_mm = parseInt($h.value,10);
    if ($wcm) $wcm.value = state.width_mm/10;
    if ($hcm) $hcm.value = state.height_mm/10;
    paintDim();
    setSummary(); clearTimeout(dimTimer); dimTimer = setTimeout(requestPrice, 180);
  }
  if ($w) $w.addEventListener('input', onDim);
  if ($h) $h.addEventListener('input', onDim);

  // cm input → hidden mm range → onDim
  function setWidthCm(cm){ cm = clampCm(cm, W_MIN, W_MAX); if ($w) { $w.value = cm * 10; onDim(); } }
  function setHeightCm(cm){ cm = clampCm(cm, H_MIN, H_MAX); if ($h) { $h.value = cm * 10; onDim(); } }
  function curWcm(){ return Math.round(state.width_mm/10); }
  function curHcm(){ return Math.round(state.height_mm/10); }

  if ($wcm) $wcm.addEventListener('input', function(){ if ($w) { $w.value = clampCm($wcm.value, W_MIN, W_MAX)*10; onDimSoft(); } });
  if ($hcm) $hcm.addEventListener('input', function(){ if ($h) { $h.value = clampCm($hcm.value, H_MIN, H_MAX)*10; onDimSoft(); } });
  // Re-clamp the visible field only when the user leaves it (avoids fighting typing).
  if ($wcm) $wcm.addEventListener('blur', function(){ $wcm.value = curWcm(); });
  if ($hcm) $hcm.addEventListener('blur', function(){ $hcm.value = curHcm(); });
  // Like onDim but doesn't overwrite the field the user is typing in.
  function onDimSoft() {
    state.width_mm = parseInt($w.value,10); state.height_mm = parseInt($h.value,10);
    paintDim(); setSummary(); clearTimeout(dimTimer); dimTimer = setTimeout(requestPrice, 180);
  }

  if (id('cfgWidthMinus'))  id('cfgWidthMinus').addEventListener('click',  function(){ setWidthCm(curWcm() - 1); });
  if (id('cfgWidthPlus'))   id('cfgWidthPlus').addEventListener('click',   function(){ setWidthCm(curWcm() + 1); });
  if (id('cfgHeightMinus')) id('cfgHeightMinus').addEventListener('click', function(){ setHeightCm(curHcm() - 1); });
  if (id('cfgHeightPlus'))  id('cfgHeightPlus').addEventListener('click',  function(){ setHeightCm(curHcm() + 1); });

  // Quantity: a visible stepper (#cfgQtyVisible) plus the legacy hidden input
  // (#cfgQty) kept in sync so all cart/quote logic continues to read it.
  var $qty = id('cfgQty');
  var $qtyVis = id('cfgQtyVisible');
  function setQty(v) {
    v = Math.max(1, Math.min(999, parseInt(v, 10) || 1));
    state.quantity = v;
    if ($qty) $qty.value = v;
    if ($qtyVis) $qtyVis.value = v;
    setSummary();
  }
  if ($qtyVis) $qtyVis.addEventListener('input', function () { setQty($qtyVis.value); });
  if (id('cfgQtyMinus')) id('cfgQtyMinus').addEventListener('click', function () { setQty(state.quantity - 1); });
  if (id('cfgQtyPlus'))  id('cfgQtyPlus').addEventListener('click',  function () { setQty(state.quantity + 1); });
  function syncVisibleSliders() { if ($wcm) $wcm.value = curWcm(); if ($hcm) $hcm.value = curHcm(); paintDim(); }

  /* ── pricing ── */
  function priceConfig() {
    return {
      collection_id: state.collection_id, color_id: state.color_id,
      door_type_id: state.usage_id, construction_type_id: state.construction_id,
      width_mm: state.width_mm, height_mm: state.height_mm,
    };
  }
  function priceLabel(p) {
    if (!p) return '—';
    if (p.available === false) return T('na');
    if (p.total_price_fmt) return p.total_price_fmt;
    if (p.total_price != null) return Number(p.total_price).toLocaleString(NUM_LOCALE) + ' ' + (p.currency || 'DZD');
    return '—';
  }
  var priceTimer;
  function requestPrice() {
    if (!state.collection_id || !state.usage_id || !state.construction_id) {
      lastPricing = null; paintPrice(T('price_hint')); return;
    }
    clearTimeout(priceTimer); priceTimer = setTimeout(doPrice, 120);
  }
  function doPrice() {
    fetch(CFG.priceUrl, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-Token':CFG.csrf}, body:JSON.stringify(priceConfig()) })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (!d || !d.success) return;
        lastPricing = d.pricing; paintPrice(priceLabel(d.pricing));
        if (step === S_REVIEW) buildReview();
      }).catch(function(){});
  }
  function paintPrice(label) {
    if ($sumPrice) $sumPrice.textContent = label;
  }

  /* ── save ── */
  function saveConfig(btn, doneLabel, restoreLabel) {
    if (!state.collection_id) { nudge(); return; }
    fetch(CFG.saveUrl, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-Token':CFG.csrf}, body:JSON.stringify({ config: priceConfig(), name: (state.collection_name||'PORTES')+' '+(state.color_name||'')+' Door' }) })
      .then(function (r) { return r.json(); })
      .then(function (d) { if (d && d.success && d.url) { history.replaceState(null,'',d.url); if (btn) { btn.textContent=doneLabel; setTimeout(function(){btn.textContent=restoreLabel;},2200); } } })
      .catch(function(){});
  }
  var $save = id('cfgSave');
  if ($save) $save.addEventListener('click', function () { saveConfig($save, T('saved'), T('save')); });
  var $reviewSave = id('cfgReviewSave');
  if ($reviewSave) $reviewSave.addEventListener('click', function () { saveConfig($reviewSave, T('saved'), T('save_cfg')); });

  /* ── quote ── */
  var $quote = id('cfgQuote');
  if ($quote) $quote.addEventListener('click', function () { showStep(S_REVIEW); });
  function val(x){ var el=id(x); return el ? el.value.trim() : ''; }
  function showError(m){ var err=id('cfgFormError'); if(err){ err.hidden=false; err.textContent=m; } }
  function firstError(o){ for (var k in o){ if (o.hasOwnProperty(k)) return o[k]; } return T('err_review'); }

  // Doors to submit: every door in the cart, PLUS the current door if it is a
  // valid, priced configuration that hasn't been added to the cart yet.
  function itemsToSubmit() {
    return allDoors().map(function (it) {
      return { quantity: it.quantity, config: {
        collection_id: it.collection_id, color_id: it.color_id,
        door_type_id: it.usage_id, construction_type_id: it.construction_id,
        product_id: it.product_id, width_mm: it.width_mm, height_mm: it.height_mm,
      }};
    });
  }

  // Single source of truth for every door in the order: all cart doors plus the
  // current door if it's a valid, priced configuration not yet added to the cart.
  // Each entry is a full display snapshot (names, dimensions, prices).
  function allDoors() {
    var doors = cart.slice();
    if (currentIsValid()) {
      doors.push(snapshotCurrent());
    }
    return doors;
  }

  // The current screen describes a complete, priced door.
  function currentIsValid() {
    return !!(state.collection_id && state.color_id &&
              state.usage_id && state.construction_id && unitPrice() > 0);
  }

  function submitQuote() {
    var err = id('cfgFormError'); if (err) err.hidden = true;
    var customer = {
      full_name: val('qName'), email: val('qEmail'), phone: val('qPhone'),
      country: val('qCountry'), city: val('qCity'), notes: val('qNotes'),
    };
    if (!customer.full_name || !customer.email || !customer.phone || !customer.country || !customer.city) {
      showError('Please fill in all required fields.'); return;
    }
    var items = itemsToSubmit();
    if (!items.length) { showError('Please configure at least one door.'); return; }

    var payload = {
      full_name: customer.full_name, email: customer.email, phone: customer.phone,
      country: customer.country, city: customer.city, notes: customer.notes,
      company_website: val('cfgHoneypot'), items: items,
    };
    $next.disabled = true;
    fetch(CFG.quoteUrl, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-Token':CFG.csrf}, body:JSON.stringify(payload) })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        $next.disabled = false;
        if (d.success && d.reference) {
          showConfirmation(d, customer);
        } else if (d.errors) { showError(firstError(d.errors)); }
        else { showError(d.message || 'Could not submit your request. Please try again.'); }
      }).catch(function(){ $next.disabled=false; showError('Network error. Please try again.'); });
  }

  /* ── printable confirmation ── */
  function showConfirmation(d, customer) {
    // Every door in the order (all cart doors + the current one).
    var lines = allDoors();
    var ref = d.reference;
    if (id('cfgConfirmRef')) id('cfgConfirmRef').textContent = ref;
    if (id('cfgConfirmCustomer')) {
      id('cfgConfirmCustomer').innerHTML =
        '<strong>' + escapeHtml(customer.full_name) + '</strong><br>' +
        escapeHtml(customer.email) + ' · ' + escapeHtml(customer.phone) + '<br>' +
        escapeHtml(customer.city) + ', ' + escapeHtml(customer.country);
    }
    var rowsHtml = '<tr><th>Door</th><th>Details</th><th>Qty</th><th>Price</th></tr>';
    lines.forEach(function (it) {
      rowsHtml += '<tr>' +
        '<td>' + escapeHtml(it.label) + '</td>' +
        '<td>' + escapeHtml(it.collection_name + ' · ' + it.color_name + ' · ' + it.usage_name + ' · ' + it.construction_name) +
          '<br>' + (it.width_mm/10) + ' × ' + (it.height_mm/10) + ' cm</td>' +
        '<td>' + it.quantity + '</td>' +
        '<td>' + money(it.line_total) + '</td>' +
        '</tr>';
    });
    if (id('cfgConfirmItems')) id('cfgConfirmItems').innerHTML = rowsHtml;
    var total = (d.pricing && d.pricing.total_price_fmt) ? d.pricing.total_price_fmt : money(lines.reduce(function(s,i){return s+i.line_total;},0));
    if (id('cfgConfirmTotal')) id('cfgConfirmTotal').textContent = total;

    // WhatsApp lead notification: when the admin has configured a number, the
    // backend returns a ready wa.me URL pre-filled with the order. Show the
    // button and auto-open it once so the order reaches the team's WhatsApp.
    var wa = id('cfgWhatsApp');
    if (wa) {
      if (d.whatsapp_url) {
        wa.href = d.whatsapp_url;
        wa.hidden = false;
        // Fire once, shortly after the success screen renders (a user-gesture
        // context — the submit click — so most browsers allow the new tab).
        try { window.open(d.whatsapp_url, '_blank'); } catch (e) {}
      } else {
        wa.hidden = true;
      }
    }

    var overlay = id('cfgConfirm'); if (overlay) overlay.hidden = false;
    document.body.style.overflow = 'hidden';
  }
  if (id('cfgPrint')) id('cfgPrint').addEventListener('click', function () { window.print(); });
  if (id('cfgAnother')) id('cfgAnother').addEventListener('click', function () { window.location.href = '/door-showroom/configure'; });

  /* ── helpers ── */
  function activate(c, btn){ if(!c)return; c.querySelectorAll('.is-active').forEach(function(x){x.classList.remove('is-active');}); if(btn) btn.classList.add('is-active'); }
  function deactivate(sel){ qa(sel + ' .is-active').forEach(function(x){x.classList.remove('is-active');}); }

  /* ── preload from a product page (locks collection + colour) ── */
  function applyPreload() {
    if (!CFG.preCollectionId) return;
    var col = findById(CFG.collections, CFG.preCollectionId);
    if (!col) return;
    state.collection_id = col.id; state.collection_name = col.name;
    buildColors(); refreshUsages(); refreshConstructions();
    if (CFG.preColorId) {
      var c = findById(colorsForCollection(col.id), CFG.preColorId);
      if (c) { state.color_id = c.id; state.color_name = c.name; state.color_hex = c.hex || ''; state.color_img = c.img || ''; }
    }
  }

  /* init */
  buildCollections();
  applyPreload();
  setRender(); setSummary(); paintDim();
  showStep(state.color_id ? S_USAGE : (state.collection_id ? S_COLOR : S_COLLECTION));
  requestPrice();
}());
