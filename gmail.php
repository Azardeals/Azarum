<?php
echo "test";
// disable warnings
if (version_compare(phpversion(), "5.3.0", ">=") == 1) {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
} else {
    error_reporting(E_ALL & ~E_NOTICE);
}
$sClientId = '437967435385-3n8oj2aulf0ug4ov514b8p5ad39u3j27.apps.googleusercontent.com';
$sClientSecret = 'QbOp2kr_pxgRRyAxq_mqdE3U';
$sCallback = 'http://bitfat2014.4demo.biz/'; // callback url, don't forget to change it to your!
$iMaxResults = 20; // max results
$sStep = 'auth'; // current step
// include GmailOath library  https://code.google.com/p/rspsms/source/browse/trunk/system/plugins/GmailContacts/GmailOath.php?r=11
include_once('GmailOath.php');
session_start();
// prepare new instances of GmailOath  and GmailGetContacts
$oAuth = new GmailOath($sClientId, $sClientSecret, $argarray, false, $sCallback);
print_r($oAuth);
$oGetContacts = new GmailGetContacts();
print_r($oGetContacts);
if ($_GET && $_GET['oauth_token']) {
    $sStep = 'fetch_contacts'; // fetch contacts step
    // decode request token and secret
    $sDecodedToken = $oAuth->rfc3986_decode($_GET['oauth_token']);
    $sDecodedTokenSecret = $oAuth->rfc3986_decode($_SESSION['oauth_token_secret']);
    // get 'oauth_verifier'
    $oAuthVerifier = $oAuth->rfc3986_decode($_GET['oauth_verifier']);
    // prepare access token, decode it, and obtain contact list
    $oAccessToken = $oGetContacts->get_access_token($oAuth, $sDecodedToken, $sDecodedTokenSecret, $oAuthVerifier, false, true, true);
    $sAccessToken = $oAuth->rfc3986_decode($oAccessToken['oauth_token']);
    $sAccessTokenSecret = $oAuth->rfc3986_decode($oAccessToken['oauth_token_secret']);
    $aContacts = $oGetContacts->GetContacts($oAuth, $sAccessToken, $sAccessTokenSecret, false, true, $iMaxResults);
    // turn array with contacts into html string
    $sContacts = $sContactName = '';
    foreach ($aContacts as $k => $aInfo) {
        $sContactName = end($aInfo['title']);
        $aLast = end($aContacts[$k]);
        foreach ($aLast as $aEmail) {
            $sContacts .= '<p>' . $sContactName . '(' . $aEmail['address'] . ')</p>';
        }
    }
} else {
    // prepare access token and set it into session
    $oRequestToken = $oGetContacts->get_request_token($oAuth, false, true, true);
    print_r($oRequestToken);
    exit;
    print_r($oRequestToken);
    exit;
    $_SESSION['oauth_token'] = $oRequestToken['oauth_token'];
    $_SESSION['oauth_token_secret'] = $oRequestToken['oauth_token_secret'];
}
?>
<!DOCTYPE html>
<html lang="en" >
    <head>
        <meta charset="utf-8" />
        <title>Google API - Get contact list | Script Tutorials</title>
        <link href="css/main.css" rel="stylesheet" type="text/css" />
    </head>
    <body>
        <header>
            <h2>Google API - Get contact list</h2>
            <a href="http://www.script-tutorials.com/google-api-get-contact-list/" class="stuts">Back to original tutorial on <span>Script Tutorials</span></a>
        </header>
        <img src="oauthLogo.png" class="google" alt="google" />
        <?php if ($sStep == 'auth'): ?>
        <center>
            <h1>Step 1. OAuth</h1>
            <h2>Please click <a href="https://www.google.com/accounts/OAuthAuthorizeToken?oauth_token=<?php echo $oAuth->rfc3986_decode($oRequestToken['oauth_token']) ?>">this link</a> in order to get access token to receive contacts</h2>
        </center>
    <?php elseif ($sStep == 'fetch_contacts'): ?>
        <center>
            <h1>Step 2. Results</h1>
            <br />
            <?php echo $sContacts ?>
        </center>
    <?php endif ?>
</body>
</html>
