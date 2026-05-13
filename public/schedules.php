<?php
require_once __DIR__.'/_init.php'; require_login(); verify_csrf();
$canManage = has_role(['admin','kepala_sekolah','supervisor']);
$u = current_user();
if(isset($_GET['delete'])){
    if(!$canManage){ http_response_code(403); die('Guru tidak dapat menghapus jadwal.'); }
    app_query('DELETE FROM schedules WHERE id=?',[(int)$_GET['delete']]); flash('success','Jadwal dihapus.'); redirect('schedules.php');
}
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!$canManage){ http_response_code(403); die('Guru tidak dapat menambahkan atau mengubah jadwal.'); }
    $supervisorId = $_POST['supervisor_id']!==''?(int)$_POST['supervisor_id']:0;
    if($supervisorId<=0){ flash('danger','Supervisor wajib dipilih sebelum jadwal disimpan.'); redirect('schedules.php'); }
    $id=(int)($_POST['id']??0);
    $data=[(int)$_POST['teacher_id'],(int)$_POST['subject_id'],(int)$_POST['class_id'],$supervisorId,$_POST['scheduled_at'],$_POST['status'],$_POST['notes']];
    if($id) app_query('UPDATE schedules SET teacher_id=?,subject_id=?,class_id=?,supervisor_id=?,scheduled_at=?,status=?,notes=? WHERE id=?',[...$data,$id]);
    else app_query('INSERT INTO schedules(teacher_id,subject_id,class_id,supervisor_id,scheduled_at,status,notes) VALUES(?,?,?,?,?,?,?)',$data);
    flash('success','Jadwal disimpan.'); redirect('schedules.php');
}
$edit=null; if($canManage && isset($_GET['edit'])) $edit=app_query('SELECT * FROM schedules WHERE id=?',[(int)$_GET['edit']])->fetch();
$teachers=app_query('SELECT * FROM teachers ORDER BY name')->fetchAll();
$subjects=app_query('SELECT * FROM subjects ORDER BY name')->fetchAll();
$classes=app_query('SELECT * FROM classes ORDER BY name')->fetchAll();
$sup=app_query("SELECT * FROM users WHERE role IN ('admin','kepala_sekolah','supervisor') AND active=1 ORDER BY name")->fetchAll();
$where=''; $params=[];
if(has_role(['guru'])){
    if(!empty($u['teacher_id'])){ $where='WHERE s.teacher_id=?'; $params[]=(int)$u['teacher_id']; }
    else { $where='WHERE 1=0'; }
}
$rows=app_query("SELECT s.*,t.name teacher,c.name class_name,sub.name subject,u.name supervisor FROM schedules s JOIN teachers t ON t.id=s.teacher_id JOIN classes c ON c.id=s.class_id JOIN subjects sub ON sub.id=s.subject_id LEFT JOIN users u ON u.id=s.supervisor_id $where ORDER BY scheduled_at DESC",$params)->fetchAll();
render_header('Jadwal Supervisi'); ?>
<?php if($canManage): ?>
<div class="card"><h2>Form Jadwal</h2><p class="muted">Jadwal hanya dapat dibuat oleh admin, kepala sekolah, atau supervisor. Supervisor wajib dipilih agar penilaian tidak diisi oleh guru sendiri.</p><form method="post" class="form two"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="id" value="<?= e($edit['id']??'') ?>"><div><label>Guru</label><select name="teacher_id" required><?php foreach($teachers as $t): ?><option value="<?= $t['id'] ?>" <?= (($edit['teacher_id']??'')==$t['id'])?'selected':'' ?>><?= e($t['name']) ?></option><?php endforeach; ?></select></div><div><label>Mapel</label><select name="subject_id" required><?php foreach($subjects as $s): ?><option value="<?= $s['id'] ?>" <?= (($edit['subject_id']??'')==$s['id'])?'selected':'' ?>><?= e($s['name']) ?></option><?php endforeach; ?></select></div><div><label>Kelas</label><select name="class_id" required><?php foreach($classes as $c): ?><option value="<?= $c['id'] ?>" <?= (($edit['class_id']??'')==$c['id'])?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div><div><label>Supervisor <b style="color:#dc2626">*</b></label><select name="supervisor_id" required><option value="">- Pilih Supervisor -</option><?php foreach($sup as $su): ?><option value="<?= $su['id'] ?>" <?= (($edit['supervisor_id']??'')==$su['id'])?'selected':'' ?>><?= e($su['name']) ?> (<?= e($su['role']) ?>)</option><?php endforeach; ?></select></div><div><label>Waktu</label><input type="datetime-local" name="scheduled_at" value="<?= $edit?date('Y-m-d\TH:i',strtotime($edit['scheduled_at'])):'' ?>" required></div><div><label>Status</label><select name="status"><?php foreach(['Direncanakan','Berlangsung','Selesai','Dibatalkan'] as $st): ?><option <?= (($edit['status']??'')===$st)?'selected':'' ?>><?= $st ?></option><?php endforeach; ?></select></div><div style="grid-column:1/-1"><label>Catatan</label><textarea name="notes"><?= e($edit['notes']??'') ?></textarea></div><button>Simpan</button></form></div>
<?php else: ?>
<div class="card"><h2>Jadwal Saya</h2><p class="muted">Akun guru hanya dapat melihat jadwal supervisi. Penambahan, perubahan, dan penilaian jadwal dilakukan oleh supervisor yang ditunjuk.</p></div>
<?php endif; ?>
<div class="card" style="margin-top:16px"><table><tr><th>Waktu</th><th>Guru</th><th>Mapel/Kelas</th><th>Supervisor</th><th>Status</th><th>Aksi</th></tr><?php foreach($rows as $r): $isAssigned=((int)($r['supervisor_id']??0)===(int)$u['id']); ?><tr><td><?= e(date('d/m/Y H:i',strtotime($r['scheduled_at']))) ?></td><td><?= e($r['teacher']) ?></td><td><?= e($r['subject']) ?><br><?= e($r['class_name']) ?></td><td><?= e($r['supervisor'] ?: 'Belum dipilih') ?></td><td><span class="badge"><?= e($r['status']) ?></span></td><td><?php if($canManage && $isAssigned): ?><a class="btn small ok" href="observations.php?schedule_id=<?= $r['id'] ?>">Observasi</a><?php endif; ?> <?php if($canManage): ?><a class="btn small secondary" href="?edit=<?= $r['id'] ?>">Edit</a> <a class="btn small danger" data-confirm="Hapus?" href="?delete=<?= $r['id'] ?>">Hapus</a><?php else: ?><span class="muted">Lihat saja</span><?php endif; ?></td></tr><?php endforeach; ?></table></div><?php render_footer(); ?>
