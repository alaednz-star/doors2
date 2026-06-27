<?php
$e        = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$isEdit   = $category !== null;
$val      = function (string $key, string $default = '') use ($old, $category): string {
    if (!empty($old)) return (string)($old[$key] ?? $default);
    if ($category)    return (string)($category[$key] ?? $default);
    return $default;
};
$checked  = function (string $key, int $default = 1) use ($old, $category): bool {
    if (!empty($old)) return !empty($old[$key]);
    if ($category)    return (bool)$category[$key];
    return (bool)$default;
};
$err      = fn(string $k): string => $errors[$k] ?? '';
?>

<div class="page-header">
  <div>
    <div class="breadcrumb">
      <a href="/door-showroom/admin/categories">Categories</a>
      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
      <span><?= $isEdit ? $e($category['name']) : 'New Category' ?></span>
    </div>
    <h1 class="page-title"><?= $isEdit ? 'Edit Category' : 'Add Category' ?></h1>
  </div>
</div>

<div class="form-layout">

  <form method="POST" action="<?= $e($formAction) ?>" id="categoryForm" novalidate>
    <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>" />

    <div class="form-card">
      <div class="form-card-header">
        <h2>Category Details</h2>
        <p>Basic information about this door category.</p>
      </div>
      <div class="form-card-body">

        <!-- Name -->
        <div class="form-field <?= $err('name') ? 'has-error' : '' ?>">
          <label for="name">
            Category Name <span class="required">*</span>
          </label>
          <input
            type="text"
            id="name"
            name="name"
            value="<?= $e($val('name')) ?>"
            placeholder="e.g. Exterior Doors"
            maxlength="120"
            required
            autocomplete="off"
          />
          <?php if ($err('name')): ?>
            <span class="form-error"><?= $e($err('name')) ?></span>
          <?php endif; ?>
          <div class="slug-preview" id="slugPreview" <?= $isEdit ? '' : 'hidden' ?>>
            Slug: <code id="slugValue"><?= $isEdit ? $e($category['slug']) : '' ?></code>
          </div>
        </div>

        <!-- Description -->
        <div class="form-field <?= $err('description') ? 'has-error' : '' ?>">
          <label for="description">Description</label>
          <textarea
            id="description"
            name="description"
            rows="4"
            placeholder="Brief description of this category…"
            maxlength="2000"
          ><?= $e($val('description')) ?></textarea>
          <?php if ($err('description')): ?>
            <span class="form-error"><?= $e($err('description')) ?></span>
          <?php else: ?>
            <span class="form-hint">Optional. Shown on the public collections page.</span>
          <?php endif; ?>
        </div>

      </div>
    </div>

    <div class="form-card">
      <div class="form-card-header">
        <h2>Settings</h2>
        <p>Display order and visibility.</p>
      </div>
      <div class="form-card-body">

        <!-- Display order -->
        <div class="form-field form-field--sm <?= $err('display_order') ? 'has-error' : '' ?>">
          <label for="display_order">Display Order</label>
          <input
            type="number"
            id="display_order"
            name="display_order"
            value="<?= $e($val('display_order', '0')) ?>"
            min="0"
            max="9999"
            style="max-width:120px"
          />
          <?php if ($err('display_order')): ?>
            <span class="form-error"><?= $e($err('display_order')) ?></span>
          <?php else: ?>
            <span class="form-hint">Lower numbers appear first. Use 0 for default.</span>
          <?php endif; ?>
        </div>

        <!-- Active -->
        <div class="form-field">
          <div class="toggle-wrap">
            <label class="toggle" for="is_active">
              <input
                type="checkbox"
                id="is_active"
                name="is_active"
                value="1"
                <?= $checked('is_active') ? 'checked' : '' ?>
              />
              <span class="toggle-track"></span>
            </label>
            <div class="toggle-label">
              <strong>Active</strong>
              <span>Visible on the public website</span>
            </div>
          </div>
        </div>

      </div>
    </div>

    <div class="form-actions">
      <a href="/door-showroom/admin/categories" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary" id="submitBtn">
        <span id="submitLabel"><?= $isEdit ? 'Save Changes' : 'Create Category' ?></span>
        <span class="btn-spinner-sm" id="submitSpinner" hidden></span>
      </button>
    </div>

  </form>

  <?php if ($isEdit): ?>
  <div class="form-sidebar">
    <div class="info-card">
      <h3>Category Info</h3>
      <dl class="info-list">
        <dt>ID</dt>
        <dd>#<?= (int)$category['id'] ?></dd>
        <dt>Slug</dt>
        <dd><code><?= $e($category['slug']) ?></code></dd>
        <dt>Created</dt>
        <dd><?= date('d M Y, H:i', strtotime($category['created_at'])) ?></dd>
        <dt>Last updated</dt>
        <dd><?= date('d M Y, H:i', strtotime($category['updated_at'])) ?></dd>
        <dt>Status</dt>
        <dd>
          <span class="status-badge status-badge--<?= $category['is_active'] ? 'active' : 'inactive' ?>">
            <?= $category['is_active'] ? 'Active' : 'Inactive' ?>
          </span>
        </dd>
      </dl>
    </div>

    <div class="danger-zone">
      <h3>Danger Zone</h3>
      <p>Deleting this category is permanent and cannot be undone.</p>
      <button
        class="btn btn-danger"
        id="sidebarDeleteBtn"
        data-id="<?= (int)$category['id'] ?>"
        data-name="<?= $e($category['name']) ?>"
      >
        Delete Category
      </button>
    </div>
  </div>
  <?php endif; ?>

</div>

<!-- Delete modal (reused from index, inline for form page) -->
<div class="modal-backdrop" id="deleteModal" hidden>
  <div class="modal" role="dialog" aria-modal="true">
    <div class="modal-icon modal-icon--danger">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
      </svg>
    </div>
    <h3>Delete Category</h3>
    <p>Are you sure you want to delete <strong id="modalCatName"><?= $isEdit ? $e($category['name']) : '' ?></strong>? This cannot be undone.</p>
    <div class="modal-actions">
      <button class="btn btn-outline" id="modalCancel">Cancel</button>
      <button class="btn btn-danger" id="modalConfirm">Delete</button>
    </div>
  </div>
</div>

<script>
(function () {
  var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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
      if (s) {
        slugValue.textContent = s;
        slugPreview.hidden = false;
      } else {
        slugPreview.hidden = true;
      }
    });
  }

  /* ── Form submit loading ── */
  var form        = document.getElementById('categoryForm');
  var submitBtn   = document.getElementById('submitBtn');
  var submitLabel = document.getElementById('submitLabel');
  var submitSpin  = document.getElementById('submitSpinner');

  if (form) {
    form.addEventListener('submit', function () {
      submitBtn.disabled     = true;
      submitLabel.textContent = 'Saving…';
      submitSpin.hidden       = false;
    });
  }

  /* ── Delete (sidebar) ── */
  var deleteBtn = document.getElementById('sidebarDeleteBtn');
  var modal     = document.getElementById('deleteModal');
  var cancelBtn = document.getElementById('modalCancel');
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

      var x = new XMLHttpRequest();
      x.open('POST', '/door-showroom/admin/categories/' + id + '/delete', true);
      x.setRequestHeader('Content-Type', 'application/json');
      x.setRequestHeader('X-CSRF-Token', csrf);
      x.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      x.onreadystatechange = function () {
        if (x.readyState !== 4) return;
        try {
          var data = JSON.parse(x.responseText);
          if (data.success) {
            window.location.href = '/door-showroom/admin/categories';
          } else {
            alert(data.message || 'Delete failed.');
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Delete';
          }
        } catch(e) {
          alert('Unexpected error.');
        }
      };
      x.send(JSON.stringify({ _csrf: csrf }));
    });
  }
}());
</script>
