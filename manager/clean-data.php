<?php
require_once './application-top.php';
require_once '../includes/navigation-functions.php';
checkAdminPermission(0);
require_once './header.php';
global $db;
$Src_frm = new Form('Src_frm', 'Src_frm');
$Src_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$Src_frm->setFieldsPerRow(1);
$Src_frm->captionInSameCell(false);
$Src_frm->addPasswordField(t_lang('M_TXT_ MASTER_PASSWORD'), 'm_password', '', '', '');
$fld = $Src_frm->addSubmitButton('', 'btn_login', t_lang('M_TXT_LOGIN'), '', ' class="medium"');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    if ($post['m_password'] == CONF_DB_NAME) {
        if (!isset($_POST['btn_clear'])) {
            $msg->addMsg('Login Sucessfull');
        }
        if ($post['sales'] == 1) {
            $sales = 'checked';
        } else {
            $sales = '';
        }
        if ($post['reg_users'] == 1) {
            $reg_users = 'checked';
        } else {
            $reg_users = '';
        }
        if ($post['all_deals'] == 1) {
            $all_deals = 'checked';
        } else {
            $all_deals = '';
        }
        if ($post['all_companies'] == 1) {
            $all_companies = 'checked';
        } else {
            $all_companies = '';
        }
        if ($post['subscribers'] == 1) {
            $subscribers = 'checked';
        } else {
            $subscribers = '';
        }
        $frm = new Form('frmClean', 'frmClean');
        $frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
        $frm->setFieldsPerRow(1);
        $frm->captionInSameCell(false);
        $frm->addCheckBox('Clean Sales Data', 'sales', '1', '', $sales);
        $frm->addCheckBox('Clean Registered Users', 'reg_users', '1', '', $reg_users);
        $frm->addCheckBox('Clean All Deals', 'all_deals', '1', '', $all_deals);
        $frm->addCheckBox('Clean All Companies', 'all_companies', '1', '', $all_companies);
        $frm->addCheckBox('Clean Subscribers', 'subscribers', '1', '', $subscribers);
        $frm->addHiddenField('', 'm_password', $post['m_password'], '', '');
        $fld = $frm->addSubmitButton('', 'btn_clear', t_lang('M_TXT_CLEAR_NOW'), '', ' class="medium"');
    } else {
        $msg->addError(t_lang('M_TXT_INVALID_PASSWORD'));
        redirectUser('?');
    }
}
if ($post['m_password'] == CONF_DB_NAME) {
    if (isset($_POST['btn_clear'])) {
        $post = getPostedData();
        if ($post['all_companies'] == 1) {
            $post['all_deals'] = 1;
        }
        if ($post['sales'] == 1 || $post['reg_users'] == 1 || $post['all_deals'] == 1 || $post['all_companies'] == 1 || $post['subscribers'] == 1) {
            if ($post['sales'] == 1) {
                $db->query("truncate  tbl_orders");
                $db->query("truncate  tbl_order_deals");
                $db->query("truncate  tbl_order_transactions");
                $db->query("truncate  tbl_order_transactions_tracking");
                $db->query("truncate  tbl_order_shipping_details");
                $db->query("truncate  tbl_order_option");
                $db->query("truncate  tbl_coupon_mark");
                $db->query("truncate  tbl_charity_history");
                $db->query("truncate  tbl_affiliate_wallet_history");
                $db->query("truncate  tbl_user_wallet_history");
                $db->query("truncate  tbl_company_coupon_purchased");
                $db->query("truncate  tbl_company_wallet_history");
                $db->query("update tbl_users set `user_wallet_amount` = 0 ");
                $db->query("truncate  tbl_affiliate");
                $db->query("truncate  tbl_business_referral");
                $db->query("truncate  tbl_business_page");
                $db->query("truncate  tbl_order_bookings");
                $db->query("truncate  tbl_order_deal_taxes");
            }
            if ($post['all_companies'] == 1) {
                $db->query("truncate  tbl_companies");
                $db->query("truncate  tbl_company_addresses");
                $db->query("truncate  tbl_company_charity");
                $db->query("truncate  tbl_representative");
                $db->query("truncate  tbl_representative_wallet_history");
                $db->query("DELETE FROM `tbl_reviews` WHERE `reviews_deal_company_id` > 0");
                $files = glob(COMPANY_LOGO_PATH . '*.*');
                foreach ($files as $file) {
                    unlink($file);
                }
                $files = glob(CHARITY_IMAGES_PATH . '*.*');
                foreach ($files as $file) {
                    unlink($file);
                }
                $db->query("truncate  tbl_email_notification");
            }
            if ($post['reg_users'] == 1) {
                $db->query("truncate  tbl_users");
                $db->query("truncate  tbl_users_card_detail");
                $db->query("truncate  tbl_users_favorite");
                $db->query("truncate  tbl_users_favorite_sent");
                $db->query("truncate  tbl_user_password_resets_requests");
                $db->query("truncate  tbl_user_to_deal_cat");
                $db->query("truncate  tbl_user_wallet_history");
                $db->query("truncate  tbl_orders");
                $db->query("truncate  tbl_order_deals");
                $db->query("truncate  tbl_order_transactions");
                $db->query("truncate  tbl_coupon_mark");
                $db->query("truncate  tbl_charity_history");
                $db->query("truncate  tbl_referral_affiliate_clicks");
                $db->query("truncate  tbl_affiliate_wallet_history");
                $db->query("truncate  tbl_reviews");
                $db->query("truncate   tbl_referral_history");
                $db->query("truncate   tbl_deal_expire_notification");
                $db->query("truncate   tbl_user_addresses");
                $db->query("truncate   tbl_mailchimp_user_desc");
                $db->query("truncate   tbl_regscheme_offer_log");
                $db->query("truncate   tbl_users_favorite_deals");
                $db->query("truncate   tbl_email_notification");
            }
            if ($post['all_deals'] == 1) {
                $db->query("truncate  tbl_deals");
                $db->query("truncate  tbl_deals_images");
                $db->query("truncate  tbl_deal_address_capacity");
                $db->query("truncate  tbl_deal_discussions");
                $db->query("truncate  tbl_deal_review");
                $db->query("truncate   tbl_deal_expire_notification");
                $db->query("truncate  tbl_deal_to_category");
                $db->query("truncate  tbl_deal_view");
                $db->query("truncate  tbl_options");
                $db->query("truncate  tbl_option_values");
                $db->query("truncate  tbl_orders");
                $db->query("truncate  tbl_order_deals");
                $db->query("truncate  tbl_order_transactions");
                $db->query("truncate  tbl_coupon_mark");
                $db->query("truncate  tbl_charity_history");
                $db->query("truncate  tbl_affiliate_wallet_history");
                $db->query("truncate  tbl_user_wallet_history");
                $db->query("truncate  tbl_deal_option");
                $db->query("truncate  tbl_deal_option_value");
                $db->query("truncate  tbl_order_shipping_details");
                $db->query("truncate  tbl_order_option");
                $db->query("truncate  tbl_digital_product_extras");
                $db->query("truncate  tbl_order_bookings");
                $db->query("truncate  tbl_deal_booking_dates");
                $db->query("truncate  tbl_deal_booking_values");
                $db->query("truncate  tbl_order_deal_taxes");
                $db->query("truncate  tbl_sub_deals");
                $db->query("update tbl_users set `user_wallet_amount` = 0 ");
                $db->query("DELETE FROM `tbl_reviews` WHERE `reviews_deal_id` > 0 ");
                $files = glob(DIGITAL_UPLOADS_PATH . '*.*');
                foreach ($files as $file) {
                    unlink($file);
                }
                $files = glob(DEAL_IMAGES_PATH . '*.*');
                foreach ($files as $file) {
                    unlink($file);
                }
            }
            if ($post['subscribers'] == 1) {
                $db->query("truncate  tbl_newsletter_subscription");
                $db->query("truncate  tbl_newsletter_sent");
                $db->query("truncate  tbl_newsletter_category");
            }
            $msg->addMsg(t_lang('M_TXT_CLEANED_SUCCESSFULL'));
        } else {
            $msg->addError(t_lang('M_TXT_PLEASE_CHOOSE_SOMETHING_TO_CLEAN'));
        }
    }
}
$arr_bread = ['index.php' => '<img alt="Home" src="images/home-icon.png">', '' => t_lang('M_TXT_CLEAN_DATA')];
?>
</div></td>
<td class="right-portion">
    <?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_CLEAN_DATA'); ?></div>
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?>
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="redtext"><?php echo $msg->display(); ?> </div>
                    <br/>
                    <br/>
                    <?php
                }
                if (isset($_SESSION['msgs'][0])) {
                    ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
    <?php ?>
    <div class="box"><div class="title"><?php echo t_lang('M_TXT_CLEAN_DATA'); ?></div><div class="content">
            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                echo $frm->getFormHtml();
            } else {
                echo $Src_frm->getFormHtml();
            }
            ?>
            <div class="gap">&nbsp;</div>
        </div></div>
</td>
<?php
require_once './footer.php';
