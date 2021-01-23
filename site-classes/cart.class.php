<?php

require_once CONF_INSTALLATION_PATH . 'includes/page-functions/cart-functions.php';

class Cart
{

    protected $db;
    protected $error;
    protected $fat_cart_shipping_charges;

    function __construct()
    {
        global $db;
        $this->db = $db;
    }

    public function add($deal_id, $quantity = 1, $option = false, $for_friend = false, $sub_deal_id = 0, $company_address_id = 0, $startDate = '', $endDate = '')
    {
        if (intval($_SESSION['logged_user']['user_id']) <= 0) {
            return false;
        }
        global $msg;
        $objDeal = new DealInfo($deal_id, false);
        if ($objDeal->getError() != '') {
            $this->error = t_lang('M_ERROR_INVALID_REQUEST');
            return false;
        }
        if ($objDeal->getFldValue('deal_status') == 2) {
            $this->error = t_lang('M_TXT_SORRY_DEAL_HAS_EXPIRED');
            return false;
        }
        $currenttime = strtotime(dateForTimeZone(CONF_TIMEZONE));
        $startTime = strtotime($objDeal->getFldValue('deal_start_time'));
        if (($currenttime < $startTime) || $objDeal->getFldValue('deal_status') > 2) {
            $this->error = t_lang('M_ERROR_DEAL_IS_NOT_OPEN');
            return false;
        }
        if ($currenttime > strtotime($objDeal->getFldValue('deal_end_time'))) {
            $this->error = t_lang('M_ERROR_SORRY_DEAL_EXPIRED');
            return false;
        }
        $startDate_timestamp = strtotime($startDate);
        $endDate_timestamp = strtotime($endDate);
        if ($endDate_timestamp < $startDate_timestamp) {
            $temp = $startDate;
            $startDate = $endDate;
            $endDate = $temp;
        }
        if ($objDeal->getFldValue('deal_type') == 0 && $objDeal->getFldValue('deal_sub_type') > 1) {
            if (isset($startDate) && !empty($endDate)) {
                if (!isset($sub_deal_id)) {
                    $sub_deal_id = 0;
                } else {
                    $sub_deal_id = $sub_deal_id;
                }
                $checkbookingQtyAvailable = getOnlineDealVoucher($deal_id, $sub_deal_id, $company_address_id, $startDate, $endDate);
                if ($checkbookingQtyAvailable < 1) {
                    $this->error = (t_lang('M_TXT_ONLY') . ' ' . $checkbookingQtyAvailable . ' ' . t_lang('M_TXT_VOUCHER_AVAILABLE_FOR_HOTEL_BOOKING'));
                    return false;
                }
            }
        }
        if ($objDeal->getFldValue('deal_type') == 1 && !empty($option)) {
            if (!checkProductQuantityAvaiable($deal_id, $option)) {
                $this->error = t_lang('M_ERROR_SORRY_PRODUCT_SOLD_OUT');
                return false;
            }
        }
        $data['cart_item_deal_id'] = $deal_id;
        $data['cart_item_sub_deal_id'] = $sub_deal_id;
        $data['cart_item_user_id'] = $_SESSION['logged_user']['user_id'];
        $data['cart_item_company_address_id'] = $company_address_id;
        $data['cart_item_max_buy'] = $objDeal->getFldValue('maxBuy');
        $data['cart_item_option'] = !empty($option) ? base64_encode(serialize($option)) : '';
        if ($startDate) {
            $db_data['cibdt_start_date'] = $startDate;
            $db_data['cibdt_end_date'] = $endDate;
        }
        $data['cart_item_qty'] = 1;
        if (checkExistingCartItem($data)) {
            $this->error = t_lang('M_TXT_EXISTING_ITEM');
            return false;
        }
        if (!$cart_item_id = addUpdateCartItem($data, $error)) {
            $this->error = $error;
            return false;
        } else {
            if (isUserLogged()) {
                if (isset($_SESSION['gift'])) {
                    unset($_SESSION['gift']);
                }
                if ($for_friend) {
                    $_SESSION['gift'] = $cart_item_id;
                }
            }
            if (is_array($db_data)) {
                $db_data['cibdt_item_id'] = $cart_item_id;
                if (!addUpdateBookingDates($db_data, $error)) {
                    return $this->prepareErrorResponse($error);
                }
            }
            $this->error = $error;
            return true;
        }
        return true;
    }

    public function getProducts($array = [], $isApiCall = '')
    {
        global $msg;
        //error_reporting(E_ALL);
        //ini_set('display_errors',1);
        $user_id = $_SESSION['logged_user']['user_id'];
        $cart_data = fetchloadAllDBProducts($user_id);
        if (!is_array($cart_data)) {
            return false;
        }
        foreach ($cart_data as $key => $cart) {
            $deal = new DealInfo($cart['cart_item_deal_id']);
            if ($deal->getError() != '') {
                $this->removeItem($cart['cart_item_id']);
                continue;
            }
            $option_data = '';
            if ($deal->getFldValue('deal_type') == 1) {
                if (!$option_data = validateProductItem($cart, $error)) {
                    $data[$cart['cart_item_deal_id']]['error'] = $error;
                }
            }
            $subdealData = [];
            if ($cart['cart_item_sub_deal_id'] > 0) {
                $subdealData = $deal->fetchSubDealdata($cart['cart_item_sub_deal_id']);
                $sdeal_price = $subdealData['sdeal_original_price'] - (($subdealData['sdeal_discount_is_percentage'] == 1) ? $subdealData['sdeal_original_price'] * $subdealData['sdeal_discount'] / 100 : $subdealData['sdeal_discount']);
                $subdealname = $subdealData['sdeal_name'] . " ( " . $deal->getFldValue('deal_name' . $_SESSION['lang_fld_prefix']) . " )";
            }
            $price = ($cart['cart_item_sub_deal_id'] > 0) ? $sdeal_price : $deal->getFldValue('price');
            if ($deal->getFldValue('deal_sub_type') == 2 && $deal->getFldValue('deal_type') == 0) {
                if (isset($cart['cart_item_sub_deal_id'])) {
                    $sub_deal_id = $cart['cart_item_sub_deal_id'];
                } else {
                    $sub_deal_id = 0;
                }
                $price = getSubTotalOfOnlineDeal($deal->getFldValue('deal_id'), $sub_deal_id, $cart['cart_item_company_address_id'], $cart['cibdt_start_date'], $cart['cibdt_end_date']);
            }
            $option_price = isset($option_data['option_price']) ? $option_data['option_price'] : 0;
            $salePrice = $price + $option_price;
            $address = [];
            $maxBuy = $this->fetchMaxBuyQunatity($cart, $deal, $address);
            if ($deal->getFldValue('deal_type') == 1) {
                $maxBuy = min($cart['cart_item_max_buy'], $maxBuy);
            }
            $temp_charity = [];
            if ($deal->getFldValue('charity')) {
                $temp_charity = getCharityInfo($cart['cart_item_deal_id'], $deal->getFldValue('charity'));
                if ($isApiCall) {
                    if (empty($temp_charity) || !is_array($temp_charity))
                        $temp_charity = [];
                    else
                        $temp_charity = array($temp_charity);
                }
            }
            $taxDetails = [];
            /* $taxDetails['taxname'] = [];
              $taxDetails['taxAmount'] = []; */
            $taxDetails['taxDetail'] = [];
            $data[$cart['cart_item_id']] = array(
                'key' => $cart['cart_item_id'],
                'deal_id' => $deal->getFldValue('deal_id'),
                'subdeal_id' => isset($cart['cart_item_sub_deal_id']) ? $cart['cart_item_sub_deal_id'] : 0,
                'deal_name' => ($cart['cart_item_sub_deal_id'] > 0) ? $subdealname : $deal->getFldValue('deal_name' . $_SESSION['lang_fld_prefix']),
                'deal_type' => $deal->getFldValue('deal_type'),
                'deal_sub_type' => $deal->getFldValue('deal_sub_type'),
                'deal_is_subdeal' => $deal->getFldValue('deal_is_subdeal'),
                'maxBuy' => $maxBuy,
                'quantity' => $cart['cart_item_qty'],
                'qty' => $cart['cart_item_qty'],
                'option' => isset($option_data['data']) ? $option_data['data'] : [],
                'to_name' => isset($cart['cigdt_user_name']) ? $cart['cigdt_user_name'] : '',
                'to_email' => isset($cart['cigdt_user_email']) ? $cart['cigdt_user_email'] : '',
                'to_msg' => isset($cart['cigdt_user_message']) ? $cart['cigdt_user_message'] : '',
                'company_address_id' => isset($cart['cart_item_company_address_id']) ? $cart['cart_item_company_address_id'] : '',
                'price' => number_format(($price + $option_price), 2, '.', ''),
                'total' => number_format((($price + $option_price) * $cart['cart_item_qty']), 2, '.', ''),
                'charity' => $temp_charity,
                'tax' => !empty($this->getDealTaxDetail($cart['cart_item_deal_id'], $salePrice)) ? $this->getDealTaxDetail($cart['cart_item_deal_id'], $salePrice) : $taxDetails,
                'shipping_charges' => ($deal->getFldValue('deal_shipping_type') > 0) ? $deal->getFldValue('deal_shipping_charges_worldwide') : $deal->getFldValue('deal_shipping_charges_us'),
                'startDate' => isset($cart['cibdt_start_date']) ? $cart['cibdt_start_date'] : '',
                'startDateTimeStamp' => isset($cart['cibdt_start_date']) ? strtotime($cart['cibdt_start_date']) : '',
                'endDate' => isset($cart['cibdt_end_date']) ? $cart['cibdt_end_date'] : '',
                'endDateTimeStamp' => isset($cart['cibdt_end_date']) ? strtotime($cart['cibdt_end_date']) : '',
                'deal_image' => CONF_WEBROOT_URL . 'deal-image-crop.php?id=' . $deal->getFldValue('deal_id') . '&type=categorylist',
                'company_locations' => $address,
                'company_name' => $deal->getFldValue('company_name'),
            );
        }
        return $data;
    }

    function fetchMaxBuyQunatity($cart = [], $deal, &$address = [])
    {
        if (empty($cart)) {
            return;
        }
        $price = 0;
        $eligible_deal_data = canBuyDeal($cart['cart_item_qty'], false, $price, $cart['cart_item_deal_id'], 0, $_SESSION['logged_user']['user_id'], $cart['cart_item_sub_deal_id']);
        if ($eligible_deal_data) {
            $maxBuy = $eligible_deal_data['max_buy'][array_search(intval($cart['cart_item_company_address_id']), $eligible_deal_data['address_id'], true)];
            $eligible_company_address_for_user = $eligible_deal_data['address_id'];
            $srch = new SearchBase('tbl_company_addresses');
            $srch->addCondition('company_address_id', 'IN', $eligible_company_address_for_user);
            $srch->addMultipleFields('company_address_id', 'company_address_line1', 'company_address_line2', 'company_address_line3', 'company_address_zip');
            $rs_company_address = $srch->getResultSet();
            while ($row_address = $this->db->fetch($rs_company_address)) {
                $data['company_address_id'] = $row_address['company_address_id'];
                $data['company_address'] = $row_address['company_address_line1' . $_SESSION['lang_fld_prefix']] . ' ' . $row_address['company_address_line2' . $_SESSION['lang_fld_prefix']] . ' ' . $row_address['company_address_line3' . $_SESSION['lang_fld_prefix']] . ' ' . $row_address['company_address_zip'];
                $address[] = $data;
            }
        }
        if ($deal->getFldValue('deal_type') == 0) {
            if ($cart['cart_item_sub_deal_id'] > 0 && $deal->getFldValue('deal_sub_type') < 2) {
                $sub_voucherleft = getSubdealVoucher($cart['cart_item_sub_deal_id'], $cart['cart_item_company_address_id']);
                if ($sub_voucherleft <= 0) {
                    $this->removeItem($cart['cart_item_id']);
                    return true;
                }
                if ($maxBuy > $sub_voucherleft) {
                    $maxBuy = $sub_voucherleft;
                }
            }
            if ($deal->getFldValue('deal_sub_type') == 2) {
                $sub_voucherleft = getOnlineDealVoucher($cart['cart_item_deal_id'], $cart['cart_item_sub_deal_id'], $cart['cart_item_company_address_id'], $cart['cibdt_start_date'], $cart['cibdt_end_date']);
                if ($sub_voucherleft == 0) {
                    $this->removeItem($cart['cart_item_id']);
                    return true;
                }
                if ($maxBuy > $sub_voucherleft) {
                    $maxBuy = $sub_voucherleft;
                }
            }
        }
        return $maxBuy;
    }

    function isEmpty()
    {
        $srch = new SearchBase('tbl_cart_items', 'ct');
        $srch->addCondition('ct.cart_item_user_id', '=', $_SESSION['logged_user']['user_id']);
        $rs = $srch->getResultSet();
        if ($srch->recordCount() > 0) {
            return false;
        } else {
            return true;
        }
    }

    function removeItem($cart_item_id)
    {
        /* 	remove  from db */
        $whr = array('smt' => 'cart_item_id = ? ', 'vals' => array($cart_item_id), 'execute_mysql_functions' => false);
        $this->db->deleteRecords('tbl_cart_items', $whr);
        $whr = array('smt' => 'cibdt_item_id = ? ', 'vals' => array($cart_item_id), 'execute_mysql_functions' => false);
        $this->db->deleteRecords('tbl_cart_item_booking_dates', $whr);
        $whr = array('smt' => 'cigdt_item_id = ? ', 'vals' => array($cart_item_id), 'execute_mysql_functions' => false);
        $this->db->deleteRecords('tbl_cart_item_gift_details', $whr);
        return true;
    }

    function validateCartItems()
    {
        $user_id = $_SESSION['logged_user']['user_id'];
        if ($user_id == 0) {
            $this->error = t_lang('M_ERROR_INVALID_USERID');
            return false;
        }
        $cart_data = fetchloadAllDBProducts($user_id);
        if (empty($cart_data)) {
            $this->error = t_lang('M_TXT_CART_IS_EMPTY');
            return false;
        }
        foreach ($cart_data as $key => $deal_cart) {
            $error = "";
            if (!($this->loadValidItemData($deal_cart['cart_item_deal_id'], $deal_cart, $deal_cart['cart_item_qty'], 0, $error))) {
                $this->removeItem($deal_cart['cart_item_id']);
                $cart_data[$key]['error'] = $error;
                $this->error = $error;
                return false;
            }
        }
        return true;
        if (sizeof($cart_data) < 1) {
            $this->clearCart();
            $this->error = t_lang('M_TXT_CART_IS_EMPTY');
            return false;
        }
        //  $this->checkDiscountValidity();
        return true;
    }

    function clearDealErrorMsg($key)
    {
        if (isset($_SESSION['fat_cart'][$key]['error'])) {
            unset($_SESSION['fat_cart'][$key]['error']);
            return true;
        }
        return false;
    }

    function getCartItem($key)
    {
        if ($key < 1)
            return false;
        $cart = $this->getProducts();
        return (isset($cart[$key]) ? $cart[$key] : false);
    }

    function updateDealGiftDetails(&$data)
    {
        $deal_id = intval($data['deal_id']);
        $key = $data['key'];
        if ($key < 1 || $key == '') {
            return false;
        }
        $conditions = array('cigdt_item_id' => $data['key']);
        $row = getRecords('tbl_cart_item_gift_details', $conditions, 'first');
        $cigdt['cigdt_item_id'] = $data['key'];
        $cigdt['cigdt_user_name'] = htmlentities($data['to_name']);
        $cigdt['cigdt_user_email'] = $data['to_email'];
        $cigdt['cigdt_user_message'] = $data['to_msg'];
        $record = new TableRecord('tbl_cart_item_gift_details');
        $record->assignValues($cigdt);
        if (!$row) {
            if (!$record->addNew()) {
                return false;
            }
            return true;
        } else {
            $whr = array('smt' => 'cigdt_item_id = ?', 'vals' => array($data['key']), 'execute_mysql_functions' => false);
            if (!$record->update($whr)) {
                return false;
            }
            return true;
        }
    }

    function clearGiftDetails($key)
    {
        if ($key < 1 || $key == '') {
            return false;
        }
        global $db;
        $whr = array('smt' => 'cigdt_item_id = ?', 'vals' => array($key), 'execute_mysql_functions' => false);
        $db->deleteRecords('tbl_cart_item_gift_details', $whr, array('IGNORE'));
        return true;
    }

    function updateQuantity($key, $new_qty, &$error)
    {
        if ($key < 1 || $new_qty < 1 || $key == '') {
            $error = t_lang('M_ERROR_INVALID_REQUEST');
            return false;
        }
        $chqqty = 0;
        $dataArray = fetchloadAllDBProducts($_SESSION['logged_user']['user_id']);
        $key_array = array_column($dataArray, 'cart_item_deal_id', 'cart_item_id');
        $deal_id = $key_array[$key];
        if ($deal_id <= 0) {
            $error = t_lang('M_ERROR_DEAL_ID_NOT_FOUND');
            return false;
        }
        $chqqty = $new_qty;
        if (!empty($dataArray)) {
            foreach ($dataArray as $key1 => $deal_cart) {
                if ($deal_cart['cart_item_id'] == $key) {
                    $cart_data = $deal_cart;
                    continue;
                }
                if ($deal_cart['cart_item_deal_id'] == $deal_id) {
                    $chqqty = $chqqty + $deal_cart['cart_item_qty'];
                }
            }
        }
        $error1 = "";
        if (!($this->loadValidItemData($deal_id, $cart_data, $new_qty, $chqqty, $error1))) {
            $error = $error1;
            return false;
        }
        return true;
    }

    function loadValidItemData($deal_id = 0, $deal_cart = [], $new_qty = 0, $chqQty = 0, &$error)
    {
        global $msg;
        // @newQuantity= updated Qty
        // @chqQty= new quantity of selected product + existing qty of another same product in cart
        if (empty($deal_cart)) {
            return false;
        }
        $subdeals = "";
        if (isset($deal_cart['cart_item_option'])) {
            $deal_options = unserialize(base64_decode($deal_cart['cart_item_option']));
            if (!empty($deal_options)) {
                $productAttributeAvailable = checkProductQuantityAvaiable($deal_id, $deal_options);
                if (!$productAttributeAvailable) {
                    return false;
                }
            }
        }
        $subdeal_id = $deal_cart['cart_item_sub_deal_id'];
        if (isset($deal_cart['cibdt_start_date']) && !empty($deal_cart['cibdt_end_date'])) {
            $checkbookingQtyAvailable = getOnlineDealVoucher($deal_id, $subdeal_id, $deal_cart['cart_item_company_address_id'], $deal_cart['cibdt_start_date'], $deal_cart['cibdt_end_date']);
            if ($checkbookingQtyAvailable < ($deal_cart['cart_item_qty'])) {
                $error = t_lang('M_TXT_ONLY') . ' ' . $checkbookingQtyAvailable . ' ' . t_lang('M_TXT_VOUCHER_AVAILABLE_FOR_HOTEL_BOOKING');
                return false;
            }
        }
        if ($deal_id < 1) {
            $error = "Invalid deal Id";
            return false;
        }
        $for_single_address = false;
        if (isset($deal_cart['cart_item_company_address_id'])) {
            $for_single_address = true;
        }
        $price = 0;
        $eligible_deal_data = "";
        $eligible_company_address_for_user = "";
        $prevQty = $deal_cart['cart_item_qty'];
        if (intval($new_qty) > 0)
            $deal_cart['cart_item_qty'] = intval($new_qty);
        if ($deal_cart['cart_item_qty'] < 1) {
            $deal_cart['cart_item_qty'] = 1;
        }
        $error = "";
        if ($chqQty > 0) {
            $eligible_deal_data = canBuyDeal($chqQty, $for_single_address, $price, $deal_id, $deal_cart['cart_item_company_address_id'], 0, $subdeal_id, $error);
        } else {
            $eligible_deal_data = canBuyDeal($deal_cart['cart_item_qty'], $for_single_address, $price, $deal_id, $deal_cart['cart_item_company_address_id'], 0, $subdeal_id, $error);
        }
        if (!$eligible_deal_data) {
            $error = $error;
            return false;
        }
        $eligible_company_address_for_user = $eligible_deal_data['address_id'];
        if ($eligible_deal_data === false || sizeof($eligible_company_address_for_user) <= 0) {
            if ($chqQty > 0) {
                if (isset($deal_cart['cart_item_max_buy']) && $deal_cart['cart_item_max_buy'] > 0 && $deal_cart['cart_item_max_buy'] < $chqQty) {
                    //	$error = $msg->display();
                    $error = $error;
                    $deal_cart['cart_item_qty'] = $prevQty;
                    return false;
                }
            }
            if (isset($deal_cart['cart_item_max_buy']) && $deal_cart['cart_item_max_buy'] > 0 && $deal_cart['cart_item_max_buy'] < $deal_cart['cart_item_qty']) {
                $error = $msg->display();
                $deal_cart['cart_item_qty'] = $deal_cart['cart_item_max_buy'];
            } else {
                $this->removeItem($deal_cart['cart_item_id']);
            }
            return false;
        } else {
            unset($error);
        }
        if (!isset($deal_cart['cart_item_company_address_id']) || intval($deal_cart['cart_item_company_address_id']) <= 0) {
            foreach ($eligible_company_address_for_user as $company_address_for_user) {
                $deal_cart['cart_item_company_address_id'] = $company_address_for_user;
                break;
            }
        }
        if (isset($eligible_deal_data)) {
            $deal_cart['cart_item_max_buy'] = $eligible_deal_data['max_buy'][array_search(intval($deal_cart['cart_item_company_address_id']), $eligible_deal_data['address_id'], true)];
        }
        if (is_array($deal_cart)) {
            if (!empty($deal_options)) {
                $deal_cart['cart_item_max_buy'] = min($deal_cart['cart_item_max_buy'], $productAttributeAvailable);
            }
            $deal_cart = filter_array($deal_cart);
            $error = "";
            if (!addUpdateCartItem($deal_cart, $error)) {
                $error = $error;
                return false;
            }
            return true;
        }
        return true;
    }

    function getItemCount()
    {
        $cart = $this->getCart();
        if (!$cart) {
            return intval(0);
        }
        if (!is_array($cart)) {
            return intval(0);
        }
        return sizeof($cart);
    }

    function validateShippingCharges()
    {
        $cart_deal_ids = array_column($this->getCart(), 'cart_item_deal_id');
        if (!is_array($cart_deal_ids) || count($cart_deal_ids) <= 0) {
            return false;
        }
        $products_in_cart = getTotalProductsInCart($cart_deal_ids);
        if (sizeof($cart_deal_ids) < 1)
            return false;
        if ($products_in_cart <= 0)
            return true;
        /* if(isset($_SESSION['fat_cart_pickup_loc_id']) && intval($_SESSION['fat_cart_pickup_loc_id']) > 0 && $this->getShippingCharges() == 0) return true; */
        if ($this->getShippingCharges() >= 0 /* && !isset($_SESSION['fat_cart_pickup_loc_id']) */)
            return true;
        return false;
    }

    /* $cid = country_id in function updateShippingCharges($cid, &$error) */

    function updateShippingCharges($cid, &$error)
    {
        $data = fetchloadAllDBProducts(intval($_SESSION['logged_user']['user_id']));
        if (!isset($data) || sizeof($data) < 0) {
            $error = t_lang('M_TXT_CART_IS_EMPTY');
            return false;
        }
        /* Get Country Data Starts */
        $co = new SearchBase('tbl_countries', 'co');
        $co->addCondition('co.country_id', '=', $cid);
        $rs = $co->getResultSet();
        if (!$country_data = $this->db->fetch($rs)) {
            $error = 'Country Record not found!';
            return false;
        }
        $country_name = $country_data['country_name'];
        /* Get Country Data Ends */
        $cart_deal_ids = [];
        $cart_deal_ids = array_column($this->getProducts(), 'deal_id', 'key');
        if (!is_array($cart_deal_ids) || count($cart_deal_ids) <= 0) {
            $error = t_lang('M_TXT_CART_IS_EMPTY');
            return false;
        }
        $cart_deal_ids = array_unique($cart_deal_ids);
        $shipping_charges = 0;
        $usa_selected = false;
        $i = 1;
        foreach ($cart_deal_ids as $key => $deal_id) {
            if ($deal_id == "") {
                continue;
            }
            $srch_deal = new SearchBase('tbl_deals', 'd');
            $srch_deal->addMultipleFields(array('d.deal_id', 'd.deal_name', 'd.deal_type', 'd.deal_sub_type', 'd.deal_shipping_type', 'd.deal_shipping_charges_us', 'd.deal_shipping_charges_worldwide'));
            $srch_deal->addCondition('d.deal_id', '=', intval($deal_id));
            $srch_deal->doNotCalculateRecords();
            $srch_deal->doNotLimitRecords();
            $rs = $srch_deal->getResultSet();
            if ($this->db->total_records($rs) > 0) {
                $row = $this->db->fetch($rs);
                if ($row['deal_type'] == 1 && $row['deal_sub_type'] == 0) { //add shipping charges only in case of Products
                    /* Add Shipping Charges Within US if Country is United States */
                    if ((strtoupper($country_name) == 'UNITED STATES' || strtoupper($country_name) == 'UNITED STATES OF AMERICA' || strtoupper($country_name) == 'USA' || strtoupper($country_name) == 'UNITED STATE OF AMERICA')) {
                        $usa_selected = true;
                        if ($row['deal_shipping_type'] == 0 && $row['deal_sub_type'] == 0) {
                            $shipping_charges += $row['deal_shipping_charges_us'];
                        } else {
                            $shipping_charges += $row['deal_shipping_charges_worldwide'];
                        }
                    } else { /* Add Shipping Charges if Country is not United States & deal is selected as worldwide */
                        $shipping_charges += $row['deal_shipping_charges_worldwide'];
                    }
                    /* Check that if deal is only shipable to usa and user selected country other than usa, then generate error, script starts */
                    if ($row['deal_shipping_type'] == 0 && $row['deal_sub_type'] == 0 && !$usa_selected) {
                        $error = 'Product "' . $row['deal_name'] . '" can not be shipped outside USA!';
                        return false;
                    }
                    /* Check that if deal is only shipable to usa and user selected country other than usa, then generate error, script ends */
                }
            } else {
                $this->removeItem($key);
            }
        }
        // $this->fat_cart_shipping_charges = round(floatval($shipping_charges), 2);
        return true;
    }

    function processOrder($order_payment_mode, $charge_from_wallet = 0, $mark_paid = false)
    {
        global $db;
        $order = new userOrder();
        $cart_data = $this->getProducts();
        foreach ($cart_data as $key => $deal_cart) {
            $order->addDeal($deal_cart);
        }
        if ($this->getShippingCharges() > 0) {
            $order->setOrderShipCharges($this->getShippingCharges());
        }
        $order->setFldValue('order_payment_mode', $order_payment_mode);
        $order->setFldValue('order_amount', $this->getCartTotal(true));
        if (!$order->addNew() || !($orderId = $order->getOrderId())) {
            $this->error = $order->getError();
            return false;
        }
        if ($charge_from_wallet > 0) { /* In case of partial payment from wallet. */
            $this->db->query("update tbl_orders set order_charge_from_wallet =" . round($charge_from_wallet, 2) . " where order_id='" . $orderId . "'");
        }
        $authStatus = false;
        $dealIdArray = [];
        if ($order_payment_mode == 4) {
            foreach ($cart_data as $dealdate => $value) {
                $dealIdArray[] = intval($value['deal_id']);
            }
            /* CODE FOR AUTHORIZED.NET START HERE */
            $srch = new SearchBase('tbl_deals', 'd');
            $srch->addCondition('deal_id', 'IN', $dealIdArray);
            $srch->addFld('deal_instant_deal');
            $rs = $srch->getResultSet();
            $row_deal = $db->fetch_all($rs);
            foreach ($row_deal as $keys => $values) {
                if ($values['deal_instant_deal'] == 1) {
                    $authStatus = true;
                }
            }
        }
        if ($mark_paid && !$order->markOrderPaid($orderId, $authStatus)) {
            $this->error = $order->getError();
            return false;
        }
        return $orderId;
    }

    function checkDiscountValidity()
    {
        if (!$dd = $this->getDiscountDetail()) {
            return true;
        }
        $min_cart_value_required = $dd['min_cart_value_required'];
        $cart_total = $this->getCartTotal();
        if ($cart_total < $min_cart_value_required) {
            $this->removeDiscount();
            return false;
        }
        return true;
    }

    function getDiscountDetail()
    {
        if (isset($_SESSION['fat_cart_discount']))
            return $_SESSION['fat_cart_discount'];
        else
            return false;
    }

    function getCartTotal($apply_discount = false)
    {
        $cart = $this->getProducts();
        if (!is_array($cart) || sizeof($cart) < 1) {
            return 0;
        }
        $total = 0;
        foreach ($cart as $deal_id => $item) {
            if (isset($item['price']) && $item['price'] > 0 && isset($item['qty']) && $item['qty'] > 0) {
                $total += ($item['price'] * $item['qty']);
            }
        }
        /*  if ($apply_discount) {
          $this->checkDiscountValidity();
          $discount = $this->getDiscountValue();
          $total = $total - $discount;
          } */
        $shipping_charges = $this->getShippingCharges();
        $total += $shipping_charges;
        $total += $this->getTaxAmount();
        return (round($total, 2));
    }

    function getDiscountValue()
    {
        return $this->calculateDiscount();
    }

    function calculateDiscount()
    {
        if (!$data = $this->getDiscountDetail()) {
            return 0;
        }
        $discount = $data['value'];
        $cart_total = $this->getCartTotal();
        if ($data['is_percentage'] == 1) {
            $discount = $cart_total * $discount / 100;
        }
        return $discount;
    }

    function getCart()
    {
        if (!$_SESSION['logged_user']['user_id']) {
            return false;
        }
        $srch = new SearchBase('tbl_cart_items', 'ct');
        $srch->addCondition('ct.cart_item_user_id', '=', $_SESSION['logged_user']['user_id']);
        $rs = $srch->getResultSet();
        if ($srch->recordCount() > 0) {
            return $this->db->fetch_all($rs);
        } else {
            return false;
        }
    }

    function getShippingCharges()
    {
        $cart_deal_ids = [];
        $cart_deal_ids = array_column($this->getProducts(), 'deal_id', 'key');
        if (!is_array($cart_deal_ids) || count($cart_deal_ids) <= 0) {
            $error = 'Cart is empty!!';
            return false;
        }
        $cart_deal_ids = array_unique($cart_deal_ids);
        $shipping_charges = 0;
        $i = 1;
        foreach ($cart_deal_ids as $key => $deal_id) {
            if ($deal_id == "") {
                continue;
            }
            $srch_deal = new SearchBase('tbl_deals', 'd');
            $srch_deal->addMultipleFields(array('d.deal_id', 'd.deal_name', 'd.deal_type', 'd.deal_sub_type', 'd.deal_shipping_type', 'd.deal_shipping_charges_us', 'd.deal_shipping_charges_worldwide'));
            $srch_deal->addCondition('d.deal_id', '=', intval($deal_id));
            $srch_deal->doNotCalculateRecords();
            $srch_deal->doNotLimitRecords();
            $rs = $srch_deal->getResultSet();
            if ($this->db->total_records($rs) > 0) {
                $row = $this->db->fetch($rs);
                if ($row['deal_type'] == 1 && $row['deal_sub_type'] == 0) { //add shipping charges only in case of Products
                    if ($row['deal_shipping_type'] == 0 && $row['deal_sub_type'] == 0) {
                        $shipping_charges += $row['deal_shipping_charges_us'];
                    } else {
                        $shipping_charges += $row['deal_shipping_charges_worldwide'];
                    }
                }
            } else {
                $this->removeItem($key);
            }
        }
        return round(floatval($shipping_charges), 2);
    }

    function clearCart()
    {
        $data = $this->getCart();
        foreach ($data as $key => $value) {
            $this->removeItem($value['cart_item_id']);
        }
        return true;
    }

    function getError()
    {
        return $this->error;
    }

    function getDealTaxDetail($dealId, $salePrice)
    {
        require_once 'includes/tax-functions.php';
        global $db;
        $row_deal = getDealInfo($dealId);
        $srch1 = new SearchBase('tbl_tax_rules', 'tr');
        $srch1->joinTable('tbl_tax_classes', 'INNER JOIN', 'tc.taxclass_id=tr.taxrule_taxclass_id', 'tc');
        $srch1->joinTable('tbl_tax_rates', 'LEFT JOIN', 'trate.taxrate_id=tr.taxrule_taxrate_id', 'trate');
        $srch1->joinTable('tbl_geo_zone_location', 'LEFT JOIN', 'gzl.zoneloc_geozone_id=trate.taxrate_geozone_id', 'gzl');
        $srch1->joinTable('tbl_tax_geo_zones', 'INNER JOIN', 'trate.taxrate_geozone_id=tgz.geozone_id', 'tgz');
        $srch1->addCondition('taxclass_active', '=', 1);
        $srch1->addCondition('tr.taxrule_taxclass_id', '=', $row_deal['deal_taxclass_id']);
        $srch1->addMultipleFields(array('gzl.zoneloc_country_id', 'trate.*', 'tr.*', 'tc.taxclass_name', 'tgz.geozone_name', "GROUP_CONCAT(distinct(gzl.zoneloc_state_id)SEPARATOR ',') as state_id"));
        $srch1->addOrder('taxrate_name');
        $srch1->addGroupBy('taxrate_id');
        $rs_listing = $srch1->getResultSet();
        if ($srch1->recordCount() == 0) {
            return false;
        }
        /*         * **
          1 => PRODUCT PRICE(EXCLUDING COMMISSION) -> Deduct the commission amount and then calculate deal price
          2 => PRODUCT PRICE(INCLUDING COMMISSION) -> Let go Sale price as it is
          3 => PRODUCT AND SHIPPING PRICE -> Product Price + Shipping Charges
         * */
        if (1 == CONF_TAX_APPLICABLE_ON) {
            $salePrice = $salePrice - ($row_deal['deal_commission_percent'] / 100 * $salePrice);
        } else if (3 == CONF_TAX_APPLICABLE_ON) {
            $shipping_charges = ($row_deal['deal_shipping_type'] > 0) ? $row_deal['deal_shipping_charges_worldwide'] : $row_deal['deal_shipping_charges_us'];
            $salePrice = ($salePrice + $shipping_charges);
        }
        $arrayBasedOn = array("1" => "Store Address", "2" => "Billing Address", "3" => "Shipping Address");
        $state_id = "";
        $str = "";
        $amount = 0;
        $taxInfo = [];
        $count = 0;
        while ($row = $db->fetch($rs_listing)) {
            //print_r($row);exit;
            $taxInfo[$count]['taxrate_name'] = $row['taxrate_name'];
            $taxInfo[$count]['taxclass_name'] = $row['taxclass_name'];
            $taxInfo[$count]['geozone_name'] = $row['geozone_name'];
            $taxInfo[$count]['taxrate'] = $row['taxrate_tax_rate'];
            $taxrate_state_ids = explode(',', $row['state_id']);
            switch ($row['taxrule_tax_based_on']) {
                case 1:
                    $storeAddress = fetchStoreAddress($row_deal['deal_company']);
                    $country_ids = explode(',', $storeAddress['country_id']);
                    if (in_array($row['zoneloc_country_id'], $country_ids)) {
                        $user_state_ids = explode(',', $storeAddress['state_id']);
                        $common_state = array_intersect($user_state_ids, $taxrate_state_ids);
                        //print_r($common_state);
                        if ($common_state) {
                            $amount += $row['taxrate_tax_rate'] * ($salePrice / 100);
                            $str .= "- " . $row['taxrate_name'] . " : " . $row['taxrate_tax_rate'] * ($salePrice / 100) . "<br/>";
                        }
                    }
                    break;
                case 2:
                    $billingAddress = fetchBillingAddress();
                    $country_ids = explode(',', $billingAddress['country_id']);
                    if (in_array($row['zoneloc_country_id'], $country_ids)) {
                        $user_state_ids = explode(',', $billingAddress['state_id']);
                        $common_state = array_intersect($user_state_ids, $taxrate_state_ids);
                        //print_r($common_state);
                        if ($common_state) {
                            $amount += $row['taxrate_tax_rate'] * ($salePrice / 100);
                            $str .= "- " . $row['taxrate_name'] . " : " . $row['taxrate_tax_rate'] * ($salePrice / 100) . "<br/>";
                        }
                    }
                    break;
                case 3:
                    if ($row_deal['deal_type'] == 1 && $row_deal['deal_sub_type'] == 0) {
                        $shippingAddress = fetchShippingAddress();
                        $country_ids = explode(',', $shippingAddress['country_id']);
                        if (in_array($row['zoneloc_country_id'], $country_ids)) {
                            $user_state_ids = explode(',', $shippingAddress['state_id']);
                            $common_state = array_intersect($user_state_ids, $taxrate_state_ids);
                            //print_r($common_state);
                            if ($common_state) {
                                $amount += $row['taxrate_tax_rate'] * ($salePrice / 100);
                                $str .= "- " . $row['taxrate_name'] . " : " . $row['taxrate_tax_rate'] * ($salePrice / 100) . "<br/>";
                            }
                        }
                    }
                    // print_r($row['zoneloc_country_id']);
                    break;
            }
            //die($salePrice.'>>>>>'.$amount);
            $count++;
        }
        if ($amount <= 0) {
            $str = "";
        }
        $array = [];
        $array['taxname'] = $str;
        $array['taxAmount'] = $amount;
        $array['taxDetail'] = $taxInfo;
        return $array;
    }

    function getTaxAmount()
    {
        $cart = $this->getProducts();
        if (!is_array($cart) || sizeof($cart) < 1) {
            return 0;
        }
        $total = 0;
        foreach ($cart as $deal_id => $item) {
            if (isset($item['tax']) && isset($item['qty']) && $item['qty'] > 0) {
                $total += ($item['tax']['taxAmount'] * $item['qty']);
            }
        }
        return (round($total, 2));
    }

    function validateCartItemsForAPI()
    {
        $user_id = $_SESSION['logged_user']['user_id'];
        if ($user_id == 0) {
            $this->error = t_lang('M_ERROR_INVALID_USERID');
            return false;
        }
        $cart_data = fetchloadAllDBProducts($user_id);
        if (empty($cart_data)) {
            $this->error = array('status' => 1, 'msg' => t_lang('M_TXT_CART_IS_EMPTY'));
            return false;
        }
        foreach ($cart_data as $key => $deal_cart) {
            $error = "";
            if (!($this->loadValidItemData($deal_cart['cart_item_deal_id'], $deal_cart, $deal_cart['cart_item_qty'], 0, $error))) {
                $this->removeItem($deal_cart['cart_item_id']);
                $cart_data[$key]['error'] = $error;
                $this->error = $error;
                return false;
            }
        }
        return true;
        if (sizeof($cart_data) < 1) {
            $this->clearCart();
            $this->error = t_lang('M_TXT_CART_IS_EMPTY');
            return false;
        }
        //  $this->checkDiscountValidity();
        return true;
    }

}
