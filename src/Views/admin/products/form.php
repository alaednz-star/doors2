<?php
$e      = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$isEdit = $product !== null;
$val    = function (string $key, string $default = '') use ($old, $product): string {
    if (!empty($old)) return (string)($old[$key] ?? $default);
    if ($product)    return (string)($product[$key] ?? $default);
    return $default;
};
$checked = function (string $key, int $default = 0) use ($old, $product): bool {
    if (!empty($old)) return !empty($old[$key]);
    if ($product)    return (bool)$product[$key];
    return (bool)$default;
};
$err     = fn(string $k): string => $errors[$k] ?? '';
$webBase = '/door-showroom/uploads/products/';
$uploadErrors = \App\Core\Session::getFlash('upload_errors', []);
?>

<div class="page-header">
  <div>
    <div class="breadcrumb">
      <a href="/door-showroom/admin/products">Products</a>
      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
      <span><?= $isEdit ? $e($product['name']) : 'New Product' ?></span>
    </div>
    <h1 class="page-title"><?= $isEdit ? 'Edit Product' : 'Add Product' ?></h1>
  </div>
</div>

<?php if (!empty($uploadErrors)): ?>
  <div class="flash flash--error">
    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
    <div>
      <strong>Some images could not be uploaded:</strong>
      <ul style="margin:4px 0 0 16px">
        <?php foreach ($uploadErrors as $ue): ?>
          <li><?= $e((string)$ue) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
<?php endif; ?>

<form method="POST" action="<?= $e($formAction) ?>" id="productForm" enctype="multipart/form-data" novalidate>
  <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>" />

  <div class="form-layout form-layout--product">

    <div class="form-main">

      <!-- Core details -->
      <div class="form-card">
        <div class="form-card-header">
          <h2>Product Details</h2>
          <p>Name, description, and identification.</p>
        </div>
        <div class="form-card-body">

          <div class="form-field <?= $err('name') ? 'has-error' : '' ?>">
            <label for="name">Product Name <span class="required">*</span></label>
            <input type="text" id="name" name="name" value="<?= $e($val('name')) ?>"
                   placeholder="e.g. Grand Pivot — Smoked Oak" maxlength="180" required autocomplete="off" />
            <?php if ($err('name')): ?>
              <span class="form-error"><?= $e($err('name')) ?></span>
            <?php endif; ?>
            <div class="slug-preview" id="slugPreview" <?= ($isEdit || $val('name') !== '') ? '' : 'hidden' ?>>
              Slug: <code id="slugValue"><?= $isEdit ? $e($product['slug']) : $e($slugPreviewValue ?? '') ?></code>
            </div>
          </div>

          <div class="form-row">
            <div class="form-field <?= $err('sku') ? 'has-error' : '' ?>">
              <label for="sku">SKU</label>
              <input type="text" id="sku" name="sku" value="<?= $e($val('sku')) ?>"
                     placeholder="e.g. GP-OAK-001" maxlength="60" autocomplete="off" />
              <?php if ($err('sku')): ?>
                <span class="form-error"><?= $e($err('sku')) ?></span>
              <?php else: ?>
                <span class="form-hint">Optional. Letters, numbers, hyphens, underscores only.</span>
              <?php endif; ?>
            </div>
            <div class="form-field <?= ($err('width_mm') || $err('height_mm')) ? 'has-error' : '' ?>">
              <label>Dimensions (mm)</label>
              <div class="dim-row" style="display:flex;gap:10px;align-items:center">
                <input type="number" id="width_mm" name="width_mm" value="<?= $e($val('width_mm')) ?>"
                       placeholder="Width" min="1" max="6000" step="1" style="flex:1" />
                <span style="color:var(--text-muted)">×</span>
                <input type="number" id="height_mm" name="height_mm" value="<?= $e($val('height_mm')) ?>"
                       placeholder="Height" min="1" max="6000" step="1" style="flex:1" />
              </div>
              <?php if ($err('width_mm') || $err('height_mm')): ?>
                <span class="form-error"><?= $e($err('width_mm') ?: $err('height_mm')) ?></span>
              <?php else: ?>
                <span class="form-hint">Width × Height in millimetres. Reference size 900 × 2100.</span>
              <?php endif; ?>
            </div>
          </div>

          <div class="form-field <?= $err('description') ? 'has-error' : '' ?>">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5"
                      placeholder="Detailed product description…" maxlength="5000"><?= $e($val('description')) ?></textarea>
            <?php if ($err('description')): ?>
              <span class="form-error"><?= $e($err('description')) ?></span>
            <?php else: ?>
              <span class="form-hint">Optional. Up to 5000 characters.</span>
            <?php endif; ?>
          </div>

        </div>
      </div>

      <!-- Image gallery -->
      <div class="form-card">
        <div class="form-card-header">
          <h2>Product Images</h2>
          <p>JPEG, PNG, or WebP · Max 8 MB each · First upload becomes the cover.</p>
        </div>
        <div class="form-card-body">

          <?php if ($isEdit && !empty($images)): ?>
            <div class="gallery-grid" id="galleryGrid">
              <?php foreach ($images as $img): ?>
                <div class="gallery-item <?= $img['is_cover'] ? 'is-cover' : '' ?>" id="gimg-<?= (int)$img['id'] ?>" data-id="<?= (int)$img['id'] ?>">
                  <img src="<?= $e($webBase . $img['filename']) ?>" alt="<?= $e($img['alt_text'] ?? '') ?>" />
                  <div class="gallery-item-overlay">
                    <button type="button" class="gallery-btn gallery-btn--cover" data-id="<?= (int)$img['id'] ?>" title="Set as cover">
                      <svg viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    </button>
                    <button type="button" class="gallery-btn gallery-btn--delete" data-id="<?= (int)$img['id'] ?>" title="Remove image">
                      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    </button>
                  </div>
                  <?php if ($img['is_cover']): ?>
                    <span class="gallery-cover-badge">Cover</span>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
            <p class="form-hint" style="margin-top:8px">Click the star to set cover. Drag to reorder.</p>
          <?php endif; ?>

          <div class="upload-zone" id="uploadZone">
            <input type="file" name="images[]" id="imageInput" multiple
                   accept="image/jpeg,image/png,image/webp" class="upload-input" />
            <label for="imageInput" class="upload-label">
              <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M28 8H12a4 4 0 00-4 4v20m0 0v4a4 4 0 004 4h24a4 4 0 004-4V20M36 8l-8 8m0-8l8 8" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <span class="upload-label-main">Drop images here or <u>browse</u></span>
              <span class="upload-label-sub">JPEG, PNG, WebP · up to 8 MB each</span>
            </label>
          </div>

          <div class="upload-preview" id="uploadPreview"></div>

        </div>
      </div>

      <!-- Construction -->
      <div class="form-card">
        <div class="form-card-header">
          <h2>Construction Type</h2>
          <p>The construction this door is built in.</p>
        </div>
        <div class="form-card-body">
          <div class="form-field <?= $err('construction_type_id') ? 'has-error' : '' ?>">
            <select name="construction_type_id" id="construction_type_id" class="form-select">
              <option value="">— None —</option>
              <?php foreach ($constructionTypes as $ct): ?>
                <option value="<?= (int)$ct['id'] ?>"
                  <?= (string)$val('construction_type_id') === (string)$ct['id'] ? 'selected' : '' ?>><?= $e($ct['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <?php if ($err('construction_type_id')): ?>
              <span class="form-error"><?= $e($err('construction_type_id')) ?></span>
            <?php endif; ?>
            <?php if (empty($constructionTypes)): ?>
              <p class="td-muted">No construction types yet. <a href="/door-showroom/admin/construction-types/create">Add one</a></p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="form-card">
        <div class="form-card-header">
          <h2>Colors</h2>
          <p>Pick a Collection first — only that collection's colors are shown.</p>
        </div>
        <div class="form-card-body">
          <div class="checkbox-grid checkbox-grid--colors" id="colorGrid">
            <?php foreach ($colors as $clr): ?>
              <label class="checkbox-item checkbox-item--color color-option" data-collection="<?= (int)($clr['collection_id'] ?? 0) ?>">
                <input type="checkbox" name="color_ids[]" value="<?= (int)$clr['id'] ?>"
                       <?= in_array((int)$clr['id'], $selectedColors, true) ? 'checked' : '' ?> />
                <span class="color-swatch" style="background:<?= $e($clr['hex'] ?? '#ccc') ?>"></span>
                <span><?= $e($clr['name']) ?></span>
              </label>
            <?php endforeach; ?>
            <?php if (empty($colors)): ?>
              <p class="td-muted">No colors configured. <a href="/door-showroom/admin/colors/create">Add colors</a></p>
            <?php endif; ?>
          </div>
          <p class="td-muted" id="colorEmptyHint" style="display:none">Select a Collection to see its colors.</p>
        </div>
      </div>

    </div>

    <!-- Sidebar -->
    <div class="form-sidebar">

      <div class="form-card">
        <div class="form-card-header">
          <h2>Publish</h2>
        </div>
        <div class="form-card-body">

          <div class="form-field">
            <div class="toggle-wrap">
              <label class="toggle" for="is_active">
                <input type="checkbox" id="is_active" name="is_active" value="1"
                       <?= $checked('is_active', 1) ? 'checked' : '' ?> />
                <span class="toggle-track"></span>
              </label>
              <div class="toggle-label">
                <strong>Active</strong>
                <span>Visible on the website</span>
              </div>
            </div>
          </div>

          <div class="form-field" style="margin-top:4px">
            <div class="toggle-wrap">
              <label class="toggle" for="is_featured">
                <input type="checkbox" id="is_featured" name="is_featured" value="1"
                       <?= $checked('is_featured') ? 'checked' : '' ?> />
                <span class="toggle-track"></span>
              </label>
              <div class="toggle-label">
                <strong>Featured</strong>
                <span>Highlighted on the homepage</span>
              </div>
            </div>
          </div>

          <div class="form-field <?= $err('display_order') ? 'has-error' : '' ?>" style="margin-top:12px">
            <label for="display_order">Display Order</label>
            <input type="number" id="display_order" name="display_order"
                   value="<?= $e($val('display_order', '0')) ?>" min="0" max="9999" style="max-width:110px" />
            <?php if ($err('display_order')): ?>
              <span class="form-error"><?= $e($err('display_order')) ?></span>
            <?php endif; ?>
          </div>

        </div>
      </div>

      <div class="form-card">
        <div class="form-card-header">
          <h2>Category</h2>
        </div>
        <div class="form-card-body">
          <div class="form-field <?= $err('category_id') ? 'has-error' : '' ?>">
            <select name="category_id" id="category_id" class="form-select">
              <option value="">— None —</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>"
                  <?= (string)($val('category_id') ?: ($product['category_id'] ?? '')) === (string)$cat['id'] ? 'selected' : '' ?>>
                  <?= $e($cat['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <?php if ($err('category_id')): ?>
              <span class="form-error"><?= $e($err('category_id')) ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="form-card">
        <div class="form-card-header">
          <h2>Collection</h2>
        </div>
        <div class="form-card-body">
          <div class="form-field <?= $err('collection_id') ? 'has-error' : '' ?>">
            <select name="collection_id" id="collection_id" class="form-select">
              <option value="">— None —</option>
              <?php foreach ($collections as $col): ?>
                <option value="<?= (int)$col['id'] ?>"
                  <?= (string)($val('collection_id') ?: ($product['collection_id'] ?? '')) === (string)$col['id'] ? 'selected' : '' ?>>
                  <?= $e($col['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <?php if ($err('collection_id')): ?>
              <span class="form-error"><?= $e($err('collection_id')) ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <?php if ($isEdit): ?>
        <div class="info-card">
          <h3>Product Info</h3>
          <dl class="info-list">
            <dt>ID</dt>     <dd>#<?= (int)$product['id'] ?></dd>
            <dt>Slug</dt>   <dd><code><?= $e($product['slug']) ?></code></dd>
            <dt>Created</dt><dd><?= date('d M Y', strtotime($product['created_at'])) ?></dd>
            <dt>Updated</dt><dd><?= date('d M Y', strtotime($product['updated_at'])) ?></dd>
          </dl>
        </div>

        <div class="danger-zone">
          <h3>Danger Zone</h3>
          <p>Deletes the product and all its images permanently.</p>
          <button type="button" class="btn btn-danger" id="sidebarDeleteBtn"
                  data-id="<?= (int)$product['id'] ?>" data-name="<?= $e($product['name']) ?>">
            Delete Product
          </button>
        </div>
      <?php endif; ?>

      <div class="form-card" style="border:none;background:none;box-shadow:none;padding:0">
        <div class="form-actions" style="flex-direction:column;gap:8px">
          <button type="submit" class="btn btn-primary" id="submitBtn" style="width:100%">
            <span id="submitLabel"><?= $isEdit ? 'Save Changes' : 'Create Product' ?></span>
            <span class="btn-spinner-sm" id="submitSpinner" hidden></span>
          </button>
          <a href="/door-showroom/admin/products" class="btn btn-outline" style="width:100%;text-align:center">Cancel</a>
        </div>
      </div>

    </div>
  </div>
</form>

<!-- Delete modal -->
<div class="modal-backdrop" id="deleteModal" hidden>
  <div class="modal" role="dialog" aria-modal="true">
    <div class="modal-icon modal-icon--danger">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
      </svg>
    </div>
    <h3>Delete Product</h3>
    <p>Delete <strong id="modalProdName"><?= $isEdit ? $e($product['name']) : '' ?></strong>? This cannot be undone.</p>
    <div class="modal-actions">
      <button class="btn btn-outline" id="modalCancel">Cancel</button>
      <button class="btn btn-danger" id="modalConfirm">Delete</button>
    </div>
  </div>
</div>

<script>
(function () {
  var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  /* Colors filtered by selected collection */
  var collSelect = document.getElementById('collection_id');
  var colorOpts  = Array.prototype.slice.call(document.querySelectorAll('.color-option'));
  var colorHint  = document.getElementById('colorEmptyHint');
  function filterColors() {
    if (!collSelect) return;
    var coll = collSelect.value;
    var shown = 0;
    colorOpts.forEach(function (opt) {
      var match = !coll || opt.getAttribute('data-collection') === coll;
      opt.style.display = match ? '' : 'none';
      if (!match) { var cb = opt.querySelector('input'); if (cb) cb.checked = false; }
      if (match) shown++;
    });
    if (colorHint) colorHint.style.display = (coll && shown === 0) ? '' : 'none';
  }
  if (collSelect) { collSelect.addEventListener('change', filterColors); filterColors(); }

  /* ── Slug preview ── */
  var nameInput   = document.getElementById('name');
  var slugPreview = document.getElementById('slugPreview');
  var slugValue   = document.getElementById('slugValue');

  function slugify(str) {
    return str.toLowerCase().trim()
      .replace(/[^a-z0-9\s\-]/g, '')
      .replace(/[\s\-]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  if (nameInput) {
    nameInput.addEventListener('input', function () {
      var s = slugify(nameInput.value);
      if (s) { slugValue.textContent = s; slugPreview.hidden = false; }
      else   { slugPreview.hidden = true; }
    });
  }

  /* ── Form submit ── */
  var form       = document.getElementById('productForm');
  var submitBtn  = document.getElementById('submitBtn');
  var submitLbl  = document.getElementById('submitLabel');
  var submitSpin = document.getElementById('submitSpinner');

  if (form) {
    form.addEventListener('submit', function () {
      submitBtn.disabled     = true;
      submitLbl.textContent  = 'Saving…';
      submitSpin.hidden      = false;
    });
  }

  /* ── Upload preview ── */
  var imageInput   = document.getElementById('imageInput');
  var uploadZone   = document.getElementById('uploadZone');
  var previewWrap  = document.getElementById('uploadPreview');

  if (imageInput) {
    imageInput.addEventListener('change', function () {
      renderPreview(this.files);
    });
  }

  if (uploadZone) {
    uploadZone.addEventListener('dragover', function (e) { e.preventDefault(); uploadZone.classList.add('is-over'); });
    uploadZone.addEventListener('dragleave', function () { uploadZone.classList.remove('is-over'); });
    uploadZone.addEventListener('drop', function (e) {
      e.preventDefault();
      uploadZone.classList.remove('is-over');
      var dt = new DataTransfer();
      for (var i = 0; i < e.dataTransfer.files.length; i++) dt.items.add(e.dataTransfer.files[i]);
      imageInput.files = dt.files;
      renderPreview(dt.files);
    });
  }

  function renderPreview(files) {
    if (!previewWrap) return;
    previewWrap.innerHTML = '';
    if (!files || !files.length) return;
    for (var i = 0; i < files.length; i++) {
      (function (file) {
        var reader = new FileReader();
        reader.onload = function (ev) {
          var div = document.createElement('div');
          div.className = 'upload-thumb';
          div.innerHTML = '<img src="' + ev.target.result + '" alt="" /><span>' + file.name.replace(/.*[\/\\]/, '').substring(0, 24) + '</span>';
          previewWrap.appendChild(div);
        };
        reader.readAsDataURL(file);
      })(files[i]);
    }
  }

  /* ── Gallery: delete image ── */
  document.querySelectorAll('.gallery-btn--delete').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = btn.dataset.id;
      if (!confirm('Remove this image?')) return;
      xhr('POST', '/door-showroom/admin/images/' + id + '/delete', function (data) {
        if (data.success) {
          var item = document.getElementById('gimg-' + id);
          if (item) item.remove();
        } else {
          alert(data.message || 'Could not remove image.');
        }
      });
    });
  });

  /* ── Gallery: set cover ── */
  document.querySelectorAll('.gallery-btn--cover').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = btn.dataset.id;
      xhr('POST', '/door-showroom/admin/images/' + id + '/cover', function (data) {
        if (data.success) {
          document.querySelectorAll('.gallery-item').forEach(function (el) {
            el.classList.remove('is-cover');
            var badge = el.querySelector('.gallery-cover-badge');
            if (badge) badge.remove();
          });
          var item = document.getElementById('gimg-' + id);
          if (item) {
            item.classList.add('is-cover');
            var b = document.createElement('span');
            b.className = 'gallery-cover-badge';
            b.textContent = 'Cover';
            item.appendChild(b);
          }
        }
      });
    });
  });

  /* ── Gallery: drag-to-reorder ── */
  var galleryGrid = document.getElementById('galleryGrid');
  if (galleryGrid) {
    var dragged = null;
    galleryGrid.querySelectorAll('.gallery-item').forEach(addDrag);

    function addDrag(el) {
      el.draggable = true;
      el.addEventListener('dragstart', function () { dragged = el; el.classList.add('is-dragging'); });
      el.addEventListener('dragend',   function () { el.classList.remove('is-dragging'); dragged = null; saveOrder(); });
      el.addEventListener('dragover',  function (e) {
        e.preventDefault();
        if (!dragged || dragged === el) return;
        var rect = el.getBoundingClientRect();
        var mid  = rect.left + rect.width / 2;
        if (e.clientX < mid) galleryGrid.insertBefore(dragged, el);
        else                 galleryGrid.insertBefore(dragged, el.nextSibling);
      });
    }

    function saveOrder() {
      var ids = Array.from(galleryGrid.querySelectorAll('.gallery-item')).map(function (el) { return parseInt(el.dataset.id, 10); });
      <?php if ($isEdit): ?>
      var x = new XMLHttpRequest();
      x.open('POST', '/door-showroom/admin/products/<?= (int)$product['id'] ?>/images/reorder', true);
      x.setRequestHeader('Content-Type', 'application/json');
      x.setRequestHeader('X-CSRF-Token', csrf);
      x.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      x.send(JSON.stringify({ _csrf: csrf, ids: ids }));
      <?php endif; ?>
    }
  }

  /* ── Sidebar delete ── */
  var deleteBtn  = document.getElementById('sidebarDeleteBtn');
  var modal      = document.getElementById('deleteModal');
  var cancelBtn  = document.getElementById('modalCancel');
  var confirmBtn = document.getElementById('modalConfirm');

  if (deleteBtn) {
    deleteBtn.addEventListener('click', function () { modal.hidden = false; });
  }
  if (cancelBtn) {
    cancelBtn.addEventListener('click', function () { modal.hidden = true; });
  }
  if (modal) {
    modal.addEventListener('click', function (e) { if (e.target === modal) modal.hidden = true; });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') modal.hidden = true; });
  }
  if (confirmBtn && deleteBtn) {
    confirmBtn.addEventListener('click', function () {
      var id = deleteBtn.dataset.id;
      confirmBtn.disabled = true;
      confirmBtn.textContent = 'Deleting…';
      xhr('POST', '/door-showroom/admin/products/' + id + '/delete', function (data) {
        if (data.success) { window.location.href = '/door-showroom/admin/products'; }
        else { alert(data.message || 'Delete failed.'); confirmBtn.disabled = false; confirmBtn.textContent = 'Delete'; }
      });
    });
  }

  function xhr(method, url, cb) {
    var x = new XMLHttpRequest();
    x.open(method, url, true);
    x.setRequestHeader('Content-Type', 'application/json');
    x.setRequestHeader('X-CSRF-Token', csrf);
    x.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    x.onreadystatechange = function () {
      if (x.readyState !== 4) return;
      try { cb(JSON.parse(x.responseText)); } catch(e2) { cb({success:false, message:'Unexpected error.'}); }
    };
    x.send(JSON.stringify({ _csrf: csrf }));
  }
}());
</script>
