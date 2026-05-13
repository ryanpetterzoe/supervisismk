# Supervisi Guru SMK - Kurikulum Merdeka

Aplikasi PHP/MySQL siap XAMPP untuk administrasi dan supervisi guru SMK berbasis Kurikulum Merdeka.

## Fitur Utama
- Installer database berbasis web: membuat database, tabel, data awal, dan akun admin.
- Login aman dengan password hashing PHP.
- Role pengguna: Admin, Kepala Sekolah, Wakil Kurikulum/Supervisor, Guru.
- Dashboard ringkas: statistik guru, jadwal, observasi, tindak lanjut, dan dokumen.
- Master data: guru, mapel, kelas, pengguna, instrumen/rubrik supervisi.
- Jadwal supervisi kelas.
- Observasi supervisi berbasis skor instrumen.
- Catatan praktik baik, rekomendasi, dan tindak lanjut.
- Upload dokumen pendukung.
- Laporan supervisi, cetak halaman, dan export CSV.
- UI modern responsif tanpa CDN, cocok offline di XAMPP.

## Cara Instal di XAMPP
1. Copy folder `supervisi_guru_smk` ke `C:/xampp/htdocs/`.
2. Jalankan Apache dan MySQL dari XAMPP Control Panel.
3. Buka browser: `http://localhost/supervisi_guru_smk/install/`.
4. Isi host database, nama database, user MySQL, password MySQL, username admin, dan password admin.
5. Setelah sukses, buka: `http://localhost/supervisi_guru_smk/public/`.
6. Login dengan akun admin yang dibuat saat instalasi.

## Catatan Keamanan
- Setelah instalasi berhasil, hapus folder `install` atau ganti namanya.
- Ganti password admin secara berkala.
- Untuk hosting publik, pastikan HTTPS aktif dan permission folder `storage/uploads` aman.

## Struktur
- `public/` entry point aplikasi.
- `app/` kode inti aplikasi.
- `install/` installer database.
- `storage/uploads/` dokumen pendukung.


## Update v7 - Identitas Sekolah
- Menu baru: Identitas Sekolah, tampil untuk role Admin dan Kepala Sekolah.
- Data identitas tersinkron ke logo sidebar/aplikasi.
- Kop surat laporan supervisi memakai nama sekolah, NPSN, alamat, telepon, email, website, dan logo.
- Tanda tangan kepala sekolah dan supervisor tampil otomatis di laporan cetak.
- Tabel `school_identity` dibuat otomatis saat aplikasi dibuka, jadi database lama tidak perlu dihapus.

## Catatan v8
- Logo sekolah kini otomatis dibatasi ukurannya di sidebar, halaman identitas, kop laporan, dan mode cetak.
- Saat upload logo baru, gambar besar akan diperkecil otomatis jika ekstensi GD aktif di PHP/XAMPP. Jika GD tidak aktif, tampilan tetap aman karena CSS membatasi ukuran logo.


## Catatan v9
Logo sudah dipaksa auto-resize dengan wrapper dan inline style di sidebar, preview identitas, kop surat, dan mode print. Jika masih terlihat besar, tekan Ctrl+F5 atau hapus cache browser.


## Update v10 - Fitur dari Panduan PDF
- Landing page publik sebelum login dengan logo sekolah, tombol login, statistik progress supervisi, dan download instrumen.
- Dashboard setelah login memiliki kartu progress dan 6 tombol akses form: Pra Mapel, Observasi Mapel, Pasca Mapel, Pra BK, Observasi BK, Pasca BK.
- Menu baru `Input Bertahap` menyimpan form supervisi secara urut. Tahap Observasi tetap dikunci agar guru tidak menilai supervisinya sendiri.
- Menu laporan memiliki tombol `Cetak Laporan Sekolah/PDF` dengan parameter jenis guru, tempat cetak, tanggal cetak, nama kepala sekolah, dan NIP.
- Migrasi otomatis membuat tabel `academic_supervision_forms` saat aplikasi dibuka.


## Fitur v13
- Menu Upload Instrumen untuk admin/kepala sekolah/supervisor.
- Card download instrumen di halaman depan memakai file asli PDF/Word/Excel/PowerPoint, bukan TXT otomatis.
