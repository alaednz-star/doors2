<?php
$e    = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$q    = $search ?? '';
$type = $type   ?? '';

$typeLabels = [
    'product'    => 'Products',
    'collection' => 'Collections',
    'color'      => 'Colors',
    'unassigned' => 'Unassigned',
];
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Media Library</h1>
    <p class="page-sub"><?= number_format($total) ?> <?= $total === 1 ? 'file' : 'files' ?></p>
  </div>
  <a href="/door-showroom/admin/media/upload" class="btn btn-primary">
    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    Upload Files
  </a>
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

<div class="ml-toolbar">
  <form method="GET" action="/door-showroom/admin/media" id="mlSearchForm">
    <div class="search-wrap" style="flex:1;max-width:320px">
      <svg class="search-icon" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
      </svg>
      <input type="search" name="q" value="<?= $e($q) ?>" placeholder="Search files…" class="search-input" id="mlSearch" autocomplete="off" />
      <?php if ($q !== ''): ?>
        <a href="?type=<?= urlencode($type) ?>" class="search-clear">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
        </a>
      <?php endif; ?>
    </div>
    <input type="hidden" name="type" value="<?= $e($type) ?>" id="mlTypeHidden" />
  </form>

  <div class="ml-type-filters">
    <a href="?q=<?= urlencode($q) ?>" class="ml-filter-pill <?= $type === '' ? 'is-active' : '' ?>">All</a>
    <a href="?q=<?= urlencode($q) ?>&type=product"    class="ml-filter-pill <?= $type === 'product'    ? 'is-active' : '' ?>">Products</a>
    <a href="?q=<?= urlencode($q) ?>&type=collection" class="ml-filter-pill <?= $type === 'collection' ? 'is-active' : '' ?>">Collections</a>
    <a href="?q=<?= urlencode($q) ?>&type=color"      class="ml-filter-pill <?= $type === 'color'      ? 'is-active' : '' ?>">Colors</a>
    <a href="?q=<?= urlencode($q) ?>&type=unassigned" class="ml-filter-pill <?= $type === 'unassigned' ? 'is-active' : '' ?>">Unassigned</a>
  </div>
</div>

<?php if (empty($items)): ?>
  <div class="table-panel">
    <div class="table-empty">
      <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5">
        <rect x="6" y="6" width="36" height="36" rx="4"/><circle cx="18" cy="18" r="4"/>
        <path d="M6 32l10-10 6 6 6-6 14 14"/>
      </svg>
      <p>No media found.</p>
      <a href="/door-showroom/admin/media/upload" class="btn btn-primary" style="margin-top:8px">Upload first file</a>
    </div>
  </div>
<?php else: ?>
  <div class="ml-grid" id="mlGrid">
    <?php foreach ($items as $item): ?>
      <?php
        $url     = '/door-showroom/uploads/media/' . $item['filename'];
        $label   = $item['original_name'] ?: $item['filename'];
        $sizeStr = '';
        $b = (int)$item['file_size'];
        if ($b >= 1048576)     $sizeStr = round($b/1048576,1) . ' MB';
        elseif ($b >= 1024)    $sizeStr = round($b/1024,1) . ' KB';
        else                   $sizeStr = $b . ' B';
      ?>
      <div class="ml-card" id="ml-card-<?= (int)$item['id'] ?>">
        <button class="ml-thumb-btn" data-id="<?= (int)$item['id'] ?>" aria-label="Preview <?= $e($label) ?>">
          <img src="<?= $e($url) ?>" alt="<?= $e($item['alt_text'] ?? '') ?>" class="ml-thumb" loading="lazy" />
          <?php if ($item['entity_type']): ?>
            <span class="ml-entity-badge ml-entity-badge--<?= $e($item['entity_type']) ?>">
              <?= $e(ucfirst($item['entity_type'])) ?>
            </span>
          <?php endif; ?>
        </button>
        <div class="ml-card-footer">
          <span class="ml-card-name" title="<?= $e($label) ?>"><?= $e(strlen($label) > 22 ? substr($label,0,19).'…' : $label) ?></span>
          <span class="ml-card-size"><?= $e($sizeStr) ?></span>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($totalPages > 1): ?>
    <div class="pagination" style="margin-top:24px">
      <?php if ($page > 1): ?>
        <a href="?<?= http_build_query(['q' => $q, 'type' => $type, 'page' => $page - 1]) ?>" class="page-btn page-btn--nav">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
        </a>
      <?php endif; ?>
      <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <a href="?<?= http_build_query(['q' => $q, 'type' => $type, 'page' => $p]) ?>" class="page-btn <?= $p === $page ? 'is-current' : '' ?>"><?= $p ?></a>
      <?php endfor; ?>
      <?php if ($page < $totalPages): ?>
        <a href="?<?= http_build_query(['q' => $q, 'type' => $type, 'page' => $page + 1]) ?>" class="page-btn page-btn--nav">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
        </a>
      <?php endif; ?>
      <span class="page-info">Page <?= $page ?> of <?= $totalPages ?></span>
    </div>
  <?php endif; ?>
<?php endif; ?>

<div class="ml-overlay" id="mlOverlay" hidden>
  <div class="ml-panel" role="dialog" aria-modal="true" aria-label="Media preview">
    <button class="ml-panel-close" id="mlPanelClose" aria-label="Close">
      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
    </button>

    <div class="ml-panel-img-wrap">
      <img src="" alt="" id="mlPanelImg" class="ml-panel-img" />
    </div>

    <div class="ml-panel-meta">

      <div class="ml-panel-section">
        <div class="ml-meta-grid">
          <div class="ml-meta-item"><span class="ml-meta-label">File</span><span class="ml-meta-value" id="mlMetaName"></span></div>
          <div class="ml-meta-item"><span class="ml-meta-label">Size</span><span class="ml-meta-value" id="mlMetaSize"></span></div>
          <div class="ml-meta-item"><span class="ml-meta-label">Dimensions</span><span class="ml-meta-value" id="mlMetaDims"></span></div>
          <div class="ml-meta-item"><span class="ml-meta-label">Type</span><span class="ml-meta-value" id="mlMetaMime"></span></div>
          <div class="ml-meta-item"><span class="ml-meta-label">Uploaded</span><span class="ml-meta-value" id="mlMetaDate"></span></div>
        </div>
      </div>

      <div class="ml-panel-section">
        <label class="form-label">Alt Text</label>
        <div style="display:flex;gap:8px">
          <input type="text" id="mlAltInput" class="form-input" placeholder="Describe the image…" maxlength="500" />
          <button class="btn btn-outline btn-sm" id="mlAltSave">Save</button>
        </div>
        <div class="ml-alt-msg" id="mlAltMsg" hidden></div>
      </div>

      <div class="ml-panel-section">
        <label class="form-label">Assign To</label>
        <div style="display:flex;gap:8px;margin-bottom:8px">
          <select id="mlAssignType" class="form-select" style="flex:1">
            <option value="">— none —</option>
            <option value="product">Product</option>
            <option value="collection">Collection</option>
            <option value="color">Color</option>
          </select>
        </div>
        <div id="mlAssignIdWrap" style="display:none;margin-bottom:8px">
          <select id="mlAssignId" class="form-select"></select>
        </div>
        <button class="btn btn-outline btn-sm" id="mlAssignSave">Save Assignment</button>
        <div class="ml-alt-msg" id="mlAssignMsg" hidden></div>
      </div>

      <div class="ml-panel-section">
        <a id="mlOpenLink" href="#" target="_blank" class="btn btn-outline btn-sm" style="width:100%;justify-content:center;margin-bottom:8px">Open Image</a>
        <button class="btn btn-danger btn-sm" id="mlDeleteBtn" style="width:100%;justify-content:center">Delete</button>
      </div>

    </div>
  </div>
</div>

<div class="modal-backdrop" id="mlDeleteModal" hidden>
  <div class="modal" role="dialog" aria-modal="true">
    <div class="modal-icon modal-icon--danger">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
      </svg>
    </div>
    <h3>Delete Image</h3>
    <p>This file will be permanently removed. This cannot be undone.</p>
    <div class="modal-actions">
      <button class="btn btn-outline" id="mlDeleteCancel">Cancel</button>
      <button class="btn btn-danger" id="mlDeleteConfirm">
        <span id="mlDeleteLabel">Delete</span>
        <span class="btn-spinner-sm" id="mlDeleteSpinner" hidden></span>
      </button>
    </div>
  </div>
</div>

<?php
$entityData = json_encode([
    'product'    => array_map(fn($r) => ['id' => $r['id'], 'name' => $r['name']], $entities['products']    ?? []),
    'collection' => array_map(fn($r) => ['id' => $r['id'], 'name' => $r['name']], $entities['collections'] ?? []),
    'color'      => array_map(fn($r) => ['id' => $r['id'], 'name' => $r['name']], $entities['colors']      ?? []),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<script src="/door-showroom/assets/js/media.js"></script>
<script>MediaLibrary.init(<?= $entityData ?>);</script>
