<?php
require_once './application-top.php';
global $arr_tax_received, $arr_tax_applicable_on;
checkAdminPermission(7);
$arr_date_format_php = ['Y-m-d', 'd-m-Y', 'm-d-Y', 'M d, Y'];
$arr_date_format_mysql = ['%Y-%m-%d', '%d-%m-%Y', '%m-%d-%Y', '%b %d, %Y'];
$arr_date_format_jquery = ['%Y-%m-%d', '%d-%m-%Y', '%m-%d-%Y', '%b %d, %Y'];
$replacement_val_for_hidden_keys = '******';
$hidden_value_key_field_names = ['conf_mandrill_api_key', 'conf_smtp_password'];
if (isset($_GET['front']) && $_GET['front'] == 'logo' && CONF_FRONT_END_LOGO != "") {
    unlink(LOGO_PATH . CONF_FRONT_END_LOGO);
    $db->query("update tbl_configurations set conf_val='' where conf_name='conf_front_end_logo'");
    $msg->addMsg(t_lang('M_TXT_IMAGE_DELETED'));
    redirectUser('configurations.php');
}
if (isset($_GET['front']) && $_GET['front'] == 'logo' && CONF_FAV_LOGO != "") {
    unlink(LOGO_PATH . CONF_FAV_LOGO);
    $db->query("update tbl_configurations set conf_val='' where conf_name='conf_fav_logo'");
    $msg->addMsg(t_lang('M_TXT_IMAGE_DELETED'));
    redirectUser('configurations.php');
}
if (isset($_GET['front_footer']) && $_GET['front_footer'] == 'logo' && CONF_FRONT_END_FOOTER_LOGO != "") {
    unlink(LOGO_PATH . CONF_FRONT_END_FOOTER_LOGO);
    $db->query("update tbl_configurations set conf_val='' where conf_name='conf_front_end_footer_logo'");
    $msg->addMsg(t_lang('M_TXT_IMAGE_DELETED'));
    redirectUser('configurations.php');
}
if (isset($_GET['admin']) && $_GET['admin'] == 'logo' && CONF_ADMIN_PANEL_LOGO != "") {
    unlink(LOGO_PATH . CONF_ADMIN_PANEL_LOGO);
    $db->query("update tbl_configurations set conf_val='' where conf_name='conf_admin_panel_logo'");
    $msg->addMsg(t_lang('M_TXT_IMAGE_DELETED'));
    redirectUser('configurations.php');
}
if (isset($_GET['email']) && $_GET['email'] == 'logo' && CONF_EMAIL_LOGO != "") {
    unlink(LOGO_PATH . CONF_EMAIL_LOGO);
    $db->query("update tbl_configurations set conf_val='" . addslashes($item_path) . "' where conf_name='conf_email_logo'");
    $msg->addMsg(t_lang('M_TXT_IMAGE_DELETED'));
    redirectUser('configurations.php');
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    if ($_FILES['conf_fav_logo']['name'] != "") {
        if (checkImageFavIconType($_FILES['conf_fav_logo']['type'])) {
            $item_path = str_replace(" ", "_", $_FILES['conf_fav_logo']['name']);
            if (CONF_FAV_LOGO != "") {
                unlink(LOGO_PATH . CONF_FAV_LOGO);
                unlink('../lib/tcpdf/images/' . CONF_FAV_LOGO);
            }
			
            if (!move_uploaded_file($_FILES['conf_fav_logo']['tmp_name'], LOGO_PATH . $item_path)) {
                die(t_lang('M_MSG_COULD_NOT_SAVE_FILE'));
            }
            copy(LOGO_PATH . $item_path, "../lib/tcpdf/images/" . $item_path);
            $db->query("update tbl_configurations set conf_val='" . addslashes($item_path) . "' where conf_name='conf_fav_logo'");
        }
    }
    if ($_FILES['conf_front_end_logo']['name'] != "") {
        if (checkImageTypes($_FILES['conf_front_end_logo']['type'])) {
            $item_path = time() . "_front_" . str_replace(" ", "_", $_FILES['conf_front_end_logo']['name']);
            if (CONF_FRONT_END_LOGO != "") {
                unlink(LOGO_PATH . CONF_FRONT_END_LOGO);
                unlink('../lib/tcpdf/images/' . CONF_FRONT_END_LOGO);
            }
            if (!move_uploaded_file($_FILES['conf_front_end_logo']['tmp_name'], LOGO_PATH . $item_path)) {
                die(t_lang('M_MSG_COULD_NOT_SAVE_FILE'));
            }
            copy(LOGO_PATH . $item_path, "../lib/tcpdf/images/" . $item_path);
            $db->query("update tbl_configurations set conf_val='" . addslashes($item_path) . "' where conf_name='conf_front_end_logo'");
        }
    }
    if ($_FILES['conf_front_end_footer_logo']['name'] != "") {
        if (checkImageTypes($_FILES['conf_front_end_footer_logo']['type'])) {
            $item_path = time() . "_front_" . str_replace(" ", "_", $_FILES['conf_front_end_footer_logo']['name']);
            if (CONF_FRONT_END_FOOTER_LOGO != "") {
                unlink(LOGO_PATH . CONF_FRONT_END_FOOTER_LOGO);
                unlink('../lib/tcpdf/images/' . CONF_FRONT_END_FOOTER_LOGO);
            }
            if (!move_uploaded_file($_FILES['conf_front_end_footer_logo']['tmp_name'], LOGO_PATH . $item_path)) {
                die(t_lang('M_MSG_COULD_NOT_SAVE_FILE'));
            }
            copy(LOGO_PATH . $item_path, "../lib/tcpdf/images/" . $item_path);
            $db->query("update tbl_configurations set conf_val='" . addslashes($item_path) . "' where conf_name='conf_front_end_footer_logo'");
        }
    }
    if ($_FILES['conf_admin_panel_logo']['name'] != "") {
        if (checkImageTypes($_FILES['conf_admin_panel_logo']['type'])) {
            $item_path = time() . "_admin_" . str_replace(" ", "_", $_FILES['conf_admin_panel_logo']['name']);
            if (CONF_ADMIN_PANEL_LOGO != "") {
                unlink(LOGO_PATH . CONF_ADMIN_PANEL_LOGO);
            }
            if (!move_uploaded_file($_FILES['conf_admin_panel_logo']['tmp_name'], LOGO_PATH . $item_path)) {
                die(t_lang('M_MSG_COULD_NOT_SAVE_FILE'));
            }
            $db->query("update tbl_configurations set conf_val='" . addslashes($item_path) . "' where conf_name='conf_admin_panel_logo'");
        }
    }
    if ($_FILES['conf_email_logo']['name'] != "") {
        if (checkImageTypes($_FILES['conf_email_logo']['type'])) {
            $item_path = time() . "_email_" . str_replace(" ", "_", $_FILES['conf_email_logo']['name']);
            if (CONF_EMAIL_LOGO != "") {
                unlink(LOGO_PATH . CONF_EMAIL_LOGO);
            }
            if (!move_uploaded_file($_FILES['conf_email_logo']['tmp_name'], LOGO_PATH . $item_path)) {
                die(t_lang('M_MSG_COULD_NOT_SAVE_FILE'));
            }
            $db->query("update tbl_configurations set conf_val='" . addslashes($item_path) . "' where conf_name='conf_email_logo'");
        }
    }
    $conf_date_format_php = $arr_date_format_php[$post['conf_date_format']];
    $conf_date_format_mysql = $arr_date_format_mysql[$post['conf_date_format']];
    $conf_date_format_jquery = $arr_date_format_jquery[$post['conf_date_format']];
    $db->query("update tbl_configurations set conf_val='" . addslashes($conf_date_format_jquery) . "' where conf_name='conf_date_format_jquery'");
    $db->query("update tbl_configurations set conf_val='" . addslashes($conf_date_format_mysql) . "' where conf_name='conf_date_format_mysql'");
    $db->query("update tbl_configurations set conf_val='" . addslashes($conf_date_format_php) . "' where conf_name='conf_date_format_php'");
    $configurable_fields = [
        'conf_emails_from',
        'conf_site_owner_email',
        'conf_site_name',
        'conf_emails_from_name',
        'conf_secondary_language',
        'conf_language_switcher',
        'conf_currency',
        'conf_currency_right',
        'conf_payment_production',
        'conf_currency_code',
        'conf_twitter_user',
        'conf_facebook_url',
        'conf_youtube_url',
        'conf_deal_purchase_notification',
        'conf_deal_purchase_notify_email_others',
        /* 'conf_comments_need_approval', */
        'conf_deal_footer_right_text',
        'conf_friendly_url',
        'conf_timezone',
        'conf_ssl_active',
        'conf_fav_logo',
        'conf_front_end_logo',
        'conf_front_end_footer_logo',
        'conf_admin_panel_logo',
        'conf_email_logo',
        'conf_success_page_text',
        'conf_success_page_text_lang1',
        'conf_affiliate_commission_percent',
        'conf_referrer_commission_percent',
        'conf_meta_title',
        'conf_meta_keywords',
        'conf_meta_description',
        'conf_deals_per_email',
        'conf_deals_expiration_notice',
        'conf_email_number',
        'conf_server_name',
        'conf_email_header_text',
        'conf_direct_browsing_allow',
        'conf_subscription_step',
        'conf_tax_received',
        'conf_tax_applicable_on',
        'conf_qr_code',
        'conf_apple_service_key',
        'conf_facebook_api_key',
        'conf_facebook_secret_key',
        'conf_facebook_api_key_mobile',
        'conf_facebook_secret_key_mobile',
        'conf_merchant_voucher',
        'conf_google_analytic_code',
        'conf_powered_by',
        'conf_review_rating_deals',
        'conf_review_rating_merchant',
        'conf_post_review_rating_deals',
        'conf_post_review_rating_merchant',
        'conf_send_email',
        'conf_email_sending_method',
        'conf_smtp_host',
        'conf_smtp_username',
        'conf_smtp_password',
        'conf_smtp_port',
        'conf_smtp_use_ssl',
        'conf_smtp_auth_required',
        'conf_admin_commission_type',
        'conf_voucher_start_date',
        'conf_voucher_end_date',
        'conf_default_notification_status',
        'conf_use_mobile_version',
        'conf_email_sending_method_promotional',
        'conf_mailchimp_api_key',
        'conf_mailchimp_list_id',
        'conf_mandrill_api_key',
        'conf_google_map_key'
    ];
    if (isset($post['conf_deal_purchase_notification']) && count($post['conf_deal_purchase_notification']) > 0) {
        $deal_purchase_notification = 0;
        foreach ($post['conf_deal_purchase_notification'] as $val) {
            $deal_purchase_notification += $val;
        }
        unset($post['conf_deal_purchase_notification']);
        $post['conf_deal_purchase_notification'] = $deal_purchase_notification;
    }
    if (isset($post['conf_deal_purchase_notify_email_others']) && strlen($post['conf_deal_purchase_notify_email_others']) > 0) {
        $post['conf_deal_purchase_notify_email_others'] = str_replace(' ', '', $post['conf_deal_purchase_notify_email_others']);
        $emails_others = explode(',', $post['conf_deal_purchase_notify_email_others']);
        $post['conf_deal_purchase_notify_email_others'] = '';
        foreach ($emails_others as $email_ot) {
            if (validateOtEmail($email_ot)) {
                $post['conf_deal_purchase_notify_email_others'] .= $email_ot . ',';
            }
        }
        if (strlen($post['conf_deal_purchase_notify_email_others']) > 1) {
            $post['conf_deal_purchase_notify_email_others'] = substr($post['conf_deal_purchase_notify_email_others'], 0, -1);
        } else {
            $post['conf_deal_purchase_notify_email_others'] = '';
        }
    }
    if (((int) $post['conf_email_sending_method']) === 2) {
        if (!checkPearMailExt()) {
            $post['conf_email_sending_method'] = 1;
            $msg->addError(t_lang('M_TXT_PEAR_MAIL_NOT_INSTALLED'));
        }
    }
    foreach ($configurable_fields as $fld) {
        if (!isset($post[$fld])) {
            continue;
        }
        $fld = strtolower($fld);
        if (in_array($fld, $hidden_value_key_field_names) && $post[$fld] == $replacement_val_for_hidden_keys) {
            continue;
        }
        $qry = "INSERT INTO tbl_configurations SET conf_name=" . $db->quoteVariable($fld) . ", conf_val=" . $db->quoteVariable($post[$fld]) . " ON DUPLICATE KEY UPDATE conf_val=" . $db->quoteVariable($post[$fld]);
        $db->query($qry);
    }
    if (count($post['conf_merchant_voucher']) > 0) {
        $count = 0;
        foreach ($post['conf_merchant_voucher'] as $val) {
            $count++;
            if ($count == count($post['conf_merchant_voucher'])) {
                $conf_merchant_voucher .= $val . '';
            } else {
                $conf_merchant_voucher .= $val . ',';
            }
        }
        $db->query("update tbl_configurations set conf_val='" . $conf_merchant_voucher . "' where conf_name='conf_merchant_voucher'");
    }
    $msg->addMsg(t_lang('M_TXT_SETTINGS_UPDATED'));
    redirectUser();
    exit;
}
$frm = new Form('frmConfig', 'frmConfig');
$frm->setTableProperties('width="100%" class="tbl_form"');
$frm->setFieldsPerRow(1);
$frm->setJsErrorDisplay('afterfield');
$frm->captionInSameCell(false);
$frm->setAction('?');
/* Deal purchase notification. */
$arr = ['4' => 'Users', '2' => 'Merchant', '1' => 'Admin'];
$ar_selected = [];
switch (CONF_DEAL_PURCHASE_NOTIFICATION) {
    case '1':
        $ar_selected[] = '1';
        break;
    case '2':
        $ar_selected[] = '2';
        break;
    case '3':
        $ar_selected[] = '1';
        $ar_selected[] = '2';
        break;
    case '4':
        $ar_selected[] = '4';
        break;
    case '5':
        $ar_selected[] = '1';
        $ar_selected[] = '4';
        break;
    case '6':
        $ar_selected[] = '2';
        $ar_selected[] = '4';
        break;
    case '7':
        $ar_selected[] = '1';
        $ar_selected[] = '2';
        $ar_selected[] = '4';
        break;
}
$frm->addCheckBoxes('', 'conf_deal_purchase_notification', $arr, $ar_selected);
$frm->addTextArea('', 'conf_deal_purchase_notify_email_others', CONF_DEAL_PURCHASE_NOTIFY_EMAIL_OTHERS, 'conf_deal_purchase_notify_email_others', 'style="width:500px;"');
/* Deal purchase notification. */
/* meta info */
$frm->addTextBox(t_lang('M_FRM_META_TITLE'), 'conf_meta_title', CONF_META_TITLE);
$frm->addTextarea(t_lang('M_FRM_META_KEYWORDS'), 'conf_meta_keywords', CONF_META_KEYWORDS);
$frm->addTextarea(t_lang('M_FRM_META_DESCRIPTION'), 'conf_meta_description', CONF_META_DESCRIPTION);
/* meta info end */
/* Site and Email Info */
$frm->addEmailField(t_lang('M_TXT_SEND_EMAILS_FROM_EMAIL_ID'), 'conf_emails_from', CONF_EMAILS_FROM, '', 'class="input"');
###########email Name########
$frm->addRequiredField(t_lang('M_TXT_EMAIL_NAME_FROM'), 'conf_emails_from_name', CONF_EMAILS_FROM_NAME);
################
###########Site Owner email Name########
$frm->addEmailField(t_lang('M_FRM_SITE_OWNER_EMAIL'), 'conf_site_owner_email', CONF_SITE_OWNER_EMAIL)->requirements()->setRequired();
################
$frm->addRequiredField(t_lang('M_TXT_SITE_NAME'), 'conf_site_name', CONF_SITE_NAME);
$frm->addTextBox(t_lang('M_FRM_SERVER_NAME'), 'conf_server_name', CONF_SERVER_NAME);
$frm->addSelectBox(t_lang('M_TXT_SEND_EMAIL'), 'conf_send_email', ['1' => 'Yes', '0' => 'No'], CONF_SEND_EMAIL, 'class="input"', '', 'conf_send_email');
$frm->addSelectBox(t_lang('M_TXT_EMAIL_SENDING_METHOD'), 'conf_email_sending_method', ['1' => 'Mail', '2' => 'SMTP Mail', '3' => 'Mandrill'], CONF_EMAIL_SENDING_METHOD, 'class="input" onchange="checkPearMailExt(this);" id="conf_email_sending_method"', 'Select');
###########email send method ########
$frm->addSelectBox(t_lang('M_TXT_EMAIL_SENDING_METHOD_PROMOTIONAL'), 'conf_email_sending_method_promotional', ['0' => 'Default', '1' => 'MailChimp'], CONF_EMAIL_SENDING_METHOD_PROMOTIONAL, 'class="input"', 'Select');
$frm->addTextBox(t_lang('M_FRM_SMTP_HOST'), 'conf_smtp_host', CONF_SMTP_HOST);
$frm->addTextBox(t_lang('M_FRM_SMTP_USERNAME'), 'conf_smtp_username', CONF_SMTP_USERNAME);
$frm->addPasswordField(t_lang('M_FRM_SMTP_PASSWORD'), 'conf_smtp_password', CONF_SMTP_PASSWORD);
$frm->addTextBox(t_lang('M_FRM_SMTP_PORT'), 'conf_smtp_port', CONF_SMTP_PORT);
$frm->addSelectBox(t_lang('M_FRM_SMTP_USE_SSL'), 'conf_smtp_use_ssl', ['1' => 'Yes', '0' => 'No'], CONF_SMTP_USE_SSL, 'class="input"', 'Select');
$frm->addSelectBox(t_lang('M_FRM_SMTP_AUTHENTICATION_REQUIRED'), 'conf_smtp_auth_required', ['true' => 'Yes', 'false' => 'No'], CONF_SMTP_AUTH_REQUIRED, 'class="input"', 'Select');
$frm->addSelectBox(t_lang('M_FRM_MOBILE_VERSION_ACTIVE'), 'conf_use_mobile_version', ['1' => 'Yes', '0' => 'No'], CONF_USE_MOBILE_VERSION, 'class="input"', 'Select');
$frm->addTextBox(t_lang('M_FRM_MAILCHIMP_API_KEY'), 'conf_mailchimp_api_key', CONF_MAILCHIMP_API_KEY, '', 'style="width:300px;"');
$frm->addTextBox(t_lang('M_FRM_MAILCHIMP_LIST_ID'), 'conf_mailchimp_list_id', CONF_MAILCHIMP_LIST_ID, '', 'style="width:300px;"');
$frm->addTextBox(t_lang('M_FRM_MAILCHIMP_API_KEY'), 'conf_mandrill_api_key', CONF_MANDRILL_API_KEY, '', 'style="width:300px;"');
$frm->addTextBox(t_lang('M_FRM_GOOGLE_MAP_API_KEY'), 'conf_google_map_key', CONF_GOOGLE_MAP_KEY, '', 'style="width:300px;"');
/* Site and Email Info End */
/* currency settings */
$frm->addTextBox(t_lang('M_TXT_CURRENCY_SYMBOL_LEFT'), 'conf_currency', CONF_CURRENCY, 'conf_currency', 'onblur="currenySymbol(this.value)"');
#################Currency at right side ##########
$frm->addTextBox(t_lang('M_TXT_CURRENCY_SYMBOL_RIGHT'), 'conf_currency_right', CONF_CURRENCY_RIGHT, 'conf_currency_right', 'onblur="currenySymbolRight(this.value)"');
##########################
$frm->addSelectBox(t_lang('M_TXT_PAYMENT_MODE'), 'conf_payment_production', ['Test Mode', 'Production Mode'], CONF_PAYMENT_PRODUCTION, 'class="input"', '');
$frm->addRequiredField(unescape_attr(t_lang('M_TXT_CURRENCY_CODE')), 'conf_currency_code', CONF_CURRENCY_CODE, '', 'class="input"');
$frm->addTextarea(t_lang('M_FRM_SUCCESS_PAGE_PAYPAL_TEXT'), 'conf_success_page_text', CONF_SUCCESS_PAGE_TEXT);
$frm->addTextarea(t_lang('M_FRM_SUCCESS_PAGE_PAYPAL_TEXT') . ' (' . t_lang('M_TXT_SECONDARY_LANGUAGE') . ')', 'conf_success_page_text_lang1', CONF_SUCCESS_PAGE_TEXT_LANG1);
/* currency settings end */
/* social site links */
$frm->addTextBox(t_lang('M_TXT_TWITTER_USERNAME'), 'conf_twitter_user', CONF_TWITTER_USER);
$frm->addTextBox(t_lang('M_TXT_FACEBOOK_URL'), 'conf_facebook_url', CONF_FACEBOOK_URL);
$frm->addTextBox(t_lang('M_TXT_YOUTUBE_URL'), 'conf_youtube_url', CONF_YOUTUBE_URL);
/* social site links end */
/* Third party Api Secret settings */
$frm->addTextBox(t_lang('M_FRM_APPLE_SERVICE_API_KEY_FOR_LOGIN'), 'conf_apple_service_key', CONF_APPLE_SERVICE_KEY);
$frm->addTextBox(t_lang('M_FRM_FACEBOOK_API_KEY_FOR_LOGIN'), 'conf_facebook_api_key', CONF_FACEBOOK_API_KEY);
$frm->addTextBox(t_lang('M_FRM_FACEBOOK_SECRET_KEY_FOR_LOGIN'), 'conf_facebook_secret_key', CONF_FACEBOOK_SECRET_KEY);
$frm->addTextBox(t_lang('M_FRM_FACEBOOK_API_KEY_FOR_LOGIN_MOBILE'), 'conf_facebook_api_key_mobile', CONF_FACEBOOK_API_KEY_MOBILE);
$frm->addTextBox(t_lang('M_FRM_FACEBOOK_SECRET_KEY_FOR_LOGIN_MOBILE'), 'conf_facebook_secret_key_mobile', CONF_FACEBOOK_SECRET_KEY_MOBILE);
/* Third party Api Secret settings end */
/* Logo Settings */
############### logo ####################
if (CONF_FRONT_END_LOGO == "") {
    $fld = $frm->addFileUpload(t_lang('M_FRM_SELECT_FRONT_END_LOGO'), 'conf_front_end_logo', 'conf_front_end_logo', '');
    $fld->requirements()->setRequired();
    $fld->html_after_field = "<span class='spn_must_field'>( Size should be 284 X 84  for best result )</span>";
    $frm->addHTML('', '', '', false);
    $frm->addHTML('', '', '', false);
} else {
    $fld = $frm->addFileUpload(t_lang('M_FRM_SELECT_FRONT_END_LOGO'), 'conf_front_end_logo', 'conf_front_end_logo', '');
    $fld->html_after_field = "<span class='spn_must_field'>( Size should be 284 X 84 for best result )</span>";
    $frm->addHTML('', 'logo1', '<img width="20%" height="auto" src="' . LOGO_URL . CONF_FRONT_END_LOGO . '"  border="0"> &nbsp;&nbsp;&nbsp; <ul class="actions margin-ul-30"><li><a href="?front=logo" title="' . t_lang('M_TXT_DELETE') . '"><i class="ion-android-delete icon"></i></a></li></ul>', false);
}
if (CONF_FAV_LOGO == "") {
    $fld = $frm->addFileUpload(t_lang('M_FRM_SELECT_Fav_Icon'), 'conf_fav_logo', 'conf_fav_logo', '');
    //$fld->requirements()->setRequired();
    $fld->html_after_field = "<span class='spn_must_field'>( Size should be 16 X 16  for best result )</span>";
    $frm->addHTML('', '', '', false);
    $frm->addHTML('', '', '', false);
} else {
    $fld = $frm->addFileUpload(t_lang('M_FRM_SELECT_Fav_Icon'), 'conf_fav_logo', 'conf_fav_logo', '');
    $fld->html_after_field = "<span class='spn_must_field'>( Size should be 16 X 16 for best result )</span>";
    $frm->addHTML('', 'favicon', '<img height="auto" src="' . LOGO_URL . CONF_FAV_LOGO . '"  border="0"> &nbsp;&nbsp;&nbsp; <ul class="actions "><li><a href="?front=logo" title="' . t_lang('M_TXT_DELETE') . '"><i class="ion-android-delete icon"></i></a></li></ul>', false);
}
if (CONF_FRONT_END_FOOTER_LOGO == "") {
    $fld = $frm->addFileUpload(t_lang('M_FRM_SELECT_FRONT_END_LOGO'), 'conf_front_end_footer_logo', 'conf_front_end_footer_logo', '');
    $fld->requirements()->setRequired();
    $fld->html_after_field = "<span class='spn_must_field'>( Size should be 284 X 84  for best result )</span>";
    $frm->addHTML('', '', '', false);
    $frm->addHTML('', '', '', false);
} else {
    $fld = $frm->addFileUpload(t_lang('M_FRM_SELECT_FRONT_END_LOGO'), 'conf_front_end_footer_logo', 'conf_front_end_footer_logo', '');
    $fld->html_after_field = "<span class='spn_must_field'>( Size should be 284 X 84 for best result )</span>";
    $frm->addHTML('', 'footerlogo1', '<img width="20%" height="auto" src="' . LOGO_URL . CONF_FRONT_END_FOOTER_LOGO . '"  border="0"> &nbsp;&nbsp;&nbsp; <ul class="actions margin-ul-30"><li><a href="?front_footer=logo" title="' . t_lang('M_TXT_DELETE') . '"><i class="ion-android-delete icon"></i></a></li></ul>', false);
}
if (CONF_ADMIN_PANEL_LOGO == "") {
    $frm->addFileUpload(t_lang('M_FRM_SELECT_ADMIN_PANEL_LOGO'), 'conf_admin_panel_logo', 'conf_admin_panel_logo', '')->requirements()->setRequired();
    $frm->addHTML('', '', '', false);
    $frm->addHTML('', '', '', false);
} else {
    $frm->addFileUpload(t_lang('M_FRM_SELECT_ADMIN_PANEL_LOGO'), 'conf_admin_panel_logo', 'conf_admin_panel_logo', '');
    $frm->addHTML('', 'logo2', '<img src="' . LOGO_URL . CONF_ADMIN_PANEL_LOGO . '" width="20%" height="auto" border="0">&nbsp;&nbsp;&nbsp; <ul class="actions margin-ul-30"><li><a href="?admin=logo" title="' . t_lang('M_TXT_DELETE') . '"><i class="ion-android-delete icon"></i></a></li></ul>', false);
}
if (CONF_EMAIL_LOGO == "") {
    $frm->addFileUpload(t_lang('M_FRM_SELECT_EMAIL_LOGO'), 'conf_email_logo', 'conf_email_logo', '')->requirements()->setRequired();
    $frm->addHTML('', '', '', false);
    $frm->addHTML('', '', '', false);
} else {
    $frm->addFileUpload(t_lang('M_FRM_SELECT_EMAIL_LOGO'), 'conf_email_logo', 'conf_email_logo', '');
    $frm->addHTML('', 'logo3', '<img src="' . LOGO_URL . CONF_EMAIL_LOGO . '" width="20%" height="auto" border="0">&nbsp;&nbsp;&nbsp; <ul class="actions margin-ul-30"><li><a href="?email=logo" title="' . t_lang('M_TXT_DELETE') . '"><i class="ion-android-delete icon"></i></a></li></ul>', false);
}
/* loggo settings end */
/* Misc Settings */
$frm->addTextBox(t_lang('M_TXT_SECONDARY_LANGUAGE'), 'conf_secondary_language', CONF_SECONDARY_LANGUAGE);
$frm->addSelectBox(t_lang('M_TXT_DEFAULT_VALUE_FOR_NOTIFICATIONS'), 'conf_default_notification_status', ['1' => 'Yes', '0' => 'No'], CONF_DEFAULT_NOTIFICATION_STATUS, 'class="input"', '', 'conf_default_notification_status');
$frm->addSelectBox(t_lang('M_TXT_DATE_FORMAT'), 'conf_date_format', $arr_date_format_php, array_search(CONF_DATE_FORMAT_PHP, $arr_date_format_php), 'class="input"', '');
$frm->addSelectBox(t_lang('M_FRM_LANGUAGE_SWITCHER'), 'conf_language_switcher', ['0' => 'OFF', '1' => 'ON'], CONF_LANGUAGE_SWITCHER, '1', 'Select', 'conf_language_switcher');
$arr_timezones = DateTimeZone::listIdentifiers();
$arr_timezones = array_combine($arr_timezones, $arr_timezones);
$frm->addSelectBox(t_lang('M_FRM_TIMEZONE'), 'conf_timezone', $arr_timezones, CONF_TIMEZONE, '', '', 'conf_timezone');
$frm->addSelectBox(t_lang('M_FRM_FRIENDLY_URL'), 'conf_friendly_url', ['0' => 'No', '1' => 'Yes'], CONF_FRIENDLY_URL, '', 'Select', 'conf_friendly_url');
$frm->addSelectBox(t_lang('M_FRM_SSL_ACTIVE'), 'conf_ssl_active', ['0' => 'No', '1' => 'Yes'], CONF_SSL_ACTIVE, '', 'Select', 'conf_ssl_active');
$frm->addSelectBox(t_lang('M_FRM_ALLOW_DIRECT_BROWSING'), 'conf_direct_browsing_allow', ['0' => 'No', '1' => 'Yes'], CONF_DIRECT_BROWSING_ALLOW, '', 'Select', 'conf_direct_browsing_allow');
$frm->addSelectBox(t_lang('M_FRM_REVIEWS_SECTION_FOR_DEALS'), 'conf_review_rating_deals', ['0' => 'Disable', '1' => 'Enable'], CONF_REVIEW_RATING_DEALS, '', 'Select', 'conf_review_rating_deals');
$frm->addSelectBox(t_lang('M_FRM_REVIEWS_SECTION_FOR_MERCHANTS'), 'conf_review_rating_merchant', ['0' => 'Disable', '1' => 'Enable'], CONF_REVIEW_RATING_MERCHANT, '', 'Select', 'conf_review_rating_merchant');
$frm->addSelectBox(t_lang('M_FRM_WHICH_USERS_CAN_POST_REVIEW_FOR_DEALS'), 'conf_post_review_rating_deals', ['1' => 'Who has purchase the voucher for this deal', '0' => 'All logged in users'], CONF_POST_REVIEW_RATING_DEALS, '', 'Select', 'conf_post_review_rating_deals');
$frm->addSelectBox(t_lang('M_FRM_WHICH_USERS_CAN_POST_REVIEW_FOR_MERCHANT'), 'conf_post_review_rating_merchant', ['1' => 'Who has purchase the voucher from this merchant', '0' => 'All logged in users'], CONF_POST_REVIEW_RATING_MERCHANT, '', 'Select', 'conf_post_review_rating_merchant');
$frm->addSelectBox(t_lang('M_FRM_REPOST_VOUCHER_START_DATE'), 'conf_voucher_start_date', ['0' => 'Deal Start Date', '1' => 'Deal End Date'], CONF_VOUCHER_START_DATE, '', '', 'conf_voucher_start_date');
$frm->addTextBox(t_lang('M_FRM_REPOST_VOUCHER_END_DATE'), 'conf_voucher_end_date', CONF_VOUCHER_END_DATE);
$frm->addTextBox(t_lang('M_TXT_REFERRER_COMMISSION') . ' ( ' . CONF_CURRENCY . CONF_CURRENCY_RIGHT . ' )', 'conf_referrer_commission_percent', CONF_REFERRER_COMMISSION_PERCENT);
$frm->addSelectBox(t_lang('M_FRM_ADMIN_COMMISSION_TYPE'), 'conf_admin_commission_type', ['1' => 'Deal Wise', '2' => 'City Wise', '3' => 'Merchant Wise'], CONF_ADMIN_COMMISSION_TYPE, 'class="input"', 'Select')->requirements()->setRequired();
$frm->addSelectBox(t_lang('M_FRM_QR_CODE'), 'conf_qr_code', ['1' => 'Voucher Code', '2' => 'Merchant Url'], CONF_QR_CODE, '', 'Select', 'conf_qr_code');
$frm->addSelectBox(t_lang('M_FRM_SUBSCRIPTION_STEPS'), 'conf_subscription_step', ['0' => 'No', '1' => 'Yes'], CONF_SUBSCRIPTION_STEP, '', 'Select', 'conf_subscription_step');
$frm->addTextBox(t_lang('M_FRM_TAX_RECEIVED_TO'), 'conf_tax_received', $arr_tax_received[CONF_TAX_RECEIVED], 'conf_tax_received', ' disabled');
$frm->addTextBox(t_lang('M_FRM_TAX_APPLICABLE_ON'), 'conf_tax_applicable_on', $arr_tax_applicable_on[CONF_TAX_APPLICABLE_ON], 'conf_tax_applicable_on', ' disabled');
if (CONF_MERCHANT_VOUCHER != "") {
    $total_value = explode(",", CONF_MERCHANT_VOUCHER);
}
$frm->addCheckboxes('Includes vouchers in merchant total ', 'conf_merchant_voucher', ['1' => 'Used', '0' => 'Un-used', '2' => 'Expired'], $total_value, 3, 'width="100%"', '');
$frm->addTextBox(t_lang('M_FRM_EMAILS_IN_ONE_GO'), 'conf_email_number', CONF_EMAIL_NUMBER);
$frm->addTextBox(t_lang('M_FRM_NUMBER_OF_DEALS_TO_BE_SENT_IN_NEWSLETTER'), 'conf_deals_per_email', CONF_DEALS_PER_EMAIL);
$frm->addTextBox(t_lang('M_FRM_DEAL_EXPIRE_NOTICE'), 'conf_deals_expiration_notice', CONF_DEALS_EXPIRATION_NOTICE);
$frm->addTextarea(t_lang('M_TXT_GOOGLE_ANALYTIC_CODE'), 'conf_google_analytic_code', CONF_GOOGLE_ANALYTIC_CODE)->merge_cells = 2;
$frm->addTextBox(t_lang('M_FRM_POWERED_BY_LINK'), 'conf_powered_by', CONF_POWERED_BY);
#################################################
$fld = $frm->addHTML('', 'sp', '', true);
$fld = $frm->addHtmlEditor(t_lang('M_FRM_EMAIL_HEADER_TEXT'), 'conf_email_header_text', CONF_EMAIL_HEADER_TEXT);
$fld->html_before_field = '<div class="frm-editor">';
$fld->html_after_field = '</div>';
$fld = $frm->addHTML('', '', '', true);
$frm->addSubmitButton('&nbsp;', 'btn_submit', t_lang('M_TXT_UPDATE'), '', 'class="inputbuttons" ');
/* This function is to hide key field values */
foreach ($hidden_value_key_field_names as $fld_name) {
    $frm->getField($fld_name)->value = $replacement_val_for_hidden_keys;
}
/* This function is to hide key field values */
require_once './header.php';
$arr_bread = array(
    'index.php' => '<img alt="Home" src="images/home-icon.png">',
    'configurations.php' => t_lang('M_TXT_SETTINGS'),
    '' => t_lang('M_TXT_GENERAL_SETTINGS')
);
if ((checkAdminAddEditDeletePermission(7, '', 'add')) && (checkAdminAddEditDeletePermission(7, '', 'edit'))) {
    ?>
    <ul class="nav-left-ul">
        <li><a href="configurations.php"  class="selected"><?php echo t_lang('M_TXT_GENERAL_SETTINGS'); ?></a></li>
        <li><a href="payment-settings.php"><?php echo t_lang('M_TXT_PAYMENT_GATEWAY_SETTINGS'); ?></a></li>
        <li><a href="email-templates.php"><?php echo t_lang('M_TXT_EMAIL_TEMPLATES'); ?></a></li>
        <li><a href="language-managment.php"><?php echo t_lang('M_TXT_LANGUAGE_MANAGEMENT'); ?></a></li>
        <li><a href="cities.php" ><?php echo t_lang('M_TXT_CITIES_MANAGEMENT'); ?></a></li>
        <!--li><a href="database-backup.php" ><?php echo t_lang('M_TXT_DATABASE_BACKUP_RESTORE'); ?></a></li-->
    </ul>
    </div></td>
    <td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
        <div class="div-inline">
            <div class="page-name"><?php echo t_lang('M_TXT_GENERAL_SETTINGS'); ?></div>
        </div>
        <div class="clear"></div>
        <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?>
            <div class="box" id="messages">
                <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
                <div class="content">
                    <?php if (isset($_SESSION['errs'][0])) { ?>
                        <div class="message error"><?php echo $msg->display(); ?> </div>
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
        <?php echo $frm->getFormTag(); ?>
        <div class="row">
            <div class="col-sm-12">
                <div class="containerwhite  tabs_nav_container">
                    <aside class="grid_2">
                        <ul class="centered_nav tabs_nav">
                            <li><a href="javascript:void(0)" rel="tabs_01" id="tab_1" class="active"> <?php echo t_lang('M_TXT_ EMAILS_AND_SITE_INFO'); ?> </a></li>
                           <!-- <li><a href="javascript:void(0)" rel="tabs_02" id="tab_2" class=""> <?php echo t_lang('M_TXT_PROMOTIONAL_EMAIL/API_SETTINGS'); ?> </a></li>-->
                            <li><a href="javascript:void(0)" rel="tabs_03" id="tab_3" class=""> <?php echo t_lang('M_TXT_PAYMENT_SETTINGS'); ?></a></li>
                            <li><a href="javascript:void(0)" rel="tabs_05" id="tab_5"class=""><?php echo t_lang('M_TXT_SOCIAL_SITES_LINK'); ?></a></li>
                            <li><a href="javascript:void(0)" rel="tabs_06" id="tab_6" class=""><?php echo t_lang('M_TXT_THIRD_PARTY_API_SECRETS'); ?></a></li>
                            <li><a href="javascript:void(0)" rel="tabs_07" id="tab_7" class=""><?php echo t_lang('M_TXT_LOGO/META_SETTINGS'); ?></a></li>
                            <li><a href="javascript:void(0)" rel="tabs_08" id="tab_8" class=""><?php echo t_lang('M_TXT_GENERAL_SETTINGS'); ?></a></li>
                        </ul>
                        <div class="box tabs_panel" id="tabs_01" style="display:block;">
                            <div class="title" rel="tabs_01"> <?php echo t_lang('M_TXT_ EMAILS_AND_SITE_INFO'); ?> </div>
                            <div class="content ">
                                <table class="tbl_form" width="100%">
                                    <tbody>
                                        <tr>
                                            <td><?php echo t_lang('M_TXT_SEND_EMAILS_FROM_EMAIL_ID'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_emails_from'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_TXT_EMAIL_NAME_FROM'); ?><div style='color:red;'>(not an email address)</div></td>
                                            <td><?php echo $frm->getFieldHTML('conf_emails_from_name'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_SITE_OWNER_EMAIL'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_site_owner_email'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_TXT_SITE_NAME'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_site_name'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_SERVER_NAME'); ?> <small>[ Example: example.com/ ]</small></td>
                                            <td><?php echo $frm->getFieldHTML('conf_server_name'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_SEND_EMAIL'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_send_email'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_EMAIL_SENDING_METHOD'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_email_sending_method'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_SMTP_HOST'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_smtp_host'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_SMTP_USERNAME'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_smtp_username'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_SMTP_PASSWORD'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_smtp_password'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_SMTP_PORT'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_smtp_port'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_SMTP_USE_SSL'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_smtp_use_ssl'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_SMTP_AUTHENTICATION_REQUIRED'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_smtp_auth_required'); ?></td>
                                        </tr>
                                        <!--<tr>
                                                <td><?php echo t_lang('M_FRM_MOBILE_VERSION_ACTIVE'); ?></td>
                                                <td><?php echo $frm->getFieldHTML('conf_use_mobile_version'); ?></td>
                                        </tr>-->
                                        <tr>
                                            <td><?php echo t_lang('M_TXT_EMAIL_SENDING_METHOD_PROMOTIONAL'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_email_sending_method_promotional'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_NUMBER_OF_DEALS_TO_BE_SENT_IN_NEWSLETTER'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_deals_per_email'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_EMAILS_IN_ONE_GO'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_email_number'); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="box tabs_panel"  id="tabs_03" style="display: none;">
                            <div class="title" rel="tabs_03"><?php echo t_lang('M_TXT_PAYMENT_SETTINGS'); ?></div>
                            <div class="content " >
                                <table class="tbl_form" width="100%">
                                    <tbody>
                                        <tr>
                                            <td><?php echo t_lang('M_TXT_CURRENCY_SYMBOL_LEFT'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_currency'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_TXT_CURRENCY_SYMBOL_RIGHT'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_currency_right'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_TXT_PAYMENT_MODE'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_payment_production'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_ADMIN_COMMISSION_TYPE'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_admin_commission_type'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo unescape_attr(t_lang('M_TXT_CURRENCY_CODE')); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_currency_code'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_SUCCESS_PAGE_PAYPAL_TEXT'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_success_page_text'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_SUCCESS_PAGE_PAYPAL_TEXT') . ' (' . t_lang('M_TXT_SECONDARY_LANGUAGE') . ')'; ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_success_page_text_lang1'); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="box tabs_panel"  id="tabs_05" style="display: none;">
                            <div class="title" rel="tabs_05"><?php echo t_lang('M_TXT_SOCIAL_SITES_LINK'); ?></div>
                            <div class="content">
                                <table class="tbl_form" width="100%">
                                    <tbody>
                                        <tr>
                                            <td><?php echo t_lang('M_TXT_TWITTER_USERNAME'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_twitter_user'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_TXT_FACEBOOK_URL'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_facebook_url'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_TXT_YOUTUBE_URL'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_youtube_url'); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="box tabs_panel"  id="tabs_06" style="display: none;">
                            <div class="title"  rel="tabs_06"><?php echo t_lang('M_TXT_THIRD_PARTY_API_SECRETS'); ?></div>
                            <div class="content">
                                <table class="tbl_form" width="100%">
                                    <tbody>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_APPLE_SERVICE_API_KEY_FOR_LOGIN'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_apple_service_key'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_FACEBOOK_API_KEY_FOR_LOGIN'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_facebook_api_key'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_FACEBOOK_SECRET_KEY_FOR_LOGIN'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_facebook_secret_key'); ?></td>
                                        </tr>
                                        <!--<tr>
                                                <td><?php echo t_lang('M_FRM_FACEBOOK_API_KEY_FOR_LOGIN_MOBILE'); ?></td>
                                                <td><?php echo $frm->getFieldHTML('conf_facebook_api_key_mobile'); ?></td>
                                        </tr>
                                        <tr>
                                                <td><?php echo t_lang('M_FRM_FACEBOOK_SECRET_KEY_FOR_LOGIN_MOBILE'); ?></td>
                                                <td><?php echo $frm->getFieldHTML('conf_facebook_secret_key_mobile'); ?></td>
                                        </tr>-->
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_MANDRILL_API_KEY'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_mandrill_api_key'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_MAILCHIMP_API_KEY'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_mailchimp_api_key'); ?></td>
                                        </tr>
                                        <tr>
                                            <td ><?php echo t_lang('M_FRM_MAILCHIMP_LIST_ID'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_mailchimp_list_id'); ?></td>
                                        </tr>
                                        <tr>
                                            <td ><?php echo t_lang('M_FRM_GOOGLE_MAP_API_KEY'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_google_map_key'); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="box tabs_panel"  id="tabs_07" style="display: none;">
                            <div class="title" rel="tabs_07" ><?php echo t_lang('M_TXT_LOGO_SETTINGS'); ?></div>
                            <div class="content">
                                <table class="tbl_form" width="100%">
                                    <tbody>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_SELECT_FAV_ICON'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_fav_logo'); ?></td>
                                        </tr>
                                        <?php if (CONF_FAV_LOGO != "") { ?>
                                            <tr>
                                                <td></td>
                                                <td><?php echo $frm->getFieldHTML('favicon'); ?></td>
                                            </tr>
                                        <?php } ?>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_SELECT_FRONT_END_LOGO'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_front_end_logo'); ?></td>
                                        </tr>
                                        <?php if (CONF_FRONT_END_LOGO != "") { ?>
                                            <tr>
                                                <td></td>
                                                <td><?php echo $frm->getFieldHTML('logo1'); ?></td>
                                            </tr>
                                        <?php } ?>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_SELECT_FRONT_END_FOOTER_LOGO'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_front_end_footer_logo'); ?></td>
                                        </tr>
                                        <?php if (CONF_FRONT_END_FOOTER_LOGO != "") { ?>
                                            <tr>
                                                <td></td>
                                                <td><?php echo $frm->getFieldHTML('footerlogo1'); ?></td>
                                            </tr>
                                        <?php } ?>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_SELECT_ADMIN_PANEL_LOGO'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_admin_panel_logo'); ?></td>
                                        </tr>
                                        <?php if (CONF_ADMIN_PANEL_LOGO != "") { ?>
                                            <tr>
                                                <td></td>
                                                <td><?php echo $frm->getFieldHTML('logo2'); ?></td>
                                            </tr>
                                        <?php } ?>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_SELECT_EMAIL_LOGO'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_email_logo'); ?></td>
                                        </tr>
                                        <?php if (CONF_EMAIL_LOGO != "") { ?>
                                            <tr>
                                                <td></td>
                                                <td><?php echo $frm->getFieldHTML('logo3'); ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="title"><?php echo t_lang('M_TXT_META_SETTINGS'); ?></div>
                            <div class="content">
                                <table class="tbl_form" width="100%">
                                    <tbody>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_META_TITLE'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_meta_title'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_META_KEYWORDS'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_meta_keywords'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_META_DESCRIPTION'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_meta_description'); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="box tabs_panel"  id="tabs_08" style="display: none;">
                            <div class="title" rel="tabs_08"><?php echo t_lang('M_TXT_GENERAL_SETTINGS'); ?></div>
                            <div class="content" rel="tabs_08">
                                <table class="tbl_form" width="100%">
                                    <tbody>
                                        <tr>
                                            <td><?php echo t_lang('M_TXT_SECONDARY_LANGUAGE'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_secondary_language'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_LANGUAGE_SWITCHER'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_language_switcher'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_TXT_DEFAULT_VALUE_FOR_NOTIFICATIONS'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_default_notification_status'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_TXT_DEAL_PURCHASE_NOTIFICATION'); ?></td>
                                            <td>
                                                <?php echo $frm->getFieldHTML('conf_deal_purchase_notification'); ?><br />
                                                <?php echo t_lang('M_TXT_OTHER_NOTIFICATION_EMAILS') . '<br />' . $frm->getFieldHTML('conf_deal_purchase_notify_email_others'); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_TXT_DATE_FORMAT'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_date_format'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_TIMEZONE'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_timezone'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_FRIENDLY_URL'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_friendly_url'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_SSL_ACTIVE'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_ssl_active'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_ALLOW_DIRECT_BROWSING'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_direct_browsing_allow'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_REVIEWS_SECTION_FOR_DEALS'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_review_rating_deals'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_REVIEWS_SECTION_FOR_MERCHANTS'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_review_rating_merchant'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_WHICH_USERS_CAN_POST_REVIEW_FOR_DEALS'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_post_review_rating_deals'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_WHICH_USERS_CAN_POST_REVIEW_FOR_MERCHANT'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_post_review_rating_merchant'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_REPOST_VOUCHER_START_DATE'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_voucher_start_date'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_REPOST_VOUCHER_END_DATE'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_voucher_end_date'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_TXT_REFERRER_COMMISSION'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_referrer_commission_percent'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_QR_CODE'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_qr_code'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_SUBSCRIPTION_STEPS'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_subscription_step'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_TAX_RECEIVED_TO'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_tax_received'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_TAX_APPLICABLE_ON'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_tax_applicable_on'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_TXT_INCLUDE_VOUCHERS_IN_MERCHANT_TOTAL'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_merchant_voucher'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_TXT_GOOGLE_ANALYTIC_CODE'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_google_analytic_code'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_TXT_POWERED_BY_LINK'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_powered_by'); ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo t_lang('M_FRM_EMAIL_HEADER_TEXT'); ?></td>
                                            <td><?php echo $frm->getFieldHTML('conf_email_header_text'); ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <table>
                            <tr>
                                <td>&nbsp;</td>
                                <td><input name="btn_submit" class="inputbuttons" style="text-align:center;" title="&nbsp;" value="<?php echo t_lang('M_TXT_UPDATE'); ?>" type="submit"></td>
                            </tr>
                        </table>
                    </aside>
                </div>
            </div>
        </div>
    </form>
    <?php echo $frm->getExternalJS(); ?>
    </td>
    <?php
}
require_once './footer.php';
?>
<script type="text/javascript">
    function currenySymbol(val) {
        if (val) {
            $("#conf_currency_right").val('');
        }
    }
    function currenySymbolRight(val) {
        if (val) {
            $("#conf_currency").val('');
        }
    }
    $(".tabs_nav li a").click(function () {
        $(this).parents('.tabs_nav_container:first').find(".tabs_panel").hide();
        var activeTab = $(this).attr("rel");
        $("#" + activeTab).fadeIn();
        $(this).parents('.tabs_nav_container:first').find(".tabs_nav li a").removeClass("active");
        $(this).addClass("active");
    });
</script>
