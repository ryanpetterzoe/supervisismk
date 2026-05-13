<?php
require_once __DIR__.'/_init.php'; require_login();
$school=school_identity();
$type=$_GET['type'] ?? 'Mapel';
$place=$_GET['place'] ?? ($school['city'] ?: '');
$date=$_GET['date'] ?? date('Y-m-d');
$principal=$_GET['principal'] ?? ($school['principal_name'] ?: '');
$principalNip=$_GET['principal_nip'] ?? ($school['principal_nip'] ?: '');
$where="WHERE f.teacher_type=?"; $params=[$type==='BK'?'BK':'Mapel'];
$rows=app_query("SELECT f.*,t.name teacher,sub.name subject,c.name class_name,u.name supervisor FROM academic_supervision_forms f JOIN teachers t ON t.id=f.teacher_id LEFT JOIN subjects sub ON sub.id=f.subject_id LEFT JOIN classes c ON c.id=f.class_id LEFT JOIN users u ON u.id=f.supervisor_user_id $where ORDER BY t.name, f.created_at",$params)->fetchAll();
$logo=public_file_url($school['logo_path'] ?? '');
render_header('Cetak Laporan Supervisi Sekolah');
?>
<div class="toolbar"><a class="btn secondary" href="reports.php">Kembali</a><button class="btn ok" onclick="window.print()">Cetak / Simpan PDF</button></div>
<div class="card no-print"><h2>Parameter Laporan</h2><form method="get" class="form two"><div><label>Jenis Guru</label><select name="type"><option <?= $type==='Mapel'?'selected':'' ?>>Mapel</option><option <?= $type==='BK'?'selected':'' ?>>BK</option></select></div><div><label>Tempat Cetak</label><input name="place" value="<?= e($place) ?>"></div><div><label>Tanggal Cetak</label><input type="date" name="date" value="<?= e($date) ?>"></div><div><label>Nama Kepala Sekolah</label><input name="principal" value="<?= e($principal) ?>"></div><div><label>NIP Kepala Sekolah</label><input name="principal_nip" value="<?= e($principalNip) ?>"></div><div style="align-self:end"><button>Terapkan</button></div></form></div>
<div class="card report-card" style="margin-top:16px">
<div class="letterhead"><?php if($logo): ?><span class="letter-logo-box"><img class="letter-logo" src="<?= e($logo) ?>" alt="Logo"></span><?php endif; ?><div><h2><?= e($school['school_name']) ?></h2><p><?= e($school['address']) ?></p><p>NPSN <?= e($school['npsn'] ?: '-') ?> <?php if($school['phone']): ?> | Telp. <?= e($school['phone']) ?><?php endif; ?></p></div></div><hr class="kop-line">
<h2 class="report-title">Laporan Supervisi Akademik <?= e($type) ?><br><small>Tahun Pelajaran 2025/2026</small></h2>
<table><tr><th>No</th><th>Guru</th><th>Tahap</th><th>Mapel/Kelas</th><th>Tanggal</th><th>Skor</th><th>Rekomendasi</th></tr><?php foreach($rows as $i=>$r): ?><tr><td><?= $i+1 ?></td><td><?= e($r['teacher']) ?></td><td><?= e(str_replace('_',' ',ucwords($r['stage'],'_'))) ?></td><td><?= e($r['subject'] ?: '-') ?><br><?= e($r['class_name'] ?: '-') ?></td><td><?= rupiah_date($r['supervision_date']) ?></td><td><?= e($r['score'] ?? '-') ?></td><td><?= e($r['recommendations']) ?></td></tr><?php endforeach; ?></table>
<div class="signature-grid report-signatures"><div><p>Supervisor,</p><?php if($school['supervisor_signature_path']): ?><img class="sign-img" src="<?= e(public_file_url($school['supervisor_signature_path'])) ?>"><?php else: ?><div class="sign-space"></div><?php endif; ?><p><b><?= e($school['supervisor_name'] ?: '........................') ?></b><br>NIP. <?= e($school['supervisor_nip'] ?: '-') ?></p></div><div><p><?= e($place) ?>, <?= rupiah_date($date) ?><br>Kepala Sekolah,</p><?php if($school['principal_signature_path']): ?><img class="sign-img" src="<?= e(public_file_url($school['principal_signature_path'])) ?>"><?php else: ?><div class="sign-space"></div><?php endif; ?><p><b><?= e($principal ?: '........................') ?></b><br>NIP. <?= e($principalNip ?: '-') ?></p></div></div>
</div>
<?php render_footer(); ?>
