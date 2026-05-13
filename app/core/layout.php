<?php
/**
 * layout.php — Supervisi Akademik SMK v14
 * BULLETPROOF dark/light theme
 */

function render_header($title = 'Dashboard') {
    $u      = current_user();
    $flash  = flash();
    $school = school_identity();
    $logoUrl = public_file_url($school['logo_path'] ?? '');
    $page   = basename($_SERVER['PHP_SELF']);

    $nav = [
        ['index.php','🏠','Dashboard'],
        ['teachers.php','👩‍🏫','Data Guru'],
        ['subjects.php','📚','Mata Pelajaran'],
        ['classes.php','🏫','Kelas'],
        ['instruments.php','📋','Instrumen'],
    ];
    if ($u && has_role(['admin','kepala_sekolah','supervisor'])) {
        $nav[] = ['instrument_files.php','📎','Upload Instrumen'];
    }
    $nav = array_merge($nav, [
        ['schedules.php','📅','Jadwal Supervisi'],
        ['observations.php','🔍','Observasi'],
        ['academic_forms.php','📝','Input Bertahap'],
        ['followups.php','✅','Tindak Lanjut'],
        ['documents.php','📁','Dokumen'],
        ['reports.php','📊','Laporan'],
    ]);
    if ($u && has_role(['admin','kepala_sekolah'])) $nav[] = ['school_identity.php','🏛️','Identitas Sekolah'];
    if ($u && has_role(['admin'])) $nav[] = ['users.php','👤','Pengguna'];
?>
<!doctype html>
<html lang="id" data-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?> – <?= e($school['app_name'] ?? $school['school_name'] ?: cfg('app_name')) ?></title>
<?php /* INLINE critical dark theme - this ALWAYS works regardless of external CSS cache */ ?>
<style>
:root{--bg:#f1f5f9;--surface:#fff;--surface2:#f8fafc;--text:#0f172a;--text2:#334155;--text3:#64748b;--border:#e2e8f0;--primary:<?= e($school['header_color'] ?? '#2563eb') ?>;--accent:<?= e($school['accent_color'] ?? '#7c3aed') ?>;--primary-light:#eff6ff}
[data-theme="dark"],html.dark{--bg:#0f172a;--surface:#1e293b;--surface2:#1a2540;--text:#f1f5f9;--text2:#cbd5e1;--text3:#94a3b8;--border:#2d3f55;--primary:<?= e($school['header_color'] ?? '#3b82f6') ?>;--accent:<?= e($school['accent_color'] ?? '#a78bfa') ?>;--primary-light:#1e3a5e}
body{background-color:var(--bg);color:var(--text);transition:background-color .2s,color .2s}
</style>
<script>
/* Anti-flash: apply theme BEFORE anything renders */
(function(){
  var k='smk_theme',h=document.documentElement,t;
  try{t=localStorage.getItem(k)}catch(e){}
  if(t!=='dark'&&t!=='light') t=(window.matchMedia&&window.matchMedia('(prefers-color-scheme:dark)').matches)?'dark':'light';
  h.setAttribute('data-theme',t);
  h.className=t;
})();
</script>
<link rel="stylesheet" href="<?= url('assets/style.css') ?>?v=14">
</head>
<body>
<div class="app-shell">
<div class="sidebar-overlay"></div>
<aside class="sidebar">
  <div class="sidebar-header">
    <a class="brand" href="<?= url('index.php') ?>">
      <div class="logo">
        <?php if($logoUrl): ?>
          <span class="app-logo-box"><img class="app-logo-img" src="<?= e($logoUrl) ?>" alt="Logo"></span>
        <?php else: ?>
          <span class="logo-text">SG</span>
        <?php endif; ?>
      </div>
      <div class="brand-text">
        <strong><?= e($school['school_name'] ?: 'Supervisi Guru') ?></strong>
        <span><?= e($school['app_name'] ?? 'Supervisi Akademik') ?></span>
      </div>
    </a>
    <button class="sidebar-close" aria-label="Tutup">✕</button>
  </div>
  <?php if($u): ?>
  <nav class="sidebar-nav">
    <?php foreach($nav as [$file,$icon,$label]): $act=($page===$file)?' active':''; ?>
    <a href="<?= url($file) ?>" class="nav-link<?= $act ?>">
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
      <button class="hamburger" id="hamburgerBtn" aria-label="Menu"><span></span><span></span><span></span></button>
      <div class="topbar-title">
        <h1><?= e($title) ?></h1>
        <p><?= date('l, d F Y') ?></p>
      </div>
    </div>
    <div class="topbar-right">
      <button class="theme-toggle" id="themeToggle" type="button" title="Toggle Tema"><span class="theme-icon">🌙</span></button>
      <?php if($u): ?>
      <div class="userbox">
        <div class="user-avatar"><?= strtoupper(mb_substr($u['name'],0,1)) ?></div>
        <div class="user-info">
          <span class="user-name"><?= e($u['name']) ?></span>
          <small class="user-role"><?= e($u['role']) ?></small>
        </div>
        <a class="btn small danger" href="<?= url('logout.php') ?>">Keluar</a>
      </div>
      <?php endif; ?>
    </div>
  </header>
  <?php if($flash): ?><div class="alert <?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div><?php endif; ?>
  <section class="content">
<?php
}

function render_footer() {
?>
  </section>
</main>
</div>
<script src="<?= url('assets/app.js') ?>?v=14"></script>
</body>
</html>
<?php
}
