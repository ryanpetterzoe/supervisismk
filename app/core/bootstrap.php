<?php
session_start();
define('ROOT_PATH', dirname(__DIR__, 2));
$configFile = ROOT_PATH . '/app/config.php';
if (!file_exists($configFile)) {
    header('Location: ../install/');
    exit;
}
$config = require $configFile;

date_default_timezone_set('Asia/Jakarta');

try {
    $pdo = new PDO(
        'mysql:host=' . $config['db_host'] . ';dbname=' . $config['db_name'] . ';charset=utf8mb4',
        $config['db_user'],
        $config['db_pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (Throwable $e) {
    die('Koneksi database gagal: ' . htmlspecialchars($e->getMessage()));
}

function db(): PDO { global $pdo; return $pdo; }
function cfg($key, $default=null) { global $config; return $config[$key] ?? $default; }
function app_base_url(){
    // Auto-detect base URL from the current /public folder so CSS/JS tetap jalan
    // walaupun folder aplikasi diganti menjadi /supervisi, /supervisi2, dll.
    $script = str_replace('\\','/', $_SERVER['SCRIPT_NAME'] ?? '');
    $dir = rtrim(str_replace('\\','/', dirname($script)), '/');
    if ($dir === '' || $dir === '.') $dir = '';
    return $dir;
}
function url($path='') { return app_base_url() . '/' . ltrim($path, '/'); }
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function rupiah_date($date) { return $date ? date('d/m/Y', strtotime($date)) : '-'; }
function current_user() { return $_SESSION['user'] ?? null; }
function is_logged_in() { return isset($_SESSION['user']); }
function has_role($roles) { $u=current_user(); return $u && in_array($u['role'], (array)$roles, true); }
function require_login(){ if(!is_logged_in()){ header('Location: '.url('login.php')); exit; } }
function require_role($roles){ require_login(); if(!has_role($roles)){ http_response_code(403); die('Akses ditolak.'); } }
function csrf_token(){ if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function verify_csrf(){ if($_SERVER['REQUEST_METHOD']==='POST' && (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf']))){ die('CSRF token tidak valid.'); } }
function flash($type=null,$msg=null){ if($type && $msg){ $_SESSION['flash']=['type'=>$type,'msg'=>$msg]; return; } $f=$_SESSION['flash']??null; unset($_SESSION['flash']); return $f; }
function redirect($path){ header('Location: '.url($path)); exit; }
function score_label($s){ if($s>=90)return 'Sangat Baik'; if($s>=80)return 'Baik'; if($s>=70)return 'Cukup'; return 'Perlu Pendampingan'; }

function ensure_app_schema(){
    static $done=false; if($done) return; $done=true;
    try {
        db()->exec("CREATE TABLE IF NOT EXISTS school_identity (
            id INT PRIMARY KEY DEFAULT 1,
            school_name VARCHAR(180) NOT NULL DEFAULT 'SMK Negeri Contoh',
            npsn VARCHAR(40) NULL,
            address TEXT NULL,
            phone VARCHAR(60) NULL,
            email VARCHAR(120) NULL,
            website VARCHAR(120) NULL,
            logo_path VARCHAR(255) NULL,
            principal_name VARCHAR(140) NULL,
            principal_nip VARCHAR(60) NULL,
            principal_signature_path VARCHAR(255) NULL,
            supervisor_name VARCHAR(140) NULL,
            supervisor_nip VARCHAR(60) NULL,
            supervisor_signature_path VARCHAR(255) NULL,
            city VARCHAR(80) NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $exists=(int)db()->query("SELECT COUNT(*) FROM school_identity WHERE id=1")->fetchColumn();
        if(!$exists){
            $st=db()->prepare("INSERT INTO school_identity(id,school_name,npsn,address,phone,email,website,city) VALUES(1,?,?,?,?,?,?,?)");
            $st->execute(['SMK Negeri Contoh','','Alamat sekolah belum diatur','','','','']);
        }

        db()->exec("CREATE TABLE IF NOT EXISTS academic_supervision_forms (
            id INT AUTO_INCREMENT PRIMARY KEY,
            stage ENUM('pra_mapel','observasi_mapel','pasca_mapel','pra_bk','observasi_bk','pasca_bk') NOT NULL,
            teacher_type ENUM('Mapel','BK') NOT NULL DEFAULT 'Mapel',
            teacher_id INT NOT NULL,
            subject_id INT NULL,
            class_id INT NULL,
            supervisor_user_id INT NULL,
            supervision_date DATE NULL,
            focus TEXT NULL,
            strengths TEXT NULL,
            notes TEXT NULL,
            recommendations TEXT NULL,
            score DECIMAL(5,2) NULL,
            created_by INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        try { db()->exec("ALTER TABLE documents ADD COLUMN instrument_id INT NULL AFTER teacher_id"); } catch(Throwable $e) {}
        // v14: color & branding customization
        try { db()->exec("ALTER TABLE school_identity ADD COLUMN header_color VARCHAR(20) NULL DEFAULT '#2563eb'"); } catch(Throwable $e) {}
        try { db()->exec("ALTER TABLE school_identity ADD COLUMN accent_color VARCHAR(20) NULL DEFAULT '#7c3aed'"); } catch(Throwable $e) {}
        try { db()->exec("ALTER TABLE school_identity ADD COLUMN app_name VARCHAR(120) NULL DEFAULT 'Supervisi Akademik'"); } catch(Throwable $e) {}
        // v14: landing page customization
        try { db()->exec("ALTER TABLE school_identity ADD COLUMN banner_path VARCHAR(255) NULL"); } catch(Throwable $e) {}
        try { db()->exec("ALTER TABLE school_identity ADD COLUMN landing_title VARCHAR(255) NULL DEFAULT 'Monitoring supervisi, instrumen, dan laporan dalam satu dashboard.'"); } catch(Throwable $e) {}
        try { db()->exec("ALTER TABLE school_identity ADD COLUMN landing_subtitle VARCHAR(255) NULL DEFAULT 'Aplikasi Supervisi Akademik Guru SMK'"); } catch(Throwable $e) {}
        try { db()->exec("ALTER TABLE school_identity ADD COLUMN landing_cta_text VARCHAR(100) NULL DEFAULT 'Masuk Sistem'"); } catch(Throwable $e) {}
        try { db()->exec("ALTER TABLE school_identity ADD COLUMN landing_footer_text VARCHAR(255) NULL DEFAULT ''"); } catch(Throwable $e) {}

        db()->exec("CREATE TABLE IF NOT EXISTS instrument_downloads (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(180) NOT NULL,
            description TEXT NULL,
            category VARCHAR(80) NULL,
            file_path VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NULL,
            file_ext VARCHAR(20) NULL,
            file_size INT NULL,
            button_label VARCHAR(80) NULL,
            active TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            uploaded_by INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    } catch(Throwable $e) { /* jangan ganggu aplikasi utama jika migrasi identitas gagal */ }
}
function school_identity(){
    ensure_app_schema();
    $default=[
        'id'=>1,'school_name'=>'SMK Negeri Contoh','npsn'=>'','address'=>'Alamat sekolah belum diatur','phone'=>'','email'=>'','website'=>'','logo_path'=>'',
        'principal_name'=>'','principal_nip'=>'','principal_signature_path'=>'','supervisor_name'=>'','supervisor_nip'=>'','supervisor_signature_path'=>'','city'=>''
    ];
    try { $row=app_query('SELECT * FROM school_identity WHERE id=1')->fetch(); return array_merge($default, $row ?: []); }
    catch(Throwable $e){ return $default; }
}
function public_file_url($path){ return $path ? url('../'.ltrim($path,'/')) : ''; }
function public_file_path($path){ return $path ? ROOT_PATH.'/'.ltrim($path,'/') : ''; }
function resize_uploaded_image_file($source, $dest, $ext, $maxW=900, $maxH=900){
    $info=@getimagesize($source);
    if(!$info) return move_uploaded_file($source, $dest);
    [$w,$h]=$info;
    if($w <= 0 || $h <= 0) return move_uploaded_file($source, $dest);
    if(!function_exists('imagecreatetruecolor') || $ext === 'gif') return move_uploaded_file($source, $dest);

    $ratio=min($maxW/$w, $maxH/$h, 1);
    $newW=max(1,(int)round($w*$ratio));
    $newH=max(1,(int)round($h*$ratio));

    switch($ext){
        case 'jpg':
        case 'jpeg': $src=@imagecreatefromjpeg($source); break;
        case 'png': $src=@imagecreatefrompng($source); break;
        case 'webp': $src=function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($source) : false; break;
        default: $src=false;
    }
    if(!$src) return move_uploaded_file($source, $dest);

    $dst=imagecreatetruecolor($newW,$newH);
    if(in_array($ext,['png','webp'],true)){
        imagealphablending($dst,false);
        imagesavealpha($dst,true);
        $transparent=imagecolorallocatealpha($dst,255,255,255,127);
        imagefilledrectangle($dst,0,0,$newW,$newH,$transparent);
    }
    imagecopyresampled($dst,$src,0,0,0,0,$newW,$newH,$w,$h);

    $ok=false;
    if($ext==='jpg' || $ext==='jpeg') $ok=imagejpeg($dst,$dest,88);
    elseif($ext==='png') $ok=imagepng($dst,$dest,6);
    elseif($ext==='webp' && function_exists('imagewebp')) $ok=imagewebp($dst,$dest,88);

    imagedestroy($src); imagedestroy($dst);
    return $ok;
}

function safe_upload_image($field, $prefix, $oldPath=''){
    if(empty($_FILES[$field]['name'])) return $oldPath;
    if(($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return $oldPath;
    $ext=strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    $allowed=['jpg','jpeg','png','gif','webp'];
    if(!in_array($ext,$allowed,true)) die('Format gambar tidak diizinkan. Gunakan jpg, png, gif, atau webp.');
    if(($_FILES[$field]['size'] ?? 0) > 5*1024*1024) die('Ukuran gambar maksimal 5MB.');
    $dir=ROOT_PATH.'/storage/uploads/school/'; if(!is_dir($dir)) mkdir($dir,0775,true);
    $name=$prefix.'_'.date('YmdHis').'_'.bin2hex(random_bytes(4)).'.'.$ext;
    $rel='storage/uploads/school/'.$name;
    $maxW = ($field === 'logo') ? 600 : 900;
    $maxH = ($field === 'logo') ? 600 : 450;
    if(!resize_uploaded_image_file($_FILES[$field]['tmp_name'],$dir.$name,$ext,$maxW,$maxH)) die('Upload gambar gagal.');
    if($oldPath && file_exists(public_file_path($oldPath))) @unlink(public_file_path($oldPath));
    return $rel;
}


function format_bytes_id($bytes){
    $bytes=(int)$bytes;
    if($bytes >= 1024*1024) return round($bytes/(1024*1024),1).' MB';
    if($bytes >= 1024) return round($bytes/1024,1).' KB';
    return $bytes.' B';
}
function instrument_downloads($activeOnly=true){
    ensure_app_schema();
    $where=$activeOnly ? 'WHERE active=1' : '';
    return app_query("SELECT * FROM instrument_downloads $where ORDER BY sort_order ASC, id DESC")->fetchAll();
}
function safe_upload_instrument_file($field, $oldPath=''){
    if(empty($_FILES[$field]['name'])) return $oldPath;
    if(($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return $oldPath;
    $ext=strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    $allowed=['pdf','doc','docx','xls','xlsx','ppt','pptx','zip','rar','jpg','jpeg','png'];
    if(!in_array($ext,$allowed,true)) die('Format file tidak diizinkan. Gunakan PDF, Word, Excel, PowerPoint, ZIP/RAR, JPG, atau PNG.');
    if(($_FILES[$field]['size'] ?? 0) > 20*1024*1024) die('Ukuran file maksimal 20MB.');
    $dir=ROOT_PATH.'/storage/uploads/instruments/'; if(!is_dir($dir)) mkdir($dir,0775,true);
    $name='instrumen_'.date('YmdHis').'_'.bin2hex(random_bytes(4)).'.'.$ext;
    $rel='storage/uploads/instruments/'.$name;
    if(!move_uploaded_file($_FILES[$field]['tmp_name'],$dir.$name)) die('Upload file instrumen gagal.');
    if($oldPath && file_exists(public_file_path($oldPath))) @unlink(public_file_path($oldPath));
    return $rel;
}

function app_query($sql,$params=[]){ $st=db()->prepare($sql); $st->execute($params); return $st; }
ensure_app_schema();
