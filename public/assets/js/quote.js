(function () {
  'use strict';

  var DATA = {};
  try { DATA = JSON.parse(document.getElementById('qData').textContent || '{}'); } catch (e) { DATA = {}; }

  // The configurator stores the configuration here before sending the user over.
  var config = null;
  try { config = JSON.parse(localStorage.getItem('portes_config') || 'null'); } catch (e) {}

  var $main  = document.querySelector('.q-main');
  var $empty = document.getElementById('qEmpty');
  var $steps = document.getElementById('qSteps');

  // No configuration → show the empty state, hide the flow.
  if (!config || !config.collection_id) {
    if ($main) $main.hidden = true;
    if ($steps) $steps.style.display = 'none';
    if ($empty) $empty.hidden = false;
    return;
  }

  function byId(list, id) {
    list = list || [];
    for (var i = 0; i < list.length; i++) { if (list[i].id === +id) return list[i]; }
    return null;
  }

  /* ── render the review summary + preview ── */
  var coll = byId(DATA.collections, config.collection_id);
  var mat  = byId(DATA.materials, config.material_id);
  var col  = byId(DATA.colors, config.color_id);
  var dt   = byId(DATA.doorTypes, config.door_type_id);

  function setSpec(key, val) {
    var el = document.querySelector('[data-spec="' + key + '"]');
    if (el) el.textContent = val || '—';
  }
  var room = byId(DATA.roomTypes, config.room_type_id);
  setSpec('room', room && room.name);
  setSpec('collection', coll && coll.name);
  setSpec('material', mat && mat.name);
  setSpec('color', col && col.name);
  setSpec('doorType', dt && dt.name);
  var dim = (config.width_mm && config.height_mm) ? ((config.width_mm / 10) + ' × ' + (config.height_mm / 10) + ' cm') : '—';
  setSpec('dim', dim);
  var $dim = document.getElementById('qDim'); if ($dim) $dim.textContent = dim;

  // optional features
  var featIds = config.feature_ids || [];
  if (featIds.length && DATA.features) {
    var names = featIds.map(function (id) { var f = byId(DATA.features, id); return f ? f.name : null; }).filter(Boolean);
    if (names.length) {
      setSpec('features', names.join(', '));
      var row = document.querySelector('[data-features-row]'); if (row) row.hidden = false;
    }
  }

  // preview image (collection door, tinted by colour like the configurator)
  var $render = document.getElementById('qRender');
  var $tint   = document.getElementById('qRenderTint');
  if ($render && coll && coll.img) {
    $render.style.backgroundImage = "url('" + coll.img + "')";
    if ($tint && col && col.hex) {
      $tint.style.maskImage = "url('" + coll.img + "')";
      $tint.style.webkitMaskImage = "url('" + coll.img + "')";
      $tint.style.background = col.hex;
      $tint.style.opacity = '0.5';
    }
  }

  /* ── live price ── */
  var $price = document.getElementById('qPrice');
  function loadPrice() {
    fetch(DATA.priceUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': DATA.csrf },
      body: JSON.stringify(config),
    })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (d && d.success && d.pricing && $price) {
          $price.textContent = d.pricing.total_price_fmt ||
            (Number(d.pricing.total_price).toLocaleString('en-US') + ' ' + (d.pricing.currency || 'DZD'));
        }
      })
      .catch(function () {});
  }
  loadPrice();

  /* ── step navigation ── */
  var panels = Array.prototype.slice.call(document.querySelectorAll('.q-panel'));
  var stepEls = Array.prototype.slice.call(document.querySelectorAll('.q-step'));
  function goStep(n) {
    panels.forEach(function (p) { p.classList.toggle('is-active', +p.dataset.panel === n); });
    stepEls.forEach(function (s, i) {
      s.classList.toggle('is-active', i === n);
      s.classList.toggle('is-done', i < n);
    });
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  var $toDetails = document.getElementById('qToDetails');
  if ($toDetails) $toDetails.addEventListener('click', function () { goStep(1); });
  var $backReview = document.getElementById('qBackToReview');
  if ($backReview) $backReview.addEventListener('click', function () { goStep(0); });

  /* ── form submit ── */
  var $form = document.getElementById('qForm');
  var $submit = document.getElementById('qSubmit');
  var $formError = document.getElementById('qFormError');

  function clearErrors() {
    document.querySelectorAll('.q-err').forEach(function (e) { e.textContent = ''; });
    document.querySelectorAll('.q-field.has-error').forEach(function (f) { f.classList.remove('has-error'); });
    if ($formError) $formError.hidden = true;
  }
  function showErrors(errors) {
    Object.keys(errors).forEach(function (key) {
      var span = document.querySelector('[data-err="' + key + '"]');
      if (span) { span.textContent = errors[key]; var f = span.closest('.q-field'); if (f) f.classList.add('has-error'); }
    });
    var first = document.querySelector('.q-field.has-error');
    if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  if ($form) $form.addEventListener('submit', function (e) {
    e.preventDefault();
    clearErrors();

    var payload = {
      config: config,
      full_name:    document.getElementById('qName').value.trim(),
      email:        document.getElementById('qEmail').value.trim(),
      phone:        document.getElementById('qPhone').value.trim(),
      company:      document.getElementById('qCompany').value.trim(),
      country:      document.getElementById('qCountry').value.trim(),
      city:         document.getElementById('qCity').value.trim(),
      install_date: document.getElementById('qInstallDate').value,
      quantity:     document.getElementById('qQuantity').value,
      notes:        document.getElementById('qNotes').value.trim(),
      company_website: (document.querySelector('[name=company_website]') || {}).value || '',
    };

    $submit.disabled = true;
    $submit.textContent = 'Submitting…';

    fetch(DATA.quoteUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': DATA.csrf },
      body: JSON.stringify(payload),
    })
      .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
      .then(function (res) {
        $submit.disabled = false;
        $submit.innerHTML = 'Request My Quote';
        if (res.d && res.d.success) {
          var ref = document.getElementById('qRefOut');
          if (ref) ref.textContent = res.d.reference;
          lastSubmission = { reference: res.d.reference, customer: payload, pricing: res.d.pricing };
          try { localStorage.removeItem('portes_config'); } catch (e) {}
          goStep(2);
        } else if (res.d && res.d.errors) {
          showErrors(res.d.errors);
        } else if ($formError) {
          $formError.hidden = false;
          $formError.textContent = (res.d && res.d.message) || 'Something went wrong. Please try again.';
        }
      })
      .catch(function () {
        $submit.disabled = false;
        $submit.innerHTML = 'Request My Quote';
        if ($formError) { $formError.hidden = false; $formError.textContent = 'Network error. Please try again.'; }
      });
  });

  /* ── download configuration (print-to-PDF of a clean summary) ── */
  var lastSubmission = null;
  var $pdf = document.getElementById('qDownloadPdf');
  if ($pdf) $pdf.addEventListener('click', function () {
    var s = lastSubmission || {};
    var rows = [
      ['Reference', s.reference || ''],
      ['Collection', coll && coll.name],
      ['Colour', col && col.name],
      ['Usage', dt && dt.name], ['Dimensions', dim],
      ['Estimated Price', $price ? $price.textContent : ''],
    ];
    var html = '<html><head><title>PORTES — ' + (s.reference || 'Configuration') + '</title>' +
      '<style>body{font-family:Georgia,serif;color:#1a1a1a;padding:48px;max-width:680px;margin:auto}' +
      'h1{font-weight:300;letter-spacing:.18em;font-size:24px}.sub{letter-spacing:.3em;text-transform:uppercase;font-size:9px;color:#888;margin-bottom:32px}' +
      'table{width:100%;border-collapse:collapse;margin-top:24px}td{padding:12px 0;border-bottom:1px solid #e5e0d8;font-size:14px}' +
      'td:first-child{text-transform:uppercase;letter-spacing:.1em;font-size:10px;color:#999;font-family:Arial}td:last-child{text-align:right}' +
      '.foot{margin-top:40px;font-size:12px;color:#888}</style></head><body>' +
      '<h1>PORTES</h1><div class="sub">Door Configuration</div><table>' +
      rows.map(function (r) { return '<tr><td>' + r[0] + '</td><td>' + (r[1] || '—') + '</td></tr>'; }).join('') +
      '</table><p class="foot">Our specialists will contact you within 24–48 hours with a tailored quotation.<br/>PORTES — Luxury Architectural Doors, Algiers</p></body></html>';
    var w = window.open('', '_blank');
    if (w) { w.document.write(html); w.document.close(); w.focus(); setTimeout(function () { w.print(); }, 300); }
  });

}());
