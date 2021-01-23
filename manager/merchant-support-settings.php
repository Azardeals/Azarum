<?php
require_once './application-top.php';
checkAdminPermission(13);
if (CONF_SEND_MERCHANT_SUPPORT_ALERTS == 1) {
    $msac_checked = true;
} else {
    $msac_checked = false;
}
$arr_selected = explode(',', CONF_MERCHANT_SUPPORT_NOTIFICATION_RECIPIENTS); //admin users who are currently receiving emails
/** Get admin users with merchant support permissions * */
$srch = new SearchBase('tbl_admin', 'a');
$srch->joinTable('tbl_admin_permissions', 'INNER JOIN', 'a.admin_id=ap.ap_admin_id', 'ap');
$srch->addCondition('ap_permission_id', '=', 13);
$srch->addMultipleFields(array('a.admin_id,a.admin_name,a.admin_email'));
$rs = $srch->getResultSet();
$records = $db->fetch_all($rs);
$arr_options = [];
$arr_options[1] = 'Administrator';  //Super Admin has all the permissions by default
foreach ($records as $ele) {
    $arr_options[$ele['admin_id']] = $ele['admin_name'];
}
/* * **** */
/** Merchant support settings form * */
$frm = new Form('frmMerchantSupportSettings', 'frmMerchantSupportSettings');
$frm->setTableProperties('width="100%" class="tbl_form"');
$frm->setLeftColumnProperties('width="30%"');
$frm->setFieldsPerRow(1);
$frm->setJsErrorDisplay('afterfield');
$frm->captionInSameCell(false);
$frm->setAction('?');
$frm->addHiddenField('', 'send_alerts', CONF_SEND_MERCHANT_SUPPORT_ALERTS, 'send_alerts');
$frm->addCheckBox(t_lang('M_FRM_NOTIFY_USERS_OF_NEW_POSTS_BY_MERCHANTS'), 'chk_notify_users', 1, 'chk_notify_users', '', $msac_checked);
$fld = $frm->addCheckBoxes(t_lang('M_FRM_WHO_SHOULD_RECEIVE_NOTIFICATIONS'), 'chk_recipients', $arr_options, $arr_selected, 3, 'class="recipients_block" width="100%"', '');
$fld->html_after_field = '<ul class="errorlist erlist_deal_name"><li><a id="err_recipients" href="javascript:void(0);"></a></li></ul>';
$frm->addButton('', 'btn_submit', t_lang('M_TXT_UPDATE'), 'btn_submit', 'class="inputbuttons"');
/* * *** */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipients_arr = [];
    if (isset($_POST['chk_notify_users'])) {
        $msac_val = 1;
        $recipients_arr = $_POST['chk_recipients'];
        if (isset($recipients_arr)) {
            $recipients_str = implode(',', $recipients_arr);
        }
    } else {
        $msac_val = 0;
    }
    $sql_1 = $db->query("UPDATE tbl_configurations SET conf_val = $msac_val WHERE conf_name = 'conf_send_merchant_support_alerts'");
    $sql_2 = $db->query("UPDATE tbl_configurations SET conf_val = '" . $recipients_str . "' WHERE conf_name = 'conf_merchant_support_notification_recipients'");
    if (!$sql_1 || !$sql_2) {
        $msg->addError($db->getError());
    } else {
        $msg->addMsg(t_lang('M_TXT_SETTINGS_UPDATED'));
    }
    redirectUser();
}
require_once './header.php';
$arr_bread = [
    'index.php' => '<img alt="Home" src="images/home-icon.png">',
    'configurations.php' => t_lang('M_TXT_SETTINGS'),
    '' => t_lang('M_TXT_MERCHANT_SUPPORT_SETTINGS')
];
if ((checkAdminAddEditDeletePermission(7, '', 'add')) && (checkAdminAddEditDeletePermission(7, '', 'edit'))) {
    ?>
    <script type="text/javascript" charset="utf-8">
        var txtselectrecipient = "<?php echo addslashes(t_lang('M_TXT_PLEASE_SELECT_ATLEAST_ONE_RECIPIENT')); ?>";
    </script>
    </div></td>
    <td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>                
        <div class="div-inline">
            <div class="page-name"><?php echo t_lang('M_TXT_MERCHANT_SUPPORT_SETTINGS'); ?></div>
        </div>
        <div class="clear"></div>
        <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
            <div class="box" id="messages">
                <div class="title-msg"> 
                    <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?>
                    <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a>
                </div>
                <div class="content">
                    <?php if (isset($_SESSION['errs'][0])) { ?>
                        <div class="message error"><?php echo $msg->display(); ?></div><br/><br/>
                        <?php
                    }
                    if (isset($_SESSION['msgs'][0])) {
                        ?>
                        <div class="greentext"> <?php echo $msg->display(); ?> </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?> 
        <div class="box">
            <div class="title"> 
                <?php echo t_lang('M_TXT_MERCHANT_SUPPORT_SETTINGS'); ?> 
            </div>
            <div class="content">
                <?php echo $frm->getFormHtml(); ?>
            </div>
        </div>
    </td>
    <?php
}
require_once './footer.php';
?>