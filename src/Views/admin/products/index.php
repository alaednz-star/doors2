<?php
$e       = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$q       = $search ?? '';
$filter  = $_GET['filter'] ?? '';
$webBase = '/door-showroom/uploads/products/';
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Products</h1>
    <p class="page-sub"><?= number_format($total) ?> <?= $total === 1 ? 'product' : 'products' ?> total</p>
  </div>
  <a href="/door-showroom/admin/products/create" class="btn btn-primary">
    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
    Add Product
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

  <div class="table-toolbar">
    <form method="GET" action="/door-showroom/admin/products" class="search-form" id="searchForm">
      <input type="hidden" name="filter" value="<?= $e($filter) ?>" />
      <div class="search-wrap">
        <svg class="search-icon" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
        </svg>
        <input
          type="search"
          name="q"
          value="<?= $e($q) ?>"
          placeholder="Search products…"
          class="search-input"
          id="searchInput"
          autocomplete="off"
        />
        <?php if ($q !== ''): ?>
          <a href="/door-showroom/admin/products<?= $filter ? '?filter=' . $e($filter) : '' ?>" class="search-clear" title="Clear">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
          </a>
        <?php endif; ?>
      </div>
    </form>

    <div class="filter-tabs">
      <?php
        $filters = ['all' => 'All', 'active' => 'Active', 'inactive' => 'Inactive', 'featured' => 'Featured'];
        foreach ($filters as $key => $label):
          $href = '/door-showroom/admin/products?' . http_build_query(array_filter(['q' => $q, 'filter' => $key === 'all' ? '' : $key]));
      ?>
        <a href="<?= $href ?>" class="filter-tab <?= ($filter === $key || ($key === 'all' && $filter === '')) ? 'is-active' : '' ?>">
          <?= $label ?>
        </a>
      <?php endforeach; ?>
    </div>

    <span class="table-count"><?= number_format($total) ?> result<?= $total !== 1 ? 's' : '' ?></span>
  </div>

  <?php if (empty($products)): ?>
    <div class="table-empty">
      <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M24 4C13 4 4 13 4 24s9 20 20 20 20-9 20-20S35 4 24 4z"/>
        <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
      </svg>
      <?php if ($q !== '' || $filter !== ''): ?>
        <p>No products match your search.</p>
        <a href="/door-showroom/admin/products" class="btn btn-outline" style="margin-top:8px">Clear filters</a>
      <?php else: ?>
        <p>No products yet.</p>
        <a href="/door-showroom/admin/products/create" class="btn btn-primary" style="margin-top:8px">Add your first product</a>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="product-grid" id="productGrid">
      <?php foreach ($products as $p): ?>
        <div class="product-card" id="prod-card-<?= (int)$p['id'] ?>">
          <div class="product-thumb">
            <?php if ($p['cover_image']): ?>
              <img src="<?= $e($webBase . $p['cover_image']) ?>" alt="<?= $e($p['name']) ?>" loading="lazy" />
            <?php else: ?>
              <div class="product-thumb-empty">
                <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.2">
                  <rect x="6" y="6" width="36" height="36" rx="3"/>
                  <circle cx="18" cy="18" r="4"/>
                  <path d="M6 32l10-10 8 8 6-6 12 12"/>
                </svg>
              </div>
            <?php endif; ?>
            <?php if ($p['is_featured']): ?>
              <span class="product-badge product-badge--featured">Featured</span>
            <?php endif; ?>
            <?php if (!$p['is_active']): ?>
              <span class="product-badge product-badge--inactive">Inactive</span>
            <?php endif; ?>
          </div>

          <div class="product-card-body">
            <div class="product-card-meta">
              <?php if (!empty($p['collection_name'])): ?>
                <span class="product-cat"><?= $e($p['collection_name']) ?></span>
              <?php endif; ?>
              <?php if (!empty($p['color_name'])): ?>
                <span class="product-color">
                  <?php if (!empty($p['color_hex'])): ?><span class="product-color-dot" style="background:<?= $e($p['color_hex']) ?>"></span><?php endif; ?>
                  <?= $e($p['color_name']) ?>
                </span>
              <?php endif; ?>
            </div>
            <h3 class="product-name">
              <a href="/door-showroom/admin/products/<?= (int)$p['id'] ?>/edit"><?= $e($p['name']) ?></a>
            </h3>
            <p class="product-collection">
              <?php
                $attrs = array_filter([$p['usage_name'] ?? '', $p['construction_name'] ?? '']);
                echo $e(implode(' · ', $attrs));
              ?>
            </p>
            <p class="product-price">
              <?php if ((float)$p['base_price'] > 0): ?>
                <strong><?= $e(number_format((float)$p['base_price'], 0, '.', ' ')) ?> DZD</strong>
              <?php else: ?>
                <span class="td-muted">Price on request</span>
              <?php endif; ?>
            </p>
          </div>

          <div class="product-card-actions">
            <button
              class="toggle-status"
              data-id="<?= (int)$p['id'] ?>"
              data-active="<?= (int)$p['is_active'] ?>"
              title="Toggle status"
            >
              <span class="status-dot status-dot--<?= $p['is_active'] ? 'on' : 'off' ?>"></span>
              <span class="status-label" id="prod-status-<?= (int)$p['id'] ?>"><?= $p['is_active'] ? 'Active' : 'Inactive' ?></span>
            </button>
            <div class="td-actions">
              <a href="/door-showroom/admin/products/<?= (int)$p['id'] ?>/edit" class="action-btn action-btn--edit" title="Edit">
                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
              </a>
              <button
                class="action-btn action-btn--delete"
                data-id="<?= (int)$p['id'] ?>"
                data-name="<?= $e($p['name']) ?>"
                title="Delete"
              >
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
              </button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php if ($page > 1): ?>
        <a href="?<?= http_build_query(array_filter(['q' => $q, 'filter' => $filter, 'page' => $page - 1])) ?>" class="page-btn page-btn--nav">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
        </a>
      <?php endif; ?>

      <?php
        $start = max(1, $page - 2);
        $end   = min($totalPages, $page + 2);
        if ($start > 1):
      ?><a href="?<?= http_build_query(array_filter(['q' => $q, 'filter' => $filter, 'page' => 1])) ?>" class="page-btn">1</a><?php
          if ($start > 2): ?><span class="page-ellipsis">…</span><?php endif;
        endif;
        for ($p2 = $start; $p2 <= $end; $p2++):
      ?>
        <a href="?<?= http_build_query(array_filter(['q' => $q, 'filter' => $filter, 'page' => $p2])) ?>"
           class="page-btn <?= $p2 === $page ? 'is-current' : '' ?>"><?= $p2 ?></a>
      <?php endfor; ?>
      <?php if ($end < $totalPages):
          if ($end < $totalPages - 1): ?><span class="page-ellipsis">…</span><?php endif; ?>
        <a href="?<?= http_build_query(array_filter(['q' => $q, 'filter' => $filter, 'page' => $totalPages])) ?>" class="page-btn"><?= $totalPages ?></a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a href="?<?= http_build_query(array_filter(['q' => $q, 'filter' => $filter, 'page' => $page + 1])) ?>" class="page-btn page-btn--nav">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
        </a>
      <?php endif; ?>
      <span class="page-info">Page <?= $page ?> of <?= $totalPages ?></span>
    </div>
  <?php endif; ?>

</div>

<div class="modal-backdrop" id="deleteModal" hidden>
  <div class="modal" role="dialog" aria-modal="true">
    <div class="modal-icon modal-icon--danger">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
      </svg>
    </div>
    <h3>Delete Product</h3>
    <p>Are you sure you want to delete <strong id="modalProdName"></strong>? All images will also be permanently deleted.</p>
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
  var modalName  = document.getElementById('modalProdName');
  var modalBtn   = document.getElementById('modalConfirm');
  var modalLabel = document.getElementById('modalBtnLabel');
  var modalSpin  = document.getElementById('modalSpinner');
  var cancelBtn  = document.getElementById('modalCancel');
  var deleteId   = null;

  document.querySelectorAll('.action-btn--delete').forEach(function (btn) {
    btn.addEventListener('click', function () {
      deleteId = btn.dataset.id;
      modalName.textContent = btn.dataset.name;
      modal.hidden = false;
    });
  });

  cancelBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) closeModal(); });

  function closeModal() { modal.hidden = true; deleteId = null; }

  modalBtn.addEventListener('click', function () {
    if (!deleteId) return;
    modalLabel.textContent = 'Deleting…';
    modalSpin.hidden = false;
    modalBtn.disabled = true;

    xhr('POST', '/door-showroom/admin/products/' + deleteId + '/delete', function (data) {
      if (data.success) {
        var card = document.getElementById('prod-card-' + deleteId);
        if (card) { card.style.opacity = '0'; card.style.transition = 'opacity 0.25s'; setTimeout(function () { card.remove(); }, 260); }
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

  document.querySelectorAll('.toggle-status').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = btn.dataset.id;
      xhr('POST', '/door-showroom/admin/products/' + id + '/toggle', function (data) {
        if (data.success) {
          var dot   = btn.querySelector('.status-dot');
          var label = document.getElementById('prod-status-' + id);
          dot.className = 'status-dot status-dot--' + (data.is_active ? 'on' : 'off');
          label.textContent = data.label;
          btn.dataset.active = data.is_active;
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
