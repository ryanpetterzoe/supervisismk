/**
 * Supervisi Akademik SMK — app.js v12
 * Theme toggle | Hamburger sidebar | Active nav | Table wrap | Alerts
 */
(function () {
  'use strict';

  var THEME_KEY = 'smk_theme';
  var html = document.documentElement;

  /* ── 1. THEME ── */
  function getSystemTheme() {
    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }

  function applyTheme(theme) {
    html.setAttribute('data-theme', theme);
    try { localStorage.setItem(THEME_KEY, theme); } catch(e) {}
    var btn  = document.getElementById('themeToggle');
    var icon = btn && (btn.querySelector('.theme-icon') || btn);
    if (icon) icon.textContent = theme === 'dark' ? '☀️' : '🌙';
    if (btn)  btn.title = theme === 'dark' ? 'Mode Terang' : 'Mode Gelap';
  }

  function initTheme() {
    var saved;
    try { saved = localStorage.getItem(THEME_KEY); } catch(e) {}
    applyTheme(saved === 'dark' || saved === 'light' ? saved : getSystemTheme());
  }

  // Run IMMEDIATELY — before DOM renders — to prevent flash
  initTheme();

  /* ── 2. SIDEBAR ── */
  function openSidebar()  { document.body.classList.add('sidebar-open'); }
  function closeSidebar() { document.body.classList.remove('sidebar-open'); }
  function isMobile()     { return window.innerWidth <= 768; }

  /* ── 3. ACTIVE NAV ── */
  function markActiveNav() {
    var file = (window.location.pathname.split('/').pop() || 'index.php').split('?')[0] || 'index.php';
    document.querySelectorAll('.sidebar .nav-link, .sidebar nav a').forEach(function (a) {
      var href = (a.getAttribute('href') || '').split('/').pop().split('?')[0];
      if (href && href === file) {
        a.classList.add('active');
        // Remove active from siblings (avoid duplicates)
      }
    });
  }

  /* ── 4. TABLE WRAP ── */
  function wrapTables() {
    document.querySelectorAll('table').forEach(function (tbl) {
      if (tbl.closest('.table-wrap')) return;
      var wrap = document.createElement('div');
      wrap.className = 'table-wrap';
      tbl.parentNode.insertBefore(wrap, tbl);
      wrap.appendChild(tbl);
    });
  }

  /* ── 5. AUTO-DISMISS ALERTS ── */
  function autoDismissAlerts() {
    document.querySelectorAll('.alert').forEach(function (el) {
      setTimeout(function () {
        el.style.transition = 'opacity 0.5s ease, max-height 0.5s ease, padding 0.5s ease, margin 0.5s ease';
        el.style.opacity = '0';
        el.style.maxHeight = '0';
        el.style.padding = '0';
        el.style.margin = '0';
        el.style.overflow = 'hidden';
      }, 5000);
    });
  }

  /* ── 6. DOM READY ── */
  document.addEventListener('DOMContentLoaded', function () {

    // Theme toggle button
    var themeBtn = document.getElementById('themeToggle');
    if (themeBtn) {
      // Sync icon with current theme (already set by initTheme)
      var cur = html.getAttribute('data-theme') || 'light';
      var icon = themeBtn.querySelector('.theme-icon') || themeBtn;
      icon.textContent = cur === 'dark' ? '☀️' : '🌙';
      themeBtn.title    = cur === 'dark' ? 'Mode Terang' : 'Mode Gelap';

      themeBtn.addEventListener('click', function () {
        applyTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
      });
    }

    // Hamburger
    var hamburger = document.getElementById('hamburgerBtn');
    if (hamburger) {
      hamburger.addEventListener('click', function () {
        document.body.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
      });
    }

    // Overlay click
    var overlay = document.querySelector('#sidebarOverlay, .sidebar-overlay');
    if (overlay) overlay.addEventListener('click', closeSidebar);

    // Sidebar close btn
    var closeBtn = document.querySelector('#sidebarClose, .sidebar-close');
    if (closeBtn) closeBtn.addEventListener('click', closeSidebar);

    // Close sidebar on nav click (mobile)
    document.querySelectorAll('.sidebar .nav-link, .sidebar nav a').forEach(function (a) {
      a.addEventListener('click', function () { if (isMobile()) closeSidebar(); });
    });

    markActiveNav();
    wrapTables();
    autoDismissAlerts();
  });

  /* ── 7. CONFIRM DIALOG ── */
  document.addEventListener('click', function (e) {
    var el = e.target.closest('[data-confirm]');
    if (el && !confirm(el.getAttribute('data-confirm') || 'Yakin?')) {
      e.preventDefault();
      e.stopPropagation();
    }
  });

  /* ── 8. RESIZE HANDLER ── */
  window.addEventListener('resize', function () {
    if (!isMobile()) closeSidebar();
  });

})();
