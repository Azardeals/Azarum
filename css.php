<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
    ob_start("ob_gzhandler");
} else {
    ob_start();
}
header('Content-Type: text/css');
header('Cache-Control: public');
header("Expires: " . date('r', strtotime("+30 Day")));
$arr = explode(',', rawurldecode($_GET['f']));
$str = '';
foreach ($arr as $fl) {
    if (substr($fl, '-4') != '.css') {
        continue;
    }
    if (file_exists($fl)) {
        $str .= file_get_contents($fl, true);
    }
}
$str = str_replace('../', '', $str);
if ($_GET['min'] == 1) {
    $str = preg_replace('/([\n][\s]*)+/', " ", $str);
    $str = str_replace("\r", '', $str);
    $str = str_replace("\n", '', $str);
}
echo $str;
