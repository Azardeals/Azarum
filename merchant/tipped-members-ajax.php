<?php

require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
if (!isCompanyUserLogged()) {
    redirectUser(CONF_WEBROOT_URL . 'merchant/login.php');
}
$post = getPostedData();
$mode = $post['mode'];
switch ($mode) {
    case 'ShippingDetails':
        $length = strlen($post['v']);
        if ($length > 13) {
            $order_no = substr($post['v'], 0, 13);
            $LastVouvherNo = ($length - 13);
            $voucher_no = substr($post['v'], 13, $LastVouvherNo);
        } else {
            die(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        $srch = new SearchBase('tbl_coupon_mark');
        $srch->addCondition('cm_order_id', '=', $order_no);
        $srch->addCondition('cm_counpon_no', '=', $voucher_no);
        $srch->addMultipleFields(array('cm_counpon_no', 'cm_order_id', 'cm_status', 'cm_id', 'cm_shipping_status', 'cm_shipping_date', 'cm_shipping_details'));
        $rs = $srch->getResultSet();
        if (!$row = $db->fetch($rs)) {
            die(t_lang('M_ERROR_NO_RECORD_FOUND'));
        }
        $row['cm_shipping_date'] = date('d-m-Y', strtotime($row['cm_shipping_date']));
        $frm = new Form('frmShipping', 'frmShipping');
        $frm->setValidatorJsObjectName('frmShippingValidator');
        $frm->setJsErrorDisplay('afterfield');
        $frm->setTableProperties('width="100%" class="tbl_form"');
        $frm->setRequiredStarWith('caption');
        $arr_options = array(
            0 => 'Pending',
            1 => 'Shipped',
            2 => 'Delivered'
        );
        $frm->addSelectBox('Shipping Status', 'cm_shipping_status', $arr_options, '', '', '', '');
        $frm->addDateField('Shipping Date', 'cm_shipping_date', '', '', '');
        $frm->addTextArea('Shipping Detail/Information', 'cm_shipping_details', '', '', 'rows = "8" cols="50"');
        $arr_options = array(
            $row['cm_order_id'] => 'All Vouchers Related to the Order',
            $row['cm_order_id'] . $row['cm_counpon_no'] => 'Only for Voucher ' . $row['cm_order_id'] . $row['cm_counpon_no']
        );
        $frm->addHiddenField('', 'shipping_effect_to', $row['cm_order_id'] . $row['cm_counpon_no'], '', '');
        $frm->addSubmitButton('', 'btn_submit', 'Submit', 'btn_submit', '');
        $frm->addHiddenField('', 'mode', 'saveShippingDetails', 'saveShippingDetails', '');
        $frm->addHiddenField('', 'cm_order_id', '', '', '');
        $frm->addHiddenField('', 'cm_counpon_no', '', '', '');
        $frm->setOnSubmit('shippingSubmit(this, frmShippingValidator); return(false);');
        if ($row['cm_shipping_date'] == '0000-00-00' || $row['cm_shipping_date'] == '01-01-1970' || $row['cm_shipping_date'] == '30-11--0001') {
            unset($row['cm_shipping_date']);
        }
        $frm->fill($row);
        die($frm->getFormHtml());
        break;
    case 'saveShippingDetails':
        $order_id = $post['cm_order_id'];
        $coupon_no = $post['cm_counpon_no'];
        $shipping_effect_to = $post['shipping_effect_to'];
        if (!isset($order_id) || !isset($coupon_no) || $order_id == '' || $coupon_no == '') {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        $length = strlen($shipping_effect_to);
        if ($length > 13) {
            $order_no = substr($shipping_effect_to, 0, 13);
            $LastVouvherNo = ($length - 13);
            $voucher_no = substr($shipping_effect_to, 13, $LastVouvherNo);
        } else if ($length == 13) {
            $order_no = $shipping_effect_to;
        } else {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
            return false;
        }
        $data_to_update = array(
            'cm_shipping_status' => $post['cm_shipping_status'],
            'cm_shipping_details' => $post['cm_shipping_details']
        );
        if ($post['cm_shipping_date'] == '') {
            $data_to_update['cm_shipping_date'] = '0000-00-00';
        } else {
            $data_to_update['cm_shipping_date'] = date('Y-m-d', strtotime($post['cm_shipping_date']));
        }
        if ($length > 13) {
            $arr_whr = array('smt' => 'cm_order_id = ? AND cm_counpon_no = ?', 'vals' => array($post['cm_order_id'], $post['cm_counpon_no']), 'execute_mysql_functions' => false);
        }
        if ($length == 13) {
            $arr_whr = array('smt' => 'cm_order_id = ?', 'vals' => array($post['cm_order_id']), 'execute_mysql_functions' => false);
        }
        $db->update_from_array('tbl_coupon_mark', $data_to_update, $arr_whr, true);
        switch ($post['cm_shipping_status']) {
            case '0':
                $shipping_status_text = 'Pending';
                break;
            case '1':
                $shipping_status_text = 'Shipped';
                break;
            case '2':
                $shipping_status_text = 'Delivered';
                break;
            default:
                $shipping_status_text = '';
        }
        $arr = array('status' => 1, 'msg' => t_lang('M_TXT_SHIPPING_DETAILS_UPDATED'), 'shipping_status_text' => $shipping_status_text);
        if ($length == 13) {
            $arr['page_reload'] = 1;
        } else {
            $arr['page_reload'] = 0;
        }
        die(convertToJson($arr));
        break;
    case 'sendlinkForm':
        $frm = new Form('user_msg_form', 'user_msg_form');
        $frm->setValidatorJsObjectName('frmSendlinkValidator');
        $frm->setJsErrorDisplay('afterfield');
        $frm->setTableProperties('width="100%" class="tbl_form"');
        $frm->setRequiredStarWith('caption');
        $frm->addRequiredField(t_lang('M_FRM_EMAIL_ADDRESS'), 'recipients', $post['useremail'], 'recipients');
        $frm->addTextArea(t_lang('M_FRM_YOUR_MESSAGE_SUCCESS_PAGE'), 'email_message', '', 'email_message', 'rows = "5" cols="50"')->requirements()->setRequired();
        $frm->addHiddenField('', 'email_subject', 'email_subject', 'email_subject');
        $frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SEND'), 'btn_submit', '');
        $frm->setOnSubmit('sendLinkInfoSubmit(this.email_subject.value, this.recipients.value, this.email_message.value,frmSendlinkValidator); return(false);');
        echo $frm->getFormHtml();
        break;
    case 'sendLinkInfoSubmit':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($_POST['recipients'] != '' && $_POST['email_message']) {
                $recipients = $_POST['recipients'];
                $recipients = str_replace(' ', '', $recipients);
                $recipients_arr = explode(',', $recipients);
                $error = 0;
                foreach ($recipients_arr as $key => $val) {
                    $recipients_arr[$key] = trim($val);
                }
                foreach ($recipients_arr as $val) {
                    if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $val)) {
                        $error = 1;
                    }
                }
                $subject = $_POST['email_subject'];
                $rs1 = $db->query("select * from tbl_email_templates where tpl_id=48");
                $row_tpl = $db->fetch($rs1);
                $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
                $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
                $arr_replacements = array(
                    'xxuser_namexx' => $_POST['recipients'],
                    'xxdownloadablelinkxx' => $_POST['email_message'],
                    'xxrecipientxx' => $row_deal['user_name'],
                    'xxemail_addressxx' => $row_deal['user_email'],
                    'xxsite_namexx' => CONF_SITE_NAME,
                    'xxserver_namexx' => $_SERVER['SERVER_NAME'],
                    'xxwebrooturlxx' => CONF_WEBROOT_URL,
                    'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
                );
                foreach ($arr_replacements as $key => $val) {
                    $subject = str_replace($key, $val, $subject);
                    $message = str_replace($key, $val, $message);
                }
                if ($error != 1) {
                    foreach ($recipients_arr as $val) {
                        sendMail($_POST['recipients'], $subject . ' ' . $order_id, emailTemplateSuccess($message));
                    }
                    die(t_lang('M_TXT_MAIL_SENT'));
                } else {
                    die(t_lang('M_ERROR_EMAIL_ADDRESSES_NOT_VALID'));
                }
            } else {
                die(t_lang('M_ERROR_ENTER_EMAIL_ADDRESS_AND_MESSAGE'));
            }
        }
        break;
}