<?php
require_once __DIR__.'/_init.php'; require_login(); verify_csrf();
$u=current_user();
$canObserve=has_role(['admin','kepala_sekolah','supervisor']);
function can_fill_observation(array $schedule, array $user): array {
    if(!in_array($user['role'], ['admin','kepala_sekolah','supervisor'], true)) return [false,'Guru tidak dapat menilai supervisi. Penilaian wajib dilakukan oleh supervisor yang ditunjuk.'];
    if(empty($schedule['supervisor_id'])) return [false,'Jadwal belum memiliki supervisor. Pilih supervisor terlebih dahulu di halaman jadwal.'];
    if((int)$schedule['supervisor_id'] !== (int)$user['id']) return [false,'Observasi hanya boleh diisi oleh supervisor yang ditunjuk pada jadwal ini.'];
    if(!empty($user['teacher_id']) && (int)$user['teacher_id'] === (int)$schedule['teacher_id']) return [false,'Guru tidak boleh menilai supervisinya sendiri. Gunakan akun supervisor/kepala sekolah lain yang ditunjuk.'];
    return [true,''];
}
if($_SERVER['REQUEST_METHOD']==='POST'){
    $schedule=app_query('SELECT * FROM schedules WHERE id=?',[(int)$_POST['schedule_id']])->fetch(); if(!$schedule) die('Jadwal tidak ditemukan.');
    [$allowed,$reason]=can_fill_observation($schedule,$u); if(!$allowed){ http_response_code(403); die($reason); }
    $exists=app_query('SELECT id FROM observations WHERE schedule_id=?',[(int)$_POST['schedule_id']])->fetch();
    if($exists){ flash('danger','Jadwal ini sudah memiliki hasil observasi.'); redirect('observations.php'); }
    $total=0; $weightTotal=0; foreach(($_POST['score']??[]) as $inst=>$score){ $ins=app_query('SELECT weight FROM instruments WHERE id=?',[(int)$inst])->fetch(); $w=(int)($ins['weight']??1); $total += max(0,min(100,(int)$score))*$w; $weightTotal += $w; }
    $final=$weightTotal?round($total/$weightTotal,2):0;
    app_query('INSERT INTO observations(schedule_id,teacher_id,observer_user_id,learning_objectives,good_practices,recommendations,final_score) VALUES(?,?,?,?,?,?,?)',[(int)$_POST['schedule_id'],$schedule['teacher_id'],$u['id'],$_POST['learning_objectives'],$_POST['good_practices'],$_POST['recommendations'],$final]);
    $obs=db()->lastInsertId(); foreach(($_POST['score']??[]) as $inst=>$score){ app_query('INSERT INTO observation_scores(observation_id,instrument_id,score,notes) VALUES(?,?,?,?)',[$obs,(int)$inst,(int)$score,$_POST['score_notes'][$inst]??'']); }
    app_query("UPDATE schedules SET status='Selesai' WHERE id=?",[(int)$_POST['schedule_id']]);
    flash('success','Observasi tersimpan dengan nilai akhir '.$final); redirect('observations.php');
}
$sid=(int)($_GET['schedule_id']??0);
$schedule=$sid?app_query("SELECT s.*,t.name teacher,c.name class_name,sub.name subject,u.name supervisor FROM schedules s JOIN teachers t ON t.id=s.teacher_id JOIN classes c ON c.id=s.class_id JOIN subjects sub ON sub.id=s.subject_id LEFT JOIN users u ON u.id=s.supervisor_id WHERE s.id=?",[$sid])->fetch():null;
$formAllowed=false; $formReason='';
if($schedule){ [$formAllowed,$formReason]=can_fill_observation($schedule,$u); if(app_query('SELECT id FROM observations WHERE schedule_id=?',[$sid])->fetch()){ $formAllowed=false; $formReason='Jadwal ini sudah dinilai. Buka detail laporan pada riwayat observasi.'; } }
$instruments=app_query('SELECT * FROM instruments WHERE active=1 ORDER BY id')->fetchAll();
$where=''; $params=[];
if(has_role(['guru'])){
    if(!empty($u['teacher_id'])){ $where='WHERE o.teacher_id=?'; $params[]=(int)$u['teacher_id']; }
    else { $where='WHERE 1=0'; }
} elseif(has_role(['supervisor'])) {
    $where='WHERE o.observer_user_id=?'; $params[]=(int)$u['id'];
}
$rows=app_query("SELECT o.*,t.name teacher,u.name observer,s.scheduled_at FROM observations o JOIN teachers t ON t.id=o.teacher_id JOIN users u ON u.id=o.observer_user_id JOIN schedules s ON s.id=o.schedule_id $where ORDER BY o.created_at DESC",$params)->fetchAll();
render_header('Observasi Supervisi'); ?>
<?php if($schedule): ?><div class="card"><h2>Form Observasi</h2><p><b><?= e($schedule['teacher']) ?></b> - <?= e($schedule['subject']) ?> / <?= e($schedule['class_name']) ?> - <?= e(date('d/m/Y H:i',strtotime($schedule['scheduled_at']))) ?><br>Supervisor: <b><?= e($schedule['supervisor'] ?: '-') ?></b></p><?php if($formAllowed): ?><form method="post" class="form"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="schedule_id" value="<?= $schedule['id'] ?>"><label>Tujuan Pembelajaran / Fokus Supervisi</label><textarea name="learning_objectives"></textarea><h3>Skor Instrumen (0-100)</h3><table><tr><th>Aspek</th><th>Indikator</th><th>Bobot</th><th>Skor</th><th>Catatan</th></tr><?php foreach($instruments as $i): ?><tr><td><?= e($i['aspect']) ?></td><td><?= e($i['indicator']) ?></td><td><?= e($i['weight']) ?></td><td><input type="number" name="score[<?= $i['id'] ?>]" min="0" max="100" value="80"></td><td><input name="score_notes[<?= $i['id'] ?>]"></td></tr><?php endforeach; ?></table><label>Praktik Baik</label><textarea name="good_practices"></textarea><label>Rekomendasi</label><textarea name="recommendations"></textarea><button>Simpan Observasi</button></form><?php else: ?><div class="alert danger"><?= e($formReason) ?></div><?php endif; ?></div><?php endif; ?>
<div class="card" style="margin-top:16px"><div class="toolbar"><h2>Riwayat Observasi</h2><a class="btn secondary" href="schedules.php">Pilih Jadwal</a></div><table><tr><th>Tanggal</th><th>Guru</th><th>Observer</th><th>Nilai</th><th>Predikat</th><th>Aksi</th></tr><?php foreach($rows as $r): ?><tr><td><?= rupiah_date($r['created_at']) ?></td><td><?= e($r['teacher']) ?></td><td><?= e($r['observer']) ?></td><td><b><?= e($r['final_score']) ?></b></td><td><span class="badge green"><?= score_label($r['final_score']) ?></span></td><td><a class="btn small" href="report_detail.php?id=<?= $r['id'] ?>">Detail/Cetak</a> <?php if(!has_role(['guru'])): ?><a class="btn small ok" href="followups.php?observation_id=<?= $r['id'] ?>">Tindak Lanjut</a><?php endif; ?></td></tr><?php endforeach; ?></table></div><?php render_footer(); ?>
