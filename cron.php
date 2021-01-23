<?php

$CONNECTION_PROTOCOL = 'http://';
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
    $CONNECTION_PROTOCOL = 'https://';
}
echo file_get_contents($CONNECTION_PROTOCOL . $_SERVER['SERVER_NAME'] . "/cron-update-deal-status.php");
echo file_get_contents($CONNECTION_PROTOCOL . $_SERVER['SERVER_NAME'] . "/favourite-sent.php");
echo file_get_contents($CONNECTION_PROTOCOL . $_SERVER['SERVER_NAME'] . "/newsletter-sent.php");
echo file_get_contents($CONNECTION_PROTOCOL . $_SERVER['SERVER_NAME'] . "/cron_commissions.php");
echo file_get_contents($CONNECTION_PROTOCOL . $_SERVER['SERVER_NAME'] . "/expire-deal-cron.php");
echo "success";
