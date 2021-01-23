<?php
require_once '../application-top.php';
if (!isCompanyUserLogged()) {
    redirectUser(CONF_WEBROOT_URL . 'merchant/login.php');
}
//Form
$company_id = $_SESSION['logged_user']['company_id'];
$facebookQry = $db->query("SELECT company_fanpage_id FROM tbl_companies where company_id=$company_id");
$row = $db->fetch($facebookQry);
$company_fanpage_id = $row['company_fanpage_id'];
$facebook_frm = new Form('facebook_frm', 'facebook_frm');
$facebook_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$facebook_frm->setFieldsPerRow(1);
$facebook_frm->captionInSameCell(false);
$facebook_frm->setAction('?');
$facebook_frm->setJsErrorDisplay('afterfield');
$fld = $facebook_frm->addRequiredField(t_lang('M_FRM_FACEBOOK_FANPAGE_ID'), 'company_fanpage_id', $row['company_fanpage_id'], '', 'style="width:300px;"');
$fld->html_after_field = '&nbsp;<a href="http://www.facebook.com/pages/create.php" target="_blank">' . t_lang('M_TXT_CREATE_YOUR_BUSINESS_PAGE') . '</a>';
$facebook_frm->addSubmitButton('', 'btn_save', t_lang('M_TXT_SAVE'), '', ' class="inputbuttons"');
$post = getPostedData();
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['company_fanpage_id'] != "" && $post['btn_save'] == "Save") {
    if ($facebook_frm->validate($post)) {
        $arr_updates = array('company_fanpage_id' => $post['company_fanpage_id']);
        $record = new TableRecord('tbl_companies');
        $record->assignValues($arr_updates);
        $company_id = $_SESSION['logged_user']['company_id'];
        if (!$record->update('company_id=' . $_SESSION['logged_user']['company_id'])) {
            $msg->addError($record->getError());
            $facebook_frm->fill($post);
        } else {
            $msg->addMsg(t_lang('M_TXT_INFO_UPDATED'));
            redirectUser();
        }
    } else {
        $errors = $facebook_frm->getValidationErrors();
        foreach ($errors as $error) {
            $msg->addError($error);
        }
        fillForm($facebook_frm, $post);
    }
}
if (isset($company_fanpage_id) && strlen($company_fanpage_id) > 1) {
    require_once "../facebook-php-sdk_old/src/facebook.php";
    $fb = new Facebook(array(
        'appId' => CONF_FACEBOOK_API_KEY,
        'secret' => CONF_FACEBOOK_SECRET_KEY,
    ));
    $user = $fb->getUser();
    if ($user && isset($_REQUEST['code'])) {
        $fb->setExtendedAccessToken();
        $extended_access_token = $fb->getAccessToken();
        if (strlen($extended_access_token) > 1) {
            $is_page_valid = false;
            $result = $fb->api('/me/accounts');
            foreach ($result['data'] as $account) {
                if ($account['id'] == $company_fanpage_id) {
                    $is_page_valid = true;
                    break;
                }
            }
            if ($is_page_valid) {
                $qry4 = "update tbl_companies set company_fb_access_token='$extended_access_token', company_fb_token_updated_on=NOW() where company_id='$company_id '";
                $db->query($qry4);
                $msg->addMsg(t_lang('M_TXT_TOKEN_SAVED_SUCCESSFULLY'));
            } else {
                file_get_contents($fb->getLogoutUrl());
                $msg->addError(t_lang('M_TXT_INVALID_FANPAGE_ID'));
            }
        } else {
            file_get_contents($fb->getLogoutUrl());
            $msg->addError(t_lang('M_TXT_TOKEN_NOT_SAVED'));
        }
        redirectUser('facebook-update.php');
    } else {
        $scope = array('scope' => array('manage_pages', 'read_stream', 'publish_pages', 'publish_actions'));
        define(FB_LOGIN_URL, $fb->getLoginUrl($scope));
    }
} else {
    define(FB_LOGIN_URL, 'javascript:void()');
}
require_once './header.php';
$arr_bread = array('' => t_lang('M_TXT_FACEBOOK_INFORMATION'));
?>
<td class="right-portion">
    <?php echo getMerchantBreadCrumb($arr_bread); ?>
    <div class="clear"></div>
    <div class="box"></div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?>
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="redtext"><?php echo $msg->display(); ?> </div>
                    <br/>
                    <br/>
                <?php } if (isset($_SESSION['msgs'][0])) { ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
    <div class="box"><div class="title"><?php echo t_lang('M_TXT_STEP') . '1 : ' . t_lang('M_TXT_FACEBOOK_INFORMATION'); ?> </div><div class="content">
            <?php echo $facebook_frm->getFormHtml(); ?>
        </div></div>
    <div class="box">
        <div class="title">
            <?php echo t_lang('M_TXT_STEP') . '2 : ' . t_lang('M_TXT_FACEBOOK_GET_AUTHENTICATE_FIRST'); ?>
        </div>
        <div class="content">
            <a class="gray button" href="<?php echo FB_LOGIN_URL; ?>">
                <?php echo t_lang('M_TXT_FACEBOOK_GET_AUTHENTICATE_FIRST'); ?>
            </a>
        </div>
    </div>
    <?php
    require_once './footer.php';
    exit;
