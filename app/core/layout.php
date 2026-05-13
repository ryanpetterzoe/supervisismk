<?php
/**
 * layout.php — Supervisi Akademik SMK v12
 * render_header() / render_footer()
 */

function render_header($title = 'Dashboard') {
    $u       = current_user();
    $flash   = flash();
    $school  = school_identity();
    $logoUrl = public_file_url($school['logo_path'] ?? '');
    $page    = basename($_SERVER['PHP_SELF']);

    /* Navigation items */
    $nav = [
        ['index.php',          '🏠', 'Dashboard'],
        ['teachers.php',       '👩‍🏫', 'Data Guru'],
        ['subjects.php',       '📚', 'Mata Pelajaran'],
        ['classes.php',        '🏫', 'Kelas'],
        ['instruments.php',    '📋', 'Instrumen'],
    ];
    if ($u && has_role(['admin','kepala_sekolah','supervisor'])) {
        $nav[] = ['instrument_files.php', '📎', 'Upload Instrumen'];
    }
    $nav = array_merge($nav, [
        ['schedules.php',      '📅', 'Jadwal Supervisi'],
        ['observations.php',   '🔍', 'Observasi'],
        ['academic_forms.php', '📝', 'Input Bertahap'],
        ['followups.php',      '✅', 'Tindak Lanjut'],
        ['documents.php',      '📁', 'Dokumen'],
        ['reports.php',        '📊', 'Laporan'],
    ]);
    if ($u && has_role(['admin','kepala_sekolah'])) {
        $nav[] = ['school_identity.php', '🏛️', 'Identitas Sekolah'];
    }
    if ($u && has_role(['admin'])) {
        $nav[] = ['users.php', '👤', 'Pengguna'];
    }
?>
<!doctype html>
<html lang="id" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?> – <?= e($school['school_name'] ?: cfg('app_name')) ?></title>
  <link rel="stylesheet" href="<?= url('assets/style.css') ?>">
  <script>
    /* ── Anti-flash: apply saved theme before page renders ── */
    (function(){
      var t;
      try { t = localStorage.getItem('smk_theme'); } catch(e){}
      if (t !== 'dark' && t !== 'light') {
        t = (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
      }
      document.documentElement.setAttribute('data-theme', t);
    })();
  </script>
</head>
<body>

<div class="app-shell">

  <!-- Overlay for mobile sidebar -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- ════ SIDEBAR ════ -->
  <aside class="sidebar" id="sidebar">

    <div class="sidebar-header">
      <a class="brand" href="<?= url('index.php') ?>" title="Beranda">
        <div class="logo">
          <?php if ($logoUrl): ?>
            <span class="app-logo-box">
              <img class="app-logo-img" src="<?= e($logoUrl) ?>" alt="Logo" width="34" height="34">
            </span>
          <?php else: ?>
            <span class="logo-text">SG</span>
          <?php endif; ?>
        </div>
        <div class="brand-text">
          <strong><?= e($school['school_name'] ?: 'Supervisi Guru') ?></strong>
          <span>SMK Kurikulum Merdeka</span>
        </div>
      </a>
      <button class="sidebar-close" id="sidebarClose" aria-label="Tutup menu">✕</button>
    </div>

    <?php if ($u): ?>
    <nav class="sidebar-nav" aria-label="Navigasi utama">
      <?php foreach ($nav as [$file, $icon, $label]):
            $active = ($page === $file) ? ' active' : '';
      ?>
      <a href="<?= url($file) ?>" class="nav-link<?= $active ?>">
        <span class="nav-icon" aria-hidden="true"><?= $icon ?></span>
        <span class="nav-label"><?= $label ?></span>
      </a>
      <?php endforeach; ?>
    </nav>
    <?php endif; ?>

  </aside>
  <!-- ════ END SIDEBAR ════ -->

  <!-- ════ MAIN ════ -->
  <main class="main" id="mainContent">

    <!-- ── Topbar ── -->
    <header class="topbar" role="banner">
      <div class="topbar-left">
        <button class="hamburger" id="hamburgerBtn" aria-label="Toggle menu" aria-expanded="false">
          <span></span>
          <span></span>
          <span></span>
        </button>
        <div class="topbar-title">
          <h1><?= e($title) ?></h1>
          <p><?= date('l, d F Y') ?></p>
        </div>
      </div>

      <div class="topbar-right">
        <!-- Theme Toggle -->
        <button class="theme-toggle" id="themeToggle" aria-label="Toggle tema" title="Toggle tema">
          <span class="theme-icon" aria-hidden="true">🌙</span>
        </button>

        <?php if ($u): ?>
        <div class="userbox">
          <div class="user-avatar" aria-hidden="true">
            <?= strtoupper(mb_substr($u['name'], 0, 1)) ?>
          </div>
          <div class="user-info">
            <span class="user-name"><?= e($u['name']) ?></span>
            <small class="user-role"><?= e($u['role']) ?></small>
          </div>
          <a class="btn small danger" href="<?= url('logout.php') ?>">Keluar</a>
        </div>
        <?php endif; ?>
      </div>
    </header>
    <!-- ── End Topbar ── -->

    <!-- Flash message -->
    <?php if ($flash): ?>
    <div class="alert <?= e($flash['type']) ?>" role="alert" aria-live="polite">
      <?= e($flash['msg']) ?>
    </div>
    <?php endif; ?>

    <!-- Page content -->
    <section class="content" id="pageContent">
<?php
}


function render_footer() {
?>
    </section><!-- /#pageContent -->
  </main><!-- /#mainContent -->

</div><!-- /.app-shell -->

<script src="<?= url('assets/app.js') ?>"></script>
</body>
</html>
<?php
}
