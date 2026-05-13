<?php
require_once __DIR__.'/_init.php'; require_login(); verify_csrf();
$can_manage = has_role(['admin','kepala_sekolah','supervisor']);
if(isset($_GET['delete'])){
    if(!$can_manage){ flash('error','Akses ditolak. Data guru hanya boleh diubah oleh Kepala Sekolah, Supervisor, atau Admin.'); redirect('teachers.php'); }
    app_query('DELETE FROM teachers WHERE id=?',[(int)$_GET['delete']]); flash('success','Data guru dihapus.'); redirect('teachers.php');
}
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!$can_manage){ flash('error','Akses ditolak. Data guru hanya boleh diubah oleh Kepala Sekolah, Supervisor, atau Admin.'); redirect('teachers.php'); }
    $id=(int)($_POST['id']??0); $data=[$_POST['nip'],$_POST['name'],$_POST['gender'],$_POST['phone'],$_POST['email'],$_POST['expertise'],$_POST['status']];
    if($id){ app_query('UPDATE teachers SET nip=?,name=?,gender=?,phone=?,email=?,expertise=?,status=? WHERE id=?',[...$data,$id]); flash('success','Data guru diperbarui.'); }
    else { app_query('INSERT INTO teachers(nip,name,gender,phone,email,expertise,status) VALUES(?,?,?,?,?,?,?)',$data); flash('success','Data guru ditambahkan.'); }
    redirect('teachers.php');
}
$edit=null; if($can_manage && isset($_GET['edit'])) $edit=app_query('SELECT * FROM teachers WHERE id=?',[(int)$_GET['edit']])->fetch();
$rows=app_query('SELECT * FROM teachers ORDER BY name')->fetchAll(); render_header('Data Guru'); ?>
<?php if($can_manage): ?>
<div class="card"><h2><?= $edit?'Edit':'Tambah' ?> Guru</h2><form method="post" class="form two"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="id" value="<?= e($edit['id']??'') ?>"><div><label>NIP/NUPTK</label><input name="nip" value="<?= e($edit['nip']??'') ?>"></div><div><label>Nama Guru</label><input name="name" required value="<?= e($edit['name']??'') ?>"></div><div><label>Gender</label><select name="gender"><option value="L">Laki-laki</option><option value="P" <?= (($edit['gender']??'')==='P')?'selected':'' ?>>Perempuan</option></select></div><div><label>No. HP</label><input name="phone" value="<?= e($edit['phone']??'') ?>"></div><div><label>Email</label><input name="email" value="<?= e($edit['email']??'') ?>"></div><div><label>Kompetensi/Keahlian</label><input name="expertise" value="<?= e($edit['expertise']??'') ?>"></div><div><label>Status</label><input name="status" value="<?= e($edit['status']??'Aktif') ?>"></div><div style="align-self:end"><button>Simpan</button></div></form></div>
<?php else: ?>
<div class="card"><h2>Data Guru</h2><p class="muted">Mode lihat saja. Data guru hanya dapat ditambah, diedit, atau dihapus oleh Kepala Sekolah, Supervisor, atau Admin.</p></div>
<?php endif; ?>
<div class="card" style="margin-top:16px"><h2>Daftar Guru</h2><table><tr><th>Nama</th><th>NIP</th><th>Keahlian</th><th>Kontak</th><?php if($can_manage): ?><th>Aksi</th><?php endif; ?></tr><?php foreach($rows as $r): ?><tr><td><?= e($r['name']) ?></td><td><?= e($r['nip']) ?></td><td><?= e($r['expertise']) ?></td><td><?= e($r['phone']) ?><br><?= e($r['email']) ?></td><?php if($can_manage): ?><td><a class="btn small secondary" href="?edit=<?= $r['id'] ?>">Edit</a> <a class="btn small danger" data-confirm="Hapus data ini?" href="?delete=<?= $r['id'] ?>">Hapus</a></td><?php endif; ?></tr><?php endforeach; ?></table></div><?php render_footer(); ?>
