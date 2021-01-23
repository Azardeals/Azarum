<?php

require_once './application-top.php';
checkAdminPermission(1);
$post = getPostedData();
$get = getQueryStringData();
$status = (isset($post['status'])) ? $post['status'] : $get['status'];
if (intval($status) == 0 || intval($status) == 1) {
    global $db;
    $query = "UPDATE tbl_configurations SET `conf_val` = {$status} WHERE `conf_name` = 'conf_manager_left_nav_display_status'";
    $db->query($query);
    exit;
}
header('HTTP/1.0 403 Forbidden');
