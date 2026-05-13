<?php
require_once __DIR__.'/_init.php';
if(is_logged_in()) redirect('index.php');
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $st=app_query("SELECT * FROM users WHERE username=? AND active=1", [$_POST['username'] ?? '']);
    $u=$st->fetch();
    if($u && password_verify($_POST['password'] ?? '', $u['password'])){
        $_SESSION['user']=['id'=>$u['id'],'name'=>$u['name'],'username'=>$u['username'],'role'=>$u['role'],'teacher_id'=>$u['teacher_id']];
        redirect('index.php');
    } else $error='Username atau password salah.';
}
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login</title><link rel="stylesheet" href="<?= url('assets/style.css') ?>"></head><body><div class="login-wrap"><div class="card login-card"><div class="hero"><h1>Supervisi Guru SMK</h1><p>Administrasi supervisi pembelajaran Kurikulum Merdeka</p></div><?php if($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?><form method="post" class="form"><label>Username</label><input name="username" autofocus required><label>Password</label><input name="password" type="password" required><button>Masuk</button></form></div></div></body></html>
