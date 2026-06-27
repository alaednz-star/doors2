<?php
$e      = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$isEdit = ($rule ?? null) !== null;
$val = function (string $k, string $d = '') use ($old, $rule): string {
    if (!empty($old)) return (string)($old[$k] ?? $d);
    if ($rule)        return (string)($rule[$k] ?? $d);
    return $d;
};
$sel = function (string $k) use ($old, $rule): string {
    if (!empty($old)) return (string)($old[$k] ?? '');
    if ($rule)        return (string)($rule[$k] ?? '');
    return '';
};
$chk = function (string $k, int $d = 1) use ($old, $rule): bool {
    if (!empty($old)) return !empty($old[$k]);
    if ($rule)        return (bool)($rule[$k] ?? $d);
    return (bool)$d;
};
$err = fn(string $k): string => $errors[$k] ?? '';
?>

<div class="page-header">
  <div>
    <div class="breadcrumb">
      <a href="/door-showroom/admin/pricing">Pricing Matrix</a>
      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
      <span><?= $isEdit ? 'Edit' : 'New' ?> Rule</span>
    </div>
    <h1 class="page-title"><?= $isEdit ? 'Edit' : 'Add' ?> Price Rule</h1>
  </div>
</div>

<div class="form-layout">
  <form method="POST" action="<?= $e($formAction) ?>" id="pricingForm" novalidate>
    <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>" />

    <div class="form-card">
      <div class="form-card-header">
        <h2>Combination</h2>
        <p>Each combination of collection, usage and construction has one price.</p>
      </div>
      <div class="form-card-body">

        <div class="form-field <?= $err('collection_id') ? 'has-error' : '' ?>">
          <label for="collection_id">Collection <span class="required">*</span></label>
          <select id="collection_id" name="collection_id" required>
            <option value="">— Select —</option>
            <?php foreach ($selects['collections'] as $c): ?>
              <option value="<?= (int)$c['id'] ?>" <?= (string)$c['id'] === $sel('collection_id') ? 'selected' : '' ?>><?= $e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if ($err('collection_id')): ?><span class="form-error"><?= $e($err('collection_id')) ?></span><?php endif; ?>
        </div>

        <div class="form-field <?= $err('door_type_id') ? 'has-error' : '' ?>">
          <label for="door_type_id">Door Usage <span class="required">*</span></label>
          <select id="door_type_id" name="door_type_id" required>
            <option value="">— Select —</option>
            <?php foreach ($selects['usages'] as $u): ?>
              <option value="<?= (int)$u['id'] ?>" <?= (string)$u['id'] === $sel('door_type_id') ? 'selected' : '' ?>><?= $e($u['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if ($err('door_type_id')): ?><span class="form-error"><?= $e($err('door_type_id')) ?></span><?php endif; ?>
        </div>

        <div class="form-field <?= $err('construction_type_id') ? 'has-error' : '' ?>">
          <label for="construction_type_id">Construction Type <span class="required">*</span></label>
          <select id="construction_type_id" name="construction_type_id" required>
            <option value="">— Select —</option>
            <?php foreach ($selects['constructions'] as $ct): ?>
              <option value="<?= (int)$ct['id'] ?>" <?= (string)$ct['id'] === $sel('construction_type_id') ? 'selected' : '' ?>><?= $e($ct['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if ($err('construction_type_id')): ?><span class="form-error"><?= $e($err('construction_type_id')) ?></span><?php endif; ?>
        </div>

      </div>
    </div>

    <div class="form-card">
      <div class="form-card-header">
        <h2>Price &amp; Availability</h2>
        <p>Base price is for the reference door size (90 × 210 cm); dimensions scale it automatically.</p>
      </div>
      <div class="form-card-body">

        <div class="form-field">
          <label class="checkbox-item">
            <input type="checkbox" name="is_available" id="is_available" value="1" <?= $chk('is_available') ? 'checked' : '' ?> />
            <span>Available for ordering</span>
          </label>
          <span class="form-hint">Unchecked = shown as “Non disponible” in the configurator.</span>
        </div>

        <div class="form-field <?= $err('base_price') ? 'has-error' : '' ?>" id="priceField">
          <label for="base_price">Base Price (DZD)</label>
          <input type="number" id="base_price" name="base_price" value="<?= $e($val('base_price')) ?>" min="0" step="100" placeholder="e.g. 34000" />
          <?php if ($err('base_price')): ?><span class="form-error"><?= $e($err('base_price')) ?></span><?php endif; ?>
        </div>

        <div class="form-field">
          <label class="checkbox-item">
            <input type="checkbox" name="is_active" value="1" <?= $chk('is_active') ? 'checked' : '' ?> />
            <span>Active (rule applied)</span>
          </label>
        </div>

      </div>
    </div>

    <div class="form-actions">
      <a href="/door-showroom/admin/pricing" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save Changes' : 'Create Rule' ?></button>
    </div>
  </form>
</div>

<script>
(function () {
  var avail = document.getElementById('is_available'), priceField = document.getElementById('priceField');
  function sync(){ priceField.style.opacity = avail.checked ? '1' : '.45'; document.getElementById('base_price').disabled = !avail.checked; }
  if (avail) { avail.addEventListener('change', sync); sync(); }
}());
</script>
