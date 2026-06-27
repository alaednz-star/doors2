<?php
$e = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$q = $search ?? '';
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Categories</h1>
    <p class="page-sub"><?= number_format($total) ?> <?= $total === 1 ? 'category' : 'categories' ?> total</p>
  </div>
  <a href="/door-showroom/admin/categories/create" class="btn btn-primary">
    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
    Add Category
  </a>
</div>

<?php if ($flash): ?>
  <div class="flash flash--success" id="flashMsg">
    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    <span><?= $e($flash) ?></span>
    <button class="flash-close" onclick="this.parentElement.remove()">
      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
    </button>
  </div>
<?php endif; ?>

<div class="table-panel">

  <!-- Search bar -->
  <div class="table-toolbar">
    <form method="GET" action="/door-showroom/admin/categories" class="search-form" id="searchForm">
      <div class="search-wrap">
        <svg class="search-icon" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
        </svg>
        <input
          type="search"
          name="q"
          value="<?= $e($q) ?>"
          placeholder="Search categories…"
          class="search-input"
          id="searchInput"
          autocomplete="off"
        />
        <?php if ($q !== ''): ?>
          <a href="/door-showroom/admin/categories" class="search-clear" title="Clear search">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
          </a>
        <?php endif; ?>
      </div>
    </form>
    <span class="table-count"><?= number_format($total) ?> result<?= $total !== 1 ? 's' : '' ?></span>
  </div>

  <!-- Table -->
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th style="width:40px">#</th>
          <th>Name</th>
          <th>Slug</th>
          <th>Description</th>
          <th style="width:80px">Order</th>
          <th style="width:90px">Status</th>
          <th style="width:130px">Created</th>
          <th style="width:110px"></th>
        </tr>
      </thead>
      <tbody id="categoryTableBody">
        <?php if (empty($categories)): ?>
          <tr>
            <td colspan="8">
              <div class="table-empty">
                <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M24 4C13 4 4 13 4 24s9 20 20 20 20-9 20-20S35 4 24 4z"/>
                  <path d="M16 20h16M16 28h8"/>
                </svg>
                <?php if ($q !== ''): ?>
                  <p>No categories match "<?= $e($q) ?>".</p>
                  <a href="/door-showroom/admin/categories" class="btn btn-outline" style="margin-top:8px">Clear search</a>
                <?php else: ?>
                  <p>No categories yet.</p>
                  <a href="/door-showroom/admin/categories/create" class="btn btn-primary" style="margin-top:8px">Add your first category</a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($categories as $i => $cat): ?>
            <tr id="cat-row-<?= (int)$cat['id'] ?>">
              <td class="td-muted"><?= (int)$cat['id'] ?></td>
              <td>
                <strong class="td-name"><?= $e($cat['name']) ?></strong>
              </td>
              <td>
                <code class="td-slug"><?= $e($cat['slug']) ?></code>
              </td>
              <td class="td-desc">
                <?php if ($cat['description']): ?>
                  <span title="<?= $e($cat['description']) ?>"><?= $e(substr($cat['description'], 0, 60)) ?><?= strlen($cat['description']) > 60 ? '…' : '' ?></span>
                <?php else: ?>
                  <span class="td-muted">—</span>
                <?php endif; ?>
              </td>
              <td class="td-center"><?= (int)$cat['display_order'] ?></td>
              <td class="td-center">
                <button
                  class="toggle-status"
                  data-id="<?= (int)$cat['id'] ?>"
                  data-active="<?= (int)$cat['is_active'] ?>"
                  title="Click to toggle status"
                >
                  <span class="status-dot status-dot--<?= $cat['is_active'] ? 'on' : 'off' ?>"></span>
                  <span class="status-label" id="status-label-<?= (int)$cat['id'] ?>"><?= $cat['is_active'] ? 'Active' : 'Inactive' ?></span>
                </button>
              </td>
              <td class="td-muted td-sm">
                <?= date('d M Y', strtotime($cat['created_at'])) ?>
              </td>
              <td class="td-actions">
                <a href="/door-showroom/admin/categories/<?= (int)$cat['id'] ?>/edit" class="action-btn action-btn--edit" title="Edit">
                  <svg viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                </a>
                <button
                  class="action-btn action-btn--delete"
                  data-id="<?= (int)$cat['id'] ?>"
                  data-name="<?= $e($cat['name']) ?>"
                  title="Delete"
                >
                  <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php if ($page > 1): ?>
        <a href="?<?= http_build_query(['q' => $q, 'page' => $page - 1]) ?>" class="page-btn page-btn--nav" title="Previous">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
        </a>
      <?php endif; ?>

      <?php
        $start = max(1, $page - 2);
        $end   = min($totalPages, $page + 2);
        if ($start > 1): ?>
          <a href="?<?= http_build_query(['q' => $q, 'page' => 1]) ?>" class="page-btn">1</a>
          <?php if ($start > 2): ?><span class="page-ellipsis">…</span><?php endif; ?>
      <?php endif; ?>

      <?php for ($p = $start; $p <= $end; $p++): ?>
        <a href="?<?= http_build_query(['q' => $q, 'page' => $p]) ?>"
           class="page-btn <?= $p === $page ? 'is-current' : '' ?>">
          <?= $p ?>
        </a>
      <?php endfor; ?>

      <?php if ($end < $totalPages): ?>
        <?php if ($end < $totalPages - 1): ?><span class="page-ellipsis">…</span><?php endif; ?>
        <a href="?<?= http_build_query(['q' => $q, 'page' => $totalPages]) ?>" class="page-btn"><?= $totalPages ?></a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a href="?<?= http_build_query(['q' => $q, 'page' => $page + 1]) ?>" class="page-btn page-btn--nav" title="Next">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
        </a>
      <?php endif; ?>

      <span class="page-info">Page <?= $page ?> of <?= $totalPages ?></span>
    </div>
  <?php endif; ?>

</div>

<!-- Delete confirmation modal -->
<div class="modal-backdrop" id="deleteModal" hidden>
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-icon modal-icon--danger">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
      </svg>
    </div>
    <h3 id="modalTitle">Delete Category</h3>
    <p id="modalBody">Are you sure you want to delete <strong id="modalCatName"></strong>? This action cannot be undone.</p>
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
  var csrf       = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  var modal      = document.getElementById('deleteModal');
  var modalName  = document.getElementById('modalCatName');
  var modalBtn   = document.getElementById('modalConfirm');
  var modalLabel = document.getElementById('modalBtnLabel');
  var modalSpin  = document.getElementById('modalSpinner');
  var cancelBtn  = document.getElementById('modalCancel');
  var deleteId   = null;

  /* ── Delete ── */
  document.querySelectorAll('.action-btn--delete').forEach(function (btn) {
    btn.addEventListener('click', function () {
      deleteId = btn.dataset.id;
      modalName.textContent = btn.dataset.name;
      modal.hidden = false;
    });
  });

  cancelBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !modal.hidden) closeModal();
  });

  function closeModal() {
    modal.hidden = true;
    deleteId = null;
  }

  modalBtn.addEventListener('click', function () {
    if (!deleteId) return;
    modalLabel.textContent = 'Deleting…';
    modalSpin.hidden = false;
    modalBtn.disabled = true;

    xhr('POST', '/door-showroom/admin/categories/' + deleteId + '/delete', function (data) {
      if (data.success) {
        var row = document.getElementById('cat-row-' + deleteId);
        if (row) {
          row.style.opacity = '0';
          row.style.transition = 'opacity 0.25s';
          setTimeout(function () { row.remove(); }, 260);
        }
        showFlash(data.message, 'success');
      } else {
        showFlash(data.message || 'Delete failed.', 'error');
      }
      closeModal();
      modalLabel.textContent = 'Delete';
      modalSpin.hidden = true;
      modalBtn.disabled = false;
    });
  });

  /* ── Toggle status ── */
  document.querySelectorAll('.toggle-status').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = btn.dataset.id;
      xhr('POST', '/door-showroom/admin/categories/' + id + '/toggle', function (data) {
        if (data.success) {
          var dot   = btn.querySelector('.status-dot');
          var label = document.getElementById('status-label-' + id);
          btn.dataset.active = data.is_active;
          dot.className   = 'status-dot status-dot--' + (data.is_active ? 'on' : 'off');
          label.textContent = data.label;
        }
      });
    });
  });

  /* ── Search on type (debounced) ── */
  var searchInput = document.getElementById('searchInput');
  var timer;
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      clearTimeout(timer);
      timer = setTimeout(function () {
        document.getElementById('searchForm').submit();
      }, 420);
    });
  }

  /* ── Flash ── */
  function showFlash(msg, type) {
    var el = document.createElement('div');
    el.className = 'flash flash--' + type;
    el.innerHTML = '<span>' + msg + '</span><button class="flash-close" onclick="this.parentElement.remove()"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button>';
    document.querySelector('.admin-content').insertBefore(el, document.querySelector('.table-panel'));
    setTimeout(function () { if (el.parentElement) el.remove(); }, 5000);
  }

  /* ── XHR helper ── */
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
