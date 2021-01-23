<?php

define('CONF_FACEBOX_ON_FORM_SUBMIT', false);
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
    ob_start("ob_gzhandler");
    $do_not_compress = true;
} else {
    ob_start();
}
$arr = explode(',', rawurldecode($_GET['f']));
$str = '';
foreach ($arr as $fl) {
    if ($fl == 'form-validation.js.php') {
        ob_start();
        include './_classes/form-validation.js.php';
        $str .= ob_get_clean();
        continue;
    }
    if ($fl == 'form-validation-lang.php') {
        ob_start();
        include './includes/form-validation-lang.php';
        $str .= ob_get_clean();
        continue;
    }
    if ($fl == 'functions.js.php') {
        ob_start();
        include './_classes/functions.js.php';
        $str .= ob_get_clean();
        continue;
    }
    if ($fl == 'form-builder.js.php') {
        ob_start();
        include './includes/form-builder.js.php';
        $str .= ob_get_clean();
        continue;
    }
    if (substr($fl, '-3') != '.js') {
        continue;
    }
    if (file_exists($fl)) {
        $str .= file_get_contents($fl, true);
    }
}
header('Content-Type: application/javascript');
header('Cache-Control: public');
if ($_GET['min'] == 1) {
    header("Expires: " . date('r', strtotime("+30 Day")));
} else {
    header("Expires: " . date('r', strtotime("+1 Day")));
}
echo($str);
