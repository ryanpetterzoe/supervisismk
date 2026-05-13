<?php
require_once __DIR__.'/_init.php';
$school=school_identity();
header('Content-Type: text/plain; charset=utf-8');
$name='instrumen_supervisi_'.date('Ymd').'.txt';
header('Content-Disposition: attachment; filename="'.$name.'"');
echo strtoupper($school['school_name'] ?: 'SEKOLAH')."\n";
echo "INSTRUMEN SUPERVISI AKADEMIK KURIKULUM MERDEKA\n";
echo str_repeat('=',70)."\n\n";
if(isset($_GET['all'])) $rows=app_query('SELECT * FROM instruments WHERE active=1 ORDER BY id')->fetchAll();
else $rows=app_query('SELECT * FROM instruments WHERE id=? AND active=1',[(int)($_GET['id']??0)])->fetchAll();
foreach($rows as $n=>$r){
    echo ($n+1).". ".$r['aspect']."\n";
    echo "Indikator : ".$r['indicator']."\n";
    echo "Bobot     : ".$r['weight']."\n";
    echo "Skor      : ______\n";
    echo "Catatan   : ________________________________________________\n\n";
}
