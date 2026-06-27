<?php
$esc   = fn(mixed $v): string => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
$isEdit= isset($quote['id']);
$v     = fn(string $k, mixed $default = '') => $old[$k] ?? ($quote[$k] ?? $default);

$selOpts = function(array $list, mixed $selectedId, string $placeholder = '— None —') use ($esc): void {
    echo '<option value="">' . $esc($placeholder) . '</option>';
    foreach ($list as $item) {
        $sel = (string)($selectedId ?? '') === (string)$item['id'] ? ' selected' : '';
        echo '<option value="' . $esc($item['id']) . '"' . $sel . '>' . $esc($item['name']) . '</option>';
    }
};

$selectedFeatures = [];
if ($isEdit && $quote['features_json']) {
    $selectedFeatures = json_decode($quote['features_json'], true) ?? [];
}
if (!empty($old['feature_ids'])) {
    $selectedFeatures = array_map('intval', (array)$old['feature_ids']);
}
?>

<div class="page-header">
  <div>
    <nav class="breadcrumb">
      <a href="/door-showroom/admin/quotes">Quotes</a>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
      <?php if ($isEdit): ?>
        <a href="/door-showroom/admin/quotes/<?= (int)$quote['id'] ?>"><?= $esc($quote['reference']) ?></a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
      <?php endif; ?>
      <span><?= $isEdit ? 'Edit' : 'New Quote' ?></span>
    </nav>
    <h1 class="page-title"><?= $isEdit ? 'Edit Quote ' . $esc($quote['reference']) : 'New Quote Request' ?></h1>
  </div>
</div>

<?php if (!empty($errors)): ?>
<div class="flash flash--error" style="margin-bottom:20px">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
  Please fix the errors below.
  <button class="flash-close" onclick="this.parentElement.remove()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
  </button>
</div>
<?php endif; ?>

<form method="POST" action="<?= $esc($formAction) ?>">
  <input type="hidden" name="_csrf" value="<?= $esc($csrfToken) ?>">

  <div class="form-layout">
    <div>

      <div class="form-card">
        <div class="form-card-header">
          <h2>Customer Information</h2>
          <p>Contact details of the person requesting the quote.</p>
        </div>
        <div class="form-card-body">

          <div class="form-row-2">
            <div class="form-field <?= isset($errors['customer_name']) ? 'has-error' : '' ?>">
              <label for="customer_name">Full Name <span class="required">*</span></label>
              <input type="text" id="customer_name" name="customer_name"
                     value="<?= $esc($v('customer_name')) ?>" maxlength="120" required>
              <?php if (isset($errors['customer_name'])): ?><span class="form-error"><?= $esc($errors['customer_name']) ?></span><?php endif; ?>
            </div>
            <div class="form-field <?= isset($errors['customer_phone']) ? 'has-error' : '' ?>">
              <label for="customer_phone">Phone <span class="required">*</span></label>
              <input type="tel" id="customer_phone" name="customer_phone"
                     value="<?= $esc($v('customer_phone')) ?>" maxlength="30" required>
              <?php if (isset($errors['customer_phone'])): ?><span class="form-error"><?= $esc($errors['customer_phone']) ?></span><?php endif; ?>
            </div>
          </div>

          <div class="form-row-2">
            <div class="form-field <?= isset($errors['customer_email']) ? 'has-error' : '' ?>">
              <label for="customer_email">Email</label>
              <input type="email" id="customer_email" name="customer_email"
                     value="<?= $esc($v('customer_email')) ?>" maxlength="180">
              <?php if (isset($errors['customer_email'])): ?><span class="form-error"><?= $esc($errors['customer_email']) ?></span><?php endif; ?>
            </div>
            <div class="form-field <?= isset($errors['customer_city']) ? 'has-error' : '' ?>">
              <label for="customer_city">City</label>
              <input type="text" id="customer_city" name="customer_city"
                     value="<?= $esc($v('customer_city')) ?>" maxlength="100">
              <?php if (isset($errors['customer_city'])): ?><span class="form-error"><?= $esc($errors['customer_city']) ?></span><?php endif; ?>
            </div>
          </div>

          <div class="form-field <?= isset($errors['notes']) ? 'has-error' : '' ?>">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="4" placeholder="Customer requirements, special requests…"><?= $esc($v('notes')) ?></textarea>
            <?php if (isset($errors['notes'])): ?><span class="form-error"><?= $esc($errors['notes']) ?></span><?php endif; ?>
          </div>

        </div>
      </div>

      <div class="form-card">
        <div class="form-card-header">
          <h2>Door Configuration</h2>
          <p>All fields are optional — fill what is known at this stage.</p>
        </div>
        <div class="form-card-body">

          <div class="form-row-2">
            <div class="form-field">
              <label for="product_id">Product</label>
              <select id="product_id" name="product_id" class="form-select">
                <?= $selOpts($selects['products'], $v('product_id')) ?>
              </select>
            </div>
            <div class="form-field">
              <label for="door_type_id">Door Type</label>
              <select id="door_type_id" name="door_type_id" class="form-select">
                <?= $selOpts($selects['doorTypes'], $v('door_type_id')) ?>
              </select>
            </div>
          </div>

          <div class="form-row-2">
            <div class="form-field">
              <label for="material_id">Material</label>
              <select id="material_id" name="material_id" class="form-select">
                <?= $selOpts($selects['materials'], $v('material_id')) ?>
              </select>
            </div>
          </div>

          <div class="form-row-2">
            <div class="form-field">
              <label for="color_id">Color</label>
              <select id="color_id" name="color_id" class="form-select">
                <option value="">— None —</option>
                <?php foreach ($selects['colors'] as $c):
                    $sel = (string)$v('color_id') === (string)$c['id'] ? ' selected' : '';
                ?>
                <option value="<?= $esc($c['id']) ?>"<?= $sel ?>>
                  <?= $esc($c['name']) ?><?= $c['hex'] ? ' (' . $c['hex'] . ')' : '' ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-field <?= isset($errors['handle']) ? 'has-error' : '' ?>">
              <label for="handle">Handle</label>
              <input type="text" id="handle" name="handle"
                     value="<?= $esc($v('handle')) ?>" maxlength="120" placeholder="e.g. Brushed Chrome Lever">
              <?php if (isset($errors['handle'])): ?><span class="form-error"><?= $esc($errors['handle']) ?></span><?php endif; ?>
            </div>
          </div>

          <div class="form-section-label">Dimensions (mm)</div>
          <div class="form-row-2">
            <div class="form-field <?= isset($errors['width_mm']) ? 'has-error' : '' ?>">
              <label for="width_mm">Width (mm)</label>
              <input type="number" id="width_mm" name="width_mm"
                     value="<?= $esc($v('width_mm')) ?>" min="100" max="6000" placeholder="e.g. 900">
              <?php if (isset($errors['width_mm'])): ?><span class="form-error"><?= $esc($errors['width_mm']) ?></span><?php endif; ?>
            </div>
            <div class="form-field <?= isset($errors['height_mm']) ? 'has-error' : '' ?>">
              <label for="height_mm">Height (mm)</label>
              <input type="number" id="height_mm" name="height_mm"
                     value="<?= $esc($v('height_mm')) ?>" min="100" max="6000" placeholder="e.g. 2100">
              <?php if (isset($errors['height_mm'])): ?><span class="form-error"><?= $esc($errors['height_mm']) ?></span><?php endif; ?>
            </div>
          </div>

          <?php if (!empty($selects['features'])): ?>
          <div class="form-section-label">Optional Features</div>
          <div class="qr-edit-features">
            <?php foreach ($selects['features'] as $f):
                $checked = in_array((int)$f['id'], $selectedFeatures, true) ? ' checked' : '';
            ?>
            <label class="qr-edit-feature-item">
              <input type="checkbox" name="feature_ids[]" value="<?= (int)$f['id'] ?>"<?= $checked ?>>
              <span><?= $esc($f['name']) ?></span>
              <span class="qr-feature-price">
                <?php if ($f['price_type'] === 'percent'): ?>
                  +<?= (float)$f['price'] ?>%
                <?php else: ?>
                  +<?= number_format((float)$f['price'], 2) ?> DZD
                <?php endif; ?>
              </span>
            </label>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <div class="form-field <?= isset($errors['final_price']) ? 'has-error' : '' ?>">
            <label for="final_price">Final Price (DZD)</label>
            <input type="number" id="final_price" name="final_price"
                   value="<?= $esc($v('final_price')) ?>" min="0" step="0.01"
                   placeholder="Leave blank if not yet determined">
            <span class="form-hint">Override the calculated price or enter a negotiated amount.</span>
            <?php if (isset($errors['final_price'])): ?><span class="form-error"><?= $esc($errors['final_price']) ?></span><?php endif; ?>
          </div>

        </div>
      </div>

      <div class="form-actions">
        <?php if ($isEdit): ?>
          <a href="/door-showroom/admin/quotes/<?= (int)$quote['id'] ?>" class="btn btn-outline">Cancel</a>
        <?php else: ?>
          <a href="/door-showroom/admin/quotes" class="btn btn-outline">Cancel</a>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          <?= $isEdit ? 'Save Changes' : 'Create Quote' ?>
        </button>
      </div>

    </div>

    <div class="form-sidebar">
      <?php if ($isEdit): ?>
      <div class="info-card">
        <h3>Quote Details</h3>
        <dl class="info-list">
          <dt>Reference</dt> <dd><code><?= $esc($quote['reference']) ?></code></dd>
          <dt>Status</dt>    <dd><?= $esc(ucfirst(str_replace('_', ' ', $quote['status']))) ?></dd>
          <dt>Created</dt>   <dd><?= date('d/m/Y', strtotime($quote['submitted_at'])) ?></dd>
        </dl>
      </div>
      <?php endif; ?>
      <div class="info-card">
        <h3>Tips</h3>
        <div class="pr-how-list">
          <div class="pr-how-item">
            <span class="pr-how-num">1</span>
            <span>Fill as much configuration detail as is known.</span>
          </div>
          <div class="pr-how-item">
            <span class="pr-how-num">2</span>
            <span>Leave Final Price blank to let pricing rules calculate it later.</span>
          </div>
          <div class="pr-how-item">
            <span class="pr-how-num">3</span>
            <span>Use the Status workflow on the detail page to advance through stages.</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>
