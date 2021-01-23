<?php
require_once '../application-top.php';
require_once '../securimage/securimage.php';
require_once '../includes/navigation-functions.php';
$frm = new Form('frmForgotPassword');
$frm->setAction('?');
$frm->setExtra('class="web_form"');
$frm->setJsErrorDisplay('afterfield');
$frm->addEmailField(t_lang('M_FRM_EMAIL_ADDRESS'), 'user_email', '', 'user_email', '')->requirements()->setRequired();
$secureimage_fld = $frm->addRequiredField(t_lang('M_FRM_SECURITY_CODE'), 'security_code', '', '', 'class="input-normal"');
$frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_LOGIN'));
if (isset($_POST['user_email'])) {
    $img = new Securimage();
    $post = getPostedData();
    $error = '';
    if ($post['user_email'] == '' || $post['security_code'] == '') {
        $msg->addError(t_lang('M_FRM_EMAIL_ADDRESS') . ' ' . t_lang('M_TXT_AND') . ' ' . t_lang('M_FRM_SECURITY_CODE') . ' ' . t_lang('M_JS_IS_MANDATORY'));
        redirectUser('forgot-password.php');
    }
    $selectEmail = new SearchBase('tbl_companies', 'c');
    $selectEmail->addCondition('company_deleted', '=', 0);
    $selectEmail->addCondition('company_active', '=', 1);
    $selectEmail->addCondition('company_email', '=', $post['user_email']);
    $selectEmail->getQuery();
    $email_listing = $selectEmail->getResultSet();
    $RowCheck = $selectEmail->recordCount($email_listing);
    if ($RowCheck > 0) {
        if (!$img->check($_POST['security_code'])) {
            $msg->addError(t_lang('M_TXT_INCORRECT_SECURITY_CODE'));
            redirectUser('forgot-password.php');
        } else {
            $row = $db->fetch($email_listing);
            $resultReset = $db->query("select * from tbl_user_password_resets_requests where uprr_affiliate_id= 0 and uprr_user_id =0 and   uprr_company_id=" . $row['company_id']);
            $row_reset = $db->fetch($resultReset);
            $user_id = 0;
            $affiliate_id = 0;
            $company_id = intval($row['company_id']);
            $rep_id = 0;
            $code = mt_rand(0, 9999999999);
            if ($db->total_records($resultReset) == 0) {
                $db->query("INSERT INTO tbl_user_password_resets_requests VALUES (0, " . $db->quoteVariable($code) . ", now(),$company_id,0,0);");
                $result = 1;
            } else {
                $resultReset1 = $db->query("select * from tbl_user_password_resets_requests where  uprr_expiry < (NOW() - INTERVAL 1 DAY)
				and uprr_company_id =" . intval($row['company_id']));
                $row_reset1 = $db->fetch($resultReset1);
                if ($db->total_records($resultReset1) == 1) {
                    $db->query("UPDATE tbl_user_password_resets_requests SET uprr_expiry =  NOW() , uprr_tocken ='$code' WHERE  uprr_company_id =" . intval($row['company_id']));
                    $result = 1;
                } else {
                    $result = 0;
                    $msg->addError(t_lang('M_TXT_FORGOT_PASSWORD_ERROR_MESSAGE'));
                }
            }
            if ($result == 1) {
                $email = $row['company_email'];
                $pass = $row['company_password'];
                $verification_url = 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'reset-password.php?code=' . $user_id . '_' . $company_id . '_' . $affiliate_id . '_' . $rep_id . '_' . $code;
                $rs = $db->query("select * from tbl_email_templates where tpl_id=4");
                $row_tpl = $db->fetch($rs);
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
                /* $msg->addMsg(t_lang('M_TXT_PASSWORD_SENT')); */
                $msg->addMsg(t_lang('M_TXT_PASSWORD_SENT'));
                redirectUser('forgot-password.php');
            }
        }
    } else {
        /* $msg->addError(t_lang('M_TXT_MERCHANT_EMAIL_NOT_FOUND')); */
        $msg->addError(t_lang('M_TXT_EMAIL_NOT_FOUND'));
        redirectUser('forgot-password.php');
    }
}
?>
<!Doctype html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
        <?php
        $arr_page_css[] = './manager/page-css/login.css';
        $arr_page_css[] = './css/system_messages.css';
        include 'meta.inc.php';
        include 'js-and-css.inc.php';
        ?>
    </head>
    <body class="enterpage">
        <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?>
            <div class="system_message">
                <a class="closeMsg" href="javascript:void(0);" onclick="closediv()"></a>
                <?php echo $msg->display(); ?>
            </div>
        <?php } ?>
        <main id="wrapper">
            <div class="backlayer">
                <?php
                if (CONF_ADMIN_PANEL_LOGO == "") {
                    $src = CONF_WEBROOT_URL . 'images/login_screen_logo.png';
                } else {
                    $src = LOGO_URL . CONF_ADMIN_PANEL_LOGO;
                }
                ?>
                <div class="layerLeft" style="background-image:url(images/dealsbg.jpg); background-repeat:no-repeat;">
                    <figure class="logo"><img alt="" src="<?php echo $src; ?>"></figure>
                </div>
                <div class="layerRight" style="background-image:url(images/dealsbg.jpg); background-repeat:no-repeat;">
                    <figure class="logo"><img alt="" src="<?php echo $src; ?>"></figure>
                </div>
            </div>
            <div class="panels pageforgot">
                <div class="innerpanel">
                    <div class="left">
                        <div class="formcontainer">
                            <h5><?php echo t_lang('M_TXT_FORGOT_PASSWWORD'); ?> </h5>
                            <?php echo $frm->getFormTag(); ?>
                            <div class="field_control fieldicon mail ">
                                <label class="field_label"><?php echo t_lang('M_FRM_EMAIL_ADDRESS'); ?> <span class="mandatory">*</span></label>
                                <div class="field_cover">
                                    <?php echo $frm->getFieldHTML('user_email'); ?>
                                </div>
                            </div>
                            <div class="field_control fieldicon secure ">
                                <label class="field_label"><?php echo t_lang('M_FRM_SECURITY_CODE'); ?> <span class="mandatory">*</span></label>
                                <div class="field_cover">
                                    <?php
                                    echo $secureimage_fld->getHTML();
                                    //$secureimage_fld->html_after_field=' <br/> <br/><img src="'.CONF_WEBROOT_URL.'securimage/securimage_show.php?sid=' . time() . '" id="security_image" style="height:35px;">' . ' <a href="javascript:void(0);" onclick="reloadSecureImage();"><img src="'.CONF_WEBROOT_URL.'securimage/images/reload.png" alt="Reload Image" /></a>';
                                    ?>
                                    <img src="<?php echo CONF_WEBROOT_URL; ?>securimage/securimage_show_forgot_password.php?sid=<?php echo time() ?>" id="security_image" class="captchapic" alt="Load Image">
                                    <a href="javascript:void(0);" onclick="reloadSecureImage();" class="reloadlink"></a>
                                </div>
                            </div>
                            <a id="moveright" href="login.php" class="linkright linkslide"><?php echo t_lang('M_TXT_BACK_TO_LOGIN'); ?></a>
                            <span class="circlebutton"><?php echo $frm->getFieldHTML('btn_submit'); ?></span>
                            </form>
                            <?php echo $frm->getExternaljs(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>
