<?php
require_once './application-top.php';
require_once '../includes/navigation-functions.php';
$arr_common_js[] = 'js/calendar.js';
$arr_common_js[] = 'js/calendar-en.js';
$arr_common_js[] = 'js/calendar-setup.js';
$arr_common_css[] = 'css/cal-css/calendar-win2k-cold-1.css';
$arr_common_css[] = 'css/prettyPhoto.css';
$arr_common_js[] = 'js/jquery.prettyPhoto.js';
checkAdminPermission(5);
require_once './update-deal-status.php';
$company_id = (int) $_REQUEST['cid'];
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$pagesize = 15;
$post = getPostedData();
//Search Form
$rsc = $db->query("SELECT company_id, IF(CHAR_LENGTH(company_name" . $_SESSION['lang_fld_prefix'] . "),company_name" . $_SESSION['lang_fld_prefix'] . ",company_name) as company_name FROM `tbl_companies` WHERE company_active=1 and company_deleted = 0 order by company_name" . $_SESSION['lang_fld_prefix'] . " asc");
$companyArray = $db->fetch_all_assoc($rsc);
$cityList = $db->query("select city_id, IF(CHAR_LENGTH(city_name" . $_SESSION['lang_fld_prefix'] . "),city_name" . $_SESSION['lang_fld_prefix'] . ",city_name) as city_name from tbl_cities where city_active=1 and city_request=0 and city_deleted=0 order by city_name" . $_SESSION['lang_fld_prefix'] . " asc");
$cityArray = $db->fetch_all_assoc($cityList);
$catList = $db->query("select cat_id, IF(CHAR_LENGTH(cat_name" . $_SESSION['lang_fld_prefix'] . "),cat_name" . $_SESSION['lang_fld_prefix'] . ",cat_name) as cat_name from tbl_deal_categories where cat_active=1  order by cat_name" . $_SESSION['lang_fld_prefix']);
$catArray = $db->fetch_all_assoc($catList);
$typeArray = array('0-0' => t_lang('M_TXT_DEAL'), '0-1' => t_lang('M_TXT_BOOKING_REQUEST'), '0-2' => t_lang('M_TXT_ONLINE_BOOKING'), '1-0' => t_lang('M_TXT_PRODUCT'), '1-1' => t_lang('M_TXT_DIGITAL_PRODUCT'));
$Src_frm = new Form('Src_frm', 'Src_frm');
$Src_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$Src_frm->setFieldsPerRow(4);
$Src_frm->captionInSameCell(true);
$Src_frm->addTextBox(t_lang('M_FRM_KEYWORD'), 'keyword', '', '', '');
$Src_frm->addSelectBox(t_lang('M_FRM_COMPNAY_NAME'), 'deal_company', $companyArray, $company_id, '', t_lang('M_TXT_SELECT'), 'deal_company');
$Src_frm->addSelectBox(t_lang('M_TXT_CITY_NAME'), 'deal_city', $cityArray, $value, '', t_lang('M_TXT_SELECT'), 'deal_city');
$Src_frm->addSelectBox(t_lang('M_TXT_CATEGORY_NAME'), 'deal_cat', $catArray, $value, '', t_lang('M_TXT_SELECT'), 'deal_cat');
$Src_frm->addDateField(t_lang('M_FRM_DEAL_STARTS_ON'), 'deal_start_time', '', 'deal_start_time', 'readonly');
$Src_frm->addDateField(t_lang('M_FRM_DEAL_ENDS_ON'), 'deal_end_time', '', 'deal_end_time', 'readonly');
//$Src_frm->addTextBox(t_lang('M_TXT_TIPPING_POINT'), 'deal_min_coupons', '', '', '');
$tipping_point = array(0 => t_lang('M_TXT_ALL'), 1 => t_lang('M_TXT_TIPPED'), 2 => t_lang('M_TXT_NOT_TIPPED'));
$Src_frm->addSelectBox(t_lang('M_TXT_TIPPING_POINT'), 'deal_tipped_at', $tipping_point, '', '');
$Src_frm->addSelectBox(t_lang('M_TXT_TYPE'), 'deal_type', $typeArray, '', '');
$Src_frm->addHiddenField('', 'mode', 'search');
$Src_frm->addHiddenField('', 'status', $_REQUEST['status']);
$fld1 = $Src_frm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="inputbuttons" onclick=location.href="deals.php"');
$fld = $Src_frm->addSubmitButton('', 'btn_search', t_lang('M_TXT_SEARCH'), '', ' class="inputbuttons" ');
$fld->attachField($fld1);
$mainTableName = 'tbl_deals';
$primaryKey = 'deal_id';
$colPrefix = 'deal_';
$frm = getMBSFormByIdentifier('frmDeal');
#$frm->setAction('?page=' . $page.'&status='. $_REQUEST['status']);
$fld = $frm->getField('btn_submit');
$fld->html_after_field = '<span style="color: #f00;">Note: All the times are according to server time. Current server time is ' . displayDate(date('Y-m-d H:i:s'), true, false) . '</span>';
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
$frm->getField('deal_start_time')->requirements()->setDateTime();
$frm->getField('deal_end_time')->requirements()->setDateTime();
$fld1 = $frm->getField('btn_submit');
$fld1->merge_cells = 2;
$fld = $frm->getField('deal_start_time');
$fld->value = displayDate(date('Y-m-d H:i:s'), true, false);
$fld = $frm->getField('deal_end_time');
$fld->value = displayDate(Date("y-m-d H:i:s", mktime(0, 0, 0, date("m"), date("d") + 10, date("Y"))), false, false, CONF_TIMEZONE);
$fld = $frm->addButton('', 'btn_submit_cancel', 'Cancel', '', ' class="inputbuttons" onclick=location.href="deals.php"')->attachField($fld1);
if (is_numeric($_GET['edit'])) {
    if ((checkAdminAddEditDeletePermission(5, '', 'edit'))) {
        $record = new TableRecord($mainTableName);
        if (!$record->loadFromDb($primaryKey . '=' . $_GET['edit'], true)) {
            $msg->addError($record->getError());
        } else {
            $arr = $record->getFlds();
            $arr['btn_submit'] = t_lang('M_TXT_UPDATE');
            $rs_cats = $db->query("select dc_cat_id, dc_cat_id as cat_id from tbl_deal_to_category where  dc_deal_id=" . $_GET['edit']);
            $arr['deal_categories'] = $db->fetch_all_assoc($rs_cats);
            fillForm($frm, $arr);
            /*  $frm->fill($arr); */
            $msg->addMsg(t_lang('M_TXT_Edit_THE_VALUES_AND_UPDATE'));
        }
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['btn_search']) && isset($_POST['btn_submit'])) {
    $post = getPostedData();
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
        if (true === $succeed) {

            $record = new TableRecord($mainTableName);
            $record->assignValues($post);
            if (!($post[$primaryKey] > 0))
                $record->setFldValue('deal_addedon', date('Y-m-d H:i:s'), false);
            $total_dac_address_capacity = count($post['dac_address_capacity']);
            $totalCapacity = 0;
            for ($i = 0; $i < $total_dac_address_capacity; $i++) {
                if ($post['dac_address_capacity'][$i] != "") {
                    $capacityCount++;
                    if (($post['dac_address_capacity'][$i]) <= ($post['deal_max_buy'] + $post['deal_max_gift']) && $post['dac_address_capacity'][$i] != 0 && ($post['dac_address_capacity'][$i]) >= ($post['deal_min_buy'])) {
                        $checkVar = 'true';
                    }
                    $totalCapacity += $post['dac_address_capacity'][$i];
                }
            }
            if ($post['dac_address_id'] != "" && $totalCapacity > 0) {
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
                        $db->query("delete from tbl_deal_to_category where dc_deal_id=" . $deal_id);
                        if (is_array($post['deal_categories'])) {
                            foreach ($post['deal_categories'] as $catid) {
                                $db->insert_from_array('tbl_deal_to_category', array('dc_deal_id' => $deal_id, 'dc_cat_id' => $catid));
                            }
                        }
                        ############## CODE FOR INSERT COMPANY ID AND ADDRESS ID FOR MULTIPLE LOCATION ###############
                        $db->query("delete from tbl_deal_address_capacity where dac_deal_id=" . $deal_id);
                        $count = 0;
                        if (is_array($_POST['dac_address_id'])) {
                            foreach ($_POST['dac_address_id'] as $addressid) {
                                $db->insert_from_array('tbl_deal_address_capacity', array('dac_deal_id' => $deal_id, 'dac_address_id' => $addressid, 'dac_address_capacity' => $_POST['dac_address_capacity'][$count]));
                                $count++;
                            }
                        }
                        ############## CODE FOR INSERT COMPANY ID AND ADDRESS ID FOR MULTIPLE LOCATION ###############
                        if (is_uploaded_file($_FILES['deal_image']['tmp_name'])) {
                            $flname = time() . '_' . $_FILES['deal_image']['name'];
                            if (!move_uploaded_file($_FILES['deal_image']['tmp_name'], DEAL_IMAGES_PATH . $flname)) {
                                $msg->addError(t_lang('M_TXT_FILE_COULD_NOT_SAVE'));
                            } else {
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
                        redirectUser('deals.php?page=' . $page . '&status=' . $get['status']);
                    } else {
                        $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
                        $frm->fill($post);
                    }
                } else {
                    $msg->addError(unescape_attr(t_lang('M_MSG_DEAL_CAPACITY_EXCEED')));
                    $frm->fill($post);
                    #redirectUser('deals.php?edit='.$post['deal_id'].'&page='.$page.'&status='.$get['status']);
                }
            } else {
                $msg->addError(t_lang('M_MSG_PLEASE_SELECT_AT_LEAST_ONE_ADDRESS'));
                $frm->fill($post);
                #redirectUser('deals.php?edit='.$post['deal_id'].'&page='.$page.'&status='.$get['status']);
            }
        }
    }
}
$srch = new SearchBase('tbl_deals', 'd');
$srch->addCondition('deal_deleted', '=', 0);
//if ($_REQUEST['status'] != 'incomplete') {
$srch->joinTable('tbl_cities', 'LEFT JOIN', 'd.deal_city=c.city_id', 'c');
$srch->joinTable('tbl_companies', 'LEFT JOIN', 'd.deal_company=company.company_id ', 'company');
$srch->addMultipleFields(array('d.*', 'c.*', 'company.*'));
//}
/* $srch->addMultipleFields(array('d.deal_id', 'deal_side_deal', 'deal_name','deal_city', 'deal_status', 
  'deal_start_time', 'deal_end_time', 'c.city_name', 'company.company_name','deal_main_deal',
  'd.deal_tipped_at'
  )); */
if ($_GET['deal_company'] != '') {
    $cnd = $srch->addDirectCondition('0');
    $cnd->attachCondition('d.deal_company', '=', $_GET['deal_company'], 'OR');
}
if ($post['mode'] == 'search') {
    if ($post['keyword'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('d.deal_name' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
    }
    if ($post['deal_company'] != '') {
        $company_id = $post['deal_company'];
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('d.deal_company', '=', $post['deal_company'], 'OR');
    }
    if ($post['deal_city'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('d.deal_city', '=', $post['deal_city'], 'OR');
    }
    if ($post['deal_cat'] != '') {
        $catCode = fetchCatCode($post['deal_cat']);
        $srch->joinTable('tbl_deal_to_category', 'INNER JOIN', 'd.deal_id=doc.dc_deal_id ', 'doc');
        $srch->joinTable('tbl_deal_categories', 'INNER JOIN', 'doc.dc_cat_id=dc.cat_id ', 'dc');
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('dc.cat_code', 'LIKE', $catCode . "%", 'OR');
        $srch->addGroupBy('d.deal_id');
        // echo $srch->getQuery();
    }
    if ($post['deal_start_time'] != '') {
        $start_time = date('Y-m-d', strtotime($post['deal_start_time']));
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition("mysql_func_date(d.`deal_start_time`)", '>=', $start_time, 'OR', true);
    }
    if ($post['deal_end_time'] != '') {
        $end_time = date('Y-m-d', strtotime($post['deal_end_time']));
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition("mysql_func_date(d.`deal_end_time`)", '<=', $end_time, 'OR', true);
    }
    if ($post['deal_tipped_at'] != '') {


        if ($post['deal_tipped_at'] == 1) {
            $cnd = $srch->addDirectCondition('0');
            $cnd->attachCondition("d.`deal_tipped_at`", '!=', "0000-00-00 00:00:00", 'OR', true);
        }
        if ($post['deal_tipped_at'] == 2) {
            $cnd = $srch->addDirectCondition('0');
            $cnd->attachCondition("d.`deal_tipped_at`", '=', "0000-00-00 00:00:00", 'OR', true);
        }
    }
    if ($post['deal_type'] != '') {
        $type = explode('-', $post['deal_type']);
        $deal_type = $type[0];
        $deal_sub_type = $type[1];
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition("d.`deal_type`", '=', $deal_type, 'OR', true);
        $cnd->attachCondition("d.`deal_sub_type`", '=', $deal_sub_type, 'AND', true);
    }
    //echo $srch->getQuery();
    $Src_frm->fill($post);
}
$status = $_REQUEST['status'];
/* * **	Reposting a new deal	*** */
$get = getQueryStringData();
if (($status == 'expired') || ($status == 'cancelled')) {
    date_default_timezone_set(CONF_TIMEZONE);
    $current_date_format = CONF_DATE_FORMAT_PHP . " H:i:s";
    if (isset($get['old_deal_id']) && $get['old_deal_id'] != "") {
        //get old deal id
        $old_deal_id = $get['old_deal_id'];
        /*         * ********		Start Adding deal data into tbl_deals		*************** */
        $srchDeal = new SearchBase('tbl_deals');
        $srchDeal->addCondition('deal_id', '=', $old_deal_id);
        $rs1 = $srchDeal->getResultSet();
        $row1 = $db->fetch($rs1);
        if (isset($row1['deal_id']) && isset($row1['deal_is_duplicate']) && $row1['deal_is_duplicate'] == 0) {
            //remove old_deal_id
            unset($row1['deal_id']);
            $row1['deal_id'] = '';
            $row1['deal_start_time'] = displayDate(date($current_date_format), true, false);
            $row1['deal_end_time'] = displayDate(date($current_date_format, strtotime('+1 week')), true, false);
            if (CONF_VOUCHER_START_DATE == 0) {
                $row1['voucher_valid_from'] = $row1['deal_start_time'];
            }
            if (CONF_VOUCHER_START_DATE == 1) {
                $row1['voucher_valid_from'] = $row1['deal_end_time'];
            }
            $old_deal_end_time = $row1['deal_end_time'];
            $days = CONF_VOUCHER_END_DATE;
            $row1['voucher_valid_till'] = date($current_date_format, strtotime("+$days day", strtotime($old_deal_end_time)));
            $row1['deal_addedon'] = displayDate(date($current_date_format), true, false);
            $row1['deal_status'] = 1;
            $row1['deal_main_deal'] = 0;
            $row1['deal_paid'] = 0;
            $row1['deal_tipped_at'] = '';
            $row1['deal_paid_date'] = '';
            $data = [];
            foreach ($row1 as $key => $val) {
                $data[$key] = $val;
            }
            /** 		inserting prepared data into tbl_deals		* */
            $record = new TableRecord('tbl_deals');
            $record->assignValues($data);
            $record->addNew();
            $new_deal_id = $record->getId();
            /*             * ********		End Adding deal data into tbl_deals		*************** */
            /*             * ********		Start Adding deal data into tbl_deal_to_category		*************** */
            /** 	getting data from tbl_deal_to_category	* */
            $srchDeal = new SearchBase('tbl_deal_to_category');
            $srchDeal->addCondition('dc_deal_id', '=', $old_deal_id);
            $rs1 = $srchDeal->getResultSet();
            while ($rowAddress = $db->fetch($rs1)) {
                $data = [];
                foreach ($rowAddress as $key => $val) {
                    if ($key == 'dc_deal_id' && $val == $old_deal_id)
                        $val = $new_deal_id;
                    $data[$key] = $val;
                }
                /** 		inserting prepared data into tbl_deal_to_category		* */
                $record = new TableRecord('tbl_deal_to_category');
                $record->assignValues($data);
                $record->addNew();
            }
            /*             * ********		End Adding deal data into tbl_deal_to_category		*************** */
            /*             * ********		Adding deal data into tbl_deal_address_capacity		*************** */
            /** 		getting data from tbl_deal_address_capacity		* */
            $srchDeal = new SearchBase('tbl_deal_address_capacity');
            $srchDeal->addCondition('dac_deal_id', '=', $old_deal_id);
            $rs1 = $srchDeal->getResultSet();
            while ($rowAddress = $db->fetch($rs1)) {
                $data = [];
                foreach ($rowAddress as $key => $val) {
                    if ($key == 'dac_deal_id' && $val == $old_deal_id)
                        $val = $new_deal_id;
                    if ($key == 'dac_id') {
                        //remove dac_id				
                        unset($rowAddress['dac_id']);
                    } else {
                        $data[$key] = $val;
                    }
                }
                /** 		inserting prepared data into tbl_deal_address_capacity		* */
                $record = new TableRecord('tbl_deal_address_capacity');
                $record->assignValues($data);
                $record->addNew();
            }
            /*             * ********		Adding deal data into tbl_deal_address_capacity		*************** */

            /** 		getting data from tbl_sub_deals		* */
            $srchDeal = new SearchBase('tbl_sub_deals');
            $srchDeal->addCondition('sdeal_deal_id', '=', $old_deal_id);
            $rs1 = $srchDeal->getResultSet();
            while ($rowAddress = $db->fetch($rs1)) {
                $data = [];
                foreach ($rowAddress as $key => $val) {
                    if ($key == 'sdeal_deal_id' && $val == $old_deal_id)
                        $val = $new_deal_id;
                    if ($key == 'sdeal_id') {
                        //remove dac_id				
                        unset($rowAddress['sdeal_id']);
                    } else {
                        $data[$key] = $val;
                    }
                }
                /** 		inserting prepared data into tbl_sub_deals		* */
                $record = new TableRecord('tbl_sub_deals');
                $record->assignValues($data);
                $record->addNew();
            }
            /*             * *******		Adding deal data into tbl_sub_deals		*************** */

            /** 		getting data from tbl_deal_booking_dates		* */
            $srchDeal = new SearchBase('tbl_deal_booking_dates');
            $srchDeal->addCondition('dbdate_deal_id', '=', $old_deal_id);
            $rs1 = $srchDeal->getResultSet();
            while ($rowAddress = $db->fetch($rs1)) {
                $data = [];
                foreach ($rowAddress as $key => $val) {
                    if ($key == 'dbdate_deal_id' && $val == $old_deal_id)
                        $val = $new_deal_id;
                    if ($key == 'dbdate_id') {
                        //remove dac_id				
                        unset($rowAddress['dbdate_id']);
                    } else if ($key == 'dbdate_date') {
                        $val = date($current_date_format, strtotime("+1 week", strtotime($val)));
                    } else {
                        $data[$key] = $val;
                    }
                }
                /** 		inserting prepared data into tbl_deal_booking_dates		* */
                $record = new TableRecord('tbl_deal_booking_dates');
                $record->assignValues($data);
                $record->addNew();
            }
            /*             * *******		Adding deal data into tbl_sub_deals		*************** */
            $record = new TableRecord('tbl_deals');
            $record->setFldValue('deal_is_duplicate', 1);
            $record->update('deal_id' . '=' . $old_deal_id);
            $msg->addMsg(t_lang('M_TXT_REPOST_DEAL_UPDATE_SUCCESSFUL'));
            if ($status == 'cancelled') {

                redirectUser(CONF_WEBROOT_URL . 'manager/add-deals.php?edit=' . $new_deal_id . '&page=1');
            } else {
                redirectUser(CONF_WEBROOT_URL . 'manager/deals.php?status=active');
            }
        }
        $msg->addMsg(t_lang('M_TXT_REPOST_DEAL_ALREADY_REPOSTED_OR_DOESNT_EXIST'));
        redirectUser(CONF_WEBROOT_URL . 'manager/deals.php');
    }
}
/* * **	Reposting a new deal	*** */
if ($status == 'upcoming') {
    $stat = t_lang(M_TXT_UPCOMING);
    $srch->addCondition('deal_status', '=', 0);
    $srch->addCondition('deal_complete', '=', 1);
} else if ($status == 'active') {
    $stat = t_lang(M_TXT_ACTIVE);
    $srch->addCondition('deal_status', '=', 1);
    $srch->addCondition('deal_complete', '=', 1);
} else if ($status == 'expired') {
    $stat = t_lang(M_TXT_EXPIRED);
    $srch->addCondition('deal_status', '=', 2);
} else if ($status == 'un-approval') {
    $stat = t_lang(M_TXT_UNAPPROVED);
    $srch->addCondition('deal_status', '=', 5);
    $srch->addCondition('d.deal_complete', '=', 1);
} else if ($status == 'cancelled') {
    $stat = t_lang(M_TXT_CANCELLED);
    $srch->addCondition('deal_status', '=', 3);
} else if ($status == 'rejected') {
    $stat = t_lang(M_TXT_REJECTED);
    $srch->addCondition('deal_status', '=', 6);
} else if ($status == 'purchased') {
    $stat = t_lang(M_TXT_PURCHASED);
    $srch->joinTable('tbl_order_deals', 'INNER JOIN', 'd.deal_id=od.od_deal_id', 'od');
    $srch->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id=o.order_id and o.order_payment_status=1', 'o');
    $srch->addGroupBy('d.deal_id');
} else if ($status == 'incomplete') {
    $stat = t_lang(M_TXT_INCOMPLETE);
    $srch->addCondition('deal_complete', '=', 0);
} else if ($status == 'unsettled') {
    $stat = t_lang(M_TXT_UNSETTLED);
    $srch->addCondition('deal_paid', '=', 0);
    $srch->addCondition('deal_status', '=', 2);
    if ($company_id > 0)
        $srch->addCondition('deal_company', '=', $company_id);
} else {
    $srch->addCondition('deal_status', '=', 1);
}
//if ($_REQUEST['status'] != 'incomplete') {
$srch->addOrder('c.city_name', 'asc');
//}
$srch->addOrder('d.deal_start_time', 'desc');
$srch->addOrder('d.deal_status');
$srch->addOrder('d.deal_name');
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
/* echo $srch->getQuery(); */
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
//if ($pages > 1) {
$pagestring .= createHiddenFormFromPost('frmPaging', '?', array('page', 'status'), array('page' => '', 'status' => $_REQUEST['status'], 'cid' => $_REQUEST['cid']));
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
		' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
//}
$arr_listing_fields = array(
    'deal_img_name' => t_lang('M_FRM_DEAL_IMAGE'),
    'deal_name' => t_lang('M_TXT_DEAL_TITLE'),
    'action' => t_lang('M_TXT_ACTION')
);
require_once './header.php';
?>
<script type="text/javascript" charset="utf-8">
    var cancelMsg = '<?php echo addslashes(t_lang('M_MSG_MESSAGE_WHEN_ADMIN_CANCEL_DEAL')); ?>';
    var txtoops = "<?php echo addslashes(t_lang('M_TXT_INTERNAL_ERROR')); ?>";
    var txtreload = "<?php echo addslashes(t_lang('M_TXT_PLEASE_RELOAD_PAGE_AND_TRY_AGAIN')); ?>";
    var txtload = "<?php echo addslashes(t_lang('M_TXT_LOADING')); ?>";
    var txtsettledeal = "<?php echo addslashes(t_lang('M_TXT_REALLY_WANT_TO_SETTLED_THE_DEAL')); ?>";
    var txtCompCommission = "<?php echo addslashes(t_lang('M_TXT_You_have_not_entered_commission_percent,_Would_you_like_to_continue_with_commission?')); ?>";
</script>
<!--<link href="<?php echo CONF_WEBROOT_URL; ?>css/prettyPhoto.css" rel="stylesheet" type="text/css" />
<script src="<?php echo CONF_WEBROOT_URL; ?>js/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>-->
<script type="text/javascript" charset="utf-8">
    $(document).ready(function () {
        $(" a[rel^='prettyPhoto']").prettyPhoto({theme: 'facebook',
            social_tools: false /* html or false to disable */});
    });
</script>
<script type="text/javascript" src="<?php echo CONF_WEBROOT_URL; ?>js/jquery.naviDropDown.1.0.js"></script>
<script type="text/javascript">
    $(function () {
        $('.navigation_vert').naviDropDown({
            dropDownWidth: '350px',
            orientation: 'vertical'
        });
    });
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
    <li>    <a href="deals.php?status=active" <?php echo $class; ?>><?php echo t_lang('M_TXT_ACTIVE'); ?> <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?> </a></li>
    <li><a href="deals.php?status=expired" <?php if ($tabStatus == 'expired') echo $tabClass; ?>><?php echo t_lang('M_TXT_EXPIRED'); ?> <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?> </a></li>
    <li><a href="deals.php?status=upcoming" <?php if ($tabStatus == 'upcoming') echo $tabClass; ?>><?php echo t_lang('M_TXT_UPCOMING'); ?>  <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?></a></li>
    <li><a href="deals.php?status=un-approval" <?php if ($tabStatus == 'un-approval') echo $tabClass; ?>><?php echo t_lang('M_TXT_UNAPPROVED'); ?>  <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?></a></li>
    <li><a href="deals.php?status=rejected" <?php if ($tabStatus == 'rejected') echo $tabClass; ?>><?php echo t_lang('M_TXT_REJECTED'); ?>  <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?></a></li>
    <li><a href="deals.php?status=cancelled" <?php if ($tabStatus == 'cancelled') echo $tabClass; ?>><?php echo t_lang('M_TXT_CANCELLED'); ?>  <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?></a></li>
    <li><a href="deals.php?status=purchased" <?php if ($tabStatus == 'purchased') echo $tabClass; ?>><?php echo t_lang('M_TXT_MINIMUM_ONE_COUPON_SOLD'); ?> </a></li>
    <li><a href="deals.php?status=incomplete" <?php if ($tabStatus == 'incomplete') echo $tabClass; ?>><?php echo t_lang('M_TXT_INCOMPLETE'); ?>  <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?></a></li>
    <li><a href="deals.php?status=unsettled" title="<?php echo t_lang('M_TXT_UNSETTLED_DEALS_TOOL_TIP'); ?>" <?php if ($tabStatus == 'unsettled') echo $tabClass; ?>><?php echo t_lang('M_TXT_UNSETTLED'); ?>  <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?></a></li>
</ul>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name">
            <?php
            if ($tabStatus == "un-approval") {
                $tabStatus = t_lang('M_TXT_UNAPPROVED');
            }
            ?>
            <?php echo $tabStatus . " " . t_lang('M_TXT_DEALS'); ?> 
            <?php if (checkAdminAddEditDeletePermission(5, '', 'add')) { ?>
                <ul class="actions right">
                    <li class="droplink">
                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                        <div class="dropwrap">
                            <ul class="linksvertical">
                                <li>
                                    <a  href="add-deals.php?page=<?php echo $page; ?>&add=new&status=<?php echo $_REQUEST['status']; ?>"><?php echo t_lang('M_TXT_ADD'); ?>  <?php echo t_lang('M_TXT_DEALS'); ?> / <?php echo t_lang('M_TXT_PRODUCTS'); ?> </a> 
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
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide();
                        return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="redtext"><?php echo $msg->display(); ?> </div>
                    <br/>
                    <br/>
                    <?php
                }
                if (isset($_SESSION['msgs'][0])) {
                    ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } if (is_numeric($_GET['edit']) || $_GET['add'] == 'new') { ?>
        <script type="text/javascript">
            $(document).ready(function () {
                addAddress(document.frmDeal.deal_company.value,<?php echo $_GET['edit']; ?>);
            });</script>
        <?php if ((checkAdminAddEditDeletePermission(5, '', 'add')) || (checkAdminAddEditDeletePermission(5, '', 'edit'))) { ?>
            <div class="box"><div class="title"> <?php echo t_lang('M_TXT_DEALS'); ?>  </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
            <?php
        } else {
            die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        }
    } else {
        ?>
        <div class="box searchform_filter"><div class="title"> <?php echo t_lang('M_TXT_DEALS'); ?>  <?php echo t_lang('M_TXT_SEARCH'); ?>  </div><div class="content togglewrap" style="display:none;">	<?php echo $Src_frm->getFormHtml(); ?></div>	 </div>	 

        <div class="contentgroup">	
            <?php require_once './inc.deal-list.php'; ?>
        </div>
    <?php } ?>
    <?php if ($pages > 1) { ?>
        <div class="footinfo">
            <aside class="grid_1">
                <?php echo $pagestring; ?>	 
            </aside>  
            <aside class="grid_2"><span class="info"><?php echo $pageStringContent; ?></span></aside>
        </div>
    <?php } ?>
</td>
<script type="text/javascript">
    var deletemsg = '<?php echo addslashes(t_lang('M_TXT_ARE_YOU_SURE_TO_DELETE')); ?>';
</script>
<?php require_once './footer.php'; ?>
