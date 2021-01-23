<?php
/* error_reporting(E_ALL);
  ini_set('display_errors',1); */
require_once __DIR__ . '/../application-top.php';
require_once __DIR__ . '/../includes/login-functions.php';
/* Remember Me functionality for the next time login starts */
if (doCookieRepresentativeLogin() === true) {
    redirectUser('my-account.php');
}
/* Remember Me functionality for the next time login ends */
$frm = new Form('frmLogin');
$frm->setAction('?');
$frm->setExtra('class="web_form"');
$frm->setJsErrorDisplay('afterfield');
$frm->setRequiredStarWith('caption');
//$frm->setLeftColumnProperties('width="25%" align="right" class="label"');
//$frm->setTableProperties('width="100%" border="0" cellspacing="20" cellpadding="0" class="login_tbl form-website"');
//$frm->captionInSameCell(false);
$frm->addEmailField(t_lang('M_FRM_EMAIL_ADDRESS'), 'rep_email', 'representative@dummyid.com', 'rep_email', 'class="input-normal"')->requirements()->setRequired();
$frm->addPasswordField(t_lang('M_FRM_PASSWORD'), 'rep_password', 'representative', 'rep_password', 'class="input-normal"')->requirements()->setRequired();
$frm->addCheckBox(t_lang('M_TXT_REMEMBER_ME'), 'remember_me', '1', '', '');
$frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_LOGIN'), '', 'class="login_btn"');
if (isset($_POST['rep_email'])) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $post = getPostedData();
        $error = '';
        $password = md5($post['rep_password']);
        if (loginRepresentativeUser($post['rep_email'], $password, $error)) {
            if (isset($post['remember_me'])) {
                setRepresentativeLoginCookie($post['rep_email'], $password);
            }
            if (isset($_SESSION['rep_login_page'])) {
                $url = $_SESSION['rep_login_page'];
                unset($_SESSION['rep_login_page']);
                redirectUser($url);
            }
            redirectUser(CONF_WEBROOT_URL . 'representative/my-account.php');
        } else {
            $msg->addError($error);
            $frm->fill(array('email' => $post['rep_email']));
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
                    <figure class="logo"><img src="<?php echo LOGO_URL . CONF_ADMIN_PANEL_LOGO ?>" alt=""></figure>
                </div>
                <div class="layerRight" style="background-image:url(images/dealsbg.jpg); background-repeat:no-repeat;">
                    <figure class="logo"><img src="<?php echo LOGO_URL . CONF_ADMIN_PANEL_LOGO ?>" alt=""></figure>
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
                                    <?php echo $frm->getFieldHTML('rep_email'); ?>
                                </div>
                            </div>
                            <div class="field_control fieldicon key">
                                <label class="field_label"><?php echo t_lang('M_FRM_PASSWORD'); ?> <span class="mandatory">*</span></label>
                                <div class="field_cover">
                                    <?php echo $frm->getFieldHTML('rep_password'); ?>
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
