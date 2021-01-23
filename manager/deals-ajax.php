<?php

require_once './application-top.php';
require_once '../includes/navigation-functions.php';
require_once '../includes/subdeals-functions.php';
require_once '../site-classes/digital-product.cls.php';
checkAdminPermission(5);
$post = getPostedData();
switch (strtoupper($post['mode'])) {
    case 'SALESUMMARY':
        if (!is_numeric($post['id'])) {
            die(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        $objDeal = new DealInfo($post['id']);
        $sold = $objDeal->getFldValue('sold');
        $saleSummary .= '<strong>Sale Summary of "' . $objDeal->getFldValue('deal_name') . '"</strong><br/>';
        $saleSummary .= 'Sold Coupons: ' . $sold . '<br/>';
        $saleSummary .= 'Deal Price: ' . $objDeal->getFldValue('price') . '<br/>';
        $saleSummary .= 'Sale Amount: ' . number_format($objDeal->getFldValue('price') * $sold, 2) . '<br/>';
        $commission = $sold * $objDeal->getFldValue('price') * $objDeal->getFldValue('deal_commission_percent') / 100;
        $saleSummary .= 'Commission @ ' . $objDeal->getFldValue('deal_commission_percent') . '%: ' . number_format($commission, 2) . '<br/>';
        $saleSummary .= 'Bonus: ' . $objDeal->getFldValue('deal_bonus') . '<br/>';
        if ($sold > 0) {
            $saleSummary .= t_lang('M_TXT_TOTAL_EARNING') . ': ' . number_format($commission + $objDeal->getFldValue('deal_bonus'), 2) . '<br/>';
        } else {
            $saleSummary .= t_lang('M_TXT_TOTAL_EARNING') . ': ' . number_format($commission, 2) . '<br/>';
        }
        $company = $commission + $objDeal->getFldValue('deal_bonus');
        $srch = new SearchBase('tbl_deals', 'd');
        $srch->addCondition('deal_deleted', '=', 0);
        $srch->addCondition('deal_id', '=', $post['id']);
        $srch->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=company.company_id', 'company');
        $srch->joinTable('tbl_company_charity', 'INNER JOIN', 'company.company_id=charity.charity_company_id', 'charity');
        $srch->addMultipleFields(['charity.charity_percentage']);
        $rs_listing = $srch->getResultSet();
        $row = $db->fetch($rs_listing);
        $charity_percentage = $row['charity_percentage'];
        if ($charity_percentage > 0 && ($sold > 0)) {
            $payAbleToMerchant = ($sold * $objDeal->getFldValue('price')) - ($commission + $objDeal->getFldValue('deal_bonus'));
            $payToCharity = ( (($payAbleToMerchant * $charity_percentage) / 100));
            $saleSummary .= 'Charity @ ' . $charity_percentage . '<br/>';
            $saleSummary .= 'Pay to Charity:&nbsp;' . number_format($payToCharity, 2) . '<br/>';
            $charity = ( (($payAbleToMerchant * $charity_percentage) / 100));
            if ($charity > 0) {
                $charity = ( (($payAbleToMerchant * $charity_percentage) / 100));
            } else {
                $charity = '0.00';
            }
            $payMerchant = ($payAbleToMerchant - (($payAbleToMerchant * $charity_percentage) / 100));
            if ($payAbleToMerchant > 0) {
                $saleSummary .= t_lang('M_TXT_PAYABLE_TO_MERCHANT') . ':&nbsp;' . number_format($payMerchant, 2);
                $merchant = ($payAbleToMerchant - (($payAbleToMerchant * $charity_percentage) / 100));
                if ($merchant > 0) {
                    $merchant = ($payAbleToMerchant - (($payAbleToMerchant * $charity_percentage) / 100));
                } else {
                    $merchant = '0.00';
                }
            } else {
                $saleSummary .= t_lang('M_TXT_PAYABLE_TO_MERCHANT') . ':&nbsp;' . number_format($payMerchant, 2);
                $merchant = ($payAbleToMerchant + (($payAbleToMerchant * $charity_percentage) / 100));
                if ($merchant > 0) {
                    $merchant = ($payAbleToMerchant - (($payAbleToMerchant * $charity_percentage) / 100));
                } else {
                    $merchant = '0.00';
                }
            }
        } else {
            $charity = 0;
            $merchant = number_format($sold * $objDeal->getFldValue('price') - $commission - $objDeal->getFldValue('deal_bonus'), 2);
            if ($merchant < 0) {
                $merchant = 0;
            }
            $saleSummary .= t_lang('M_TXT_PAYABLE_TO_MERCHANT') . ':&nbsp;' . number_format($merchant, 2) . '<br/>';
            if ($merchant > 0) {
                $merchant = $sold * $objDeal->getFldValue('price') - $commission - $objDeal->getFldValue('deal_bonus');
            } else {
                $merchant = '0.00';
            }
        }
        $tipped_at = displayDate($objDeal->getFldValue('deal_tipped_at'), true);
        if ($tipped_at == '') {
            $saleSummary .= '<div style="color: #f00;"> ' . t_lang('M_TXT_DEAL_IS_NOT_TIPPED_YET') . ($objDeal->getFldValue('deal_min_coupons') - $sold) . t_lang('M_TXT_MORE_TO_BE_SOLD') . '</div> ';
        } else {
            $saleSummary .= t_lang('M_TXT_TIPPED_AT') . ':&nbsp;' . $tipped_at;
        }
        $saleSummary .= "<div id='pie2' style='margin-top:20px; margin-left:20px; width:300px; height:300px;'><img src='" . CONF_WEBROOT_URL . "facebox/loading.gif'></div>";
        if ($charity < 0) {
            $charity = 0;
        }
        if ($merchant < 0) {
            $merchant = 0;
        }
        $arr = array('status' => 1, 'msg' => $saleSummary, 'merchant' => $merchant, 'company' => $company, 'charity' => $charity);
        die(convertToJson($arr));
        break;
    case 'CANCELDEAL':
        if (!is_numeric($post['id'])) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        $rs = $db->query("select deal_status from tbl_deals where deal_id=" . $post['id']);
        if (!$row_deal = $db->fetch($rs)) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        if ($row_deal['deal_status'] == 3) {
            dieJsonError(t_lang('M_MSG_DEAL_ALREADY_CANCELLED'));
        }
        if (!notifyDealCancelation(intval($post['id']))) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        if (!$db->update_from_array('tbl_deals', ['deal_status' => 3], 'deal_id=' . $post['id'])) {
            dieJsonError($db->getError());
        }
        dieJsonSuccess(t_lang('M_TXT_DEAL') . ' ' . t_lang('M_TXT_CANCELLED'));
        break;
    case 'UNREJECTDEAL':
        if (!is_numeric($post['id'])) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        $rs = $db->query("select deal_status from tbl_deals where deal_id=" . $post['id']);
        if (!$row_deal = $db->fetch($rs)) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        if (!$db->update_from_array('tbl_deals', ['deal_status' => 1], 'deal_id=' . $post['id'])) {
            dieJsonError($db->getError());
        }
        dieJsonSuccess(t_lang('M_TXT_DEAL') . ' ' . t_lang('M_TXT_REPOST'));
        break;
    case 'CHECKDEALCOMMISSION':
        if (!is_numeric($post['id'])) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        $srch = new SearchBase('tbl_deals');
        $srch->addCondition('deal_id', '=', intval($post['id']));
        $srch->addCondition('deal_status', '=', 5);
        $srch->addMultipleFields(['deal_id', 'deal_name', 'deal_commission_percent', 'deal_status', 'deal_company']);
        $rs = $srch->getResultSet();
        if (!$row_deal = $db->fetch($rs)) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        $deal_commission_percent = $row_deal['deal_commission_percent'];
        $arr = ['status' => 1, 'commission' => $deal_commission_percent, 'msg' => 'success', 'redirectTo' => CONF_WEBROOT_URL . 'manager/add-deals.php?edit=' . $post['id'], 'id' => $post['id']];
        //$arr = array('status' => 1, 'commission' => $deal_commission_percent, 'msg' => 'success', 'redirectTo' => CONF_WEBROOT_URL . 'manager/companies.php?edit='.$row_deal['deal_company'],'companyId'=>$row_deal['deal_company']);
        die(convertToJson($arr));
        break;
    case 'CHECKCOMPANYCOMMISSION':
        if (!is_numeric($post['id'])) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        $srch = new SearchBase('tbl_deals', 'd');
        $srch->addCondition('deal_id', '=', intval($post['id']));
        $srch->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=c.company_id', 'c');
        $srch->addCondition('deal_status', '=', 5);
        $srch->addMultipleFields(['deal_id', 'deal_name', 'company_deal_commission_percent', 'deal_status', 'deal_company']);
        $rs = $srch->getResultSet();
        if (!$row_deal = $db->fetch($rs)) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        $company_deal_commission_percent = $row_deal['company_deal_commission_percent'];
        $arr = ['status' => 1, 'commission' => $company_deal_commission_percent, 'msg' => 'success', 'redirectTo' => CONF_WEBROOT_URL . 'manager/companies.php?edit=' . $row_deal['deal_company'], 'id' => $row_deal['deal_company']];
        die(convertToJson($arr));
        break;
    case 'CHECKCITYWISECOMMISSION':
        if (!is_numeric($post['id']))
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        $rs1 = $db->query("select city_deal_commission_percent from tbl_cities where city_id=" . intval($post['deal_city']));
        $row1 = $db->fetch($rs1);
        $srch = new SearchBase('tbl_deals', 'd');
        $srch->addCondition('deal_id', '=', intval($post['id']));
        $srch->joinTable('tbl_cities', 'INNER JOIN', 'd.deal_city=c.city_id', 'c');
        $srch->addCondition('deal_status', '=', 5);
        $srch->addMultipleFields(['city_id', 'deal_name', 'city_deal_commission_percent', 'deal_status', 'deal_company']);
        $rs = $srch->getResultSet();
        if (!$row_deal = $db->fetch($rs)) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        $city_deal_commission_percent = $row_deal['city_deal_commission_percent'];
        $arr = ['status' => 1, 'commission' => $city_deal_commission_percent, 'msg' => 'success', 'redirectTo' => CONF_WEBROOT_URL . 'manager/cities.php?edit=' . $row_deal['city_id'], 'id' => $row_deal['city_id']];
        die(convertToJson($arr));
        break;
    case 'APPROVEDEAL':
        if (!is_numeric($post['id'])) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        $srch = new SearchBase('tbl_deals', 'd');
        $srch->addCondition('deal_id', '=', intval($post['id']));
        $srch->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=c.company_id', 'c');
        $srch->addMultipleFields(['deal_id', 'deal_name', 'company_name', 'company_email', 'deal_status']);
        $rs = $srch->getResultSet();
        if (!$row_deal = $db->fetch($rs)) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        if ($row_deal['deal_status'] == 0) {
            dieJsonError(t_lang('M_MSG_DEAL_ALREADY_APPROVED'));
        }
        if ($db->total_records($rs) > 0) {
            $rs_tpl = $db->query("select * from tbl_email_templates where tpl_id=12");
            $row_tpl = $db->fetch($rs_tpl);
            /* Notify User */
            $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
            $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
            $arr_replacements = [
                'xxdeal_namexx' => $row_deal['deal_name'],
                'xxpreviewlinkxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'preview-deal.php?deal=' . $row_deal['deal_id'] . '&mode=preview',
                'xxcompany_namexx' => $row_deal['company_name'],
                'xxcompany_emailxx' => $row_deal['company_email'],
                'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
                'xxshadow_imgxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '/images/shadow.jpg',
                'xxserver_namexx' => $_SERVER['SERVER_NAME'],
                'xxsite_namexx' => CONF_SITE_NAME,
                'xxwebrooturlxx' => CONF_WEBROOT_URL
            ];
            foreach ($arr_replacements as $key => $val) {
                $subject = str_replace($key, $val, $subject);
                $message = str_replace($key, $val, $message);
            }
            if ($row_tpl['tpl_status'] == 1) {
                sendMail($row_deal['company_email'], $subject, emailTemplate(($message)), $headers);
            }
            /* Notify User Ends */
        }
        if (!$db->update_from_array('tbl_deals', ['deal_status' => 0], 'deal_id=' . $post['id'])) {
            dieJsonError($db->getError());
        }
        dieJsonSuccess(t_lang('M_TXT_DEAL') . ' ' . t_lang('M_TXT_APPROVED'));
        break;
    case 'DISAPPROVEDEAL':
        if (!is_numeric($post['id'])) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        $srch = new SearchBase('tbl_deals', 'd');
        $srch->addCondition('deal_id', '=', intval($post['id']));
        $srch->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=c.company_id', 'c');
        $srch->addMultipleFields(['deal_id', 'deal_name', 'company_name', 'company_email', 'deal_status']);
        $rs = $srch->getResultSet();
        if (!$row_deal = $db->fetch($rs)) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        if ($row_deal['deal_status'] == 6) {
            dieJsonError(t_lang('M_MSG_DEAL_ALREADY_DISAPPROVED'));
        }
        if ($row_deal['deal_status'] == 1) {
            dieJsonError(t_lang('M_MSG_DEAL_IS_OPEN_CANNOT_DISAPPROVED'));
        }
        if ($db->total_records($rs) > 0) {
            $rs_tpl = $db->query("select * from tbl_email_templates where tpl_id=13");
            $row_tpl = $db->fetch($rs_tpl);
            /* Notify User */
            $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
            $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
            $arr_replacements = [
                'xxdeal_namexx' => $row_deal['deal_name'],
                'xxpreviewlinkxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'preview-deal.php?deal=' . $row_deal['deal_id'] . '&mode=preview',
                'xxcompany_namexx' => $row_deal['company_name'],
                'xxcompany_emailxx' => $row_deal['company_email'],
                'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
                'xxshadow_imgxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '/images/shadow.jpg',
                'xxserver_namexx' => $_SERVER['SERVER_NAME'],
                'xxsite_namexx' => CONF_SITE_NAME,
                'xxwebrooturlxx' => CONF_WEBROOT_URL
            ];
            foreach ($arr_replacements as $key => $val) {
                $subject = str_replace($key, $val, $subject);
                $message = str_replace($key, $val, $message);
            }
            if ($row_tpl['tpl_status'] == 1) {
                sendMail($row_deal['company_email'], $subject, emailTemplate(($message)), $headers);
            }
            /* Notify User Ends */
        }
        if (!$db->update_from_array('tbl_deals', ['deal_status' => 6], 'deal_id=' . $post['id'])) {
            dieJsonError($db->getError());
        }
        dieJsonSuccess(t_lang('M_TXT_DEAL') . ' ' . t_lang('M_TXT_DISAPPROVED'));
        break;
    case 'GETADDRESS':
        if (!is_numeric($post['company'])) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        $rs = $db->query("select * from tbl_company_addresses where company_id=" . intval($post['company']));
        if (!$row_deal = $db->fetch($rs)) {
            //dieJsonError(t_lang('M_MSG_NO_ADDRESS_IS_ADDED_PLEASE_ADD_ADDRESS_FIRST'));
            if ($post['company'] == 0 && $post['deal_id'] > 0) {
                $redirectTo = CONF_WEBROOT_URL . 'manager/add-deals.php?edit=' . $post['deal_id'] . '&step=2';
            } else {
                $redirectTo = CONF_WEBROOT_URL . 'manager/company-addresses.php?company_id=' . $post['company'];
            }
            $arr = ['status' => 0, 'msg' => t_lang('M_MSG_NO_ADDRESS_IS_ADDED_PLEASE_ADD_ADDRESS_FIRST'), 'redirectTo' => $redirectTo];
            die(convertToJson($arr));
        }
        if (!is_numeric($post['deal_id'])) {
            $post['deal_id'] = 0;
        }
        $deal_data = $db->fetch($db->query("select deal_sub_type from tbl_deals where deal_id=" . intval($post['deal_id'])));
        if ($deal_data['deal_sub_type'] == 2) {
            $caption = t_lang('M_TXT_CAPACITY_PER_DAY');
        } else {
            $caption = t_lang('M_TXT_CAPACITY');
        }
        $srch = new SearchBase('tbl_company_addresses', 'ca');
        $srch->addCondition('ca.company_id', '=', $post['company']);
        $srch->joinTable('tbl_deal_address_capacity', 'LEFT OUTER JOIN', 'ca.company_address_id = dac.dac_address_id and dac.dac_sub_deal_id = 0 and dac.dac_deal_id = ' . $post['deal_id'], 'dac');
        if ($_SESSION['lang_fld_prefix'] == '_lang1') {
            $srch->addFld("CONCAT(company_address_line1_lang1, '<br/>', company_address_line2_lang1, '<br/>', company_address_line3_lang1,  '<br/>', company_address_zip, ' ') AS address");
        } else {
            $srch->addFld("CONCAT(company_address_line1, '<br/>', company_address_line2, '<br/>', company_address_line3,  '<br/>', company_address_zip, ' ') AS address");
        }
        $srch->addFld("CASE WHEN dac.dac_id IS NULL THEN 0 ELSE dac.dac_address_capacity END AS capacity");
        $srch->addMultipleFields(['ca.*', 'dac.*']);
        $rs = $srch->getResultSet();
        if ($db->total_records($rs) > 0) {
            $frm = new Form('frmDealAddress', 'frmDealAddress');
            $frm->setTableProperties('border="0" cellspacing="0" cellpadding="0" class="tbl_form"  width="100%"');
            $frm->setFieldsPerRow(2);
            $fld = $frm->addHtml('', '', '<div class="tblheading">' . t_lang('M_TXT_PLEASE_CHECK_ATLEAST_ONE_ADDRESS') . '</div>');
            $fld->merge_cells = 2;
            $fld->merge_caption = true;
            $frm->captionInSameCell(false);
            $count = 0;
            while ($row = $db->fetch($rs)) {
                $count++;
                $dac_address_capacity = $row['capacity'];
                $company_address_id[] = $row['company_address_id'];
                $str .= $row['address'];
                if ($dac_address_capacity > 0) {
                    $fld1 = $frm->addCheckBox($row['address'], 'dac_address_id[]', ($_POST['company_address_id'] == '' ? $row['company_address_id'] : $_POST['company_address_id']), 'dac_address_id' . $count, 'onclick="return updateMaxCoupons(this,' . intval($post['deal_id']) . ',' . intval($row['company_address_id']) . ');"', true);
                } else {
                    $fld1 = $frm->addCheckBox($row['address'], 'dac_address_id[]', $row['company_address_id'], 'dac_address_id' . $count, 'onclick="return updateMaxCoupons(this,' . intval($post['deal_id']) . ',' . intval($row['company_address_id']) . ');"', false);
                }
                $fld = $frm->addTextBox($caption, 'dac_address_capacity[]', $dac_address_capacity, 'dac_address_capacity' . $count, 'onchange="return updateMaxCoupons(this,' . intval($post['deal_id']) . ',' . intval($row['company_address_id']) . ');" maxLength="15"');
                $dac_address_capacity = '';
            }
        }
        $str = $frm->getFormHtml(false);
        dieJsonSuccess($str);
        break;
    case 'GETTOTALSOLDCOUPONS':
        if (intval($post['deal']) <= 0) {
            $arr = ['status' => 0];
        }
        $sold = 0;
        $sold = intval(checkDealSoldForCompanyLoc($post['deal'], $post['loc']));
        if ($sold === null || $sold == '') {
            $sold = 0;
        }
        $arr = ['status' => 1, 'total_sold_for_selected_loc' => $sold];
        die(convertToJson($arr));
        break;
    case 'MAINDEAL':
        if (!is_numeric($post['city'])) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        if (!is_numeric($post['id'])) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        if (!$db->update_from_array('tbl_deals', ['deal_main_deal' => 1], 'deal_id=' . $post['id'])) {
            dieJsonError($db->getError());
        }
        if (!$db->update_from_array('tbl_deals', ['deal_main_deal' => 0], 'deal_city=' . $post['city'] . ' and deal_id!=' . $post['id'] . ' and deal_status > 0')) {
            dieJsonError($db->getError());
        }
        dieJsonSuccess(t_lang('M_TXT_INFO_UPDATED'));
        break;
    case 'MARKDEALPAID':
        if (!$db->update_from_array('tbl_deals', ['deal_paid' => 1, 'deal_paid_date' => date('Y-m-d')], 'deal_id=' . $post['id'])) {
            dieJsonError($db->getError());
        }
        $arr = ['status' => 1, 'msg' => t_lang('M_TXT_INFO_UPDATED')];
        die(convertToJson($arr));
        break;
    case 'UPCOMINGMAINDEAL':
        if (!is_numeric($post['city'])) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        if (!is_numeric($post['id'])) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        if (!$db->update_from_array('tbl_deals', array('deal_main_deal' => 1), 'deal_id=' . $post['id'])) {
            dieJsonError($db->getError());
        }
        if (!$db->update_from_array('tbl_deals', array('deal_main_deal' => 0), 'deal_city=' . $post['city'] . ' and deal_id!=' . $post['id'] . ' and deal_status = 0')) {
            dieJsonError($db->getError());
        }
        dieJsonSuccess(t_lang('M_TXT_INFO_UPDATED'));
        break;
    case 'PAYTOMERCHANT1':
        $rscompany = $db->query("SELECT  *  FROM `tbl_deals` WHERE deal_id=" . $_POST['deal_id']);
        while ($arrs = $db->fetch($rscompany)) {
            $company_id = $arrs['deal_company'];
        }
        $frm = new Form('frmPayToMerchant', 'frmPayToMerchant');
        $frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
        $frm->setFieldsPerRow(1);
        $frm->setJsErrorDisplay('afterfield');
        $frm->setValidatorJsObjectName('frmValidator');
        $frm->setExtra('onsubmit="submitPayToMerchant(this, frmValidator); return(false);"');
        $frm->captionInSameCell(false);
        $frm->addHTML('<strong>' . t_lang('M_TXT_DEBIT') . ':</strong> ' . t_lang('M_TXT_MAKE_PAYMENT_TO_MERCHANT') . '<br/><strong>' . t_lang('M_TXT_CREDIT') . ':</strong> ' . t_lang('M_TXT_WHEN_WANT_TO_GIVE_CREDIT'), '', '', '', true)->merge_caption = 2;
        $frm->addRadioButtons(t_lang('M_TXT_ENTRY_TYPE'), 'entry_type', array('1' => t_lang('M_TXT_DEBIT'), '2' => t_lang('M_TXT_CREDIT')), '', 2, 'width="100%" border="0"', '');
        $frm->addTextBox(t_lang('M_TXT_AMOUNT'), 'cwh_amount', '', '', '')->requirements()->setRequired();
        $frm->addTextArea(t_lang('M_TXT_PARTICULARS'), 'cwh_particulars', '', '', '')->requirements()->setRequired();
        $frm->addHiddenField('', 'cwh_untipped_deal_id', $post['deal_id']);
        $frm->addHiddenField('', 'mode', 'pay');
        $frm->addHiddenField('', 'mode', 'updatemerchantwallet');
        $frm->addHiddenField('', 'cwh_company_id', $company_id);
        $frm->addHiddenField('', 'payable_amount', $post['payable_amount']);
        $fld = $frm->addSubmitButton('', 'update_btn', t_lang('M_TXT_UPDATE'), '', ' class="medium"');
        echo '<div class="box">
                <div class="title"> ' . t_lang('M_TXT_ADD_TRANSACTION') . '<span class="fr">' . t_lang('M_TXT_PAYABLE_AMOUNT_:') . ' <strong>' . CONF_CURRENCY . number_format($post['payable_amount'], 2) . CONF_CURRENCY_RIGHT . '</strong></span></div>
                <div class="content">' . $frm->getFormHtml() . '</div>
              </div>';
        break;
    case 'UPDATEMERCHANTWALLET':
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
        die('<div class="box" id="messages">
                <div class="title-msg"> ' . t_lang('M_TXT_SYSTEM_MESSAGES') . '</div>
                <div class="content">
                  <div class="greentext">' . $msg->display() . '</div>
                </div>
              </div>');
        break;
    case 'UPDATECATEGORY':
        $success = $db->query("INSERT IGNORE INTO tbl_deal_to_category (dc_deal_id,dc_cat_id) VALUES (" . $post['deal_id'] . "," . $post['cat_id'] . ")");
        if ($success) {
            dieJsonSuccess(t_lang('M_TXT_INFO_UPDATED'));
        }
        break;
    case 'SELECTEDCATEGORY':
        $deal_id = intval($post['deal_id']);
        $List = $db->query("SELECT * FROM tbl_deal_to_category WHERE dc_deal_id=" . $deal_id);
        while ($row1 = $db->fetch($List)) {
            $arr[] = $row1['dc_cat_id'];
        }
        if ($db->total_records($List) > 0) {
            $catList = $db->query("SELECT * FROM tbl_deal_categories WHERE  cat_id  IN (" . implode(',', $arr) . ")");
            $str = '';
            while ($row = $db->fetch($catList)) {
                $str .= '<li><a href="?edit=' . $deal_id . '&step=4&catRemove=' . $row['cat_id'] . '" style="color:#565656;">' . $row['cat_name' . $_SESSION['lang_fld_prefix']] . ' <img src="images/remove1.png"></a></li>';
            }
            echo $str;
        } else {
            
        }
        break;
    case 'DELETESUBDEAL':
        $sdeal_id = intval($post['sub_deal_id']);
        $whr = array('smt' => 'sdeal_id = ?', 'vals' => array($sdeal_id));
        if ($db->deleteRecords('tbl_sub_deals', $whr)) {
            $whr1 = array('smt' => 'dbdate_sub_deal_id = ?', 'vals' => array($sdeal_id));
            $db->deleteRecords('tbl_deal_booking_dates', $whr1);
            $whr1 = array('smt' => 'dac_sub_deal_id = ?', 'vals' => array($sdeal_id));
            $db->deleteRecords('tbl_deal_address_capacity', $whr1);
            dieJsonSuccess(t_lang('M_TXT_RECORD_DELETED'));
        } else {
            $db->getError();
            $arr = array('status' => 0, 'msg' => $db->getError());
            die(convertToJson($arr));
        }
        break;
    case'GETOPTIONVALUES' :
        $option_id = intval($post['id']);
        $d_op_src = new SearchBase('tbl_option_values', 'd_op');
        // $d_op_src->addCondition('d_op.deal_id', '=', $deal_id);
        $d_op_src->addCondition('d_op.option_id', '=', $option_id);
        $deal_op = $d_op_src->getResultSet();
        $deal_op = $db->fetch_all($deal_op);
        foreach ($deal_op as $option_value) {
            $options .= '<option value="' . $option_value['option_value_id'] . '">' . $option_value['name'] . '</option>';
        }
        dieJsonSuccess($options);
        break;
    case'DELETEOPTIONVALUE' :
        global $db;
        $option_value_id = intval($post['option_value_id']);
        $whr = array('smt' => 'option_value_id = ? ', 'vals' => array($option_value_id), 'execute_mysql_functions' => false);
        $res = $db->deleteRecords('tbl_option_values', $whr);
        dieJsonSuccess(t_lang('M_TXT_Option_DELETED'));
        break;
    case 'DELETEDEALOPTIONVALUE':
        global $db;
        $deal_option_value_id = intval($post['deal_option_value_id']);
        $whr = array('smt' => 'deal_option_value_id = ? ', 'vals' => array($deal_option_value_id), 'execute_mysql_functions' => false);
        $res = $db->deleteRecords('tbl_deal_option_value', $whr);
        dieJsonSuccess($res);
        break;
    case 'DELETEDEALOPTION':
        global $db;
        $deal_option_id = intval($post['deal_option_id']);
        $whr = array('smt' => 'deal_option_id = ? ', 'vals' => array($deal_option_id), 'execute_mysql_functions' => false);
        $res = $db->deleteRecords('tbl_deal_option', $whr);
        $res = $db->deleteRecords('tbl_deal_option_value', $whr);
        dieJsonSuccess($res);
        break;
    case 'DELETEDIGITALFILE':
        global $db;
        $deal_id = intval($post['productId']);
        $dp = new DigitalProduct();
        $digital_product = $dp->getDigitalProductRecord($deal_id);
        if (!empty($digital_product)) {
            unlink(DIGITAL_UPLOADS_PATH . $digital_product['dpe_product_file']);
            $whr = array('smt' => 'dpe_deal_id= ?', 'vals' => array($deal_id));
            $res = $db->update_from_array('tbl_digital_product_extras', array('dpe_product_file' => "", 'dpe_product_file_name' => ''), $whr);
        }
        dieJsonSuccess(t_lang('M_TXT_INFO_DELETED'));
        break;
    case 'FETCHTAXRATE' :
        global $db;
        $srch = new SearchBase('tbl_tax_rules', 'tr');
        $srch->joinTable('tbl_tax_classes', 'LEFT JOIN', 'tc.taxclass_id=tr.taxrule_taxclass_id', 'tc');
        $srch->joinTable('tbl_tax_rates', 'LEFT JOIN', 'trate.taxrate_id=tr.taxrule_taxrate_id', 'trate');
        $srch->addCondition('taxclass_active', '=', 1);
        $srch->addCondition('tr.taxrule_taxclass_id', '=', $post['classId']);
        $srch->addMultipleFields(array('tc.*', 'tr.*', 'trate.taxrate_name', 'trate.taxrate_tax_rate'));
        $srch->addOrder('taxrate_name');
        $rs_listing = $srch->getResultSet();
        $arr_listing_fields = array(
            'taxclass_name' => t_lang('M_FRM_CLASS_NAME'),
            'taxrate_name' => t_lang('M_TXT_RATE_NAME'),
            'taxrate_tax_rate' => t_lang('M_TXT_RATE'),
            'taxrule_tax_based_on' => t_lang('M_TXT_TAX_BASED_ON')
        );
        $str = '';
        $str .= '<table class="tbl_data" width="100%">
                    <thead>
                        <tr>';
        foreach ($arr_listing_fields as $val) {
            $str .= '<th>' . $val . '</th>';
        }
        $str .= ' </tr></thead>';
        while ($row = $db->fetch($rs_listing)) {
            $str .= '<tr' . (($row['class_active'] == 0) ? ' class="inactive"' : '') . '>';
            foreach ($arr_listing_fields as $key => $val) {
                $str .= '<td ' . (($key == action) ? 'width="20%"' : '') . '>';
                switch ($key) {
                    case 'taxrule_tax_based_on':
                        $arrayBased0n = array("1" => "Store Address", "2" => "Billing Address", "3" => "Shipping Address");
                        $str .= $arrayBased0n[$row['taxrule_tax_based_on']];
                        break;
                    case 'taxrate_tax_rate':
                        $str .= $row['taxrate_tax_rate'] . ' % <br/>';
                        break;
                    default:
                        $str .= $row[$key];
                        break;
                }
                $str .= '</td>';
            }
            $str .= '</tr>';
        }
        if ($db->total_records($rs_listing) == 0) {
            $str .= '<tr><td colspan="' . count($arr_listing_fields) . '">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
        }
        $str .= '</table>';
        dieJsonSuccess($str);
        break;
    case 'ADDREMOVEBOOKINGREQUESTDATE':
        global $db;
        dieJsonSuccess(fetchRequestForm($post));
        break;
    case 'FETCHBOOKINGDATEFORM':
        global $db;
        $srch = new SearchBase('tbl_deal_address_capacity', 'dac');
        $srch->addCondition('dac_deal_id', '=', $post['dealId']);
        $srch->addCondition('dbdate_deal_id', '=', $post['dealId']);
        $srch->addCondition('dbdate_sub_deal_id', '=', $post['sub_deal_id']);
        if ($post['sub_deal_id'] > 0) {
            $srch->addCondition('dac_sub_deal_id', '>', 0);
        }
        $srch->addCondition('dbdate_date', '=', $post['date']);
        $srch->joinTable('tbl_deal_booking_dates', 'LEFT JOIN', 'dac.dac_address_id=dbd.dbdate_company_location_id and dac_deal_id =' . $post['dealId'], 'dbd');
        $srch->joinTable('tbl_company_addresses', 'INNER JOIN', 'dac.dac_address_id=ca.company_address_id', 'ca');
        $srch->addMultipleFields(array('dac.dac_address_id', 'CONCAT_WS("<br/>" ,company_address_line1' . $_SESSION['lang_fld_prefix'] . ', company_address_line2' . $_SESSION['lang_fld_prefix'] . ',company_address_line3' . $_SESSION['lang_fld_prefix'] . ') AS `company_address`', 'dbd.*'));
        $srch->addGroupBy('dac.dac_address_id');
        $rs_listing = $srch->getResultSet();
        while ($row = $db->fetch($rs_listing)) {
            $frm = new Form('booking_form', 'booking_form');
            if (!empty($row)) {
                $frm->addHiddenField('', 'dbdate_id', $row['dbdate_id'], '');
                $frm->addHiddenField('', 'dbdate_company_location_id', $row['dbdate_company_location_id'], '');
            }
            $frm->setValidatorJsObjectName('frmbookingValidator');
            $frm->setJsErrorDisplay('afterfield');
            $frm->setTableProperties('width="100%" class="tbl_form"');
            $frm->setRequiredStarWith('caption');
            $frm->addHTML(t_lang('M_FRM_LOCATION'), 'company_address', $row['company_address']);
            $frm->addFloatField(t_lang('M_FRM_PRICE'), 'dbdate_price', $row['dbdate_price']);
            $frm->addIntegerField(t_lang('M_FRM_STOCK'), 'dbdate_stock', $row['dbdate_stock'], '');
            $frm->addHiddenField('', 'dbdate_deal_id', $post['dealId'], '');
            $frm->addHiddenField('', 'dbdate_sub_deal_id', $post['sub_deal_id'], '');
            $frm->addHiddenField('', 'dbdate_date', $post['date'], '');
            $frm->addHiddenField('', 'mode', 'bookingFormSubmit', '');
            $fld = $frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_UPDATE'), 'btn_submit', '');
            $fld1 = $frm->addButton('', 'btn_delete', t_lang('M_TXT_BLOCK'), 'delete', 'onClick="deleteOnlinebookingDealRecord(' . $row['dbdate_id'] . ')"');
            $fld1->attachField($fld);
            $frm->setOnSubmit('bookingFormSubmit(this,frmbookingValidator); return(false);');
            echo $frm->getFormHtml();
        }
        break;
    case 'BOOKINGFORMSUBMIT':
        $frm = new Form('booking_form', 'booking_form');
        if (!$frm->validate($post)) {
            $msg->addError($frm->getValidationErrors());
            dieJsonError($msg->display());
        }
        $record = new TableRecord('tbl_deal_booking_dates');
        $data['dbdate_deal_id'] = $post['dbdate_deal_id'];
        $data['dbdate_sub_deal_id'] = $post['dbdate_sub_deal_id'];
        $data['dbdate_date'] = $post['dbdate_date'];
        $data['dbdate_stock'] = $post['dbdate_stock'];
        $data['dbdate_price'] = $post['dbdate_price'];
        $data['dbdate_id'] = $post['dbdate_id'];
        $data['dbdate_company_location_id'] = $post['dbdate_company_location_id'];
        $record->assignValues($data, false);
        $record->addNew(array('IGNORE'), $data);
        break;
    case 'DELETEONLINEBOOKINGDEALRECORD':
        global $db;
        $whr = array('smt' => 'dbdate_id = ?', 'vals' => array($post['dbdate_id']), 'execute_mysql_functions' => false);
        $data['dbdate_stock'] = 0;
        $data['dbdate_price'] = 0;
        $res = $db->update_from_array('tbl_deal_booking_dates', $data, $whr);
        break;
    case 'REQUESTFORMSUBMIT':
        if (!isset($post['dbdate_company_location_id'])) {
            $post['dbdate_company_location_id'] = [];
        }
        $companyLocationArray = fetchcompanyAddress($post['dbdate_deal_id']);
        $company_location_ids = array_keys($companyLocationArray);
        foreach ($company_location_ids as $key => $value) {
            if (in_array($value, $post['dbdate_company_location_id'])) {
                $row = fetchbookingDealDatesId($post['dbdate_deal_id'], $post['dbdate_sub_deal_id'], $post['dbdate_date'], $value);
                if (!$row) {
                    $record = new TableRecord('tbl_deal_booking_dates');
                    $data['dbdate_deal_id'] = $post['dbdate_deal_id'];
                    $data['dbdate_sub_deal_id'] = $post['dbdate_sub_deal_id'];
                    $data['dbdate_date'] = $post['dbdate_date'];
                    $data['dbdate_company_location_id'] = $value;
                    $record->assignValues($data, false);
                    $record->addNew(array('IGNORE'), $data);
                }
            } else {
                $whr = array('smt' => 'dbdate_date = ? and dbdate_deal_id = ? and dbdate_sub_deal_id = ? and dbdate_company_location_id =?', 'vals' => array($post['dbdate_date'], $_POST['dbdate_deal_id'], $_POST['dbdate_sub_deal_id'], $value), 'execute_mysql_functions' => false);
                $res = $db->deleteRecords('tbl_deal_booking_dates', $whr);
            }
        }
        $row = fetchbookingDealDatesId($post['dbdate_deal_id'], $post['dbdate_sub_deal_id'], $post['dbdate_date']);
        if (!$row) {
            $msg = t_lang('M_TXT_DATE_UNAVAILABLE');
            dieJsonSuccess($msg);
        } else {
            $msg = t_lang('M_TXT_UPDATED');
            dieJsonSuccess($msg);
        }
        break;
    case 'DELETEDEAL':
        $deal_id = $post['id'];
        if (!is_numeric($post['id'])) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        global $db;
        $srch = new SearchBase('tbl_order_deals', 'od');
        $srch->addCondition('od_deal_id', '=', $post['id']);
        $srch->addCondition('od_voucher_suffixes', '!=', "");
        $rs = $srch->getResultSet();
        $total_count = $srch->recordCount($rs);
        if ($total_count > 0) {
            dieJsonError(t_lang('M_ERROR_RECORD_CANNOT_BE_DELETED'));
        }
        $query1 = "DELETE dealimg.*,dealaddcap.*,dealdisc.*,dealrev.*,rat.*,dealexpire_not.*,deal_cat.*,deal_opt.*,deal_opt_val.*,
			digital_prod_ex.*,deal.* FROM 
			tbl_deals deal
			LEFT JOIN tbl_deals_images dealimg  ON  dealimg.dimg_deal_id = deal.deal_id 
			LEFT JOIN tbl_deal_address_capacity dealaddcap ON  dealaddcap.dac_deal_id = deal.deal_id
			LEFT JOIN tbl_deal_discussions dealdisc ON  dealdisc.comment_deal_id =deal.deal_id 
			LEFT JOIN tbl_deal_review dealrev  ON dealrev.review_deal_id = deal.deal_id
			LEFT JOIN tbl_ratings rat ON rat.ratings_deal_id = deal.deal_id
			LEFT JOIN tbl_deal_expire_notification dealexpire_not ON dealexpire_not.den_deal_id = deal.deal_id
			LEFT JOIN tbl_deal_to_category deal_cat ON  deal_cat.dc_deal_id = deal.deal_id 
			LEFT JOIN tbl_deal_option deal_opt ON deal_opt.deal_id = deal.deal_id
			LEFT JOIN tbl_deal_option_value deal_opt_val ON deal_opt_val.deal_id = deal.deal_id 
			LEFT JOIN tbl_digital_product_extras digital_prod_ex ON digital_prod_ex.dpe_deal_id = deal.deal_id 
			LEFT JOIN tbl_sub_deals subDeal ON subDeal.sdeal_deal_id = deal.deal_id 
			WHERE
			deal.deal_id =" . $deal_id;
        $db->query($query1);
        $msg = t_lang('M_TXT_DELETED');
        dieJsonSuccess($msg);
        break;
}
