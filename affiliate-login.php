<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
$verification_status = (int) $_GET['s'];
if ($verification_status == 1) {
    $msg->addMsg(t_lang('M_MSG_TOKKEN_EXPIRED'));
}
/* define configuration variables */
$rs1 = $db->query("select * from tbl_extra_values");
while ($row1 = $db->fetch($rs1)) {
    define(strtoupper($row1['extra_conf_name']), $row1['extra_conf_val']);
}
/* end configuration variables */
if (isAffiliateUserLogged()) {
    redirectUser(CONF_WEBROOT_URL);
}
if (isUserLogged()) {
    redirectUser(CONF_WEBROOT_URL);
}
$frmLogin = getMBSFormByIdentifier('frmLogin');
$fld = $frmLogin->getField('keep_logged');
$fld->html_after_field = '<i class="input-helper"></i>' . t_lang('M_TXT_KEEP_ME_LOGGED_IN') . ' ';
$fld = $frmLogin->getField('btn_login');
$fld->value = t_lang('M_TXT_SIGN_IN');
$url = CONF_WEBROOT_URL . 'affiliate-forgot-password.php';
$fld->html_after_field = '&nbsp;<a href="' . friendlyUrl($url) . '" class="linknormal right">' . t_lang('M_TXT_FORGOT_PASSWWORD') . '</a>';
updateFormLang($frmLogin);
if (isset($_POST['email'])) {
    $post = getPostedData();
    $error = '';
    if (loginAffiliateUser($post['email'], md5($post['password']), $error)) {
        if ($post['keep_logged'] == 1) {
            setcookie('au', $post['email'], time() + 3600 * 24 * 30, '/');
            setcookie('ap', crypt($post['password'], 'JAO8maIFyojQvvDbrnHP9iEHQAhK4Cjl7mr33CYxf2CVo6JpNxhPwrR07zYiMW1E'), time() + 3600 * 24 * 30, '/');
        }
        redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'affiliate-account.php'));
    } else {
        $msg->addError($error);
        redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'affiliate-login.php'));
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
                    <li class="current"><a href="javascript:void(0);" onclick="showLoginForm(this);"><?php echo t_lang('M_TXT_AFFILIATE_LOGIN'); ?></a></li>
                </ul>
                <div id="loginform">
                    <div class="formwrap">
                        <?php echo $frmLogin->getFormTag(); ?> 
                        <table class="formwrap__table">
                            <tbody>
                                <tr><td><?php echo $frmLogin->getFieldHTML('email'); ?> </td></tr>
                                <tr><td><?php echo $frmLogin->getFieldHTML('password'); ?> </td></tr>
                                <tr><td><label class="checkbox"><?php echo $frmLogin->getFieldHTML('keep_logged'); ?> </label></td></tr>
                                <tr><td><?php echo $frmLogin->getFieldHTML('btn_login'); ?> </td></tr>
                            </tbody>
                        </table> 
                        <?php echo $frmLogin->getExternalJS(); ?>
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
<?php
require_once './footer.php';
