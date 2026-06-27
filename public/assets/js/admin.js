(function () {
  'use strict';

  /* ── Sidebar ── */
  var sidebar        = document.getElementById('sidebar');
  var sidebarToggle  = document.getElementById('sidebarToggle');
  var sidebarClose   = document.getElementById('sidebarClose');
  var sidebarOverlay = document.getElementById('sidebarOverlay');

  function openSidebar() {
    sidebar.classList.add('is-open');
    sidebarOverlay.classList.add('is-open');
    document.body.style.overflow = 'hidden';
  }

  function closeSidebar() {
    sidebar.classList.remove('is-open');
    sidebarOverlay.classList.remove('is-open');
    document.body.style.overflow = '';
  }

  if (sidebarToggle)  sidebarToggle.addEventListener('click', openSidebar);
  if (sidebarClose)   sidebarClose.addEventListener('click', closeSidebar);
  if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);

  /* ── Notification dropdown ── */
  var notifBtn      = document.getElementById('notifBtn');
  var notifDropdown = document.getElementById('notifDropdown');

  if (notifBtn && notifDropdown) {
    notifBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      var open = !notifDropdown.hidden;
      closeAllDropdowns();
      if (!open) {
        notifDropdown.hidden = false;
        notifBtn.setAttribute('aria-expanded', 'true');
      }
    });
  }

  /* ── User menu dropdown ── */
  var userMenuBtn  = document.getElementById('userMenuBtn');
  var userDropdown = document.getElementById('userDropdown');

  if (userMenuBtn && userDropdown) {
    userMenuBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      var open = !userDropdown.hidden;
      closeAllDropdowns();
      if (!open) {
        userDropdown.hidden = false;
        userMenuBtn.setAttribute('aria-expanded', 'true');
      }
    });
  }

  function closeAllDropdowns() {
    if (notifDropdown) {
      notifDropdown.hidden = true;
      if (notifBtn) notifBtn.setAttribute('aria-expanded', 'false');
    }
    if (userDropdown) {
      userDropdown.hidden = true;
      if (userMenuBtn) userMenuBtn.setAttribute('aria-expanded', 'false');
    }
  }

  document.addEventListener('click', closeAllDropdowns);
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      closeAllDropdowns();
      closeSidebar();
    }
  });

  /* ── Logout ── */
  var logoutBtn = document.getElementById('logoutBtn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', function () {
      var csrf = document.querySelector('meta[name="csrf-token"]');
      if (!csrf) return;

      logoutBtn.disabled = true;
      logoutBtn.textContent = 'Signing out…';

      var xhr = new XMLHttpRequest();
      xhr.open('POST', '/door-showroom/admin/logout', true);
      xhr.setRequestHeader('Content-Type', 'application/json');
      xhr.setRequestHeader('X-CSRF-Token', csrf.getAttribute('content'));
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

      xhr.onreadystatechange = function () {
        if (xhr.readyState !== 4) return;
        try {
          var data = JSON.parse(xhr.responseText);
          window.location.href = data.redirect || '/door-showroom/admin/login';
        } catch (e) {
          window.location.href = '/door-showroom/admin/login';
        }
      };

      xhr.onerror = function () {
        window.location.href = '/door-showroom/admin/login';
      };

      xhr.send(JSON.stringify({ _csrf: csrf.getAttribute('content') }));
    });
  }

  /* ── Close sidebar on resize to desktop ── */
  window.addEventListener('resize', function () {
    if (window.innerWidth > 768) {
      closeSidebar();
    }
  });

}());
