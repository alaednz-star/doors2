<div class="page-header">
  <div>
    <h1 class="page-title">Dashboard</h1>
    <p class="page-sub">Good <?= (date('H') < 12 ? 'morning' : (date('H') < 18 ? 'afternoon' : 'evening')) ?>, <?= htmlspecialchars(explode(' ', $user['name'] ?? 'Admin')[0], ENT_QUOTES, 'UTF-8') ?>.</p>
  </div>
  <div class="page-header-actions">
    <a href="/door-showroom/admin/quotes" class="btn btn-primary">
      <svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg>
      View Quotes
    </a>
  </div>
</div>

<!-- Stats grid -->
<div class="stats-grid">

  <div class="stat-card">
    <div class="stat-card-icon stat-card-icon--blue">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0121 9.414V19a2 2 0 01-2 2z"/>
      </svg>
    </div>
    <div class="stat-card-body">
      <span class="stat-label">Total Quotes</span>
      <div class="stat-value"><?= number_format($stats['total_quotes']) ?></div>
      <span class="stat-meta">All time requests</span>
    </div>
  </div>

  <div class="stat-card stat-card--highlight">
    <div class="stat-card-icon stat-card-icon--gold">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/>
        <path d="M12 8v4l3 3"/>
      </svg>
    </div>
    <div class="stat-card-body">
      <span class="stat-label">New Requests</span>
      <div class="stat-value"><?= number_format($stats['new_quotes']) ?></div>
      <span class="stat-meta <?= $stats['new_quotes'] > 0 ? 'stat-meta--alert' : '' ?>">
        <?= $stats['new_quotes'] > 0 ? 'Awaiting response' : 'All handled' ?>
      </span>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-card-icon stat-card-icon--green">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
    </div>
    <div class="stat-card-body">
      <span class="stat-label">Won Quotes</span>
      <div class="stat-value"><?= number_format($stats['won_quotes']) ?></div>
      <span class="stat-meta">Converted clients</span>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-card-icon stat-card-icon--purple">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
      </svg>
    </div>
    <div class="stat-card-body">
      <span class="stat-label">Conversion Rate</span>
      <div class="stat-value"><?= $stats['conversion_rate'] ?>%</div>
      <span class="stat-meta">Won / Total quotes</span>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-card-icon stat-card-icon--ink">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
      </svg>
    </div>
    <div class="stat-card-body">
      <span class="stat-label">Active Products</span>
      <div class="stat-value"><?= number_format($stats['active_products']) ?></div>
      <span class="stat-meta">Published door models</span>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-card-icon stat-card-icon--rose">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
      </svg>
    </div>
    <div class="stat-card-body">
      <span class="stat-label">Categories</span>
      <div class="stat-value"><?= number_format($stats['categories']) ?></div>
      <span class="stat-meta">Active collections</span>
    </div>
  </div>

</div>

<!-- Bottom section -->
<div class="dashboard-grid">

  <!-- Recent quotes -->
  <div class="panel">
    <div class="panel-header">
      <h2 class="panel-title">Recent Quote Requests</h2>
      <a href="/door-showroom/admin/quotes" class="panel-link">View all</a>
    </div>
    <div class="panel-body">
      <?php if (empty($recent)): ?>
        <div class="empty-state">
          <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M14 6h20a2 2 0 012 2v32a2 2 0 01-2 2H14a2 2 0 01-2-2V8a2 2 0 012-2z"/>
            <path d="M18 16h12M18 22h12M18 28h8"/>
          </svg>
          <p>No quote requests yet.</p>
          <span>They will appear here once customers submit requests.</span>
        </div>
      <?php else: ?>
        <div class="quote-list">
          <?php foreach ($recent as $q): ?>
            <a href="/door-showroom/admin/quotes/<?= (int)$q['id'] ?>" class="quote-row">
              <div class="quote-row-avatar"><?= strtoupper(substr($q['customer_name'], 0, 1)) ?></div>
              <div class="quote-row-info">
                <strong><?= htmlspecialchars($q['customer_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                <span><?= htmlspecialchars($q['reference'] ?? '#' . $q['id'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($q['customer_phone'], ENT_QUOTES, 'UTF-8') ?></span>
              </div>
              <div class="quote-row-right">
                <span class="status-badge status-badge--<?= htmlspecialchars($q['status'], ENT_QUOTES, 'UTF-8') ?>">
                  <?= htmlspecialchars(ucfirst($q['status']), ENT_QUOTES, 'UTF-8') ?>
                </span>
                <time class="quote-row-time"><?= date('d M', strtotime($q['submitted_at'])) ?></time>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Quick actions -->
  <div class="panel">
    <div class="panel-header">
      <h2 class="panel-title">Quick Actions</h2>
    </div>
    <div class="panel-body">
      <div class="quick-actions">
        <a href="/door-showroom/admin/products/create" class="quick-action">
          <div class="quick-action-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 4v16m8-8H4"/>
            </svg>
          </div>
          <span>Add Product</span>
        </a>
        <a href="/door-showroom/admin/categories/create" class="quick-action">
          <div class="quick-action-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 4v16m8-8H4"/>
            </svg>
          </div>
          <span>Add Category</span>
        </a>
        <a href="/door-showroom/admin/media" class="quick-action">
          <div class="quick-action-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </div>
          <span>Upload Media</span>
        </a>
        <a href="/door-showroom/admin/quotes" class="quick-action">
          <div class="quick-action-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
          </div>
          <span>Manage Quotes</span>
        </a>
        <a href="/door-showroom/admin/pricing" class="quick-action">
          <div class="quick-action-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
            </svg>
          </div>
          <span>Update Pricing</span>
        </a>
        <a href="/door-showroom/admin/settings" class="quick-action">
          <div class="quick-action-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
          </div>
          <span>Settings</span>
        </a>
      </div>
    </div>
  </div>

</div>
