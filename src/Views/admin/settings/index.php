<?php
$e   = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$get = fn(string $k): string => $e($settings[$k] ?? '');
$groupLabels = [
    'general'       => 'General',
    'pricing'       => 'Pricing',
    'notifications' => 'Notifications',
    'advanced'      => 'Advanced',
];
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Settings</h1>
    <p class="page-sub">Platform configuration</p>
  </div>
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

<form method="POST" action="/door-showroom/admin/settings/update" id="settingsForm">
  <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>" />

  <?php foreach ($groups as $groupKey => $rows): ?>
    <div class="form-card" style="margin-bottom:20px">
      <div class="form-card-header">
        <h2><?= $e($groupLabels[$groupKey] ?? ucfirst($groupKey)) ?></h2>
      </div>
      <div class="form-card-body">
        <?php foreach ($rows as $row): ?>
          <?php $key = $row['setting_key']; ?>
          <?php if ($key === 'vat_percent') continue; // VAT removed — not used in pricing ?>

          <?php if ($key === 'maintenance_mode' || $key === 'quote_email_notify'): ?>
            <div class="form-field">
              <div class="toggle-wrap">
                <label class="toggle" for="<?= $e($key) ?>">
                  <input type="checkbox" id="<?= $e($key) ?>" name="<?= $e($key) ?>" value="1"
                    <?= ($settings[$key] ?? '0') === '1' ? 'checked' : '' ?> />
                  <span class="toggle-track"></span>
                </label>
                <div class="toggle-label">
                  <strong><?= $e($row['label']) ?></strong>
                  <?php if ($key === 'maintenance_mode'): ?>
                    <span>Displays a maintenance page to visitors</span>
                  <?php elseif ($key === 'quote_email_notify'): ?>
                    <span>Send an email when a new quote is submitted</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>

          <?php elseif ($key === 'contact_address'): ?>
            <div class="form-field">
              <label for="<?= $e($key) ?>"><?= $e($row['label']) ?></label>
              <textarea id="<?= $e($key) ?>" name="<?= $e($key) ?>" rows="3"><?= $get($key) ?></textarea>
            </div>

          <?php else: ?>
            <div class="form-field">
              <label for="<?= $e($key) ?>"><?= $e($row['label']) ?></label>
              <input type="<?= in_array($key, ['contact_email', 'notification_email']) ? 'email' : 'text' ?>"
                id="<?= $e($key) ?>" name="<?= $e($key) ?>" value="<?= $get($key) ?>"
                autocomplete="off" />
            </div>
          <?php endif; ?>

        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary" id="submitBtn">
      <span id="submitLabel">Save Settings</span>
      <span class="btn-spinner-sm" id="submitSpinner" hidden></span>
    </button>
  </div>

</form>

<script>
(function () {
  var form = document.getElementById('settingsForm');
  var btn = document.getElementById('submitBtn');
  var label = document.getElementById('submitLabel');
  var spin = document.getElementById('submitSpinner');
  if (form) {
    form.addEventListener('submit', function () {
      btn.disabled = true;
      label.textContent = 'Saving…';
      spin.hidden = false;
    });
  }
}());
</script>
