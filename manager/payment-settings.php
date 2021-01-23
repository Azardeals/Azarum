<?php
require_once './application-top.php';
checkAdminPermission(7);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    if (!$db->update_from_array('tbl_payment_options', ['po_active' => $post['paypal_active'], 'po_account_id' => $post['paypal_po_account_id']], 'po_id=1')) {
        $msg->addError($db->getError());
    }
    if (!$db->update_from_array('tbl_payment_options', ['po_active' => $post['authorize_active'], 'po_account_id' => $post['authorize_po_account_id'], 'po_key' => $post['authorize_po_key']], 'po_id=2')) {
        $msg->addError($db->getError());
    }
    if (!$db->update_from_array('tbl_payment_options', ['po_active' => $post['cim_active'], 'po_account_id' => $post['cim_po_account_id'], 'po_key' => $post['cim_po_key']], 'po_id=3')) {
        $msg->addError($db->getError());
    }
    $msg->addMsg(t_lang('M_TXT_SETTINGS_UPDATED'));
    redirectUser();
}
$frm = new Form('frmPaymentSettings', 'frmPaymentSettings');
$frm->setTableProperties('width="100%" class="tbl_form"');
$frm->setFieldsPerRow(1);
$frm->captionInSameCell(false);
$frm->setAction('?');
$payment_settings = [];
$rs = $db->query("select * from tbl_payment_options");
while ($row = $db->fetch($rs)) {
    $payment_settings[$row['po_id']] = $row;
}
$frm->addHTML('', '', '<strong>' . t_lang('M_TXT_PAYPAL_SETTINGS') . '</strong>')->merge_cells = 3;
$frm->addSelectBox(t_lang('M_TXT_ENABLE'), 'paypal_active', array('Disable', 'Enable'), $payment_settings[1]['po_active'], 'class="input"', '');
$frm->addTextBox(t_lang('M_FRM_MERCHANT_EMAIL_ID'), 'paypal_po_account_id', $payment_settings[1]['po_account_id']);
$frm->addHTML('', '', '<strong>' . t_lang('M_FRM_AUTHORIZED_NET_SETTINGS') . '</strong>')->merge_cells = 3;
$frm->addSelectBox(t_lang('M_TXT_ENABLE'), 'authorize_active', array('Disable', 'Enable'), $payment_settings[2]['po_active'], 'class="input"', '');
$frm->addTextBox(t_lang('M_FRM_LOGIN_ID'), 'authorize_po_account_id', $payment_settings[2]['po_account_id']);
$frm->addTextBox(t_lang('M_FRM_TRANSACTION_KEY'), 'authorize_po_key', $payment_settings[2]['po_key']);
$frm->addHTML('', '', '<strong>' . t_lang('M_FRM_CIM_SETTINGS') . '</strong>')->merge_cells = 3;
$frm->addSelectBox(t_lang('M_TXT_ENABLE'), 'cim_active', array('Disable', 'Enable'), $payment_settings[3]['po_active'], 'class="input"', '');
$frm->addTextBox(t_lang('M_FRM_LOGIN_ID'), 'cim_po_account_id', $payment_settings[3]['po_account_id']);
$frm->addTextBox(t_lang('M_FRM_TRANSACTION_KEY'), 'cim_po_key', $payment_settings[3]['po_key']);
$frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_UPDATE'), '', 'class="inputbuttons"');
require_once './header.php';
$arr_bread = [
    'index.php' => '<img alt="Home" src="images/home-icon.png">',
    'configurations.php' => t_lang('M_TXT_SETTINGS'),
    '' => t_lang('M_TXT_PAYMENT_GATEWAY_SETTINGS')
];
?>
<ul class="nav-left-ul">
    <li><a href="configurations.php" ><?php echo t_lang('M_TXT_GENERAL_SETTINGS'); ?></a></li>
    <li><a href="payment-settings.php" class="selected"><?php echo t_lang('M_TXT_PAYMENT_GATEWAY_SETTINGS'); ?></a></li>
    <li><a href="email-templates.php"><?php echo t_lang('M_TXT_EMAIL_TEMPLATES'); ?></a></li>
    <li><a href="language-managment.php"><?php echo t_lang('M_TXT_LANGUAGE_MANAGEMENT'); ?></a></li>
    <li><a href="cities.php" ><?php echo t_lang('M_TXT_CITIES_MANAGEMENT'); ?></a></li>
    <!--li><a href="database-backup.php" ><?php echo t_lang('M_TXT_DATABASE_BACKUP_RESTORE'); ?></a></li-->
</ul>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_PAYMENT_SETTINGS'); ?> </div>
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?>
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="message error"><?php echo $msg->display(); ?> </div><br/><br/>
                <?php } if (isset($_SESSION['msgs'][0])) { ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
    <?php if ((checkAdminAddEditDeletePermission(7, '', 'add')) && (checkAdminAddEditDeletePermission(7, '', 'edit'))) { ?>
        <div class="box"><div class="title"> <?php echo t_lang('M_TXT_PAYMENT_SETTINGS'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
        <?php } ?>
</td>
<?php require_once './footer.php'; ?>
