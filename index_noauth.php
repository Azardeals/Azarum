<?php

$valid_urls = array('paypal-ipn.php');
if (!in_array($_GET['url'], $valid_urls)) {
    die('Unauthorized Access.');
}
require_once $_GET['url'];
