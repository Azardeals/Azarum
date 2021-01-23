<?php
require_once '../application-top.php';
require_once '../includes/login-functions.php';
/* Remember Me functionality for the next time login starts */
if (doCookieMerchantLogin() === true) {
    redirectUser('company-deals.php');
}
/* Remember Me functionality for the next time login ends */
$frm = new Form('frmLogin');
$frm->setAction('?');
$frm->setExtra('class="web_form"');
$frm->setJsErrorDisplay('afterfield');
$frm->addEmailField(t_lang('M_FRM_EMAIL_ADDRESS'), 'merchant_email', '', 'merchant_email', '')->requirements()->setRequired();
$frm->addPasswordField(t_lang('M_FRM_PASSWORD'), 'merchant_password', '', 'merchant_password', '')->requirements()->setRequired();
$frm->addCheckBox(t_lang('M_TXT_REMEMBER_ME'), 'remember_me', '1', '', '');
$frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_LOGIN'));
if (isset($_POST['merchant_email'])) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $post = getPostedData();
        $error = '';
        $password = md5($post['merchant_password']);
        if (loginCompanyUser($post['merchant_email'], $password, $error)) {
            if (isset($post['remember_me'])) {
                setMerchantLoginCookie($post['merchant_email'], $password);
            }
            if (isset($_SESSION['merchant_login_page'])) {
                $url = $_SESSION['merchant_login_page'];
                unset($_SESSION['merchant_login_page']);
                redirectUser($url);
            }
            redirectUser(CONF_WEBROOT_URL . 'merchant/company-deals.php');
        } else {
            $msg->addError($error);
            $frm->fill(array('email' => $post['merchant_email']));
        }
    }
}
?>
<!Doctype html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
        <?php
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
            <div class="panels">
                <div class="innerpanel">
                    <div class="right">
                        <div class="formcontainer">
                            <?php echo $frm->getFormTag(); ?>
                            <div class="field_control fieldicon user">
                                <label class="field_label"><?php echo t_lang('M_FRM_EMAIL_ADDRESS'); ?> <span class="mandatory">*</span></label>
                                <div class="field_cover">
                                    <?php echo $frm->getFieldHTML('merchant_email'); ?>
                                </div>
                            </div>
                            <div class="field_control fieldicon key">
                                <label class="field_label"><?php echo t_lang('M_FRM_PASSWORD'); ?> <span class="mandatory">*</span></label>
                                <div class="field_cover">
                                    <?php echo $frm->getFieldHTML('merchant_password'); ?>
                                </div>
                            </div>
                            <div class="field_control">
                                <label class="checkbox leftlabel"><?php echo $frm->getFieldHtml('remember_me'); ?><i class="input-helper"></i><?php echo t_lang('M_TXT_REMEMBER_ME'); ?></label>
                                <a id="moveleft" href="forgot-password.php" class="linkright linkslide"><?php echo t_lang('M_TXT_FORGOT_PASSWWORD'); ?></a>
                            </div>
                            <span class="circlebutton"><?php echo $frm->getFieldHTML('btn_submit'); ?> </span>
                            </form>
                            <?php echo $frm->getExternaljs(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>
