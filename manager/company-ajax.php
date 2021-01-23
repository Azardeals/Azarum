<?php

require_once './application-top.php';
require_once '../includes/navigation-functions.php';
checkAdminPermission(3);
$post = getPostedData();
$get = getQueryStringData();
$mode = (isset($post['mode'])) ? $post['mode'] : $get['mode'];
switch (strtoupper($mode)) {
    case 'CHANGEPASSWORD':
        $Src_frm = new Form('changePassword', 'changePassword');
        $Src_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
        $Src_frm->setFieldsPerRow(1);
        $Src_frm->setJsErrorDisplay('afterfield');
        $Src_frm->setValidatorJsObjectName('frmValidator');
        $Src_frm->setExtra('onsubmit="submitChangePassword(this, frmValidator); return(false);"');
        $Src_frm->captionInSameCell(false);
        $Src_frm->addTextBox(t_lang('M_TXT_USERNAME'), 'company_name', '', '', 'readonly="readonly"');
        $Src_frm->addTextBox(t_lang('M_FRM_EMAIL_ADDRESS'), 'company_email', '', '', 'readonly="readonly"');
        $Src_frm->addTextBox(t_lang('M_FRM_PASSWORD'), 'company_password', '', '', '')->requirements()->setRequired();
        $Src_frm->addHiddenField('', 'mode', 'updatechangepassword');
        $Src_frm->addHiddenField('', 'company_id', $post['company_id']);
        $fld = $Src_frm->addSubmitButton('', 'update_btn', t_lang('M_TXT_UPDATE'), '', ' class="medium"');
        $record = new TableRecord('tbl_companies');
        if (!$record->loadFromDb('company_id=' . $post['company_id'], true)) {
            $msg->addError($record->getError());
        } else {
            $arr = $record->getFlds();
            $arr['company_password'] = '';
            $Src_frm->fill($arr);
        }
        echo '<div class="box"><div class="title"> ' . t_lang('M_TXT_CHANGE_PASSWORD') . ' </div><div class="content">' . $Src_frm->getFormHtml() . '</div></div>';
        break;
    case 'UPDATECHANGEPASSWORD':
        if ($post['company_password'] != "") {
            if (!$db->update_from_array('tbl_companies', array('company_password' => md5($post['company_password'])), 'company_id=' . $post['company_id'])) {
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
    case 'DISAPPROVEUSER' :
        $company_id = $post['company_id'];
        if (canDeleteCompany($company_id) == 0) {
            $db->query("UPDATE tbl_companies set company_active = 0 WHERE company_id =$company_id");
            $rsCompany = $db->query("select * from tbl_companies where company_id=$company_id");
            $row = $db->fetch($rsCompany);
            $rs = $db->query("select * from tbl_email_templates where tpl_id=37");
            $row_tpl = $db->fetch($rs);
            $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
            $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
            $arr_replacements = array(
                'xxcompany_namexx' => $row['company_name'],
                'xxuser_namexx' => $row['company_email'],
                'xxemail_addressxx' => $row['company_email'],
                'xxsite_namexx' => CONF_SITE_NAME,
                'xxserver_namexx' => $_SERVER['SERVER_NAME'],
                'xxwebrooturlxx' => CONF_WEBROOT_URL,
                'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL
            );
            foreach ($arr_replacements as $key => $val) {
                $subject = str_replace($key, $val, $subject);
                $message = str_replace($key, $val, $message);
            }
            if ($row_tpl['tpl_status'] == 1) {
                sendMail($row['company_email'], $subject, emailTemplateSuccess($message), $headers);
            }
            echo '1';
        } else {
            echo '0';
        }
        break;
    case 'APPROVEUSER' :
        $company_id = $post['company_id'];
        if ($company_id) {
            $srch = new SearchBase('tbl_companies', 'd');
            $srch->addCondition('company_id', '=', intval($company_id));
            //$srch->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=c.company_id', 'c');
            $srch->addMultipleFields(array('company_deal_commission_percent'));
            $rs = $srch->getResultSet();
            $comp = $db->fetch($rs);
            if (CONF_ADMIN_COMMISSION_TYPE == 3) { //echo $comp['company_deal_commission_percent']; exit;
                if ($comp['company_deal_commission_percent'] == '0.00') {
                    $arr = array('status' => 2, 'redirectTo' => CONF_WEBROOT_URL . 'manager/companies.php?edit=' . $company_id, 'id' => $company_id);
                    die(convertToJson($arr));
                }
            }
            $db->query("UPDATE tbl_companies set company_active = 1 WHERE company_id =$company_id");
            $rsCompany = $db->query("select * from tbl_companies where company_id=$company_id");
            $row = $db->fetch($rsCompany);
            $rs = $db->query("select * from tbl_email_templates where tpl_id=36");
            $row_tpl = $db->fetch($rs);
            $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
            $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
            $arr_replacements = array(
                'xxcompany_namexx' => $row['company_name'],
                'xxuser_namexx' => $row['company_email'],
                'xxemail_addressxx' => $row['company_email'],
                'xxsite_namexx' => CONF_SITE_NAME,
                'xxserver_namexx' => $_SERVER['SERVER_NAME'],
                'xxwebrooturlxx' => CONF_WEBROOT_URL,
                'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'merchant/'
            );
            foreach ($arr_replacements as $key => $val) {
                $subject = str_replace($key, $val, $subject);
                $message = str_replace($key, $val, $message);
            }
            if ($row_tpl['tpl_status'] == 1) {
                sendMail($row['company_email'], $subject, emailTemplateSuccess($message), $headers);
            }
            $arr = array('status' => 1);
            die(convertToJson($arr));
        }
        break;
    case 'DELETECOMPANY':
        echo canDeleteCompany($post['company']);
        break;
    case 'COMPANYLOCATIONS':
        $srch = new SearchBase('tbl_company_addresses', 'ca');
        $srch->addCondition('company_id', '=', $_POST['company']);
        $srch->addMultipleFields(array('ca.*'));
        if ($_SESSION['lang_fld_prefix'] == '') {
            $srch->addFld("CONCAT(company_address_line1, '<br/>', company_address_line2, '<br/>', company_address_line3,  '-', company_address_zip, ' ') AS address");
        } else {
            $srch->addFld("CONCAT(company_address_line1_lang1, '<br/>', company_address_line2_lang1, '<br/>', company_address_line3_lang1,  '-', company_address_zip, ' ') AS address");
        }
        $rs_listing = $srch->getResultSet();
        $arr_listing_fields = array(
            'listserial' => t_lang('M_TXT_SR_NO'),
            'address' . $_SESSION['lang_fld_prefix'] => t_lang('M_TXT_ADDRESS')
        );
        echo '<table class="tbl_data" width="100%">
            <thead>
            <tr>
            <th width="20%">' . t_lang('M_TXT_SR_NO') . '</th>
            <th width="80%">' . t_lang('M_TXT_ADDRESS') . '</th>';
        echo '</tr>
            </thead>';
        for ($listserial = ($page - 1) * $pagesize + 1; $row = $db->fetch($rs_listing); $listserial++) {
            if ($listserial % 2 == 0) {
                $even = 'even';
            } else {
                $even = '';
            }
            echo '<tr >';
            foreach ($arr_listing_fields as $key => $val) {
                echo '<td>';
                switch ($key) {
                    case 'listserial':
                        echo $listserial;
                        break;
                    case 'address_lang1':
                        echo '<strong>' . $arr_lang_name[0] . '</strong>' . ' ' . $row['address'] . '<br/>';
                        echo '<strong>' . $arr_lang_name[1] . '</strong>' . ' ' . $row['address_lang1'];
                        break;
                    default:
                        echo $row[$key];
                        break;
                }
                echo '</td>';
            }
            echo '</tr>';
        }
        if ($db->total_records($rs_listing) == 0)
            echo '<tr><td colspan="' . count($arr_listing_fields) . '">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
        echo '</table>';
        break;
    case 'ADDTRANSACTION':
        $post = getPostedData();
        $frm = new Form('payfrm', 'payfrm');
        $frm->setTableProperties('border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
        $frm->setFieldsPerRow(1);
        $frm->captionInSameCell(false);
        $frm->setJsErrorDisplay('afterfield');
        $frm->setValidatorJsObjectName('frmValidator');
        $frm->setExtra('onsubmit="submitAddTransaction(this, frmValidator); return(false);"');
        $frm->addHTML('<strong>' . t_lang('M_TXT_DEBIT') . ':</strong>' . t_lang('M_TXT_MAKE_PAYMENT_TO_MERCHANT') . '<br/>
        <strong>' . t_lang('M_TXT_CREDIT') . ':</strong> ' . t_lang('M_TXT_WHEN_WANT_TO_GIVE_CREDIT'), '', '', '', true)->merge_caption = 2;
        $frm->addRadioButtons(t_lang('M_TXT_ENTRY_TYPE'), 'entry_type', array('1' => t_lang('M_TXT_DEBIT'), '2' => t_lang('M_TXT_CREDIT')), '', 2, 'width="100%"', '');
        $frm->addTextBox(t_lang('M_TXT_AMOUNT'), 'cwh_amount', '', '', '')->requirements()->setRequired();
        $frm->addTextArea(t_lang('M_TXT_PARTICULARS'), 'cwh_particulars', '', '', '')->requirements()->setRequired();
        $frm->addHiddenField('', 'cwh_company_id', $_POST['company']);
        $frm->addHiddenField('', 'deal', $_POST['deal']);
        $frm->addHiddenField('', 'payable_amount', $post['payable_amount']);
        $frm->addHiddenField('', 'mode', 'submitAddTransaction');
        $fld = $frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SUBMIT'), '', ' class="inputbuttons"');
        echo '<div class="box">
                <div class="title"> ' . t_lang('M_TXT_ADD_TRANSACTION') . '<span class="fr">' . t_lang('M_TXT_PAYABLE_AMOUNT_:') . ' <strong>' . CONF_CURRENCY . number_format($post['payable_amount'], 2) . CONF_CURRENCY_RIGHT . '</strong></span></div>
                <div class="content">' . $frm->getFormHtml() . '</div>
              </div>';
        break;
    case 'SUBMITADDTRANSACTION':
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
        payToMerchantByAdmin($post);
        die('<div class="boxes" id="messages">
                <div class="title-msg"> ' . t_lang('M_TXT_SYSTEM_MESSAGES') . '</div>
                <div class="content">
                    <div class="greentext">' . $msg->display() . '</div>                       
                </div>
            </div>');
        break;
    case 'DEALWISETRANSACTION':
        $post = getPostedData();
        $srch = new SearchBase('tbl_company_wallet_history', 'cwh');
        if ($post['deal_id'] > 0) {
            $srch->addCondition('cwh_untipped_deal_id', '=', $post['deal_id']);
        } else {
            $srch->addCondition('cwh_company_id', '=', $post['company_id']);
        }
        $srch->addFld('cwh.*');
        $srch->addFld('CASE WHEN cwh_amount > 0 THEN cwh_amount ELSE 0 END as added');
        $srch->addFld('CASE WHEN cwh_amount <= 0 THEN ABS(cwh_amount) ELSE 0 END as used');
        $rs_listing = $srch->getResultSet();
        $pagestring = '';
        $pages = $srch->pages();
        $arr_listing_fields = array(
            'listserial' => t_lang('M_TXT_S_N'),
            'cwh_particulars' => t_lang('M_TXT_PARTICULARS'),
            'added' => t_lang('M_TXT_CREDIT'),
            'used' => t_lang('M_TXT_DEBIT'),
            'balance' => t_lang('M_TXT_BALANCE'),
            'cwh_time' => t_lang('M_TXT_DATE')
        );
        echo '<thead>
<tr>
<tr>';
        foreach ($arr_listing_fields as $key => $val)
            echo '<th' . (($key == 'added' || $key == 'used' || $key == 'wh_time') ? '  width="15%"' : '') . (($key == 'balance' ) ? '   width="12%"' : '') . '>' . $val . '</th>';
        echo '</tr>';
        $arr = $db->fetch_all($rs_listing);
        $balance = 0;
        foreach ($arr as $key => $row) {
            $balance += $row['cwh_amount'];
            $arr[$key]['cwh_time'] = displayDate($row['cwh_time'], true, true, '');
            $arr[$key]['added'] = $row['added'];
            $arr[$key]['used'] = $row['used'];
            $arr[$key]['balance'] = $balance;
        }
        $arr = array_reverse($arr);
        $listserial = ($page - 1) * $pagesize + 1;
        $balanceNew = 0;
        foreach ($arr as $row) {
            /* $balanceNew = $total; */
            echo '<tr  >';
            foreach ($arr_listing_fields as $key => $val) {
                echo '<td ' . (($key == 'added' || $key == 'used') ? ' ' : '') . '>';
                switch ($key) {
                    case 'listserial':
                        echo $listserial;
                        break;
                    case 'cwh_time':
                        echo $row[$key];
                        break;
                    case 'added':
                        echo CONF_CURRENCY . number_format(($row['added']), 2) . CONF_CURRENCY_RIGHT;
                        break;
                    case 'used':
                        echo CONF_CURRENCY . number_format($row['used'], 2) . CONF_CURRENCY_RIGHT;
                        break;
                    case 'balance':
                        echo CONF_CURRENCY . number_format(($row['balance']), 2) . CONF_CURRENCY_RIGHT;
                        break;
                    default:
                        echo $row[$key];
                        break;
                }
                echo '</td>';
            }
            echo '</tr>';
            $listserial++;
        }
        if (count($arr) == 0) {
            echo '<tr><td colspan="' . count($arr_listing_fields) . '" >' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
        }
        break;
    case 'GETDEALNAME':
        $post = getPostedData();
        $rs = $db->query("select deal_name from tbl_deals where deal_id=" . $post['deal_id']);
        $row = $db->fetch($rs);
        echo t_lang('M_TXT_TRANSACTION_FILTERED_FOR') . ': ' . $row['deal_name'];
        break;
}
