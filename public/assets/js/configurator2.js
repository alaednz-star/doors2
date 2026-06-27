(function () {
  'use strict';

  var CFG = {};
  try {
    var _el = document.getElementById('cfgData');
    if (_el) CFG = JSON.parse(_el.textContent || '{}');
  } catch (e) { CFG = window.CFG || {}; }

  var STEPS = 8;
  var S_COLLECTION = 0, S_COLOR = 1, S_USAGE = 2, S_CONSTRUCTION = 3,
      S_PRODUCT = 4, S_DIMENSIONS = 5, S_REVIEW = 6, S_DETAILS = 7;

  function freshState() {
    return {
      collection_id: null, collection_name: '',
      color_id: null, color_name: '', color_hex: '', color_img: '',
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
  var $stageDim = id('cfgStageDim');
  var $sumColor = id('sumColor'), $sumColl = id('sumCollection'), $sumUsage = id('sumUsage'),
      $sumCon = id('sumConstruction'), $sumProd = id('sumProduct'),
      $sumDim = id('sumDim'), $sumPrice = id('sumPrice'), $fabPrice = id('cfgFabPrice');
  var $cart = id('cfgCart'), $cartList = id('cfgCartList'), $cartTotal = id('cfgCartTotal');

  function id(x){ return document.getElementById(x); }
  function qa(s){ return Array.prototype.slice.call(document.querySelectorAll(s)); }
  function findById(list, i){ list = list || []; for (var k=0;k<list.length;k++){ if(list[k].id===i) return list[k]; } return null; }
  function escapeHtml(s){ return String(s==null?'':s).replace(/[&<>"']/g,function(c){return({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c];}); }

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
  // Products matching Collection + Colour + Usage + Construction.
  function matchingProducts() {
    if (!state.collection_id || !state.color_id || !state.usage_id || !state.construction_id) return [];
    return (CFG.products || []).filter(function (p) {
      return p.collection_id===state.collection_id && p.color_id===state.color_id &&
             p.usage_id===state.usage_id && p.construction_id===state.construction_id;
    });
  }

  /* ── builders ── */
  function buildCollections() {
    var wrap = id('cfgCollections'); if (!wrap) return;
    wrap.innerHTML = '';
    (CFG.collections || []).forEach(function (col) {
      var b = document.createElement('button');
      b.type = 'button'; b.className = 'cfg-opt'; b.dataset.id = col.id; b.dataset.name = col.name;
      b.innerHTML = '<span class="cfg-opt-name">' + escapeHtml(col.name) + '</span>' +
        '<span class="cfg-opt-check" aria-hidden="true"><svg viewBox="0 0 20 20" fill="none"><polyline points="4,10 8,14 16,6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>';
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
      b.innerHTML = '<span class="cfg-swatch-dot" style="' +
        (c.img ? "background-image:url('"+c.img+"')" : 'background-color:'+(c.hex||'#ccc')) +
        '"></span><span class="cfg-swatch-name">' + escapeHtml(c.name) + '</span>';
      b.addEventListener('click', function () { selectColor(c, b, wrap); });
      wrap.appendChild(b);
    });
  }

  // Door Design step: show products matching all four choices; preselect if one.
  function buildProducts() {
    var wrap = id('cfgProducts'); var empty = id('cfgProductsEmpty'); if (!wrap) return;
    wrap.innerHTML = '';
    var list = matchingProducts();
    if (!list.length) {
      if (empty) empty.hidden = false;
      state.product_id = null; state.product_name = ''; state.product_img = '';
      setRender(); setSummary();
      return;
    }
    if (empty) empty.hidden = true;
    list.forEach(function (p, i) {
      var b = document.createElement('button');
      b.type = 'button'; b.className = 'cfg-card' + (p.img ? '' : ' cfg-card--noimg');
      b.dataset.id = p.id;
      b.innerHTML =
        (p.img ? '<span class="cfg-card-img" style="background-image:url(\'' + p.img + '\')"></span>' : '') +
        '<span class="cfg-card-overlay"></span>' +
        '<span class="cfg-card-label">' + escapeHtml(p.name) + '</span>' +
        '<span class="cfg-card-check" aria-hidden="true"><svg viewBox="0 0 20 20" fill="none"><polyline points="4,10 8,14 16,6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>';
      b.addEventListener('click', function () { selectProduct(p, b, wrap); });
      wrap.appendChild(b);
    });
    // Preselect when only one design matches.
    if (list.length === 1) {
      var only = wrap.querySelector('.cfg-card');
      selectProduct(list[0], only, wrap);
    }
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
      state.color_id = null; state.color_name = ''; state.color_hex = ''; state.color_img = '';
      clearUsage(); clearConstruction(); clearProduct();
    }
    state.collection_id = col.id; state.collection_name = col.name;
    buildColors(); refreshUsages(); refreshConstructions();
    setRender(); setSummary(); requestPrice();
  }
  function selectColor(c, btn, wrap) {
    activate(wrap, btn);
    state.color_id = c.id; state.color_name = c.name; state.color_hex = c.hex || ''; state.color_img = c.img || '';
    clearProduct();
    setRender(); setSummary(); requestPrice();
  }
  function selectProduct(p, btn, wrap) {
    activate(wrap, btn);
    state.product_id = p.id; state.product_name = p.name; state.product_img = p.img || '';
    setRender(); setSummary();
  }
  function clearUsage(){ state.usage_id=null; state.usage_name=''; deactivate('#cfgUsages'); }
  function clearConstruction(){ state.construction_id=null; state.construction_name=''; deactivate('#cfgConstructions'); }
  function clearProduct(){ state.product_id=null; state.product_name=''; state.product_img=''; deactivate('#cfgProducts'); }

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

  /* ── preview: product image > colour image, tinted ── */
  function setRender() {
    if (!$render || !$renderDoor) return;
    var url = state.product_img || state.color_img || '';
    if (url) {
      $renderDoor.style.backgroundImage = "url('" + url + "')";
      $renderDoor.style.filter = 'drop-shadow(0 30px 56px rgba(0,0,0,.55))';
      $render.classList.remove('is-empty');
    } else {
      $renderDoor.style.backgroundImage = ''; $renderDoor.style.filter = 'none';
      $render.classList.add('is-empty');
    }
    var mask = url ? "url('" + url + "')" : 'none';
    if ($renderTint) {
      $renderTint.style.background = state.color_hex || 'transparent';
      $renderTint.style.opacity = (url && state.color_hex && !state.product_img) ? '0.35' : '0';
      $renderTint.style.webkitMaskImage = mask; $renderTint.style.maskImage = mask;
    }
  }

  function setSummary() {
    txt($sumColl, state.collection_name); txt($sumColor, state.color_name);
    txt($sumUsage, state.usage_name); txt($sumCon, state.construction_name);
    txt($sumProd, state.product_name);
    var dim = (state.width_mm/10) + ' × ' + (state.height_mm/10) + ' cm';
    if ($sumDim) $sumDim.textContent = dim;
    if ($stageDim) $stageDim.textContent = dim;
  }
  function txt(el,v){ if (el) el.textContent = v || '—'; }

  function buildReview() {
    var dl = id('cfgReview'); if (!dl) return;
    var rows = [
      ['Design', state.product_name], ['Collection', state.collection_name],
      ['Colour', state.color_name], ['Usage', state.usage_name],
      ['Construction', state.construction_name],
      ['Dimensions', (state.width_mm/10) + ' × ' + (state.height_mm/10) + ' cm'],
      ['Quantity', String(state.quantity)],
    ];
    dl.innerHTML = rows.map(function (r) { return '<div><dt>'+r[0]+'</dt><dd>'+escapeHtml(r[1]||'—')+'</dd></div>'; }).join('');
    var rp = id('cfgReviewPrice'); if (rp) rp.textContent = lineLabel();
  }

  /* ── cart of doors ── */
  function unitPrice() { return (lastPricing && lastPricing.available !== false) ? Number(lastPricing.total_price||0) : 0; }
  function lineLabel() {
    if (!lastPricing || lastPricing.available === false) return 'Non disponible';
    var line = unitPrice() * state.quantity;
    return state.quantity > 1
      ? (priceLabel(lastPricing) + ' × ' + state.quantity + ' = ' + money(line))
      : priceLabel(lastPricing);
  }
  function money(n) { return Number(n).toLocaleString('en-US').replace(/,/g,' ') + ' DZD'; }

  function snapshotCurrent() {
    var unit = unitPrice();
    return {
      collection_id: state.collection_id, collection_name: state.collection_name,
      color_id: state.color_id, color_name: state.color_name, color_img: state.color_img,
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
    if (n === S_PRODUCT) buildProducts();
    $steps.forEach(function (s) { s.classList.toggle('is-active', +s.dataset.step === n); });
    $prog.forEach(function (p, i) { p.classList.toggle('is-active', i===n); p.classList.toggle('is-done', i<n); });
    $lines.forEach(function (l, i) { l.classList.toggle('is-done', i<n); });
    if (n === S_REVIEW) buildReview();
    if ($back) $back.disabled = (n === 0);
    if ($next) {
      // Review has its own buttons; Details submits; everything else advances.
      $next.style.display = (n === S_REVIEW) ? 'none' : '';
      var label = (n === S_DETAILS) ? 'Submit Request' : 'Next';
      $next.innerHTML = label + '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>';
    }
    if ($navStep) $navStep.textContent = 'Step ' + (n+1) + ' of ' + STEPS;
  }
  function canAdvance() {
    switch (step) {
      case S_COLLECTION:   return !!state.collection_id;
      case S_COLOR:        return !!state.color_id;
      case S_USAGE:        return !!state.usage_id;
      case S_CONSTRUCTION: return !!state.construction_id && !!(matrixRow(state.usage_id, state.construction_id) || {}).available;
      case S_PRODUCT:      return !!state.product_id;
      default: return true;
    }
  }
  function nudge(){ var a=$steps[step]; if(!a)return; a.classList.remove('cfg-nudge'); void a.offsetWidth; a.classList.add('cfg-nudge'); }

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
    state = freshState();
    deactivate('#cfgCollections'); deactivate('#cfgColors'); deactivate('#cfgUsages'); deactivate('#cfgConstructions'); deactivate('#cfgProducts');
    if ($qty) $qty.value = 1;
    if ($w) $w.value = 900; if ($h) $h.value = 2100;
    if ($wv) $wv.textContent = '90 cm'; if ($hv) $hv.textContent = '210 cm';
    buildColors(); refreshUsages(); refreshConstructions();
    lastPricing = null; paintPrice('Configure to see price');
    setRender(); setSummary();
    showStep(S_COLLECTION);
  });
  if (id('cfgToDetails')) id('cfgToDetails').addEventListener('click', function () {
    // Ensure the current door is part of the request if the cart is empty.
    if (!cart.length && !addCurrentToCart()) return;
    showStep(S_DETAILS);
  });

  /* ── dimensions + quantity ── */
  var $w = id('cfgWidth'), $h = id('cfgHeight'), $wv = id('cfgWidthVal'), $hv = id('cfgHeightVal'), dimTimer;
  function onDim() {
    state.width_mm = parseInt($w.value,10); state.height_mm = parseInt($h.value,10);
    if ($wv) $wv.textContent = (state.width_mm/10)+' cm';
    if ($hv) $hv.textContent = (state.height_mm/10)+' cm';
    setSummary(); clearTimeout(dimTimer); dimTimer = setTimeout(requestPrice, 180);
  }
  if ($w) $w.addEventListener('input', onDim);
  if ($h) $h.addEventListener('input', onDim);

  var $qty = id('cfgQty');
  function setQty(v) { v = Math.max(1, Math.min(999, parseInt(v,10) || 1)); state.quantity = v; if ($qty) $qty.value = v; }
  if ($qty) $qty.addEventListener('input', function(){ setQty($qty.value); });
  if (id('cfgQtyMinus')) id('cfgQtyMinus').addEventListener('click', function(){ setQty(state.quantity - 1); });
  if (id('cfgQtyPlus'))  id('cfgQtyPlus').addEventListener('click',  function(){ setQty(state.quantity + 1); });

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
    if (p.available === false) return 'Non disponible';
    if (p.total_price_fmt) return p.total_price_fmt;
    if (p.total_price != null) return Number(p.total_price).toLocaleString('en-US') + ' ' + (p.currency || 'DZD');
    return '—';
  }
  var priceTimer;
  function requestPrice() {
    if (!state.collection_id || !state.usage_id || !state.construction_id) {
      lastPricing = null; paintPrice('Configure to see price'); return;
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
  function paintPrice(label) { if ($sumPrice) $sumPrice.textContent = label; if ($fabPrice) $fabPrice.textContent = label; }

  /* ── save ── */
  var $save = id('cfgSave');
  if ($save) $save.addEventListener('click', function () {
    if (!state.collection_id) { nudge(); return; }
    fetch(CFG.saveUrl, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-Token':CFG.csrf}, body:JSON.stringify({ config: priceConfig(), name: (state.collection_name||'PORTES')+' '+(state.color_name||'')+' Door' }) })
      .then(function (r) { return r.json(); })
      .then(function (d) { if (d && d.success && d.url) { $save.textContent='Saved ✓'; history.replaceState(null,'',d.url); setTimeout(function(){$save.textContent='Save Configuration';},2200); } })
      .catch(function(){});
  });

  /* ── quote ── */
  var $quote = id('cfgQuote');
  if ($quote) $quote.addEventListener('click', function () { showStep(S_REVIEW); });
  function val(x){ var el=id(x); return el ? el.value.trim() : ''; }
  function showError(m){ var err=id('cfgFormError'); if(err){ err.hidden=false; err.textContent=m; } }
  function firstError(o){ for (var k in o){ if (o.hasOwnProperty(k)) return o[k]; } return 'Please review your details.'; }

  // Doors to submit: the cart, plus the current door if not yet added.
  function itemsToSubmit() {
    var items = cart.map(function (it) {
      return { quantity: it.quantity, config: {
        collection_id: it.collection_id, color_id: it.color_id,
        door_type_id: it.usage_id, construction_type_id: it.construction_id,
        product_id: it.product_id, width_mm: it.width_mm, height_mm: it.height_mm,
      }};
    });
    if (!items.length && state.collection_id) {
      items.push({ quantity: state.quantity, config: priceConfig() });
    }
    return items;
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
    // Build the line list from the cart (or the single current door).
    var lines = cart.length ? cart : [snapshotCurrent()];
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
    var overlay = id('cfgConfirm'); if (overlay) overlay.hidden = false;
    document.body.style.overflow = 'hidden';
  }
  if (id('cfgPrint')) id('cfgPrint').addEventListener('click', function () { window.print(); });
  if (id('cfgAnother')) id('cfgAnother').addEventListener('click', function () { window.location.href = '/door-showroom/configure'; });

  /* ── mobile summary ── */
  var $fab = id('cfgSummaryFab'), $summary = id('cfgSummary');
  if ($fab && $summary) $fab.addEventListener('click', function () { $summary.classList.toggle('is-open'); });

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
  setRender(); setSummary();
  showStep(state.color_id ? S_USAGE : (state.collection_id ? S_COLOR : S_COLLECTION));
  requestPrice();
}());
