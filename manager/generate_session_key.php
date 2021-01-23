<?php

include_once './facebookapi_php5_restlib.php';
// FB_APIKEY is your facebook application api key
// FB_SECRET is your application secrete key
$FB_APIKEY = "419045244788868";
$FB_SECRET = "eaec5a0cb6592748d3c55ea1507728d7";
$fb = new FacebookRestClient($FB_APIKEY, $FB_SECRET);
$testtoken = "HLC8G4"; // Replace this value with your Token Value
$result = $fb->call_method('facebook.auth.getSession', array('auth_token' => $testtoken, 'generate_session_secret' => true));
echo "<br /><pre>";
print_r($result);
echo $session_key = $result['session_key'];
