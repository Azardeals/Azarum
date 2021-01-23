<?php

ini_set('memory_limit', '128M');
require_once './application-top.php';
$get = getQueryStringData();
$actual_image_path = '';
if (!is_numeric($get['id'])) {
    $name = 'no-image.jpg';
} else {
    $rs = $db->query("select deal_img_name from tbl_deals where deal_id=" . $get['id']);
    if (!$row = $db->fetch($rs)) {
        $name = 'no-image.jpg';
    } else {
        $name = $row['deal_img_name'];
        $actual_image_path = DEAL_IMAGES_PATH . $name;
    }
}
/** for get the galleries images of deal * */
if (is_numeric($get['galleryImgId'])) {
    $rs = $db->query("select dimg_name from  tbl_deals_images where dimg_id=" . $get['galleryImgId']);
    if (!$row = $db->fetch($rs)) {
        $name = 'no-image.jpg';
    } else {
        $name = $row['dimg_name'];
        $actual_image_path = DEAL_IMAGES_PATH . $name;
    }
}
if (!file_exists(DEAL_IMAGES_PATH . $name) || $name == '') {
    $name = 'no-image.jpg';
}
$img = new imageResize(DEAL_IMAGES_PATH . $name);
$size = getimagesize($actual_image_path);
if (empty($size[0])) {
    $size[0] = 250;
    $size[1] = 250;
}
switch (strtoupper($get['type'])) {
    case 'ACTUAL':
        $img->setMaxDimensions($size[0], $size[1]);
        break;
    case 'OTHER':
        $img->setMaxDimensions(142, 97);
        break;
    CASE 'CARTTABLE':
        $img->setMaxDimensions(600, 450);
        break;
    case 'PASTLIST':
        $img->setMaxDimensions(307, 132);
        break;
    case 'CATEGORY':
        $img->setMaxDimensions(250, 250);
        break;
    case 'CATEGORYLIST':
        $img->setMaxDimensions(250, 250);
        break;
    case 'MORECITIES':
        $img->setMaxDimensions(250, 250);
        break;
    case 'EMAILMAIN':
        $img->setMaxDimensions(570, 238);
        break;
    case 'EMAILUPCOMING':
        $img->setMaxDimensions(270, 150);
        break;
    case 'INSTANT':
        $img->setMaxDimensions(600, 450);
        break;
    case 'ESCAPEMAIN':
        $img->setMaxDimensions(722, 236);
        break;
    case 'ESCAPE':
        $img->setMaxDimensions(355, 200);
        break;
    case 'ADMIN':
        $img->setMaxDimensions(189, 123);
        break;
    case'ADMINDEALPAGE':
        $img->setMaxDimensions(100, 100);
        break;
    default:
        $img->setMaxDimensions(80, 66);
        break;
}
$img->setResizeMethod(ImageResize::IMG_RESIZE_EXTRA_CROP);
$img->setCropMethod(imageResize::IMG_CROP_BOTH);
showImage($img, $actual_image_path);
