<?php

require_once './application-top.php';
require_once './includes/navigation-functions.php';
if (isset($_GET['code']) && isset($_GET['mail']) && filter_var($_GET['mail'], FILTER_VALIDATE_EMAIL)) {
    $srch = new SearchBase('tbl_users');
    $srch->addCondition('user_email', '=', $_GET['mail']);
    $srch->addCondition('reg_code', '=', $_GET['code']);
    $srch->doNotLimitRecords();
    $srch->doNotCalculateRecords();
    $rs = $srch->getResultSet();
    $result = $db->fetch($rs);
    if ($result['user_active'] == 1 && $result['user_email_verified'] == 1 && $_GET['redirect'] != 'changePassword') {
        redirectUser(CONF_WEBROOT_URL . 'login.php?s=1');
    }
    if ($result) {
        $db->query("update tbl_users set user_active = 1 , user_email_verified = 1 where user_email=" . $db->quoteVariable($_GET['mail']) . " and reg_code=" . $db->quoteVariable($_GET['code']));
        $data['sub_email'] = $result['user_email'];
        subscribeToMailChimp($data); //function defined in sit- function.php
        addBonusAmountToRegisteredUser($result['user_id']);
        loginUser($result['user_email'], $result['user_password'], $error);
        //set by default all email notifications on for first time signup
        $default_value = (in_array(intval(CONF_DEFAULT_NOTIFICATION_STATUS), array(0, 1), true)) ? CONF_DEFAULT_NOTIFICATION_STATUS : 1;
        $verified_user_id = intval($_SESSION['logged_user']['user_id']);
        $db->query("REPLACE INTO tbl_email_notification set 
                        en_user_id = $verified_user_id, 
                        en_city_subscriber = $default_value,
                        en_favourite_merchant = $default_value,
                        en_near_to_expired = $default_value,
                        en_earned_deal_buck = $default_value,
                        en_friend_buy_deal = $default_value
					");
        selectCity(intval($result['user_city']));
        if (isset($_GET['redirect'])) {
            if ($_GET['redirect'] == 'profile') {
                $url = friendlyUrl(CONF_WEBROOT_URL . 'my-account.php?s=1');
            }
            if ($_GET['redirect'] == 'changePassword') {
                $url = friendlyUrl(CONF_WEBROOT_URL . 'my-account.php?s=2');
            }
        } else {
            $url = friendlyUrl(CONF_WEBROOT_URL . 'all-deals.php?s=1');
        }
        $rs = $db->query("select * from tbl_email_templates where tpl_id=28");
        $row_tpl = $db->fetch($rs);
        $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
        $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
        $arr_replacements = array(
            'xxuser_namexx' => $result['user_name'],
            'xxlogin_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . convertStringToFriendlyUrl($row["city_name"]) . '/login/',
            'xxsite_namexx' => CONF_SITE_NAME,
            'xxclick_buttonxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '/images/start' . $_SESSION['lang_fld_prefix'] . '.png',
            'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
            'xxshadow_imgxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '/images/shadow.jpg',
            'xxserver_namexx' => $_SERVER['SERVER_NAME'],
            'xxuser_member_idxx' => $result['user_member_id'],
            'xxwebrooturlxx' => CONF_WEBROOT_URL
        );
        foreach ($arr_replacements as $key => $val) {
            $subject = str_replace($key, $val, $subject);
            $message = str_replace($key, $val, $message);
        }
        if ($row_tpl['tpl_status'] == 1) {
            sendMail($result['user_email'], $subject, signUpEmailTemplate($message, $result['reg_code'], $result['user_email']), $headers);
        }
        redirectUser($url);
    } else {
        redirectUser(CONF_WEBROOT_URL . 'login.php?s=2');
    }
}
