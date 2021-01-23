<?php

require_once './application-top.php';
if (!session_id()) {
    session_start();
}
require_once './site-classes/order.cls.php';
require_once './includes/buy-deal-functions.php';
$cart = new Cart();
$post = getPostedData();
$arr = [
    'ott_order_id' => $post['custom'],
    'ott_transaction_id' => $post['txn_id'],
    'ott_transaction_status' => (strtoupper($post['payment_status']) == 'COMPLETED') ? 1 : 0,
    'ott_gateway_response' => var_export($post, true),
];
if (!$db->insert_from_array('tbl_order_transactions_tracking', $arr)) {
    die('Transaction details could not be updated.');
}
$ott_id = $db->insert_id();
$req = 'cmd=_notify-validate';
foreach ($post as $key => $value) {
    $value = urlencode($value);
    $req .= "&$key=$value";
}
// post back to PayPal system to validate
$headers = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
$fromemail = CONF_EMAILS_FROM;
$fromname = CONF_EMAILS_FROM_NAME;
$headers .= "From: " . $fromname . " <" . $fromemail . ">\r\n";
$rs = $db->query("select * from tbl_payment_options where po_id=1");
$row_paypal_setting = $db->fetch($rs);
$paypal_url = (CONF_PAYMENT_PRODUCTION == 0) ? $row_paypal_setting['po_test_address'] : $row_paypal_setting['po_address'];
// assign posted variables to local variables
$item_name = $_POST['item_name'];
$item_number = $_POST['item_number'];
$payment_status = $_POST['payment_status'];
$payment_amount = $_POST['mc_gross'];
$payment_currency = $_POST['mc_currency'];
$txn_id = $_POST['txn_id'];
$receiver_email = $_POST['receiver_email'];
$payer_email = $_POST['payer_email'];
$res = validatePaypalIpn($req);
if ($ott_id > 0) {
    $db->query("Update tbl_order_transactions_tracking set ott_user_verified='$res' where ott_id=" . $ott_id);
}
if (strcmp($res, "VERIFIED") == 0) {
    $arr = [
        'ot_order_id' => $post['custom'],
        'ot_transaction_id' => $post['txn_id'],
        'ot_transaction_status' => (strtoupper($post['payment_status']) == 'COMPLETED') ? 1 : 0,
        'ot_gateway_response' => var_export($post, true)
    ];
    if (!$db->insert_from_array('tbl_order_transactions', $arr)) {
        die('Transaction details could not be updated. Please contact administrator. Your payment is successful with transaction ID ' . $response->transaction_id);
    }
    if (strtoupper($post['payment_status']) == 'COMPLETED') {
        $order = new userOrder();
        $order->markOrderPaid($post['custom']);
        $orderId = $post['custom'];
        ################ EMAIL TO USERS#################
        notifyAboutPurchase($orderId);
        ################ EMAIL TO USERS#################
        $error = '';
    }
    $cart->clearCart();
}
