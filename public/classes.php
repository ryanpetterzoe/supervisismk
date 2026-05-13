<?php
require_once __DIR__.'/_init.php'; require_login(); verify_csrf();
$can_manage = has_role(['admin','kepala_sekolah','supervisor']);
if(isset($_GET['delete'])){
    if(!$can_manage){ flash('error','Akses ditolak. Kelas hanya boleh diubah oleh Kepala Sekolah, Supervisor, atau Admin.'); redirect('classes.php'); }
    app_query('DELETE FROM classes WHERE id=?',[(int)$_GET['delete']]); flash('success','Data dihapus.'); redirect('classes.php');
}
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!$can_manage){ flash('error','Akses ditolak. Kelas hanya boleh diubah oleh Kepala Sekolah, Supervisor, atau Admin.'); redirect('classes.php'); }
    $id=(int)($_POST['id']??0); $data=[$_POST['name'],$_POST['major'],$_POST['level']]; if($id){ app_query('UPDATE classes SET name=?,major=?,level=? WHERE id=?',[...$data,$id]); } else { app_query('INSERT INTO classes(name,major,level) VALUES(?,?,?)',$data); } flash('success','Data disimpan.'); redirect('classes.php');
}
$edit=null; if($can_manage && isset($_GET['edit'])) $edit=app_query('SELECT * FROM classes WHERE id=?',[(int)$_GET['edit']])->fetch(); $rows=app_query('SELECT * FROM classes ORDER BY id DESC')->fetchAll(); render_header('Kelas'); ?>
<?php if($can_manage): ?>
<div class="card"><h2>Form Kelas</h2><form method="post" class="form two"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="id" value="<?= e($edit['id']??'') ?>"><div><label>Nama Kelas</label><input name="name" required value="<?= e($edit['name']??'') ?>"></div><div><label>Jurusan</label><input name="major" required value="<?= e($edit['major']??'') ?>"></div><div><label>Tingkat</label><input name="level" required value="<?= e($edit['level']??'') ?>"></div><div style="align-self:end"><button>Simpan</button></div></form></div>
<?php else: ?>
<div class="card"><h2>Kelas</h2><p class="muted">Mode lihat saja. Kelas hanya dapat ditambah, diedit, atau dihapus oleh Kepala Sekolah, Supervisor, atau Admin.</p></div>
<?php endif; ?>
<div class="card" style="margin-top:16px"><h2>Daftar Kelas</h2><table><tr><th>Nama Kelas</th><th>Jurusan</th><th>Tingkat</th><?php if($can_manage): ?><th>Aksi</th><?php endif; ?></tr><?php foreach($rows as $r): ?><tr><td><?= e($r['name']) ?></td><td><?= e($r['major']) ?></td><td><?= e($r['level']) ?></td><?php if($can_manage): ?><td><a class="btn small secondary" href="?edit=<?= $r['id'] ?>">Edit</a> <a class="btn small danger" data-confirm="Hapus?" href="?delete=<?= $r['id'] ?>">Hapus</a></td><?php endif; ?></tr><?php endforeach; ?></table></div><?php render_footer(); ?>
