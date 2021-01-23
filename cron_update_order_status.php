<?php

require_once './application-top.php';
require_once './site-classes/order.cls.php';
$order = new userOrder();
$pendingOrders = $order->getAllPendingOrders();
$success = [];
$fail = [];
foreach ($pendingOrders AS $o) {
    $result = $order->markOrderCancelled($o['order_id']);
    if ($result) {
        $success[] = $o['order_id'];
    } else {
        $fail[] = $o['order_id'];
    }
}
echo 'Request processed successfully with (' . count($success) . ') [' . implode(', ', $success) . '] orders marked as cancelled and failed to update order status for the following orders (' . count($fail) . ') [' . implode(', ', $fail) . ']';
echo '<pre>' . print_r($pendingOrders, true);
die;
