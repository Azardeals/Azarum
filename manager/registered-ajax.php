<?php

require_once './application-top.php';
require_once '../site-classes/user-info.cls.php';
checkAdminPermission(8);
if (!checkAdminAddEditDeletePermission(8, '', 'add') || !checkAdminAddEditDeletePermission(8, '', 'edit')) {
    die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
}
$post = getPostedData();
switch (strtoupper($post['mode'])) {
    case 'CHANGEPASSWORD':
        $Src_frm = new Form('changePassword', 'changePassword');
        $Src_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
        $Src_frm->setFieldsPerRow(1);
        $Src_frm->setJsErrorDisplay('afterfield');
        $Src_frm->setValidatorJsObjectName('frmValidator');
        $Src_frm->setExtra('onsubmit="submitChangePassword(this, frmValidator); return(false);"');
        $Src_frm->captionInSameCell(false);
        $Src_frm->addTextBox(t_lang('M_TXT_USERNAME'), 'user_name', '', '', 'readonly="readonly"');
        $Src_frm->addTextBox(t_lang('M_FRM_EMAIL_ADDRESS'), 'user_email', '', '', 'readonly="readonly"');
        $Src_frm->addTextBox(t_lang('M_FRM_PASSWORD'), 'user_password', '', '', '')->requirements()->setRequired();
        $Src_frm->addHiddenField('', 'mode', 'updatechangepassword');
        $Src_frm->addHiddenField('', 'user_id', $post['user_id']);
        $fld = $Src_frm->addSubmitButton('', 'update_btn', t_lang('M_TXT_UPDATE'), '', ' class="medium"');
        $record = new TableRecord('tbl_users');
        if (!$record->loadFromDb('user_id=' . $post['user_id'], true)) {
            $msg->addError($record->getError());
        } else {
            $arr = $record->getFlds();
            $arr['user_password'] = '';
            $Src_frm->fill($arr);
        }
        echo '<div class="box"><div class="title"> ' . t_lang('M_TXT_CHANGE_PASSWORD') . ' </div><div class="content">' . $Src_frm->getFormHtml() . '</div></div>';
        break;
    case 'UPDATECHANGEPASSWORD':
        if ($post['user_password'] != "") {
            if (!$db->update_from_array('tbl_users', array('user_password' => md5($post['user_password'])), 'user_id=' . $post['user_id'])) {
                $msg->addError($db->getError());
            }
            $msg->addMsg(t_lang('M_TXT_PASSWORD_UPDATED'));
            die('<div class="box" id="messages">
                    <div class="title-msg"> ' . t_lang('M_TXT_SYSTEM_MESSAGES') . '</div>
                    <div class="content">
                      <div class="greentext">' . $msg->display() . '</div>
                    </div>
                  </div>');
        } else {
            $msg->addError(t_lang('M_TXT_PASSWORD_NOT_UPDATED'));
            die('<div class="box" id="messages">
                    <div class="title-msg"> ' . t_lang('M_TXT_SYSTEM_MESSAGES') . '</div>
                    <div class="content">
                      <div class="redtext">' . $msg->display() . '</div>
                    </div>
                  </div>');
        }
        break;
    case 'UPDATEWALLET':
        $Src_frm = new Form('updateWallet', 'updateWallet');
        $Src_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
        $Src_frm->setFieldsPerRow(1);
        $Src_frm->setJsErrorDisplay('afterfield');
        $Src_frm->setValidatorJsObjectName('frmValidator');
        $Src_frm->setExtra('onsubmit="submitUserUpdateWallet(this, frmValidator); return(false);"');
        $Src_frm->captionInSameCell(false);
        $Src_frm->addHTML('<strong>' . t_lang('M_TXT_DEBIT') . ':</strong> ' . t_lang('M_TXT_DEBIT_USER_WALLET') . '<br/>
        <strong>' . t_lang('M_TXT_CREDIT') . ':</strong> ' . t_lang('M_TXT_CREDIT_USER_WALLET'), '', '', '', true)->merge_caption = 2;
        $Src_frm->addTextBox(t_lang('M_TXT_CURRENT_WALLET_AMOUNT'), 'user_wallet_amount', '', '', '');
        $Src_frm->addRadioButtons(t_lang('M_TXT_ENTRY_TYPE'), 'entry_type', array('1' => t_lang('M_TXT_DEBIT'), '2' => t_lang('M_TXT_CREDIT')), '1', 2, 'width="100%"', '');
        $Src_frm->addTextBox(t_lang('M_TXT_AMOUNT'), 'user_edit_wallet_amount', '', '', '')->requirements()->setRequired();
        $Src_frm->addTextArea(t_lang('M_TXT_PARTICULARS'), 'uwh_particulars', '', '', '')->requirements()->setRequired();
        $Src_frm->addHiddenField('', 'mode', 'updateuserwallet');
        $Src_frm->addHiddenField('', 'user_id', $post['user_id']);
        $fld = $Src_frm->addSubmitButton('', 'update_btn', t_lang('M_TXT_UPDATE'), '', ' class="medium"');
        $record = new TableRecord('tbl_users');
        if (!$record->loadFromDb('user_id=' . $post['user_id'], true)) {
            $msg->addError($record->getError());
        } else {
            $arr = $record->getFlds();
            $arr['user_password'] = '';
            $Src_frm->fill($arr);
        }
        $Usermsg = '<div class="box"><div class="title">' . t_lang('M_TXT_UPDATE_WALLET') . ' </div><div class="content">' . $Src_frm->getFormHtml() . '</div></div>';
        echo $Usermsg;
        break;
    case 'UPDATEUSERWALLET':
        $user = new userInfo();
        $user->updateUserWallet($post['entry_type'], $post['user_wallet_amount'], $post['user_edit_wallet_amount'], $post['uwh_particulars'], $post['user_id']);
        /* die($Usermsg); */
        break;
    case 'DISAPPROVEUSER' :
        $affiliate_id = $post['affiliate_id'];
        $db->query("UPDATE tbl_affiliate set affiliate_status = 0 WHERE affiliate_id =$affiliate_id");
        dieJsonSuccess(t_lang('M_TXT_DISAPPROVE'));
        break;
    case 'APPROVEUSER' :
        $affiliate_id = $post['affiliate_id'];
        $db->query("UPDATE tbl_affiliate set affiliate_status = 1 WHERE affiliate_id =$affiliate_id");
        dieJsonSuccess(t_lang('M_TXT_APPROVE'));
        break;
    case 'DISAPPROVEREPUSER' :
        $rep_id = $post['rep_id'];
        $db->query("UPDATE tbl_representative set 	rep_status = 0 WHERE rep_id =$rep_id");
        break;
    case 'APPROVEREPUSER' :
        $rep_id = $post['rep_id'];
        $db->query("UPDATE tbl_representative set 	rep_status = 1 WHERE rep_id =$rep_id");
        break;
    case 'PAYNOW':
        $post = getPostedData();
        $frm = new Form('payfrm', 'payfrm');
        $frm->setTableProperties('border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
        $frm->setFieldsPerRow(1);
        $frm->captionInSameCell(false);
        $frm->setJsErrorDisplay('afterfield');
        $frm->setValidatorJsObjectName('frmValidator');
        $frm->setExtra('onsubmit="submitPayNow(this, frmValidator); return(false);"');
        $frm->addHTML('<strong>Debit:</strong> When you actually make the payment to representative.<br/>
        <strong>Credit:</strong> When you want to give credit to the representative for their commissions.', '', '', '', true)->merge_caption = 2;
        $frm->addRadioButtons(t_lang('M_TXT_ENTRY_TYPE'), 'entry_type', array('1' => 'Debit', '2' => 'Credit'), '', 2, 'width="100%"', '');
        $frm->addTextBox(t_lang('M_TXT_AMOUNT'), 'rwh_amount', '', '', '')->requirements()->setRequired();
        $frm->addTextArea(t_lang('M_TXT_PARTICULARS'), 'rwh_particulars', '', '', '')->requirements()->setRequired();
        $frm->addHiddenField('', 'rwh_rep_id', $post['rep_id']);
        $frm->addHiddenField('', 'mode', 'submitpaynow');
        $fld = $frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SUBMIT'), '', ' class="inputbuttons"');
        echo '<div class="box"><div class="title"> ' . t_lang('M_TXT_ADD_TRANSACTION') . ' </div><div class="content">' . $frm->getFormHtml() . '</div></div>';
        break;
    case 'SUBMITPAYNOW':
        $post = getPostedData();
        if (!isset($post['entry_type'])) {
            $msg->addError(t_lang('M_TXT_PLEASE_SELECT_ENTRY_TYPE_FIRST'));
            die('<div class="box" id="messages">
                    <div class="title-msg"> ' . t_lang('M_TXT_SYSTEM_MESSAGES') . '</div>
                    <div class="content">
                      <div class="greentext">' . $msg->display() . '</div>
                       
                    </div>
                  </div>');
        }
        payToRepresentativeByAdmin($post);
        die('<div class="box" id="messages">
                    <div class="title-msg"> ' . t_lang('M_TXT_SYSTEM_MESSAGES') . '</div>
                    <div class="content">
                      <div class="greentext">' . $msg->display() . '</div>
                       
                    </div>
                  </div>');
        break;
}
exit(0);
