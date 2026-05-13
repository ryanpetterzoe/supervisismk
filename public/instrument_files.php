<?php
require_once __DIR__.'/_init.php';
require_role(['admin','kepala_sekolah','supervisor']);
verify_csrf();

$categories = ['Mapel','BK','Pra Observasi','Observasi','Pasca Observasi','Umum'];

if(isset($_GET['delete'])){
    $id=(int)$_GET['delete'];
    $row=app_query('SELECT * FROM instrument_downloads WHERE id=?',[$id])->fetch();
    if($row){
        if(!empty($row['file_path']) && file_exists(public_file_path($row['file_path']))) @unlink(public_file_path($row['file_path']));
        app_query('DELETE FROM instrument_downloads WHERE id=?',[$id]);
        flash('success','Card download instrumen dihapus.');
    }
    redirect('instrument_files.php');
}

$edit=null;
if(isset($_GET['edit'])){
    $edit=app_query('SELECT * FROM instrument_downloads WHERE id=?',[(int)$_GET['edit']])->fetch();
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    $id=(int)($_POST['id'] ?? 0);
    $old=$id ? app_query('SELECT * FROM instrument_downloads WHERE id=?',[$id])->fetch() : null;
    $filePath=safe_upload_instrument_file('instrument_file', $old['file_path'] ?? '');
    if(!$filePath){
        flash('error','File instrumen wajib diupload.');
        redirect('instrument_files.php'.($id?'?edit='.$id:''));
    }
    $original = !empty($_FILES['instrument_file']['name']) ? $_FILES['instrument_file']['name'] : ($old['original_name'] ?? '');
    $size = !empty($_FILES['instrument_file']['name']) ? (int)($_FILES['instrument_file']['size'] ?? 0) : (int)($old['file_size'] ?? 0);
    $ext = strtolower(pathinfo($original ?: $filePath, PATHINFO_EXTENSION));
    $data=[
        trim($_POST['title'] ?? ''),
        trim($_POST['description'] ?? ''),
        trim($_POST['category'] ?? ''),
        $filePath,
        $original,
        $ext,
        $size,
        trim($_POST['button_label'] ?? 'Download'),
        isset($_POST['active']) ? 1 : 0,
        (int)($_POST['sort_order'] ?? 0),
        current_user()['id'] ?? null
    ];
    if($data[0]===''){
        flash('error','Judul card wajib diisi.');
        redirect('instrument_files.php'.($id?'?edit='.$id:''));
    }
    if($id){
        app_query('UPDATE instrument_downloads SET title=?,description=?,category=?,file_path=?,original_name=?,file_ext=?,file_size=?,button_label=?,active=?,sort_order=?,uploaded_by=? WHERE id=?',[...$data,$id]);
        flash('success','Card download instrumen diperbarui.');
    } else {
        app_query('INSERT INTO instrument_downloads(title,description,category,file_path,original_name,file_ext,file_size,button_label,active,sort_order,uploaded_by) VALUES(?,?,?,?,?,?,?,?,?,?,?)',$data);
        flash('success','Card download instrumen ditambahkan ke halaman depan.');
    }
    redirect('instrument_files.php');
}

$rows=instrument_downloads(false);
render_header('Upload Instrumen Supervisi');
?>
<div class="card">
  <div class="toolbar"><div><h2><?= $edit ? 'Edit Card Download' : 'Tambah Card Download Halaman Depan' ?></h2><p class="muted">Upload file instrumen asli seperti PDF/Word/Excel/PowerPoint. File ini akan muncul sebagai card download di halaman depan, bukan lagi file TXT otomatis.</p></div><a class="btn secondary" href="<?= url('index.php') ?>" target="_blank">Lihat Halaman Depan</a></div>
  <form method="post" enctype="multipart/form-data" class="form">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
    <div class="form two">
      <div><label>Judul Card</label><input name="title" required placeholder="Contoh: Instrumen Supervisi Pra Mapel" value="<?= e($edit['title'] ?? '') ?>"></div>
      <div><label>Kategori</label><select name="category"><?php foreach($categories as $cat): ?><option value="<?= e($cat) ?>" <?= (($edit['category'] ?? '')===$cat?'selected':'') ?>><?= e($cat) ?></option><?php endforeach; ?></select></div>
    </div>
    <label>Deskripsi Singkat</label><textarea name="description" rows="3" placeholder="Contoh: Format instrumen pra observasi guru mata pelajaran."><?= e($edit['description'] ?? '') ?></textarea>
    <div class="form two">
      <div><label>Label Tombol</label><input name="button_label" value="<?= e($edit['button_label'] ?? 'Download') ?>"></div>
      <div><label>Urutan Tampil</label><input type="number" name="sort_order" value="<?= e($edit['sort_order'] ?? 0) ?>"></div>
    </div>
    <label>File Instrumen <?= $edit ? '<small class="muted">kosongkan jika tidak diganti</small>' : '' ?></label>
    <input type="file" name="instrument_file" <?= $edit ? '' : 'required' ?> accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.jpg,.jpeg,.png">
    <?php if($edit && !empty($edit['original_name'])): ?><p class="muted">File saat ini: <?= e($edit['original_name']) ?> (<?= e(format_bytes_id($edit['file_size'] ?? 0)) ?>)</p><?php endif; ?>
    <label><input type="checkbox" name="active" <?= (($edit['active'] ?? 1)?'checked':'') ?>> Aktif dan tampil di halaman depan</label>
    <div><button><?= $edit ? 'Update Card' : 'Upload & Tampilkan' ?></button><?php if($edit): ?> <a class="btn secondary" href="<?= url('instrument_files.php') ?>">Batal Edit</a><?php endif; ?></div>
  </form>
</div>

<div class="card" style="margin-top:16px">
  <h2>Card Download Aktif/Nonaktif</h2>
  <table><tr><th>Urutan</th><th>Judul</th><th>Kategori</th><th>File</th><th>Status</th><th>Aksi</th></tr>
  <?php foreach($rows as $r): ?>
    <tr>
      <td><?= e($r['sort_order']) ?></td>
      <td><b><?= e($r['title']) ?></b><br><small class="muted"><?= e($r['description']) ?></small></td>
      <td><?= e($r['category']) ?></td>
      <td><?= e($r['original_name']) ?><br><small class="muted"><?= e(strtoupper($r['file_ext'])) ?> · <?= e(format_bytes_id($r['file_size'] ?? 0)) ?></small></td>
      <td><span class="badge <?= $r['active']?'green':'red' ?>"><?= $r['active']?'Tampil':'Nonaktif' ?></span></td>
      <td><a class="btn small secondary" href="?edit=<?= $r['id'] ?>">Edit</a> <a class="btn small" href="<?= url('instrument_file_download.php?id='.$r['id']) ?>" target="_blank">Download</a> <a class="btn small danger" data-confirm="Hapus card dan file?" href="?delete=<?= $r['id'] ?>">Hapus</a></td>
    </tr>
  <?php endforeach; ?>
  <?php if(!$rows): ?><tr><td colspan="6" class="muted">Belum ada file instrumen yang diupload.</td></tr><?php endif; ?>
  </table>
</div>
<?php render_footer(); ?>
