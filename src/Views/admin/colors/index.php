<?php
$e      = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$q      = $search ?? '';
$status = $filter ?? '';
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Colors</h1>
    <p class="page-sub">Grouped by collection · <?= number_format($total) ?> <?= $total === 1 ? 'color' : 'colors' ?> total</p>
  </div>
  <div style="display:flex;gap:8px;align-items:center">
    <a href="/door-showroom/admin/colors/create" class="btn btn-primary">
      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
      Add Color
    </a>
  </div>
</div>

<?php if ($flash): ?>
  <div class="flash flash--success">
    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    <span><?= $e($flash) ?></span>
    <button class="flash-close" onclick="this.parentElement.remove()">
      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
    </button>
  </div>
<?php endif; ?>

<div class="table-panel">

  <div class="table-toolbar">
    <form method="GET" action="/door-showroom/admin/colors" class="search-form" id="searchForm">
      <div class="search-wrap">
        <svg class="search-icon" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
        </svg>
        <input type="search" name="q" value="<?= $e($q) ?>" placeholder="Search colors…" class="search-input" id="searchInput" autocomplete="off" />
        <?php if ($q !== ''): ?>
          <a href="/door-showroom/admin/colors" class="search-clear">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
          </a>
        <?php endif; ?>
      </div>
      <div class="filter-tabs">
        <a href="?q=<?= urlencode($q) ?>" class="filter-tab <?= $status === '' ? 'is-active' : '' ?>">All</a>
        <a href="?q=<?= urlencode($q) ?>&status=active" class="filter-tab <?= $status === 'active' ? 'is-active' : '' ?>">Active</a>
        <a href="?q=<?= urlencode($q) ?>&status=inactive" class="filter-tab <?= $status === 'inactive' ? 'is-active' : '' ?>">Inactive</a>
      </div>
    </form>
    <span class="table-count"><?= number_format($total) ?> result<?= $total !== 1 ? 's' : '' ?></span>
  </div>

  <?php if (empty($colors)): ?>
    <div class="table-wrap">
      <div class="table-empty">
        <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="24" cy="24" r="18"/><circle cx="24" cy="24" r="8"/></svg>
        <p>No colors found.</p>
        <a href="/door-showroom/admin/colors/create" class="btn btn-primary" style="margin-top:8px">Add first color</a>
      </div>
    </div>
  <?php else: ?>
    <?php foreach ($grouped as $cid => $group): if (empty($group['colors']) && $q === '' && $status === '') : ?>
      <!-- empty collection: still show the header so the hierarchy is obvious -->
      <div class="color-group is-empty">
        <div class="color-group-head">
          <h2 class="color-group-title"><?= $e($group['name']) ?></h2>
          <span class="color-group-count">0 colors</span>
          <a href="/door-showroom/admin/colors/create" class="color-group-add">+ Add color</a>
        </div>
        <p class="td-muted" style="padding:14px 18px">No colors in this collection yet.</p>
      </div>
    <?php elseif (!empty($group['colors'])): ?>
      <div class="color-group">
        <div class="color-group-head">
          <h2 class="color-group-title"><?= $e($group['name']) ?></h2>
          <span class="color-group-count"><?= count($group['colors']) ?> color<?= count($group['colors']) === 1 ? '' : 's' ?></span>
          <a href="/door-showroom/admin/colors/create" class="color-group-add">+ Add color</a>
        </div>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th style="width:56px">Swatch</th>
                <th>Name</th>
                <th style="width:120px">Hex</th>
                <th style="width:90px">Products</th>
                <th style="width:80px">Order</th>
                <th style="width:100px">Status</th>
                <th style="width:110px"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($group['colors'] as $color): ?>
                <tr id="row-<?= (int)$color['id'] ?>">
                  <td>
                    <?php if ($color['hex']): ?>
                      <span class="color-swatch" style="background:<?= $e($color['hex']) ?>;display:inline-block;width:28px;height:28px;border-radius:6px;border:1px solid rgba(0,0,0,.12);" title="<?= $e($color['hex']) ?>"></span>
                    <?php else: ?>
                      <span class="td-muted" style="display:inline-block;width:28px;height:28px;border-radius:6px;background:#f3f3f3;border:1px solid #e0e0e0;"></span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <strong><?= $e($color['name']) ?></strong>
                    <?php if ($color['description']): ?>
                      <div class="td-muted td-sm"><?= $e(substr($color['description'], 0, 60)) ?><?= strlen($color['description']) > 60 ? '…' : '' ?></div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($color['hex']): ?>
                      <code class="td-slug"><?= $e($color['hex']) ?></code>
                    <?php else: ?>
                      <span class="td-muted">—</span>
                    <?php endif; ?>
                  </td>
                  <td class="td-center"><?= (int)$color['product_count'] ?></td>
                  <td class="td-center"><?= (int)$color['display_order'] ?></td>
                  <td class="td-center">
                    <button class="toggle-status" data-id="<?= (int)$color['id'] ?>" title="Toggle status">
                      <span class="status-dot status-dot--<?= $color['is_active'] ? 'on' : 'off' ?>"></span>
                      <span class="status-label" id="status-label-<?= (int)$color['id'] ?>"><?= $color['is_active'] ? 'Active' : 'Inactive' ?></span>
                    </button>
                  </td>
                  <td class="td-actions">
                    <a href="/door-showroom/admin/colors/<?= (int)$color['id'] ?>/edit" class="action-btn action-btn--edit" title="Edit">
                      <svg viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                    </a>
                    <button class="action-btn action-btn--delete" data-id="<?= (int)$color['id'] ?>" data-name="<?= $e($color['name']) ?>" title="Delete">
                      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; endforeach; ?>
  <?php endif; ?>

</div>

<div class="modal-backdrop" id="deleteModal" hidden>
  <div class="modal" role="dialog" aria-modal="true">
    <div class="modal-icon modal-icon--danger">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
      </svg>
    </div>
    <h3>Delete Color</h3>
    <p>Are you sure you want to delete <strong id="modalItemName"></strong>? This cannot be undone.</p>
    <div class="modal-actions">
      <button class="btn btn-outline" id="modalCancel">Cancel</button>
      <button class="btn btn-danger" id="modalConfirm">
        <span id="modalBtnLabel">Delete</span>
        <span class="btn-spinner-sm" id="modalSpinner" hidden></span>
      </button>
    </div>
  </div>
</div>

<script>
(function () {
  var csrf     = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  var modal    = document.getElementById('deleteModal');
  var itemName = document.getElementById('modalItemName');
  var btn      = document.getElementById('modalConfirm');
  var label    = document.getElementById('modalBtnLabel');
  var spin     = document.getElementById('modalSpinner');
  var cancelBtn = document.getElementById('modalCancel');
  var deleteId = null;

  document.querySelectorAll('.action-btn--delete').forEach(function (b) {
    b.addEventListener('click', function () { deleteId = b.dataset.id; itemName.textContent = b.dataset.name; modal.hidden = false; });
  });

  cancelBtn.addEventListener('click', function () { modal.hidden = true; deleteId = null; });
  modal.addEventListener('click', function (e) { if (e.target === modal) { modal.hidden = true; deleteId = null; } });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) { modal.hidden = true; deleteId = null; } });

  btn.addEventListener('click', function () {
    if (!deleteId) return;
    label.textContent = 'Deleting…'; spin.hidden = false; btn.disabled = true;
    xhr('POST', '/door-showroom/admin/colors/' + deleteId + '/delete', function (data) {
      if (data.success) {
        var row = document.getElementById('row-' + deleteId);
        if (row) { row.style.opacity = '0'; row.style.transition = 'opacity 0.25s'; setTimeout(function () { row.remove(); }, 260); }
        showFlash(data.message, 'success');
      } else {
        showFlash(data.message || 'Delete failed.', 'error');
      }
      modal.hidden = true; deleteId = null;
      label.textContent = 'Delete'; spin.hidden = true; btn.disabled = false;
    });
  });

  document.querySelectorAll('.toggle-status').forEach(function (b) {
    b.addEventListener('click', function () {
      var id = b.dataset.id;
      xhr('POST', '/door-showroom/admin/colors/' + id + '/toggle', function (data) {
        if (data.success) {
          var dot = b.querySelector('.status-dot');
          var lbl = document.getElementById('status-label-' + id);
          dot.className = 'status-dot status-dot--' + (data.is_active ? 'on' : 'off');
          lbl.textContent = data.label;
        }
      });
    });
  });

  var searchInput = document.getElementById('searchInput');
  var timer;
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      clearTimeout(timer);
      timer = setTimeout(function () { document.getElementById('searchForm').submit(); }, 420);
    });
  }

  function showFlash(msg, type) {
    var el = document.createElement('div');
    el.className = 'flash flash--' + type;
    el.innerHTML = '<span>' + msg + '</span><button class="flash-close" onclick="this.parentElement.remove()"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button>';
    document.querySelector('.admin-content').insertBefore(el, document.querySelector('.table-panel'));
    setTimeout(function () { if (el.parentElement) el.remove(); }, 5000);
  }

  function xhr(method, url, cb) {
    var x = new XMLHttpRequest();
    x.open(method, url, true);
    x.setRequestHeader('Content-Type', 'application/json');
    x.setRequestHeader('X-CSRF-Token', csrf);
    x.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    x.onreadystatechange = function () {
      if (x.readyState !== 4) return;
      try { cb(JSON.parse(x.responseText)); } catch(e) { cb({success:false, message:'Unexpected error.'}); }
    };
    x.send(JSON.stringify({ _csrf: csrf }));
  }
}());
</script>
