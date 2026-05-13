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
<link rel="stylesheet" href="<?= url('assets/style.css') ?>">
<script>
(function(){
  var t;try{t=localStorage.getItem('smk_theme');}catch(e){}
  if(t!=='dark'&&t!=='light'){t=(window.matchMedia&&window.matchMedia('(prefers-color-scheme:dark)').matches)?'dark':'light';}
  document.documentElement.setAttribute('data-theme',t);
})();
</script>
</head>
<body>
<div class="login-wrap">
  <div class="login-card">
    <button class="theme-toggle" id="themeToggle" style="position:absolute;top:20px;right:20px;" title="Toggle tema">
      <span class="theme-icon">🌙</span>
    </button>
    <div class="login-logo">🏫</div>
    <h2>Supervisi Guru SMK</h2>
    <p class="login-sub">Kurikulum Merdeka — Masuk untuk melanjutkan</p>
    <?php if($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="form" style="margin-top:4px">
      <div>
        <label for="username">Username</label>
        <input id="username" name="username" autofocus required placeholder="Masukkan username">
      </div>
      <div>
        <label for="password">Password</label>
        <input id="password" name="password" type="password" required placeholder="Masukkan password">
      </div>
      <button class="btn large" style="width:100%;margin-top:4px">Masuk →</button>
    </form>
  </div>
</div>
<script src="<?= url('assets/app.js') ?>"></script>
</body>
</html>
