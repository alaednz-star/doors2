(function () {
  'use strict';

  var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

  /* ─── Admin rule list: toggle active state ─── */
  document.addEventListener('click', function (e) {
    var toggleBtn = e.target.closest('.js-toggle-rule');
    if (toggleBtn) {
      e.preventDefault();
      toggleRule(toggleBtn);
      return;
    }

    var deleteBtn = e.target.closest('.js-delete-rule');
    if (deleteBtn) {
      e.preventDefault();
      openDeleteModal(deleteBtn);
      return;
    }
  });

  function toggleRule(btn) {
    var id   = btn.dataset.id;
    var tok  = btn.dataset.csrf || csrf;
    var dot  = btn.querySelector('.status-dot');
    var lbl  = btn.querySelector('.js-toggle-label');
    btn.disabled = true;

    xhr('POST', '/door-showroom/admin/pricing/' + id + '/toggle', { _csrf: tok }, function (res) {
      btn.disabled = false;
      if (!res.success) return;
      dot.className = 'status-dot ' + (res.is_active ? 'status-dot--on' : 'status-dot--off');
      lbl.textContent = res.label;
    }, function () { btn.disabled = false; });
  }

  /* ─── Delete modal ─── */
  var deleteModal     = document.getElementById('deleteModal');
  var deleteRuleName  = document.getElementById('deleteRuleName');
  var deleteCancelBtn = document.getElementById('deleteCancelBtn');
  var deleteConfirmBtn= document.getElementById('deleteConfirmBtn');
  var pendingDelete   = null;

  function openDeleteModal(btn) {
    pendingDelete = { id: btn.dataset.id, csrf: btn.dataset.csrf || csrf };
    if (deleteRuleName) deleteRuleName.textContent = '"' + btn.dataset.name + '"';
    if (deleteModal)    deleteModal.hidden = false;
  }

  if (deleteCancelBtn) {
    deleteCancelBtn.addEventListener('click', function () {
      deleteModal.hidden = true;
      pendingDelete = null;
    });
  }

  if (deleteConfirmBtn) {
    deleteConfirmBtn.addEventListener('click', function () {
      if (!pendingDelete) return;
      deleteConfirmBtn.disabled = true;
      deleteConfirmBtn.innerHTML = '<span class="btn-spinner-sm"></span> Deleting…';

      xhr('POST', '/door-showroom/admin/pricing/' + pendingDelete.id + '/delete',
        { _csrf: pendingDelete.csrf },
        function (res) {
          if (res.success) {
            window.location.reload();
          } else {
            deleteConfirmBtn.disabled = false;
            deleteConfirmBtn.textContent = 'Delete';
            deleteModal.hidden = true;
          }
        },
        function () {
          deleteConfirmBtn.disabled = false;
          deleteConfirmBtn.textContent = 'Delete';
        }
      );
    });
  }

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && deleteModal && !deleteModal.hidden) {
      deleteModal.hidden = true;
      pendingDelete = null;
    }
  });

  /* ─── Live price calculator widget ─── */
  var calcWidget = document.getElementById('priceCalculator');
  if (calcWidget) {
    initCalculator(calcWidget);
  }

  function initCalculator(widget) {
    var resultBox   = widget.querySelector('.calc-result');
    var totalEl     = widget.querySelector('.calc-total');
    var breakdownEl = widget.querySelector('.calc-breakdown');
    var calcBtn     = widget.querySelector('.calc-btn');
    var inputs      = widget.querySelectorAll('select, input[type="number"], input[type="checkbox"]');
    var debounceTimer;

    function gather() {
      var data = { feature_ids: [] };

      widget.querySelectorAll('[data-calc-field]').forEach(function (el) {
        var key = el.dataset.calcField;
        if (el.type === 'checkbox') {
          if (el.checked) data.feature_ids.push(parseInt(el.value, 10));
        } else {
          var val = el.value.trim();
          if (val !== '') data[key] = val;
        }
      });

      return data;
    }

    function calculate() {
      var data = gather();
      if (calcBtn) {
        calcBtn.disabled = true;
        calcBtn.innerHTML = '<span class="btn-spinner-sm"></span> Calculating…';
      }

      xhr('POST', '/door-showroom/admin/pricing/calculate', data, function (res) {
        if (calcBtn) {
          calcBtn.disabled = false;
          calcBtn.textContent = 'Calculate Price';
        }

        if (!res.success) return;

        var d = res.data;
        if (totalEl) totalEl.textContent = formatPrice(d.total_price) + ' ' + d.currency;
        if (resultBox) resultBox.hidden = false;

        if (breakdownEl) {
          var rows = '';

          d.rules_applied.forEach(function (r) {
            rows += '<div class="calc-breakdown-row">'
              + '<span>' + escHtml(r.name) + '</span>'
              + '<span>' + formatPrice(r.contribution) + '</span>'
              + '</div>';
          });

          d.features_applied.forEach(function (f) {
            rows += '<div class="calc-breakdown-row calc-breakdown-row--feature">'
              + '<span>' + escHtml(f.name) + '</span>'
              + '<span>+' + formatPrice(f.price) + '</span>'
              + '</div>';
          });

          if (d.options_price > 0) {
            rows += '<div class="calc-breakdown-row calc-breakdown-row--sub">'
              + '<span>Options subtotal</span>'
              + '<span>' + formatPrice(d.options_price) + '</span>'
              + '</div>';
          }

          rows += '<div class="calc-breakdown-row calc-breakdown-row--total">'
            + '<span>Total</span>'
            + '<span>' + formatPrice(d.total_price) + ' ' + escHtml(d.currency) + '</span>'
            + '</div>';

          breakdownEl.innerHTML = rows;
        }
      }, function () {
        if (calcBtn) {
          calcBtn.disabled = false;
          calcBtn.textContent = 'Calculate Price';
        }
      });
    }

    inputs.forEach(function (el) {
      el.addEventListener('change', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(calculate, 350);
      });
    });

    if (calcBtn) {
      calcBtn.addEventListener('click', function (e) {
        e.preventDefault();
        calculate();
      });
    }
  }

  /* ─── Utilities ─── */
  function xhr(method, url, data, onSuccess, onError) {
    var req  = new XMLHttpRequest();
    req.open(method, url, true);
    req.setRequestHeader('X-CSRF-Token', csrf);
    req.setRequestHeader('Content-Type', 'application/json');
    req.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    req.onload = function () {
      try {
        var res = JSON.parse(req.responseText);
        onSuccess(res);
      } catch (err) {
        if (onError) onError(err);
      }
    };

    req.onerror = function () { if (onError) onError(); };
    req.send(JSON.stringify(data));
  }

  function formatPrice(n) {
    return Number(n).toLocaleString('fr-DZ', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
}());
