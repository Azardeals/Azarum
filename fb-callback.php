<?php

require_once './application-top.php';
require_once './includes/navigation-functions.php';
require_once './site-classes/facebook.php';
$fb = new facebook();
if ($_REQUEST['error_code']) {
    $msg->addError($_REQUEST['error_message']);
    redirectUser('login.php');
}
$fb->callback();
