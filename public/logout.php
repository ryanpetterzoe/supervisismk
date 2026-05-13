<?php
require_once __DIR__.'/_init.php';
session_destroy();
header('Location: '.url('index.php'));
exit;
