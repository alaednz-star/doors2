<?php
$e      = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$isEdit = $color !== null;
$val    = function (string $key, string $default = '') use ($old, $color): string {
    if (!empty($old)) return (string)($old[$key] ?? $default);
    if ($color)       return (string)($color[$key] ?? $default);
    return $default;
};
$checked = function (string $key, int $default = 1) use ($old, $color): bool {
    if (!empty($old)) return !empty($old[$key]);
    if ($color)       return (bool)$color[$key];
    return (bool)$default;
};
$err = fn(string $k): string => $errors[$k] ?? '';
?>

<div class="page-header">
  <div>
    <div class="breadcrumb">
      <a href="/door-showroom/admin/colors">Colors</a>
      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
      <span><?= $isEdit ? $e($color['name']) : 'New Color' ?></span>
    </div>
    <h1 class="page-title"><?= $isEdit ? 'Edit Color' : 'Add Color' ?></h1>
  </div>
</div>

<div class="form-layout">

  <form method="POST" action="<?= $e($formAction) ?>" enctype="multipart/form-data" id="colorForm" novalidate>
    <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>" />

    <div class="form-card">
      <div class="form-card-header">
        <h2>Color Details</h2>
        <p>Name and hex value for this color option.</p>
      </div>
      <div class="form-card-body">

        <div class="form-field <?= $err('name') ? 'has-error' : '' ?>">
          <label for="name">Color Name <span class="required">*</span></label>
          <input type="text" id="name" name="name" value="<?= $e($val('name')) ?>" placeholder="e.g. Marron Prestige" maxlength="80" required autocomplete="off" />
          <?php if ($err('name')): ?><span class="form-error"><?= $e($err('name')) ?></span><?php endif; ?>
        </div>

        <?php
          $selColl = !empty($old) ? ($old['collection_id'] ?? '') : (string)($color['collection_id'] ?? '');
        ?>
        <div class="form-field">
          <label for="collection_id">Collection</label>
          <select id="collection_id" name="collection_id">
            <option value="">— No collection —</option>
            <?php foreach (($collections ?? []) as $coll): ?>
              <option value="<?= (int)$coll['id'] ?>" <?= (string)$coll['id'] === (string)$selColl ? 'selected' : '' ?>><?= $e($coll['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <span class="form-hint">Which collection this colour belongs to.</span>
        </div>

        <div class="form-field <?= $err('hex') ? 'has-error' : '' ?>" style="display:flex;gap:12px;align-items:flex-end">
          <div style="flex:1">
            <label for="hex">Hex Color</label>
            <input type="text" id="hex" name="hex" value="<?= $e($val('hex')) ?>" placeholder="#2C2C2C" maxlength="7" autocomplete="off" style="font-family:monospace" />
            <?php if ($err('hex')): ?>
              <span class="form-error"><?= $e($err('hex')) ?></span>
            <?php else: ?>
              <span class="form-hint">Optional. Use #RRGGBB format.</span>
            <?php endif; ?>
          </div>
          <div>
            <input type="color" id="colorPicker" style="width:44px;height:40px;border:1px solid #d4d4d4;border-radius:8px;cursor:pointer;padding:2px" title="Pick a color" />
          </div>
        </div>

        <div class="form-field <?= $err('description') ? 'has-error' : '' ?>">
          <label for="description">Description</label>
          <textarea id="description" name="description" rows="3" placeholder="Optional description…" maxlength="1000"><?= $e($val('description')) ?></textarea>
          <?php if ($err('description')): ?><span class="form-error"><?= $e($err('description')) ?></span><?php endif; ?>
        </div>

      </div>
    </div>

    <div class="form-card">
      <div class="form-card-header">
        <h2>Imagery</h2>
        <p>Preview swatch and a seamless texture image (JPG/PNG/WebP).</p>
      </div>
      <div class="form-card-body">
        <div class="form-field">
          <label for="color_image">Preview Image</label>
          <?php if ($isEdit && !empty($color['image_filename'])): ?>
            <div style="margin-bottom:10px"><img src="/door-showroom/uploads/colors/<?= $e($color['image_filename']) ?>" alt="" style="max-width:100px;border-radius:8px;border:1px solid var(--border,#e5e0d8)" /></div>
          <?php endif; ?>
          <input type="file" id="color_image" name="color_image" accept="image/jpeg,image/png,image/webp" />
          <span class="form-hint">Shown as the swatch / chip.</span>
        </div>
        <div class="form-field">
          <label for="texture_image">Texture Image</label>
          <?php if ($isEdit && !empty($color['texture_filename'])): ?>
            <div style="margin-bottom:10px"><img src="/door-showroom/uploads/colors/<?= $e($color['texture_filename']) ?>" alt="" style="max-width:100px;border-radius:8px;border:1px solid var(--border,#e5e0d8)" /></div>
          <?php endif; ?>
          <input type="file" id="texture_image" name="texture_image" accept="image/jpeg,image/png,image/webp" />
          <span class="form-hint">Used to render the door surface in the configurator.</span>
        </div>
      </div>
    </div>

    <?php if (!empty($products)): ?>
    <div class="form-card">
      <div class="form-card-header">
        <h2>Assigned Products</h2>
        <p>Products where this color is available.</p>
      </div>
      <div class="form-card-body">
        <div class="checkbox-grid">
          <?php foreach ($products as $product): ?>
            <label class="checkbox-item">
              <input type="checkbox" name="product_ids[]" value="<?= (int)$product['id'] ?>"
                <?= in_array($product['id'], $assigned ?? []) ? 'checked' : '' ?> />
              <span><?= $e($product['name']) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <div class="form-card">
      <div class="form-card-header">
        <h2>Settings</h2>
      </div>
      <div class="form-card-body">

        <div class="form-field form-field--sm <?= $err('display_order') ? 'has-error' : '' ?>">
          <label for="display_order">Display Order</label>
          <input type="number" id="display_order" name="display_order" value="<?= $e($val('display_order', '0')) ?>" min="0" max="9999" style="max-width:120px" />
          <?php if ($err('display_order')): ?>
            <span class="form-error"><?= $e($err('display_order')) ?></span>
          <?php else: ?>
            <span class="form-hint">Lower numbers appear first.</span>
          <?php endif; ?>
        </div>

        <div class="form-field">
          <div class="toggle-wrap">
            <label class="toggle" for="is_active">
              <input type="checkbox" id="is_active" name="is_active" value="1" <?= $checked('is_active') ? 'checked' : '' ?> />
              <span class="toggle-track"></span>
            </label>
            <div class="toggle-label"><strong>Active</strong><span>Available in the configurator</span></div>
          </div>
        </div>

      </div>
    </div>

    <div class="form-actions">
      <a href="/door-showroom/admin/colors" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary" id="submitBtn">
        <span id="submitLabel"><?= $isEdit ? 'Save Changes' : 'Create Color' ?></span>
        <span class="btn-spinner-sm" id="submitSpinner" hidden></span>
      </button>
    </div>

  </form>

  <?php if ($isEdit): ?>
  <div class="form-sidebar">
    <div class="info-card">
      <h3>Color Info</h3>
      <dl class="info-list">
        <dt>ID</dt><dd>#<?= (int)$color['id'] ?></dd>
        <?php if ($color['hex']): ?>
          <dt>Hex</dt><dd style="display:flex;align-items:center;gap:6px"><span style="display:inline-block;width:16px;height:16px;border-radius:3px;background:<?= $e($color['hex']) ?>;border:1px solid rgba(0,0,0,.15)"></span><code><?= $e($color['hex']) ?></code></dd>
        <?php endif; ?>
        <dt>Status</dt>
        <dd><span class="status-badge status-badge--<?= $color['is_active'] ? 'active' : 'inactive' ?>"><?= $color['is_active'] ? 'Active' : 'Inactive' ?></span></dd>
      </dl>
    </div>
    <div class="danger-zone">
      <h3>Danger Zone</h3>
      <p>Deleting this color is permanent.</p>
      <button class="btn btn-danger" id="sidebarDeleteBtn" data-id="<?= (int)$color['id'] ?>" data-name="<?= $e($color['name']) ?>">Delete Color</button>
    </div>
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
    <h3>Delete Color</h3>
    <p>Are you sure? This cannot be undone.</p>
    <div class="modal-actions">
      <button class="btn btn-outline" id="modalCancel">Cancel</button>
      <button class="btn btn-danger" id="modalConfirm">Delete</button>
    </div>
  </div>
</div>

<script>
(function () {
  var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  var hexInput    = document.getElementById('hex');
  var colorPicker = document.getElementById('colorPicker');

  if (hexInput && colorPicker) {
    if (hexInput.value) { try { colorPicker.value = hexInput.value; } catch(e) {} }
    hexInput.addEventListener('input', function () {
      if (/^#[0-9A-Fa-f]{6}$/.test(hexInput.value)) { try { colorPicker.value = hexInput.value; } catch(e) {} }
    });
    colorPicker.addEventListener('input', function () {
      hexInput.value = colorPicker.value.toUpperCase();
    });
  }

  var form = document.getElementById('colorForm');
  var submitBtn = document.getElementById('submitBtn');
  var submitLabel = document.getElementById('submitLabel');
  var submitSpin = document.getElementById('submitSpinner');
  if (form) {
    form.addEventListener('submit', function () { submitBtn.disabled = true; submitLabel.textContent = 'Saving…'; submitSpin.hidden = false; });
  }

  var deleteBtn = document.getElementById('sidebarDeleteBtn');
  var modal = document.getElementById('deleteModal');
  var cancelBtn = document.getElementById('modalCancel');
  var confirmBtn = document.getElementById('modalConfirm');

  if (deleteBtn) { deleteBtn.addEventListener('click', function () { modal.hidden = false; }); }
  if (cancelBtn) { cancelBtn.addEventListener('click', function () { modal.hidden = true; }); }
  if (modal) {
    modal.addEventListener('click', function (e) { if (e.target === modal) modal.hidden = true; });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') modal.hidden = true; });
  }

  if (confirmBtn && deleteBtn) {
    confirmBtn.addEventListener('click', function () {
      var id = deleteBtn.dataset.id;
      confirmBtn.disabled = true; confirmBtn.textContent = 'Deleting…';
      var x = new XMLHttpRequest();
      x.open('POST', '/door-showroom/admin/colors/' + id + '/delete', true);
      x.setRequestHeader('Content-Type', 'application/json');
      x.setRequestHeader('X-CSRF-Token', csrf);
      x.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      x.onreadystatechange = function () {
        if (x.readyState !== 4) return;
        try {
          var data = JSON.parse(x.responseText);
          if (data.success) { window.location.href = '/door-showroom/admin/colors'; }
          else { alert(data.message || 'Delete failed.'); confirmBtn.disabled = false; confirmBtn.textContent = 'Delete'; }
        } catch(e) { alert('Unexpected error.'); }
      };
      x.send(JSON.stringify({ _csrf: csrf }));
    });
  }
}());
</script>
