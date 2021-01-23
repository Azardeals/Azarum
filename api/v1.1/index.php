<?php

try {
    require_once realpath(dirname(__FILE__) . "/../../") . '/application-top.php';
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    require_once dirname(__FILE__) . '/classes/YoDealsWebAPI.class.php';
    $api = new YoDealsWebAPI();
    exit($api->executeRequest());
} catch (Exception $e) {
    header('HTTP/1.1 400 OK');
    header("Content-Type: application/json; charset=utf-8");
    exit(json_encode(["error" => ["message" => $e->getMessage(), "code" => $e->getCode()]]));
}