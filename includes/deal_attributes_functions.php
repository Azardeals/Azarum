<?php

function canDeleteAttribute($dattr_id)
{
    global $srch, $db, $msg;
    $srch = new SearchBase('tbl_deal_to_attributes', 'dta');
    $srch->addCondition('d_to_attr_dattr_id', '=', $dattr_id);
    $rs = $srch->getResultSet();
    $total_count = $srch->recordCount($rs);
    return $total_count;
    /* if($total_count == 0 ) return true; else return false; */
}

function deleteAttribute($dattr_id)
{
    global $db, $msg, $srch;
    if (checkAdminAddEditDeletePermission(15, '', 'delete')) {
        if (canDeleteAttribute($dattr_id) == 0) {
            if (!$db->update_from_array('tbl_deal_attributes', array('dattr_deleted' => 1), 'dattr_id' . '=' . $dattr_id)) {
                $msg->addError($db->getError());
            } else {
                $msg->addMsg(t_lang('M_TXT_RECORD_DELETED'));
            }
        } else {
            $msg->addError(t_lang('M_TXT_ATTRIBUTE_DELETION_NOT_ALLOWED'));
        }
    } else {
        $msg->addError(t_lang("M_TXT_UNAUTHORIZED_ACCESS"));
    }
}

function getAttributesKeyValue($data = [])
{
    global $db;
    $srch = new SearchBase('tbl_deal_attributes', 'dattr');
    if ($data['type_id'] && intval($data['type_id'])) {
        $type_id = intval($data['type_id']);
        $srch->addCondition('dattr.dattr_type_id', '=', $type_id);
    }
    $srch->addMultipleFields(array('dattr_id', 'dattr_value'));
    $result = $db->fetch_all_assoc($srch->getResultSet());
    return $result;
}

/* Options functions starts here
  function getDealOptions() is used to fecth all the options/attributes associated with deal/product by passing integer type deal_id
 */

function getDealOptions($deal_id)
{
    if (!isset($deal_id) || intval($deal_id) <= 0) {
        return [];
    }
    $deal_id = intval($deal_id);
    global $db;
    $deal_option_data = [];
    $srch = new SearchBase('tbl_deal_option', 'd_op');
    $srch->addMultipleFields(array('op.option_id', 'op.option_name', 'op.option_name_lang1', 'op.option_type', 'd_op.deal_option_id', 'd_op.deal_id', 'd_op.required'));
    $srch->joinTable('tbl_options', 'LEFT JOIN', 'op.option_id = d_op.option_id', 'op');
    $srch->addCondition('d_op.deal_id', '=', $deal_id);
    $srch->addCondition('op.is_deleted', '=', 0);
    //  $srch->addOrder('d_op.parent_option_id');
    $srch->addOrder('d_op.deal_option_id');
    //echo $srch->getQuery();
    $rs = $srch->getResultSet();
    $deal_options = $db->fetch_all($srch->getResultSet());
    if (is_array($deal_options) && count($deal_options)) {
        foreach ($deal_options as $key => $option) {
            if ($option['option_type'] == 'select' || $option['option_type'] == 'radio') {
                $deal_option_value_data = [];
                $srch = new SearchBase('tbl_deal_option_value', 'dov');
                //    $srch->joinTable('tbl_deal_option_value', 'INNER JOIN', "dov.option_value_id = dp.parent_option_value_id AND dp.deal_id= $deal_id", 'dp');
                $srch->joinTable('tbl_option_values', 'INNER JOIN', 'dov.option_value_id = ov.option_value_id', 'ov');
                $srch->addCondition('dov.deal_id', '=', $deal_id);
                $srch->addCondition('dov.deal_option_id', '=', $option['deal_option_id']);
                $srch->addOrder('ov.sort_order');
                $srch->addGroupBy('dov.option_value_id');
                $srch->addGroupBy('dov.price');
                $rs = $srch->getResultSet();
                $deal_option_values = $db->fetch_all($srch->getResultSet());
                if (is_array($deal_option_values) && count($deal_option_values)) {
                    foreach ($deal_option_values as $deal_op_value) {
                        //$optionVals = getProductAttributeValue1($deal_id, $option['deal_option_id'], $deal_op_value['deal_option_value_id']);
                        $deal_option_value_data[] = array(
                            'deal_option_value_id' => $deal_op_value['deal_option_value_id'],
                            'option_value_id' => $deal_op_value['option_value_id'],
                            'name' => $deal_op_value['name'],
                            'quantity' => $deal_op_value['quantity'],
                            'price' => $deal_op_value['price'],
                            'price_prefix' => $deal_op_value['price_prefix'],
                            'deal_option_id' => $option['deal_option_id'],
                                //'deal_option' =>$optionVals
                        );
                    }
                }
                $deal_option_data[] = array(
                    'deal_option_id' => $option['deal_option_id'],
                    'option_id' => $option['option_id'],
                    'option_name' => $option['option_name' . $_SESSION['lang_fld_prefix']],
                    'option_type' => $option['option_type'],
                    'option_value' => $deal_option_value_data,
                    'required' => $option['required']
                );
            } elseif ($option['option_type'] == 'text' || $option['option_type'] == 'textarea') {
                $deal_option_data[] = array(
                    'deal_option_id' => $option['deal_option_id'],
                    'option_id' => $option['option_id'],
                    'option_name' => $option['option_name' . $_SESSION['lang_fld_prefix']],
                    'option_type' => $option['option_type'],
                    'option_value' => $option['option_value'],
                    'required' => $option['required']
                );
            }
        }
    }
    //   echo "<pre>";
    // print_r($deal_option_data);
    return $deal_option_data;
}

/* Options functions ends here */

function getProductAttributeValue1($dealId, $dealOptionId, $dealOptionValueId)
{
    global $db;
    $srch = new SearchBase('tbl_deal_option_value', 'dov2');
    $srch->addCondition('dov2.deal_option_value_id', '=', $dealOptionValueId, 'AND');
    $srch->addCondition('dov2.deal_option_id', '=', $dealOptionId, 'AND');
    $srch->addCondition('dov2.deal_id', '=', $dealId);
    $srch->joinTable('tbl_deal_option_value', 'INNER JOIN', "dov2.option_value_id = dov1.parent_option_value_id AND dov2.deal_id= $dealId AND dov1.deal_id= $dealId", 'dov1');
    $srch->joinTable('tbl_option_values', 'INNER JOIN', 'dov1.option_value_id=ov.option_value_id', 'ov');
    $srch->joinTable('tbl_options', 'INNER JOIN', 'op.option_id=ov.option_id AND op.is_deleted=0', 'op');
    //$srch->joinTable('tbl_deal_option', 'left JOIN', 'op.option_id = d_op.option_id', 'd_op');
    $srch->addMultipleFields(array('dov1.deal_option_value_id,ov.name,dov1.price,dov1.price_prefix,dov1.option_value_id,dov1.deal_option_id,op.option_id, op.option_name, op.option_name_lang1,op.option_type'));
    $rs = $srch->getResultSet();
    $option_val_info = $db->fetch_all($rs);
    $optionVals = [];
    foreach ($option_val_info as $option_value) {
        $data['deal_option_id'] = $option_value['deal_option_id'];
        $data['deal_option_value_id'] = $option_value['deal_option_value_id'];
        $data['name'] = $option_value["name"];
        $data['price'] = $option_value["price"];
        $optionVals[] = $data;
    }
    $srch = new SearchBase('tbl_deal_option', 'd_op1');
    $srch->addMultipleFields(array('d_op1.required'));
    $srch->addCondition('d_op1.deal_id', '=', $dealId);
    $rs = $srch->getResultSet();
    $deal_options = $db->fetch($srch->getResultSet());
    $deal_option_data[] = array(
        'deal_option_id' => $dealOptionId,
        'option_id' => $option_value['option_id'],
        'option_name' => $option_value['option_name' . $_SESSION['lang_fld_prefix']],
        'option_type' => $option_value['option_type'],
        'option_value' => $optionVals,
        'required' => $deal_options['required']
    );
    return $deal_option_data;
}

/* function get_order_option() is used to fetch option associated with order
  Parameters: array type with key & value data,
  'od_id'  Integer type, od_id
 */

function get_order_option($data = [])
{
    global $db;
    $srch = new SearchBase('tbl_order_option', 'oo');
    if (count($data)) {
        foreach ($data as $key => $val) {
            switch ($key) {
                case 'od_id':
                    $srch->addCondition('oo.oo_od_id', '=', $val);
                    break;
            }
        }
    }
    $rs = $srch->getResultSet();
    if ($rows = $db->fetch_all($srch->getResultSet())) {
        return $rows;
    } else {
        return false;
    }
}

/* used to display options of selected products on the cart summary right side of cart page */

function displayCartOptions($cart_data_arr)
{
    $out = ''; //echo '<pre>';print_r($cart_data_arr); exit;
    if (isset($cart_data_arr) && count($cart_data_arr) && is_array($cart_data_arr)) {
        foreach ($cart_data_arr as $cart_data) {
            $out .= '<tr class="cart_summary_options"><td ><span class="prodct-name">' . $cart_data['deal_name'] . '</span>';
            $out .= '</td><td>' . amount(($cart_data['price'] * $cart_data['quantity']), 2) . '</td></tr>';
            $out .= '<tr class="Info-tbl-in"><td >- ' . t_lang('M_TXT_Quantity') . '</td><td >' . $cart_data['quantity'] . '</td></tr>';
            if (isset($cart_data['option']) && is_array($cart_data) && count($cart_data['option'])) {
                foreach ($cart_data['option'] as $op) {
                    $out .= '<tr class="Info-tbl-in"><td >- ' . $op['option_name'] . ': ' . $op['option_value'] . '</td><td >' . amount(($op['price'] * $cart_data['quantity']), 2) . '</td></tr>';
                }
            }
            if ($cart_data['tax']['taxAmount'] > 0) {
                $out .= '<tr class="Info-tbl-in"><td > ' . $cart_data['tax']['taxname'] . '</td><td >' . amount($cart_data['tax']['taxAmount'], 2) . '</td></tr>';
            }
            if ($cart_data['deal_type'] == 1 && $cart_data['deal_sub_type'] == 0 && $cart_data['shipping_charges'] > 0) {
                $out .= '<tr class="Info-tbl-in"><td >- ' . t_lang('M_TXT_SHIPPING_CHARGES') . '</td><td >' . amount($cart_data['shipping_charges'], 2) . '</td></tr>';
            }
        }
    }
    return $out;
}

/** this function is used for handle subdeal data and booking request data.* */
function getSubdealDataHtml($objDeal)
{
    $dealData = $objDeal->getFields();
    global $msg;
    $flag = true;
    $company_locations = true;
    include_once './subdeals-functions.php';
    $sub_type = $objDeal->getFldValue('deal_sub_type');
    if (is_numeric($sub_type) == 0) {
        $class = "subdeal";
    } else {
        $class = "even";
    }
    $deal_id = $objDeal->getFldValue('deal_id');
    global $db;
    $subdealArray = [];
    echo '<div id="getaway-calender" class="popup" >
    <div class="popup__content-wrap">
    <div class="popup__content">
		<section class="getaways_selection"> <div class="fixed_container">';
    echo '<div class="panel__full" >';
    echo '<a class="link__close" href="javascript:void(0)" onclick="$(\'.popup\').css(\'display\',\'none\');$(body).removeClass(\'hide__scroll\')"></a>';
    echo '<aside class="panel__left">
                <h4>' . t_lang('M_TXT_CHOOSE_YOUR_DEAL') . '</h4>
                <div class="list__scroller">
                        <ul class="list__selection">';
    /* used for display hotelbooking and booking request data without having subdeal */
    if ($objDeal->getFldValue('deal_is_subdeal') == 0) {
        $remaining = $dealData['deal_max_coupons'] - ($dealData['sold']);
        if ($remaining <= 0) {
            $flag = false;
            echo t_lang('M_TXT_ALL_VOUCHER_OF_DEAL_IS_SOLD_OUT');
        } else {
            $checked = "checked=true";
            $off = (($dealData['deal_discount_is_percent'] == 1) ? $dealData['deal_discount'] . '%' : CONF_CURRENCY . "" . $dealData['deal_discount']);
            $save = $dealData['deal_original_price'] - $dealData['price'];
            echo '<li class="selected">
                                    <a class="box__selection" href="javascript:void(0);" onclick="addSubdealClass(this,' . $deal_id . ')">
                                                    <span class="grid_1">
                                                        <strong>' . $dealData['deal_name'] . '</strong>
                                                        ' . amount($dealData['deal_original_price']) . ' list price - ' . $off . ' ' . t_lang('M_TXT_OFF') . ' - ' . t_lang('M_TXT_SAVE') . ' ' . amount($save) . '
                                                    </span>
                                                    <span class="grid_2">' . amount($dealData['price']) . '</span>
                                                </a>';
            $value = 0;
            echo '<input type="hidden" value=' . $value . ' ' . $checked . ' name="sdeal_id" >';
            echo '<li>';
        }
        /* End of used for display hotelbooking and booking request data without having subdeal */
        $subdeal_id = 0;
    }
    /*  used for display subdeal data */
    $button = t_lang('M_TXT_BOOK_NOW');
    if ($objDeal->getFldValue('deal_is_subdeal') == 1 && $objDeal->getFldValue('deal_sub_type') == 0) {
        $button = t_lang('M_TXT_BUY_NOW');
    }
    if ($objDeal->getFldValue('deal_is_subdeal') == 1) {
        $subdeal = getSubOption($deal_id);
        $result = $subdeal->getResultSet();
        $subdealcount = $subdeal->recordCount();
        if ($subdealcount == 0) {
            $company_locations = false;
            $flag = false;
            echo '<span class="noresultsfound">' . t_lang('M_TXT_ALL_VOUCHER_OF_SUBDEALS_IS_SOLD_OUT') . '</span>';
        }
        $rs = $db->fetch_all($result);
        $subdeal_id = $rs[0]['sdeal_id'];
        foreach ($rs as $key => $data) {
            $class = "";
            $checked = '';
            if ($key == 0) {
                $checked = "checked=true";
                $class = "selected";
            }
            echo '<li class="' . $class . '">';
            echo '<a class="box__selection" href="javascript:void(0);" onclick="addSubdealClass(this,' . $deal_id . ')">';
            $value = $data["sdeal_id"];
            echo '<input type="hidden" value=' . $value . ' ' . $checked . ' name="sdeal_id" >';
            $sdeal_price = (($data['sdeal_discount_is_percentage'] == 1) ? $data['sdeal_original_price'] - ($data['sdeal_original_price'] * $data['sdeal_discount'] / 100) : $data['sdeal_original_price'] - $data['sdeal_discount']);
            $off = (($data['sdeal_discount_is_percentage'] == 1) ? $data['sdeal_discount'] . ' %' : CONF_CURRENCY . "" . $data['sdeal_discount']);
            $save = $data['sdeal_original_price'] - $sdeal_price;
            echo '<span class="grid_1"> <strong>' . $data['sdeal_name'] . '</strong>' . amount($data['sdeal_original_price']) . t_lang('M_TXT_LIST_PRICE') . '  - ' . $off . ' ' . t_lang('M_TXT_OFF') . ' - ' . t_lang('M_TXT_SAVE') . ' ' . amount($save) . '</span> <span class="grid_2">' . amount($sdeal_price, 2) . '</span> </a>';
            echo '</li>';
        }
    }
    /*  used for display subdeal data */
    echo '</ul>';
    echo '</div>
     </aside>';
    if ($company_locations) {
        $company_List = fetchcompanyAddressListForPopup($deal_id, $subdeal_id);
        if (!empty($company_List)) {
            $count = 0;
            $first_key = key($company_List); // First Element's Key
            $first_value = reset($company_List); // First Element's Value
            $class = (sizeof($company_List) > 1) ? 'multilocation' : 'singleLocation';
            ?>
            <aside class="panel__right">
                <h4><?php echo t_lang('M_TXT_CHOOSE_DEAL_LOCATION_AND_DATE'); ?></h4>
                <div class="selection_area">
                    <a class="seleclink active <?php echo $class; ?>" href="javascript:void(0)" ><?php echo $first_value ?> </a>
                    <input type="hidden" name="company_location_id" id="company_location_id" value="<?php echo $first_key ?>">
                    <div style="display:none;" class="section_droparea">
                        <?php if (sizeof($company_List) > 1) {
                            ?>
                            <ul class="verticaldots_list" >
                                <?php foreach ($company_List as $key => $compLoc) {
                                    ?>
                                    <li><a href="javascript:void(0)" onclick="fetchCalenderlist('<?php echo $deal_id; ?>', '<?php echo $key; ?>', this);" > <?php echo $compLoc ?></a></li>
                                <?php }
                                ?>
                            </ul>
                        <?php }
                        ?>
                    </div>
                </div>
                <div class="section__cols">
                    <div class="grid_1">
                        <?php if ($sub_type >= 1) {
                            ?>
                            <div class="calender_container calendar">
                                <?php echo getCalenderHtml($deal_id, $subdeal_id, $first_key); ?>
                            </div>
                        </div>
                        <div class="grid_2">
                            <p><strong><?php echo t_lang('M_TXT_NOTE'); ?>:</strong>
                                <?php
                                if ($sub_type == 1) {
                                    echo t_lang('M_TXT_BOOKING_REQUEST_INFO');
                                } else {
                                    echo t_lang('M_TXT_ONLINE_BOOKING_INFO');
                                }
                                ?>
                            </p>
                            <span class="gap"></span>
                        <?php }
                        ?>
                        <?php
                    } else {
                        $flag = false;
                    }
                }
                ?>
                <?php if ($sub_type == 2) {
                    ?>
                    <span class ="price__total"> <?php echo t_lang('M_TXT_SUB_TOTAL') . ' = ' . CONF_CURRENCY ?><span id="subDealPrice"><?php echo number_format(0, 2) ?></span><?php echo CONF_CURRENCY_RIGHT ?></span>
                <?php }
                ?>
                <input type="hidden" id="start_date" value="">
                <input type="hidden" id="end_date">
                <?php if ($flag) {
                    ?>
                    <a class="themebtn themebtn--org themebtn--large themebtn--block" href="javascript:void(0);"   onclick="buySubDeal(<?php echo $deal_id; ?>, false,<?php echo CONF_FRIENDLY_URL; ?>, frm_data, $('.selected').find('input').val());" ><?php echo $button; ?></a>
                    <?php
                } else {

                }
                ?>
            </div>
        </div>
    </aside>
    </div>
    </div>
    </section>
    </div>
    </div>
    </div>
    <?php
}

function fetchcompanyAddressListForPopup($deal_id, $subdeal_id = 0)
{
    if (intval($deal_id) <= 0) {
        return false;
    }
    global $db;
    $deal_id = intval($deal_id);
    $srch = new SearchBase('tbl_deals', 'd');
    $srch->addCondition('deal_id', '=', $deal_id);
    $srch->addCondition('deal_deleted', '=', 0);
    $srch->joinTable('tbl_company_addresses', 'INNER JOIN', 'ca.company_id=d.deal_company', 'ca');
    if ($subdeal_id > 0) {
        $srch->joinTable('tbl_sub_deals', 'INNER JOIN', 'sd.sdeal_deal_id=d.deal_id AND sd.sdeal_id=' . $subdeal_id, 'sd');
        $srch->joinTable('tbl_deal_address_capacity', 'INNER JOIN', 'dac.dac_sub_deal_id=sd.sdeal_id AND dac.dac_address_id=ca.company_address_id', 'dac');
        $srch->joinTable('tbl_order_deals', 'LEFT OUTER JOIN', 'od.od_subdeal_id=sd.sdeal_id  AND od.od_company_address_id=dac.dac_address_id', 'od');
        $srch->joinTable('tbl_orders', 'LEFT OUTER JOIN', 'od.od_order_id=o.order_id', 'o');
        $srch->addFld("sd.sdeal_id");
    } else {
        $srch->joinTable('tbl_deal_address_capacity', 'INNER JOIN', 'dac.dac_deal_id=d.deal_id AND dac.dac_address_id=ca.company_address_id', 'dac');
        $srch->joinTable('tbl_order_deals', 'LEFT OUTER JOIN', 'od.od_deal_id=d.deal_id AND od.od_company_address_id=dac.dac_address_id', 'od');
        $srch->joinTable('tbl_orders', 'LEFT OUTER JOIN', 'od.od_order_id=o.order_id', 'o');
    }
    $srch->addMultipleFields(array('d.deal_id', 'dac.dac_address_capacity', 'ca.company_address_id', 'd.deal_city',
        'CONCAT_WS("<br/>" ,company_address_line1' . $_SESSION['lang_fld_prefix'] . ', company_address_line2' . $_SESSION['lang_fld_prefix'] . ',company_address_line3' . $_SESSION['lang_fld_prefix'] . ') AS `company_address`'));
    $probation_time = date('Y-m-d H:i:s', strtotime(ORDER_PROBATION_TIME));
    $srch->addFld("SUM(CASE WHEN o.order_payment_status=1 THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS sold");
    $srch->addFld("SUM(CASE WHEN o.order_payment_status=2 THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS refund");
    $srch->addFld("SUM(CASE WHEN o.order_payment_status=0 AND o.order_date > '" . $probation_time . "'  THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS payment_pending");
    $srch->addGroupBy('company_address_id');
    $rs = $srch->getResultSet();
    $data = [];
    if ($rows = $db->fetch_all($rs)) {
        foreach ($rows as $row) {
            $total_sold = intval($row['sold']) + intval($row['payment_pending']);
            if ($row['dac_address_capacity'] > $total_sold) {
                $data[$row['company_address_id']] = $row['company_address'];
            }
        }
        return $data;
    }
    return false;
}

function getCalenderHtml($deal_id, $subdealId = 0, $location_id = 0, $month = 0, $year = 0)
{
    //echo $location_id;
    include_once 'subdeals-functions.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . CONF_WEBROOT_URL . 'site-classes/calender.php';
    $calender = new Calendars();
    $calender->setAjax(true);
    if ($deal_id > 0) {
        $dealData = fetchDealInfo($deal_id);
        $start_date = date("Y-m-d", strtotime($dealData['deal_start_time']));
        $end_date = date("Y-m-d", strtotime($dealData['deal_end_time']));
        $calender->setDateRange($start_date, $end_date);
        $showprice = false;
        if ($dealData['deal_sub_type'] == 2) {
            $showprice = true;
        } else {
            $calender->attachEventHandler("javascript:void(0);");
        }
        $data = fetchRequestBookingblockUnblockDate($dealData['deal_id'], $subdealId, $location_id, $start_date, $dealData['deal_end_time'], $showprice, '');
        $calender->setDateData($data);
    }
    if ($month == 0) {
        $month = date('m');
    }
    if ($year == 0) {
        $year = date('Y');
    }
    if ($deal_id <= 0) {
        $start_date = date("Y-m-d");
        //$end_date = date("Y-m-d");
        $end_date = date('Y-m-d', strtotime(date("Y-m-d", time()) . " + 365 day"));
        $calender->setDateRange($start_date, $end_date);
    }
    return $calender->show($month, $year);
}

function getSubTotalOfOnlineDeal($deal_id, $subdeal_id = 0, $location_id, $startDate, $endDate, $is_ajax = false)
{
    global $db;
    if ($startDate == "" || $endDate == "") {
        return false;
    }
    $startDate_timestamp = strtotime($startDate);
    $endDate_timestamp = strtotime($endDate);
    if ($endDate_timestamp < $startDate_timestamp) {
        $temp = $startDate;
        $startDate = $endDate;
        $endDate = $temp;
    }
    $endDate = date('Y-m-d', strtotime($endDate . ' -1 day'));
    $probation_time = date('Y-m-d H:i:s', strtotime(ORDER_PROBATION_TIME));
    $srch = new SearchBase('tbl_deal_booking_dates', 'dbd');
    $srch->addCondition('dbd.dbdate_deal_id', '=', $deal_id);
    $srch->addCondition('dbd.dbdate_sub_deal_id', '=', $subdeal_id);
    $srch->addCondition('dbd.dbdate_date', 'BETWEEN', array($startDate, $endDate));
    $srch->addCondition('dbd.dbdate_company_location_id', '=', $location_id);
    $srch->addMultipleFields(array('SUM(dbdate_price) as subdeal_price'));
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
    $subdealData = $db->fetch($rs);
    if ($is_ajax) {
        if ($subdeal_id > 0) {
            $data['subdeal_list'] = getSubDealOptionHtmlByAjax($deal_id, $subdeal_id, $startDate, $endDate);
        }
        $data['subdeal_price'] = $subdealData['subdeal_price'];
        return $data;
    } else {
        return $subdealData['subdeal_price'];
    }
}

function getSubDealOptionHtmlByAjax($deal_id, $subdeal_id, $startDate, $endDate)
{
    global $db;
    $probation_time = date('Y-m-d H:i:s', strtotime(ORDER_PROBATION_TIME));
    $srch = new SearchBase('tbl_deal_booking_dates', 'dbd');
    $srch->addCondition('dbd.dbdate_deal_id', '=', $deal_id);
    $srch->addCondition('dbd.dbdate_sub_deal_id', '>', 0);
    $srch->addCondition('dbd.dbdate_date', 'BETWEEN', array($startDate, $endDate));
    $srch->joinTable('tbl_sub_deals', 'INNER JOIN', 's.sdeal_id=dbd.dbdate_sub_deal_id AND sdeal_active=1 AND dbd.dbdate_sub_deal_id>0', 's');
    $srch->addMultipleFields(array('dbdate_sub_deal_id ,s.*'));
    $condition = 'dbd.dbdate_stock > (SELECT count( * )
		FROM `tbl_order_bookings` ob
		INNER JOIN tbl_order_deals AS od ON obooking_od_id = od.od_id
		INNER JOIN tbl_orders AS o ON od.od_order_id = o.order_id
		WHERE dbdate_date
		BETWEEN `obooking_booking_from`
		AND `obooking_booking_till`
		AND od.od_deal_id =' . $deal_id . '
		AND (o.order_payment_status=1 OR( o.order_payment_status=0 AND o.order_date > "' . $probation_time . '")) )';
    $srch->addDirectCondition($condition);
    $srch->addGroupBy('s.sdeal_id');
    //echo $srch->getQuery();
    $rs = $srch->getResultSet();
    $subdealData = $db->fetch_all($rs);
    $str = "";
    foreach ($subdealData as $key => $data) {
        $class = "";
        $checked = '';
        if ($data["sdeal_id"] == $subdeal_id) {
            $checked = "checked=true";
            $class = "selected-deal";
        }
        $str .= '<li class="' . $class . '">';
        $str .= '<label>';
        $value = $data["sdeal_id"];
        $str .= '<input type="radio" value=' . $value . ' ' . $checked . ' name="sdeal_id" class="facetoption" onclick="addSubdealClass(this,' . $deal_id . ')">';
        // echo'<div class="deals-cnt">';
        $sdeal_price = (($data['sdeal_discount_is_percentage'] == 1) ? $data['sdeal_original_price'] - ($data['sdeal_original_price'] * $data['sdeal_discount'] / 100) : $data['sdeal_original_price'] - $data['sdeal_discount']);
        $off = (($data['sdeal_discount_is_percentage'] == 1) ? $data['sdeal_discount'] . '%' : CONF_CURRENCY . "" . $data['sdeal_discount']);
        $save = $data['sdeal_original_price'] - $sdeal_price;
        $str .= '<span class="deal-deatils"> <strong>' . $data['sdeal_name'] . '</strong>' . CONF_CURRENCY . number_format($data['sdeal_original_price'], 2) . CONF_CURRENCY_RIGHT . ' list price - ' . $off . ' ' . t_lang('M_TXT_OFF') . ' - ' . t_lang('M_TXT_SAVE') . ' ' . CONF_CURRENCY . number_format($save, 2) . CONF_CURRENCY_RIGHT . '</span> <span class="price-deatils">' . CONF_CURRENCY . number_format($sdeal_price, 2) . CONF_CURRENCY_RIGHT . '</span> </label>';
        $str .= '</li>';
    }
    return $str;
}

function calculateperDayPriceOfOnlineDeal($deal_id)
{
    global $db;
    $srch = new SearchBase('tbl_deals', 'd');
    $srch->joinTable('tbl_deal_address_capacity', 'INNER JOIN', 'dac.dac_deal_id=d.deal_id ', 'dac');
    $srch->addCondition('dac.dac_sub_deal_id', '=', 0);
    $srch->addCondition('d.deal_id', '=', $deal_id);
    $srch->addMultipleFields(array('d.deal_start_time ,d.deal_end_time ,SUM(dac_address_capacity) as per_day_capacity'));
    $rs = $srch->getResultSet();
    $dealData = $db->fetch($rs);
    $start_date = strtotime($dealData['deal_start_time']);
    $end_date = strtotime($dealData['deal_end_time']);
    $current_date = strtotime(date('Y-m-d'));
    $voucher_used = 0;
    if ($current_date <= $end_date) {
        $diff = $current_date - $start_date;
        $day_diff = floor($diff / 3600 / 24);
        $voucher_used = $day_diff * $dealData['per_day_capacity'];
    }
    if ($start_date <= $end_date) {
        $diff1 = $end_date - $start_date;
        $day_diff = floor($diff1 / 3600 / 24);
        $total_voucher = $day_diff * $dealData['per_day_capacity'];
    }
    //calculation for today_voucher_sold
    $srch = new SearchBase('tbl_order_deals', 'od');
    $srch->addCondition('mysql_func_date(o.order_date)', '=', date('Y-m-d'), 'AND', true);
    $srch->addCondition('od.od_deal_id', '=', $deal_id);
    $srch->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id=o.order_id', 'o');
    $probation_time = date('Y-m-d H:i:s', strtotime(ORDER_PROBATION_TIME));
    $srch->addFld("SUM(CASE WHEN o.order_payment_status=1  THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS sold");
    $srch->addFld("SUM(CASE WHEN o.order_payment_status=0 AND o.order_date>'" . $probation_time . "'  THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS payment_pending");
    $srch->addFld('od.od_deal_price');
    $rs = $srch->getResultSet();
    $today_voucher_detail = $db->fetch($rs);
    $today_sold = $today_voucher_detail['sold'];
    $total_voucherUsed = $today_voucher_detail['sold'] + $voucher_used;
    return $total_voucher - $total_voucherUsed;
}

function getSubOption($deal_id)
{
    $subdeal = new SearchBase('tbl_sub_deals', 'sd');
    $subdeal->addCondition('sdeal_deal_id', '=', $deal_id);
    $subdeal->addCondition('sdeal_active', '=', 1);
    $subdeal->addCondition('sdeal_max_coupons', '>', 0);
    $subdeal->addFld('sd.*');
    $subdeal->joinTable('tbl_order_deals', 'LEFT OUTER JOIN', 'sd.sdeal_id=od.od_subdeal_id', 'od');
    $subdeal->joinTable('tbl_orders', 'LEFT OUTER JOIN', 'od.od_order_id=o.order_id', 'o');
    $subdeal->addGroupBy('sd.sdeal_id');
    $subdeal->addFld("SUM(CASE WHEN o.order_payment_status=1 or o.order_date >'" . date('Y-m-d H:i:s', strtotime('-30 MINUTE')) . "' THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS sold");
    $subdeal->addHaving('mysql_func_sold', '<', 'mysql_func_(sdeal_max_coupons)', 'AND', true);
    return $subdeal;
}
?>
