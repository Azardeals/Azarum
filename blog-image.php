<?php

require_once './application-top.php';
$img_width = isset($_GET['w']) ? (int) $_GET['w'] : 100;
$img_height = isset($_GET['h']) ? (int) $_GET['h'] : 100;
$rs = $db->query("select blog_image from tbl_blogs where blog_id=" . $_GET['id']);
if ($row = $db->fetch($rs)) {
    $img_name = $row['blog_image'];
    if ($img_name == "") {
        $img_name = "default_image.jpg";
    }
    $actual_image_path = BLOG_IMAGES_PATH . $img_name;
    $img = new imageResize(BLOG_IMAGES_PATH . $img_name);
    $img->setMaxDimensions($img_width, $img_height);
    showImage($img, $actual_image_path);
}