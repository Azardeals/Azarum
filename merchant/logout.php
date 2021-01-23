<?php

require_once '../includes/login-functions.php';
require_once '../conf/' . $_SERVER['SERVER_NAME'] . '.php';
require_once '../conf/common-conf.php';
session_start();
clearLoggedMerchantLoginCookie();
session_destroy();
$url = CONF_WEBROOT_URL . 'merchant/login.php';
header("Location: $url");
