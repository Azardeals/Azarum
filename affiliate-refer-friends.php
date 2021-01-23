<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
if (!isAffiliateUserLogged()) {
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'login.php'));
}
require_once './header.php';
$rs = $db->query("select sum(wh_amount) as  wallet_amount  from tbl_affiliate_wallet_history where wh_affiliate_id=" . $_SESSION['logged_user']['affiliate_id']);
$row = $db->fetch($rs);
$wallet_amount = $row['wallet_amount'];
?>
<?php if ($_SERVER['REQUEST_METHOD'] == 'POST') { ?>
    <script type="text/javascript">
        referFriendInfoSubmit('<?php echo $_POST['email_subject'] ?>', '<?php echo $_POST['recipients'] ?>', '<?php echo $_POST['email_message'] ?>');
    </script>
    <?php
}
if (isset($verification_status) && $verification_status > 0) {
    if ($verification_status == 1) {
        $msg->addMsg(t_lang('M_TXT_UPDATE_YOUR_PASSWORD'));
    }
    ?>
<?php } ?>
<section class="pagebar">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-7 col-sm-7">
                <h3><?php echo t_lang('M_TXT_DEAL_BUCKS'); ?></h3>
                <ul class="breadcrumb">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL); ?>"><?php echo t_lang('M_TXT_HOME'); ?></a></li>
                    <li><?php echo t_lang('M_TXT_DEAL_BUCKS') ?></li>
                </ul>
            </aside>
        </div>
    </div>
</section> 
<?php require_once './left-panel-links.php'; ?> 
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <div class="col-md-12">
                <div class="section__row">
                    <h2 class="section__subtitle hide__mobile hide__tab hide__ipad"><?php echo t_lang('M_TXT_DEAL_BUCKS') ?></h2>
                    <div class="section__row-border">
                        <div class="info__amount">
                            <span class="info__largetxt"><?php echo amount(($wallet_amount == '') ? '0.00' : $wallet_amount); ?></span>
                            <p><?php echo t_lang('M_TXT_MY_DEAL_BUCKS') ?></p>
                        </div>
                        <div class="table__info">
                            <table>
                                <tr>
                                    <td>
                                        <h6><?php echo t_lang('M_TXT_WHAT_DEAL_BUCKS_HEADING'); ?></h6>
                                        <p><?php echo t_lang('M_TXT_WHAT_DEAL_BUCKS_ANSWER'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h6><?php echo t_lang('M_TXT_WANT_EARN_MORE_DEAL_BUCKS_HEADING'); ?></h6>
                                        <p> <a href="javascript:void(0);" onclick="referFriendInfo();"><?php echo t_lang('M_TXT_INVITE_YOUR_FRINDS'); ?></a>&nbsp;<?php echo t_lang('M_TXT_WANT_EARN_MORE_DEAL_BUCKS_ANSWER'); ?></br> </br><strong> <?php echo t_lang('M_TXT_NOTE'); ?></strong> : <?php echo t_lang('M_TXT_GET_REFERAL_COMISSION_NOTE'); ?> 
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </div>   
                    </div>
                </div> 
                <div class="section__row">
                    <h2 class="section__subtitle"><?php echo t_lang('M_TXT_REFERRER_HEAD'); ?></h2>
                    <div class="cover__grey">  
                        <div class="form__small siteForm">
                            <ul>
                                <li><input type="text" value="http://<?php echo $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '?code=' . $_SESSION['logged_user']['affiliate_code'] . '&affid=' . $_SESSION['logged_user']['affiliate_id']; ?>"></li>
                                <li><input type="button" onclick="referFriendInfo();" value="<?php echo t_lang('M_TXT_INVITE_YOUR_FRINDS'); ?>">
                                </li>
                            </ul>
                        </div>
                    </div>      
                </div>    
            </div>    
        </div>
    </div>    
</section>	
<?php
require_once './footer.php';
