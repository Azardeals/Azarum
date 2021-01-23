<?php

require_once dirname(__FILE__) . '/../application-top.php';
require_once dirname(__FILE__) . '/navigation-functions.php';

function saveSubdealData($deal_id, $dealData)
{
    global $msg;
    global $db;
    $post = getPostedData();
    if ($post['sdeal_active'] == 1) {
        $subdeal = new Searchbase('tbl_sub_deals', 'sd');
        $subdeal->joinTable('tbl_deal_address_capacity', 'INNER JOIN', 'dac.dac_sub_deal_id=sd.sdeal_id', 'dac');
        $subdeal->addFld('dac_address_id,SUM(dac_address_capacity) as total_deal_coupon');
        $subdeal->addCondition('sdeal_deal_id', '=', $deal_id);
        if (!empty($post['sdeal_id'])) {
            $subdeal->addCondition('sdeal_id', '!=', $post['sdeal_id']);
        }
        $subdeal->addCondition('sdeal_active', '=', 1);
        $subdeal->addOrder('dac_address_id');
        $subdeal->addGroupBy('dac_address_id');
        $result = $subdeal->getResultSet();
        $existing_subdeal_coupon = $db->fetch_all_assoc($result);
        $dealCapacity = fetchSubDealAddressCapacity($deal_id);
        $companyLocationArray = fetchcompanyAddress($_GET['edit']);
        foreach ($dealCapacity as $key => $value) {
            $available_cupon[$key] = $value - $existing_subdeal_coupon[$key];
            if ($post['dac_address_capacity'][$key] > $available_cupon[$key]) {
                $post['dac_address_capacity'][$key] = $available_cupon[$key];
                if ($available_cupon < 0) {
                    $post['dac_address_capacity'][$key] = 0;
                    $post['sdeal_max_coupons'] = 0;
                }
                $msg->addError(t_lang('M_TXT_SUBDEAL_COUPON_SHOULD_NOT_GREATER_THAN_MAIN_DEAL_CAPACITY_ON_LOCATION') . $companyLocationArray[$key] . ' ' . t_lang('M_TXT_MAXIMUM YOU_CAN_ADD') . ' ' . $available_cupon[$key]);
                return false;
            }
        }
    }
    $post['sdeal_max_coupons'] = array_sum($post['dac_address_capacity']);
    if ($post['sdeal_id'] > 0) {
        updateSubdealOnlineBookingRequestData($deal_id, $post['sdeal_id'], $post);
    }
    $record = new TableRecord('tbl_sub_deals');
    $subdeal_option_value_data = array(
        'sdeal_name' => $post['sdeal_name'],
        'sdeal_deal_id' => $deal_id,
        'sdeal_original_price' => $post['sdeal_original_price'],
        'sdeal_discount' => $post['sdeal_discount'],
        'sdeal_discount_is_percentage' => $post['sdeal_discount_is_percentage'],
        'sdeal_max_coupons' => $post['sdeal_max_coupons'],
        'sdeal_active' => $post['sdeal_active'],
            //  'sdeal_active' => $post['sdeal_active'],
    );
    $subdeal_option_value_data['sdeal_id'] = $post['sdeal_id'];
    $record->assignValues($subdeal_option_value_data);
    if (!empty($_REQUEST['sdeal_id'])) {
        //    $whr= array('smt'=>'sdeal_id = ?', 'vals'=>array($post['sdeal_id']), 'execute_mysql_functions'=>false);
        //   $record->update($whr,[]);
        $msg->addMessage(t_lang('M_TXT_SUB_DEAL_UPDATED_SUCCESSFULLY'));
    } else {
        if (!$db->update_from_array('tbl_deals', array('deal_complete' => 1), 'deal_id=' . $deal_id)) {
            dieJsonError($db->getError());
        }
        $msg->addMessage(t_lang('M_TXT_SUB_DEAL_ADDED_SUCCESSFULLY'));
    }
    $record->addNew(array('IGNORE'), $subdeal_option_value_data);
    $sub_deal_id = ($post['sdeal_id'] > 0) ? $post['sdeal_id'] : $record->getId();
    if (isset($post['dac_address_capacity'])) {
        savelocationCapacity($deal_id, $sub_deal_id, $post);
    }
    return true;
}

function savelocationCapacity($deal_id, $sub_deal_id, $post)
{
    global $msg;
    global $db;
    if ($sub_deal_id <= 0) {
        return false;
    } else {
        $whr = array('smt' => 'dac_sub_deal_id = ?', 'vals' => array($sub_deal_id), 'execute_mysql_functions' => false);
        $res = $db->deleteRecords('tbl_deal_address_capacity', $whr);
    }
    foreach ($post['dac_address_capacity'] as $location_id => $capacity) {
        $record1 = new TableRecord('tbl_deal_address_capacity');
        $subdeal_address_value_data = array(
            'dac_deal_id' => $deal_id,
            'dac_address_id' => $location_id,
            'dac_address_capacity' => $capacity,
            'dac_sub_deal_id' => $sub_deal_id
        );
        $record1->assignValues($subdeal_address_value_data);
        $record1->addNew(array('IGNORE'), $subdeal_address_value_data);
        if ($capacity > 0) {
            saveBookingRequestDate($location_id, $deal_id, $sub_deal_id, $capacity);
        } else {
            $whr = array('smt' => 'dbdate_deal_id = ? and dbdate_sub_deal_id = ? and dbdate_company_location_id =?', 'vals' => array($deal_id, $sub_deal_id, $location_id), 'execute_mysql_functions' => false);
            $res = $db->deleteRecords('tbl_deal_booking_dates', $whr);
        }
    }
    return true;
}

function updateSubdealOnlineBookingRequestData($dealId, $subdealId, $post)
{
    global $db;
    $row = fetchSubDealPrice($subdealId);
    if ($row) {
        $location_capacity = fetchSubDealAddressCapacity($dealId, $subdealId);
        foreach ($location_capacity as $location => $capacity) {
            if ($capacity != $post['dac_address_capacity'][$location]) {
                $whr = array('smt' => 'dbdate_deal_id = ? and dbdate_sub_deal_id= ? and dbdate_company_location_id = ?', 'vals' => array($dealId, $subdealId, $location), 'execute_mysql_functions' => false);
                $data['dbdate_stock'] = $post['dac_address_capacity'][$location];
                $res = $db->update_from_array('tbl_deal_booking_dates', $data, $whr);
            }
        }
        if (($row['sdeal_original_price'] != $post['sdeal_original_price']) || ($row['sdeal_discount'] != $post['sdeal_discount']) || ($row['sdeal_discount_is_percentage'] != $post['sdeal_discount_is_percentage'])) {
            $price = $post['sdeal_original_price'] - (($post['sdeal_discount_is_percentage'] == 1) ? $post['sdeal_original_price'] * $post['sdeal_discount'] / 100 : $post['sdeal_discount']);
            $whr = array('smt' => 'dbdate_deal_id = ? and dbdate_sub_deal_id= ?', 'vals' => array($dealId, $subdealId), 'execute_mysql_functions' => false);
            $data['dbdate_price'] = $price;
            $res = $db->update_from_array('tbl_deal_booking_dates', $data, $whr);
        }
    }
    return true;
}

function updateOnlineBookingRequestData($dealId, $post)
{
    global $db;
    $row = fetchDealInfo($dealId);
    if ($row['deal_sub_type'] == 2 && $row['deal_is_subdeal'] == 0) {
        if (isset($post['deal_original_price'])) {
            if (($row['deal_original_price'] != $post['deal_original_price']) || ($row['deal_discount'] != $post['deal_discount']) || ($row['deal_discount_is_percentage'] != $post['deal_discount_is_percentage'])) {
                $price = $post['deal_original_price'] - (($post['deal_discount_is_percent'] == 1) ? $post['deal_original_price'] * $post['deal_discount'] / 100 : $post['deal_discount']);
                $whr = array('smt' => 'dbdate_deal_id = ? ', 'vals' => array($dealId), 'execute_mysql_functions' => false);
                $data['dbdate_price'] = $price;
                $res = $db->update_from_array('tbl_deal_booking_dates', $data, $whr);
            }
        } else {
            $location_capacity = fetchSubDealAddressCapacity($dealId);
            //print_r($location_capacity); print_r($post); exit();
            foreach ($location_capacity as $location => $capacity) {
                if ($capacity != $post[$location]) {
                    $whr = array('smt' => 'dbdate_deal_id = ? and  dbdate_company_location_id = ?', 'vals' => array($dealId, $location), 'execute_mysql_functions' => false);
                    $data['dbdate_stock'] = $post[$location];
                    $res = $db->update_from_array('tbl_deal_booking_dates', $data, $whr);
                }
            }
        }
    }
    return true;
}

function fetchbookingDealDatesId($dealId, $subdealId = 0, $dbdate_date = '', $company_location_id = 0)
{
    global $msg;
    global $db;
    $srch = new SearchBase('tbl_deal_booking_dates', 'dbd');
    $srch->doNotCalculateRecords();
    $srch->doNotLimitRecords();
    $srch->addCondition('dbd.dbdate_deal_id', '=', $dealId);
    if ($subdealId > 0) {
        $srch->addCondition('dbd.dbdate_sub_deal_id', '=', $subdealId);
    }
    if ($dbdate_date != "") {
        $srch->addCondition('dbd.dbdate_date', '=', $dbdate_date);
    }
    if ($company_location_id != "") {
        $srch->addCondition('dbd.dbdate_company_location_id', '=', $company_location_id);
    }
    $srch->addMultipleFields(array('dbdate_id,dbdate_date'));
    $srch->addOrder('dbdate_date');
    $rs = $srch->getResultSet();
    $row = $db->fetch_all_assoc($rs);
    if (!$row) {
        return false;
    } else {
        return $row;
    }
}

function fetchbookingDealInfo($dealId, $subdealId = 0, $dbdate_date = '', $company_location_id = '')
{
    global $msg;
    global $db;
    $srch = new SearchBase('tbl_deal_booking_dates', 'dbd');
    $srch->doNotCalculateRecords();
    $srch->doNotLimitRecords();
    $srch->addCondition('dbd.dbdate_deal_id', '=', $dealId);
    if ($subdealId > 0) {
        $srch->addCondition('dbd.dbdate_sub_deal_id', '=', $subdealId);
    }
    if ($dbdate_date != "") {
        $srch->addCondition('dbd.dbdate_date', '=', $dbdate_date);
    }
    if ($company_location_id != "") {
        $srch->addCondition('dbd.dbdate_company_location_id', '=', $company_location_id);
    }
    $srch->addOrder('dbdate_date');
    $rs = $srch->getResultSet();
    $row = $db->fetch_all($rs);
    if (!$row) {
        return false;
    } else {
        return $row;
    }
}

function fetchQuantityPriceDetail($dealId, $subdealId = 0, $start_time, $end_time, $sub_type = 0)
{
    $availableDates = fetchbookingDealInfo($dealId, $subdealId);
    foreach ($availableDates as $key => $value) {
        $availableDates[$value['dbdate_date']] = $value;
    }
    $dbdate_dates = array_column($availableDates, 'dbdate_date');
    $start_date = date("Y-m-d", strtotime($start_time));
    $end_date = date("Y-m-d", strtotime($end_time));
    $dateArray = getDatesFromRange($start_date, $end_date);
    $stockArray = [];
    foreach ($dateArray as $key => $value) {
        if (!(in_array($value, $dbdate_dates))) {
            $stockArray[$value]['type'] = 'backend_block';
        } elseif ($availableDates[$value]['dbdate_stock'] == 0) {
            if ($sub_type == 2) {
                $stockArray[$value]['type'] = 'backend_block';
            }
        }
        //$stockArray[$value]['stock']= getStockCellHtml($availableDates[$key]['dbdate_stock']);
        //$stockArray[$value]['price'] = getCalenderInfoHtml($availableDates[$value]['dbdate_price']);
    }
    return $stockArray;
}

function fetchRequestBookingblockUnblockDate($dealId, $subdealId = 0, $company_address_id = 0, $start_time = '', $end_time, $showprice, $device = "")
{
    $start_date = date("Y-m-d", strtotime($start_time));
    $end_date = date("Y-m-d", strtotime($end_time));
    $dateArray = getDatesFromRange($start_date, $end_date);
    $stockArray = [];
    if (!$showprice) {
        $availableDates = fetchbookingDealDatesId($dealId, $subdealId, '', $company_address_id);
        foreach ($dateArray as $key => $value) {
            if (!(in_array($value, $availableDates))) {
                $stockArray[$value]['type'] = 'disabled';
            }
        }
    } else {
        $availableDates = fetchbookingDealInfo($dealId, $subdealId, '', $company_address_id);
        $availableStockDates = fetchavailableDatesStock($dealId, $subdealId, $company_address_id, $start_time, $end_time);
        foreach ($availableDates as $key => $value) {
            $availableDates[$value['dbdate_date']] = $value;
        }
        $dbdate_dates = array_column($availableDates, 'dbdate_date');
        foreach ($dateArray as $key => $value) {
            if ($device == "api") {
                $stockArray[$value]['date'] = $value;
            }
            if (!(in_array($value, $dbdate_dates)) || $availableDates[$value]['dbdate_stock'] <= 0) {
                $stockArray[$value]['type'] = 'disabled';
                continue;
            } else if (in_array($value, $availableStockDates)) {
                $price = CONF_CURRENCY . number_format($availableDates[$value]['dbdate_price'], 2) . CONF_CURRENCY_RIGHT;
                if ($device == "api") {
                    $stockArray[$value]['price'] = number_format((float) $availableDates[$value]['dbdate_price'], 2, '.', '');
                } else {
                    $stockArray[$value]['price'] = getCalenderInfoHtml($price);
                }
            } else {
                $stockArray[$value]['type'] = 'Unavailable';
            }
        }
    }
    return $stockArray;
}

//price =0 and stock= 0 indicates date is disabled 
/* fetch dates whose stock is available */
function fetchavailableDatesStock($deal_id, $subdeal_id = 0, $location_id = 0, $startDate = '', $endDate = '')
{
    global $db;
    $srch = new SearchBase('tbl_deal_booking_dates', 'dbd');
    $srch->addCondition('dbd.dbdate_deal_id', '=', $deal_id);
    $srch->addCondition('dbd.dbdate_sub_deal_id', '=', $subdeal_id);
    $srch->addCondition('dbd.dbdate_date', 'BETWEEN', array($startDate, $endDate));
    $srch->addCondition('dbd.dbdate_company_location_id', '=', $location_id);
    $probation_time = date('Y-m-d H:i:s', strtotime(ORDER_PROBATION_TIME));
    $srch->addMultipleFields(array('dbdate_id, dbdate_date'));
    $condition = 'dbd.dbdate_stock > (SELECT count( * )
		FROM `tbl_order_bookings` ob
		INNER JOIN tbl_order_deals AS od ON obooking_od_id = od.od_id
		INNER JOIN tbl_orders AS o ON od.od_order_id = o.order_id
		WHERE dbdate_date
		BETWEEN `obooking_booking_from`
		AND `obooking_booking_till`
		AND od.od_deal_id =' . $deal_id . '
		AND od.od_subdeal_id =' . $subdeal_id . '
		AND (o.order_payment_status=1 OR( o.order_payment_status=0 AND o.order_date > "' . $probation_time . '")) )';
    $srch->addDirectCondition($condition);
    $rs = $srch->getResultSet();
    $subdealData = $db->fetch_all_assoc($rs);
    return $subdealData;
}

function getDatesFromRange($start, $end)
{
    $dates = array($start);
    while (end($dates) < $end) {
        $dates[] = date('Y-m-d', strtotime(end($dates) . ' +1 day'));
    }
    return $dates;
}

function savenewBookingDates($deal_id)
{
    global $db;
    $row = fetchDealInfo($deal_id);
    $dealcompanyLocationArray = fetchSubDealAddressCapacity($deal_id);
    if (isset($_POST['deal_is_subdeal']) && ($_POST['deal_is_subdeal'] == 1)) {
        $condition['sdeal_deal_id'] = $deal_id;
        $sub_deal_ids = getRecords('tbl_sub_deals', $condition, 'LIST');
        foreach ($sub_deal_ids as $subdealId => $deal_id) {
            $companyLocationArray = fetchSubDealAddressCapacity($deal_id, $subdealId);
            foreach ($companyLocationArray as $location_id => $capacity) {
                saveBookingRequestDate($location_id, $deal_id, $subdealId, $capacity);
            }
        }
    } else {
        //$companyLocationArray = fetchSubDealAddressCapacity($deal_id);
        foreach ($dealcompanyLocationArray as $location_id => $capacity) {
            saveBookingRequestDate($location_id, $deal_id, 0, $capacity);
        }
    }
    if ($row['deal_sub_type'] == 2) {
        $ts1 = strtotime(date('Y-m-d', strtotime($row['deal_start_time'])));
        $ts2 = strtotime(date('Y-m-d', strtotime($row['deal_end_time'])));
        $seconds_diff = $ts2 - $ts1;
        $day_diff = floor($seconds_diff / 3600 / 24);
        $deal_max_coupon = 0;
        foreach ($dealcompanyLocationArray as $location_id => $capacity) {
            $deal_max_coupon += ($day_diff * $capacity);
        }
        $whr = array('smt' => 'deal_id = ? ', 'vals' => array($deal_id), 'execute_mysql_functions' => false);
        $data['deal_max_coupons'] = $deal_max_coupon;
        $res = $db->update_from_array('tbl_deals', $data, $whr);
    }
    return true;
}

function saveBookingRequestDate($company_location_id, $deal_id, $subdeal_id = 0, $stock = 0)
{
    if ($deal_id <= 0) {
        return false;
    }
    global $db;
    $row = fetchDealInfo($deal_id);
    if ($row['deal_sub_type'] >= 1 && $row['deal_type'] == 0) {
        $record = new TableRecord('tbl_deal_booking_dates');
        $start_date = date("Y-m-d", strtotime($row['deal_start_time']));
        $end_date = date("Y-m-d", strtotime($row['deal_end_time']));
        $dateArray = getDatesFromRange($start_date, $end_date);
        $row1 = fetchbookingDealDatesId($deal_id, $subdeal_id, '', $company_location_id);
        if ($row['deal_sub_type'] == 2) {
            $price = $row['deal_original_price'] - (($row['deal_discount_is_percent'] == 1) ? $row['deal_original_price'] * $row['deal_discount'] / 100 : $row['deal_discount']);
        }
        if ($row['deal_is_subdeal'] == 1 && $row['deal_sub_type'] == 2) {
            $subdealData = fetchSubDealPrice($subdeal_id);
            $price = $subdealData['sdeal_original_price'] - (($subdealData['sdeal_discount_is_percentage'] == 1) ? $subdealData['sdeal_original_price'] * $subdealData['sdeal_discount'] / 100 : $subdealData['sdeal_discount']);
        }
        if (!empty($row1)) {
            /* echo "<pre>";
              print_r($row1);
              print_r($dateArray);
              echo "</pre>"; */
            $first = reset($row1);
            $last = end($row1);
            $i = 0;
            if ($dateArray[$i] < $first) {
                while ($dateArray[$i] < $first) {
                    $data[$i]['dbdate_deal_id'] = $deal_id;
                    $data[$i]['dbdate_sub_deal_id'] = $subdeal_id;
                    $data[$i]['dbdate_date'] = $dateArray[$i];
                    $data[$i]['dbdate_company_location_id'] = $company_location_id;
                    if ($row['deal_sub_type'] == 2) {
                        $data[$i]['dbdate_price'] = $price;
                        $data[$i]['dbdate_stock'] = $stock;
                    }
                    $record->assignValues($data[$i], false);
                    if ($record->addNew(array('IGNORE'), $data[$i])) {
                        $msg = "date added";
                    }
                    $i++;
                }
            } else {
                foreach ($row1 as $dkey => $datevalue) {
                    if ($datevalue < $dateArray[0]) {
                        $whr = array('smt' => 'dbdate_date = ? and dbdate_deal_id = ? and dbdate_sub_deal_id = ? and dbdate_company_location_id =?', 'vals' => array($datevalue, $deal_id, $subdeal_id, $company_location_id), 'execute_mysql_functions' => false);
                        $res = $db->deleteRecords('tbl_deal_booking_dates', $whr);
                    }
                    //  $i++;
                }
            }
            $key = array_search($last, $dateArray);
            if ($key) {
                $i = $key + 1;
                while ($dateArray[$i] > $last) {
                    $data[$i]['dbdate_deal_id'] = $deal_id;
                    $data[$i]['dbdate_company_location_id'] = $company_location_id;
                    $data[$i]['dbdate_sub_deal_id'] = $subdeal_id;
                    $data[$i]['dbdate_date'] = $dateArray[$i];
                    if ($row['deal_sub_type'] == 2) {
                        $data[$i]['dbdate_price'] = $price;
                        $data[$i]['dbdate_stock'] = $stock;
                    }
                    $record->assignValues($data[$i], false);
                    if ($record->addNew(array('IGNORE'), $data[$i])) {
                        $msg = "date added";
                    }
                    $i++;
                }
            } else {
                $key = array_search($end_date, $row1);
                if ($key) {
                    foreach ($row1 as $dkey => $dvalue) {
                        if ($dvalue > $end_date) {
                            $whr = array('smt' => 'dbdate_date = ? and dbdate_deal_id = ? and dbdate_sub_deal_id = ? and dbdate_company_location_id =?', 'vals' => array($dvalue, $deal_id, $subdeal_id, $company_location_id), 'execute_mysql_functions' => false);
                            $res = $db->deleteRecords('tbl_deal_booking_dates', $whr);
                        }
                    }
                }
            }
        } else {
            foreach ($dateArray as $key => $value) {
                $data[$key]['dbdate_deal_id'] = $deal_id;
                $data[$key]['dbdate_company_location_id'] = $company_location_id;
                $data[$key]['dbdate_sub_deal_id'] = $subdeal_id;
                $data[$key]['dbdate_date'] = $value;
                if ($row['deal_sub_type'] == 2) {
                    $data[$key]['dbdate_price'] = $price;
                    $data[$key]['dbdate_stock'] = $stock;
                }
                $record->assignValues($data[$key], false);
                if ($record->addNew(array('IGNORE'), $data[$key])) {
                    $msg = "date added";
                }
            }
        }
        return true;
    }
}

function fetchDealInfo($dealId)
{
    global $db;
    $srch = new SearchBase('tbl_deals');
    $srch->addCondition('deal_id', '=', $dealId);
    $rs = $srch->getResultSet();
    $row = $db->fetch($rs);
    return $row;
}

function fetchSubDealPrice($sdeal_deal_id)
{
    global $db;
    $srch = new SearchBase('tbl_sub_deals');
    $srch->addCondition('sdeal_id', '=', $sdeal_deal_id);
    $rs = $srch->getResultSet();
    $row = $db->fetch($rs);
    return $row;
}

function fetchcompanyAddress($deal_id, $sub_deal_id = 0)
{
    global $db;
    $srch = new SearchBase('tbl_deal_address_capacity', 'dac');
    $srch->addCondition('dac_deal_id', '=', $deal_id);
    if ($sub_deal_id > 0) {
        $srch->addCondition('dac_address_capacity', '>', 0);
        $srch->addCondition('dac_sub_deal_id', '=', $sub_deal_id);
    } else {
        $srch->addCondition('dac_sub_deal_id', '=', 0);
    }
    $srch->joinTable('tbl_company_addresses', 'INNER JOIN', 'dac.dac_address_id=ca.company_address_id', 'ca');
    $srch->addMultipleFields(array('dac.dac_address_id', 'CONCAT_WS("\n" ,company_address_line1' . $_SESSION['lang_fld_prefix'] . ', company_address_line2' . $_SESSION['lang_fld_prefix'] . ',company_address_line3' . $_SESSION['lang_fld_prefix'] . ') AS `company_address`'));
    $rs = $srch->getResultSet();
    $row = $db->fetch_all_assoc($rs);
    return $row;
}

function fetchRequestForm($post)
{
    $arry = fetchbookingDealInfo($post['dealId'], $post['sub_deal_id'], $post['date']);
    $arr_selected = array_column($arry, 'dbdate_company_location_id');
    $companydata = fetchcompanyAddress($post['dealId'], $post['sub_deal_id']);
    $length = sizeof($companydata);
    $frm = new Form('location_form', 'location_form');
    $frm->setJsErrorDisplay('afterfield');
    $frm->setValidatorJsObjectName('frmrequestValidator');
    $frm->setTableProperties('width="100%" class="tbl_form"');
    $frm->setRequiredStarWith('caption');
    $fld = $frm->addCheckBoxes(t_lang('M_TXT_LOCATION'), 'dbdate_company_location_id', $companydata, $arr_selected, $length);
    //$fld->requirements()->setRequired();
    $frm->addHiddenField('', 'dbdate_deal_id', $post['dealId'], '');
    $frm->addHiddenField('', 'dbdate_sub_deal_id', $post['sub_deal_id'], '');
    $frm->addHiddenField('', 'dbdate_date', $post['date'], '');
    $frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_UPDATE'), 'btn_submit', '');
    $frm->addHiddenField('', 'mode', 'requestFormSubmit', '');
    $frm->setOnSubmit('requestFormSubmit(this,frmrequestValidator); return(false);');
    return $frm->getFormHtml();
}

function deletebookingdateForLocation($deal_id)
{
    global $db;
    $companyLocationArray = fetchcompanyAddress($deal_id);
    //print_r($companyLocationArray);
    $company_location_ids = array_keys($companyLocationArray);
    $notRequireLocId = array_diff($company_location_ids, $_POST['dac_address_id']);
    foreach ($notRequireLocId as $key => $value) {
        $whr = array('smt' => 'dbdate_deal_id = ? and dbdate_company_location_id = ?', 'vals' => array($deal_id, $value), 'execute_mysql_functions' => false);
        $res = $db->deleteRecords('tbl_deal_booking_dates', $whr);
        $whr = array('smt' => 'dac_deal_id = ? and dac_address_id = ? and dac_sub_deal_id >0', 'vals' => array($deal_id, $value), 'execute_mysql_functions' => false);
        $res = $db->deleteRecords('tbl_deal_address_capacity', $whr);
    }
}

function getCalenderInfoHtml($data)
{
    $template = "<span class='text-info'>   " . $data . "</span>";
    return $template;
}

function getStockCellHtml($data)
{
    $template = "<span class='text-info1'><i class='ion-record green-color'></i>   " . $data . "</span>";
    return $template;
}

function fetchSubDealAddressCapacity($dealId, $subdeal_id = 0)
{
    global $db;
    $srch = new SearchBase('tbl_deal_address_capacity', 'dac');
    $srch->doNotCalculateRecords();
    $srch->doNotLimitRecords();
    if ($subdeal_id > 0) {
        $srch->addCondition('dac.dac_deal_id', '=', $dealId);
        $srch->addCondition('dac.dac_sub_deal_id', '=', $subdeal_id);
    }
    if ($dealId > 0 && $subdeal_id == 0) {
        $srch->addCondition('dac.dac_deal_id', '=', $dealId);
        $srch->addCondition('dac.dac_sub_deal_id', '=', 0);
    }
    $srch->addMultipleFields(array('dac_address_id,dac_address_capacity'));
    $srch->addOrder('dac_address_id');
    $rs = $srch->getResultSet();
    $row = $db->fetch_all_assoc($rs);
    if (!$row) {
        return false;
    } else {
        return $row;
    }
}

function deleteAllSubdealData($post)
{
    global $db;
    $whr = array('smt' => 'sdeal_deal_id = ?', 'vals' => array((int) $post['deal_id']));
    $db->deleteRecords('tbl_sub_deals', $whr);
    $whr1 = array('smt' => 'dbdate_deal_id = ? and dbdate_sub_deal_id > 0 ', 'vals' => array($post['deal_id']));
    $db->deleteRecords('tbl_deal_booking_dates', $whr1);
    $whr = array('smt' => 'dac_deal_id = ? and dac_sub_deal_id >0', 'vals' => array($post['deal_id']), 'execute_mysql_functions' => false);
    $res = $db->deleteRecords('tbl_deal_address_capacity', $whr);
    return true;
}

function deletebookingDataofWithoutSubDeal($post)
{
    global $db;
    $whr1 = array('smt' => 'dbdate_deal_id = ? and dbdate_sub_deal_id = 0 ', 'vals' => array($post['deal_id']));
    $db->deleteRecords('tbl_deal_booking_dates', $whr1);
    return true;
}
