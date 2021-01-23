<?php
require_once './application-top.php';
if (isUserLogged()) {
    ?>
    <li class="dropdown dropdown--trigger-nav">
        <a class="hide__mobile" href="javascript:void(0)"><?php echo t_lang('M_TXT_HELLO'); ?>, <?php echo htmlentities(substr($_SESSION['logged_user']['user_name'], 0, 12)); ?></a>
        <div class="dropsection dropsection--org dropdown--target-nav">
            <div class="dropsection__head aligncenter">
                <span class="iconavtar"><i class="icon ion-ios-contact"></i></span>
                <a  class="link"  href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'my-account.php'); ?>"><?php echo t_lang('M_TXT_MY_ACCOUNT'); ?></a>
                <p><?php echo $_SESSION['logged_user']['user_email']; ?></p>
            </div>
            <div class="dropsection__body">
                <ul class="linksvertical links__for-desktop">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'my-deals.php'); ?>"><i class="voucher_icon"></i><?php echo t_lang('M_TXT_MY_VOUCHERS'); ?></a></li>
                    <li> <a  href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'purchase-history.php'); ?>"><?php echo t_lang('M_TXT_PURCHASE_HISTORY') ?></a></li>
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'logout.php'); ?>"><i class=""></i><?php echo t_lang('M_TXT_SIGN_OUT'); ?></a></li>
                </ul>
                <ul class="linksvertical links__for-responsive">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'my-account.php'); ?>"><?php echo t_lang('M_TXT_MY_ACCOUNT'); ?></a></li>
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'my-deals.php'); ?>"><i class="voucher_icon"></i><?php echo t_lang('M_TXT_MY_VOUCHERS'); ?></a></li>
                    <li> <a  href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'purchase-history.php'); ?>"><?php echo t_lang('M_TXT_PURCHASE_HISTORY') ?></a></li>
                    <li><a  href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'refer-friends.php'); ?>"><?php echo t_lang('M_TXT_DEAL_BUCKS'); ?></a></li>
                    <li><a  href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'my-subscriptions.php'); ?>"><?php echo t_lang('M_TXT_SUBSCRIPTIONS'); ?></a></li>
                    <li> <a  href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'my-favorites.php'); ?>"><?php echo t_lang('M_TXT_FAVOURITE_MERCHANT'); ?></a></li>
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'my-favorites-deals.php'); ?>"><?php echo t_lang('M_TXT_FAVOURITE_DEALS_PRODUCTS'); ?></a></li>
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'logout.php'); ?>"><i class=""></i><?php echo t_lang('M_TXT_SIGN_OUT'); ?></a></li>
                </ul>
            </div>    
        </div>
    </li>
<?php } else if (isAffiliateUserLogged()) { ?>
    <li class="dropdown dropdown--trigger-nav">
        <a class="hide__mobile" href="javascript:void(0)"><?php echo t_lang('M_TXT_HELLO'); ?>,<?php echo $_SESSION['logged_user']['affiliate_fname' . $_SESSION['lang_fld_prefix']]; ?></a>
        <div class="dropsection dropsection--org dropdown--target-nav">
            <div class="dropsection__head aligncenter">
                <span class="iconavtar"><i class="icon ion-ios-contact"></i></span>
                <a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'affiliate-account.php'); ?>"><?php echo t_lang('M_TXT_MY_ACCOUNT'); ?></a>
                <p><?php echo $_SESSION['logged_user']['user_email']; ?></p>
            </div>
            <div class="dropsection__body">
                <ul class="linksvertical links__for-desktop">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'affiliate-account.php'); ?>"><i class="voucher_icon"></i><?php echo t_lang('M_TXT_MY_ACCOUNT'); ?></a></li>
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'affiliate-refer-friends.php'); ?>"><?php echo t_lang('M_TXT_DEAL_BUCKS'); ?></a></li>
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'logout.php'); ?>"><i class=""></i><?php echo t_lang('M_TXT_SIGN_OUT'); ?></a></li>
                </ul>
                <ul class="linksvertical links__for-responsive">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'affiliate-account.php'); ?>"><?php echo t_lang('M_TXT_MY_ACCOUNT'); ?></a></li>
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'affiliate-refer-friends.php'); ?>"><i class="voucher_icon"></i><?php echo t_lang('M_TXT_DEAL_BUCKS'); ?></a></li>
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'affiliate-report.php'); ?>"><i class="voucher_icon"></i><?php echo t_lang('M_TXT_AFFILIATE_REPORTS'); ?> </a></li>
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'affiliate-list.php'); ?>"><i class="voucher_icon"></i><?php echo t_lang('M_TXT_DEAL_PRODUCT_WISE_REPORTS'); ?> </a></li>
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'affiliate-balance.php'); ?>"><i class="voucher_icon"></i><?php echo t_lang('M_TXT_AFFILIATE_BALANCE'); ?> </a></li>
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'logout.php'); ?>"><i class=""></i><?php echo t_lang('M_TXT_SIGN_OUT'); ?></a></li>
                </ul>
            </div>    
        </div>
    </li>
<?php } else { ?>
    <li class="dropdown dropdown--trigger-nav">
        <a class="hide__mobile" href="javascript:void(0)"><?php echo t_lang('M_TXT_HELLO'); ?>, <?php echo t_lang('M_TXT_SIGN_IN'); ?></a>
        <div class="dropsection dropsection--org  dropdown--target-nav">
            <div class="dropsection__head aligncenter">
                <span class="iconavtar"><i class="icon ion-ios-contact"></i></span>
                <a class="link"  href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'login.php'); ?>"><?php echo t_lang('M_TXT_SIGN_IN'); ?></a>
                <p><?php echo t_lang('M_TXT_NEW_USER'); ?>? <a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'login.php?type=register'); ?>"><?php echo t_lang('M_TXT_REGISTER'); ?></a></p>
            </div>
            <div class="dropsection__body">
                <ul class="linksvertical">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'my-account.php'); ?>"><?php echo t_lang('M_TXT_MY_ACCOUNT'); ?></a></li>
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'my-deals.php'); ?>"><i class="voucher_icon"></i><?php echo t_lang('M_TXT_MY_VOUCHERS'); ?></a></li>
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'my-favorites-deals.php'); ?>"><?php echo t_lang('M_TXT_MY_FAVOURITE_DEALS'); ?></a></li>
                </ul>
            </div>    
        </div>
    </li>
<?php } ?>