<?php
use App\Validators\QuoteValidator;

$esc = fn(mixed $v): string => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');

$statusMeta = [
    'new'            => ['label' => 'New',            'color' => 'blue'],
    'contacted'      => ['label' => 'Contacted',      'color' => 'gold'],
    'quotation_sent' => ['label' => 'Quotation Sent', 'color' => 'purple'],
    'in_progress'    => ['label' => 'In Progress',    'color' => 'orange'],
    'confirmed'      => ['label' => 'Confirmed',      'color' => 'green'],
    'delivered'      => ['label' => 'Delivered',      'color' => 'teal'],
    'completed'      => ['label' => 'Completed',      'color' => 'ink'],
    'cancelled'      => ['label' => 'Cancelled',      'color' => 'red'],
];

$sm = $statusMeta[$quote['status']] ?? ['label' => $quote['status'], 'color' => 'ink'];
?>

<div class="page-header">
  <div>
    <nav class="breadcrumb">
      <a href="/door-showroom/admin/quotes">Quotes</a>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
      <span><?= $esc($quote['reference']) ?></span>
    </nav>
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-top:4px">
      <h1 class="page-title"><?= $esc($quote['reference']) ?></h1>
      <span class="qr-badge qr-badge--<?= $sm['color'] ?>" id="currentStatusBadge"><?= $sm['label'] ?></span>
    </div>
  </div>
  <div class="page-header-actions">
    <a href="/door-showroom/admin/quotes/<?= (int)$quote['id'] ?>/edit" class="btn btn-outline">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Edit
    </a>
    <a href="/door-showroom/admin/quotes" class="btn btn-outline">Back</a>
  </div>
</div>

<?php if ($flash): ?>
<div class="flash flash--success">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
  <?= $esc($flash) ?>
  <button class="flash-close" onclick="this.parentElement.remove()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
  </button>
</div>
<?php endif; ?>

<?php
// Visual status stepper. The main pipeline is linear; "cancelled" sits off it.
$pipeline = ['new', 'contacted', 'quotation_sent', 'in_progress', 'confirmed', 'delivered', 'completed'];
$curStatus = $quote['status'];
$isCancelled = $curStatus === 'cancelled';
$curIndex = array_search($curStatus, $pipeline, true);
if ($curIndex === false) $curIndex = -1; // cancelled or unknown
?>
<div class="qr-stepper <?= $isCancelled ? 'qr-stepper--cancelled' : '' ?>">
  <?php foreach ($pipeline as $i => $stage):
      $meta  = $statusMeta[$stage] ?? ['label' => $stage];
      $state = $i <  $curIndex ? 'done'
             : ($i === $curIndex ? 'current' : 'todo');
  ?>
    <div class="qr-step qr-step--<?= $state ?>">
      <span class="qr-step-dot">
        <?php if ($state === 'done'): ?>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
        <?php else: ?>
          <?= $i + 1 ?>
        <?php endif; ?>
      </span>
      <span class="qr-step-label"><?= $esc($meta['label']) ?></span>
    </div>
  <?php endforeach; ?>
  <?php if ($isCancelled): ?>
    <div class="qr-step qr-step--cancelled">
      <span class="qr-step-dot">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
      </span>
      <span class="qr-step-label">Cancelled</span>
    </div>
  <?php endif; ?>
</div>

<div class="qr-detail-grid">

  <div class="qr-detail-main">

    <div class="form-card">
      <div class="form-card-header">
        <h2>Customer Information</h2>
      </div>
      <div class="qr-info-grid">
        <div class="qr-info-item">
          <span class="qr-info-label">Full Name</span>
          <span class="qr-info-value"><?= $esc($quote['customer_name']) ?></span>
        </div>
        <div class="qr-info-item">
          <span class="qr-info-label">Phone</span>
          <span class="qr-info-value">
            <a href="tel:<?= $esc($quote['customer_phone']) ?>" class="qr-phone-link"><?= $esc($quote['customer_phone']) ?></a>
          </span>
        </div>
        <?php if ($quote['customer_email']): ?>
        <div class="qr-info-item">
          <span class="qr-info-label">Email</span>
          <span class="qr-info-value">
            <a href="mailto:<?= $esc($quote['customer_email']) ?>" class="qr-phone-link"><?= $esc($quote['customer_email']) ?></a>
          </span>
        </div>
        <?php endif; ?>
        <?php if (!empty($quote['customer_company'])): ?>
        <div class="qr-info-item">
          <span class="qr-info-label">Company</span>
          <span class="qr-info-value"><?= $esc($quote['customer_company']) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($quote['customer_country'])): ?>
        <div class="qr-info-item">
          <span class="qr-info-label">Country</span>
          <span class="qr-info-value"><?= $esc($quote['customer_country']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($quote['customer_city']): ?>
        <div class="qr-info-item">
          <span class="qr-info-label">City</span>
          <span class="qr-info-value"><?= $esc($quote['customer_city']) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($quote['project_type'])): ?>
        <div class="qr-info-item">
          <span class="qr-info-label">Project Type</span>
          <span class="qr-info-value"><?= $esc(ucfirst($quote['project_type'])) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($quote['install_date'])): ?>
        <div class="qr-info-item">
          <span class="qr-info-label">Desired Install Date</span>
          <span class="qr-info-value"><?= $esc($quote['install_date']) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($quote['quantity']) && (int)$quote['quantity'] > 1): ?>
        <div class="qr-info-item">
          <span class="qr-info-label">Quantity</span>
          <span class="qr-info-value"><?= (int)$quote['quantity'] ?> doors</span>
        </div>
        <?php endif; ?>
        <?php if ($quote['notes']): ?>
        <div class="qr-info-item qr-info-item--full">
          <span class="qr-info-label">Notes</span>
          <span class="qr-info-value qr-notes"><?= nl2br($esc($quote['notes'])) ?></span>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!empty($doorItems) && count($doorItems) > 1): ?>
    <!-- Multi-door order: itemised list of every door the customer ordered. -->
    <div class="form-card">
      <div class="form-card-header">
        <h2>Doors Ordered (<?= count($doorItems) ?>)</h2>
      </div>
      <div class="qr-doors-table-wrap">
        <table class="qr-doors-table">
          <thead>
            <tr><th>#</th><th>Collection · Colour</th><th>Usage · Construction</th><th>Dimensions</th><th>Qty</th><th>Line Total</th></tr>
          </thead>
          <tbody>
            <?php foreach ($doorItems as $i => $d): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td>
                <strong><?= $esc($d['collection']) ?></strong>
                <span class="qr-doors-color">
                  <?php if ($d['color_hex']): ?><span class="qr-color-swatch" style="background:<?= $esc($d['color_hex']) ?>"></span><?php endif; ?>
                  <?= $esc($d['color']) ?>
                </span>
              </td>
              <td><?= $esc($d['usage']) ?> · <?= $esc($d['construction']) ?></td>
              <td><?= $d['width_mm'] ? $d['width_mm']/10 : '—' ?> × <?= $d['height_mm'] ? $d['height_mm']/10 : '—' ?> cm</td>
              <td>×<?= (int)$d['quantity'] ?></td>
              <td class="qr-doors-price"><?= number_format($d['line_total'], 2) ?> DZD</td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="5" class="qr-doors-total-label">Total</td>
              <td class="qr-doors-total"><?= number_format((float)$quote['final_price'], 2) ?> <?= $esc($quote['currency']) ?></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
    <?php else: ?>
    <div class="form-card">
      <div class="form-card-header">
        <h2>Door Configuration</h2>
      </div>
      <div class="qr-info-grid">
        <?php if (!empty($quote['room_type_name'])): ?>
        <div class="qr-info-item">
          <span class="qr-info-label">Room / Project</span>
          <span class="qr-info-value"><?= $esc($quote['room_type_name']) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($quote['collection_name'])): ?>
        <div class="qr-info-item">
          <span class="qr-info-label">Collection</span>
          <span class="qr-info-value"><?= $esc($quote['collection_name']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($quote['product_name']): ?>
        <div class="qr-info-item">
          <span class="qr-info-label">Product</span>
          <span class="qr-info-value"><?= $esc($quote['product_name']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($quote['door_type_name']): ?>
        <div class="qr-info-item">
          <span class="qr-info-label">Door Type</span>
          <span class="qr-info-value"><?= $esc($quote['door_type_name']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($quote['material_name']): ?>
        <div class="qr-info-item">
          <span class="qr-info-label">Material</span>
          <span class="qr-info-value"><?= $esc($quote['material_name']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($quote['color_name']): ?>
        <div class="qr-info-item">
          <span class="qr-info-label">Color</span>
          <span class="qr-info-value qr-color-val">
            <?php if ($quote['color_hex']): ?>
              <span class="qr-color-swatch" style="background:<?= $esc($quote['color_hex']) ?>"></span>
            <?php endif; ?>
            <?= $esc($quote['color_name']) ?>
          </span>
        </div>
        <?php endif; ?>
        <?php if ($quote['width_mm'] || $quote['height_mm']): ?>
        <div class="qr-info-item">
          <span class="qr-info-label">Dimensions</span>
          <span class="qr-info-value">
            <?= $quote['width_mm'] ? ($quote['width_mm'] / 10) . ' cm' : '—' ?> ×
            <?= $quote['height_mm'] ? ($quote['height_mm'] / 10) . ' cm' : '—' ?>
          </span>
        </div>
        <?php endif; ?>
        <?php if ($quote['handle']): ?>
        <div class="qr-info-item">
          <span class="qr-info-label">Handle</span>
          <span class="qr-info-value"><?= $esc($quote['handle']) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($features)): ?>
        <div class="qr-info-item qr-info-item--full">
          <span class="qr-info-label">Optional Features</span>
          <div class="qr-features-list">
            <?php foreach ($features as $f): ?>
              <span class="qr-feature-tag">
                <?= $esc($f['name']) ?>
                <span class="qr-feature-price">
                  <?php if ($f['price_type'] === 'percent'): ?>
                    +<?= (float)$f['price'] ?>%
                  <?php else: ?>
                    +<?= number_format((float)$f['price'], 2) ?> DZD
                  <?php endif; ?>
                </span>
              </span>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
        <?php if ($quote['final_price'] !== null): ?>
        <div class="qr-info-item qr-info-item--full">
          <span class="qr-info-label">Final Price</span>
          <span class="qr-info-value qr-final-price">
            <?= number_format((float)$quote['final_price'], 2) ?>
            <span class="qr-currency"><?= $esc($quote['currency']) ?></span>
          </span>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="form-card">
      <div class="form-card-header">
        <h2>Status History</h2>
      </div>
      <div class="qr-timeline">
        <?php if (empty($log)): ?>
          <p class="td-muted" style="padding:16px 20px;font-size:13px">No history yet.</p>
        <?php else: ?>
          <?php foreach ($log as $entry): ?>
          <?php $toMeta = $statusMeta[$entry['to_status']] ?? ['label' => $entry['to_status'], 'color' => 'ink']; ?>
          <div class="qr-timeline-item">
            <div class="qr-timeline-dot qr-timeline-dot--<?= $toMeta['color'] ?>"></div>
            <div class="qr-timeline-body">
              <div class="qr-timeline-header">
                <span class="qr-badge qr-badge--<?= $toMeta['color'] ?> qr-badge--sm"><?= $toMeta['label'] ?></span>
                <?php if ($entry['from_status']): ?>
                  <span class="qr-timeline-from">from <?= $esc($statusMeta[$entry['from_status']]['label'] ?? $entry['from_status']) ?></span>
                <?php endif; ?>
                <time class="qr-timeline-time"><?= date('d M Y, H:i', strtotime($entry['changed_at'])) ?></time>
                <?php if ($entry['changed_by_name']): ?>
                  <span class="qr-timeline-by">by <?= $esc($entry['changed_by_name']) ?></span>
                <?php endif; ?>
              </div>
              <?php if ($entry['notes']): ?>
                <p class="qr-timeline-notes"><?= nl2br($esc($entry['notes'])) ?></p>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <div class="qr-detail-side">

    <div class="form-card">
      <div class="form-card-header">
        <h2>Update Status</h2>
      </div>
      <div class="form-card-body">
        <?php if (empty($allowed)): ?>
          <p class="td-muted" style="font-size:13px">No further transitions available for this status.</p>
        <?php else: ?>
          <div id="statusError" class="flash flash--error" hidden style="margin-bottom:12px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            <span id="statusErrorMsg"></span>
          </div>
          <div class="form-field" style="margin-bottom:12px">
            <label for="newStatus">New Status</label>
            <select id="newStatus" class="form-select">
              <option value="">— Select —</option>
              <?php foreach ($allowed as $s): ?>
                <?php $m = $statusMeta[$s] ?? ['label' => $s]; ?>
                <option value="<?= $esc($s) ?>"><?= $esc($m['label']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-field" style="margin-bottom:16px">
            <label for="statusNotes">Note (optional)</label>
            <textarea id="statusNotes" class="form-select" rows="3" placeholder="Add a note about this transition…" style="resize:vertical;font-size:13px"></textarea>
          </div>
          <button class="btn btn-primary" id="updateStatusBtn" style="width:100%"
                  data-id="<?= (int)$quote['id'] ?>"
                  data-csrf="<?= $esc($csrfToken) ?>">
            Update Status
          </button>
        <?php endif; ?>
      </div>
    </div>

    <div class="form-card">
      <div class="form-card-header">
        <h2>Details</h2>
      </div>
      <div style="padding:16px 20px">
        <dl class="info-list">
          <dt>ID</dt>       <dd><?= (int)$quote['id'] ?></dd>
          <dt>Submitted</dt><dd><?= date('d/m/Y H:i', strtotime($quote['submitted_at'])) ?></dd>
          <dt>Updated</dt>  <dd><?= date('d/m/Y H:i', strtotime($quote['updated_at'])) ?></dd>
        </dl>
      </div>
    </div>

    <div class="danger-zone">
      <h3>Danger Zone</h3>
      <p>Permanently delete this quote request. This action cannot be undone.</p>
      <button class="btn btn-danger js-delete-quote"
              style="width:100%"
              data-id="<?= (int)$quote['id'] ?>"
              data-ref="<?= $esc($quote['reference']) ?>"
              data-csrf="<?= $esc($csrfToken) ?>">
        Delete Quote
      </button>
    </div>

  </div>
</div>

<div class="modal-backdrop" id="deleteModal" hidden>
  <div class="modal">
    <div class="modal-icon modal-icon--danger">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
    </div>
    <h3>Delete Quote?</h3>
    <p>You are about to permanently delete <strong id="deleteQuoteRef"></strong>. This cannot be undone.</p>
    <div class="modal-actions">
      <button class="btn btn-outline" id="deleteCancelBtn">Cancel</button>
      <button class="btn btn-danger"  id="deleteConfirmBtn">Delete</button>
    </div>
  </div>
</div>

<script src="/door-showroom/assets/js/quotes.js"></script>
