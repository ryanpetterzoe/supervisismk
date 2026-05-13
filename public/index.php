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
<style>
:root{--bg:#f1f5f9;--surface:#fff;--surface2:#f8fafc;--text:#0f172a;--text2:#334155;--text3:#64748b;--border:#e2e8f0;--primary:#2563eb;--primary-light:#eff6ff}
[data-theme="dark"],html.dark{--bg:#0f172a;--surface:#1e293b;--surface2:#1a2540;--text:#f1f5f9;--text2:#cbd5e1;--text3:#94a3b8;--border:#2d3f55;--primary:#3b82f6;--primary-light:#1e3a5e}
body{background-color:var(--bg);color:var(--text);transition:background-color .2s,color .2s}
</style>
<script>
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
<body class="landing-body">
<nav class="landing-nav">
  <a class="landing-brand" href="<?= url('index.php') ?>">
    <?php if($logo): ?><img src="<?= e($logo) ?>" alt="Logo"><?php else: ?><b>SG</b><?php endif; ?>
    <span><?= e($school['school_name'] ?: 'Supervisi Akademik') ?></span>
  </a>
  <div style="display:flex;align-items:center;gap:10px">
    <button class="theme-toggle" id="themeToggle" title="Toggle tema"><span class="theme-icon">🌙</span></button>
    <a class="btn small" href="login.php">Login</a>
  </div>
</nav>

<!-- Hero Section with optional banner -->
<section class="landing-hero" style="<?php if($school['banner_path'] ?? ''): ?>background-image:linear-gradient(rgba(0,0,0,0.65),rgba(0,0,0,0.75)),url('<?= e(public_file_url($school['banner_path'])) ?>');background-size:cover;background-position:center<?php else: ?>background:linear-gradient(135deg,<?= e($school['header_color'] ?? '#2563eb') ?>,<?= e($school['accent_color'] ?? '#7c3aed') ?>)<?php endif; ?>;color:#fff;min-height:340px">
  <div>
    <p class="eyebrow" style="color:#fff;font-weight:700;text-shadow:0 1px 6px rgba(0,0,0,.5)"><?= e($school['landing_subtitle'] ?? 'Aplikasi Supervisi Akademik Guru SMK') ?></p>
    <h1 style="color:#fff;text-shadow:0 3px 20px rgba(0,0,0,.7)"><?= e($school['landing_title'] ?? 'Monitoring supervisi, instrumen, dan laporan dalam satu dashboard.') ?></h1>
    <div class="landing-actions">
      <a class="btn large" href="login.php"><?= e($school['landing_cta_text'] ?? 'Masuk Sistem') ?></a>
      <a class="btn secondary large" href="#instrumen">Download Instrumen</a>
    </div>
  </div>
  <div class="progress-card">
    <div class="progress-ring" style="--pct:<?= $pct ?>"><span><?= $pct ?>%</span></div>
    <b>Progres Pelaksanaan</b>
    <small><?= $stats['selesai'] ?> dari <?= $stats['jadwal'] ?> jadwal selesai</small>
  </div>
</section>

<!-- Stats -->
<section class="landing-stats">
  <div><b><?= $stats['guru'] ?></b><span>Guru Terdaftar</span></div>
  <div><b><?= $stats['jadwal'] ?></b><span>Jadwal Supervisi</span></div>
  <div><b><?= $stats['observasi'] ?></b><span>Observasi Selesai</span></div>
  <div><b><?= $stats['instrumen'] ?></b><span>Instrumen Aktif</span></div>
</section>

<!-- Download Instrumen -->
<section id="instrumen" class="landing-section">
  <h2>Download Instrumen Supervisi</h2>
  <div class="download-grid">
    <?php foreach($downloadCards as $card): ?>
    <a class="download-tile" href="instrument_file_download.php?id=<?= $card['id'] ?>">
      <b><?= e($card['title']) ?></b>
      <span><?= e($card['description'] ?: 'Klik untuk mengunduh instrumen supervisi.') ?></span>
    </a>
    <?php endforeach; ?>
    <?php if(!$downloadCards): ?>
    <div class="empty-download">
      <b>Belum ada instrumen yang diupload.</b>
      <span>Silakan upload instrumen terlebih dahulu melalui menu admin.</span>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php if($school['landing_footer_text'] ?? ''): ?>
<footer style="text-align:center;padding:24px;color:var(--text3);font-size:13px;border-top:1px solid var(--border)">
  <?= e($school['landing_footer_text']) ?>
</footer>
<?php endif; ?>

<script src="<?= url('assets/app.js') ?>?v=14"></script>
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
