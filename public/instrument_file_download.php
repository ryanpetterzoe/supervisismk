<?php
require_once __DIR__.'/_init.php';
$id=(int)($_GET['id'] ?? 0);
$row=app_query('SELECT * FROM instrument_downloads WHERE id=? AND active=1',[$id])->fetch();
if(!$row && is_logged_in() && has_role(['admin','kepala_sekolah','supervisor'])){
    $row=app_query('SELECT * FROM instrument_downloads WHERE id=?',[$id])->fetch();
}
if(!$row){ http_response_code(404); die('File instrumen tidak ditemukan atau belum aktif.'); }
$path=public_file_path($row['file_path']);
if(!$path || !file_exists($path)){ http_response_code(404); die('File tidak ada di server.'); }
$downloadName=$row['original_name'] ?: basename($path);
$ext=strtolower(pathinfo($downloadName, PATHINFO_EXTENSION));
$mime='application/octet-stream';
$map=['pdf'=>'application/pdf','doc'=>'application/msword','docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document','xls'=>'application/vnd.ms-excel','xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','ppt'=>'application/vnd.ms-powerpoint','pptx'=>'application/vnd.openxmlformats-officedocument.presentationml.presentation','zip'=>'application/zip','rar'=>'application/vnd.rar','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png'];
if(isset($map[$ext])) $mime=$map[$ext];
header('Content-Type: '.$mime);
header('Content-Length: '.filesize($path));
header('Content-Disposition: attachment; filename="'.str_replace('"','',basename($downloadName)).'"');
readfile($path);
exit;
