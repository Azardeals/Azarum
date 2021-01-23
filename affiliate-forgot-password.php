<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
if (isAffiliateUserLogged()) {
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'affiliate-account.php'));
}
if (isUserLogged()) {
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'my-account.php'));
}
$frmForgot = getMBSFormByIdentifier('frmForgotPassword');
$fld = $frmForgot->getField('user_email');
$fld->extra = "placeholder='" . t_lang('M_TXT_EMAIL_ADDRESS') . "*'";
$fld = $frmForgot->getField('submit');
$fld->value = t_lang('M_TXT_SUBMIT');
$url = CONF_WEBROOT_URL . 'affiliate-login.php';
$fld->html_after_field = '&nbsp;<a href="' . friendlyUrl($url) . '" class="linknormal right">' . t_lang('M_TXT_BACK_TO_LOGIN') . '</a>';
updateFormLang($frmForgot);
updateFormLang($frmForgot);
if (isset($_POST['user_email'])) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $post = getPostedData();
        $error = '';
        $selectEmail = new SearchBase('tbl_affiliate', 'user');
        $selectEmail->addCondition('affiliate_status', '=', 1);
        $selectEmail->addCondition('affiliate_email_address', '=', $post['user_email']);
        $selectEmail->getQuery();
        $email_listing = $selectEmail->getResultSet();
        $RowCheck = $selectEmail->recordCount($email_listing);
        if ($RowCheck > 0) {
            $row = $db->fetch($email_listing);
            $resultReset = $db->query("select * from tbl_user_password_resets_requests where uprr_company_id= 0 and uprr_user_id =0 and uprr_affiliate_id=" . intval($row['affiliate_id']));
            $row_reset = $db->fetch($resultReset);
            $user_id = 0;
            $affiliate_id = intval($row['affiliate_id']);
            $company_id = 0;
            $rep_id = 0;
            $code = mt_rand(0, 9999999999);
            if ($db->total_records($resultReset) == 0) {
                $db->query("INSERT INTO tbl_user_password_resets_requests VALUES (0, " . $db->quoteVariable($code) . ", now(),0,$affiliate_id,0);");
                $result = 1;
            } else {
                $resultReset1 = $db->query("select * from tbl_user_password_resets_requests where  uprr_expiry < (NOW() - INTERVAL 1 DAY)
				and uprr_affiliate_id=" . intval($row['affiliate_id']));
                $row_reset1 = $db->fetch($resultReset1);
                if ($db->total_records($resultReset1) == 1) {
                    $db->query("UPDATE tbl_user_password_resets_requests SET uprr_expiry =  NOW() , uprr_tocken = " . $db->quoteVariable($code) . " WHERE uprr_affiliate_id=" . intval($row['affiliate_id']));
                    $result = 1;
                } else {
                    $result = 0;
                    $msg->addError(t_lang('M_TXT_FORGOT_PASSWORD_ERROR_MESSAGE'));
                }
            }
            if ($result == 1) {
                $email = $row['affiliate_email_address'];
                $pass = $row['affiliate_password'];
                $verification_url = 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'reset-password.php?code=' . $user_id . '_' . $company_id . '_' . $affiliate_id . '_' . $rep_id . '_' . $code;
                $rs = $db->query("select * from tbl_email_templates where tpl_id=4");
                $row_tpl = $db->fetch($rs);
                $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
                $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
                $arr_replacements = array(
                    'xxuser_namexx' => $row['affiliate_fname'] . ' ' . $row['affiliate_lname'],
                    'xxuser_emailxx' => $row['affiliate_email_address'],
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
                    sendMail($post['user_email'], $subject, emailTemplate($message), $headers);
                }
                ##############################################
                $msg->addMsg(t_lang('M_TXT_PASSWORD_SENT'));
            }
        } else {
            $msg->addError(t_lang('M_TXT_EMAIL_NOT_FOUND'));
        }
    }
}
require_once './header.php';
?>
<!--bodyContainer start here-->
<section class="sectionfull">
    <div class="sectionfull__centered">
        <div class="sectiontable">
            <aside class="sectiontable__leftcell">
                <ul class="tabs__dual clearfix">
                    <li class="current"><?php echo t_lang('M_TXT_FORGOT_PASSWORD'); ?></li>
                </ul>
                <p><?php echo t_lang('M_TXT_FORGOT_PASSWORD_HEADER_CONTENT'); ?></p>
                <div class="formwrap">
                    <?php echo $frmForgot->getFormTag(); ?> 
                    <table class="formwrap__table">
                        <tr>
                            <td> <?php echo $frmForgot->getFieldHtml('user_email'); ?> </td>
                        </tr>
                        <tr>
                            <td><?php echo $frmForgot->getFieldHtml('submit'); ?> </td>
                        </tr>
                    </table>
                    <?php echo $frmForgot->getExternalJs(); ?> 
                    </form> 
                </div>
            </aside>
            <?php
            $rows = fetchBannerDetail(5, 1);
            if (!empty($rows[0])) {
                $src = CONF_WEBROOT_URL . 'banner-image-crop.php?banner=' . $rows[0]['banner_id'] . '&type=' . $rows[0]['banner_type'];
            }
            ?>
            <aside class="sectiontable__rightcell" style="background-image:url(<?php echo $src; ?>); background-repeat:no-repeat;"></aside>
        </div>
    </div>
</section>	
<?php
require_once './footer.php';

