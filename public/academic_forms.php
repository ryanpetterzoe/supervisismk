<?php
require_once __DIR__.'/_init.php'; require_login(); verify_csrf();
$u=current_user();
$isGuru=($u['role']??'')==='guru';
$teacherId=(int)($u['teacher_id']??0);
$canInput = has_role(['admin','kepala_sekolah','supervisor','guru']);
$stages=[
 'pra_mapel'=>['Pra Mapel','Mapel',1], 'observasi_mapel'=>['Observasi Mapel','Mapel',2], 'pasca_mapel'=>['Pasca Mapel','Mapel',3],
 'pra_bk'=>['Pra BK','BK',1], 'observasi_bk'=>['Observasi BK','BK',2], 'pasca_bk'=>['Pasca BK','BK',3]
];
$stage=$_GET['stage'] ?? '';
if(!isset($stages[$stage])) $stage='';
function teacher_allowed_for_stage($tid,$teacherType,$order){
    if($order<=1) return true;
    $praStage=$teacherType==='BK'?'pra_bk':'pra_mapel';
    return (bool)app_query('SELECT id FROM academic_supervision_forms WHERE teacher_id=? AND stage=? LIMIT 1',[(int)$tid,$praStage])->fetch();
}
if($_SERVER['REQUEST_METHOD']==='POST'){
    $stagePost=$_POST['stage'] ?? '';
    if(!isset($stages[$stagePost])) die('Tahap tidak valid.');
    [$stageName,$teacherType,$order]=$stages[$stagePost];
    if($isGuru && strpos($stagePost, 'observasi_') === 0){
        http_response_code(403);
        die('Guru tidak dapat mengisi tahap observasi/penilaian. Tahap observasi wajib diisi supervisor/kepala sekolah/admin.');
    }
    $postTeacherId=$isGuru ? $teacherId : (int)($_POST['teacher_id']??0);
    if($isGuru && $teacherId<=0){ flash('error','Akun guru belum dihubungkan dengan data guru.'); redirect('academic_forms.php?stage='.$stagePost); }
    if($postTeacherId<=0){ flash('error','Guru wajib dipilih.'); redirect('academic_forms.php?stage='.$stagePost); }
    if(!teacher_allowed_for_stage($postTeacherId,$teacherType,$order)){ flash('error','Tahap ini belum bisa diisi. Isi form Pra terlebih dahulu agar data guru tersinkron otomatis.'); redirect('academic_forms.php?stage='.$stagePost); }
    $id=(int)($_POST['id']??0);
    $score=($_POST['score']??'')!=='' ? (float)$_POST['score'] : null;
    if($id){
        $check = $isGuru ? app_query('SELECT id FROM academic_supervision_forms WHERE id=? AND teacher_id=?',[$id,$teacherId])->fetch() : app_query('SELECT id FROM academic_supervision_forms WHERE id=?',[$id])->fetch();
        if(!$check){ http_response_code(403); die('Data bukan milik akun ini.'); }
        app_query('UPDATE academic_supervision_forms SET subject_id=?,class_id=?,supervisor_user_id=?,supervision_date=?,focus=?,strengths=?,notes=?,recommendations=?,score=? WHERE id=?',[
            ($_POST['subject_id']??'')!==''?(int)$_POST['subject_id']:null,
            ($_POST['class_id']??'')!==''?(int)$_POST['class_id']:null,
            ($_POST['supervisor_user_id']??'')!==''?(int)$_POST['supervisor_user_id']:null,
            $_POST['supervision_date'] ?: null, $_POST['focus'], $_POST['strengths'], $_POST['notes'], $_POST['recommendations'], $score, $id
        ]);
    } else {
        app_query('INSERT INTO academic_supervision_forms(stage,teacher_type,teacher_id,subject_id,class_id,supervisor_user_id,supervision_date,focus,strengths,notes,recommendations,score,created_by) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)',[
            $stagePost,$teacherType,$postTeacherId,
            ($_POST['subject_id']??'')!==''?(int)$_POST['subject_id']:null,
            ($_POST['class_id']??'')!==''?(int)$_POST['class_id']:null,
            ($_POST['supervisor_user_id']??'')!==''?(int)$_POST['supervisor_user_id']:null,
            $_POST['supervision_date'] ?: null, $_POST['focus'], $_POST['strengths'], $_POST['notes'], $_POST['recommendations'], $score, $u['id']
        ]);
    }
    flash('success',$stageName.' berhasil disimpan.'); redirect('academic_forms.php?stage='.$stagePost);
}
$teachers = $isGuru && $teacherId>0 ? app_query('SELECT * FROM teachers WHERE id=?',[$teacherId])->fetchAll() : app_query('SELECT * FROM teachers ORDER BY name')->fetchAll();
$subjects=app_query('SELECT * FROM subjects ORDER BY name')->fetchAll();
$classes=app_query('SELECT * FROM classes ORDER BY name')->fetchAll();
$supervisors=app_query("SELECT * FROM users WHERE role IN ('admin','kepala_sekolah','supervisor') AND active=1 ORDER BY name")->fetchAll();
$edit=null; if(isset($_GET['edit'])){ $editId=(int)$_GET['edit']; $edit=$isGuru ? app_query('SELECT * FROM academic_supervision_forms WHERE id=? AND teacher_id=?',[$editId,$teacherId])->fetch() : app_query('SELECT * FROM academic_supervision_forms WHERE id=?',[$editId])->fetch(); if($edit) $stage=$edit['stage']; }
$where=''; $params=[];
if($isGuru){ $where=$teacherId>0?'WHERE f.teacher_id=?':'WHERE 1=0'; if($teacherId>0)$params[]=$teacherId; }
elseif($stage){ $where='WHERE f.stage=?'; $params[]=$stage; }
$rows=app_query("SELECT f.*,t.name teacher,sub.name subject,c.name class_name,u.name supervisor FROM academic_supervision_forms f JOIN teachers t ON t.id=f.teacher_id LEFT JOIN subjects sub ON sub.id=f.subject_id LEFT JOIN classes c ON c.id=f.class_id LEFT JOIN users u ON u.id=f.supervisor_user_id $where ORDER BY f.updated_at DESC",$params)->fetchAll();
render_header('Input Bertahap Supervisi');
?>
<div class="card"><h2>Akses Form Supervisi Akademik</h2><p class="muted">Isi data secara berurutan: <b>Pra → Observasi → Pasca</b>. Data guru dari tahap Pra otomatis menjadi acuan untuk tahap berikutnya. Role guru tetap tidak bisa mengisi tahap observasi/penilaian.</p><div class="stage-grid"><?php foreach($stages as $key=>$meta): ?><a class="<?= $stage===$key?'active':'' ?>" href="?stage=<?= e($key) ?>"><?= e($meta[0]) ?></a><?php endforeach; ?></div></div>
<?php if($stage): [$stageName,$teacherType,$order]=$stages[$stage]; ?>
<div class="card" style="margin-top:16px"><h2>Form <?= e($stageName) ?></h2>
<?php if($isGuru && $teacherId<=0): ?><div class="alert error">Akun guru belum dihubungkan ke data guru.</div><?php else: ?>
<form method="post" class="form two"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="stage" value="<?= e($stage) ?>"><input type="hidden" name="id" value="<?= e($edit['id']??'') ?>">
<div><label>Guru <?= e($teacherType) ?></label><?php if($isGuru): ?><input value="<?= e($teachers[0]['name'] ?? '') ?>" readonly><?php else: ?><select name="teacher_id" required><option value="">- Pilih Guru -</option><?php foreach($teachers as $t): ?><option value="<?= $t['id'] ?>" <?= (($edit['teacher_id']??'')==$t['id'])?'selected':'' ?>><?= e($t['name']) ?></option><?php endforeach; ?></select><?php endif; ?></div>
<div><label>Tanggal</label><input type="date" name="supervision_date" value="<?= e($edit['supervision_date']??date('Y-m-d')) ?>"></div>
<div><label>Mapel</label><select name="subject_id"><option value="">- Opsional -</option><?php foreach($subjects as $s): ?><option value="<?= $s['id'] ?>" <?= (($edit['subject_id']??'')==$s['id'])?'selected':'' ?>><?= e($s['name']) ?></option><?php endforeach; ?></select></div>
<div><label>Kelas</label><select name="class_id"><option value="">- Opsional -</option><?php foreach($classes as $c): ?><option value="<?= $c['id'] ?>" <?= (($edit['class_id']??'')==$c['id'])?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
<div><label>Supervisor</label><select name="supervisor_user_id"><option value="">- Pilih -</option><?php foreach($supervisors as $sp): ?><option value="<?= $sp['id'] ?>" <?= (($edit['supervisor_user_id']??'')==$sp['id'])?'selected':'' ?>><?= e($sp['name']) ?></option><?php endforeach; ?></select></div>
<div><label>Skor Ringkas</label><input type="number" min="0" max="100" step="0.01" name="score" value="<?= e($edit['score']??'') ?>" placeholder="khusus observasi/pasca"></div>
<div style="grid-column:1/-1"><label>Fokus / Tujuan</label><textarea name="focus" required><?= e($edit['focus']??'') ?></textarea></div>
<div style="grid-column:1/-1"><label>Praktik Baik / Kekuatan</label><textarea name="strengths"><?= e($edit['strengths']??'') ?></textarea></div>
<div style="grid-column:1/-1"><label>Catatan</label><textarea name="notes"><?= e($edit['notes']??'') ?></textarea></div>
<div style="grid-column:1/-1"><label>Rekomendasi / Rencana Perbaikan</label><textarea name="recommendations"><?= e($edit['recommendations']??'') ?></textarea></div>
<button>Simpan <?= e($stageName) ?></button></form><?php endif; ?></div>
<?php endif; ?>
<div class="card" style="margin-top:16px"><h2>Data Form Bertahap</h2><table><tr><th>Tahap</th><th>Guru</th><th>Mapel/Kelas</th><th>Tanggal</th><th>Skor</th><th>Supervisor</th><th>Aksi</th></tr><?php foreach($rows as $r): ?><tr><td><span class="badge"><?= e($stages[$r['stage']][0] ?? $r['stage']) ?></span></td><td><?= e($r['teacher']) ?></td><td><?= e($r['subject'] ?: '-') ?><br><?= e($r['class_name'] ?: '-') ?></td><td><?= rupiah_date($r['supervision_date']) ?></td><td><?= e($r['score'] ?? '-') ?></td><td><?= e($r['supervisor'] ?: '-') ?></td><td><a class="btn small secondary" href="?edit=<?= $r['id'] ?>">Edit</a></td></tr><?php endforeach; ?></table></div>
<?php render_footer(); ?>
