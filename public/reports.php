<?php
require_once __DIR__.'/_init.php'; require_login();
if(isset($_GET['export'])){
 header('Content-Type: text/csv'); header('Content-Disposition: attachment; filename="laporan_supervisi.csv"');
 $out=fopen('php://output','w'); fputcsv($out,['Tanggal','Guru','Observer','Nilai','Predikat','Rekomendasi']);
 $rows=app_query("SELECT o.*,t.name teacher,u.name observer FROM observations o JOIN teachers t ON t.id=o.teacher_id JOIN users u ON u.id=o.observer_user_id ORDER BY o.created_at DESC")->fetchAll();
 foreach($rows as $r){ fputcsv($out,[date('d/m/Y',strtotime($r['created_at'])),$r['teacher'],$r['observer'],$r['final_score'],score_label($r['final_score']),$r['recommendations']]); }
 exit;
}
$rows=app_query("SELECT o.*,t.name teacher,u.name observer FROM observations o JOIN teachers t ON t.id=o.teacher_id JOIN users u ON u.id=o.observer_user_id ORDER BY o.created_at DESC")->fetchAll();
$avg=db()->query('SELECT AVG(final_score) FROM observations')->fetchColumn(); render_header('Laporan Supervisi'); ?>
<div class="toolbar"><div><a class="btn" href="?export=1">Export CSV</a> <a class="btn ok" href="report_school.php">Cetak Laporan Sekolah/PDF</a></div><button onclick="window.print()" class="btn secondary">Cetak Rekap</button></div>
<div class="grid"><div class="card stat"><span>Total Observasi</span><b><?= count($rows) ?></b></div><div class="card stat"><span>Rata-rata Nilai</span><b><?= number_format((float)$avg,1) ?></b></div><div class="card stat"><span>Sangat Baik</span><b><?= count(array_filter($rows,fn($r)=>$r['final_score']>=90)) ?></b></div><div class="card stat"><span>Perlu Pendampingan</span><b><?= count(array_filter($rows,fn($r)=>$r['final_score']<70)) ?></b></div></div>
<div class="card" style="margin-top:16px"><h2>Rekap Observasi</h2><table><tr><th>Tanggal</th><th>Guru</th><th>Observer</th><th>Nilai</th><th>Predikat</th><th>Rekomendasi</th><th>Aksi</th></tr><?php foreach($rows as $r): ?><tr><td><?= rupiah_date($r['created_at']) ?></td><td><?= e($r['teacher']) ?></td><td><?= e($r['observer']) ?></td><td><?= e($r['final_score']) ?></td><td><?= score_label($r['final_score']) ?></td><td><?= e($r['recommendations']) ?></td><td><a class="btn small" href="report_detail.php?id=<?= $r['id'] ?>">Detail</a></td></tr><?php endforeach; ?></table></div><?php render_footer(); ?>
