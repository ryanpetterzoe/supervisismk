/**
 * Supervisi Akademik SMK — app.js
 * Features: dark/light theme, hamburger sidebar, active nav, table wrap, confirm, auto-dismiss alert
 */
(function () {
  'use strict';

  /* ── 1. THEME TOGGLE ── */
  var THEME_KEY = 'theme';
  var html = document.documentElement;

  function applyTheme(theme) {
    html.setAttribute('data-theme', theme);
    localStorage.setItem(THEME_KEY, theme);
    updateThemeIcon(theme);
  }

  function updateThemeIcon(theme) {
    var btn = document.getElementById('themeToggle');
    if (!btn) return;
    var icon = btn.querySelector('.theme-icon') || btn;
    icon.textContent = theme === 'dark' ? '\u2600\uFE0F' : '\uD83C\uDF19';
    btn.setAttribute('title', theme === 'dark' ? 'Ganti ke mode terang' : 'Ganti ke mode gelap');
  }

  function getCurrentTheme() {
    return html.getAttribute('data-theme') || 'light';
  }

  function initTheme() {
    var saved = localStorage.getItem(THEME_KEY);
    var theme = (saved === 'dark' || saved === 'light')
      ? saved
      : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    applyTheme(theme);
  }

  // Run immediately to prevent flash
  initTheme();

  /* ── 2. SIDEBAR ── */
  function openSidebar()  { document.body.classList.add('sidebar-open'); }
  function closeSidebar() { document.body.classList.remove('sidebar-open'); }

  /* ── 3. ACTIVE NAV ── */
  function setActiveNavLink() {
    var currentFile = (window.location.pathname.split('/').pop() || 'index.php').split('?')[0];
    document.querySelectorAll('.nav-link, .sidebar nav a').forEach(function (link) {
      var linkFile = (link.getAttribute('href') || '').split('/').pop().split('?')[0];
      if (linkFile && linkFile === currentFile) link.classList.add('active');
    });
  }

  /* ── 4. TABLE WRAP ── */
  function wrapTables() {
    document.querySelectorAll('table').forEach(function (table) {
      if (table.parentElement && table.parentElement.classList.contains('table-wrap')) return;
      var wrapper = document.createElement('div');
      wrapper.className = 'table-wrap';
      table.parentNode.insertBefore(wrapper, table);
      wrapper.appendChild(table);
    });
  }

  /* ── 5. DOM READY ── */
  document.addEventListener('DOMContentLoaded', function () {

    // Theme button
    var themeBtn = document.getElementById('themeToggle');
    if (themeBtn) {
      updateThemeIcon(getCurrentTheme());
      themeBtn.addEventListener('click', function () {
        applyTheme(getCurrentTheme() === 'dark' ? 'light' : 'dark');
      });
    }

    // Hamburger
    var hamburger = document.getElementById('hamburgerBtn');
    if (hamburger) hamburger.addEventListener('click', function () {
      document.body.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
    });

    // Overlay
    var overlay = document.getElementById('sidebarOverlay') || document.querySelector('.sidebar-overlay');
    if (overlay) overlay.addEventListener('click', closeSidebar);

    // Close button
    var closeBtn = document.getElementById('sidebarClose') || document.querySelector('.sidebar-close');
    if (closeBtn) closeBtn.addEventListener('click', closeSidebar);

    // Auto-close sidebar on nav click (mobile)
    document.querySelectorAll('.sidebar .nav-link, .sidebar nav a').forEach(function (link) {
      link.addEventListener('click', function () {
        if (window.innerWidth <= 768) closeSidebar();
      });
    });

    setActiveNavLink();
    wrapTables();

    // Auto-dismiss alerts after 5s
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
  });

  /* ── 6. CONFIRM DIALOG ── */
  document.addEventListener('click', function (e) {
    var target = e.target.closest('[data-confirm]');
    if (target) {
      if (!confirm(target.getAttribute('data-confirm') || 'Apakah Anda yakin?')) {
        e.preventDefault();
        e.stopPropagation();
      }
    }
  });

  /* ── 7. RESIZE: close sidebar on desktop ── */
  window.addEventListener('resize', function () {
    if (window.innerWidth > 768) closeSidebar();
  });

})();
