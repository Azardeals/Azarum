<?php

/* require_once("../application-top.php"); */

/*
  D I S C L A I M E R
  WARNING: ANY USE BY YOU OF THE SAMPLE CODE PROVIDED IS AT YOUR OWN RISK.
  Authorize.Net provides this code "as is" without warranty of any kind, either express or implied, including but not limited to the implied warranties of merchantability and/or fitness for a particular purpose.
  Authorize.Net owns and retains all right, title and interest in and to the Automated Recurring Billing intellectual property.
 */
global $db;
$rs = $db->query("select * from tbl_payment_options where po_id=3");
$row = $db->fetch($rs);
$login_id = (CONF_PAYMENT_PRODUCTION == 1) ? $row['po_account_id'] : $row['po_test_account_id'];
$transaction_key = (CONF_PAYMENT_PRODUCTION == 1) ? $row['po_key'] : $row['po_test_key'];
$host_url = (CONF_PAYMENT_PRODUCTION == 1) ? $row['po_address'] : $row['po_test_address'];
global $g_loginname, $g_transactionkey, $g_apihost, $g_apipath;
$g_loginname = $login_id; // Keep this secure.
$g_transactionkey = $transaction_key; // Keep this secure.
$g_apihost = $host_url;
$g_apipath = "/xml/v1/request.api";
?>
