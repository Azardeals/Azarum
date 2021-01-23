<?php

require_once './application-top.php';
$get = getQueryStringData();
$actual_image_path = '';
if ($_GET['mode'] == 'images') {
    $rs = $db->query("select things_image from tbl_things_todo where things_id=" . $_GET['id']);
    if ($row = $db->fetch($rs)) {
        $name = $row['things_image'];
        $actual_image_path = THINGS_IMAGES_PATH . $name;
        $img = new imageResize(THINGS_IMAGES_PATH . $name);
        $img->setMaxDimensions(420, 280);
        $img->setResizeMethod(imageResize::IMG_RESIZE_EXTRA_ADDSPACE);
        showImage($img, $actual_image_path);
    }
} else {
    if (!is_numeric($get['id'])) {
        $name = 'no-image.jpg';
    } else {
        $rs = $db->query("select things_image from tbl_things_todo where things_id=" . $_GET['id']);
        if (!$row = $db->fetch($rs)) {
            $name = 'no-image.jpg';
        } else {
            $name = $row['things_image'];
            $actual_image_path = THINGS_IMAGES_PATH . $name;
        }
    }
    if (!file_exists(THINGS_IMAGES_PATH . $name) || $name == '') {
        $name = 'no-image.jpg';
    }
    $img = new imageResize(THINGS_IMAGES_PATH . $name);
    switch (strtoupper($get['type'])) {
        case 'MAIN':
            $img->setMaxDimensions(241, 192);
            break;
        case 'LIST':
            $img->setMaxDimensions(205, 135);
            break;
        default:
            $img->setMaxDimensions(100, 80);
            break;
    }
    $img->setResizeMethod(ImageResize::IMG_RESIZE_EXTRA_CROP);
    showImage($img, $actual_image_path);
}