<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
require_once './site-classes/user-info.cls.php';
if (!isUserLogged()) {
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'login.php'));
}
$frm = getMBSFormByIdentifier('frmMyProfile');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    if ($frm->validate($post)) {
        $user = new userInfo();
        if (!$user->userToCategory($_SESSION['logged_user']['user_id'], $post['categories'])) {
            $msg->addError('User execution error! ' . $user->getError());
            require_once './msgdie.php';
        } else {
            $msg->addMsg('Preferences updated.');
            redirectUser();
        }
    } else {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error) {
            $msg->addError($error);
        }
        $frm->fill($post);
    }
}
$rs = $db->query("select udc_cat_id, udc_cat_id as catid from tbl_user_to_deal_cat where udc_user_id=" . $_SESSION['logged_user']['user_id']);
$arr_cats = $db->fetch_all_assoc($rs);
$frm->getField('categories')->value = $arr_cats;
require_once './header.php';
?>
<!--body start here-->
<?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
    <div  id="msg">
        <div class="system-notice notice"><a class="close" href="javascript:void(0);" onclick="$(this).closest('#msg').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a><p id="message"><?php echo $msg->display(); ?> </p></div>
    </div>
<?php } ?>	
<div class="deal-cont">
    <div class="introWrapper">
        <span class="fl"><img alt="" src="<?php echo CONF_WEBROOT_URL; ?>images/dblBg_left.jpg"></span>
        <div class="introWrap">
            <h2><?php echo t_lang('M_TXT_MY_VOUCHERS'); ?></h2> 
            <div class="accountLinks">
                <ul>
                    <li><a  title="<?php echo t_lang('M_TXT_MY_ACCOUNT'); ?>" href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'my-account.php'); ?>"><span><?php echo t_lang('M_TXT_MY_ACCOUNT'); ?></span></a></li>
                    <li><a class="activeTab" title="<?php echo t_lang('M_TXT_MY_VOUCHERS'); ?>" href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'my-deals.php'); ?>"><span><?php echo t_lang('M_TXT_MY_VOUCHERS'); ?></span></a></li>
                    <li><a  title="<?php echo t_lang('M_TXT_PURCHASE_HISTORY'); ?>" href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'my-wallet.php'); ?>"><span><?php echo t_lang('M_TXT_PURCHASE_HISTORY'); ?></span></a></li>
                    <li><a title="<?php echo t_lang('M_TXT_DEAL_BUCKS'); ?>" href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'refer-friends.php'); ?>"><span><?php echo t_lang('M_TXT_DEAL_BUCKS'); ?></span></a></li>
                    <li class="nobackground"><a title="<?php echo t_lang('M_TXT_SUBSCRIPTIONS'); ?>" href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'my-subscriptions.php'); ?>"><span><?php echo t_lang('M_TXT_SUBSCRIPTIONS'); ?></span></a></li>
                </ul>
            </div>
        </div>
        <span class="fl"><img alt="" src="<?php echo CONF_WEBROOT_URL; ?>images/dblBg_right.jpg"></span>
    </div>
    <div class="questions">
        <div class="subscription-area">
            <div class="subscription-top"></div>
            <div style="padding-top:5px;" class="subscription-mid">
                <div class="ribbon"> <span class="rb-mid"><?php echo t_lang('M_TXT_MY_FAVOURITE'); ?></span> <span class="rb-rgt"></span> </div>
                <div class="my-email">
                    <div class="purchase_wrapper" style="color:#5E5D5D;">
                        <?php
                        echo $frm->getFormHtml();
                        ?>
                        <span class="fl"><img alt="" src="<?php echo CONF_WEBROOT_URL; ?>images/blackHead_bot.jpg"></span>  
                    </div>
                </div>
            </div>
            <div class="subscription-btm"></div>
        </div>
    </div>
</div>
<div class="deal-cont-btm2"></div> 
<?php require_once './footer.php'; ?>
 