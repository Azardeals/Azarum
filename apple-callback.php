<?php

require_once './application-top.php';
require_once './includes/navigation-functions.php';
require_once './site-classes/apple.php';
$apple = new apple();
if ($_REQUEST['error_code']) {
    $msg->addError($_REQUEST['error_message']);
    redirectUser('login.php');
}
$apple->login();
