<?php
require_once __DIR__.'/_init.php';
$school = school_identity();
if(!is_logged_in()){
    $logo = public_file_url($school['logo_path'] ?? '');
    $stats = [
        'guru' => (int)db()->query('SELECT COUNT(*) FROM teachers')->fetchColumn(),
        'jadwal' => (int)db()->query('SELECT COUNT(*) FROM schedules')->fetchColumn(),
        'selesai' => (int)db()->query("SELECT COUNT(*) FROM schedules WHERE status='Selesai'")->fetchColumn(),
        'observasi' => (int)db()->query('SELECT COUNT(*) FROM observations')->fetchColumn(),
        'instrumen' => (int)db()->query('SELECT COUNT(*) FROM instruments WHERE active=1')->fetchColumn(),
    ];
    $pct = $stats['jadwal'] ? round(($stats['selesai']/$stats['jadwal'])*100) : 0;
    $downloadCards = instrument_downloads(true);
?>
<!doctype html>
<html lang="id" data-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($school['school_name']) ?> - Supervisi Akademik</title>
<link rel="stylesheet" href="<?= url('assets/style.css') ?>">
<script>
(function(){
  var t;try{t=localStorage.getItem('smk_theme');}catch(e){}
  if(t!=='dark'&&t!=='light'){t=(window.matchMedia&&window.matchMedia('(prefers-color-scheme:dark)').matches)?'dark':'light';}
  document.documentElement.setAttribute('data-theme',t);
})();
</script>
</head>
<body class="landing-body">
<nav class="landing-nav"><a class="landing-brand" href="<?= url('index.php') ?>" title="Beranda"><?php if($logo): ?><img src="<?= e($logo) ?>" alt="Logo"><?php else: ?><b>SG</b><?php endif; ?><span><?= e($school['school_name'] ?: 'Supervisi Akademik') ?></span></a><div style="display:flex;align-items:center;gap:10px"><button class="theme-toggle" id="themeToggle" title="Toggle tema"><span class="theme-icon">🌙</span></button><a class="btn small" href="login.php">Login</a></div></nav>
<section class="landing-hero"><div><p class="eyebrow">Aplikasi Supervisi Akademik Guru SMK</p><h1>Monitoring supervisi, instrumen, dan laporan dalam satu dashboard.</h1><div class="landing-actions"><a class="btn" href="login.php">Masuk Sistem</a><a class="btn secondary" href="#instrumen">Download Instrumen</a></div></div><div class="progress-card"><div class="progress-ring" style="--pct:<?= $pct ?>"><span><?= $pct ?>%</span></div><b>Progres Pelaksanaan</b><small><?= $stats['selesai'] ?> dari <?= $stats['jadwal'] ?> jadwal selesai</small></div></section>
<section class="landing-stats"><div><b><?= $stats['guru'] ?></b><span>Guru</span></div><div><b><?= $stats['jadwal'] ?></b><span>Jadwal</span></div><div><b><?= $stats['observasi'] ?></b><span>Observasi</span></div><div><b><?= $stats['instrumen'] ?></b><span>Instrumen</span></div></section>
<section id="instrumen" class="landing-section"><h2>Download Instrumen Supervisi</h2><div class="download-grid"><?php foreach($downloadCards as $card): ?><a class="download-tile file-card clean-card" href="instrument_file_download.php?id=<?= $card['id'] ?>"><b><?= e($card['title']) ?></b><span><?= e($card['description'] ?: 'Klik untuk mengunduh instrumen supervisi.') ?></span></a><?php endforeach; ?><?php if(!$downloadCards): ?><div class="empty-download"><b>Belum ada instrumen yang diupload.</b><span>Silakan upload instrumen terlebih dahulu.</span></div><?php endif; ?></div></section>
<script src="<?= url('assets/app.js') ?>"></script>
</body></html>
<?php exit; }
require_login(); render_header('Dashboard');
$stats=[]; foreach(['teachers','schedules','observations','followups','documents'] as $t){ $stats[$t]=(int)db()->query("SELECT COUNT(*) FROM $t")->fetchColumn(); }
$progressDone=(int)db()->query("SELECT COUNT(*) FROM schedules WHERE status='Selesai'")->fetchColumn();
$progressPct=$stats['schedules']?round(($progressDone/$stats['schedules'])*100):0;
$recent=app_query("SELECT s.*,t.name teacher,c.name class_name,sub.name subject FROM schedules s JOIN teachers t ON t.id=s.teacher_id JOIN classes c ON c.id=s.class_id JOIN subjects sub ON sub.id=s.subject_id ORDER BY scheduled_at DESC LIMIT 5")->fetchAll();
?>
<div class="grid"><div class="card stat"><span>Guru</span><b><?= $stats['teachers'] ?></b></div><div class="card stat"><span>Jadwal</span><b><?= $stats['schedules'] ?></b></div><div class="card stat"><span>Observasi</span><b><?= $stats['observations'] ?></b></div><div class="card stat"><span>Progress</span><b><?= $progressPct ?>%</b></div></div>
<div class="card dashboard-progress" style="margin-top:16px"><div><h2>Progress Supervisi Akademik</h2><p class="muted">Ringkasan pelaksanaan supervisi tahun berjalan.</p></div><div class="progress-bar"><span style="width:<?= $progressPct ?>%"></span></div></div>
<div class="card" style="margin-top:16px"><div class="toolbar"><h2>Akses Form Supervisi</h2><a class="btn" href="academic_forms.php">Buka Input Bertahap</a></div><div class="stage-grid"><a href="academic_forms.php?stage=pra_mapel">Pra Mapel</a><a href="academic_forms.php?stage=observasi_mapel">Observasi Mapel</a><a href="academic_forms.php?stage=pasca_mapel">Pasca Mapel</a><a href="academic_forms.php?stage=pra_bk">Pra BK</a><a href="academic_forms.php?stage=observasi_bk">Observasi BK</a><a href="academic_forms.php?stage=pasca_bk">Pasca BK</a></div></div>
<div class="card" style="margin-top:16px"><h2>Jadwal Terbaru</h2><table><tr><th>Tanggal</th><th>Guru</th><th>Mapel/Kelas</th><th>Status</th></tr><?php foreach($recent as $r): ?><tr><td><?= e(date('d/m/Y H:i',strtotime($r['scheduled_at']))) ?></td><td><?= e($r['teacher']) ?></td><td><?= e($r['subject']) ?> / <?= e($r['class_name']) ?></td><td><span class="badge"><?= e($r['status']) ?></span></td></tr><?php endforeach; ?></table></div>
<?php render_footer(); ?>
