<?php
/**
 * layout.php — Supervisi Akademik SMK
 * HTML shell: render_header() + render_footer()
 * Supports: dark/light theme toggle, hamburger sidebar, responsive, mobile-friendly
 */

function render_header($title = 'Dashboard') {
    $u      = current_user();
    $flash  = flash();
    $school = school_identity();
    $logoUrl = public_file_url($school['logo_path'] ?? '');

    $currentPage = basename($_SERVER['PHP_SELF']);

    $navItems = [
        ['index.php',          '&#127968;', 'Dashboard'],
        ['teachers.php',       '&#128105;', 'Data Guru'],
        ['subjects.php',       '&#128218;', 'Mapel'],
        ['classes.php',        '&#127979;', 'Kelas'],
        ['instruments.php',    '&#128203;', 'Instrumen'],
    ];

    if ($u && has_role(['admin', 'kepala_sekolah', 'supervisor'])) {
        $navItems[] = ['instrument_files.php', '&#128206;', 'Upload Instrumen'];
    }

    $navItems = array_merge($navItems, [
        ['schedules.php',      '&#128197;', 'Jadwal Supervisi'],
        ['observations.php',   '&#128269;', 'Observasi'],
        ['academic_forms.php', '&#128221;', 'Input Bertahap'],
        ['followups.php',      '&#9989;',   'Tindak Lanjut'],
        ['documents.php',      '&#128193;', 'Dokumen'],
        ['reports.php',        '&#128202;', 'Laporan'],
    ]);

    if ($u && has_role(['admin', 'kepala_sekolah'])) {
        $navItems[] = ['school_identity.php', '&#127963;', 'Identitas Sekolah'];
    }
    if ($u && has_role(['admin'])) {
        $navItems[] = ['users.php', '&#128100;', 'Pengguna'];
    }
?>
<!doctype html>
<html lang="id" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?> - <?= e($school['school_name'] ?: cfg('app_name')) ?></title>
  <link rel="stylesheet" href="<?= url('assets/style.css') ?>">
  <script>
    /* Prevent theme flash */
    (function(){
      var t=localStorage.getItem('theme')||(window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light');
      document.documentElement.setAttribute('data-theme',t);
    })();
  </script>
</head>
<body>
<div class="app-shell">

  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <a class="brand" href="<?= url('index.php') ?>" title="Beranda">
        <div class="logo">
          <?php if ($logoUrl): ?>
            <span class="app-logo-box"><img class="app-logo-img" src="<?= e($logoUrl) ?>" alt="Logo" width="36" height="36"></span>
          <?php else: ?>
            <span class="logo-text">SG</span>
          <?php endif; ?>
        </div>
        <div class="brand-text">
          <b><?= e($school['school_name'] ?: 'Supervisi Guru') ?></b>
          <span>SMK Kurikulum Merdeka</span>
        </div>
      </a>
      <button class="sidebar-close" id="sidebarClose" aria-label="Tutup menu">&#10005;</button>
    </div>

    <?php if ($u): ?>
    <nav class="sidebar-nav">
      <?php foreach ($navItems as [$file, $icon, $label]):
            $isActive = ($currentPage === $file) ? ' active' : '';
      ?>
      <a href="<?= url($file) ?>" class="nav-link<?= $isActive ?>">
        <span class="nav-icon"><?= $icon ?></span>
        <span class="nav-label"><?= $label ?></span>
      </a>
      <?php endforeach; ?>
    </nav>
    <?php endif; ?>
  </aside>

  <main class="main">
    <header class="topbar">
      <div class="topbar-left">
        <button class="hamburger" id="hamburgerBtn" aria-label="Buka menu">
          <span></span><span></span><span></span>
        </button>
        <div class="topbar-title">
          <h1><?= e($title) ?></h1>
          <p><?= date('l, d F Y') ?></p>
        </div>
      </div>
      <div class="topbar-right">
        <button class="theme-toggle" id="themeToggle" aria-label="Toggle tema" title="Ganti tema">
          <span class="theme-icon">&#127769;</span>
        </button>
        <?php if ($u): ?>
        <div class="userbox">
          <div class="user-avatar"><?= strtoupper(substr($u['name'], 0, 1)) ?></div>
          <div class="user-info">
            <span class="user-name"><?= e($u['name']) ?></span>
            <small class="user-role"><?= e($u['role']) ?></small>
          </div>
          <a class="btn small danger" href="<?= url('logout.php') ?>">Keluar</a>
        </div>
        <?php endif; ?>
      </div>
    </header>

    <?php if ($flash): ?>
    <div class="alert <?= e($flash['type']) ?>" role="alert"><?= e($flash['msg']) ?></div>
    <?php endif; ?>

    <section class="content">
<?php
}

function render_footer()
{
?>
    </section>
  </main>
</div>
<script src="<?= url('assets/app.js') ?>"></script>
</body>
</html>
<?php
}
