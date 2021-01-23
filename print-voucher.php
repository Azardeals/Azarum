<?php

require_once './application-top.php';
require_once './includes/navigation-functions.php';
require_once "./qrcode/qrlib.php";
if (!isUserLogged()) {
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'login.php'));
}
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
/* ------ Insert voucher number start here-------- */
insertVoucherNumbers();
/*   ------ Insert voucher number End Here -------- */
$id = $_GET['id'];
$row_deal = [];
$message = '';
printVoucherDetail($id, $row_deal, $message, 'user');
if (!isset($message) || $message === null || strlen($message) < 10) {
    $msg->addError(t_lang('M_ERROR_INVALID_REQUEST'));
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'my-deals.php'));
}
 
echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><link rel="shortcut icon" type="image/ico" href="' . CONF_WEBROOT_URL . 'images/favicon.ico"></head><body onload="print();">';
echo emailTemplate($message);
echo '</body></html>';
