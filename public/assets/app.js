/**
 * Supervisi Akademik SMK — app.js v14
 * Bulletproof dark/light theme + sidebar + utilities
 */
(function(){
  'use strict';
  var KEY = 'smk_theme';
  var html = document.documentElement;

  function getSystemTheme() {
    try { return window.matchMedia('(prefers-color-scheme:dark)').matches ? 'dark' : 'light'; }
    catch(e) { return 'light'; }
  }

  function getSavedTheme() {
    try { var t = localStorage.getItem(KEY); return (t==='dark'||t==='light') ? t : null; }
    catch(e) { return null; }
  }

  function applyTheme(theme) {
    // Set attribute on html - this triggers CSS variable changes
    html.setAttribute('data-theme', theme);
    // Also set a class as fallback
    html.classList.remove('dark','light');
    html.classList.add(theme);
    // Save
    try { localStorage.setItem(KEY, theme); } catch(e) {}
    // Update button icon
    updateIcon(theme);
  }

  function updateIcon(theme) {
    var btns = document.querySelectorAll('#themeToggle, .theme-toggle');
    btns.forEach(function(btn){
      var icon = btn.querySelector('.theme-icon') || btn;
      icon.textContent = theme === 'dark' ? '☀️' : '🌙';
      btn.title = theme === 'dark' ? 'Switch to Light Mode' : 'Switch to Dark Mode';
    });
  }

  function toggle() {
    var current = html.getAttribute('data-theme') || 'light';
    applyTheme(current === 'dark' ? 'light' : 'dark');
  }

  // ═══ INIT THEME IMMEDIATELY (before DOM ready) ═══
  applyTheme(getSavedTheme() || getSystemTheme());

  // ═══ DOM READY ═══
  document.addEventListener('DOMContentLoaded', function(){

    // Theme toggle buttons (support multiple)
    document.querySelectorAll('#themeToggle, .theme-toggle').forEach(function(btn){
      btn.addEventListener('click', toggle);
    });

    // Update icons after DOM ready
    updateIcon(html.getAttribute('data-theme') || 'light');

    // ── Sidebar hamburger ──
    var ham = document.getElementById('hamburgerBtn');
    if(ham) ham.addEventListener('click', function(){
      document.body.classList.toggle('sidebar-open');
    });

    // ── Sidebar overlay close ──
    var ov = document.querySelector('.sidebar-overlay');
    if(ov) ov.addEventListener('click', function(){ document.body.classList.remove('sidebar-open'); });

    // ── Sidebar close button ──
    var cl = document.querySelector('.sidebar-close');
    if(cl) cl.addEventListener('click', function(){ document.body.classList.remove('sidebar-open'); });

    // ── Active nav link ──
    var file = (location.pathname.split('/').pop()||'index.php').split('?')[0] || 'index.php';
    document.querySelectorAll('.nav-link, .sidebar nav a').forEach(function(a){
      var href = (a.getAttribute('href')||'').split('/').pop().split('?')[0];
      if(href && href === file) a.classList.add('active');
    });

    // ── Auto table wrap ──
    document.querySelectorAll('table').forEach(function(t){
      if(t.closest('.table-wrap')) return;
      var w = document.createElement('div');
      w.className = 'table-wrap';
      t.parentNode.insertBefore(w, t);
      w.appendChild(t);
    });

    // ── Auto dismiss alerts ──
    document.querySelectorAll('.alert').forEach(function(el){
      setTimeout(function(){
        el.style.transition = 'opacity .5s, max-height .5s, padding .5s, margin .5s';
        el.style.opacity = '0';
        el.style.maxHeight = '0';
        el.style.padding = '0';
        el.style.margin = '0';
        el.style.overflow = 'hidden';
      }, 5000);
    });

    // ── Resize: close sidebar on desktop ──
    window.addEventListener('resize', function(){
      if(window.innerWidth > 768) document.body.classList.remove('sidebar-open');
    });
  });

  // ── Confirm dialog ──
  document.addEventListener('click', function(e){
    var t = e.target.closest('[data-confirm]');
    if(t && !confirm(t.getAttribute('data-confirm')||'Yakin?')){
      e.preventDefault();
      e.stopPropagation();
    }
  });
})();
