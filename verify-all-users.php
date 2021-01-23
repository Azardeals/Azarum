<?php

require_once './application-top.php';
require_once './includes/navigation-functions.php';
if (isset($_GET['code']) && isset($_GET['mail'])) {
    if ($_GET['redirect'] == 'changePassword') {
        $query = $db->query("select * from  tbl_users where  user_active = 1 and user_deleted = 0 and user_email='" . $_GET['mail'] . "'");
        $result = $db->fetch($query);
        if ($result) {
            $query1 = $db->query("select * from  tbl_user_password_resets_requests where uprr_company_id= 0 and uprr_affiliate_id =0 and  uprr_user_id=" . $result['user_id']);
            $result1 = $db->fetch($query1);
            if ($result1) {
                $query2 = $db->query("select * from  tbl_user_password_resets_requests where  uprr_expiry > (NOW() - INTERVAL 1 DAY) and uprr_tocken = '" . $_GET['code'] . "' and uprr_user_id=" . $result['user_id']);
                $result2 = $db->fetch($query2);
                if ($db->total_records($query2) == 1) {
                    loginUser($result['user_email'], $result['user_password'], $error);
                    $url = friendlyUrl(convertStringToFriendlyUrl($result['user_city']) . '/my-account.php?s=2');
                    redirectUser($url);
                } else {
                    redirectUser(CONF_WEBROOT_URL . 'login.php?s=3');
                }
            }
        }
    }
    if ($_GET['redirect'] == 'affiliateChangePassword') {
        $query = $db->query("select * from  tbl_affiliate where affiliate_email_address='" . $_GET['mail'] . "'");
        $result = $db->fetch($query);
        if ($result) {
            $query1 = $db->query("select * from  tbl_user_password_resets_requests where uprr_company_id= 0 and uprr_user_id =0 and  uprr_affiliate_id=" . $result['affiliate_id']);
            $result1 = $db->fetch($query1);
            if ($result1) {
                $query2 = $db->query("select * from  tbl_user_password_resets_requests where  uprr_expiry > (NOW() - INTERVAL 1 DAY) and uprr_tocken = '" . $_GET['code'] . "' and uprr_affiliate_id=" . $result['affiliate_id']);
                $result2 = $db->fetch($query2);
                if ($db->total_records($query2) == 1) {
                    loginAffiliateUser($result['affiliate_email_address'], $result['affiliate_password'], $error);
                    $url = friendlyUrl(convertStringToFriendlyUrl($_SESSION['city']) . '/affiliate-account.php?s=1');
                    redirectUser($url);
                } else {
                    redirectUser(CONF_WEBROOT_URL . 'affiliate-login.php?s=1');
                }
            }
        }
    }
}
