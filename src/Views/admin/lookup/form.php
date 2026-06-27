<?php
$e      = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$isEdit = ($row ?? null) !== null;
$base   = $meta['routeBase'];
$sing   = $meta['singular'];
$plur   = $meta['plural'];
$hasImg   = !empty($meta['hasImage']);
$imgWeb   = $meta['imageWebPath'] ?? '';
$imgField = $meta['imageField'] ?? 'image';

$val = function (string $k, string $d = '') use ($old, $row): string {
    if (!empty($old)) return (string)($old[$k] ?? $d);
    if ($row)         return (string)($row[$k] ?? $d);
    return $d;
};
$checked = function (string $k, int $d = 1) use ($old, $row): bool {
    if (!empty($old)) return !empty($old[$k]);
    if ($row)         return (bool)$row[$k];
    return (bool)$d;
};
$err = fn(string $k): string => $errors[$k] ?? '';
?>

<div class="page-header">
  <div>
    <div class="breadcrumb">
      <a href="<?= $e($base) ?>"><?= $e($plur) ?></a>
      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
      <span><?= $isEdit ? $e($row['name']) : ('New ' . $e($sing)) ?></span>
    </div>
    <h1 class="page-title"><?= $isEdit ? ('Edit ' . $e($sing)) : ('Add ' . $e($sing)) ?></h1>
  </div>
</div>

<div class="form-layout">
  <form method="POST" action="<?= $e($formAction) ?>" enctype="multipart/form-data" id="lookupForm" novalidate>
    <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>" />

    <div class="form-card">
      <div class="form-card-header">
        <h2><?= $e($sing) ?> Details</h2>
        <p>Name, description and display order.</p>
      </div>
      <div class="form-card-body">

        <div class="form-field <?= $err('name') ? 'has-error' : '' ?>">
          <label for="name"><?= $e($sing) ?> Name <span class="required">*</span></label>
          <input type="text" id="name" name="name" value="<?= $e($val('name')) ?>" maxlength="100" required autocomplete="off" />
          <?php if ($err('name')): ?><span class="form-error"><?= $e($err('name')) ?></span><?php endif; ?>
          <div class="slug-preview" id="slugPreview" <?= $isEdit ? '' : 'hidden' ?>>Slug: <code id="slugValue"><?= $isEdit ? $e($row['slug']) : '' ?></code></div>
        </div>

        <div class="form-field <?= $err('description') ? 'has-error' : '' ?>">
          <label for="description">Description</label>
          <textarea id="description" name="description" rows="3" maxlength="1000"><?= $e($val('description')) ?></textarea>
          <?php if ($err('description')): ?><span class="form-error"><?= $e($err('description')) ?></span><?php endif; ?>
        </div>

      </div>
    </div>

    <?php if ($hasImg): ?>
    <div class="form-card">
      <div class="form-card-header"><h2>Image</h2><p>Optional preview image (JPG/PNG/WebP).</p></div>
      <div class="form-card-body">
        <?php if ($isEdit && !empty($row['image_filename'])): ?>
          <div style="margin-bottom:12px"><img src="<?= $e($imgWeb) ?>/<?= $e($row['image_filename']) ?>" alt="" style="max-width:120px;border-radius:8px;border:1px solid var(--border,#e5e0d8)" /></div>
        <?php endif; ?>
        <div class="form-field">
          <input type="file" name="<?= $e($imgField) ?>" accept="image/jpeg,image/png,image/webp" />
          <p class="form-hint" style="margin-top:6px;color:var(--muted,#888);font-size:.85em">This is the image shown for this <?= $e(strtolower($sing)) ?> in the configurator.</p>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <div class="form-card">
      <div class="form-card-body">
        <div class="form-row" style="display:flex;gap:24px;flex-wrap:wrap">
          <div class="form-field <?= $err('display_order') ? 'has-error' : '' ?>" style="max-width:160px">
            <label for="display_order">Display Order</label>
            <input type="number" id="display_order" name="display_order" value="<?= $e($val('display_order','0')) ?>" min="0" max="9999" />
            <?php if ($err('display_order')): ?><span class="form-error"><?= $e($err('display_order')) ?></span><?php endif; ?>
          </div>
          <div class="form-field">
            <label class="checkbox-item" style="margin-top:28px">
              <input type="checkbox" name="is_active" value="1" <?= $checked('is_active') ? 'checked' : '' ?> />
              <span>Active</span>
            </label>
          </div>
        </div>
      </div>
    </div>

    <div class="form-actions">
      <a href="<?= $e($base) ?>" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save Changes' : ('Create ' . $e($sing)) ?></button>
    </div>
  </form>
</div>

<script>
(function () {
  var name = document.getElementById('name'), preview = document.getElementById('slugPreview'), val = document.getElementById('slugValue');
  function slugify(s){ return s.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g,'').replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,''); }
  if (name) name.addEventListener('input', function(){ var s = slugify(name.value); if (s){ preview.hidden=false; val.textContent=s; } else preview.hidden=true; });
}());
</script>
