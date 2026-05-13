<?php
require_once __DIR__.'/_init.php';
require_login();
verify_csrf();

$u = current_user();
$isGuru = ($u['role'] ?? '') === 'guru';
$teacherId = (int)($u['teacher_id'] ?? 0);
$uploadDir = ROOT_PATH.'/storage/uploads/';

if(isset($_GET['delete'])){
    $docId = (int)$_GET['delete'];
    if($isGuru){
        $d = $teacherId > 0 ? app_query('SELECT * FROM documents WHERE id=? AND teacher_id=?', [$docId, $teacherId])->fetch() : null;
    } else {
        $d = app_query('SELECT * FROM documents WHERE id=?', [$docId])->fetch();
    }
    if($d){
        @unlink($uploadDir.$d['file_name']);
        app_query('DELETE FROM documents WHERE id=?', [$docId]);
        flash('success','Dokumen dihapus.');
    } else {
        flash('error','Dokumen tidak ditemukan atau bukan milik akun ini.');
    }
    redirect('documents.php');
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    if($isGuru){
        if($teacherId <= 0){
            flash('error','Akun guru belum dihubungkan dengan data guru. Hubungi admin.');
            redirect('documents.php');
        }
        $postTeacherId = $teacherId; // Guru selalu otomatis memakai akun guru yang sedang login.
    } else {
        $postTeacherId = ($_POST['teacher_id'] ?? '') !== '' ? (int)$_POST['teacher_id'] : null;
    }

    $fileName = '';
    if(!empty($_FILES['file']['name'])){
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf','doc','docx','xls','xlsx','jpg','jpeg','png'];
        if(!in_array($ext, $allowed, true)) die('Format file tidak diizinkan.');
        $fileName = date('YmdHis').'_'.preg_replace('/[^a-zA-Z0-9._-]/','_',$_FILES['file']['name']);
        move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir.$fileName);
    }
    app_query('INSERT INTO documents(teacher_id,title,category,file_name,notes,uploaded_by) VALUES(?,?,?,?,?,?)', [$postTeacherId, $_POST['title'], $_POST['category'], $fileName, $_POST['notes'], $u['id']]);
    flash('success','Dokumen diupload.');
    redirect('documents.php');
}

$teachers = app_query('SELECT * FROM teachers ORDER BY name')->fetchAll();
$currentTeacher = $teacherId > 0 ? app_query('SELECT * FROM teachers WHERE id=?', [$teacherId])->fetch() : null;

if($isGuru){
    $rows = $teacherId > 0
        ? app_query('SELECT d.*,t.name teacher,u.name uploader FROM documents d LEFT JOIN teachers t ON t.id=d.teacher_id LEFT JOIN users u ON u.id=d.uploaded_by WHERE d.teacher_id=? ORDER BY d.created_at DESC', [$teacherId])->fetchAll()
        : [];
} else {
    $rows = app_query('SELECT d.*,t.name teacher,u.name uploader FROM documents d LEFT JOIN teachers t ON t.id=d.teacher_id LEFT JOIN users u ON u.id=d.uploaded_by ORDER BY d.created_at DESC')->fetchAll();
}

render_header('Dokumen Pendukung');
?>
<?php if($isGuru && $teacherId <= 0): ?>
<div class="card"><p class="muted">Akun guru belum dihubungkan ke data guru. Hubungi admin agar upload dokumen otomatis tersambung ke akun guru.</p></div>
<?php else: ?>
<div class="card"><h2>Upload Dokumen</h2>
<?php if($isGuru): ?><p class="muted">Dokumen yang diupload otomatis terkait ke akun guru: <b><?= e($currentTeacher['name'] ?? $u['name']) ?></b>. Guru tidak dapat memilih akun guru lain.</p><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="form two"><input type="hidden" name="csrf" value="<?= csrf_token() ?>">
<?php if($isGuru): ?>
<input type="hidden" name="teacher_id" value="<?= $teacherId ?>"><div><label>Guru Terkait</label><input value="<?= e($currentTeacher['name'] ?? $u['name']) ?>" readonly></div>
<?php else: ?>
<div><label>Guru Terkait</label><select name="teacher_id"><option value="">Umum</option><?php foreach($teachers as $t): ?><option value="<?= $t['id'] ?>"><?= e($t['name']) ?></option><?php endforeach; ?></select></div>
<?php endif; ?>
<div><label>Kategori</label><select name="category"><option>Modul Ajar</option><option>ATP/CP</option><option>Asesmen</option><option>Foto Kegiatan</option><option>Bukti Tindak Lanjut</option><option>Lainnya</option></select></div><div><label>Judul</label><input name="title" required></div><div><label>File</label><input type="file" name="file" required></div><div style="grid-column:1/-1"><label>Catatan</label><textarea name="notes"></textarea></div><button>Upload</button></form></div>
<div class="card" style="margin-top:16px"><table><tr><th>Judul</th><th>Kategori</th><th>Guru</th><th>File</th><th>Uploader</th><th>Aksi</th></tr><?php foreach($rows as $r): ?><tr><td><?= e($r['title']) ?><br><small><?= e($r['notes']) ?></small></td><td><?= e($r['category']) ?></td><td><?= e($r['teacher'] ?: 'Umum') ?></td><td><?php if($r['file_name']): ?><a class="btn small secondary" href="../storage/uploads/<?= e($r['file_name']) ?>" target="_blank">Buka</a><?php endif; ?></td><td><?= e($r['uploader']) ?></td><td><a class="btn small danger" data-confirm="Hapus?" href="?delete=<?= $r['id'] ?>">Hapus</a></td></tr><?php endforeach; ?></table></div>
<?php endif; ?>
<?php render_footer(); ?>
