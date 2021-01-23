<?php

require_once './application-top.php';
header("Content-Type: image/png");
$get = getQueryStringData();
$actual_image_path = '';
if ($_GET['mode'] == 'userImages') {
    $rs = $db->query("select user_avatar from tbl_users where user_id=" . $_GET['id']);
    if ($row = $db->fetch($rs)) {
        $name = $row['user_avatar'];
        $actual_image_path = USER_IMAGES_PATH . $name;
        $img = new imageResize(USER_IMAGES_PATH . $name);
        $img->setMaxDimensions(53, 53);
        $img->setResizeMethod(imageResize::IMG_RESIZE_EXTRA_ADDSPACE);
        $img->setCropMethod(imageResize::IMG_CROP_BOTH);
        showImage($img, $actual_image_path);
        exit;
    }
}
if ($_GET['mode'] == 'companyLogo') {
    $rs = $db->query("select company_logo from tbl_companies where company_id=" . $_GET['id']);
    if ($row = $db->fetch($rs)) {
        $name = $row['company_logo'];
        $actual_image_path = COMPANY_LOGO_PATH . $name;
        $img = new imageResize(COMPANY_LOGO_PATH . $name);
        $img->setMaxDimensions(53, 53);
        $img->setResizeMethod(imageResize::IMG_RESIZE_EXTRA_ADDSPACE);
        $img->setCropMethod(imageResize::IMG_CROP_BOTH);
        showImage($img, $actual_image_path);
        exit;
    }
}
