<?php
require_once __DIR__.'/_init.php'; require_login();
$id=(int)($_GET['id']??0);
$o=app_query("SELECT o.*,t.name teacher,t.nip,u.name observer,u.teacher_id observer_teacher_id,s.scheduled_at,c.name class_name,sub.name subject FROM observations o JOIN teachers t ON t.id=o.teacher_id JOIN users u ON u.id=o.observer_user_id JOIN schedules s ON s.id=o.schedule_id JOIN classes c ON c.id=s.class_id JOIN subjects sub ON sub.id=s.subject_id WHERE o.id=?",[$id])->fetch(); if(!$o) die('Laporan tidak ditemukan.');
$scores=app_query("SELECT os.*,i.aspect,i.indicator,i.weight FROM observation_scores os JOIN instruments i ON i.id=os.instrument_id WHERE os.observation_id=?",[$id])->fetchAll();
$fups=app_query('SELECT * FROM followups WHERE observation_id=? ORDER BY created_at DESC',[$id])->fetchAll();
$school=school_identity();
$supervisorName=$school['supervisor_name'] ?: $o['observer'];
$supervisorNip=$school['supervisor_nip'] ?: '';
if(!empty($o['observer_teacher_id'])){
    $supTeacher=app_query('SELECT nip FROM teachers WHERE id=?',[(int)$o['observer_teacher_id']])->fetch();
    if($supTeacher && !$supervisorNip) $supervisorNip=$supTeacher['nip'];
}
render_header('Detail Laporan Supervisi'); ?>
<div class="toolbar"><a class="btn secondary" href="reports.php">Kembali</a><button class="btn" onclick="window.print()">Cetak</button></div>
<div class="card report-card">
  <div class="letterhead">
    <?php if($school['logo_path']): ?><span class="letter-logo-box"><img class="letter-logo" src="<?= e(public_file_url($school['logo_path'])) ?>" alt="Logo sekolah" width="72" height="72" style="display:block;width:72px!important;height:72px!important;max-width:72px!important;max-height:72px!important;object-fit:contain!important;"></span><?php endif; ?>
    <div>
      <h2><?= e($school['school_name']) ?></h2>
      <p><?= e($school['address']) ?></p>
      <p><?php if($school['phone']): ?>Telp. <?= e($school['phone']) ?><?php endif; ?> <?php if($school['email']): ?>Email: <?= e($school['email']) ?><?php endif; ?></p>
      <p><?php if($school['website']): ?>Website: <?= e($school['website']) ?><?php endif; ?> <?php if($school['npsn']): ?>NPSN: <?= e($school['npsn']) ?><?php endif; ?></p>
    </div>
  </div>
  <hr class="kop-line">
  <h2 class="report-title">Laporan Supervisi Pembelajaran<br><small>Implementasi Kurikulum Merdeka SMK</small></h2>
  <table class="compact"><tr><th>Guru</th><td><?= e($o['teacher']) ?> / <?= e($o['nip']) ?></td></tr><tr><th>Mapel/Kelas</th><td><?= e($o['subject']) ?> / <?= e($o['class_name']) ?></td></tr><tr><th>Waktu Supervisi</th><td><?= e(date('d/m/Y H:i',strtotime($o['scheduled_at']))) ?></td></tr><tr><th>Supervisor/Observer</th><td><?= e($o['observer']) ?></td></tr><tr><th>Nilai Akhir</th><td><b><?= e($o['final_score']) ?> - <?= score_label($o['final_score']) ?></b></td></tr></table>
  <h3>Tujuan/Fokus</h3><p><?= nl2br(e($o['learning_objectives'])) ?></p>
  <h3>Skor Instrumen</h3><table><tr><th>Aspek</th><th>Indikator</th><th>Bobot</th><th>Skor</th><th>Catatan</th></tr><?php foreach($scores as $s): ?><tr><td><?= e($s['aspect']) ?></td><td><?= e($s['indicator']) ?></td><td><?= e($s['weight']) ?></td><td><?= e($s['score']) ?></td><td><?= e($s['notes']) ?></td></tr><?php endforeach; ?></table>
  <h3>Praktik Baik</h3><p><?= nl2br(e($o['good_practices'])) ?></p>
  <h3>Rekomendasi</h3><p><?= nl2br(e($o['recommendations'])) ?></p>
  <h3>Tindak Lanjut</h3><table><tr><th>Rencana</th><th>Deadline</th><th>Status</th><th>Hasil</th></tr><?php foreach($fups as $f): ?><tr><td><?= e($f['action_plan']) ?></td><td><?= rupiah_date($f['due_date']) ?></td><td><?= e($f['status']) ?></td><td><?= e($f['result_notes']) ?></td></tr><?php endforeach; ?></table>
  <div class="signature-grid report-signatures">
    <div>
      <p>Supervisor,</p>
      <?php if($school['supervisor_signature_path']): ?><img class="sign-img" src="<?= e(public_file_url($school['supervisor_signature_path'])) ?>" alt="TTD supervisor"><?php else: ?><div class="sign-space"></div><?php endif; ?>
      <p><b><?= e($supervisorName ?: '........................') ?></b><br>NIP. <?= e($supervisorNip ?: '-') ?></p>
    </div>
    <div>
      <p><?= e($school['city'] ?: '') ?>, <?= date('d/m/Y') ?><br>Kepala Sekolah,</p>
      <?php if($school['principal_signature_path']): ?><img class="sign-img" src="<?= e(public_file_url($school['principal_signature_path'])) ?>" alt="TTD kepala sekolah"><?php else: ?><div class="sign-space"></div><?php endif; ?>
      <p><b><?= e($school['principal_name'] ?: '........................') ?></b><br>NIP. <?= e($school['principal_nip'] ?: '-') ?></p>
    </div>
  </div>
</div><?php render_footer(); ?>
