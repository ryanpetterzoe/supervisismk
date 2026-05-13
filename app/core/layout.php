<?php
function render_header($title='Dashboard'){
    $u=current_user();
    $flash=flash();
    $school=school_identity();
    $logoUrl=public_file_url($school['logo_path'] ?? '');
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> - <?= e($school['school_name'] ?: cfg('app_name')) ?></title>
<link rel="stylesheet" href="<?= url('assets/style.css') ?>">
</head>
<body>
<div class="app-shell">
<aside class="sidebar">
  <a class="brand" href="<?= url('index.php') ?>" title="Kembali ke beranda"><div class="logo"><?php if($logoUrl): ?><span class="app-logo-box"><img class="app-logo-img" src="<?= e($logoUrl) ?>" alt="Logo" width="36" height="36" style="display:block;width:36px!important;height:36px!important;max-width:36px!important;max-height:36px!important;object-fit:contain!important;border-radius:10px!important;padding:2px!important;background:#fff!important;"></span><?php else: ?>SG<?php endif; ?></div><div><b><?= e($school['school_name'] ?: 'Supervisi Guru') ?></b><span>SMK Kurikulum Merdeka</span></div></a>
  <?php if($u): ?>
  <nav>
    <a href="<?= url('index.php') ?>">Dashboard</a>
    <a href="<?= url('teachers.php') ?>">Data Guru</a>
    <a href="<?= url('subjects.php') ?>">Mapel</a>
    <a href="<?= url('classes.php') ?>">Kelas</a>
    <a href="<?= url('instruments.php') ?>">Instrumen</a>
    <?php if(has_role(['admin','kepala_sekolah','supervisor'])): ?><a href="<?= url('instrument_files.php') ?>">Upload Instrumen</a><?php endif; ?>
    <a href="<?= url('schedules.php') ?>">Jadwal Supervisi</a>
    <a href="<?= url('observations.php') ?>">Observasi</a>
    <a href="<?= url('academic_forms.php') ?>">Input Bertahap</a>
    <a href="<?= url('followups.php') ?>">Tindak Lanjut</a>
    <a href="<?= url('documents.php') ?>">Dokumen</a>
    <a href="<?= url('reports.php') ?>">Laporan</a>
    <?php if(has_role(['admin','kepala_sekolah'])): ?><a href="<?= url('school_identity.php') ?>">Identitas Sekolah</a><?php endif; ?>
    <?php if(has_role(['admin'])): ?><a href="<?= url('users.php') ?>">Pengguna</a><?php endif; ?>
  </nav>
  <?php endif; ?>
</aside>
<main class="main">
<header class="topbar">
  <div><h1><?= e($title) ?></h1><p><?= date('l, d F Y') ?></p></div>
  <?php if($u): ?><div class="userbox"><span><?= e($u['name']) ?></span><small><?= e($u['role']) ?></small><a class="btn small danger" href="<?= url('logout.php') ?>">Keluar</a></div><?php endif; ?>
</header>
<?php if($flash): ?><div class="alert <?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div><?php endif; ?>
<section class="content">
<?php }
function render_footer(){ ?>
</section>
</main>
</div>
<script src="<?= url('assets/app.js') ?>"></script>
</body></html>
<?php }
