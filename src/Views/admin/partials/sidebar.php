<aside class="sidebar" id="sidebar">

  <div class="sidebar-header">
    <div class="sidebar-logo">
      <span class="sidebar-logo-mark">D</span>
      <div class="sidebar-logo-text">
        <strong>DOORS</strong>
        <span>Admin Console</span>
      </div>
    </div>
    <button class="sidebar-close" id="sidebarClose" aria-label="Close menu">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
        <path d="M18 6L6 18M6 6l12 12"/>
      </svg>
    </button>
  </div>

  <nav class="sidebar-nav" aria-label="Main navigation">

    <div class="nav-group">
      <span class="nav-group-label">Overview</span>
      <a href="/door-showroom/admin" class="nav-item <?= $currentPage === 'dashboard' ? 'is-active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
          <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
        </svg>
        <span>Dashboard</span>
      </a>
    </div>

    <div class="nav-group">
      <span class="nav-group-label">Showroom</span>
      <a href="/door-showroom/admin/quotes" class="nav-item <?= $currentPage === 'quotes' ? 'is-active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0121 9.414V19a2 2 0 01-2 2z"/>
        </svg>
        <span>Quote Requests</span>
        <?php if (($stats['new_quotes'] ?? 0) > 0): ?>
          <span class="nav-badge"><?= $stats['new_quotes'] ?></span>
        <?php endif; ?>
      </a>
      <a href="/door-showroom/admin/products" class="nav-item <?= $currentPage === 'products' ? 'is-active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
        <span>Products</span>
      </a>
      <a href="/door-showroom/admin/categories" class="nav-item <?= $currentPage === 'categories' ? 'is-active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
        </svg>
        <span>Categories</span>
      </a>
      <a href="/door-showroom/admin/collections" class="nav-item <?= $currentPage === 'collections' ? 'is-active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
        <span>Collections</span>
      </a>
    </div>

    <div class="nav-group">
      <span class="nav-group-label">Configuration</span>
      <a href="/door-showroom/admin/colors" class="nav-item <?= $currentPage === 'colors' ? 'is-active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="13.5" cy="6.5" r="2.5"/><circle cx="19" cy="13" r="2.5"/><circle cx="6" cy="14" r="2.5"/><circle cx="10" cy="20" r="2.5"/>
          <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10"/>
        </svg>
        <span>Colors</span>
      </a>
      <a href="/door-showroom/admin/usages" class="nav-item <?= $currentPage === 'usages' ? 'is-active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <rect x="6" y="3" width="12" height="18" rx="1"/><circle cx="14.5" cy="12" r="1"/>
        </svg>
        <span>Door Usages</span>
      </a>
      <a href="/door-showroom/admin/construction-types" class="nav-item <?= $currentPage === 'construction-types' ? 'is-active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 21h18M5 21V7l8-4 8 4v14M9 21v-6h6v6"/>
        </svg>
        <span>Construction Types</span>
      </a>
      <a href="/door-showroom/admin/pricing" class="nav-item <?= $currentPage === 'pricing' ? 'is-active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
        </svg>
        <span>Pricing</span>
      </a>
      <a href="/door-showroom/admin/media" class="nav-item <?= $currentPage === 'media' ? 'is-active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
          <path d="M21 15l-5-5L5 21"/>
        </svg>
        <span>Media Library</span>
      </a>
    </div>

    <div class="nav-group">
      <span class="nav-group-label">System</span>
      <a href="/door-showroom/admin/settings" class="nav-item <?= $currentPage === 'settings' ? 'is-active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
          <circle cx="12" cy="12" r="3"/>
        </svg>
        <span>Settings</span>
      </a>
    </div>

  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="sidebar-user-avatar"><?= strtoupper(substr($user['name'] ?? 'A', 0, 1)) ?></div>
      <div class="sidebar-user-info">
        <strong><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
        <span><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $user['role'] ?? '')), ENT_QUOTES, 'UTF-8') ?></span>
      </div>
    </div>
  </div>

</aside>

<div class="sidebar-overlay" id="sidebarOverlay"></div>
