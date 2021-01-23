<?php

session_start();
setcookie('u', '', time() - 3600 * 24 * 30, '/');
setcookie('p', '', time() - 3600 * 24 * 30, '/');
setcookie('mu', '', time() - 3600 * 24 * 30, '/');
setcookie('mp', '', time() - 3600 * 24 * 30, '/');
session_destroy();
require_once dirname(__FILE__) . '/conf/' . $_SERVER['SERVER_NAME'] . '.php';
require_once dirname(__FILE__) . '/conf/common-conf.php';
header('Location: ' . CONF_WEBROOT_URL);
exit(0);
