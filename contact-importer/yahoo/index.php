<?php
/*
  ------------------------------------------------------
  www.idiotminds.com
  --------------------------------------------------------
 */
// Include the YOS library.
require dirname(__FILE__) . '/lib/Yahoo.inc';
//ini_set('display_errors',true);
//for converting xml to array
// debug settings
//error_reporting(E_ALL | E_NOTICE); # do not show notices as library is php4 compatable
//ini_set('display_errors', true);
YahooLogger::setDebug(false);
YahooLogger::setDebugDestination('LOG');

// use memcache to store oauth credentials via php native sessions
//ini_set('session.save_handler', 'files');
//session_save_path('/tmp/');
session_start();

// Make sure you obtain application keys before continuing by visiting:
// https://developer.yahoo.com/dashboard/createKey.html

define('OAUTH_CONSUMER_KEY', 'dj0yJmk9d2hRR1FKclBWWmF3JmQ9WVdrOVRGVlVTMkZ3TldjbWNHbzlNQS0tJnM9Y29uc3VtZXJzZWNyZXQmeD01Mg--');
define('OAUTH_CONSUMER_SECRET', '7c3377e214a103aef5e8e308c1326b473dfe2b5c');
define('OAUTH_DOMAIN', $_SERVER['HTTP_HOST']);
define('OAUTH_APP_ID', 'LUTKap5g');
/*
  for live
  define('OAUTH_CONSUMER_KEY', 'dj0yJmk9Yndmc01vMW9YWm5LJmQ9WVdrOWFrZHVZa05CTkdNbWNHbzlNQS0tJnM9Y29uc3VtZXJzZWNyZXQmeD00YQ--');
  define('OAUTH_CONSUMER_SECRET', '2b4c731c6a8b82c82d0fa14536836936aba06910');
  define('OAUTH_DOMAIN', 'http://'.$_SERVER['HTTP_HOST']);
  define('OAUTH_APP_ID', 'jGnbCA4c');


  define('OAUTH_CONSUMER_KEY', 'dj0yJmk9MTZ3TzhBOWhDaDkzJmQ9WVdrOWFWZG5lbXBITldNbWNHbzlOelUyTnpZeE9EWXkmcz1jb25zdW1lcnNlY3JldCZ4PTFi');
  define('OAUTH_CONSUMER_SECRET', '5c5112de9f4700ff5d28bf983857e9432f572f72');
  define('OAUTH_DOMAIN', 'www.zumit.it');
  define('OAUTH_APP_ID', 'iWgzjG5c'); */

if (array_key_exists("logout", $_GET)) {
    // if a session exists and the logout flag is detected
    // clear the session tokens and reload the page.
    YahooSession::clearSession();
    header("Location: index.php");
}

// check for the existance of a session.
// this will determine if we need to show a pop-up and fetch the auth url,
// or fetch the user's social data.
$hasSession = YahooSession::hasSession(OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET, OAUTH_APP_ID);

if ($hasSession == FALSE) {
    // create the callback url,
    $callback = YahooUtil::current_url() . "?in_popup";
    $sessionStore = new NativeSessionStore();
    // pass the credentials to get an auth url.
    // this URL will be used for the pop-up.
    $auth_url = YahooSession::createAuthorizationUrl(OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET, $callback, $sessionStore);
} else {
    // pass the credentials to initiate a session
    $session = YahooSession::requireSession(OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET, OAUTH_APP_ID);

    // if the in_popup flag is detected,
    // the pop-up has loaded the callback_url and we can close this window.
    // if(array_key_exists("in_popup", $_GET)) {
    //   close_popup();
    //exit;
//  }
    // if a session is initialized, fetch the user's profile information
    if ($session) {
        // Get the currently sessioned user.
        $user = $session->getSessionedUser();

        // Load the profile for the current user.
        $profile = $user->getProfile();
        $contacts = $session->query("select fields.type,fields.value from social.contacts where guid=me and (fields.type='name' or fields.type='email')");

        if ($contacts == NULL) {
            echo 'No Contact Found!!';
        }
        echo "</pre>";
        if ($contacts->query->count > 0) {
            $contact = $contacts->query->results->contact;
            $contact = getAssocContactYahoo(toArray($contact));
        } else {
            echo 'No Contact Found!!';
        }
    }
}

//echo "<pre />";
//print_r($contact);
/**
 * Helper method to close the pop-up window via javascript.
 */
/*
  function close_popup() {
  ?>
  <script type="text/javascript">
  //window.close();
  </script>
  <?php
  }
 */
function getAssocContactYahoo($contacts)
{
    $ret_arr = array();
    $i = 0;
    $j = 0;
    foreach ($contacts as $k => $v) {
        if (is_array($v['fields']['value'])) {
            $v['fields']['value'] = implode(' ', $v['fields']['value']);
        }
        $ret_arr[$i][$v['fields']['type']] = $v['fields']['value'];
        if ($j % 2 == 1)
            $i++;
        $j++;
    }
    return $ret_arr;
}

function toArray($obj)
{
    if (is_object($obj))
        $obj = (array) $obj;
    if (is_array($obj)) {
        $new = array();
        foreach ($obj as $key => $val) {
            $new[$key] = toArray($val);
        }
    } else {
        $new = $obj;
    }

    return $new;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
    <head>
        <title>Import Contacts</title>

        <!-- Combo-handled YUI JS files: -->
        <script type="text/javascript" src="http://yui.yahooapis.com/combo?2.7.0/build/yahoo-dom-event/yahoo-dom-event.js"></script>
        <script type="text/javascript" src="popupmanager.js"></script>

        <!-- Combo-handled YUI CSS files: 
        <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/combo?2.7.0/build/reset-fonts-grids/reset-fonts-grids.css&2.7.0/build/base/base-min.css">-->
    </head>
    <body>
        <?php
        if ($hasSession == FALSE) {
            // if a session does not exist, output the
            // login / share button linked to the auth_url.
            sprintf("<a href=\"%s\" id=\"yloginLink\"><img src=\"http://l.yimg.com/a/i/ydn/social/updt-spurp.png\"></a>\n", $auth_url);
            echo "<script type='text/javascript'>window.location='" . $auth_url . "';</script>";
        } else if ($hasSession && $contacts) {
            $act = "../../";
            include_once '../mailer.php';
        }
        ?>
        <script type="text/javascript">
            var Event = YAHOO.util.Event;
            var _gel = function (el) {
                return document.getElementById(el)
            };

            function handleDOMReady() {
                if (_gel("yloginLink")) {
                    Event.addListener("yloginLink", "click", handleLoginClick);
                }
            }

            function handleLoginClick(event) {
                // block the url from opening like normal
                Event.preventDefault(event);

                // open pop-up using the auth_url
                var auth_url = _gel("yloginLink").href;
                PopupManager.open(auth_url, 600, 435);
            }

            Event.onDOMReady(handleDOMReady);
        </script>
    </body>
</html>