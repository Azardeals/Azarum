<?php

require_once './application-top.php';
$file = $_GET['fname'];
$fullPath = SUPPORT_FILES_PATH . $file;
$mime_types = [
    "pdf" => "application/pdf",
    "txt" => "text/plain",
    "html" => "text/html",
    "htm" => "text/html",
    "exe" => "application/octet-stream",
    "zip" => "application/zip",
    "doc" => "application/msword",
    "xls" => "application/vnd.ms-excel",
    "ppt" => "application/vnd.ms-powerpoint",
    "gif" => "image/gif",
    "png" => "image/png",
    "jpeg" => "image/jpg",
    "jpg" => "image/jpg"
];
if (!$fd = fopen($fullPath, "r")) {
    die('File not found.');
} else {
    $fsize = filesize($fullPath);
    $path_parts = pathinfo($fullPath);
    $ext = strtolower($path_parts["extension"]);
    header("Content-type: $mime_types[$ext]");
    header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
    header("Content-length: $fsize");
    header("Cache-control: private"); //use this to open files directly
    while (!feof($fd)) {
        $buffer = fread($fd, 2048);
        echo $buffer;
    }
}
fclose($fd);
exit;
