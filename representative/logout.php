<?php

require_once '../includes/login-functions.php';
require_once '..conf/' . $_SERVER['SERVER_NAME'] . '.php';
require_once '../conf/common-conf.php';
session_start();
clearLoggedRepresentativeLoginCookie();
session_destroy();
$url = CONF_WEBROOT_URL . 'representative/login.php';
header("Location: $url");
