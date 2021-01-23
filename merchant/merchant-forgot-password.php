<?php
require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
if ($_SESSION['cityname'] != "") {
    $cityname = $_SESSION['cityname'];
} else {
    $cityname = 1;
}
if (isCompanyUserLogged()) {
    redirectUser(CONF_WEBROOT_URL . 'merchant/merchant-account.php');
}
if (isUserLogged()) {
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . convertStringToFriendlyUrl($_SESSION['cityname']) . '/my-account.php'));
}
$frmForgot = getMBSFormByIdentifier('frmForgotPassword');
if (isset($_POST['user_email'])) {
    $post = getPostedData();
    if (!$frmForgot->validate($post)) {
        $errors = $frmForgot->getValidationErrors();
        foreach ($errors as $error) {
            $msg->addError($error);
        }
    } else {
        $error = '';
        $selectEmail = new SearchBase('tbl_companies', 'c');
        $selectEmail->addCondition('company_deleted', '=', 0);
        $selectEmail->addCondition('company_active', '=', 1);
        $selectEmail->addCondition('company_email', '=', $post['user_email']);
        $selectEmail->getQuery();
        $email_listing = $selectEmail->getResultSet();
        $RowCheck = $selectEmail->recordCount($email_listing);
        if ($RowCheck > 0) {
            $row = $db->fetch($email_listing);
            $resultReset = $db->query("select * from tbl_user_password_resets_requests where uprr_user_id= 0 and uprr_affiliate_id =0 and   uprr_company_id=" . intval($row['company_id']));
            $row_reset = $db->fetch($resultReset);
            $company_id = intval($row_reset['uprr_company_id']);
            $affiliate_id = 0;
            $user_id = 0;
            $rep_id = 0;
            $code = mt_rand(0, 9999999999);
            if ($db->total_records($resultReset) == 0) {
                $company_id = intval($row['company_id']);
                $db->query("INSERT INTO tbl_user_password_resets_requests VALUES (0, " . $db->quoteVariable($code) . ", now(),$company_id,0,0);");
                $result = 1;
            } else {
                $resultReset1 = $db->query("select * from tbl_user_password_resets_requests where  uprr_expiry < (NOW() - INTERVAL 1 DAY) and uprr_company_id=" . intval($row['company_id']));
                $row_reset1 = $db->fetch($resultReset1);
                if ($db->total_records($resultReset1) == 1) {
                    $db->query("UPDATE tbl_user_password_resets_requests SET uprr_expiry =  NOW() , uprr_tocken ='$code' WHERE  uprr_company_id=" . $row['company_id']);
                    $result = 1;
                } else {
                    $result = 0;
                    $msg->addError(t_lang('M_TXT_FORGOT_PASSWORD_ERROR_MESSAGE'));
                    redirectUser(CONF_WEBROOT_URL . 'merchant-forgot-password.php?sid=' . rand(1, 9999));
                }
            }
            if ($result == 1) {
                $email = $row['company_email'];
                $pass = $row['company_password'];
                $user_code = $row['reg_code'];
                $rs = $db->query("select * from tbl_email_templates where tpl_id=4");
                $row_tpl = $db->fetch($rs);
                $verification_url = 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'reset-password.php?code=' . $user_id . '_' . $company_id . '_' . $affiliate_id . '_' . $rep_id . '_' . $code;
                $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
                $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
                $arr_replacements = array(
                    'xxuser_namexx' => $row['company_name'],
                    'xxuser_emailxx' => $row['company_email'],
                    'xxuser_passwordxx' => '<a style="text-decoration:none;font-weight:bold;color:#0066cc;" href="' . $verification_url . '">' . t_lang('M_TXT_CLICK_HERE') . '</a>',
                    'xxsite_namexx' => CONF_SITE_NAME,
                    'xxserver_namexx' => $_SERVER['SERVER_NAME'],
                    'xxwebrooturlxx' => CONF_WEBROOT_URL,
                    'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL
                );
                foreach ($arr_replacements as $key => $val) {
                    $subject = str_replace($key, $val, $subject);
                    $message = str_replace($key, $val, $message);
                }
                if ($row_tpl['tpl_status'] == 1) {
                    sendMail($post['user_email'], $subject, emailTemplate(($message)), $headers);
                }
                ##############################################
                $msg->addMsg(t_lang('M_TXT_PASSWORD_SENT'));
                redirectUser(CONF_WEBROOT_URL . 'merchant-forgot-password.php?sid=' . rand(1, 9999));
            }
        } else {
            $msg->addError(t_lang('M_TXT_EMAIL_NOT_FOUND'));
            redirectUser(CONF_WEBROOT_URL . 'forgot-password.php?sid=' . rand(1, 9999));
        }
    }
}
require_once './header.php';
?>
<!--body start here-->
<div id="body">
    <!--left_Area start here-->
    <div id="left_Area">
        <div class="content_area">
            <!--content_wrapper start here-->
            <div class="content_wrapper">
                <div class="content_wrap_head">
                    <h2><?php echo t_lang('M_TXT_MERCHANT_FORGOT_PASSWORD'); ?> </h2>
                </div>
                <div class="content_wrap">
                    <div class="inner_area">
                        <?php
                        echo $msg->display();
                        echo $frmForgot->getFormHtml();
                        ?>
                    </div>
                </div>
                <img src="<?php echo CONF_WEBROOT_URL; ?>images/center_bg_bottom.png" alt="" class="content_wrapBg" />
            </div>
            <!--content_wrapper end here-->
        </div>
    </div>
    <!--right_Area start here-->
    <?php include './right.inc.php'; ?>
    <!--right_Area end here-->
    <div class="clear"></div>
</div>
<!--body end here-->
<div class="clear"></div>
<?php
require_once './footer.php';
