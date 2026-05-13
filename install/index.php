<?php
$error=''; $success='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $host=trim($_POST['db_host'] ?? 'localhost'); $db=trim($_POST['db_name'] ?? 'supervisi_guru_smk'); $user=trim($_POST['db_user'] ?? 'root'); $pass=$_POST['db_pass'] ?? '';
    $base=trim($_POST['base_url'] ?? '/supervisi_guru_smk/public');
    $adminName=trim($_POST['admin_name'] ?? 'Administrator'); $adminUser=trim($_POST['admin_user'] ?? 'admin'); $adminPass=$_POST['admin_pass'] ?? '';
    if(strlen($adminPass)<6){ $error='Password admin minimal 6 karakter.'; }
    else{
        try{
            $pdo=new PDO('mysql:host='.$host.';charset=utf8mb4',$user,$pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
            $pdo->exec('CREATE DATABASE IF NOT EXISTS `'.str_replace('`','',$db).'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            $pdo->exec('USE `'.str_replace('`','',$db).'`');
            require __DIR__.'/schema.php'; install_schema($pdo,$adminName,$adminUser,$adminPass);
            $config="<?php\nreturn [\n    'db_host' => '".addslashes($host)."',\n    'db_name' => '".addslashes($db)."',\n    'db_user' => '".addslashes($user)."',\n    'db_pass' => '".addslashes($pass)."',\n    'base_url' => '".addslashes($base)."',\n    'app_name' => 'Supervisi Guru SMK'\n];\n";
            file_put_contents(dirname(__DIR__).'/app/config.php',$config);
            $success='Instalasi berhasil. Akun admin sudah dibuat.';
        }catch(Throwable $e){ $error='Gagal instalasi: '.$e->getMessage(); }
    }
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Installer Supervisi Guru SMK</title><link rel="stylesheet" href="../public/assets/style.css"></head><body><div class="login-wrap"><div class="card login-card"><div class="hero"><h1>Installer Database</h1><p>Supervisi Guru SMK Kurikulum Merdeka - XAMPP Ready</p></div><?php if($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?><?php if($success): ?><div class="alert success"><?= htmlspecialchars($success) ?></div><a class="btn" href="../public/">Buka Aplikasi</a><?php else: ?><form method="post" class="form"><div class="form two"><div><label>Host DB</label><input name="db_host" value="localhost"></div><div><label>Nama DB</label><input name="db_name" value="supervisi_guru_smk"></div><div><label>User DB</label><input name="db_user" value="root"></div><div><label>Password DB</label><input name="db_pass" type="password" placeholder="kosongkan untuk XAMPP default"></div></div><label>Base URL</label><input name="base_url" value="/supervisi_guru_smk/public"><div class="form two"><div><label>Nama Admin</label><input name="admin_name" value="Administrator"></div><div><label>Username Admin</label><input name="admin_user" value="admin"></div></div><label>Password Admin</label><input name="admin_pass" type="password" required minlength="6"><button>Install Sekarang</button></form><?php endif; ?></div></div></body></html>
