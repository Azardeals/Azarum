<?php
require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
if ($_SESSION['cityname'] != "") {
    $cityname = convertStringToFriendlyUrl($_SESSION['cityname']);
} else {
    $cityname = 1;
}
if (!isCompanyUserLogged()) {
    redirectUser(CONF_WEBROOT_URL . 'merchant/login.php');
}
$company_id = $_SESSION['logged_user']['company_id'];
$TotalDeals = $db->query("SELECT SQL_CALC_FOUND_ROWS * FROM tbl_deals where deal_company=$company_id");
while ($rowDeal = $db->fetch($TotalDeals)) {
    $objDeal = new DealInfo($rowDeal['deal_id']);
    $sold += $objDeal->getFldValue('sold');
    $price += $objDeal->getFldValue('price') * $objDeal->getFldValue('sold');
}
$couponPurchased = $db->query("SELECT SQL_CALC_FOUND_ROWS * FROM tbl_company_coupon_purchased where ccp_company_id=$company_id");
$row = $db->fetch($couponPurchased);
$fetchVal['ccp_amount'] = $row['ccp_amount'];
$fetchVal['ccp_coupon'] = $row['ccp_coupon'];
$fetchVal['ccp_id'] = $row['ccp_id'];
$fetchVal['ccp_deal_id'] = $row['ccp_deal_id'];
$arr = $_SESSION['logged_user'];
$fetchVal['charity_comapny_id'] = $_SESSION['logged_user']['company_id'];
$frm = new Form('frmComapnyCouponPurchased', 'frmComapnyCouponPurchased');
$frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0"  class="tbl_form" width="100%"');
$frm->setLeftColumnProperties('class="medium"');
$frm->captionInSameCell(false);
$frm->addHTML('<b>' . t_lang('M_FRM_ACTUAL_TOTAL_COUPON_SOLD') . '</b>', '', $sold, false);
$frm->addHTML('<b>' . t_lang('M_FRM_ACTUAL_TOTAL_AMOUNT') . '</b>', '', number_format($price, 2), false);
$frm->addTextBox(t_lang('M_FRM_TOTAL_COUPON'), 'ccp_coupon', '', 'ccp_coupon', 'class="medium"');
$frm->addTextBox(t_lang('M_FRM_TOTAL_AMOUNT'), 'ccp_amount', '', 'ccp_amount', 'class="medium"');
$frm->addHiddenField('', 'mode', 'search');
$frm->addHiddenField('', 'ccp_id', $fetchVal['ccp_id']);
$fld = $frm->addSubmitButton('', 'btn_search', t_lang('M_TXT_UPDATE'), '', ' class="inputbuttons"');
$frm->fill($fetchVal);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    if ($frm->validate($post)) {
        $arr_updates = array('ccp_amount' => $post['ccp_amount'], 'ccp_coupon' => $post['ccp_coupon'], 'ccp_id' => $post['ccp_id']);
        $record = new TableRecord('tbl_company_coupon_purchased');
        $record->assignValues($arr_updates);
        $record->setFldValue('ccp_company_id', $company_id);
        $success = ($post['ccp_id'] > 0) ? $record->update('ccp_id=' . $post['ccp_id']) : $record->addNew();
        if ($success) {
            $msg->addMsg(t_lang('M_TXT_COMP_COUPOUN_PURCHASED'));
            redirectUser(CONF_WEBROOT_URL . 'merchant/merchant-coupon-purchased.php');
        } else {
            $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
            $frm->fill($post);
        }
    } else {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error) {
            $msg->addError($error);
        }
        $frm->fill($post);
    }
}
require_once './header.php';
?>
</div></td>
<td class="right-portion">
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_VOUCHER_PURCHASED'); ?> </div>
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?>
        <div class="box" id="messages">
            <div class="title-msg"><?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
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
    <div class="box"><div class="title"><?php echo t_lang('M_TXT_VOUCHER_PURCHASED'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
</td>
<?php
require_once './footer.php';
exit;
