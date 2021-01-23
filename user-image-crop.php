<?php

ini_set('memory_limit', '128M');
require_once './application-top.php';
$get = getQueryStringData();
$actual_image_path = '';
if (!is_numeric($get['id'])) {
    $name = 'defaults.jpg';
} else {
    $rs = $db->query("select admin_avtar from tbl_admin where admin_id=" . $get['id']);
    if (!$row = $db->fetch($rs)) {
        $name = 'defaults.jpg';
    } else {
        $name = $row['admin_avtar'];
        if (empty($name)) {
            $name = 'defaults.jpg';
        }
        $actual_image_path = UPLOADS_PATH . 'admin-images/' . $name;
    }
}
if (!file_exists(UPLOADS_PATH . 'admin-images/' . $name) || $name == '') {
    $name = 'defaults.jpg';
}
$img = new imageResize(UPLOADS_PATH . 'admin-images/' . $name);
switch (strtoupper($get['type'])) {
    case 'ADMIN':
        $img->setMaxDimensions(300, 300);
        break;
    case 'PROFILE':
        $img->setMaxDimensions(100, 100);
        break;
    default:
        $img->setMaxDimensions(400, 400);
        break;
}
$img->setCropMethod(imageResize::IMG_CROP_BOTH);

showImage($img, $actual_image_path);
