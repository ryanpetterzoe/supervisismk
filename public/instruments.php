<?php
require_once __DIR__.'/_init.php'; require_login(); verify_csrf();
$can_manage = has_role(['admin','kepala_sekolah','supervisor']);
if(isset($_GET['delete'])){
    if(!$can_manage){ flash('error','Akses ditolak. Instrumen hanya boleh diubah oleh Kepala Sekolah, Supervisor, atau Admin.'); redirect('instruments.php'); }
    app_query('DELETE FROM instruments WHERE id=?',[(int)$_GET['delete']]); flash('success','Instrumen dihapus.'); redirect('instruments.php');
}
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!$can_manage){ flash('error','Akses ditolak. Instrumen hanya boleh diubah oleh Kepala Sekolah, Supervisor, atau Admin.'); redirect('instruments.php'); }
    $id=(int)($_POST['id']??0); $data=[$_POST['aspect'],$_POST['indicator'],(int)$_POST['weight'], isset($_POST['active'])?1:0]; if($id) app_query('UPDATE instruments SET aspect=?,indicator=?,weight=?,active=? WHERE id=?',[...$data,$id]); else app_query('INSERT INTO instruments(aspect,indicator,weight,active) VALUES(?,?,?,?)',$data); flash('success','Instrumen disimpan.'); redirect('instruments.php');
}
$edit=null; if($can_manage && isset($_GET['edit'])) $edit=app_query('SELECT * FROM instruments WHERE id=?',[(int)$_GET['edit']])->fetch(); $rows=app_query('SELECT * FROM instruments ORDER BY active DESC,id')->fetchAll(); render_header('Instrumen Supervisi'); ?>
<?php if($can_manage): ?>
<div class="card"><h2>Form Instrumen</h2><form method="post" class="form"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="id" value="<?= e($edit['id']??'') ?>"><label>Aspek</label><input name="aspect" required value="<?= e($edit['aspect']??'') ?>"><label>Indikator</label><textarea name="indicator" required><?= e($edit['indicator']??'') ?></textarea><div class="form two"><div><label>Bobot</label><input type="number" name="weight" min="1" value="<?= e($edit['weight']??1) ?>"></div><div><label><input type="checkbox" name="active" <?= (($edit['active']??1)?'checked':'') ?>> Aktif</label></div></div><button>Simpan</button></form></div>
<?php else: ?>
<div class="card"><h2>Instrumen Supervisi</h2><p class="muted">Mode lihat saja. Instrumen hanya dapat ditambah, diedit, atau dihapus oleh Kepala Sekolah, Supervisor, atau Admin.</p></div>
<?php endif; ?>
<div class="card" style="margin-top:16px"><table><tr><th>Aspek</th><th>Indikator</th><th>Bobot</th><th>Status</th><?php if($can_manage): ?><th>Aksi</th><?php endif; ?></tr><?php foreach($rows as $r): ?><tr><td><?= e($r['aspect']) ?></td><td><?= e($r['indicator']) ?></td><td><?= e($r['weight']) ?></td><td><span class="badge <?= $r['active']?'green':'red' ?>"><?= $r['active']?'Aktif':'Nonaktif' ?></span></td><?php if($can_manage): ?><td><a class="btn small secondary" href="?edit=<?= $r['id'] ?>">Edit</a> <a class="btn small danger" data-confirm="Hapus?" href="?delete=<?= $r['id'] ?>">Hapus</a></td><?php endif; ?></tr><?php endforeach; ?></table></div><?php render_footer(); ?>
