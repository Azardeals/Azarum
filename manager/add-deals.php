<?php
require_once dirname(__FILE__) . '/application-top.php';
require_once dirname(__FILE__) . '/../includes/navigation-functions.php';
require_once dirname(__FILE__) . '/../includes/tax-functions.php';
require_once dirname(__FILE__) . '/../includes/subdeals-functions.php';
require_once dirname(__FILE__) . '/../site-classes/digital-product.cls.php';
require_once dirname(__FILE__) . '/../site-classes/calender.php';
$arr_common_js[] = 'js/calendar.js';
$arr_common_js[] = 'js/calendar-en.js';
$arr_common_js[] = 'js/calendar-setup.js';
$arr_common_css[] = 'css/cal-css/calendar-win2k-cold-1.css';
$arr_common_css[] = 'css/calender.css';
checkAdminPermission(5);
require_once dirname(__FILE__) . '/update-deal-status.php';
loadModels(array('DealModel'));
$_REQUEST['step'] = (is_numeric($_REQUEST['step']) ? $_REQUEST['step'] : 1);
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$tickAttr = [];
$post = getPostedData();
$get = getQueryStringData();
$mainTableName = 'tbl_deals';
$primaryKey = 'deal_id';
$colPrefix = 'deal_';
$option_row = 0;
if ($_REQUEST['status'] == 'cancelled' || $_REQUEST['status'] == 'expired') {
    header('Location: add-deals.php?page=1&add=new&status=active');
    exit;
}
/**
 * DEAL EDIT MODE - CODE FOR TICK PURPOSE ONLY
 * */
if (isset($_REQUEST['edit']) && !empty($_REQUEST['edit'])) {
    $srch = Deal::getSearchObject();
    $srch->addCondition("deal_id", "=", $_GET['edit']);
    $srch->addMultipleFields(array('deal_type', 'deal_max_coupons', 'deal_is_subdeal', 'deal_sub_type', 'deal_start_time', 'deal_company', 'deal_end_time'));
    $result = $srch->getResultSet();
    $dealData = $db->fetch($result);
    if ($dealData['deal_sub_type'] == 2) {
        $ts1 = strtotime(date('Y-m-d', strtotime($dealData['deal_start_time'])));
        $ts2 = strtotime(date('Y-m-d', strtotime($dealData['deal_end_time'])));
        $seconds_diff = $ts2 - $ts1;
        $day_diff = floor($seconds_diff / 3600 / 24);
    }
    if ($dealData['deal_type'] == 1) {
        $deal_op_value_obj = new SearchBase('tbl_deal_option', 'd_op');
        $deal_op_value_obj->addCondition('d_op.deal_id', '=', $_REQUEST['edit']);
        $deal_op_value_obj->joinTable('tbl_deal_option_value', 'JOIN', 'd_op_val.deal_option_id=d_op.deal_option_id', 'd_op_val');
        $deal_op_value_obj->addMultipleFields(array('d_op.deal_option_id,option_id'));
        $result = $deal_op_value_obj->getResultSet();
        $deal_op_values = $db->fetch_all_assoc($result);
        if (!empty($deal_op_values)) {
            $tickAttr = $deal_op_values;
        }
    } else {
        $subdeal_data = $db->query("select * from tbl_sub_deals where  sdeal_deal_id=" . $_GET['edit']);
        $getsubdealData = $db->fetch_all($subdeal_data);
    }
    if ($dealData['deal_type'] == 1 && $dealData['deal_sub_type'] == 1) {
        $dgProduct = new DigitalProduct();
        $digital_product = $dgProduct->getDigitalProductRecord($_GET['edit']);
    }
}
if ($_REQUEST['step'] == 2) {
    $frm = getMBSFormByIdentifier('frmDealLocation');
    if ($_SESSION['lang_fld_prefix'] == '_lang1') {
        $get_field = 'IF(CHAR_LENGTH(`city_name_lang1`), `city_name_lang1`, `city_name`)';
    } else {
        $get_field = '`city_name`';
    }
    $fld = $frm->getField('deal_city');
    $rs_opts_list = $db->query("select city_id,
			case  when (SELECT conf_val FROM `tbl_configurations` WHERE `conf_name`='conf_admin_commission_type')  = 2 THEN concat((" . $get_field . "), ' /  ',`city_deal_commission_percent`)
			WHEN (SELECT conf_val FROM `tbl_configurations` WHERE `conf_name`='conf_admin_commission_type')  != 2 THEN  " . $get_field . "
			END
			city_name
			FROM  tbl_cities where city_active=1 and city_deleted=0 and city_request=0 order by city_name");
    $fld->options = $db->fetch_all_assoc($rs_opts_list);
    if ($_SESSION['lang_fld_prefix'] == '_lang1') {
        $get_field = 'company_name_lang1';
    } else {
        $get_field = '`company_name`';
    }
    $fld = $frm->getField('deal_company');
    $rs_opts_list = $db->query("select company_id,
			case when (SELECT conf_val FROM `tbl_configurations` WHERE `conf_name`='conf_admin_commission_type')  = 3 THEN concat((" . $get_field . "), ' /  ',`company_deal_commission_percent`)
			WHEN (SELECT conf_val FROM `tbl_configurations` WHERE `conf_name`='conf_admin_commission_type')  != 3 THEN  " . $get_field . "
			END company_name
			FROM tbl_companies where company_active=1 and company_deleted=0 order by company_name");
    $fld->options = $db->fetch_all_assoc($rs_opts_list);
    $frm->addHiddenField('', 'city_deal', '', 'city_deal', '');
    $frm->addHiddenField('', 'company_deal', '', 'company_deal', '');
    $frm->addHiddenField('', 'deal_commission_percent', '', 'deal_commission_percent', '');
    $fld1 = $frm->getField('deal_company');
    if ($_GET['edit'] > 0 && $dealData['deal_company'] > 0) {
        $deal_id = $_GET['edit'];
        $fld1->extra = 'onchange="changeAddress(this.value,' . $deal_id . ',' . $dealData['deal_company'] . ');"';
    }
    $fld1 = $frm->getField('btn_submit');
    $fld = $frm->addButton('', 'btn_submit_cancel', t_lang('M_TXT_CANCEL'), '', ' class="inputbuttons" onclick=location.href="deals.php"')->attachField($fld1);
}
if ($_REQUEST['step'] == 3) {
    $frm = getMBSFormByIdentifier('frmDealVoucher');
    $frm->setRequiredStarWith('field');
    $fld = $frm->getField('voucher_valid_from');
    $frm->removeField($fld);
    $fld = $frm->getField('voucher_valid_till');
    $frm->removeField($fld);
    $fld = $frm->addDateTimeField('M_FRM_VOUCHER_VALID_FROM', 'voucher_valid_from', '', 'voucher_valid_from', 'readonly');
    $frm->changeFieldPosition($fld->getFormIndex(), 0);
    $fld->html_before_field = '<div class="frm-dob fld-req">';
    $fld->html_after_field = '</div>';
    $fld->requirements()->setRequired();
    $fld->requirements()->setCompareWith('deal_start_time', 'gt', t_lang('M_FRM_DEAL_STARTS_ON'));
    $fld->requirements()->setCompareWith('voucher_valid_till', 'lt', t_lang('M_FRM_VOUCHER_VALID_TILL'));
    $fld = $frm->addDateTimeField('M_FRM_VOUCHER_VALID_TILL', 'voucher_valid_till', '', 'voucher_valid_till', 'readonly');
    $frm->changeFieldPosition($fld->getFormIndex(), 1);
    $fld->html_before_field = '<div class="frm-dob fld-req">';
    $fld->html_after_field = '</div>';
    $fld->requirements()->setRequired();
    $fld->requirements()->setCompareWith('deal_end_time', 'gt', t_lang('M_FRM_DEAL_ENDS_ON'));
    $fld1 = $frm->getField('btn_submit');
    $fld = $frm->addButton('', 'btn_submit_cancel', t_lang('M_TXT_CANCEL'), '', ' class="inputbuttons" onclick=location.href="deals.php"')->attachField($fld1);
}
if ($_REQUEST['step'] == 6) {
    $frm = getMBSFormByIdentifier('frmDealCategory');
    $fld = $frm->getField('deal_categories');
    $cat_list = $db->query("select cat_id, IF(CHAR_LENGTH(cat_name" . $_SESSION['lang_fld_prefix'] . "),cat_name" . $_SESSION['lang_fld_prefix'] . ",cat_name) as cat_name from tbl_deal_categories where cat_parent_id = 0 order by cat_parent_id");
    $catrow1 = $db->fetch_all_assoc($cat_list);
    $rs_cats = $db->query("select dc_cat_id, dc_cat_id as cat_id from tbl_deal_to_category where  dc_deal_id=" . $_GET['edit']);
    $selCategory = $db->fetch_all_assoc($rs_cats);
    $catArray = '';
    $catArray .= '<ul class="clearfix">';
    foreach ($catrow1 as $key => $val) {
        $selected = "";
        $subCat = fetchsubCategory($key, $selCategory, false);
        if (strlen($subCat) > 1) {
            $catArray .= '<li class="main_parent">' . $val . '';
            $catArray .= $subCat;
        } else {
            if (in_array($key, $selCategory)) {
                $selected = 'checked="checked"';
                unset($selCategory[$key]);
            }
            $catArray .= '<li class="main_parent nullChild"><input type="checkbox" ' . $selected . 'id="deal_categories" name="deal_categories[]" value="' . $key . '"/><label>' . $val . '</label>';
        }
        $catArray .= '</li>';
    }
    $catArray .= '</ul>';
    $fld = $frm->getField('deal-category');
    $frm->removeField($fld);
    $fld = $frm->getField('cat-display');
    $frm->removeField($fld);
    $fld1 = $frm->getField('btn_submit');
    $fld = $frm->addButton('', 'btn_submit_cancel', t_lang('M_TXT_CANCEL'), '', ' class="inputbuttons" onclick=location.href="deals.php"')->attachField($fld1);
}
if ($_REQUEST['step'] == 7) {
    $frm = getMBSFormByIdentifier('frmDealSeo');
    $fld1 = $frm->getField('btn_submit');
    $fld = $frm->addButton('', 'btn_submit_cancel', t_lang('M_TXT_CANCEL'), '', ' class="inputbuttons" onclick=location.href="deals.php"')->attachField($fld1);
}
if ($_REQUEST['step'] == 8) {
    $frm = getMBSFormByIdentifier('frmDealCharity');
    $frm->setOnSubmit('return checkCharity()');
    $fld = $frm->getField('deal_charity');
    $fld->extra = "id='deal_charity'";
    $fld1 = $frm->getField('deal_charity_discount');
    $fld1->extra = "id='charity_amount'";
    $fld1->fldCellExtra = "id='percent'";
    $cat_list = $db->query("select charity_id, IF(CHAR_LENGTH(charity_name" . $_SESSION['lang_fld_prefix'] . "),charity_name" . $_SESSION['lang_fld_prefix'] . ",charity_name) as charity_name from tbl_company_charity where charity_status=1 order by charity_name");
    $fld->options = $db->fetch_all_assoc($cat_list);
    $fld1 = $frm->getField('btn_submit');
    $fld = $frm->addButton('', 'btn_submit_cancel', t_lang('M_TXT_CANCEL'), '', ' class="inputbuttons" onclick=location.href="deals.php"')->attachField($fld1);
}
if ($_REQUEST['step'] == 9) {
    $frm = getMBSFormByIdentifier('frmDealSettings');
    $fld = $frm->getField('deal_image');
    $frm->removeField($fld);
    $fld1 = $frm->getField('btn_submit');
    $fld = $frm->addButton('', 'btn_submit_cancel', t_lang('M_TXT_CANCEL'), '', ' class="inputbuttons" onclick=location.href="deals.php"')->attachField($fld1);
}
if ($_REQUEST['step'] == 4) {
    $deal_id = intval($_GET['edit']);
    if ($dealData['deal_type'] == 1) {
        $frm = new Form('frmDealAttributes', 'frmDealAttributes');
        $frm->setValidatorJsObjectName('frmDealAttributes');
        $frm->setJsErrorDisplay('afterfield');
        $options = '';
        /* Fetch all options like colors,size etc starts */
        $srch = new SearchBase('tbl_options', 'op');
        $srch->joinTable('tbl_deal_option', 'INNER JOIN', 'op.option_id=d_op.option_id and d_op.deal_id=' . $deal_id, 'd_op');
        $srch->addCondition('op.is_deleted', '=', 0);
        $srch->addOrder('d_op.deal_option_id');
        $rs_listing = $srch->getResultSet();
        $options_data = $db->fetch_all($rs_listing);
        $srch1 = new SearchBase('tbl_options', 'op');
        $srch1->addCondition('op.is_deleted', '=', 0);
        $srch1->addOrder('op.option_name');
        $rs_listing1 = $srch1->getResultSet();
        $option_parent_data = $db->fetch_all($rs_listing1);
        $counter = 0;
        foreach ($options_data as $op) {
            $options_data[$counter]['deal_option_id'] = '';
            $options_data[$counter]['deal_id'] = '';
            $counter++;
        }
        /* Fetch all options like colors,size etc ends */
        $option_row = 0;
        $option_value_row = 0;
        $options .= '<table id="option-value" class="tbl_form" width="100%" border="0" cellspacing="0" cellpadding="0">';
        $options .= '<tr>';
        $options .= '<th  colspan="3" style="text-align:center;">';
        $options .= '<select name="" id="optionDropdown" >';
        foreach ($option_parent_data as $key1 => $value1) {
            $options .= '<option  value="' . $value1['option_id'] . '">' . $value1['option_name' . $_SESSION['lang_fld_prefix']] . '</option>';
        }
        $options .= '</select>';
        $options .= '<input type="button" name=""  value="' . t_lang("M_TXT_ADD_NEW_OPTION") . '" onclick="AddOption();" class="gray">';
        $options .= '</th>';
        $options .= '</tr>';
        foreach ($options_data as $row) {
            $d_op_src = new SearchBase('tbl_deal_option', 'd_op');
            $d_op_src->addCondition('d_op.deal_id', '=', $deal_id);
            $d_op_src->addCondition('d_op.option_id', '=', $row['option_id']);
            $d_op_src->addFld('d_op.required');
            $d_op_src->addFld('d_op.parent_option_id');
            $d_op_src->addFld('d_op.deal_option_id');
            $deal_op = $d_op_src->getResultSet();
            $deal_op = $db->fetch($deal_op);
            if (!empty($deal_op)) {
                $row['deal_option_id'] = $deal_op['deal_option_id'];
                if ($deal_op && $deal_op['required'] == 1) {
                    $row['required'] = 1;
                }
                if (!$deal_op) {
                    $row['required'] = 1;
                }
                $options .= '<input type="hidden" name="deal_option[' . $option_row . '][deal_option_id]" value="' . $row['deal_option_id'] . '" />';
                $options .= '<input type="hidden" name="deal_option[' . $option_row . '][option_name]" value="' . $row['option_name' . $_SESSION['lang_fld_prefix']] . '" />';
                $options .= '<input type="hidden" id="child_' . $option_row . '" name="deal_option[' . $option_row . '][option_id]" value="' . $row['option_id'] . '" />';
                $options .= '<input type="hidden" name="deal_option[' . $option_row . '][type]" value="' . $row['option_type'] . '" />';
                $options .= '<input type="hidden" name="deal_id" value="' . $deal_id . '" />';
                $options .= '<div class="option_rows"><tr class="option_rows">';
                $options .= '<th ><span >' . $row['option_name' . $_SESSION['lang_fld_prefix']] . ' : ' . t_lang("M_TXT_REQUIRED") . '</span>';
                $options .= ' <select name="deal_option[' . $option_row . '][required]">';
                if ($row['required'] == 1) {
                    $options .= '<option selected="selected" value="1">' . t_lang("M_TXT_YES") . '</option>';
                    $options .= '<option value="0">' . t_lang("M_TXT_NO") . '</option>';
                } else {
                    $options .= '<option value="1">' . t_lang("M_TXT_YES") . '</option>';
                    $options .= '<option selected="selected" value="0">' . t_lang("M_TXT_NO") . '</option>';
                }
                $option .= '</select>';
                $options .= '</th>';
                $options .= '<th >';
                $options .= t_lang("M_TXT_SELECT_PARENT_OPTION") . ' <select name="deal_option[' . $option_row . '][parent_option_id]" id="parent_' . $option_row . '" onChange="changeparentoptionvalue(' . $option_row . ');">';
                $options .= '<option  value="0" >' . t_lang("M_TXT_NONE") . '</option>';
                foreach ($option_parent_data as $key1 => $value1) {
                    $selected = "";
                    if ($deal_op['parent_option_id'] == $value1['option_id']) {
                        $selected = 'selected="selected"';
                    }
                    if ($row['option_id'] != $value1['option_id']) {
                        $options .= '<option  value="' . $value1['option_id'] . '" ' . $selected . '>' . $value1['option_name' . $_SESSION['lang_fld_prefix']] . '</option>';
                    }
                }
                $options .= '</select>';
                $options .= '</th>';
                $options .= '<th >';
                $options .= '<ul class="actions"><li><a title="' . t_lang("M_TXT_REMOVE") . '" onclick="removeElement(' . $row['option_id'] . ');removeParentRecord(' . $row['deal_option_id'] . ');$(\'#option-value' . $option_row . '\').remove();$(this).parent().parent().remove();"><i class="ion-minus icon"></i></a></li></ul>';
                $options .= '</th>';
                $options .= '</tr>';
                $options .= '<tr class="no_padd">';
                $options .= '<td colspan=3>';
                if ($row['option_type'] == 'select') {
                    /* Fetch deal_options_values starts here */
                    $deal_op_value_obj = new SearchBase('tbl_deal_option', 'd_op');
                    $deal_op_value_obj->addCondition('d_op.option_id', '=', $row['option_id']);
                    $deal_op_value_obj->addCondition('d_op.deal_id', '=', $deal_id);
                    $deal_op_value_obj->joinTable('tbl_deal_option_value', 'JOIN', 'd_op_val.deal_option_id=d_op.deal_option_id', 'd_op_val');
                    //     $deal_op_value_obj->addCondition('d_op_val.parent_option_value_id', '!=', 0);
                    $result = $deal_op_value_obj->getResultSet();
                    $deal_op_values = $db->fetch_all($result);
                    /* Fetch deal_options_values ends here */
                    $options .= '<table id="option-value' . $option_row . '" class="tbl-optionlist" width="100%" border="0" cellspacing="0" cellpadding="0">';
                    $options .= '<tr><th width="30%">' . t_lang("M_TXT_OPTION_VALUE") . ':</th><th width="10%">' . t_lang("M_TXT_QUANTITY") . ':</th><th width="30%">' . t_lang("M_TXT_PARENT_OPTION_VALUE") . ':</th><th width="25%">' . t_lang("M_TXT_PRICE") . ':</th><th></th></tr>';
                    $option_values = [];
                    $op_value_obj = new SearchBase('tbl_option_values', 'op_values');
                    $op_value_obj->addCondition('op_values.option_id', '=', $deal_op['parent_option_id']);
                    $op_value_obj->doNotLimitRecords();
                    $result = $op_value_obj->getResultSet();
                    $parent_option_values = $db->fetch_all($result);
                    $op_value_obj = new SearchBase('tbl_option_values', 'op_values');
                    $op_value_obj->addCondition('op_values.option_id', '=', $row['option_id']);
                    $op_value_obj->doNotLimitRecords();
                    $result = $op_value_obj->getResultSet();
                    $op_values = $db->fetch_all($result);
                    /* Show rows that are associated with current deal starts here */
                    if ($deal_op_values)
                        foreach ($deal_op_values as $val) {
                            /*  echo "<pre>";
                              print_r($val); */
                            $options .= '<tr class="">';
                            $options .= '<input type="hidden" name="deal_option[' . $option_row . '][deal_option_value][' . $option_value_row . '][deal_option_value_id]" value="' . $val['deal_option_value_id'] . '" />';
                            $options .= '<td><select  class="child_option_value' . $option_row . '" name="deal_option[' . $option_row . '][deal_option_value][' . $option_value_row . '][option_value_id]">';
                            if (count($op_values)) {
                                foreach ($op_values as $o_val) {
                                    if ($val['option_value_id'] == $o_val['option_value_id']) {
                                        $options .= '<option selected="selected" value="' . $o_val['option_value_id'] . '">' . $o_val['name'] . '</option>';
                                    } else {
                                        $options .= '<option value="' . $o_val['option_value_id'] . '">' . $o_val['name'] . '</option>';
                                    }
                                }
                            }
                            $options .= '</select></td>';
                            $options .= '<td><input size="5" type="number" min="1" class="child' . $val['option_value_id'] . ' parent' . $val['parent_option_value_id'] . '" onchange="checkQuantity(this)" value="' . $val['quantity'] . '" name="deal_option[' . $option_row . '][deal_option_value][' . $option_value_row . '][quantity]"/></td>';
                            $options .= '<td><select  class="parent_option_value' . $option_row . '" onchange="$(this).parent().prev().find(\'input\').trigger(\'change\');" name="deal_option[' . $option_row . '][deal_option_value][' . $option_value_row . '][parent_option_value_id]">';
                            if (count($parent_option_values)) {
                                foreach ($parent_option_values as $o_val) {
                                    if ($val['parent_option_value_id'] == $o_val['option_value_id']) {
                                        $options .= '<option selected="selected" value="' . $o_val['option_value_id'] . '">' . $o_val['name'] . '</option>';
                                    } else {
                                        $options .= '<option value="' . $o_val['option_value_id'] . '">' . $o_val['name'] . '</option>';
                                    }
                                }
                            }
                            $options .= '</select></td>';
                            $options .= '<td><select class="fieldSmall" name="deal_option[' . $option_row . '][deal_option_value][' . $option_value_row . '][price_prefix]">';
                            if ($val['price_prefix'] == '+') {
                                $options .= '<option value="+" selected="selected">+</option>';
                            } else {
                                $options .= '<option value="+">+</option>';
                            }
                            if ($val['price_prefix'] == '-') {
                                $options .= '<option value="-" selected="selected">-</option>';
                            } else {
                                $options .= '<option value="-">-</option>';
                            }
                            $options .= '</select>';
                            $options .= '&nbsp;&nbsp;&nbsp;<input type="text" class="fieldSmalltext" value="' . $val['price'] . '" name="deal_option[' . $option_row . '][deal_option_value][' . $option_value_row . '][price]"/>';
                            $options .= '</td>';
                            $options .= '<td><ul class="actions"><li><a title="' . t_lang("M_TXT_REMOVE") . '" class="remove_row" onclick="removeRecord(' . $val['deal_option_value_id'] . ')"><i class="ion-minus icon"></i></li></ul></a></td>';
                            $options .= '</tr>';
                            $option_value_row++;
                        }
                    /* Show rows that are associated with current deal ends here */
                }
                $options .= '<tfoot><tr><td colspan="4"></td><td><ul class="actions"><li><a href="javascript:void(0);" onclick="addOptionValue(' . $option_row . ')" title="' . t_lang("M_TXT_ADD_OPTION_VALUE") . '"><i class="ion-plus-round icon icon"></i></a></li></ul></td></tr></tfoot></div>';
                $options .= '</table>';
                $option_row++;
            }
            $options .= '</td>';
            $options .= '</tr>';
        }
        $options .= '<tr><td align="center" >
		<input type="button" name="btn_submit_cancel" class="inputbuttons" onclick=location.href="deals.php" title="" value="' . t_lang('M_TXT_CANCEL') . '">
		<input name="btn_submit" type="submit" value="' . t_lang('M_TXT_UPDATE') . '"/></td></tr>';
        $options .= '</table>';
        $frm->addHTML('', '', $options, true);
    } else {
        if ($dealData['deal_is_subdeal'] == 0 && $dealData['deal_sub_type'] == 0) {
            redirectUser('add-deals.php?edit=' . $deal_id . '&page=' . $page . '&status=' . $get['status'] . '&step=' . ($_REQUEST['step'] + 1));
        } else if ($dealData['deal_is_subdeal'] == 0 && $dealData['deal_type'] == 0 && $dealData['deal_sub_type'] == 1 || $_REQUEST['url'] == 'manageDates') {
            $frm = new Form('requestBooking', 'requestBooking');
            $calender = new Calendars($_SERVER['REQUEST_URI']);
            $companyLocationArray = fetchcompanyAddress($_REQUEST['edit']);
            $company_location_ids = array_keys($companyLocationArray);
            foreach ($company_location_ids as $key => $value) {
                saveBookingRequestDate($value, $_REQUEST['edit'], $_REQUEST['sub_deal_id']);
            }
            $data = fetchQuantityPriceDetail($_REQUEST['edit'], $_REQUEST['sub_deal_id'], $dealData['deal_start_time'], $dealData['deal_end_time']);
            $subdealId = $_REQUEST['sub_deal_id'];
            if (!isset($_REQUEST['sub_deal_id'])) {
                $subdealId = 0;
            }
            $month = $_REQUEST['month'];
            $year = $_REQUEST['year'];
            $start_date = date("Y-m-d", strtotime($dealData['deal_start_time']));
            $end_date = date("Y-m-d", strtotime($dealData['deal_end_time']));
            $calender->setDateRange($start_date, $end_date);
            $calender->attachEventHandler("addRemoveBookingRequestDate(this," . $_REQUEST['edit'] . "," . $subdealId . ");");
            $calender->setDateData($data);
            $html = $calender->show($month, $year);
            $frm->addHTML('', '', $html, true);
            if (isset($_REQUEST['sub_deal_id'])) {
                $button = '<a href="add-deals.php?edit=' . $deal_id . '&sub_deal_id=' . $_REQUEST['sub_deal_id'] . '&step=' . ($_REQUEST['step'] ) . '" class="button">' . t_lang('M_TXT_BACK_TO_SUBDEAL') . '</a>';
                $frm->addHTML('', '', $button, true);
            }
        } else if ($dealData['deal_is_subdeal'] == 0 && $dealData['deal_type'] == 0 && $dealData['deal_sub_type'] == 2 || $_REQUEST['url'] == 'manageOnlinebookingDates') {
            $frm = new Form('onlineBooking', 'onlineBooking');
            $subdealId = $_REQUEST['sub_deal_id'];
            if (!isset($_REQUEST['sub_deal_id'])) {
                $subdealId = 0;
            }
            $calender = new Calendars($_SERVER['REQUEST_URI']);
            $companyLocationArray = fetchSubDealAddressCapacity($_REQUEST['edit'], $subdealId);
            foreach ($companyLocationArray as $location_id => $capacity) {
                saveBookingRequestDate($location_id, $_REQUEST['edit'], $subdealId, $capacity);
            }
            $data = fetchQuantityPriceDetail($_REQUEST['edit'], $_REQUEST['sub_deal_id'], $dealData['deal_start_time'], $dealData['deal_end_time'], $dealData['deal_sub_type']);
            $month = $_REQUEST['month'];
            $year = $_REQUEST['year'];
            $start_date = date("Y-m-d", strtotime($dealData['deal_start_time']));
            $end_date = date("Y-m-d", strtotime($dealData['deal_end_time']));
            $calender->setDateRange($start_date, $end_date);
            $calender->attachEventHandler("addQuantityPrice(this," . $_REQUEST['edit'] . "," . $subdealId . ")");
            $calender->setDateData($data);
            $html = $calender->show($month, $year);
            $frm->addHTML('', '', $html, true);
            if (isset($_REQUEST['sub_deal_id'])) {
                $button = '<div style="text-align:center"><a href="add-deals.php?edit=' . $deal_id . '&sub_deal_id=' . $_REQUEST['sub_deal_id'] . '&step=' . ($_REQUEST['step'] ) . '" class="button">' . t_lang('M_TXT_BACK_TO_SUBDEAL') . '</a></div>';
                $frm->addHTML('', '', $button, true);
            }
        } else {
            $option_row = 1;
            $deal_id = intval($_GET['edit']);
            $subdeal = new SearchBase('tbl_sub_deals');
            $subdeal->addCondition('sdeal_deal_id', '=', $deal_id);
            $result = $subdeal->getResultSet();
            $subdealData = $db->fetch_all($result);
            $companyLocationArray = fetchcompanyAddress($_GET['edit']);
            $company_location_ids = array_keys($companyLocationArray);
            $frm = new Form('frmDealOptions', 'frmDealOptions');
            $frm->setTableProperties('class="tbl_form" width=100%');
            $frm->setValidatorJsObjectName('frmDealOptions');
            $frm->setJsErrorDisplay('afterfield');
            $frm->addRequiredField(t_lang('M_TXT_SUBDEAL_NAME'), 'sdeal_name');
            $frm->addFloatField(t_lang('M_TXT_SUBDEAL_ORIGINAL_PRICE'), 'sdeal_original_price');
            $fld = $frm->addFloatField(t_lang('M_TXT_SUBDEAL_DISCOUNT'), 'sdeal_discount');
            $array_op = array(1 => 'Percentage', 0 => 'Fixed');
            $fld1 = $frm->addSelectBox('', 'sdeal_discount_is_percentage', $array_op, 0);
            $fld->attachField($fld1);
            $fld = $frm->addHiddenField(t_lang('M_TXT_SUBDEAL_MAX_COUPON'), 'sdeal_max_coupons');
            $fixed_discount_req = new FormFieldRequirement('sdeal_discount', t_lang('M_TXT_DISCOUNT'));
            $fixed_discount_req->setFloatPositive(true);
            $fixed_discount_req->setCompareWith('sdeal_original_price', 'le', t_lang('M_FRM_ORIGINAL_PRICE'));
            $percent_discount_req = new FormFieldRequirement('sdeal_discount', t_lang('M_TXT_DISCOUNT'));
            $percent_discount_req->setFloatPositive(true);
            $percent_discount_req->setRange(0, 100);
            $fld = $frm->getField('sdeal_discount_is_percentage');
            $fld->id = "sdeal_discount_is_percentage";
            $fld_req = $fld->requirements();
            $fld_req->addOnChangerequirementUpdate('1', 'ne', 'sdeal_discount', $fixed_discount_req);
            $fld_req->addOnChangerequirementUpdate('1', 'eq', 'sdeal_discount', $percent_discount_req);
            if ($_REQUEST['sub_deal_id'] > 0) {
                $subDealAddressCapacity = fetchSubDealAddressCapacity($deal_id, $_REQUEST['sub_deal_id']);
            }
            $text = t_lang('M_TXT_ENTER_VOUCHER_OF_LOCATION') . '<br/>';
            foreach ($companyLocationArray as $key => $location) {
                $caption = $text . $location;
                $frm->addFloatField($caption, "dac_address_capacity[$key]", $subDealAddressCapacity[$key]);
            }
            $frm->addHiddenField('', 'sdeal_id');
            $arr_status = array(1 => 'Active', 0 => 'Inactive');
            $frm->addSelectBox(t_lang('M_TXT_SUBDEAL_STATUS'), 'sdeal_active', $arr_status, 1);
            if ($dealData['deal_type'] == 2) {
                
            }
            $fld = $frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_ADD'));
            $fld11 = $frm->addButton('', 'btn_submit_cancel', t_lang('M_TXT_CANCEL'), '', ' class="inputbuttons" onclick=location.href="deals.php"')->attachField($fld);
            $redirect = 'add-deals.php?edit=' . $deal_id . '&page=' . $page . '&status=' . $get['status'] . '&step=' . ($_REQUEST['step'] + 1);
            $fld1 = $frm->addButton('', 'btn_cancel', t_lang('M_TXT_GO_TO_NEXT_STEP'), '', ' class="inputbuttons" onclick=location.href="' . $redirect . '"');
            $fld->attachField($fld1);
            //<a href="deals.php" class="button gray" style="align:right">GO TO HOME</a>
            $options .= '<div class="div-inline page-name">' . t_lang("M_TXT_SUBDEALS") . '</div>';
            $options .= '<table id="subdeal" class="tbl_form" width="100%" border="0" cellspacing="0" cellpadding="0">';
            $options .= '<th>' . t_lang("M_TXT_SUBDEAL_NAME") . '</th><th>' . t_lang("M_TXT_SUBDEAL_ORIGINAL_PRICE") . '</th><th>' . t_lang("M_TXT_SUBDEAL_DISCOUNT") . '</th><th>' . t_lang("M_TXT_SUBDEAL_DISCOUNT_IS_PERCENTAGE") . '</th><th>' . t_lang("M_TXT_STATUS") . '</th><th>' . t_lang("M_TXT_ACTION") . '</th>';
            foreach ($subdealData as $key => $value) {
                foreach ($subDealAddressCapacity as $location_id => $capacity) {
                    if ($capacity > 0 && ($dealData['deal_sub_type'] >= 1)) {
                        saveBookingRequestDate($location_id, $deal_id, $value['sdeal_id']);
                    }
                }
                if ($value['sdeal_discount_is_percentage'] == 1) {
                    $value['sdeal_discount_is_percentage'] = "Percentage";
                } else {
                    $value['sdeal_discount_is_percentage'] = "Fixed";
                }
                if ($value['sdeal_active'] == 1) {
                    $value['sdeal_active'] = '<span class="label label-primary">' . t_lang('M_TXT_ACTIVE') . '</span>';
                } else {
                    $value['sdeal_active'] = '<span class="label label-danger">' . t_lang('M_TXT_INACTIVE') . '</span>';
                }
                $options .= '<tr>';
                $options .= '<td width="20%">' . $value['sdeal_name'] . '</td><td>' . $value['sdeal_original_price'] . '</td><td>' . $value['sdeal_discount'] . '</td><td>' . $value['sdeal_discount_is_percentage'] . '</td><td>' . $value['sdeal_active'] . '</td><td><ul class="actions"><li><a href="add-deals.php?edit=' . $value['sdeal_deal_id'] . '&step=4&sub_deal_id=' . $value['sdeal_id'] . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li><li><a href="javascript:void(0);" onclick="deleteSubdeal(' . $value['sdeal_id'] . ')" title="' . t_lang('M_TXT_DELETE') . '" onclick="requestPopup(this,\'' . t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD') . '\',1);"><i class="ion-android-delete icon"></i></a></li>';
                if ($dealData['deal_sub_type'] == 1) {
                    $options .= '<li><a href="?edit=' . $deal_id . '&sub_deal_id=' . $value['sdeal_id'] . '&url=manageDates&step=' . ($_REQUEST['step']) . '" class="delete btn" title="' . t_lang('M_TXT_MANAGE_DATES') . '" ><i class="ion-calendar icon"></i></a></li>';
                }
                if ($dealData['deal_sub_type'] == 2) {
                    $options .= '<li><a href="?edit=' . $deal_id . '&sub_deal_id=' . $value['sdeal_id'] . '&url=manageOnlinebookingDates&step=' . ($_REQUEST['step']) . '" title="' . t_lang('M_TXT_MANAGE_DATES') . '" ><i class="ion-calendar icon"></i></a></li>';
                }
                $options .= '</ul></td>';
                $options .= '</tr>';
                $options .= '<tr class="dateArea" style="display:none">
				<td colspan="7">Date: <input type="text" id="datepicker"></td>
				</tr>';
            }
            $options .= '</table>';
            $frm->addHTML('', '', $options, true);
            if (!empty($_GET['sub_deal_id'])) {
                $subdeal1 = new SearchBase('tbl_sub_deals');
                $subdeal1->addCondition('sdeal_id', '=', $_GET['sub_deal_id']);
                $result1 = $subdeal1->getResultSet();
                $subdealData1 = $db->fetch($result1);
                $frm->fill($subdealData1);
            }
        }
    }
}
if ($_REQUEST['step'] == 5) {
    $deal_id = intval($_GET['edit']);
    if ($dealData['deal_type'] == 1 && $dealData['deal_sub_type'] == 1) {
        redirectUser('add-deals.php?edit=' . $deal_id . '&page=' . $page . '&status=' . $get['status'] . '&step=' . ($_REQUEST['step'] + 1));
    }
    if ($dealData['deal_type'] == 0) {
        redirectUser('add-deals.php?edit=' . $deal_id . '&page=' . $page . '&status=' . $get['status'] . '&step=' . ($_REQUEST['step'] + 1));
    }
    $frm = new Form('frmDealShipping', 'frmDealShipping');
    $frm->setValidatorJsObjectName('frmDealShipping');
    $frm->setJsErrorDisplay('afterfield');
    $frm->setTableProperties('width="100%" class = "tbl_form"');
    $options = array(
        '0' => t_lang('M_TXT_WITHIN_USA'),
        '1' => t_lang('M_TXT_WORLDWIDE'),
    );
    $fld = $frm->addSelectBox(t_lang('M_TXT_SHIPPING_TYPE'), 'deal_shipping_type', $options, '', 'onchange="showShippingField(this.value)"', 'Select', 'deal_shipping_type');
    $fld->requirements()->setRequired();
    $fld = $frm->addTextBox(t_lang('M_TXT_SHIPPING_CHARGES_(FOR_US)'), 'deal_shipping_charges_us', '', 'deal_shipping_charges_us', '');
    $fld = $frm->addTextBox(t_lang('M_TXT_SHIPPING_CHARGES_(FOR_WORLDWIDE)'), 'deal_shipping_charges_worldwide', '', 'deal_shipping_charges_worldwide', '');
    $frm->setLeftColumnProperties('width="40%"');
    $frm->addSubmitButton('', 'btn_submit', 'Submit', 'btn_submit', '');
    $fld1 = $frm->getField('btn_submit');
    $fld = $frm->addButton('', 'btn_submit_cancel', t_lang('M_TXT_CANCEL'), '', ' class="inputbuttons" onclick=location.href="deals.php"')->attachField($fld1);
    $frm->setOnSubmit('return shippingInfoValidate(this)');
}
if ($_REQUEST['step'] == 1 || !isset($_REQUEST['step'])) {
    $deal_id = intval($_GET['edit']);
    $frm = getMBSFormByIdentifier('frmDeal');
    $frm->setOnSubmit('checkformValidation(this)');
    $frm->addHiddenField('dpe_id', 'dpe_id', $digital_product['dpe_id']);
    $fld = $frm->getField('deal_type');
    $fld->options = array("0" => t_lang('M_TXT_DEAL'), "1" => t_lang('M_TXT_PRODUCT'));
    $product_sub_options = array("0" => t_lang('M_TXT_PHYSICAL_PRODUCT'), "1" => t_lang('M_TXT_DIGITAL_PRODUCT'));
    $deal_sub_options = array("0" => t_lang('M_TXT_NORMAL_DEAL'), "1" => t_lang('M_TXT_BOOKING_REQUEST'), "2" => t_lang('M_TXT_ONLINE_BOOKING'));
    $options = $deal_sub_options;
    if ($dealData['deal_type'] == 1) {
        $options = $product_sub_options;
    }
    $fld = $frm->addSelectBox(t_lang('M_TXT_SELECT_SUB_OPTION'), 'deal_sub_type', $options, '', '', '', 'deal_sub_type');
    $frm->changeFieldPosition($fld->getFormIndex(), ($frm->getField('deal_type')->getFormIndex() + 1));
    $fld = $frm->addFileUpload(t_lang('M_TXT_FIRST_OPTION'), 'dpe_product_file', 'dpe_product_file', '');
    $frm->changeFieldPosition($fld->getFormIndex(), ($frm->getField('deal_sub_type')->getFormIndex() + 1));
    $fld->field_caption = t_lang('M_TXT_FIRST_OPTION') . ' ' . '<br/><span style="color: red;">' . t_lang("M_TXT_FILE_SIZE_LESS_THAN_50MB") . '</span>';
    if (!empty($digital_product['dpe_product_file_name'])) {
        $fld->html_after_field = '<br/> <br/><span style="color: black;">' . $digital_product['dpe_product_file_name'] . '<img src="' . CONF_WEBROOT_URL . 'images/cross.png" alt="Remove" onclick="removeDigitalFile(' . $digital_product['dpe_deal_id'] . ')"><span>';
    }
    $fld = $frm->addTextBox(t_lang('M_TXT_SECOND_OPTION'), 'dpe_product_external_url', $digital_product['dpe_product_external_url']);
    $fld->field_caption = t_lang('M_TXT_SECOND_OPTION') . ' ' . '<br/><span style="color: red;">' . t_lang("M_TXT_IF_FILE_SIZE_GREATER_THAN_50MB") . '</span>';
    $frm->changeFieldPosition($fld->getFormIndex(), ($frm->getField('dpe_product_file')->getFormIndex() + 1));
    $fld = $frm->getField('deal_redeeming_instructions');
    $fld->requirements()->setCustomErrorMessage('Redeeming Instructions Mandatory.');
    $fld = $frm->getField('btn_submit');
    $fld->value = t_lang('M_TXT_SUBMIT');
    $fld->html_after_field = '<span style="color: #f00;">' . t_lang('M_TXT_NOTE_SERVER_TIME') . ' ' . displayDate(date("l M d, Y, H:i"), true, false) . '</span>';
    $fld = $frm->getField('deal_cities');
    $frm->removeField($fld);
    $fld1 = $frm->getField('deal_company');
    if ($_GET['edit'] > 0) {
        $deal_id = $_GET['edit'];
        $fld1->extra = 'onchange="addAddress(this.value,' . $deal_id . ');"';
    } else {
        $fld1->extra = 'onchange="addAddress(this.value,0);"';
    }
    $frm->addHiddenField('', 'status', $_REQUEST['status'], 'status', '');
    $fld = $frm->getField('deal_company');
    $fld->selectCaption = 'Select';
    $fld->requirements()->setRequired();
    $fld6 = $frm->getField('addAddress');
    $fld6->merge_cells = 2;
    /* removing fields for the Step 1 */
    $fld = $frm->getField('deal_image');
    $fld->extra = 'onchange="readURL(this);"';
    $fld->html_after_field = ' <img alt="" src="' . CONF_WEBROOT_URL . 'deal-image-crop.php?id=' . $deal_id . '&type=admindealPage&' . time() . '" class="deal_image">';
    $fld->field_caption = t_lang('M_TXT_DEAL_IMAGE') . '<br/>' . t_lang('M_TXT_IMAGE_SIZE_SHOULD_BE_500*500');
    $fld = $frm->getField('deal_city');
    $frm->removeField($fld);
    $fld = $frm->getField('deal_private_note');
    $frm->removeField($fld);
    $fld = $frm->getField('deal_discount');
    $fld->requirements()->setFloatPositive();
    $fld = $frm->getField('deal_company');
    $frm->removeField($fld);
    $fld = $frm->getField('attachCompany');
    $frm->removeField($fld);
    $fld = $frm->getField('addAddress');
    $frm->removeField($fld);
    $fld = $frm->getField('setCoupons');
    $frm->removeField($fld);
    $fld = $frm->getField('voucher_valid_from');
    $frm->removeField($fld);
    $fld = $frm->getField('voucher_valid_till');
    $frm->removeField($fld);
    $fld = $frm->getField('deal_min_coupons');
    $frm->removeField($fld);
    $fld = $frm->getField('deal_max_coupons');
    $frm->removeField($fld);
    $fld = $frm->getField('deal_min_buy');
    $frm->removeField($fld);
    $fld = $frm->getField('deal_max_buy');
    $frm->removeField($fld);
    $fld = $frm->getField('deal_charity');
    $frm->removeField($fld);
    $fld = $frm->getField('deal_charity_discount');
    $frm->removeField($fld);
    $fld = $frm->getField('deal_charity_discount_is_percent');
    $frm->removeField($fld);
    $fld = $frm->getField('basicInfo');
    $frm->removeField($fld);
    $fld = $frm->getField('deal_categories');
    $frm->removeField($fld);
    $fld = $frm->getField('metaInfo');
    $frm->removeField($fld);
    $fld = $frm->getField('deal_meta_title');
    $frm->removeField($fld);
    $fld = $frm->getField('deal_meta_keywords');
    $frm->removeField($fld);
    $fld = $frm->getField('deal_meta_description');
    $frm->removeField($fld);
    $fld = $frm->getField('defaultSettings');
    $frm->removeField($fld);
    $fld = $frm->getField('deal_side_deal');
    $frm->removeField($fld);
    $fld = $frm->getField('deal_instant_deal');
    $frm->removeField($fld);
    $fld = $frm->getField('deal_recent_deal');
    $frm->removeField($fld);
    $fld = $frm->getField('deal_type');
    $fld->field_caption = t_lang('M_TXT_DEAL_TYPE');
    $fld = $frm->getField('deal_is_subdeal');
    $fld->field_caption = t_lang('M_TXT_DEAL_HAS_SUBDEAL');
    $fld = $frm->getField('deal_bonus');
    $fld->html_after_field = '<a href="javascript:void(0);" title="' . t_lang('M_TXT_PORTAL_FEE_OF_DEAL') . '">[?]</a>';
    $fld = $frm->getField('deal_commission_percent');
    $fld->html_after_field = '<a href="javascript:void(0);" title="' . t_lang('M_TXT_PORTAL_FEE_OF_DEAL_PER_VOUCHER') . '">[?]</a>';
    $fld = $frm->getField('deal_original_price');
    $fld->requirements()->setFloatPositive();
    $fld->html_after_field = '<a href="javascript:void(0);" title="' . t_lang('M_TXT_DEAL_REAL_PRICE') . '">[?]</a>';
    $fld = $frm->getField('deal_start_time');
    $fld->html_after_field = '<a href="javascript:void(0);" title="' . t_lang('M_TXT_DEAL_STARTED_FROM') . '">[?]</a>';
    $fld = $frm->getField('deal_end_time');
    $fld->html_after_field = '<a href="javascript:void(0);" title="' . t_lang('M_TXT_DEAL_ENDED_ON') . '">[?]</a>';
    $arr_tax_classes = getActiveTaxClass();
    $fld = $frm->addSelectBox(t_lang('M_TXT_TAX_CLASS'), 'deal_taxclass_id', $arr_tax_classes, '', '', 'Select', 'deal_taxclass_id');
    $options = '<ul class="actions"><li><a href="javascript:void(0);" onClick="viewTaxRate();" title="' . t_lang('M_TXT_VIEW_TAX_RATE') . '"><i class="ion-eye icon"></i></a></li></ul>';
    $fld1 = $frm->addHTML('', '', $options, true);
    $fld->attachField($fld1);
    /* removing fields for the Step 1 */
    /* CODE FOR CITY WIDE */
    if (CONF_ADMIN_COMMISSION_TYPE == 2 || CONF_ADMIN_COMMISSION_TYPE == 3) {
        $fld = $frm->getField('deal_commission_percent');
        $frm->removeField($fld);
    } else {
        $fld = $frm->getField('deal_commission_percent');
        $fld->requirements()->setRequired(true);
        $fld->requirements()->setFloatPositive(true);
        $fld->requirements()->setRange(1, 100);
    }
    /* CODE FOR CITY WIDE */
    $frm->getField('deal_start_time')->requirements()->setDateTime();
    $frm->getField('deal_end_time')->requirements()->setDateTime();
    $fld1 = $frm->getField('btn_submit');
    $fld1->merge_cells = 2;
    $fld = $frm->getField('deal_start_time');
    $fld->value = displayDate(date("l M d, Y, H:i"), true, false);
    $fld = $frm->getField('deal_end_time');
    $fld->value = displayDate(Date("y-m-d H:i:s", mktime(0, 0, 0, date("m"), date("d") + 10, date("Y"))), false, false, CONF_TIMEZONE);
    $fld = $frm->addButton('', 'btn_submit_cancel', t_lang('M_TXT_CANCEL'), '', ' class="inputbuttons" onclick=location.href="deals.php"')->attachField($fld1);
    $fixed_discount_req = new FormFieldRequirement('deal_discount', t_lang('M_TXT_DISCOUNT'));
    $fixed_discount_req->setFloatPositive(true);
    $fixed_discount_req->setCompareWith('deal_original_price', 'le', t_lang('M_FRM_ORIGINAL_PRICE'));
    $percent_discount_req = new FormFieldRequirement('deal_discount', t_lang('M_TXT_DISCOUNT'));
    $percent_discount_req->setFloatPositive(true);
    $percent_discount_req->setRange(0, 100);
    $fld = $frm->getField('deal_discount_is_percent');
    $fld->id = "deal_discount_is_percent";
    $fld_req = $fld->requirements();
    $fld_req->addOnChangerequirementUpdate('1', 'ne', 'deal_discount', $fixed_discount_req);
    $fld_req->addOnChangerequirementUpdate('1', 'eq', 'deal_discount', $percent_discount_req);
}
$frm->addHiddenField('', 'step', $_REQUEST['step']);
$frm->addHiddenField('', 'deal_id', $_REQUEST['edit']);
updateFormLang($frm);
if (is_numeric($_GET['edit'])) {
    if ((checkAdminAddEditDeletePermission(5, '', 'edit'))) {
        $record = new TableRecord($mainTableName);
        if (!$record->loadFromDb($primaryKey . '=' . $_GET['edit'], true)) {
            $msg->addError($record->getError());
            redirectUser('deals.php?status=' . $_REQUEST['status']);
        } else {
            $arr = $record->getFlds();
            $arr['btn_submit'] = t_lang('M_TXT_UPDATE');
            $frm->addHiddenField('', 'old_deal_name', $arr['deal_name']);
            $rs_cats = $db->query("select dc_cat_id, dc_cat_id as cat_id from tbl_deal_to_category where  dc_deal_id=" . $_GET['edit']);
            $arr['deal_categories'] = $db->fetch_all_assoc($rs_cats);
            if ($_GET['step'] == 2) {
                $fld = $frm->getField('deal_company');
                if (CONF_ADMIN_COMMISSION_TYPE == 2) {
                    $arr['city_deal'] = $arr['deal_city'];
                }
                if (CONF_ADMIN_COMMISSION_TYPE == 3) {
                    $arr['company_deal'] = $arr['deal_company'];
                }
            }
            /* $frm->fill($arr);
              $msg->addMsg('Change the values and submit.'); */
            $frm->addHiddenField('', 'deal_type_status', $arr['deal_type']);
            if ($_REQUEST['step'] == 5) {
                if ($dealData['deal_type'] == 1 && $dealData['deal_sub_type'] == 1) {
                    redirectUser('add-deals.php?edit=' . $deal_id . '&page=' . $page . '&status=' . $get['status'] . '&step=' . ($_REQUEST['step'] + 1));
                }
                if ($arr['deal_shipping_type'] == 0 || $arr['deal_shipping_type'] == 1) {
                    if ($arr['deal_shipping_type'] == 0) {
                        $fld = $frm->getField('deal_shipping_charges_worldwide');
                        $fld->fldCellExtra = 'style="display:none;"';
                        $fld->captionCellExtra = 'style="display:none;"';
                    }
                    if ($arr['deal_shipping_type'] == 1) {
                        $fld = $frm->getField('deal_shipping_charges_us');
                        $fld->fldCellExtra = 'style="display:none;"';
                        $fld->captionCellExtra = 'style="display:none;"';
                    }
                }
            }
            if ($_REQUEST['step'] == 4) {
                if ($arr['deal_type'] == 0 && !isset($_REQUEST['sub_deal_id'])) {
                    $arr['btn_submit'] = t_lang('M_TXT_ADD');
                }
            }
            if ($_REQUEST['step'] == 9) {
                $fld = $frm->getField('deal_recent_deal');
                $frm->removeField($fld);
            }
            fillForm($frm, $arr);
        }
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
if ($_GET['catRemove'] > 0) {
    $success = $db->query("delete from tbl_deal_to_category where dc_deal_id =" . $_GET['edit'] . " and dc_cat_id=" . $_GET['catRemove']);
    if ($success) {
        $msg->addMsg(t_lang('M_TXT_CATEGORY_REMOVED'));
    }
    redirectUser('add-deals.php?edit=' . $_GET['edit'] . '&page=' . $page . '&status=' . $get['status'] . '&step=4');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['btn_search']) && isset($_POST['btn_submit'])) {
    $post = getPostedData();
    if (!isset($post['deal_featured'])) {
        $post['deal_featured'] = 0;
    }
    if ($post['step'] == 1 && $_GET['edit'] > 0) {
        if (!isset($post['deal_is_subdeal']) || $post['deal_type'] == 1) {
            $post['deal_is_subdeal'] = 0;
            deleteAllSubdealData($post);
        }
        if ($post['deal_is_subdeal'] == 0 && $post['deal_sub_type'] == 1 && $post['deal_type'] == 0) {
            deleteAllSubdealData($post);
        }
        if ($post['deal_is_subdeal'] == 1 && $post['deal_sub_type'] >= 1 && $post['deal_type'] == 0) {
            deletebookingDataofWithoutSubDeal($post);
        }
    }
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error)
            $msg->addError($error);
    } else {
        $succeed = true;
        /* Image Validations if uploaded */
        if (is_uploaded_file($_FILES['deal_image']['tmp_name'])) {
            $ext = strtolower(strrchr($_FILES['deal_image']['name'], '.'));
            if ((!in_array($ext, array('.gif', '.jpg', '.jpeg', '.png'))) || ($_FILES['deal_image']['size'] > CONF_IMAGE_MAX_SIZE)) {
                $msg->addError(t_lang('M_TXT_DEAL') . ' ' . t_lang('M_TXT_IMAGE_NOT_SUPPORTED_OR_SIZE_EXCEEDED'));
                fillForm($frm, $post);
                $succeed = false;
            }
        }
        /* Image Validations if uploaded */
        if (is_uploaded_file($_FILES['dpe_product_file']['tmp_name'])) {
            $dP = new DigitalProduct();
            if (false === $dP->checkProductOptionImageValid($_FILES['dpe_product_file'])) {
                $msg->addError(t_lang('M_TXT_PRODUCT_OPTION') . ' ' . t_lang('M_TXT_IMAGE_NOT_SUPPORTED_OR_SIZE_EXCEEDED'));
                fillForm($frm, $post);
                $succeed = false;
            }
        }
        if (true === $succeed) {
            if (empty($post['deal_taxclass_id'])) {
                $post['deal_taxclass_id'] = 0;
            }
            $record = new TableRecord($mainTableName);
            /* $record->assignValues($post); */
            $arr_lang_independent_flds = array('deal_id', 'deal_company', 'deal_city', 'deal_start_time', 'deal_end_time', 'voucher_valid_from', 'voucher_valid_till', 'deal_min_coupons', 'deal_max_coupons', 'deal_min_buy', 'deal_max_buy', 'deal_side_deal', 'deal_featured', 'deal_instant_deal', 'deal_main_deal', 'deal_recent_deal', 'deal_original_price', 'deal_discount', 'deal_discount_is_percent', 'deal_charity', 'deal_charity_discount', 'deal_charity_discount_is_percent', 'deal_bonus', 'deal_commission_percent', 'deal_addedon', 'deal_tipped_at', 'deal_status', 'deal_deleted', 'deal_complete', 'deal_taxclass_id', 'mode', 'btn_submit', 'deal_location', 'deal_type', 'deal_featured', 'deal_is_subdeal', 'deal_shipping_type', 'deal_shipping_charges_us', 'deal_shipping_charges_worldwide');
            assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
            //mysql_func_now() changed to date('Y-m-d H:i:s')
            if (!($post[$primaryKey] > 0))
                $record->setFldValue('deal_addedon', date('Y-m-d H:i:s'), true);
            if (!isset($post['old_deal_name']) && ($post['step'] == 1)) {
                $record->setFldValue('deal_name', $post['deal_name']);
            }
            if ($post['deal_id'] && $post['deal_sub_type'] == 2 && $post['deal_type'] == 0) {
                updateOnlineBookingRequestData($post['deal_id'], $post);
            }
            //set Product tipping point
            if ($post['deal_type'] == 1) {
                $record->setFldValue('deal_min_coupons', 1);
            }
            //echo '<pre>';print_r($post); exit;
            $total_dac_address_capacity = count($post['dac_address_capacity']);
            $total_dac_address_ids = count($post['dac_address_id']);
            $totalCapacity = 0;
            $capacity_chk = 0;
            $checkVar = '';
            $day_diff = 1;
            if ($dealData['deal_sub_type'] == 2) {
                $ts1 = strtotime(date('Y-m-d', strtotime($dealData['deal_start_time'])));
                $ts2 = strtotime(date('Y-m-d', strtotime($dealData['deal_end_time'])));
                $seconds_diff = $ts2 - $ts1;
                $day_diff = floor($seconds_diff / 3600 / 24);
            }
            $j = 0;
            for ($i = 0; $i <= $total_dac_address_capacity; $i++) {
                if ($post['dac_address_capacity'][$i] != "" && intval($post['dac_address_capacity'][$i]) > 0) {
                    // $capacityCount++;
                    if (intval($post['dac_address_id'][$j]) > 0) {
                        $sold_for_loc = checkDealSoldForCompanyLoc(intval($post['deal_id']), intval($post['dac_address_id'][$j]));
                        if (($post['dac_address_capacity'][$i] * $day_diff) >= $sold_for_loc) {
                            $capacity_chk++;
                        }
                        if (($post['dac_address_capacity'][$i] * $day_diff) < ($post['deal_max_buy']) && ($post['dac_address_capacity'][$i] * $day_diff) != 0 && ($post['dac_address_capacity'][$i] * $day_diff) >= ($post['deal_min_buy'])) {
                            $checkVar = 'true';
                        }
                        $j++;
                    }
                    $totalCapacity += ($post['dac_address_capacity'][$i] * $day_diff);
                }
            }
            /*             * * 11 DEC 2019 ** */
            $allLocatiopnCap = array_sum($post['dac_address_capacity']);
            $checkVar = '';
            if (($allLocatiopnCap * $day_diff) < ($post['deal_max_buy']) && ($allLocatiopnCap * $day_diff) != 0 && ($allLocatiopnCap * $day_diff) >= ($post['deal_min_buy'])) {
                $checkVar = 'true';
            }
            //echo $checkVar; exit;
            if ($post['step'] == 2 || $post['step'] == 3) {
                if ($post['dac_address_id'] != "" && $totalCapacity > 0) {
                    if ($total_dac_address_ids == $capacity_chk) {
                        //echo $total_dac_address_capacity;exit;
                        if ($totalCapacity >= $post['deal_min_coupons'] && $totalCapacity == ($post['deal_max_coupons']) && $checkVar == '') {
                            if ((checkAdminAddEditDeletePermission(5, '', 'edit'))) {
                                if ($post[$primaryKey] > 0)
                                    $success = $record->update($primaryKey . '=' . $post[$primaryKey]);
                            }
                            if ((checkAdminAddEditDeletePermission(5, '', 'add'))) {
                                if ($post[$primaryKey] == '')
                                    $success = $record->addNew();
                            }
                            if ($success) {
                                $deal_id = ($post[$primaryKey] > 0) ? $post[$primaryKey] : $record->getId();
                                ############## CODE FOR INSERT COMPANY ID AND ADDRESS ID FOR MULTIPLE LOCATION ###############
                                if (($dealData['deal_sub_type'] >= 1 && $dealData['deal_type'] == 0) || ($dealData['deal_is_subdeal'] == 1 && $dealData['deal_type'] == 0)) {
                                    deletebookingdateForLocation($deal_id);
                                }
                                /* echo "<pre>";
                                  print_r($post); exit(); */
                                if ($dealData['deal_is_subdeal'] == 0 && $dealData['deal_sub_type'] == 2 && $dealData['deal_type'] == 0) {
                                    foreach ($_POST['dac_address_capacity'] as $key => $val) {
                                        if ($_POST['dac_address_capacity'][$key] != 0)
                                            $dac_address_capacity[] = $val;
                                    }
                                    $count = 0;
                                    foreach ($_POST['dac_address_id'] as $addressid) {
                                        $data[$addressid] = $dac_address_capacity[$count];
                                        $count++;
                                    }
                                    updateOnlineBookingRequestData($deal_id, $data);
                                }
                                $db->query("delete from tbl_deal_address_capacity where dac_deal_id=" . $deal_id . " and dac_sub_deal_id =0");
                                $count = 0;
                                foreach ($_POST['dac_address_capacity'] as $key => $val) {
                                    if ($_POST['dac_address_capacity'][$key] != 0)
                                        $dac_address_capacity[] = $val;
                                }
                                if (is_array($_POST['dac_address_id'])) {
                                    foreach ($_POST['dac_address_id'] as $addressid) {
                                        if ($dealData['deal_is_subdeal'] == 0 && $dealData['deal_sub_type'] == 1 && $dealData['deal_type'] == 0) {
                                            saveBookingRequestDate($addressid, $deal_id);
                                        }
                                        $db->insert_from_array('tbl_deal_address_capacity', array('dac_deal_id' => $deal_id, 'dac_address_id' => $addressid, 'dac_address_capacity' => $dac_address_capacity[$count]));
                                        $count++;
                                    }
                                }
                                ############## CODE FOR INSERT COMPANY ID AND ADDRESS ID FOR MULTIPLE LOCATION ###############
                                if ($post['status'] == 'upcoming') {
                                    if ($post['deal_main_deal'] != "") {
                                        if (!$db->update_from_array('tbl_deals', array('deal_main_deal' => $post['deal_main_deal']), 'deal_id=' . $deal_id))
                                            dieJsonError($db->getError());
                                        if (!$db->update_from_array('tbl_deals', array('deal_main_deal' => 0), 'deal_city=' . $post['deal_city'] . ' and deal_id!=' . $deal_id . ' and deal_status = 0'))
                                            dieJsonError($db->getError());
                                    }
                                }
                                if ($post['status'] == 'active') {
                                    if ($post['deal_main_deal'] != "") {
                                        if (!$db->update_from_array('tbl_deals', array('deal_main_deal' => $post['deal_main_deal']), 'deal_id=' . $deal_id))
                                            dieJsonError($db->getError());
                                        if (!$db->update_from_array('tbl_deals', array('deal_main_deal' => 0), 'deal_city=' . $post['deal_city'] . ' and deal_id!=' . $deal_id . ' and deal_status > 0'))
                                            dieJsonError($db->getError());
                                    }
                                }
                                ############### DISPLAY AS MAIN DEAL #################
                                ############### DISPLAY AS RECENT DEAL #################
                                if ($post['deal_recent_deal'] != 1) {
                                    if (!$db->update_from_array('tbl_deals', array('deal_recent_deal' => 0), 'deal_id=' . $deal_id))
                                        dieJsonError($db->getError());
                                }
                                ############### DISPLAY AS RECENT DEAL #################
                                /* CODE FOR CITY WIDE */
                                if (CONF_ADMIN_COMMISSION_TYPE == 2) {
                                    if (($post['deal_city'] != $post['city_deal']) || $post['deal_commission_percent'] == '0.00') {
                                        if ($post['step'] == 2) {
                                            $rs1 = $db->query("select city_deal_commission_percent from tbl_cities where city_id=" . intval($post['deal_city']));
                                            $row1 = $db->fetch($rs1);
                                            $commission_from = (float) $row1['city_deal_commission_percent'];
                                            if ($commission_from > 0) {
                                                if (!$db->update_from_array('tbl_deals', array('deal_commission_percent' => $commission_from), 'deal_id=' . $deal_id))
                                                    dieJsonError($db->getError());
                                            }
                                        }
                                    }
                                }
                                if (CONF_ADMIN_COMMISSION_TYPE == 3) {
                                    if (($post['deal_company'] != $post['company_deal']) || $post['deal_commission_percent'] == '0.00') {
                                        if ($post['step'] == 2) {
                                            $rs1 = $db->query("select company_deal_commission_percent from tbl_companies where company_id=" . intval($post['deal_company']));
                                            $row1 = $db->fetch($rs1);
                                            $commission_from = (float) $row1['company_deal_commission_percent'];
                                            if ($commission_from > 0) {
                                                if (!$db->update_from_array('tbl_deals', array('deal_commission_percent' => $commission_from), 'deal_id=' . $deal_id))
                                                    dieJsonError($db->getError());
                                            }
                                        }
                                    }
                                }
                                /* CODE FOR CITY WIDE */
                                if ($post['step'] == 3) {
                                    if ($dealData['deal_is_subdeal'] != 1) {
                                        if (!$db->update_from_array('tbl_deals', array('deal_complete' => 1), 'deal_id=' . $deal_id))
                                            dieJsonError($db->getError());
                                    }
                                }
                                $msg->addMsg(t_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
                                redirectUser('add-deals.php?edit=' . $deal_id . '&page=' . $page . '&status=' . $get['status'] . '&step=' . ($_REQUEST['step'] + 1));
                                exit();
                            } else {
                                $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
                                fillForm($frm, $post);
                            }
                        } else {
                            $msg->addError(unescape_attr(t_lang('M_MSG_DEAL_CAPACITY_EXCEED')));
                            fillForm($frm, $post);
                        }
                    } else {
                        $msg->addError(t_lang('M_MSG_CAPACITY_MUST_BE_GREATER_THAN_SOLD_VOUCHERS_FOR_EACH_LOCATION'));
                        fillForm($frm, $post);
                    }
                } else {
                    if ($post['dac_address_id'] == "") {
                        $msg->addError(t_lang('M_MSG_Please_select_at_least_one_address_and_add_a_valid_capacity'));
                    } else {
                        $msg->addError(t_lang('M_MSG_PLEASE_ENTER_DEAL_CAPACITY'));
                    }
                    fillForm($frm, $post);
                }
            } else if ($post['step'] == 6) {
                if ((checkAdminAddEditDeletePermission(5, '', 'edit'))) {
                    if ($post[$primaryKey] > 0)
                        $success = $record->update($primaryKey . '=' . $post[$primaryKey]);
                }
                if ((checkAdminAddEditDeletePermission(5, '', 'add'))) {
                    if ($post[$primaryKey] == '')
                        $success = $record->addNew();
                }
                if ($success) {
                    $deal_id = ($post[$primaryKey] > 0) ? $post[$primaryKey] : $record->getId();
                    if ($post['step'] == 6) {
                        if (count($post['deal_categories']) > 0) {
                            $db->query("delete from tbl_deal_to_category where dc_deal_id=" . $deal_id);
                            if (is_array($post['deal_categories'])) {
                                foreach ($post['deal_categories'] as $catid) {
                                    $db->insert_from_array('tbl_deal_to_category', array('dc_deal_id' => $deal_id, 'dc_cat_id' => $catid));
                                }
                            }
                            $msg->addMsg(t_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
                            redirectUser('add-deals.php?edit=' . $deal_id . '&page=' . $page . '&status=' . $get['status'] . '&step=' . ($_REQUEST['step'] + 1));
                            exit();
                        } else {
                            $msg->addError(t_lang('M_TXT_PLAESE_CHOOSE_ATLEAST_ONE_CATEGORY'));
                        }
                    }
                } else {
                    $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
                    fillForm($frm, $post);
                }
            } else if ($post['step'] == 4) {
                $deal_id = $post['deal_id'];
                if ($post['deal_type_status'] == 1) {
                    $attrQuant = 0;
                    $parentCheck = false;
                    if (!empty($post['deal_option'])) {
                        if ($post['deal_option'][0]['parent_option_id'] != 0) {
                            $msg->addError(t_lang('M_TXT_PLEASE_SELECT_PARENT_OPTION_AT_LEVEL_ONE'));
                            redirectUser();
                        }
                        foreach ($post['deal_option'] as $key => $deal_option) {
                            if ($deal_option['parent_option_id'] == 0) {
                                $parentCheck = true;
                                foreach ($deal_option['deal_option_value'] as $key1 => $suboptions) {
                                    $attrQuant += $suboptions['quantity'];
                                }
                            }
                        }
                        if ($attrQuant > $dealData['deal_max_coupons']) {
                            $msg->addError(t_lang('M_TXT_DEAL_ATTRIBUTE_VOUCHER_SHOULD_NOT_EXCEED_DEAL_MAX_COUPON'));
                            redirectUser();
                        }
                        if (!$parentCheck || ($attrQuant == 0)) {
                            $msg->addError('Please select parent quantity');
                            redirectUser();
                        }
                    }
                    if (isset($post['deal_option'])) {
                        $parent_counter = 1;
                        foreach ($post['deal_option'] as $key => $deal_option) {
                            if ($deal_option['type'] == "select") {
                                $record = new TableRecord('tbl_deal_option');
                                $deal_option_data = array(
                                    'deal_id' => $post['deal_id'],
                                    'option_id' => $deal_option['option_id'],
                                    'deal_option_id' => $deal_option['deal_option_id'],
                                    'parent_option_id' => $deal_option['parent_option_id'],
                                    'required' => $deal_option['required']
                                );
                                $record->assignValues($deal_option_data);
                                $record->addNew(array('IGNORE'), $deal_option_data);
                                if (!empty($deal_option['deal_option_id'])) {
                                    $deal_option_id = $deal_option['deal_option_id'];
                                } else {
                                    $deal_option_id = $record->getId();
                                }
                                if (isset($deal_option['deal_option_value']) && count($deal_option['deal_option_value']) > 0) {
                                    foreach ($deal_option['deal_option_value'] as $deal_option_value) {
                                        $record = new TableRecord('tbl_deal_option_value');
                                        $deal_option_value_data = array(
                                            'deal_option_id' => $deal_option_id,
                                            'deal_option_value_id' => $deal_option_value['deal_option_value_id'],
                                            'deal_id' => $deal_id,
                                            'option_value_id' => $deal_option_value['option_value_id'],
                                            'quantity' => $deal_option_value['quantity'],
                                            'parent_option_value_id' => $deal_option_value['parent_option_value_id'],
                                            'price_prefix' => $deal_option_value['price_prefix'],
                                            'price' => $deal_option_value['price']
                                        );
                                        $record->assignValues($deal_option_value_data);
                                        $record->addNew(array('IGNORE'), $deal_option_value_data);
                                    }
                                } else {
                                    $whr = array('smt' => 'deal_option_id = ?', 'vals' => array($deal_option_id));
                                    $db->deleteRecords('tbl_deal_option', $whr);
                                }
                            }
                        }
                    }
                    $msg->addMessage(t_lang('M_TXT_OPTIONS_UPDATED_SUCCESSFULLY'));
                    /* redirectUser('add-deals.php?edit=' . $deal_id . '&step='.($_REQUEST['step'])); */
                    redirectUser('add-deals.php?edit=' . $deal_id . '&step=' . ($_REQUEST['step'] + 1));
                } else {
                    $saveSubdealData = saveSubdealData($deal_id, $dealData);
                    if ($saveSubdealData) {
                        redirectUser('add-deals.php?edit=' . $deal_id . '&step=' . ($_REQUEST['step']));
                    }
                }
                //  exit;
            } else if ($post['step'] == 5) {
                if ((checkAdminAddEditDeletePermission(5, '', 'edit'))) {
                    if ($post['deal_shipping_type'] == 0 && $post['deal_shipping_charges_us'] == '') {
                        $msg->addError(t_lang('M_TXT_SHIPPING_CHARGES_FOR_USA_IS_MANDATORY'));
                        redirectUser('add-deals.php?edit=' . $deal_id . '&step=' . ($_REQUEST['step']));
                    }
                    if ($post['deal_shipping_type'] == 1 && $post['deal_shipping_charges_worldwide'] == '') {
                        $msg->addError(t_lang('M_TXT_SHIPPING_CHARGES_FOR_WORLDWIDE_IS_MANDATORY'));
                        redirectUser('add-deals.php?edit=' . $deal_id . '&step=' . ($_REQUEST['step']));
                    }
                    if ($post[$primaryKey] > 0)
                        $success = $record->update($primaryKey . '=' . $post[$primaryKey]);
                    if ($success) {
                        $msg->addMessage(t_lang('M_TXT_SHIPPING_DETAILS_UPDATED_SUCCESSFULLY'));
                        //redirectUser('deals.php?page=' . $page . '&status=' . $get['status']);
                        redirectUser('add-deals.php?edit=' . $deal_id . '&page=' . $page . '&status=' . $get['status'] . '&step=' . ($_REQUEST['step'] + 1));
                        exit();
                    } else {
                        $msg->addError(t_lang('M_TXT_NO_PERMISSION') . $record->getError());
                        fillForm($frm, $post);
                    }
                } else {
                    $msg->addError(t_lang('M_TXT_NO_PERMISSION') . $record->getError());
                    fillForm($frm, $post);
                }
            } else {
                if ((checkAdminAddEditDeletePermission(5, '', 'edit'))) {
                    if ($post[$primaryKey] > 0)
                        $success = $record->update($primaryKey . '=' . $post[$primaryKey]);
                }
                if ((checkAdminAddEditDeletePermission(5, '', 'add'))) {
                    if ($post[$primaryKey] == '')
                        $success = $record->addNew();
                }
                if ($success) {
                    if ($post['step'] == 1 && $_GET['edit'] > 0) {
                        if ($post['deal_sub_type'] >= 1 && $post['deal_type'] == 0) {
                            savenewBookingDates($post['deal_id']);
                        }
                    }
                    $deal_id = ($post[$primaryKey] > 0) ? $post[$primaryKey] : $record->getId();
                    $dP = new DigitalProduct();
                    if (isset($post['dpe_product_external_url']) && $post['step'] == 1) {
                        $dpadded = $dP->saveDownloadlinkDigitalProduct($deal_id);
                        if ($dpadded) {
                            $digital_product = $dP->getDigitalProductRecord($deal_id);
                        }
                    }
                    $dP->uploadDigitalProduct($deal_id, $digital_product);
                    if (is_uploaded_file($_FILES['deal_image']['tmp_name'])) {
                        $flname = time() . '_' . $_FILES['deal_image']['name'];
                        if (!move_uploaded_file($_FILES['deal_image']['tmp_name'], DEAL_IMAGES_PATH . $flname)) {
                            $msg->addError(t_lang('M_TXT_FILE_COULD_NOT_SAVE'));
                        } else {
                            $getImg = $db->query("select * from tbl_deals where deal_id='" . $deal_id . "'");
                            $imgRow = $db->fetch($getImg);
                            unlink(DEAL_IMAGES_PATH . $imgRow['deal_img_name' . $_SESSION['lang_fld_prefix']]);
                            $db->update_from_array('tbl_deals', array('deal_img_name' => $flname), 'deal_id=' . $deal_id);
                        }
                    }
                    ############### DISPLAY AS MAIN DEAL #################
                    if ($post['status'] == 'upcoming') {
                        if ($post['deal_main_deal'] != "") {
                            if (!$db->update_from_array('tbl_deals', array('deal_main_deal' => $post['deal_main_deal']), 'deal_id=' . $deal_id))
                                dieJsonError($db->getError());
                            if (!$db->update_from_array('tbl_deals', array('deal_main_deal' => 0), 'deal_city=' . $post['deal_city'] . ' and deal_id!=' . $deal_id . ' and deal_status = 0'))
                                dieJsonError($db->getError());
                        }
                    }
                    if ($post['status'] == 'active') {
                        if ($post['deal_main_deal'] != "") {
                            if (!$db->update_from_array('tbl_deals', array('deal_main_deal' => $post['deal_main_deal']), 'deal_id=' . $deal_id))
                                dieJsonError($db->getError());
                            if (!$db->update_from_array('tbl_deals', array('deal_main_deal' => 0), 'deal_city=' . $post['deal_city'] . ' and deal_id!=' . $deal_id . ' and deal_status > 0'))
                                dieJsonError($db->getError());
                        }
                    }
                    ############### DISPLAY AS MAIN DEAL #################
                    ############### DISPLAY AS RECENT DEAL #################
                    if ($post['deal_recent_deal'] != 1) {
                        if (!$db->update_from_array('tbl_deals', array('deal_recent_deal' => 0), 'deal_id=' . $deal_id))
                            dieJsonError($db->getError());
                    }
                    ############### DISPLAY AS RECENT DEAL #################
                    $msg->addMsg(t_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
                    if ($post['step'] == 9) {
                        redirectUser('deals.php');
                        exit();
                    }
                    if ($_REQUEST['step'] == 4 && $post['deal_type_status'] == 0) {
                        redirectUser('add-deals.php?edit=' . $deal_id . '&page=' . $page . '&status=' . $get['status'] . '&step=' . ($_REQUEST['step'] + 1));
                        /* redirectUser('deals.php?page='.$page.'&status='.$get['status']); */
                        exit();
                    } else {
                        redirectUser('add-deals.php?edit=' . $deal_id . '&page=' . $page . '&status=' . $get['status'] . '&step=' . ($_REQUEST['step'] + 1));
                        exit();
                    }
                } else {
                    $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
                    /* $frm->fill($post); */
                    fillForm($frm, $post);
                }
            }
        }
    }
}
require_once './header.php';
?>
<script type="text/javascript" src="<?php echo CONF_WEBROOT_URL; ?>js/jquery.autocomplete.js"></script>
<link rel="stylesheet" href="<?php echo CONF_WEBROOT_URL; ?>css/jquery.autocomplete.css" type="text/css" />
<script type="text/javascript">
    var txtoops = "<?php echo addslashes(t_lang('M_TXT_INTERNAL_ERROR')); ?>";
    var txtreload = "<?php echo addslashes(t_lang('M_TXT_PLEASE_RELOAD_PAGE_AND_TRY_AGAIN')); ?>";
    var txtloading = "<?php echo addslashes(t_lang('M_TXT_LOADING')); ?>";
    txt_msg_capacity_greater_than_sold = "<?php echo addslashes(t_lang('M_MSG_CAPACITY_MUST_BE_GREATER_THAN_SOLD_VOUCHERS_FOR_EACH_LOCATION')); ?>";
</script>
<link href="<?php echo CONF_WEBROOT_URL; ?>css/prettyPhoto.css" rel="stylesheet" type="text/css" />
<script src="<?php echo CONF_WEBROOT_URL; ?>js/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">
    var deal_id = '<?php echo (intval($_GET['edit']) > 0) ? intval($_GET['edit']) : ''; ?>';
    $(document).ready(function () {
        $(" a[rel^='prettyPhoto']").prettyPhoto({theme: 'facebook', social_tools: false});
        $("#deal-category").autocomplete(
                "autocomplete.php",
                {
                    extraParams: {'deal_id': deal_id},
                    onItemSelect: function (li) {
                        /* alert(li.extra[0]); */
                        var cat_id = li.extra[0];
                        callAjax('deals-ajax.php', 'mode=UpdateCategory&cat_id=' + cat_id + '&deal_id=' + deal_id, function (t) {
                            location.reload();
                        });
                    }
                }
        );
        $('#cat-display').html('Loading...');
        callAjax('deals-ajax.php', 'mode=selectedCategory&deal_id=' + deal_id, function (t) {
            $('#cat-display').html(t);
        });
    });
</script>
<script type="text/javascript" src="<?php echo CONF_WEBROOT_URL; ?>js/jquery.hoverIntent.minified.js"></script>
<script type="text/javascript" src="<?php echo CONF_WEBROOT_URL; ?>js/jquery.naviDropDown.1.0.js"></script>
<script type="text/javascript">
    var checkAdressMsg = '<?php echo addslashes(t_lang('M_MSG_PLEASE_SELECT_AT_LEAST_ONE_ADDRESS')); ?>';
    $(function () {
        $('.navigation_vert').naviDropDown({
            dropDownWidth: '350px',
            orientation: 'vertical'
        });
    });
    var confirmMsg = '<?php echo addslashes(t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD')); ?>';
</script>
<link rel="stylesheet" type="text/css" href="plugins/jquery.jqplot.css" />
  <!--[if lt IE 9]><script language="javascript" type="text/javascript" src="plugins/excanvas.js"></script><![endif]-->
<!-- BEGIN: load jqplot -->
<script language="javascript" type="text/javascript" src="plugins/jquery.jqplot.js"></script>
<script language="javascript" type="text/javascript" src="plugins/jqplot.pieRenderer.js"></script>
<!-- END: load jqplot -->
<?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['btn_search']) && isset($_POST['btn_submit'])) { ?>
    <script type="text/javascript">
        addAddress('<?php echo $post['deal_company']; ?>', '0');
    </script>
<?php } ?>
<?php
$arr_bread = array(
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    '' => t_lang('M_TXT_DEALS')
);
if ($_REQUEST['status'] == "") {
    $class = 'class="selected"';
} else {
    $tabStatus = $_REQUEST['status'];
    $tabClass = 'class="selected"';
    if ($_REQUEST['status'] == 'active')
        $class = 'class="selected"';
    else
        $class = '';
}
?>
<ul class="nav-left-ul">
    <li><a href="deals.php?status=active" <?php echo $class; ?>><?php echo t_lang('M_TXT_ACTIVE'); ?> <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?> </a></li>
    <li><a href="deals.php?status=expired" <?php if ($tabStatus == 'expired') echo $tabClass; ?>><?php echo t_lang('M_TXT_EXPIRED'); ?> <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?> </a></li>
    <li><a href="deals.php?status=upcoming" <?php if ($tabStatus == 'upcoming') echo $tabClass; ?>><?php echo t_lang('M_TXT_UPCOMING'); ?>  <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?></a></li>
    <li><a href="deals.php?status=un-approval" <?php if ($tabStatus == 'un-approval') echo $tabClass; ?>><?php echo t_lang('M_TXT_UNAPPROVED'); ?>  <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?></a></li>
    <li><a href="deals.php?status=rejected" <?php if ($tabStatus == 'rejected') echo $tabClass; ?>><?php echo t_lang('M_TXT_REJECTED'); ?>  <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?></a></li>
    <li><a href="deals.php?status=cancelled" <?php if ($tabStatus == 'cancelled') echo $tabClass; ?>><?php echo t_lang('M_TXT_CANCELLED'); ?>  <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?></a></li>
    <li><a href="deals.php?status=purchased" <?php if ($tabStatus == 'purchased') echo $tabClass; ?>><?php echo t_lang('M_TXT_MINIMUM_ONE_COUPON_SOLD'); ?> </a></li>
    <li><a href="deals.php?status=incomplete" <?php if ($tabStatus == 'incomplete') echo $tabClass; ?>><?php echo t_lang('M_TXT_INCOMPLETE'); ?>  <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?></a></li>
</ul>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo $_REQUEST['status']; ?> <?php echo t_lang('M_TXT_DEALS'); ?>
            <?php if (checkAdminAddEditDeletePermission(5, '', 'add')) { ?>
                <ul class="actions right">
                    <li class="droplink">
                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                        <div class="dropwrap">
                            <ul class="linksvertical">
                                <li>
                                    <a href="?page=<?php echo $page; ?>&add=new&status=<?php echo $_REQUEST['status']; ?>"><?php echo t_lang('M_TXT_ADD'); ?>  <?php echo t_lang('M_TXT_DEALS'); ?> / <?php echo t_lang('M_TXT_PRODUCTS'); ?> </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            <?php } ?>
        </div>
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?>
        <?php if (isset($_SESSION['errs'][0])) { ?>
            <div class="redtext"><?php echo stripcslashes($msg->display()); ?> </div>
        <?php } if (isset($_SESSION['msgs'][0])) { ?>
            <div class="greentext"> <?php echo $msg->display(); ?> </div>
        <?php } ?>
    <?php } ?>
    <?php
    if (is_numeric($_GET['edit']) || $_GET['add'] == 'new') {
        if (!isset($_GET['edit']))
            $edit = 0;
        else
            $edit = $_GET['edit'];
        ?>
        <?php if ($_REQUEST['step'] == 2) { ?>
            <script type="text/javascript">
                $(document).ready(function () {
                    addAddress(document.frmDealLocation.deal_company.value,<?php echo $edit; ?>);
                });</script>
        <?php } ?>
        <?php if ($_REQUEST['step'] == 3) { ?>
            <script type="text/javascript">
                $(document).ready(function () {
                    addAddress(document.frmDealVoucher.deal_company.value,<?php echo $edit; ?>);
                });
            </script>
        <?php } ?>
        <?php if ((checkAdminAddEditDeletePermission(5, '', 'add')) || (checkAdminAddEditDeletePermission(5, '', 'edit'))) { ?>
            <div class="box">
                <div class="content">
                    <div class="tabsholder">
                        <ul class="tabs">
                            <?php
                            if ($_REQUEST['step'] == 1) {
                                if ($_GET['edit'] > 0) {
                                    $checkImg1 = '<img alt="" src="images/checkmark.png">';
                                } else {
                                    $checkImg1 = '<img alt="" src="images/checkmark_red.png">';
                                }
                            } else {
                                if ($_GET['edit'] > 0) {
                                    $checkImg1 = '<img alt="" src="images/checkmark.png">';
                                } else {
                                    $checkImg1 = '<img alt="" src="images/checkmark_red.png">';
                                }
                            }
                            if ($_REQUEST['step'] == 2) {
                                if (($arr['deal_city'] > 0 || $arr['deal_city'] == 0 ) && $arr['deal_company'] > 0) {
                                    $checkImg2 = '<img alt="" src="images/checkmark.png">';
                                } else {
                                    $checkImg2 = '<img alt="" src="images/checkmark_red.png">';
                                }
                            } else {
                                if (($arr['deal_city'] > 0 || $arr['deal_city'] == 0 ) && $arr['deal_company'] > 0) {
                                    $checkImg2 = '<img alt="" src="images/checkmark.png">';
                                } else {
                                    $checkImg2 = '<img alt="" src="images/checkmark_red.png">';
                                }
                            }
                            if ($_REQUEST['step'] == 3) {
                                if ($arr['deal_min_coupons'] > 0 && $arr['deal_max_buy'] > 0) {
                                    $checkImg3 = '<img alt="" src="images/checkmark.png">';
                                } else {
                                    $checkImg3 = '<img alt="" src="images/checkmark_red.png">';
                                }
                            } else {
                                if ($arr['deal_min_coupons'] > 0 && $arr['deal_max_buy'] > 0) {
                                    $checkImg3 = '<img alt="" src="images/checkmark.png">';
                                } else {
                                    $checkImg3 = '<img alt="" src="images/checkmark_red.png">';
                                }
                            }
                            if ($_REQUEST['step'] == 6) {
                                if (count($arr['deal_categories']) > 0) {
                                    $checkImg6 = '<img alt="" src="images/checkmark.png">';
                                } else {
                                    $checkImg6 = '<img alt="" src="images/checkmark_red.png">';
                                }
                            } else {
                                if (count($arr['deal_categories']) > 0) {
                                    $checkImg6 = '<img alt="" src="images/checkmark.png">';
                                } else {
                                    $checkImg6 = '<img alt="" src="images/checkmark_red.png">';
                                }
                            }
                            if ($_REQUEST['step'] == 7) {
                                $checkImg7 = '<img alt="" src="images/checkmark.png">';
                            } else {
                                if ($arr['deal_meta_title'] == "") {
                                    $checkImg7 = '<img alt="" src="images/checkmark_d.png">';
                                } else {
                                    $checkImg7 = '<img alt="" src="images/checkmark.png">';
                                }
                            }
                            if ($_REQUEST['step'] == 8) {
                                $checkImg8 = '<img alt="" src="images/checkmark.png">';
                            } else {
                                if ($arr['deal_charity'] > 0 || $arr['deal_charity_discount'] > 0) {
                                    $checkImg8 = '<img alt="" src="images/checkmark.png">';
                                } else {
                                    $checkImg8 = '<img alt="" src="images/checkmark_d.png">';
                                }
                            }
                            if ($_REQUEST['step'] == 9) {
                                $checkImg9 = '<img alt="" src="images/checkmark.png">';
                            } else {
                                if ($arr['deal_instant_deal'] > 0 || $arr['deal_featured'] > 0) {
                                    $checkImg9 = '<img alt="" src="images/checkmark.png">';
                                } else
                                    $checkImg9 = '<img alt="" src="images/checkmark_d.png">';
                            }
                            if ($_REQUEST['step'] == 4) {
                                $chckImg4 = '<img alt="" src="images/checkmark.png">';
                            } else {
                                if (!empty($tickAttr) || !empty($getsubdealData) || !empty(fetchbookingDealDatesId($arr['deal_id']))) {
                                    $chckImg4 = '<img alt="" src="images/checkmark.png">';
                                } else
                                    $chckImg4 = '<img alt="" src="images/checkmark_d.png">';
                            }
                            if ($_REQUEST['step'] == 5) {
                                $chckImg5 = '<img alt="" src="images/checkmark.png">';
                            } else {
                                if ($arr['deal_shipping_charges_us'] > 0 || $arr['deal_shipping_charges_worldwide'] > 0) {
                                    $chckImg5 = '<img alt="" src="images/checkmark.png">';
                                } else
                                    $chckImg5 = '<img alt="" src="images/checkmark_d.png">';
                            }
                            if ($_GET['edit'] > 0) {
                                if ($_REQUEST['step'] == 1)
                                    $link1 = 'javascript:void(0);';
                                else
                                    $link1 = '?edit=' . $_GET['edit'] . '&step=1';
                                if ($_REQUEST['step'] == 2)
                                    $link2 = 'javascript:void(0);';
                                else
                                    $link2 = '?edit=' . $_GET['edit'] . '&step=2';
                                if ($_REQUEST['step'] == 3)
                                    $link3 = 'javascript:void(0);';
                                else
                                    $link3 = '?edit=' . $_GET['edit'] . '&step=3';
                                if ($_REQUEST['step'] == 4)
                                    $link4 = 'javascript:void(0);';
                                else
                                    $link4 = '?edit=' . $_GET['edit'] . '&step=4';
                                if ($_REQUEST['step'] == 5)
                                    $link5 = 'javascript:void(0);';
                                else
                                    $link5 = '?edit=' . $_GET['edit'] . '&step=5';
                                if ($_REQUEST['step'] == 6)
                                    $link6 = 'javascript:void(0);';
                                else
                                    $link6 = '?edit=' . $_GET['edit'] . '&step=6';
                                if ($_REQUEST['step'] == 7)
                                    $link7 = 'javascript:void(0);';
                                else
                                    $link7 = '?edit=' . $_GET['edit'] . '&step=7';
                                if ($_REQUEST['step'] == 8)
                                    $link8 = 'javascript:void(0);';
                                else
                                    $link8 = '?edit=' . $_GET['edit'] . '&step=8';
                                if ($_REQUEST['step'] == 9)
                                    $link9 = 'javascript:void(0);';
                                else
                                    $link9 = '?edit=' . $_GET['edit'] . '&step=9';
                            } else {
                                $link1 = 'javascript:void(0);';
                                $link2 = 'javascript:void(0);';
                                $link3 = 'javascript:void(0);';
                                $link4 = 'javascript:void(0);';
                                $link5 = 'javascript:void(0);';
                                $link6 = 'javascript:void(0);';
                                $link7 = 'javascript:void(0);';
                                $link8 = 'javascript:void(0);';
                                $link9 = 'javascript:void(0);';
                            }
                            ?>
                            <li ><a href="<?php echo $link1; ?>" <?php if ($_REQUEST['step'] == 1 || !isset($_REQUEST['step'])) echo 'class="current"'; ?>  ><?php echo t_lang('M_TXT_FRIST_STEP'); ?> <span><?php echo $checkImg1; ?></span> </a></li>
                            <li><a href="<?php echo $link2; ?>"  <?php if ($_REQUEST['step'] == 2) echo 'class="current"'; ?>> <?php echo t_lang('M_TXT_LOCATION'); ?> <span><?php echo $checkImg2; ?></span></a></li>
                            <li><a href="<?php echo $link3; ?>" <?php if ($_REQUEST['step'] == 3) echo 'class="current"'; ?>>  <?php echo t_lang('M_TXTVOUCHER_SETTINGS'); ?> <span><?php echo $checkImg3; ?></span></a></li>
                            <?php if ($arr['deal_type'] == 1) { ?>
                                <li><a href="<?php echo $link4; ?>" <?php if ($_REQUEST['step'] == 4) echo 'class="current"'; ?>><?php echo t_lang('M_TXT_ATTRIBUTES'); ?><span><?php echo $chckImg4; ?></span></a></li>
                                <?php if ($arr['deal_sub_type'] == 0) { ?>
                                    <li><a href="<?php echo $link5; ?>" <?php if ($_REQUEST['step'] == 5) echo 'class="current"'; ?>><?php echo t_lang('M_TXT_SHIPPING'); ?><span><?php echo $chckImg5; ?></span></a></li>
                                    <?php
                                }
                            }
                            ?>
                            <?php if ($arr['deal_type'] == 0 && $arr['deal_is_subdeal'] == 1) { ?>
                                <li><a href="<?php echo $link4; ?>" <?php if ($_REQUEST['step'] == 4) echo 'class="current"'; ?>><?php echo t_lang('M_TXT_SUB_DEAL_OPTIONS'); ?><span><?php echo $chckImg4; ?></span></a></li>
                            <?php } else if ($arr['deal_type'] == 0 && ($arr['deal_sub_type'] == 1 || $arr['deal_sub_type'] == 2)) { ?>
                                <li><a href="<?php echo $link4; ?>" <?php if ($_REQUEST['step'] == 4) echo 'class="current"'; ?>><?php echo t_lang('M_TXT_MANAGE_DATES'); ?><span><?php echo $chckImg4; ?></span></a></li>
                            <?php } ?>
                            <li><a href="<?php echo $link6; ?>" <?php if ($_REQUEST['step'] == 6) echo 'class="current"'; ?>>  <?php echo t_lang('M_FRM_CATEGORIES'); ?> <span><?php echo $checkImg6; ?></span></a></li>
                            <li><a href="<?php echo $link7; ?>" <?php if ($_REQUEST['step'] == 7) echo 'class="current"'; ?>>  <?php echo t_lang('M_TXT_SEO'); ?> <span><?php echo $checkImg7; ?></span></a></li>
                            <li><a href="<?php echo $link8; ?>" <?php if ($_REQUEST['step'] == 8) echo 'class="current"'; ?>>  <?php echo t_lang('M_TXT_CHARITY_AND_COMMISION'); ?> <span><?php echo $checkImg8; ?></span></a></li>
                            <li><a href="<?php echo $link9; ?>" <?php if ($_REQUEST['step'] == 9) echo 'class="current"'; ?>>  <?php echo t_lang('M_TXT_DISPLAY_SETTINGS'); ?> <span><?php echo $checkImg9; ?></span></a></li>
                        </ul>
                        <div class="contents">
                            <?php
                            if ($_REQUEST['step'] == 1) {
                                $fld = $frm->getField('deal_fine_print');
                                $fld->html_before_field = '<div class="frm-editor">';
                                $fld->html_after_field = '</div>';
                                $fld = $frm->getField('deal_desc');
                                $fld->html_before_field = '<div class="frm-editor">';
                                $fld->html_after_field = '</div>';
                                $fld = $frm->getField('deal_redeeming_instructions');
                                $fld->html_before_field = '<div class="frm-editor">';
                                $fld->html_after_field = '</div>';
                                ?>
                                <div class="tabscontent" id="content1"><?php echo $frm->getFormHtml(); ?></div>
                            <?php } ?>
                            <?php if ($_REQUEST['step'] == 2) { ?>
                                <div class="tabscontent" id="content1" ><?php echo $frm->getFormHtml(); ?></div>
                            <?php } ?>
                            <?php if ($_REQUEST['step'] == 3) { ?>
                                <div class="tabscontent" id="content1" ><?php
                                    if ($dealData['deal_type'] == 1) {
                                        $fld = $frm->getField('deal_min_coupons');
                                        $fld->fldType = "hidden";
                                        $fld->value = 1;
                                        $fld->setRequiredStarWith('caption');
                                    }
                                    $fld = $frm->getField('deal_max_coupons');
                                    $fld->extra = "id='deal_max_coupons'";
                                    echo $frm->getFormHtml();
                                    ?></div>
                            <?php } ?>
                            <?php if ($_REQUEST['step'] == 6) { ?>
                                <div class="tabscontent clearfix" id="content1" ><?php
                                    echo $frm->getFormTag();
                                    $fld = $frm->getField('step');
                                    echo $fld->getHTML();
                                    $fld = $frm->getField('deal_id');
                                    echo $fld->getHTML();
                                    echo $catArray;
                                    echo $frm->getFieldHTML('btn_submit_cancel');
                                    echo "</form>";
                                    ?></div>
                            <?php } ?>
                            <?php if ($_REQUEST['step'] == 7) { ?>
                                <div class="tabscontent" id="content1" ><?php echo $frm->getFormHtml(); ?></div>
                            <?php } ?>
                            <?php if ($_REQUEST['step'] == 8) { ?>
                                <div class="tabscontent" id="content1" ><?php
                                    //  $frm->setExtra= 'onSubmit="alert();"';
                                    echo $frm->getFormHtml();
                                    ?></div>
                            <?php } ?>
                            <?php
                            if ($_REQUEST['step'] == 9) {
                                $fld = $frm->getField('deal_featured');
                                if ($dealData['deal_type'] == 0) {
                                    $fld->fldType = "hidden";
                                    $fld->value = 1;
                                }
                                $fld->field_caption = t_lang('M_TXT_IS_FEATURED_PRODUCT');
                                $fld = $frm->getField('deal_side_deal');
                                $frm->removeField($fld);
                                ?>
                                <div class="tabscontent" id="content1" ><?php echo $frm->getFormHtml(); ?></div>
                            <?php } ?>
                            <?php if ($_REQUEST['step'] == 4) { ?>
                                <div class="tabscontent" id="content1" ><?php echo $frm->getFormHtml(); ?></div>
                            <?php } ?>
                            <?php if ($_REQUEST['step'] == 5) { ?>
                                <div class="tabscontent" id="content1" ><?php echo $frm->getFormHtml(); ?></div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        } else {
            die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        }
    }
    ?>
</div>
</td>
<script type="text/javascript">
    var deleteCityMsg = "<?php echo addslashes(t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD')); ?>";
    var global_id = [<?php echo '"' . implode('","', $tickAttr) . '"' ?>];
    var Capacity = <?php echo (isset($dealData['deal_max_coupons']) ? $dealData['deal_max_coupons'] : 0) ?>;
    var dayDiff =<?php echo (isset($day_diff) ? $day_diff : 1) ?>;
    var option_row = <?php echo $option_row; ?>;
    var spotion = '<?php echo addslashes(t_lang("M_TXT_SELECT_PARENT_OPTION")); ?>';
    var Quantity = '<?php echo addslashes(t_lang("M_TXT_QUANTITY")); ?>';
    var OValue = '<?php echo addslashes(t_lang("M_TXT_OPTION_VALUE")); ?>';
    var POValue = '<?php echo addslashes(t_lang("M_TXT_PARENT_OPTION_VALUE")); ?>';
    var price = '<?php echo addslashes(t_lang("M_TXT_PRICE")); ?>';
    var req = '<?php echo addslashes(t_lang("M_TXT_REQUIRED")); ?>';
    var AddOptionValue = '<?php echo addslashes(t_lang("M_TXT_ADD_OPTION_VALUE")); ?>';
    var remove = '<?php echo addslashes(t_lang("M_TXT_REMOVE")); ?>';
    var yes = '<?php echo addslashes(t_lang("M_TXT_YES")); ?>';
    var no = '<?php echo addslashes(t_lang("M_TXT_NO")); ?>';
    var companyChangeMessage = '<?php echo addslashes(t_lang("M_MSG_REALLY_WANT_TO_CHANGE_COMPANY")); ?>';
    var txtTaxClass = '<?php echo addslashes(t_lang('M_MSG_Please_select_tax_class')); ?>';
    var txtCharity = '<?php echo addslashes(t_lang('M_MSG_Please_select_Charity_organisation')); ?>';
    function AddOption() {
        var option_value = $('#optionDropdown option:selected').val();
        var a = global_id.indexOf(option_value);
        if (a == -1) {
            global_id.push(option_value);
        } else {
            requestPopup(1, '<?php echo addslashes(t_lang('M_MSG_This_option_has_been_already_added')); ?>', 0);
            return false;
        }
        var option_name = $('#optionDropdown option:selected').text();
        $parent_option = $('#optionDropdown').clone();
        $parent_option.find('[value="' + option_value + '"]').remove();
        var options;
        options += '<tr class="option_rows">';
        options += ' <input type="hidden" id="child_' + option_row + '" name="deal_option[' + option_row + '][option_id]" value="' + option_value + '"  /></td>';
        options += '<th><span >' + option_name + ' :  ' + req + '</span>';
        options += '<input type="hidden" name="deal_option[' + option_row + '][type]" value="select" />';
        options += '</br> <select name="deal_option[' + option_row + '][required]">';
        options += '<option selected="selected" value="1">' + yes + '</option>';
        options += '<option value="0">' + no + '</option>';
        options += '</select>';
        options += '</th>';
        options += '<th >';
        options += spotion + ' <br/> <select name="deal_option[' + option_row + '][parent_option_id]" id="parent_' + option_row + '" onChange="changeparentoptionvalue(' + option_row + ');">';
        options += '<option  value="0" >None</option>';
        options += $parent_option.html();
        options += '</select>';
        options += '</th>';
        options += '<th >';
        options += '<ul class="actions"><li><a title="' + remove + '"onclick="removeElement(' + option_value + ');$(\'#option-value' + option_row + '\').remove();$(this).parent().parent().parent().parent().remove();"><i class="ion-minus icon"></i></a></li></ul>';
        options += '</th>';
        options += '</tr>';
        options += '<tr class="no_padd"><td colspan="3"><table width="100%" cellspacing="0" cellpadding="0" border="0" class="tbl-optionlist " id="option-value' + option_row + '">';
        options += '<tr><th width="30%">' + OValue + ':</th><th width="10%">' + Quantity + ':</th><th width="30%">' + POValue + ':</th><th width="25%">' + price + ':</th><th></th></tr>';
        options += '<tfoot><tr><td colspan="4"></td><td><ul class="actions"><li><a href="javascript:void(0);" onclick="addOptionValue(' + option_row + ')" title="' + AddOptionValue + '"><i class="ion-plus-round icon"></i></a></li></ul></td></tr></tfoot></table></tr>';
        $('#option-value tr:last').before(options);
        option_row++;
    }
    function removeElement(option_value) {
        option_value = "" + option_value;
        var index = global_id.indexOf(option_value);
        if (index > -1) {
            global_id.splice(index, 1);
        }
    }
    var option_value_row = <?php echo $option_value_row; ?>
    function changeparentoptionvalue(id) {
        var parent_option = $('#parent_' + id).val();
        callAjax('deals-ajax.php', 'mode=GetOptionValues&id=' + parent_option, function (t) {
            var ans = parseJsonData(t);
            option_values_name = ans.msg;
            $('.parent_option_value' + id).html(option_values_name);
            $('.parent_option_value' + id).trigger('change');
        });
    }
    function changechildoptionvalue(id, option_value_row) {
        var parent_option = $('#child_' + id).val();
        callAjax('deals-ajax.php', 'mode=GetOptionValues&id=' + parent_option, function (t) {
            var ans = parseJsonData(t);
            option_values_name = ans.msg;
            $('.child_option_value' + id + '_' + option_value_row).html(option_values_name);
        });
    }
    function addOptionValue(option_row) {
        var option_values_name;
        var parent_option = $('#parent_' + option_row).val();
        callAjax('deals-ajax.php', 'mode=GetOptionValues&id=' + parent_option, function (t) {
            var ans = parseJsonData(t);
            option_values_name = ans.msg;
            var html = create_option(option_values_name, option_row);
            changechildoptionvalue(option_row, option_value_row)
            $('#option-value' + option_row + ' tfoot').before(html);
            option_value_row++;
        });
    }
    function create_option(option_values_name, option_row) {
        html = '<tbody id="option-value-row' + option_value_row + '">';
        html += '  <tr>';
        html += '    <td><select  class="child_option_value' + option_row + '_' + option_value_row + ' " name="deal_option[' + option_row + '][deal_option_value][' + option_value_row + '][option_value_id]">';
        html += $('#option-values' + option_row).html();
        html += '    </select></td>';
        html += '    <td><input min="1" type="number" class="" name="deal_option[' + option_row + '][deal_option_value][' + option_value_row + '][quantity]" value="" size="5" onchange="checkQuantity(this)"/></td>';
        html += '    <td><select  class="parent_option_value' + option_row + '"  onchange="$(this).parent().prev().find(\'input\').trigger(\'change\');" name="deal_option[' + option_row + '][deal_option_value][' + option_value_row + '][parent_option_value_id]">';
        html += option_values_name;
        html += '    </select></td>';
        html += '    <td><select class="fieldSmall" name="deal_option[' + option_row + '][deal_option_value][' + option_value_row + '][price_prefix]">';
        html += '      <option value="+">+</option>';
        html += '      <option value="-">-</option>';
        html += '    </select>&nbsp;&nbsp;';
        html += '    <input type="text" class="fieldSmalltext" name="deal_option[' + option_row + '][deal_option_value][' + option_value_row + '][price]" value="" /></td>';
        html += '    <td><ul class="actions"><li><a onclick="$(\'#option-value-row' + option_value_row + '\').remove();" title="' + remove + '"><i class="ion-minus icon"></i></a></li></ul></td>';
        html += '  </tr>';
        html += '</tbody>';
        return html;
    }
    function checkQuantity(obj) {
        var child_id = $(obj).parent().prev().find('select').val();
        var parent_id = $(obj).parent().next().find('select').val();
        var childclass = "child" + child_id;
        var numItems = $('.' + childclass).length;
        var main_qty = 0;
        if (!parent_id || parent_id === null) {
            $(obj).addClass("child" + child_id + "");
            $(obj).addClass("parent0");
            $(".parent0").each(function () {
                main_qty += parseInt($(this).val());
            });
            if (main_qty > Capacity) {
                requestPopup(1, '<?php echo addslashes(t_lang('M_MSG_Value_should_not_be_greater_than_Max_Quantity')); ?>', 0);
                main_qty = main_qty - parseInt($(obj).val());
                $(obj).val('');
                return false;
            }
        } else {
            var parentQty = $('.child' + parent_id).val();
            var siblingqty = 0;
            $(".parent" + parent_id).each(function () {
                siblingqty += parseInt($(this).val());
            });
            if (!parentQty) {
                requestPopup(1, '<?php echo addslashes(t_lang('M_MSG_please_add_parent_qty_first')); ?>', 0);
                $(obj).val('');
                return false;
            } else {
                var current_val = $(obj).val();
                var existingClass = $(obj).attr('class');
                if (existingClass) {
                    current_val = 0;
                }
                if ((parseInt(siblingqty) + parseInt(current_val)) > parseInt(parentQty)) {
                    requestPopup(1, '<?php echo addslashes(t_lang('M_MSG_Child_Value_should_not_be_greater_than_parent_value')); ?>', 0);
                    $(obj).val('');
                    return false;
                }
                $(obj).addClass("parent" + parent_id + "");
                $(obj).addClass("child" + child_id + "");
            }
        }
    }
    function hideshowfields() {
        var deal_type = $('#deal_type').val();
        if (deal_type == 0 && $('#is_subdeal').is(':checked')) {
            $("input[name='deal_original_price']").val(0);
            $("input[name='deal_discount']").val(0);
            $("input[name='deal_original_price']").parent().parent().hide();
            $("input[name='deal_discount']").parent().parent().hide();
        } else {
            $("input[name='deal_original_price']").parent().parent().show();
            $("input[name='deal_discount']").parent().parent().show();
        }
    }
    $('#is_subdeal').click(function () {
        hideshowfields();
    });
    function removeRecord(deal_option_value_id) {
        callAjax('deals-ajax.php', 'mode=deleteDealOptionValue&deal_option_value_id=' + deal_option_value_id, function (t) {
            var ans = parseJsonData(t);
            if (ans.msg)
                $(this).closest('tr').remove();
        });
    }
    function removeParentRecord(deal_option_id) {
        callAjax('deals-ajax.php', 'mode=deleteDealOption&deal_option_id=' + deal_option_id, function (t) {
            var ans = parseJsonData(t);
        });
    }
    $("document").ready(function () {
        $(".remove_row").live('click', function () {
            $(this).closest('tr').remove();
        });
<?php if (isset($_REQUEST['edit'])) { ?>
    <?php if (($dealData['deal_type'] == 1) && ($dealData['deal_sub_type'] == 1)) { ?>
                $("input[name='dpe_product_file']").parent().parent().show();
                $("input[name='dpe_product_external_url']").parent().parent().show();
                $("input[name='deal_is_subdeal']").parent().parent().hide();
    <?php } else if (($dealData['deal_type'] == 1) && ($dealData['deal_sub_type'] == 0)) { ?>
                $("select[name='deal_sub_type']").parent().parent().show();
                $("input[name='dpe_product_file']").parent().parent().hide();
                $("input[name='dpe_product_external_url']").parent().parent().hide();
                $("input[name='deal_is_subdeal']").parent().parent().hide();
    <?php } else if (($dealData['deal_type'] == 0)) { ?>
                $("select[name='deal_sub_type']").parent().parent().show();
                //$("#is_subdeal").attr('disabled',true);
                $("input[name='dpe_product_file']").parent().parent().hide();
                $("input[name='dpe_product_external_url']").parent().parent().hide();
    <?php } else { ?>
                $("input[name='deal_is_subdeal']").parent().parent().show();
                $("input[name='dpe_product_file']").parent().parent().hide();
                $("input[name='dpe_product_external_url']").parent().parent().hide();
                $("select[name='deal_sub_type']").parent().parent().hide();
        <?php
    }
}
?>
        hideshowfields();
<?php if (!isset($_REQUEST['edit'])) { ?>
            $("input[name='dpe_product_file']").parent().parent().hide();
            $("input[name='dpe_product_external_url']").parent().parent().hide();
<?php } ?>
        $('#deal_type').change(function () {
            var deal_type = $('#deal_type').val();
            if (deal_type == 1) {
                options = '<option value="0">Physical Product</option><option value="1">Digital Product</option>';
                $('#deal_sub_type').html(options);
                $("input[name='deal_is_subdeal']").parent().parent().hide();
                $('#deal_sub_type').trigger('change');
            } else {
                options = '<option value="0">Normal Deal</option><option selected="selected" value="1">Booking Request</option><option value="2">Online Booking</option>';
                $('#deal_sub_type').html(options);
                $("input[name='dpe_product_file']").parent().parent().hide();
                $("input[name='dpe_product_external_url']").parent().parent().hide();
                $("input[name='deal_is_subdeal']").parent().parent().show();
            }
        });
        $('#deal_sub_type').change(function () {
            var deal_sub_type = $('#deal_sub_type').val();
            var deal_type = $('#deal_type').val();
            updateDealSubType(deal_type, deal_sub_type);
        });
        $('#deal_discount_is_percent').trigger('change');
        function updateDealSubType(deal_type, deal_sub_type) {
            if (deal_type == 1) {
                if (deal_sub_type == 1) {
                    $("input[name='dpe_product_file']").parent().parent().show();
                    $("input[name='dpe_product_external_url']").parent().parent().show();
                } else {
                    $("input[name='dpe_product_file']").parent().parent().hide();
                    $("input[name='dpe_product_external_url']").parent().parent().hide();
                }
            }
        }
    });
</script>
<link href="<?php echo CONF_WEBROOT_URL; ?>css/jquery.qtip.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="<?php echo CONF_WEBROOT_URL; ?>js/jquery.qtip-1.0.0-rc3.js"></script>
<script type="text/javascript" src="<?php echo CONF_WEBROOT_URL; ?>js/jquery.qtip.js"></script>
<?php if (false === $succeed) { ?>
    <script type="text/javascript">
        updateDealTypeOptions(<?php echo $post['deal_type']; ?>,<?php echo $post['deal_sub_type']; ?>);
        function updateDealTypeOptions(deal_type, deal_sub_type) {
            if (deal_type == 1) {
                options = '<option value="0">Physical Product</option><option value="1">Digital Product</option>';
                $('#deal_sub_type').html(options);
                $("input[name='deal_is_subdeal']").parent().parent().hide();
            }
        }
    </script>
<?php } ?>
<?php if (is_numeric($_REQUEST['commssion']) || $_REQUEST['commssion'] == 1) { ?>
    <script type="text/javascript">
        $(document).ready(function () {
            $('input[name^="deal_commission_percent"]').focus();
        });
    </script>
<?php } ?>
<?php
require_once './footer.php';
