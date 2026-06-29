(function () {
  var modal = document.getElementById('deleteModal');
  if (!modal) return;

  // Config passed via data-attributes (CSP-safe — no inline script).
  var BASE = modal.getAttribute('data-base') || '';
  var csrf = modal.getAttribute('data-csrf') || '';

  var itemName = document.getElementById('modalItemName');
  var btn = document.getElementById('modalConfirm');
  var label = document.getElementById('modalBtnLabel');
  var cancel = document.getElementById('modalCancel');
  var deleteId = null;

  function xhr(method, url, cb) {
    var x = new XMLHttpRequest();
    x.open(method, url, true);
    x.setRequestHeader('Content-Type', 'application/json');
    x.setRequestHeader('X-CSRF-Token', csrf);
    x.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    x.onreadystatechange = function () {
      if (x.readyState !== 4) return;
      try { cb(JSON.parse(x.responseText)); }
      catch (e) { cb({ success: false, message: 'Unexpected error.' }); }
    };
    x.send(JSON.stringify({ _csrf: csrf }));
  }

  function showFlash(msg, type) {
    var el = document.createElement('div');
    el.className = 'flash flash--' + type;
    el.innerHTML = '<span></span><button class="flash-close">&times;</button>';
    el.querySelector('span').textContent = msg;
    el.querySelector('.flash-close').addEventListener('click', function () { el.remove(); });
    var content = document.querySelector('.admin-content');
    var panel = document.querySelector('.table-panel');
    if (content && panel) content.insertBefore(el, panel);
    setTimeout(function () { if (el.parentElement) el.remove(); }, 5000);
  }

  document.querySelectorAll('.action-btn--delete').forEach(function (b) {
    b.addEventListener('click', function () {
      deleteId = b.dataset.id;
      if (itemName) itemName.textContent = b.dataset.name || '';
      modal.hidden = false;
    });
  });

  if (cancel) cancel.addEventListener('click', function () { modal.hidden = true; deleteId = null; });
  modal.addEventListener('click', function (e) {
    if (e.target === modal) { modal.hidden = true; deleteId = null; }
  });

  if (btn) btn.addEventListener('click', function () {
    if (!deleteId) return;
    if (label) label.textContent = 'Deleting…';
    btn.disabled = true;
    xhr('POST', BASE + '/' + deleteId + '/delete', function (data) {
      if (data.success) {
        var row = document.getElementById('row-' + deleteId);
        if (row) { row.style.transition = 'opacity .25s'; row.style.opacity = '0'; setTimeout(function () { row.remove(); }, 260); }
        showFlash(data.message, 'success');
      } else {
        showFlash(data.message || 'Delete failed.', 'error');
      }
      modal.hidden = true;
      deleteId = null;
      if (label) label.textContent = 'Delete';
      btn.disabled = false;
    });
  });

  document.querySelectorAll('.toggle-status').forEach(function (b) {
    b.addEventListener('click', function () {
      var id = b.dataset.id;
      xhr('POST', BASE + '/' + id + '/toggle', function (data) {
        if (data.success) {
          var dot = b.querySelector('.status-dot');
          if (dot) dot.className = 'status-dot status-dot--' + (data.is_active ? 'on' : 'off');
          var lbl = document.getElementById('status-label-' + id);
          if (lbl) lbl.textContent = data.label;
        }
      });
    });
  });

  var si = document.getElementById('searchInput'), t;
  if (si) si.addEventListener('input', function () {
    clearTimeout(t);
    t = setTimeout(function () { document.getElementById('searchForm').submit(); }, 420);
  });
}());
