<?php
require_once __DIR__.'/_init.php';
require_role(['admin','kepala_sekolah']);
verify_csrf();
$identity = school_identity();

if($_SERVER['REQUEST_METHOD']==='POST'){
    $logo = safe_upload_image('logo', 'logo_sekolah', $identity['logo_path'] ?? '');
    $principalSignature = safe_upload_image('principal_signature', 'ttd_kepala_sekolah', $identity['principal_signature_path'] ?? '');
    $supervisorSignature = safe_upload_image('supervisor_signature', 'ttd_supervisor', $identity['supervisor_signature_path'] ?? '');
    $headerColor = $_POST['header_color'] ?? '#2563eb';
    $accentColor = $_POST['accent_color'] ?? '#7c3aed';
    $appName = $_POST['app_name'] ?? 'Supervisi Akademik';
    app_query("UPDATE school_identity SET school_name=?, npsn=?, address=?, phone=?, email=?, website=?, logo_path=?, principal_name=?, principal_nip=?, principal_signature_path=?, supervisor_name=?, supervisor_nip=?, supervisor_signature_path=?, city=?, header_color=?, accent_color=?, app_name=? WHERE id=1", [
        $_POST['school_name'], $_POST['npsn'], $_POST['address'], $_POST['phone'], $_POST['email'], $_POST['website'], $logo,
        $_POST['principal_name'], $_POST['principal_nip'], $principalSignature,
        $_POST['supervisor_name'], $_POST['supervisor_nip'], $supervisorSignature,
        $_POST['city'], $headerColor, $accentColor, $appName
    ]);
    flash('success','Identitas sekolah berhasil disimpan dan disinkronkan ke aplikasi/laporan.');
    redirect('school_identity.php');
}

$identity = school_identity();
render_header('Identitas Sekolah');
?>
<div class="grid two">
  <div class="card">
    <h2>Pengaturan Identitas Sekolah</h2>
    <p class="muted">Data ini otomatis dipakai untuk logo aplikasi, kop surat laporan, dan tanda tangan laporan cetak.</p>
    <form method="post" enctype="multipart/form-data" class="form two">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <div><label>Nama Sekolah</label><input name="school_name" required value="<?= e($identity['school_name']) ?>"></div>
      <div><label>NPSN</label><input name="npsn" value="<?= e($identity['npsn']) ?>"></div>
      <div style="grid-column:1/-1"><label>Alamat</label><textarea name="address"><?= e($identity['address']) ?></textarea></div>
      <div><label>Telepon</label><input name="phone" value="<?= e($identity['phone']) ?>"></div>
      <div><label>Email</label><input name="email" value="<?= e($identity['email']) ?>"></div>
      <div><label>Website</label><input name="website" value="<?= e($identity['website']) ?>"></div>
      <div><label>Kota/Kabupaten untuk Surat</label><input name="city" value="<?= e($identity['city']) ?>" placeholder="Contoh: Jakarta"></div>
      <div style="grid-column:1/-1"><hr style="border:none;border-top:2px solid var(--border);margin:12px 0"><h3 style="margin:8px 0;font-size:16px">🎨 Pengaturan Tampilan Aplikasi</h3><p class="muted" style="margin:0 0 8px">Kustomisasi warna dan nama yang tampil di seluruh aplikasi</p></div>
      <div><label>Nama Aplikasi</label><input name="app_name" value="<?= e($identity['app_name'] ?? 'Supervisi Akademik') ?>" placeholder="Supervisi Akademik"><small class="muted">Tampil di sidebar, judul browser, dan halaman login</small></div>
      <div><label>Warna Header / Primary</label><div style="display:flex;gap:10px;align-items:center"><input type="color" name="header_color" value="<?= e($identity['header_color'] ?? '#2563eb') ?>" style="width:50px;height:40px;padding:2px;cursor:pointer;border-radius:8px;border:1px solid var(--border)"><code style="font-size:13px;color:var(--text2)"><?= e($identity['header_color'] ?? '#2563eb') ?></code></div><small class="muted">Warna tombol, link, dan aksen utama</small></div>
      <div><label>Warna Aksen / Gradient</label><div style="display:flex;gap:10px;align-items:center"><input type="color" name="accent_color" value="<?= e($identity['accent_color'] ?? '#7c3aed') ?>" style="width:50px;height:40px;padding:2px;cursor:pointer;border-radius:8px;border:1px solid var(--border)"><code style="font-size:13px;color:var(--text2)"><?= e($identity['accent_color'] ?? '#7c3aed') ?></code></div><small class="muted">Warna gradient di logo sidebar & avatar user</small></div>
      <div style="grid-column:1/-1;padding:14px;background:var(--surface2);border-radius:var(--radius);border:1px solid var(--border)">
        <p style="margin:0 0 10px;font-weight:600;font-size:14px">Preview Warna Saat Ini:</p>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
          <div style="text-align:center"><span style="display:block;width:60px;height:30px;border-radius:6px;background:<?= e($identity['header_color'] ?? '#2563eb') ?>"></span><small class="muted">Header</small></div>
          <div style="text-align:center"><span style="display:block;width:60px;height:30px;border-radius:6px;background:<?= e($identity['accent_color'] ?? '#7c3aed') ?>"></span><small class="muted">Aksen</small></div>
          <div style="text-align:center"><span style="display:block;width:90px;height:30px;border-radius:6px;background:linear-gradient(135deg,<?= e($identity['header_color'] ?? '#2563eb') ?>,<?= e($identity['accent_color'] ?? '#7c3aed') ?>)"></span><small class="muted">Gradient</small></div>
        </div>
      </div>
      <div style="grid-column:1/-1"><hr style="border:none;border-top:2px solid var(--border);margin:12px 0"></div>
      <div><label>Logo Sekolah / Aplikasi</label><?php if($identity['logo_path']): ?><span class="school-logo-preview-box"><img class="school-logo-current" src="<?= e(public_file_url($identity['logo_path'])) ?>" alt="Logo saat ini" width="84" height="84" style="display:block;width:84px!important;height:84px!important;max-width:84px!important;max-height:84px!important;object-fit:contain!important;"></span><?php endif; ?><input type="file" name="logo" accept="image/*"><small class="muted">Logo otomatis diperkecil tampilannya di aplikasi dan kop surat.</small></div>
      <div><label>Nama Kepala Sekolah</label><input name="principal_name" value="<?= e($identity['principal_name']) ?>"></div>
      <div><label>NIP Kepala Sekolah</label><input name="principal_nip" value="<?= e($identity['principal_nip']) ?>"></div>
      <div><label>Tanda Tangan Kepala Sekolah</label><input type="file" name="principal_signature" accept="image/*"></div>
      <div><label>Nama Supervisor Default</label><input name="supervisor_name" value="<?= e($identity['supervisor_name']) ?>"></div>
      <div><label>NIP Supervisor Default</label><input name="supervisor_nip" value="<?= e($identity['supervisor_nip']) ?>"></div>
      <div><label>Tanda Tangan Supervisor</label><input type="file" name="supervisor_signature" accept="image/*"></div>
      <div style="grid-column:1/-1"><button>Simpan Identitas</button></div>
    </form>
  </div>
  <div class="card">
    <h2>Preview Kop & Tanda Tangan</h2>
    <div class="letter-preview">
      <?php if($identity['logo_path']): ?><span class="kop-logo-box"><img class="kop-logo" src="<?= e(public_file_url($identity['logo_path'])) ?>" alt="Logo sekolah" width="72" height="72" style="display:block;width:72px!important;height:72px!important;max-width:72px!important;max-height:72px!important;object-fit:contain!important;"></span><?php endif; ?>
      <div class="kop-text">
        <h2><?= e($identity['school_name']) ?></h2>
        <p><?= e($identity['address']) ?></p>
        <p><?php if($identity['phone']): ?>Telp. <?= e($identity['phone']) ?><?php endif; ?> <?php if($identity['email']): ?>Email: <?= e($identity['email']) ?><?php endif; ?></p>
        <p><?php if($identity['website']): ?>Website: <?= e($identity['website']) ?><?php endif; ?> <?php if($identity['npsn']): ?>NPSN: <?= e($identity['npsn']) ?><?php endif; ?></p>
      </div>
    </div>
    <hr>
    <div class="signature-grid">
      <div>
        <b>Supervisor</b><br>
        <?php if($identity['supervisor_signature_path']): ?><img class="sign-img" src="<?= e(public_file_url($identity['supervisor_signature_path'])) ?>" alt="TTD supervisor"><?php else: ?><div class="sign-box">Belum ada TTD</div><?php endif; ?>
        <p><b><?= e($identity['supervisor_name'] ?: 'Nama Supervisor') ?></b><br>NIP. <?= e($identity['supervisor_nip'] ?: '-') ?></p>
      </div>
      <div>
        <b>Kepala Sekolah</b><br>
        <?php if($identity['principal_signature_path']): ?><img class="sign-img" src="<?= e(public_file_url($identity['principal_signature_path'])) ?>" alt="TTD kepala sekolah"><?php else: ?><div class="sign-box">Belum ada TTD</div><?php endif; ?>
        <p><b><?= e($identity['principal_name'] ?: 'Nama Kepala Sekolah') ?></b><br>NIP. <?= e($identity['principal_nip'] ?: '-') ?></p>
      </div>
    </div>
  </div>
</div>
<?php render_footer(); ?>
