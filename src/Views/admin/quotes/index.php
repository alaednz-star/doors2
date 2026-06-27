<?php
use App\Validators\QuoteValidator;

$esc = fn(mixed $v): string => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');

$statusMeta = [
    'new'            => ['label' => 'New',            'color' => 'blue'],
    'contacted'      => ['label' => 'Contacted',      'color' => 'gold'],
    'quotation_sent' => ['label' => 'Quotation Sent', 'color' => 'purple'],
    'in_progress'    => ['label' => 'In Progress',    'color' => 'orange'],
    'confirmed'      => ['label' => 'Confirmed',      'color' => 'green'],
    'completed'      => ['label' => 'Completed',      'color' => 'ink'],
    'cancelled'      => ['label' => 'Cancelled',      'color' => 'red'],
];

$activeStatus = $_GET['status'] ?? '';
$search       = $_GET['q']      ?? '';
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Quote Requests</h1>
    <p class="page-sub"><?= number_format($counts['_total']) ?> total requests across all statuses.</p>
  </div>
  <div class="page-header-actions">
    <a href="/door-showroom/admin/quotes/create" class="btn btn-primary">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
      New Quote
    </a>
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

<div class="qr-status-tabs">
  <a href="/door-showroom/admin/quotes<?= $search ? '?q=' . urlencode($search) : '' ?>"
     class="qr-status-tab <?= $activeStatus === '' ? 'is-active' : '' ?>">
    All <span class="qr-tab-count"><?= $counts['_total'] ?></span>
  </a>
  <?php foreach ($statusMeta as $key => $meta): ?>
  <a href="/door-showroom/admin/quotes?status=<?= $key ?><?= $search ? '&q=' . urlencode($search) : '' ?>"
     class="qr-status-tab <?= $activeStatus === $key ? 'is-active' : '' ?> qr-status-tab--<?= $meta['color'] ?>">
    <?= $meta['label'] ?>
    <?php if ($counts[$key] > 0): ?>
      <span class="qr-tab-count"><?= $counts[$key] ?></span>
    <?php endif; ?>
  </a>
  <?php endforeach; ?>
</div>

<div class="table-panel">
  <div class="table-toolbar">
    <form class="search-form" method="GET" action="/door-showroom/admin/quotes">
      <?php if ($activeStatus): ?>
        <input type="hidden" name="status" value="<?= $esc($activeStatus) ?>">
      <?php endif; ?>
      <div class="search-wrap">
        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input class="search-input" type="text" name="q" placeholder="Search name, phone, reference…" value="<?= $esc($search) ?>" autocomplete="off">
        <?php if ($search): ?>
          <a href="/door-showroom/admin/quotes<?= $activeStatus ? '?status=' . urlencode($activeStatus) : '' ?>" class="search-clear">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
          </a>
        <?php endif; ?>
      </div>
    </form>
    <span class="table-count"><?= $total ?> result<?= $total !== 1 ? 's' : '' ?></span>
  </div>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Reference</th>
          <th>Customer</th>
          <th>Configuration</th>
          <th>Final Price</th>
          <th>Status</th>
          <th>Submitted</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($quotes)): ?>
        <tr><td colspan="7">
          <div class="table-empty">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0121 9.414V19a2 2 0 01-2 2z"/></svg>
            <p>No quote requests found</p>
            <span>Try a different search or status filter.</span>
          </div>
        </td></tr>
        <?php else: ?>
          <?php foreach ($quotes as $q): ?>
          <tr>
            <td>
              <a href="/door-showroom/admin/quotes/<?= (int)$q['id'] ?>" class="qr-ref-link">
                <?= $esc($q['reference']) ?>
              </a>
            </td>
            <td>
              <div class="qr-customer">
                <span class="qr-avatar"><?= $esc(strtoupper(mb_substr($q['customer_name'], 0, 1))) ?></span>
                <div class="qr-customer-info">
                  <strong><?= $esc($q['customer_name']) ?></strong>
                  <span><?= $esc($q['customer_phone']) ?></span>
                  <?php if ($q['customer_city']): ?>
                    <span class="td-muted"><?= $esc($q['customer_city']) ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </td>
            <td class="td-sm">
              <?php
              $parts = array_filter([
                  $q['product_name']   ?? null,
                  $q['material_name']  ?? null,
                  $q['door_type_name'] ?? null,
              ]);
              if (!empty($parts)) {
                  echo $esc(implode(' · ', $parts));
              } else {
                  echo '<span class="td-muted">—</span>';
              }
              if ($q['width_mm'] && $q['height_mm']) {
                  echo '<br><span class="td-muted">' . $q['width_mm'] . ' × ' . $q['height_mm'] . ' mm</span>';
              }
              ?>
            </td>
            <td class="td-sm">
              <?php if ($q['final_price'] !== null): ?>
                <strong><?= number_format((float)$q['final_price'], 2) ?></strong>
                <span class="td-muted"> <?= $esc($q['currency']) ?></span>
              <?php else: ?>
                <span class="td-muted">—</span>
              <?php endif; ?>
            </td>
            <td>
              <?php $sm = $statusMeta[$q['status']] ?? ['label' => $q['status'], 'color' => 'ink']; ?>
              <span class="qr-badge qr-badge--<?= $sm['color'] ?>"><?= $sm['label'] ?></span>
            </td>
            <td class="td-muted td-sm">
              <?= date('d M Y', strtotime($q['submitted_at'])) ?>
              <br><?= date('H:i', strtotime($q['submitted_at'])) ?>
            </td>
            <td>
              <div class="td-actions">
                <a href="/door-showroom/admin/quotes/<?= (int)$q['id'] ?>" class="action-btn action-btn--edit" title="View">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </a>
                <a href="/door-showroom/admin/quotes/<?= (int)$q['id'] ?>/edit" class="action-btn action-btn--edit" title="Edit">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </a>
                <button class="action-btn action-btn--delete js-delete-quote"
                        data-id="<?= (int)$q['id'] ?>"
                        data-ref="<?= $esc($q['reference']) ?>"
                        data-csrf="<?= $esc($csrfToken) ?>"
                        title="Delete">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                </button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($totalPages > 1): ?>
  <div class="pagination">
    <?php if ($page > 1): ?>
      <a href="?page=<?= $page - 1 ?><?= $activeStatus ? '&status=' . urlencode($activeStatus) : '' ?><?= $search ? '&q=' . urlencode($search) : '' ?>" class="page-btn page-btn--nav">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
      </a>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <a href="?page=<?= $i ?><?= $activeStatus ? '&status=' . urlencode($activeStatus) : '' ?><?= $search ? '&q=' . urlencode($search) : '' ?>"
         class="page-btn <?= $i === $page ? 'is-current' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($page < $totalPages): ?>
      <a href="?page=<?= $page + 1 ?><?= $activeStatus ? '&status=' . urlencode($activeStatus) : '' ?><?= $search ? '&q=' . urlencode($search) : '' ?>" class="page-btn page-btn--nav">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
      </a>
    <?php endif; ?>
    <span class="page-info">Page <?= $page ?> of <?= $totalPages ?></span>
  </div>
  <?php endif; ?>
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
