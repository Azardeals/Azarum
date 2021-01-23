<?php

require_once '../application-top.php';

function getAdminCookieName()
{
    return '_atoken';
}

function getLoggedAdminSession()
{
    return $_SESSION['admin_logged'];
}

function getClientIpAddress()
{
    return $_SERVER['REMOTE_ADDR'];
}

function getClientBrowser()
{
    return $_SERVER['HTTP_USER_AGENT'];
}

function checkLoginTokenInDB($token)
{
    global $db;
    $srch = new SearchBase('tbl_auser_auth_token');
    $srch->addCondition('auauth_token', '=', $token);
    $srch->doNotCalculateRecords();
    $srch->doNotLimitRecords();
    $rs = $srch->getResultSet();
    return $db->fetch($rs);
}

function saveRememberLoginToken(&$values)
{
    global $db;
    if ($db->insert_from_array('tbl_auser_auth_token', $values)) {
        return true;
    }
    return false;
}

function generateLoginToken()
{
    do {
        $salt = substr(md5(microtime()), 5, 12);
        $token = md5($salt . microtime() . substr($salt, 5));
    } while (checkLoginTokenInDB($token));
    return $token;
}

function clearRememberLoginCookie($cookie_name)
{
    global $db;
    if (!isset($_COOKIE[$cookie_name])) {
        return false;
    }
    if (strlen($_COOKIE[$cookie_name])) {
        $db->deleteRecords('tbl_auser_auth_token', array(
            'smt' => '`auauth_token` = ?',
            'vals' => array($_COOKIE[$cookie_name])
        ));
    }
    setcookie($cookie_name, '', time() - 3600, '/');
    return true;
}

function doCookieAdminLogin()
{
    $cookie_name = getAdminCookieName();
    if (isset($_COOKIE[$cookie_name])) {
        $token = $_COOKIE[$cookie_name];
        $auth_row = false;
        if (strlen($token) != 32 || !($auth_row = checkLoginTokenInDB($token))) {
            clearRememberLoginCookie($cookie_name);
            return false;
        }
        $browser = getClientBrowser();
        $ip = getClientIpAddress();
        if ($auth_row['auauth_user_type'] != 1 || strtotime($auth_row['auauth_expiry']) < strtotime('now') || $auth_row['auauth_browser'] != $browser || $ip != $auth_row['auauth_last_ip']) {
            clearRememberLoginCookie($cookie_name);
            return false;
        }
        if (loginAdministratorById($auth_row['auauth_user_id'], $auth_row['auauth_user_password'])) {
            return true;
        }
        clearRememberLoginCookie($cookie_name);
    }
    return false;
}

function setAdminLoginCookie($username, $password)
{
    $logged_admin = getLoggedAdminSession();
    if ($username !== $logged_admin['admin_username'] && !isset($logged_admin['admin_id'])) {
        return false;
    }
    $admin_id = intval($logged_admin['admin_id']);
    if ($admin_id < 1) {
        return false;
    }
    $token = generateLoginToken();
    $expiry = strtotime('+7 day');
    $values = array(
        'auauth_user_id' => $admin_id,
        'auauth_user_password' => $password,
        'auauth_user_type' => 1, /* 1 for Admin */
        'auauth_token' => $token,
        'auauth_expiry' => date('Y-m-d H:i:s', $expiry),
        'auauth_browser' => getClientBrowser(),
        'auauth_last_access' => date('Y-m-d H:i:s'),
        'auauth_last_ip' => getClientIpAddress()
    );
    if (saveRememberLoginToken($values)) {
        $cookie_name = getAdminCookieName();
        setcookie($cookie_name, $token, $expiry, '/');
        return true;
    }
    return false;
}

function clearLoggedAdminLoginCookie()
{
    $cookie_name = getAdminCookieName();
    clearRememberLoginCookie($cookie_name);
}

/* Functions for Merchant start from here */

function getMerchantCookieName()
{
    return '_mtoken';
}

function getLoggedMerchantSession()
{
    return $_SESSION['logged_user'];
}

function doCookieMerchantLogin()
{
    $cookie_name = getMerchantCookieName();
    if (isset($_COOKIE[$cookie_name])) {
        $token = $_COOKIE[$cookie_name];
        $auth_row = false;
        if (strlen($token) != 32 || !($auth_row = checkLoginTokenInDB($token))) {
            clearRememberLoginCookie($cookie_name);
            return false;
        }
        $browser = getClientBrowser();
        $ip = getClientIpAddress();
        if ($auth_row['auauth_user_type'] != 2 || strtotime($auth_row['auauth_expiry']) < strtotime('now') || $auth_row['auauth_browser'] != $browser || $ip != $auth_row['auauth_last_ip']) {
            clearRememberLoginCookie($cookie_name);
            return false;
        }
        if (loginMerchantById($auth_row['auauth_user_id'], $auth_row['auauth_user_password'])) {
            return true;
        }
        clearRememberLoginCookie($cookie_name);
    }
    return false;
}

function setMerchantLoginCookie($merchant_email, $password)
{
    $logged_merchant = getLoggedMerchantSession();
    if ($merchant_email !== $logged_merchant['company_email'] && !isset($logged_merchant['company_id'])) {
        return false;
    }
    $company_id = intval($logged_merchant['company_id']);
    if ($company_id < 1) {
        return false;
    }
    $token = generateLoginToken();
    $expiry = strtotime('+7 day');
    $values = array(
        'auauth_user_id' => $company_id,
        'auauth_user_password' => $password,
        'auauth_user_type' => 2, /* 2 for Merchant */
        'auauth_token' => $token,
        'auauth_expiry' => date('Y-m-d H:i:s', $expiry),
        'auauth_browser' => getClientBrowser(),
        'auauth_last_access' => date('Y-m-d H:i:s'),
        'auauth_last_ip' => getClientIpAddress()
    );
    if (saveRememberLoginToken($values)) {
        $cookie_name = getMerchantCookieName();
        setcookie($cookie_name, $token, $expiry, '/');
        return true;
    }
    return false;
}

function clearLoggedMerchantLoginCookie()
{
    $cookie_name = getMerchantCookieName();
    clearRememberLoginCookie($cookie_name);
}

/* Functions for Merchant end here */
/* Functions for Representative start from here */

function getRepresentativeCookieName()
{
    return '_rtoken';
}

function getLoggedRepresentativeSession()
{
    return $_SESSION['logged_user'];
}

function doCookieRepresentativeLogin()
{
    $cookie_name = getRepresentativeCookieName();
    if (isset($_COOKIE[$cookie_name])) {
        $token = $_COOKIE[$cookie_name];
        $auth_row = false;
        if (strlen($token) != 32 || !($auth_row = checkLoginTokenInDB($token))) {
            clearRememberLoginCookie($cookie_name);
            return false;
        }
        $browser = getClientBrowser();
        $ip = getClientIpAddress();
        if ($auth_row['auauth_user_type'] != 3 || strtotime($auth_row['auauth_expiry']) < strtotime('now') || $auth_row['auauth_browser'] != $browser || $ip != $auth_row['auauth_last_ip']) {
            clearRememberLoginCookie($cookie_name);
            return false;
        }
        if (loginRepresentativeById($auth_row['auauth_user_id'], $auth_row['auauth_user_password'])) {
            return true;
        }
        clearRememberLoginCookie($cookie_name);
    }
    return false;
}

function setRepresentativeLoginCookie($representative_email, $password)
{
    $logged_representative = getLoggedRepresentativeSession();
    if ($representative_email !== $logged_representative['rep_email_address'] && !isset($logged_representative['rep_id'])) {
        return false;
    }
    $representative_id = intval($logged_representative['rep_id']);
    if ($representative_id < 1) {
        return false;
    }
    $token = generateLoginToken();
    $expiry = strtotime('+7 day');
    $values = array(
        'auauth_user_id' => $representative_id,
        'auauth_user_password' => $password,
        'auauth_user_type' => 3, /* 3 for Representative */
        'auauth_token' => $token,
        'auauth_expiry' => date('Y-m-d H:i:s', $expiry),
        'auauth_browser' => getClientBrowser(),
        'auauth_last_access' => date('Y-m-d H:i:s'),
        'auauth_last_ip' => getClientIpAddress()
    );
    if (saveRememberLoginToken($values)) {
        $cookie_name = getRepresentativeCookieName();
        setcookie($cookie_name, $token, $expiry, '/');
        return true;
    }
    return false;
}

function clearLoggedRepresentativeLoginCookie()
{
    $cookie_name = getRepresentativeCookieName();
    clearRememberLoginCookie($cookie_name);
}

/* Functions for Representative end here */