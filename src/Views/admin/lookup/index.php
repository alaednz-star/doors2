<?php
$e      = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$q      = $search ?? '';
$status = $filter ?? '';
$base   = $meta['routeBase'];
$sing   = $meta['singular'];
$plur   = $meta['plural'];
$hasImg = !empty($meta['hasImage']);
$slug   = trim((string)parse_url($base, PHP_URL_PATH));
?>

<div class="page-header">
  <div>
    <h1 class="page-title"><?= $e($plur) ?></h1>
    <p class="page-sub"><?= number_format($total) ?> total</p>
  </div>
  <div style="display:flex;gap:8px;align-items:center">
    <a href="<?= $e($base) ?>/create" class="btn btn-primary">
      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
      Add <?= $e($sing) ?>
    </a>
  </div>
</div>

<?php if ($flash): ?>
  <div class="flash flash--success">
    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    <span><?= $e($flash) ?></span>
    <button class="flash-close" onclick="this.parentElement.remove()">×</button>
  </div>
<?php endif; ?>

<div class="table-panel">
  <div class="table-toolbar">
    <form method="GET" action="<?= $e($base) ?>" class="search-form" id="searchForm">
      <div class="search-wrap">
        <svg class="search-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/></svg>
        <input type="search" name="q" value="<?= $e($q) ?>" placeholder="Search…" class="search-input" id="searchInput" autocomplete="off" />
        <?php if ($q !== ''): ?><a href="<?= $e($base) ?>" class="search-clear">×</a><?php endif; ?>
      </div>
      <div class="filter-tabs">
        <a href="?q=<?= urlencode($q) ?>" class="filter-tab <?= $status === '' ? 'is-active' : '' ?>">All</a>
        <a href="?q=<?= urlencode($q) ?>&status=active" class="filter-tab <?= $status === 'active' ? 'is-active' : '' ?>">Active</a>
        <a href="?q=<?= urlencode($q) ?>&status=inactive" class="filter-tab <?= $status === 'inactive' ? 'is-active' : '' ?>">Inactive</a>
      </div>
    </form>
    <span class="table-count"><?= number_format($total) ?> result<?= $total !== 1 ? 's' : '' ?></span>
  </div>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th style="width:40px">#</th>
          <?php if ($hasImg): ?><th style="width:64px">Image</th><?php endif; ?>
          <th>Name</th>
          <th>Slug</th>
          <th>Description</th>
          <th style="width:80px">Order</th>
          <th style="width:90px">Status</th>
          <th style="width:110px"></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="<?= $hasImg ? 8 : 7 ?>">
            <div class="table-empty">
              <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="8" y="8" width="32" height="32" rx="4"/><path d="M16 24h16M16 30h8"/></svg>
              <p>No <?= $e(strtolower($plur)) ?> found.</p>
              <a href="<?= $e($base) ?>/create" class="btn btn-primary" style="margin-top:8px">Add first <?= $e(strtolower($sing)) ?></a>
            </div>
          </td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr id="row-<?= (int)$r['id'] ?>">
            <td class="td-muted"><?= (int)$r['id'] ?></td>
            <?php if ($hasImg): ?>
              <td>
                <?php if (!empty($r['image_filename'])): ?>
                  <img src="/door-showroom/uploads/<?= $slug === '/door-showroom/admin/construction-types' ? 'construction' : 'misc' ?>/<?= $e($r['image_filename']) ?>" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:6px" />
                <?php else: ?><span class="td-muted">—</span><?php endif; ?>
              </td>
            <?php endif; ?>
            <td><strong><?= $e($r['name']) ?></strong></td>
            <td><code class="td-slug"><?= $e($r['slug']) ?></code></td>
            <td class="td-desc"><?= $r['description'] ? $e(substr($r['description'],0,60)) . (strlen($r['description'])>60?'…':'') : '<span class="td-muted">—</span>' ?></td>
            <td class="td-center"><?= (int)$r['display_order'] ?></td>
            <td class="td-center">
              <button class="toggle-status" data-id="<?= (int)$r['id'] ?>" title="Toggle status">
                <span class="status-dot status-dot--<?= $r['is_active'] ? 'on' : 'off' ?>"></span>
                <span class="status-label" id="status-label-<?= (int)$r['id'] ?>"><?= $r['is_active'] ? 'Active' : 'Inactive' ?></span>
              </button>
            </td>
            <td class="td-actions">
              <a href="<?= $e($base) ?>/<?= (int)$r['id'] ?>/edit" class="action-btn action-btn--edit" title="Edit">
                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
              </a>
              <button class="action-btn action-btn--delete" data-id="<?= (int)$r['id'] ?>" data-name="<?= $e($r['name']) ?>" title="Delete">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
              </button>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <a href="?<?= http_build_query(['q'=>$q,'status'=>$status,'page'=>$p]) ?>" class="page-btn <?= $p === $page ? 'is-current' : '' ?>"><?= $p ?></a>
      <?php endfor; ?>
      <span class="page-info">Page <?= $page ?> of <?= $totalPages ?></span>
    </div>
  <?php endif; ?>
</div>

<div class="modal-backdrop" id="deleteModal" hidden>
  <div class="modal" role="dialog" aria-modal="true">
    <div class="modal-icon modal-icon--danger"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg></div>
    <h3>Delete <?= $e($sing) ?></h3>
    <p>Are you sure you want to delete <strong id="modalItemName"></strong>? This cannot be undone.</p>
    <div class="modal-actions">
      <button class="btn btn-outline" id="modalCancel">Cancel</button>
      <button class="btn btn-danger" id="modalConfirm"><span id="modalBtnLabel">Delete</span></button>
    </div>
  </div>
</div>

<script>
(function () {
  var BASE = <?= json_encode($base) ?>;
  var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  var modal = document.getElementById('deleteModal');
  var itemName = document.getElementById('modalItemName');
  var btn = document.getElementById('modalConfirm'), label = document.getElementById('modalBtnLabel');
  var deleteId = null;

  document.querySelectorAll('.action-btn--delete').forEach(function (b) {
    b.addEventListener('click', function () { deleteId = b.dataset.id; itemName.textContent = b.dataset.name; modal.hidden = false; });
  });
  document.getElementById('modalCancel').addEventListener('click', function () { modal.hidden = true; deleteId = null; });
  modal.addEventListener('click', function (e) { if (e.target === modal) { modal.hidden = true; deleteId = null; } });

  btn.addEventListener('click', function () {
    if (!deleteId) return;
    label.textContent = 'Deleting…'; btn.disabled = true;
    xhr('POST', BASE + '/' + deleteId + '/delete', function (data) {
      if (data.success) { var row = document.getElementById('row-' + deleteId); if (row) { row.style.opacity='0'; row.style.transition='opacity .25s'; setTimeout(function(){row.remove();},260); } showFlash(data.message,'success'); }
      else showFlash(data.message || 'Delete failed.', 'error');
      modal.hidden = true; deleteId = null; label.textContent = 'Delete'; btn.disabled = false;
    });
  });
  document.querySelectorAll('.toggle-status').forEach(function (b) {
    b.addEventListener('click', function () {
      var id = b.dataset.id;
      xhr('POST', BASE + '/' + id + '/toggle', function (data) {
        if (data.success) { b.querySelector('.status-dot').className = 'status-dot status-dot--' + (data.is_active?'on':'off'); document.getElementById('status-label-'+id).textContent = data.label; }
      });
    });
  });
  var si = document.getElementById('searchInput'), t;
  if (si) si.addEventListener('input', function(){ clearTimeout(t); t=setTimeout(function(){document.getElementById('searchForm').submit();},420); });

  function showFlash(msg, type){ var el=document.createElement('div'); el.className='flash flash--'+type; el.innerHTML='<span>'+msg+'</span><button class="flash-close" onclick="this.parentElement.remove()">×</button>'; document.querySelector('.admin-content').insertBefore(el, document.querySelector('.table-panel')); setTimeout(function(){if(el.parentElement)el.remove();},5000); }
  function xhr(method,url,cb){ var x=new XMLHttpRequest(); x.open(method,url,true); x.setRequestHeader('Content-Type','application/json'); x.setRequestHeader('X-CSRF-Token',csrf); x.setRequestHeader('X-Requested-With','XMLHttpRequest'); x.onreadystatechange=function(){ if(x.readyState!==4)return; try{cb(JSON.parse(x.responseText));}catch(e){cb({success:false,message:'Unexpected error.'});} }; x.send(JSON.stringify({_csrf:csrf})); }
}());
</script>
