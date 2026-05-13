<?php
require_once __DIR__.'/_init.php';
require_login();
verify_csrf();

$u = current_user();
$isGuru = ($u['role'] ?? '') === 'guru';
$teacherId = (int)($u['teacher_id'] ?? 0);

function followup_owned_by_current_teacher($followupId, $teacherId){
    if($teacherId <= 0) return false;
    $row = app_query('SELECT f.id FROM followups f JOIN observations o ON o.id=f.observation_id WHERE f.id=? AND o.teacher_id=?', [(int)$followupId, $teacherId])->fetch();
    return (bool)$row;
}
function observation_owned_by_current_teacher($observationId, $teacherId){
    if($teacherId <= 0) return false;
    $row = app_query('SELECT id FROM observations WHERE id=? AND teacher_id=?', [(int)$observationId, $teacherId])->fetch();
    return (bool)$row;
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    $id = (int)($_POST['id'] ?? 0);
    $observationId = (int)($_POST['observation_id'] ?? 0);

    if($isGuru){
        if($teacherId <= 0){
            flash('error','Akun guru belum dihubungkan dengan data guru. Hubungi admin.');
            redirect('followups.php');
        }
        if($id && !followup_owned_by_current_teacher($id, $teacherId)){
            flash('error','Guru hanya boleh mengedit tindak lanjut miliknya sendiri.');
            redirect('followups.php');
        }
        if(!observation_owned_by_current_teacher($observationId, $teacherId)){
            flash('error','Observasi tidak sesuai dengan akun guru yang login.');
            redirect('followups.php');
        }
    }

    $data = [$observationId, $_POST['action_plan'], $_POST['due_date'] ?: null, $_POST['status'], $_POST['result_notes']];
    if($id){
        app_query('UPDATE followups SET observation_id=?,action_plan=?,due_date=?,status=?,result_notes=? WHERE id=?', [...$data, $id]);
    } else {
        app_query('INSERT INTO followups(observation_id,action_plan,due_date,status,result_notes) VALUES(?,?,?,?,?)', $data);
    }
    flash('success','Tindak lanjut disimpan.');
    redirect('followups.php');
}

$edit = null;
if(isset($_GET['edit'])){
    $editId = (int)$_GET['edit'];
    if($isGuru && !followup_owned_by_current_teacher($editId, $teacherId)){
        flash('error','Guru hanya boleh mengedit tindak lanjut miliknya sendiri.');
        redirect('followups.php');
    }
    $edit = app_query('SELECT * FROM followups WHERE id=?', [$editId])->fetch();
}

if($isGuru){
    $obs = $teacherId > 0
        ? app_query('SELECT o.id,o.final_score,t.name teacher FROM observations o JOIN teachers t ON t.id=o.teacher_id WHERE o.teacher_id=? ORDER BY o.created_at DESC', [$teacherId])->fetchAll()
        : [];
    $rows = $teacherId > 0
        ? app_query("SELECT f.*,o.final_score,t.name teacher FROM followups f JOIN observations o ON o.id=f.observation_id JOIN teachers t ON t.id=o.teacher_id WHERE o.teacher_id=? ORDER BY f.created_at DESC", [$teacherId])->fetchAll()
        : [];
} else {
    $obs = app_query('SELECT o.id,o.final_score,t.name teacher FROM observations o JOIN teachers t ON t.id=o.teacher_id ORDER BY o.created_at DESC')->fetchAll();
    $rows = app_query("SELECT f.*,o.final_score,t.name teacher FROM followups f JOIN observations o ON o.id=f.observation_id JOIN teachers t ON t.id=o.teacher_id ORDER BY f.created_at DESC")->fetchAll();
}

render_header('Tindak Lanjut');
?>
<?php if($isGuru && $teacherId <= 0): ?>
<div class="card"><p class="muted">Akun guru belum dihubungkan ke data guru. Hubungi admin agar menu tindak lanjut dan dokumen otomatis tersambung ke akun guru.</p></div>
<?php else: ?>
<div class="card"><h2>Form Tindak Lanjut</h2>
<?php if($isGuru): ?><p class="muted">Akun guru hanya dapat menambah atau mengedit tindak lanjut untuk observasi miliknya sendiri.</p><?php endif; ?>
<form method="post" class="form two"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="id" value="<?= e($edit['id']??'') ?>"><div><label>Observasi</label><select name="observation_id" required><?php foreach($obs as $o): ?><option value="<?= $o['id'] ?>" <?= (($edit['observation_id']??($_GET['observation_id']??''))==$o['id'])?'selected':'' ?>>#<?= $o['id'] ?> - <?= e($o['teacher']) ?> (<?= e($o['final_score']) ?>)</option><?php endforeach; ?></select></div><div><label>Deadline</label><input type="date" name="due_date" value="<?= e($edit['due_date']??'') ?>"></div><div><label>Status</label><select name="status"><?php foreach(['Belum Mulai','Proses','Selesai'] as $st): ?><option <?= (($edit['status']??'')===$st)?'selected':'' ?>><?= $st ?></option><?php endforeach; ?></select></div><div style="grid-column:1/-1"><label>Rencana Aksi</label><textarea name="action_plan" required><?= e($edit['action_plan']??'') ?></textarea></div><div style="grid-column:1/-1"><label>Hasil / Catatan</label><textarea name="result_notes"><?= e($edit['result_notes']??'') ?></textarea></div><button>Simpan</button></form></div>
<div class="card" style="margin-top:16px"><table><tr><th>Guru</th><th>Rencana Aksi</th><th>Deadline</th><th>Status</th><th>Aksi</th></tr><?php foreach($rows as $r): ?><tr><td><?= e($r['teacher']) ?></td><td><?= e($r['action_plan']) ?></td><td><?= rupiah_date($r['due_date']) ?></td><td><span class="badge"><?= e($r['status']) ?></span></td><td><a class="btn small secondary" href="?edit=<?= $r['id'] ?>">Edit</a></td></tr><?php endforeach; ?></table></div>
<?php endif; ?>
<?php render_footer(); ?>
