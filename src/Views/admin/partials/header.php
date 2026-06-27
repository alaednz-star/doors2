<header class="topbar">

  <div class="topbar-left">
    <button class="topbar-menu-btn" id="sidebarToggle" aria-label="Open menu">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
        <path d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>
    <div class="topbar-breadcrumb">
      <span class="topbar-page-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard', ENT_QUOTES, 'UTF-8') ?></span>
    </div>
  </div>

  <div class="topbar-right">

    <button class="topbar-icon-btn" id="notifBtn" aria-label="Notifications" aria-expanded="false">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
      </svg>
      <?php if (($stats['new_quotes'] ?? 0) > 0): ?>
        <span class="topbar-badge"><?= min($stats['new_quotes'], 9) ?><?= $stats['new_quotes'] > 9 ? '+' : '' ?></span>
      <?php endif; ?>
    </button>

    <div class="notif-dropdown" id="notifDropdown" hidden>
      <div class="notif-header">
        <span>Notifications</span>
        <?php if (($stats['new_quotes'] ?? 0) > 0): ?>
          <span class="notif-count"><?= $stats['new_quotes'] ?> new</span>
        <?php endif; ?>
      </div>
      <div class="notif-body">
        <?php if (($stats['new_quotes'] ?? 0) > 0): ?>
          <a href="/door-showroom/admin/quotes" class="notif-item notif-item--unread">
            <div class="notif-icon notif-icon--gold">
              <svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg>
            </div>
            <div class="notif-text">
              <strong><?= $stats['new_quotes'] ?> new quote <?= $stats['new_quotes'] === 1 ? 'request' : 'requests' ?></strong>
              <span>Awaiting your response</span>
            </div>
          </a>
        <?php else: ?>
          <div class="notif-empty">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>All caught up</span>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="user-menu-wrap" id="userMenuWrap">
      <button class="user-menu-btn" id="userMenuBtn" aria-expanded="false">
        <div class="user-avatar"><?= strtoupper(substr($user['name'] ?? 'A', 0, 1)) ?></div>
        <div class="user-info">
          <span class="user-name"><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
          <span class="user-role"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $user['role'] ?? '')), ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <svg class="user-chevron" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
        </svg>
      </button>

      <div class="user-dropdown" id="userDropdown" hidden>
        <div class="user-dropdown-header">
          <div class="user-avatar user-avatar--lg"><?= strtoupper(substr($user['name'] ?? 'A', 0, 1)) ?></div>
          <div>
            <strong><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
            <span><?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
          </div>
        </div>
        <div class="user-dropdown-body">
          <a href="/door-showroom/admin/profile" class="dropdown-item">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
            My Profile
          </a>
          <a href="/door-showroom/admin/settings" class="dropdown-item">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/></svg>
            Settings
          </a>
        </div>
        <div class="user-dropdown-footer">
          <button class="dropdown-item dropdown-item--danger" id="logoutBtn">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/></svg>
            Sign Out
          </button>
        </div>
      </div>
    </div>

  </div>
</header>
