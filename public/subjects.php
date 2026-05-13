<?php
require_once __DIR__.'/_init.php'; require_login(); verify_csrf();
$can_manage = has_role(['admin','kepala_sekolah','supervisor']);
if(isset($_GET['delete'])){
    if(!$can_manage){ flash('error','Akses ditolak. Mapel hanya boleh diubah oleh Kepala Sekolah, Supervisor, atau Admin.'); redirect('subjects.php'); }
    app_query('DELETE FROM subjects WHERE id=?',[(int)$_GET['delete']]); flash('success','Data dihapus.'); redirect('subjects.php');
}
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!$can_manage){ flash('error','Akses ditolak. Mapel hanya boleh diubah oleh Kepala Sekolah, Supervisor, atau Admin.'); redirect('subjects.php'); }
    $id=(int)($_POST['id']??0); $data=[$_POST['name'],$_POST['phase'],$_POST['area']]; if($id){ app_query('UPDATE subjects SET name=?,phase=?,area=? WHERE id=?',[...$data,$id]); } else { app_query('INSERT INTO subjects(name,phase,area) VALUES(?,?,?)',$data); } flash('success','Data disimpan.'); redirect('subjects.php');
}
$edit=null; if($can_manage && isset($_GET['edit'])) $edit=app_query('SELECT * FROM subjects WHERE id=?',[(int)$_GET['edit']])->fetch(); $rows=app_query('SELECT * FROM subjects ORDER BY id DESC')->fetchAll(); render_header('Mapel'); ?>
<?php if($can_manage): ?>
<div class="card"><h2>Form Mapel</h2><form method="post" class="form two"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="id" value="<?= e($edit['id']??'') ?>"><div><label>Nama Mapel</label><input name="name" required value="<?= e($edit['name']??'') ?>"></div><div><label>Fase</label><input name="phase" required value="<?= e($edit['phase']??'') ?>"></div><div><label>Kelompok/Bidang</label><input name="area" required value="<?= e($edit['area']??'') ?>"></div><div style="align-self:end"><button>Simpan</button></div></form></div>
<?php else: ?>
<div class="card"><h2>Mapel</h2><p class="muted">Mode lihat saja. Mapel hanya dapat ditambah, diedit, atau dihapus oleh Kepala Sekolah, Supervisor, atau Admin.</p></div>
<?php endif; ?>
<div class="card" style="margin-top:16px"><h2>Daftar Mapel</h2><table><tr><th>Nama Mapel</th><th>Fase</th><th>Kelompok/Bidang</th><?php if($can_manage): ?><th>Aksi</th><?php endif; ?></tr><?php foreach($rows as $r): ?><tr><td><?= e($r['name']) ?></td><td><?= e($r['phase']) ?></td><td><?= e($r['area']) ?></td><?php if($can_manage): ?><td><a class="btn small secondary" href="?edit=<?= $r['id'] ?>">Edit</a> <a class="btn small danger" data-confirm="Hapus?" href="?delete=<?= $r['id'] ?>">Hapus</a></td><?php endif; ?></tr><?php endforeach; ?></table></div><?php render_footer(); ?>
