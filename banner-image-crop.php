<?php

require_once './application-top.php';
Header("Content-Type: image/png");
$get = getQueryStringData();
if (!is_numeric($get['banner'])) {
    $name = 'no-image.jpg';
} else {
    $rs = $db->query("select banner_image from tbl_banner where banner_id='" . intval($get['banner']) . "'");
    if (!$row = $db->fetch($rs)) {
        $name = 'no-image.jpg';
    } else {
        $name = $row['banner_image'];
    }
}
if (!file_exists(BANNER_IMAGES_PATH . $name) || $name == '') {
    $name = 'no-image.jpg';
}
$img = new imageResize(BANNER_IMAGES_PATH . $name);
switch (strtoupper($get['type'])) {
    case '0':
        $img->setMaxDimensions(1000, 450);
        break;
    case '1':
        $img->setMaxDimensions(1200, 100);
        break;
    case '2':
        $img->setMaxDimensions(277, 120);
        break;
    case '3':
        $img->setMaxDimensions(120, 120);
        break;
    case '4':
        $img->setMaxDimensions(1000, 450);
        break;
    case '5':
        $img->setMaxDimensions(360, 590);
        break;
    case '6':
        $img->setMaxDimensions(1000, 450);
        break;
    case'ADMINBANNERPAGE':
        $img->setMaxDimensions(100, 100);
        break;
    default:
        $img->setMaxDimensions(272, 120);
        break;
}
$img->setResizeMethod(1);
showImage($img, BANNER_IMAGES_PATH . $name);
