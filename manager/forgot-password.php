<?php
require_once '../application-top.php';
require_once '../securimage/securimage.php';
require_once '../includes/navigation-functions.php';
$frm = new Form('frmForgotPassword');
$frm->setAction('?');
$frm->setExtra('class="web_form"');
$frm->setJsErrorDisplay('afterfield');
$frm->addEmailField('', 'admin_email', '', 'admin_email', '')->requirements()->setRequired(true);
$secureimage_fld = $frm->addRequiredField('', 'security_code', '', '', 'class="input-normal"');
$frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_LOGIN'));
if (isset($_POST['admin_email'])) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $img = new Securimage();
        $post = getPostedData();
        $error = '';
        if ($post['admin_email'] == '' || $post['security_code'] == '') {
            $msg->addError(t_lang('M_FRM_EMAIL_ADDRESS') . ' ' . t_lang('M_TXT_AND') . ' ' . t_lang('M_FRM_SECURITY_CODE') . ' ' . t_lang('M_JS_IS_MANDATORY'));
            redirectUser('forgot-password.php');
        }
        $selectEmail = new SearchBase('tbl_admin', 'a');
        $selectEmail->addCondition('admin_email', '=', $post['admin_email']);
        $selectEmail->getQuery();
        $email_listing = $selectEmail->getResultSet();
        $RowCheck = $selectEmail->recordCount($email_listing);
        if ($RowCheck > 0) {
            if (!$img->check($post['security_code'])) {
                $msg->addError(t_lang('M_TXT_INCORRECT_SECURITY_CODE'));
                redirectUser('forgot-password.php');
            } else {
                $row = $db->fetch($email_listing);
                $resultReset = $db->query("select * from tbl_admin_password_resets_requests where aprr_admin_id=" . intval($row['admin_id']));
                $row_reset = $db->fetch($resultReset);
                $admin_id = $row_reset['aprr_admin_id'];
                $email = $post['admin_email'];
                $code = mt_rand(0, 9999999999);
                if ($db->total_records($resultReset) == 0) {
                    $admin_id = $row['admin_id'];
                    $db->query("INSERT INTO tbl_admin_password_resets_requests VALUES ($admin_id, '$code', now());");
                    $result = 1;
                } else {
                    $resultReset1 = $db->query("select * from tbl_admin_password_resets_requests where  aprr_expiry < (NOW() - INTERVAL 1 DAY)
		 and aprr_admin_id=" . intval($row['admin_id']));
                    $row_reset1 = $db->fetch($resultReset1);
                    if ($db->total_records($resultReset1) == 1) {
                        $db->query("UPDATE tbl_admin_password_resets_requests SET aprr_expiry =  NOW() , aprr_tocken =" . $db->quoteVariable($code) . " WHERE  aprr_admin_id=" . intval($row['admin_id']));
                        $result = 1;
                    } else {
                        $result = 0;
                        $msg->addError(t_lang('M_TXT_FORGOT_PASSWORD_ERROR_MESSAGE'));
                    }
                }
                if ($result == 1) {
                    $pass = $row['admin_password'];
                    $username = $row['admin_username'];
                    $name = $row['admin_username'];
                    #forgotPassword($email,$pass,$username);
                    ########## Email #####################
                    $rs1 = $db->query("select * from tbl_email_templates where tpl_id=4");
                    $row_tpl = $db->fetch($rs1);
                    $verification_url = 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'manager/reset-password.php?code=' . $admin_id . '_' . $code;
                    $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
                    $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
                    $arr_replacements = array(
                        'xxuser_namexx' => $username,
                        'xxuser_emailxx' => $username,
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
                    $emailMsg = '<p style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; line-height: 20px; color: rgb(75, 75, 74);  padding-left: 10px;float:left;">' . $message . '</p>';
                    sendMail($email, $subject, emailTemplateSuccess($message), $headers);
                    ##############################################
                    $msg->addMsg(t_lang('M_TXT_PASSWORD_SENT'));
                    redirectUser('forgot-password.php');
                }
            }
        } else {
            $msg->addError(t_lang('M_TXT_EMAIL_NOT_FOUND'));
            redirectUser('forgot-password.php');
        }
    }
}
?>
<!Doctype html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
        <?php
        $arr_common_css[] = 'manager/page-css/login.css';
        $arr_common_css[] = 'manager/css/system_messages.css';
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
            </div>
            <div class="panels pageforgot">
                <div class="innerpanel">
                    <div class="left">
                        <div class="formcontainer">
                            <h5><?php echo t_lang('M_TXT_FORGOT_PASSWWORD'); ?> </h5>
                            <?php echo $frm->getFormTag(); ?>
                            <div class="field_control fieldicon mail">
                                <label class="field_label"><?php echo t_lang('M_FRM_EMAIL_ADDRESS'); ?> <span class="mandatory">*</span></label>
                                <div class="field_cover">
                                    <?php echo $frm->getFieldHTML('admin_email'); ?>
                                </div>
                            </div>
                            <div class="field_control fieldicon secure">
                                <label class="field_label"><?php echo t_lang('M_FRM_SECURITY_CODE'); ?> <span class="mandatory">*</span></label>
                                <div class="field_cover">
                                    <?php
                                    echo $secureimage_fld->getHTML();
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
