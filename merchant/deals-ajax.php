<?php

require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
require_once '../includes/subdeals-functions.php';
require_once '../site-classes/digital-product.cls.php';
if (!isCompanyUserLogged()) {
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'merchant-login.php'));
}
$post = getPostedData();
switch (strtoupper($post['mode'])) {
    case 'DELETESUBDEAL':
        $sdeal_id = intval($post['sub_deal_id']);
        $whr = array('smt' => 'sdeal_id = ?', 'vals' => array($sdeal_id));
        if ($db->deleteRecords('tbl_sub_deals', $whr)) {
            $whr1 = array('smt' => 'dbdate_sub_deal_id = ?', 'vals' => array($sdeal_id));
            $db->deleteRecords('tbl_deal_booking_dates', $whr1);
            dieJsonSuccess(t_lang('M_TXT_INFO_DELETED'));
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
        dieJsonSuccess($res);
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
        //$res = $db->deleteRecords('tbl_deal_booking_dates', $whr);
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
