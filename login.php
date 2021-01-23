<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
require_once './site-classes/user-info.cls.php';
require_once './site-classes/facebook.php';
require_once './site-classes/apple.php';
if (isUserLogged()) {
    redirectUser(CONF_WEBROOT_URL);
}
if (isCompanyUserLogged()) {
    redirectUser(CONF_WEBROOT_URL . 'merchant/merchant-account.php');
}
if (isAffiliateUserLogged()) {
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'affiliate-account.php'));
}
if (CONF_FACEBOOK_API_KEY != "") {
    $fb = new facebook();
}
if (CONF_APPLE_SERVICE_KEY != "") {
    $apple = new apple();
}
$verification_status = (int) $_GET['s'];
if (!isUserLogged()) {
    if ($_SERVER['REQUEST_METHOD'] != 'POST' && $_SERVER['HTTP_REFERER'] != 'http://' . $_SERVER['SERVER_NAME'] . friendlyUrl(CONF_WEBROOT_URL . 'login.php')) {
        $_SESSION['login_other_page'] = $_SERVER['HTTP_REFERER'];
    }
}
$frm = getUserRegisterationForm();
$fld = $frm->getField('user_email');
$fld->extra = "autocomplete='off' placeholder=" . t_lang('M_TXT_EMAIL_ADDRESS');
$fld = $frm->getField('agree_terms');
$fld->setRequiredStarPosition('none');
$urlTerm = CONF_WEBROOT_URL . 'terms.php';
$urlPrivacy = CONF_WEBROOT_URL . 'privacy.php';
$fld->html_after_field = '<i class="input-helper"></i><span class="text"> ' . t_lang('M_TXT_BY_REGISTER_YOU_AGREE') . '
<a href="' . friendlyUrl($urlTerm) . '" target="_blank">' . t_lang('M_TXT_TERMS_AND_CONDITIONS') . '</a> ' . t_lang('M_TXT_AND') . ' <a href="' . friendlyUrl($urlPrivacy) . '" target="_blank">' . t_lang('M_TXT_PRIVACY_POLICY') . '</a>. </span>';
$fld->extra = 'title="' . t_lang('M_FRM_CHECKBOX_TERMS') . '"';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
    $post = getPostedData();
    $error = '';
    if (registerNewUser($frm, $post, $error)) {
        $msg->addMsg(t_lang('M_TXT_EMAIL_VERIFICATION'));
        redirectUser(CONF_WEBROOT_URL);
    } else {
        $msg->addError($error);
        $frm->fill($post);
    }
}
/* for login */
$frmLogin = getMBSFormByIdentifier('frmLogin');
define('CONF_FORM_REQUIRED_STAR_WITH', '');
//$frmLogin->captionInSameCell(true);
$frmLogin->setRequiredStarWith('field');
$frmLogin->setRequiredStarPosition('after');
$fld = $frmLogin->getField('email');
$fld->merge_caption = true;
$fld = $frmLogin->getField('keep_logged');
$fld->html_after_field = '<i class="input-helper"></i> ' . t_lang('M_TXT_KEEP_ME_LOGGED_IN') . ' ';
$fld = $frmLogin->getField('pass_link');
$url = CONF_WEBROOT_URL . 'forgot-password.php';
$fld = $frmLogin->getField('btn_login');
$fld->value = t_lang('M_TXT_LOGIN');
$fld->html_after_field = '&nbsp;<a href="' . friendlyUrl($url) . '" class="linknormal right">' . t_lang('M_TXT_FORGOT_PASSWWORD') . '</a>';
updateFormLang($frmLogin);
if (isset($_POST['email'])) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $post = getPostedData();
        $error = '';
        if (loginUser($post['email'], md5($post['password']), $error)) {
            if ($post['keep_logged'] == 1) {
                setcookie('u', $post['email'], time() + 3600 * 24 * 30, '/');
                setcookie('p', crypt(md5($post['password']), 'JAO8maIFyojQvvDbrnHP9iEHQAhK4Cjl7mr33CYxf2CVo6JpNxhPwrR07zYiMW1E'), time() + 3600 * 24 * 30, '/');
            }
            /* CASE WHEN USER COME FOR BUY AND REDIRECT USER TO ITS SELECTED CITY */
            if ($_SESSION['cart']['deal_id'] == "") {
                selectCity(intval($_SESSION['logged_user']['user_city']));
            }
            /* CASE WHEN USER COME FOR BUY AND REDIRECT USER TO ITS SELECTED CITY */
            if (isset($_SESSION['login_page'])) {
                $url = $_SESSION['login_page'];
                unset($_SESSION['login_page']);
                $cart = new Cart();
                if ($cart->isEmpty() == false) {
                    redirectUser($url);
                }
            }
            if (isset($_SESSION['login_other_page'])) {
                $otherUrl = $_SESSION['login_other_page'];
                unset($_SESSION['login_other_page']);
                if (($otherUrl != "")) {
                    $find_url = 'reset-password.php';
                    $pos = strpos($otherUrl, $find_url);
                    if ($pos === false) {
                        redirectUser($otherUrl);
                    }
                }
            }
            redirectUser(CONF_WEBROOT_URL);
        } else {
            $msg->addError($error);
            $frmLogin->fill(array('email' => $post['email']));
        }
    }
}
/* for login */
require_once './header.php';
?>
<div id="fb-root"></div>  
<script type="text/javascript">
    txtreload = "<?php echo addslashes(t_lang('M_TXT_PLEASE_RELOAD_PAGE_AND_TRY_AGAIN')); ?>";
    txtoops = "<?php echo addslashes(t_lang('M_TXT_INTERNAL_ERROR')); ?>";
    txtemailfail = "<?php echo addslashes(t_lang('M_MSG_EMAIL_SENDING_FAILED')); ?>";
    txtemailsent = "<?php echo addslashes(t_lang('M_TXT_MAIL_SENT')); ?>";
</script>
<?php
if (isset($verification_status) && $verification_status > 0) {
    if ($verification_status == 2) {
        $eMsg = t_lang('M_MSG_VERIFICATION_FAILED');
    }
    if ($verification_status == 1) {
        $eMsg = t_lang('M_MSG_ALREADY_VERIFIED');
    }
    if ($verification_status == 3) {
        $eMsg = t_lang('M_MSG_TOKKEN_EXPIRED');
    }
    $msg->addMsg($eMsg);
}
?>
<!--bodyContainer start here-->
<section class="sectionfull">
    <div class="sectionfull__centered">
        <div class="sectiontable">
            <aside class="sectiontable__leftcell">
                <ul class="tabs__dual clearfix">
                    <li class="current"><a href="javascript:void(0);" onclick="showLoginForm(this);"><?php echo t_lang('M_TXT_SIGN_IN'); ?></a></li>
                    <li><a href="javascript:void(0);" id="register" onclick="showRegisterationForm(this);"><?php echo t_lang('M_TXT_SIGN_UP'); ?></a></li>
                </ul>
                <div id="loginform">
                    <div class="formwrap">
                        <?php echo $frmLogin->getFormTag(); ?> 
                        <table class="formwrap__table">
                            <tbody><tr>
                                    <td><?php echo $frmLogin->getFieldHTML('email'); ?> </td>
                                </tr>
                                <tr>
                                    <td><?php echo $frmLogin->getFieldHTML('password'); ?> </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkbox">
                                            <?php echo $frmLogin->getFieldHTML('keep_logged'); ?> 
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo $frmLogin->getFieldHTML('btn_login'); ?> </td>
                                </tr>
                            </tbody></table> 
                        <?php echo $frmLogin->getExternalJS(); ?>
                        </form>
                    </div>
                    <?php if (CONF_FACEBOOK_API_KEY != "" || CONF_APPLE_SERVICE_KEY != "") { ?>
                        <span class="vertical_devider"></span>
                        <div class="or-divider"><span class="or">OR</span></div>
                        <div class="group group--social group--social-onehalf">
                            <?php if (CONF_FACEBOOK_API_KEY != "") { ?>
                                <a href="<?php echo $fb->index(); ?>" onclick="" class="btn themebtn themebtn--large btn--social-fb"><i class="icon ion-social-facebook1"></i><?php echo t_lang('M_TXT_FACEBOOK'); ?></a>
                            <?php } if (CONF_APPLE_SERVICE_KEY != "") { ?>		
                                <a href="<?php echo $apple->login(); ?>" onclick="" class="btn themebtn themebtn--large btn--social-gp"><i class="icon ion-social-apple1"></i><?php echo t_lang('M_TXT_APPLE'); ?></a>
                            <?php } ?>	
                        </div>
                    <?php } ?>	
                </div>
                <div id="registerationform" style="display:none">
                    <div class="formwrap">
                        <?php echo $frm->getFormTag(); ?> 
                        <table class="formwrap__table">
                            <tbody><tr>
                                    <td> <?php echo $frm->getFieldHTML('user_name'); ?> </td>
                                </tr>
                                <tr>
                                    <td> <?php echo $frm->getFieldHTML('user_lname'); ?> </td>
                                </tr>
                                <tr>
                                    <td><?php echo $frm->getFieldHTML('user_email'); ?> </td>
                                </tr>
                                <tr>
                                    <td><?php echo $frm->getFieldHTML('user_password'); ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo $frm->getFieldHTML('password1'); ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo $frm->getFieldHTML('user_city'); ?></td>
                                </tr>
                                <tr>
                                    <td><label class="checkbox">
                                            <?php echo $frm->getFieldHTML('agree_terms'); ?>
                                        </label></td>
                                </tr>
                                <tr>
                                    <td><?php echo $frm->getFieldHTML('btn_submit'); ?></td>
                                </tr>
                            </tbody></table>
                        <?php echo $frm->getExternalJS(); ?>
                        </form>
                    </div>     
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
<!--bodyContainer end here-->
<script type="text/javascript">
    $(document).ready(function () {
<?php if (isset($_REQUEST['type']) && ($_REQUEST['type'] == "register")) { ?>
            $('#register').trigger('click');
<?php } else { ?>
            $('input[name="user_email"]').attr('onchange', '');
<?php } ?>
    });
    function singupFormSubmit(frm, v)
    {
        if ($('#frmRegistration').hasClass('submitted')) {
            return false;
        }
        v.validate();
        if (!v.isValid()) {
            return false;
        }
        $('#frmRegistration').addClass('submitted');
    }
</script>
<?php require_once './footer.php'; ?>