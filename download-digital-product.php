<?php

require_once './application-top.php';
require_once './includes/navigation-functions.php';
require_once './site-classes/digital-product.cls.php';
if (!isset($_GET['device']) || !isUserLogged()) {
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'login.php'));
}
error_reporting(E_ALL);
$productId = $_GET['product_id'];
$id = $_GET['id'];
$length = strlen($id);
if ($length > 13) {
    $order_id = substr($id, 0, 13);
    $LastVouvherNo = ($length - 13);
    $voucher_no = substr($id, 13, $LastVouvherNo);
} else {
    echo 'Invalid request!!';
    exit;
}
$userId = $_SESSION['logged_user']['user_id'];
if (isset($_GET['user_id']))
    $userId = $_GET['user_id'];
$srch = new SearchBase('tbl_orders', 'o');
$srch->doNotCalculateRecords();
$srch->doNotLimitRecords();
$srch->addCondition('order_id', '=', $order_id);
$srch->addCondition('order_user_id', '=', $userId);
$rs = $srch->getResultSet();
$row = $db->fetch($rs);
if (!($row)) {
    die(t_lang('M_TXT_NOT_AUTHORISED'));
}
$digitalProduct = new DigitalProduct();
$row = $digitalProduct->getDigitalProductRecord($productId);
if (!($row)) {
    die(t_lang('M_TXT_RECORD_NOT_FOUND'));
}
$file = $row['dpe_product_file'];
$file_name = $row['dpe_product_file_name'];
$file_full_path = DIGITAL_UPLOADS_PATH . $file;
if (!empty($file)) {
    if (file_exists($file_full_path) && is_file($file_full_path)) {
        makeDownload($file_name, $file_full_path);
    }
}

function makeDownload($file_name, $file_full_path)
{
    switch (strtolower(end(explode(".", $file_name)))) {
        case "zip":
            $type = "application/zip";
            break;
        case "rar":
            $type = "application/x-rar-compressed";
            break;
        default:
            $type = "application/octet-stream";
    }
    ob_end_clean();
    ob_start();
    header("Content-Type: " . $type);
    header("Content-Disposition: attachment; filename=" . $file_name . "");
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: " . date('r', strtotime("-1 Day")));
    header("Content-Length: " . filesize($file_full_path));
    readfile($file_full_path);
    exit;
}
