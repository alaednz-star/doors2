<?php
$e = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$q = $search ?? '';
$money = fn($n) => number_format((float)$n, 0, '.', ' ') . ' DZD';
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Pricing Matrix</h1>
    <p class="page-sub"><?= number_format($total) ?> rule<?= $total !== 1 ? 's' : '' ?> · Collection × Usage × Construction</p>
  </div>
  <a href="/door-showroom/admin/pricing/create" class="btn btn-primary">
    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
    Add Price Rule
  </a>
</div>

<?php if ($flash): ?>
  <div class="flash flash--success"><span><?= $e($flash) ?></span><button class="flash-close" onclick="this.parentElement.remove()">×</button></div>
<?php endif; ?>

<div class="table-panel">
  <div class="table-toolbar">
    <form method="GET" action="/door-showroom/admin/pricing" class="search-form" id="searchForm">
      <div class="search-wrap">
        <svg class="search-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/></svg>
        <input type="search" name="q" value="<?= $e($q) ?>" placeholder="Search by collection, usage or construction…" class="search-input" id="searchInput" autocomplete="off" />
      </div>
    </form>
    <span class="table-count"><?= number_format($total) ?> result<?= $total !== 1 ? 's' : '' ?></span>
  </div>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Collection</th>
          <th>Door Usage</th>
          <th>Construction</th>
          <th style="width:130px">Base Price</th>
          <th style="width:130px">Availability</th>
          <th style="width:90px">Status</th>
          <th style="width:90px"></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rules)): ?>
          <tr><td colspan="7">
            <div class="table-empty">
              <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="8" y="8" width="32" height="32" rx="4"/><path d="M16 24h16M16 30h8"/></svg>
              <p>No price rules yet.</p>
              <a href="/door-showroom/admin/pricing/create" class="btn btn-primary" style="margin-top:8px">Add first rule</a>
            </div>
          </td></tr>
        <?php else: foreach ($rules as $r): ?>
          <tr id="row-<?= (int)$r['id'] ?>">
            <td><strong><?= $e($r['collection_name'] ?? '—') ?></strong></td>
            <td><?= $e($r['usage_name'] ?? '—') ?></td>
            <td><?= $e($r['construction_name'] ?? '—') ?></td>
            <td><?= $r['is_available'] ? $e($money($r['base_price'])) : '<span class="td-muted">—</span>' ?></td>
            <td>
              <button class="toggle-avail" data-id="<?= (int)$r['id'] ?>" title="Toggle availability"
                      style="background:none;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:6px">
                <span class="status-dot status-dot--<?= $r['is_available'] ? 'on' : 'off' ?>"></span>
                <span class="avail-label" id="avail-<?= (int)$r['id'] ?>" style="font-size:12px"><?= $r['is_available'] ? 'Available' : 'Non disponible' ?></span>
              </button>
            </td>
            <td class="td-center">
              <button class="toggle-status" data-id="<?= (int)$r['id'] ?>" title="Toggle active">
                <span class="status-dot status-dot--<?= $r['is_active'] ? 'on' : 'off' ?>"></span>
                <span class="status-label" id="status-label-<?= (int)$r['id'] ?>"><?= $r['is_active'] ? 'Active' : 'Inactive' ?></span>
              </button>
            </td>
            <td class="td-actions">
              <a href="/door-showroom/admin/pricing/<?= (int)$r['id'] ?>/edit" class="action-btn action-btn--edit" title="Edit">
                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
              </a>
              <button class="action-btn action-btn--delete" data-id="<?= (int)$r['id'] ?>" data-name="<?= $e(($r['collection_name'] ?? '') . ' · ' . ($r['usage_name'] ?? '') . ' · ' . ($r['construction_name'] ?? '')) ?>" title="Delete">
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
        <a href="?<?= http_build_query(['q'=>$q,'page'=>$p]) ?>" class="page-btn <?= $p === $page ? 'is-current' : '' ?>"><?= $p ?></a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
</div>

<div class="modal-backdrop" id="deleteModal" hidden>
  <div class="modal" role="dialog" aria-modal="true">
    <div class="modal-icon modal-icon--danger"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg></div>
    <h3>Delete Price Rule</h3>
    <p>Delete <strong id="modalItemName"></strong>? This cannot be undone.</p>
    <div class="modal-actions">
      <button class="btn btn-outline" id="modalCancel">Cancel</button>
      <button class="btn btn-danger" id="modalConfirm">Delete</button>
    </div>
  </div>
</div>

<script>
(function () {
  var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  var modal = document.getElementById('deleteModal'), itemName = document.getElementById('modalItemName');
  var btn = document.getElementById('modalConfirm'); var deleteId = null;

  document.querySelectorAll('.action-btn--delete').forEach(function (b) {
    b.addEventListener('click', function () { deleteId = b.dataset.id; itemName.textContent = b.dataset.name; modal.hidden = false; });
  });
  document.getElementById('modalCancel').addEventListener('click', function () { modal.hidden = true; deleteId = null; });
  modal.addEventListener('click', function (e) { if (e.target === modal) { modal.hidden = true; deleteId = null; } });
  btn.addEventListener('click', function () {
    if (!deleteId) return; btn.disabled = true; btn.textContent = 'Deleting…';
    xhr('POST', '/door-showroom/admin/pricing/' + deleteId + '/delete', function (d) {
      if (d.success) { var row = document.getElementById('row-'+deleteId); if (row){ row.style.opacity='0'; row.style.transition='opacity .25s'; setTimeout(function(){row.remove();},260);} flash(d.message,'success'); }
      else flash(d.message || 'Delete failed.', 'error');
      modal.hidden = true; deleteId = null; btn.disabled = false; btn.textContent = 'Delete';
    });
  });

  document.querySelectorAll('.toggle-status').forEach(function (b) {
    b.addEventListener('click', function () { var id = b.dataset.id;
      xhr('POST', '/door-showroom/admin/pricing/' + id + '/toggle', function (d) {
        if (d.success) { b.querySelector('.status-dot').className='status-dot status-dot--'+(d.is_active?'on':'off'); document.getElementById('status-label-'+id).textContent=d.label; }
      });
    });
  });
  document.querySelectorAll('.toggle-avail').forEach(function (b) {
    b.addEventListener('click', function () { var id = b.dataset.id;
      xhr('POST', '/door-showroom/admin/pricing/' + id + '/available', function (d) {
        if (d.success) { b.querySelector('.status-dot').className='status-dot status-dot--'+(d.is_available?'on':'off'); document.getElementById('avail-'+id).textContent=d.label; }
      });
    });
  });
  var si = document.getElementById('searchInput'), t;
  if (si) si.addEventListener('input', function(){ clearTimeout(t); t=setTimeout(function(){document.getElementById('searchForm').submit();},420); });

  function flash(msg,type){ var el=document.createElement('div'); el.className='flash flash--'+type; el.innerHTML='<span>'+msg+'</span><button class="flash-close" onclick="this.parentElement.remove()">×</button>'; document.querySelector('.admin-content').insertBefore(el, document.querySelector('.table-panel')); setTimeout(function(){if(el.parentElement)el.remove();},5000); }
  function xhr(m,u,cb){ var x=new XMLHttpRequest(); x.open(m,u,true); x.setRequestHeader('Content-Type','application/json'); x.setRequestHeader('X-CSRF-Token',csrf); x.setRequestHeader('X-Requested-With','XMLHttpRequest'); x.onreadystatechange=function(){ if(x.readyState!==4)return; try{cb(JSON.parse(x.responseText));}catch(e){cb({success:false,message:'Error.'});} }; x.send(JSON.stringify({_csrf:csrf})); }
}());
</script>
