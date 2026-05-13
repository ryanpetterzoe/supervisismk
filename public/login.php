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
?><!doctype html>
<html lang="id" data-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login – Supervisi Akademik</title>
<style>
:root{--bg:#f1f5f9;--surface:#fff;--surface2:#f8fafc;--text:#0f172a;--text2:#334155;--text3:#64748b;--border:#e2e8f0;--primary:#2563eb;--primary-light:#eff6ff}
[data-theme="dark"],html.dark{--bg:#0f172a;--surface:#1e293b;--surface2:#1a2540;--text:#f1f5f9;--text2:#cbd5e1;--text3:#94a3b8;--border:#2d3f55;--primary:#3b82f6;--primary-light:#1e3a5e}
body{background-color:var(--bg);color:var(--text);transition:background-color .2s,color .2s}
</style>
<script>
(function(){
  var k='smk_theme',h=document.documentElement,t;
  try{t=localStorage.getItem(k)}catch(e){}
  if(t!=='dark'&&t!=='light') t=(window.matchMedia&&window.matchMedia('(prefers-color-scheme:dark)').matches)?'dark':'light';
  h.setAttribute('data-theme',t);
  h.className=t;
})();
</script>
<link rel="stylesheet" href="<?= url('assets/style.css') ?>?v=14">
</head>
<body>
<div class="login-wrap">
  <div class="login-card">
    <button class="theme-toggle" id="themeToggle" type="button" title="Toggle Tema" style="position:absolute;top:16px;right:16px"><span class="theme-icon">🌙</span></button>
    <div class="login-logo">🏫</div>
    <h2>Supervisi Guru SMK</h2>
    <p class="login-sub">Kurikulum Merdeka — Masuk untuk melanjutkan</p>
    <?php if($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="form">
      <div><label>Username</label><input name="username" autofocus required placeholder="Masukkan username"></div>
      <div><label>Password</label><input name="password" type="password" required placeholder="Masukkan password"></div>
      <button class="btn large" style="width:100%;justify-content:center">Masuk</button>
    </form>
  </div>
</div>
<script src="<?= url('assets/app.js') ?>?v=14"></script>
</body>
</html>
