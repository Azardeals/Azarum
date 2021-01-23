<?php

require_once 'application-top.php';
require_once CONF_INSTALLATION_PATH . 'includes/navigation-functions.php';
require_once CONF_INSTALLATION_PATH . "qrcode/qrlib.php";

class userOrder extends TableRecord
{
    private $orderId;
    private $deals;
    private $discounts;
    private $pick_up_location;
    private $shipping_charges;

    public function __construct()
    {
        if (!parent::__construct('tbl_orders')) {
            return false;
        }
        global $db;
        $this->deals = [];
        $this->db = $db;
    }

    public function addDeal($cart = [])
    {
        if (intval($cart['deal_id']) <= 0) {
            $this->error = t_lang('M_ERROR_INVALID_DEAL_ID');
            return false;
        }
        $this->deals[$cart['key']] = $cart;
    }

    public function addDiscount($discount)
    {
        $this->discounts[$discount['coupon_code']] = $discount;
    }

    public static function getRandomCode($n)
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = '';
        for ($i = 0; $i < $n; $i++) {
            $pass .= substr($chars, rand(0, strlen($chars) - 1), 1);
        }
        return $pass;
    }

    public function saveDiscounts()
    {
        if (strlen($this->orderId) < 1) {
            return false;
        }
        if (sizeof($this->discounts) < 1) {
            return true;
        }
        $values = [];
        foreach ($this->discounts as $c_code => $discount) {
            $values[] = '("' . mysql_real_escape_string($this->orderId) . '","' . mysql_real_escape_string($c_code) . '",' . round($discount['value'], 2) . ')';
        }
        if (sizeof($values) > 0) {
            $values = implode(',', $values);
            if (strlen($values) > 10 && !$this->db->query('INSERT INTO `tbl_order_discounts` (`odisc_order_id`,`odisc_coupon_code`,`odisc_discount_value`) VALUES ' . $values . ';')) {
                $this->error = $this->db->getError();
                return false;
            }
        }
        return true;
    }

    public function setPickupLocation($loc_id)
    {
        $loc_id = intval($loc_id);
        if ($loc_id < 1) {
            return false;
        }
        $this->pick_up_location = $loc_id;
    }

    public function setOrderShipCharges($amount)
    {
        $amount = floatval($amount);
        if ($amount < 1) {
            return false;
        }
        $this->shipping_charges = round($amount, 2);
    }

    public function saveShippingDetails()
    {
        if (strlen($this->orderId) < 1) {
            return false;
        }
        $srch = new SearchBase('tbl_user_addresses', 'addr');
        $srch->addMultipleFields(array('addr.uaddr_name', 'addr.uaddr_address_line1', 'addr.uaddr_address_line2', 'addr.uaddr_country_id as country_id', 'addr.uaddr_state_id as state_id', 'addr.uaddr_city_id as city_id', 'addr.uaddr_city_name', 'addr.uaddr_zip_code'));
        $srch->addCondition('addr.uaddr_user_id', '=', intval($_SESSION['logged_user']['user_id']));
        $srch->addCondition('addr.uaddr_is_active', '=', 1);
        $srch->addCondition('addr.uaddr_type', '=', 2);
        $srch->addCondition('addr.uaddr_is_dafault', '=', 1);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        if (!$row = $this->db->fetch($rs)) {
            $this->error = 'Shipping address not updated!!';
            return false;
        }
        $values = array(
            'osd_order_id' => $this->orderId,
            'osd_recipient_name' => $row['uaddr_name'],
            'osd_address_line1' => $row['uaddr_address_line1'],
            'osd_address_line2' => $row['uaddr_address_line2'],
            'osd_country_id' => $row['country_id'],
            'osd_state_id' => $row['state_id'],
            'osd_city_id' => $row['city_id'],
            'osd_city_name' => $row['uaddr_city_name'],
            'osd_zip_code' => $row['uaddr_zip_code']
        );
        if (!$this->db->insert_from_array('tbl_order_shipping_details', $values)) {
            $this->error = $this->db->getError();
            return false;
        }
        return true;
    }

    public function addNew($x = '', $y = '')
    {
        if (!($this->getFldValue('order_payment_mode') > 0 && $this->getFldValue('order_payment_mode') <= 4 && is_numeric($this->getFldValue('order_payment_mode')))) {
            $this->error = t_lang('M_ERROR_INVALID_PAYMENT_MODE');
            return false;
        }
        if (count($this->deals) == 0) {
            $this->error = t_lang('M_ERROR_NO_DEAL_ADDED');
            return false;
        }
        if (!is_numeric($this->getFldValue('order_user_id'))) {
            $this->setFldValue('order_user_id', intval($_SESSION['logged_user']['user_id']));
        }
        $id = time() . rand(10, 99);
        $rs = $this->db->query("select count(*) as total from tbl_orders where order_id='G" . $id . "'");
        $row = $this->db->fetch($rs);
        while ($row['total'] > 0) {
            $id++;
            $rs = $this->db->query("select count(*) as total from tbl_orders where order_id='G" . $id . "'");
            $row = $this->db->fetch($rs);
        }
        $this->orderId = 'G' . $id;
        $this->setFldValue('order_id', $this->orderId);
        $orderId = $this->orderId;
        if (!isset($this->flds['order_date'])) {
            $this->setFldValue('order_date', date('Y-m-d H:i:s'), false);
        }
        $this->setFldValue('order_shipping_charges', $this->shipping_charges);
        /* Set charity in order */
        if (($_SESSION['logged_user']['user_referral_id'] > 0) && $_SESSION['logged_user']['user_affiliate_id'] == 0) {
            $rs_first_order = $this->db->query("select count(*) as total from tbl_orders where order_payment_status = 1 and order_user_id = " . intval($_SESSION['logged_user']['user_id']));
            $row_first_order = $this->db->fetch($rs_first_order);
            if ($row_first_order['total'] == 0) { // This is not first successive order.
                /* We dont care if already registered user is giving referrer commission to someone for first order.
                  Also we are ignoring if first order of referred user is not tipped. */
                /* $this->setFldValue('order_referrer_id', $_COOKIE['refid']+0); */
                $this->setFldValue('order_referrer_id', $_SESSION['logged_user']['user_referral_id']);
            }
        }
        if (!parent::addNew()) {
            return false;
        }
        $all_cart_deal_ids = array_keys($this->deals);
        $cart_keys = array_keys($this->deals);
        if (!is_array($cart_keys) || count($cart_keys) <= 0) {
            return false;
        }
        $all_cart_deal_ids = [];
        $all_cart_deal_ids = array_column($this->deals, 'deal_id', 'key');
        if (!is_array($all_cart_deal_ids) || count($all_cart_deal_ids) <= 0) {
            $error = 'Cart is empty!!';
            return false;
        }
        $all_cart_deal_ids = array_unique($all_cart_deal_ids);
        //if(getTotalProductsInCart($all_cart_deal_ids) > 0 && !$this->pick_up_location && !$this->saveShippingDetails()) return false;
        if (getTotalProductsInCart($all_cart_deal_ids) > 0 && !$this->saveShippingDetails()) {
            return false;
        }
        foreach ($this->deals as $key => $arr) {
            $deal_id = $arr['deal_id'];
            if ($_SESSION['lang_fld_prefix'] == '_lang1') {
                $fld = ' deal_name_lang1 as order_deal_name';
            } else {
                $fld = 'deal_name as order_deal_name';
            }
            $rs = $this->db->query("select d.*, " . $fld . " from tbl_deals d where deal_id=" . intval($deal_id));
            if (!$row = $this->db->fetch($rs)) {
                $this->error = t_lang('M_ERROR_INVALID_DEAL_ID');
            }
            $price = $arr['price'];
            $tax = $arr['tax']['taxAmount'];
            $taxOption = $arr['tax']['taxname'];
            if (isset($arr['to_email']) && filter_var($arr['to_email'], FILTER_VALIDATE_EMAIL)) {
                $arr['gift_qty'] = $arr['qty'];
                $arr['qty'] = 0;
            }
            if ($arr['qty'] > 0) {
                if ($arr['qty'] < 4444) {
                    $arr_self = [];
                    while ($arr['qty'] > count($arr_self)) {
                        $rand = rand(1111, 5555);
                        if (in_array($rand, $arr_self)) {
                            continue;
                        }
                        $arr_self[] = $rand;
                    }
                    $od_voucher_suffixes_qty = implode(', ', $arr_self);
                }
            } else {
                $od_voucher_suffixes_qty = '';
            }
            if ($arr['gift_qty'] > 0) {
                if ($arr['qty'] < 4443) {
                    $arr_self_gift = [];
                    while ($arr['gift_qty'] > count($arr_self_gift)) {
                        $rand = rand(5556, 9999);
                        if (in_array($rand, $arr_self_gift)) {
                            continue;
                        }
                        $arr_self_gift[] = $rand;
                    }
                    $od_voucher_suffixes_gift = implode(', ', $arr_self_gift);
                }
            } else {
                $od_voucher_suffixes_gift = '';
            }

            $codeToMarkDealAsUsed = self::getRandomCode(6);

            $arr_od = array(
                'od_order_id' => $this->orderId,
                'od_deal_id' => $deal_id,
                'od_deal_name' => $row['order_deal_name'],
                'od_deal_price' => $price,
                'od_deal_tax_amount' => $tax,
                'od_deal_tax_option' => $taxOption,
                'od_company_address_id' => $arr['company_address_id'],
                'od_voucher_suffixes' => $od_voucher_suffixes_qty,
                'od_qty' => $arr['qty'],
                'od_deal_charity_id' => $arr['charity']['charity_id'],
                'od_mark_as_used_code' => $codeToMarkDealAsUsed,
                    //	'od_deal_charity_id' => $cartDetail['cart_item_charity_id']
            );
            if (isset($arr['subdeal_id']) && $arr['subdeal_id'] > 0) {
                $arr_od['od_subdeal_id'] = $arr['subdeal_id'];
                $condition['sdeal_id'] = $arr['subdeal_id'];
                $subdealData = getRecords('tbl_sub_deals', $condition, 'first');
                $arr_od['od_sub_deal_name'] = $subdealData['sdeal_name'];
            }
            if ($arr['qty'] > 0) {
                if (!$this->db->insert_from_array('tbl_order_deals', $arr_od)) {
                    $this->error = $this->db->getError();
                    return false;
                }
                $order_deal_id = $this->db->insert_id();
                /* Save Selected Deal's/Product's Options in table "tbl_order_option", script starts here */
                if (isset($arr['option']) && is_array($arr['option']) && count($arr['option'])) {
                    foreach ($arr['option'] as $option) {
                        $arr_od_option = array(
                            //   'oo_order_id' => $this->orderId,
                            'oo_od_id' => $order_deal_id,
                            'oo_deal_option_id' => $option['deal_option_id'],
                            'oo_deal_option_value_id' => $option['option_value_id'],
                            'oo_option_name' => $option['option_name'],
                            'oo_option_value' => $option['option_value'],
                            'oo_option_type' => $option['option_type'],
                            'oo_price' => $option['price'],
                            'oo_price_prefix' => $option['price_prefix'],
                        );
                        if (!$this->db->insert_from_array('tbl_order_option', $arr_od_option)) {
                            $this->error = $this->db->getError();
                            return false;
                        }
                    }
                }
                /* Save Selected Deal's/Product's Options in table "tbl_order_option", script ends here */
                /* Save Selected booking's Options in table "tbl_order_bookings", script starts here */
                if (($arr['deal_sub_type'] == 2) && ($arr['startDate'] != "") && ($arr['endDate'] != "")) {
                    $endDate = date('Y-m-d', strtotime($arr['endDate'] . ' -1 day'));
                    $arr_obooking_array = array(
                        //   'oo_order_id' => $this->orderId,
                        'obooking_od_id' => $order_deal_id,
                        'obooking_voucher_code' => $od_voucher_suffixes_qty,
                        'obooking_booking_from' => $arr['startDate'],
                        'obooking_booking_till' => $endDate
                    );
                    if (!$this->db->insert_from_array('tbl_order_bookings', $arr_obooking_array)) {
                        $this->error = $this->db->getError();
                        return false;
                    }
                }
                /* Save Selected tax's Options in table "tbl_order_deal_taxes", script ends here */
            }
            if ($arr['gift_qty'] > 0) {
                $arr_od_gift = array(
                    'od_order_id' => $this->orderId,
                    'od_deal_id' => $deal_id,
                    'od_deal_name' => $row['order_deal_name'],
                    'od_deal_price' => $price,
                    'od_deal_tax_amount' => $tax,
                    'od_deal_tax_option' => $taxOption,
                    'od_gift_qty' => $arr['gift_qty'],
                    'od_to_name' => $arr['to_name'],
                    'od_to_email' => $arr['to_email'],
                    'od_company_address_id' => $arr['company_address_id'],
                    'od_voucher_suffixes' => $od_voucher_suffixes_gift,
                    'od_email_msg' => $arr['to_msg'],
                    //'od_deal_charity_id' => $arr['charity']['charity_id'],
                    'od_deal_charity_id' => $cartDetail['cart_item_charity_id']
                );
                if (isset($arr['subdeal_id']) && $arr['subdeal_id'] > 0) {
                    $arr_od_gift['od_subdeal_id'] = $arr['subdeal_id'];
                    $condition['sdeal_id'] = $arr['subdeal_id'];
                    $subdealData = getRecords('tbl_sub_deals', $condition, 'first');
                    $arr_od_gift['od_sub_deal_name'] = $subdealData['sdeal_name'];
                }
                if (!$this->db->insert_from_array('tbl_order_deals', $arr_od_gift)) {
                    $this->error = $this->db->getError();
                    return false;
                }
                $order_deal_id = $this->db->insert_id();
                /* Save Selected Deal's/Product's Options in table "tbl_order_option", script starts here */
                if (isset($arr['option']) && is_array($arr['option']) && count($arr['option'])) {
                    foreach ($arr['option'] as $option) {
                        $arr_od_option = array(
                            //   'oo_order_id' => $this->orderId,
                            'oo_od_id' => $order_deal_id,
                            'oo_deal_option_id' => $option['deal_option_id'],
                            'oo_deal_option_value_id' => $option['option_value_id'],
                            'oo_option_name' => $option['option_name'],
                            'oo_option_value' => $option['option_value'],
                            'oo_option_type' => $option['option_type'],
                            'oo_price' => $option['price'],
                            'oo_price_prefix' => $option['price_prefix'],
                        );
                        if (!$this->db->insert_from_array('tbl_order_option', $arr_od_option)) {
                            $this->error = $this->db->getError();
                            return false;
                        }
                    }
                }
                /* Save Selected Deal's/Product's Options in table "tbl_order_option", script ends here */
            }
            /* Save Selected tax's Options in table "tbl_order_deal_taxes", script starts here */
            if (isset($arr['tax']) && is_array($arr['tax']) && count($arr['tax'])) {
                foreach ($arr['tax']['taxDetail'] as $option) {
                    $arr_od_option = array(
                        //   'oo_order_id' => $this->orderId,
                        'odtax_od_id' => $order_deal_id,
                        'odtax_rate' => $option['taxrate'],
                        'odtax_rate_name' => $option['taxrate_name'],
                        'odtax_class_name' => $option['taxclass_name'],
                        'odtax_zone_name' => $option['geozone_name']
                    );
                    if (!$this->db->insert_from_array('tbl_order_deal_taxes', $arr_od_option)) {
                        $this->error = $this->db->getError();
                        return false;
                    }
                }
            }
            /* Save Selected tax's Options in table "tbl_order_deal_taxes", script ends here */
        }
        /*   ------ Insert voucher number -------- */
        insertVouchers($this->orderId);
        /*   ------ Insert voucher number End Here -------- */
        return true;
    }

    public function getCharityId($cart_item_id, $cart_item_deal_id = 0)
    {
        $rs = $this->db->query("select * from tbl_cart_items where cart_item_id =" . $cart_item_id . " AND cart_item_deal_id=" . $cart_item_deal_id);
        if (!$cartDetail = $this->db->fetch($rs)) {
            $this->error = t_lang('M_ERROR_INVALID_DEAL_ID');
        }
        return $cartDetail;
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    public function markOrderPaid($orderid, $auth = false)
    {
        global $msg;
        if (strlen($orderid) < 13) {
            $msg->addError(t_lang('M_ERROR_RECORDS_NOT_FOUND_TO_MARK_ORDER_PAID') . "*Code*165*");
            return false;
        }
        $rs = $this->db->query("select * from tbl_orders where order_payment_status != 2 AND order_id=" . $this->db->quoteVariable($orderid));
        if ($this->db->total_records($rs) <= 0) {
            $msg->addError($orderid . t_lang('M_ERROR_RECORDS_NOT_FOUND_TO_MARK_ORDER_PAID') . "*Code*173*");
            return false;
        }
        if (!($row = $this->db->fetch($rs))) {
            $msg->addError(t_lang('M_ERROR_RECORDS_NOT_FOUND_TO_MARK_ORDER_PAID') . "*Code*178*");
            return false;
        }
        if ($row['order_payment_mode'] == 1) {
            $paymentMode = 'M_TXT_PAYPAL';
        }
        if ($row['order_payment_mode'] == 2) {
            $paymentMode = 'M_TXT_CREDIT_CARD';
        }
        if ($row['order_payment_mode'] == 3) {
            $paymentMode = 'M_TXT_CIM';
        }
        if (intval($row['order_user_id']) <= 0) {
            $msg->addError(t_lang('M_ERROR_RECORDS_NOT_FOUND_TO_MARK_ORDER_PAID') . "*Code*188*");
            return false;
        }
        if ($row['order_charge_from_wallet'] > 0) {
            $rs1 = $this->db->query("select user_wallet_amount from tbl_users where user_id='" . intval($row['order_user_id']) . "'");
            if (!($row1 = $this->db->fetch($rs1))) {
                return false;
            }
            $amount = 0;
            if ($row1['user_wallet_amount'] >= $row['order_charge_from_wallet']) {
                $this->db->query("update tbl_users set user_wallet_amount = user_wallet_amount - " . ($row['order_charge_from_wallet']) . " where user_id=" . intval($row['order_user_id']));
                $rs2 = $this->db->query("select od_qty,od_gift_qty,od_deal_price,od_deal_tax_amount,od_deal_id from tbl_order_deals where od_order_id=" . $this->db->quoteVariable($orderid));
                while ($row2 = $this->db->fetch($rs2)) {
                    $srch = new SearchBase('tbl_deals', 'd');
                    $srch->addCondition('deal_id', '=', $row2['od_deal_id']);
                    $srch->addMultipleFields(array('deal_type', 'deal_sub_type'));
                    $rsDeal = $srch->getResultSet();
                    $dealData = $this->db->fetch($rsDeal);
                    $shipingCharging = 0;
                    if (($dealData['deal_type'] == 1) & ($dealData['deal_sub_type'] == 0)) {
                        $shipingCharging = $row['order_shipping_charges'];
                    }
                    $totalQuantity = ($row2['od_qty'] + $row2['od_gift_qty']);
                    $priceQty = $row2['od_deal_price'] + $row2['od_deal_tax_amount'] + $shipingCharging;
                    $deal_id = $row2['od_deal_id'];
                    $amount += (intval($totalQuantity) * intval($priceQty));
                }
                /*  Amount Deposited */
                if (intval($deal_id) <= 0) {
                    $msg->addError(t_lang('M_ERROR_RECORDS_NOT_FOUND_TO_MARK_ORDER_PAID') . "*Code*207*");
                    return false;
                }
                $this->db->insert_from_array('tbl_user_wallet_history', array(
                    'wh_user_id' => $row['order_user_id'],
                    'wh_untipped_deal_id' => $deal_id,
                    'wh_particulars' => 'Amount Deposited : ' . $paymentMode,
                    'wh_amount' => ($amount - $row['order_charge_from_wallet']),
                    'wh_time' => 'mysql_func_now()'
                        ), true);
                /*  Amount Deducted */
                $rs3 = $this->db->query("select deal_name from tbl_deals where deal_id=" . intval($deal_id));
                $rowDeal = $this->db->fetch($rs3);
                $dealUrl = CONF_WEBROOT_URL . 'deal.php?deal=' . $deal_id . '&type=main';
                $this->db->insert_from_array('tbl_user_wallet_history', array(
                    'wh_user_id' => $row['order_user_id'],
                    'wh_untipped_deal_id' => $deal_id,
                    // 'wh_particulars' => 'Order Purchased : <a href="' . $dealUrl . '">' . $rowDeal['deal_name'] . '</a>',
                    'wh_particulars' => 'Order ' . $orderid . ' placed with Wallet and ' . $paymentMode,
                    'wh_amount' => 0 - $amount,
                    'wh_time' => 'mysql_func_now()'
                        ), true);
            } else { //Refund order here, in case of insufficient wallet amount..
                $amount = 0;
                $rs2 = $this->db->query("select od_qty,od_gift_qty,od_deal_price,od_deal_tax_amount,od_deal_id from tbl_order_deals where od_order_id=" . $this->db->quoteVariable($orderid));
                while ($row2 = $this->db->fetch($rs2)) {
                    $srch = new SearchBase('tbl_deals', 'd');
                    $srch->addCondition('deal_id', '=', $row2['od_deal_id']);
                    $srch->addMultipleFields(array('deal_type', 'deal_sub_type'));
                    $rsDeal = $srch->getResultSet();
                    $dealData = $this->db->fetch($rsDeal);
                    $shipingCharging = 0;
                    if (($dealData['deal_type'] == 1) & ($dealData['deal_sub_type'] == 0)) {
                        $shipingCharging = $row['order_shipping_charges'];
                    }
                    $totalQuantity = ($row2['od_qty'] + $row2['od_gift_qty']);
                    $priceQty = $row2['od_deal_price'] + $row2['od_deal_tax_amount'] + $shipingCharging;
                    $amount += (intval($totalQuantity) * intval($priceQty));
                    $deal_id = $row2['od_deal_id'];
                }
                if (intval($deal_id) <= 0) {
                    return false;
                }
                $this->db->query("update tbl_users set user_wallet_amount = user_wallet_amount+" . ($amount - $row['order_charge_from_wallet']) . " where user_id=" . intval($row['order_user_id']));
                $this->db->query("update tbl_orders set order_payment_status = 2 where order_id=" . $this->db->quoteVariable($orderid));
                $this->db->insert_from_array('tbl_user_wallet_history', array(
                    'wh_user_id' => $row['order_user_id'],
                    'wh_untipped_deal_id' => $deal_id,
                    'wh_particulars' => 'Amount Deposited : Wallet and ' . $paymentMode,
                    'wh_amount' => ($amount - $row['order_charge_from_wallet']),
                    'wh_time' => 'mysql_func_now()'
                        ), true);
                $msg->addMsg(t_lang('M_ERROR_DUE_TO_INSUFFICIENT_BALANCE_ORDER_REFUNDED'));
                return false;
                exit(0);
            }
        }
        if ($auth == 'Auth') {
            $this->db->update_from_array('tbl_orders', array('order_payment_status' => 3), "order_id=" . $this->db->quoteVariable($orderid));
        } else {
            $this->db->update_from_array('tbl_orders', array('order_payment_status' => 1), "order_id=" . $this->db->quoteVariable($orderid));
        }
        $srch = new SearchBase('tbl_order_deals', 'od');
        $srch->addCondition('od_order_id', '=', $orderid);
        $srch->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id=d.deal_id', 'd');
        $srch->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=c.company_id', 'c');
        $srch->joinTable('tbl_countries', 'INNER JOIN', 'count.country_id=c.company_country', 'count');
        $srch->joinTable('tbl_company_addresses', 'INNER JOIN', 'od.od_company_address_id =ca.company_address_id', 'ca');
        $srch->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id=o.order_id', 'o');
        $srch->joinTable('tbl_states', 'LEFT JOIN', 'state.state_id=c.company_state', 'state');
        $srch->joinTable('tbl_users', 'INNER JOIN', 'o.order_user_id=u.user_id', 'u');
        $srch->addMultipleFields(array('d.deal_min_coupons', 'd.deal_tipped_at', 'd.deal_id', 'd.deal_name', 'd.deal_status', 'd.deal_highlights', 'd.deal_desc', 'd.voucher_valid_till', 'd.voucher_valid_from', 'd.deal_charity_discount', 'd.deal_charity_discount_is_percent', 'd.deal_redeeming_instructions', 'c.company_name', 'c.company_email', 'c.company_phone', 'c.company_address1', 'c.company_address2', 'c.company_address3', 'c.company_city', 'c.company_zip', 'c.company_country', 'count.country_name', 'ca.company_address_line1', 'ca.company_address_line2', 'ca.company_address_line3', 'ca.company_address_zip',
            'od.od_to_email', 'od.od_to_name', 'od.od_email_msg', 'u.user_name', 'o.order_id', 'o.order_date', 'o.order_charity_id', 'o.order_referrer_id', 'o.order_payment_mode',
            'od_deal_price', 'od_qty', 'od_gift_qty', 'order_shipping_charges', 'od_deal_charity_id', 'od_voucher_suffixes', 'u.user_id', 'u.user_email', 'u.user_member_id', 'state.state_name' . $_SESSION['lang_fld_prefix'] . ' as company_state'
        ));
        $rs = $srch->getResultSet();
        if ($this->db->total_records($rs) <= 0) {
            $msg->addError(t_lang('M_ERROR_RECORDS_NOT_FOUND_TO_MARK_ORDER_PAID') . "*Code*307*");
            return false;
        }
        $resulset = $this->db->fetch_all($rs);
        //while($row_deal = $this->db->fetch($rs))
        /* Update Wallet History for maintaining the transaction history */
        if ($resulset[0]['order_payment_mode'] == 1) {
            $paymentMode = 'M_TXT_PAYPAL';
        }
        if ($resulset[0]['order_payment_mode'] == 2) {
            $paymentMode = 'M_TXT_CREDIT_CARD';
        }
        if ($row['order_charge_from_wallet'] == 0) {
            if (($resulset[0]['order_payment_mode'] == 1) || ($resulset[0]['order_payment_mode'] == 2 || ($resulset[0]['order_payment_mode'] == 4))) {
                $rs2 = $this->db->query("select od_qty,od_gift_qty,od_deal_price,od_deal_tax_amount,od_deal_id,od_deal_name from tbl_order_deals where od_order_id=" . $this->db->quoteVariable($orderid));
                while ($row2 = $this->db->fetch($rs2)) {
                    $srch = new SearchBase('tbl_deals', 'd');
                    $srch->addCondition('deal_id', '=', $row2['od_deal_id']);
                    $srch->addMultipleFields(array('deal_type', 'deal_sub_type'));
                    $rsDeal = $srch->getResultSet();
                    $dealData = $this->db->fetch($rsDeal);
                    $shipingCharging = 0;
                    if (($dealData['deal_type'] == 1) & ($dealData['deal_sub_type'] == 0)) {
                        $shipingCharging = $resulset[0]['order_shipping_charges'];
                    }
                    $totalQuantity = ($row2['od_qty'] + $row2['od_gift_qty']);
                    $priceQty = $row2['od_deal_price'] + $row2['od_deal_tax_amount'] + $shipingCharging;
                    //$deal_id = $row2['od_deal_id'];
                    /*  Amount Deposited */
                    $this->db->insert_from_array('tbl_user_wallet_history', array(
                        'wh_user_id' => $resulset[0]['user_id'],
                        'wh_untipped_deal_id' => $row2['od_deal_id'],
                        'wh_particulars' => t_lang('M_TXT_AMOUNT_DEPOSITED') . ' : ' . $paymentMode,
                        'wh_amount' => ($priceQty * $totalQuantity),
                        'wh_time' => 'mysql_func_now()'
                            ), true);
                    /*  Amount Deducted */
                    $dealUrl = CONF_WEBROOT_URL . 'deal.php?deal=' . $row2['od_deal_id'] . '&type=main';
                    $this->db->insert_from_array('tbl_user_wallet_history', array(
                        'wh_user_id' => $resulset[0]['user_id'],
                        'wh_untipped_deal_id' => $row2['od_deal_id'],
                        'wh_particulars' => t_lang('M_TXT_ITEM_PURCHASED') . ': <a href="' . friendlyUrl($dealUrl) . '">' . $row2['od_deal_name'] . '</a>',
                        'wh_amount' => 0 - ($priceQty * ($totalQuantity)),
                        'wh_time' => 'mysql_func_now()'
                            ), true);
                }
            }
        }
        /* Update Wallet History for maintaining the transaction history */
        #################### EMAIL TO USER FOR CASE4 FRIEND BUY AN OFFER ###########
        $this->notifyPurchaseToReferrer($resulset[0]['order_referrer_id']);
        #################### EMAIL TO USER FOR CASE4 FRIEND BUY AN OFFER ###########
        $loopCount = 0;
        $refer_order_id = '';
        foreach ($resulset as $key => $row_deal) {
            /* Deal is not marked as tipped. Check if need to be tipped. */
            if (displayDate($row_deal['deal_tipped_at']) == '') {
                $rs_sold = $this->db->query('select SUM(CASE WHEN o.order_payment_status=1  THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS sold from tbl_orders o, tbl_order_deals od where o.order_id=od.od_order_id AND o.order_payment_status=1 AND od.od_deal_id=' . intval($row_deal['deal_id']));
                $row_sold = $this->db->fetch($rs_sold);
                if ((intval($row_sold['sold']) >= $row_deal['deal_min_coupons']) && ($row_deal['deal_status'] != 3)) { /* mark as tipped */
                    $this->db->update_from_array('tbl_deals', array('deal_tipped_at' => 'mysql_func_now()'), 'deal_id=' . intval($row_deal['deal_id']), true);
                    /* Notify users and update commissions */
                    $rs = $this->db->query("select * from tbl_email_templates where tpl_id=1");
                    $row_tpl = $this->db->fetch($rs);
                    $rs = $this->db->query("select * from tbl_email_templates where tpl_id=6");
                    $row_tpl_gift = $this->db->fetch($rs);
                    $srch = new SearchBase('tbl_order_deals', 'od');
                    $srch->addCondition('od_deal_id', '=', intval($row_deal['deal_id']));
                    $srch->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id=o.order_id and o.order_payment_status=1', 'o');
                    $srch->joinTable('tbl_users', 'INNER JOIN', 'o.order_user_id=u.user_id', 'u');
                    $srch->addMultipleFields(array('o.order_referrer_id', 'u.user_affiliate_id', 'od.od_qty', 'od.od_gift_qty', 'o.order_id', 'od.od_to_email', 'od.od_to_name', 'od.od_email_msg', 'o.order_date', 'order_shipping_charges', 'od.od_voucher_suffixes', 'u.user_name', 'u.user_email', 'u.user_member_id'));
                    $srch->doNotCalculateRecords();
                    $srch->doNotLimitRecords();
                    $rs = $srch->getResultSet();
                    $count = 0;
                    while ($row = $this->db->fetch($rs)) {
                        $count++;
                        $instruction = '';
                        $deal_desc = '';
                        $deal_name = '';
                        $instruction = ($row_deal['deal_redeeming_instructions' . $_SESSION['lang_fld_prefix']] ? $row_deal['deal_redeeming_instructions' . $_SESSION['lang_fld_prefix']] : 'N/A');
                        $dealPrice = $row_deal['od_deal_price'] + $row_deal['tax_amount'];
                        $date = "";
                        if ($row_deal['obooking_booking_from'] != "" && $row_deal['obooking_booking_till'] != "") {
                            $checkoutDate = date('Y-m-d', strtotime($row_deal['obooking_booking_till'] . ' +1 day'));
                            $date = date("D M j Y", strtotime($row_deal['obooking_booking_from'])) . ' ' . t_lang('M_TXT_TO') . ' ' . date("D M j Y", strtotime($checkoutDate));
                            $date1 = strtotime($row_deal['obooking_booking_from']);
                            $date2 = strtotime($checkoutDate);
                            $diff = $date2 - $date1;
                            $date .= " ( " . floor($diff / 3600 / 24) . ' ' . t_lang('M_TXT_NIGHTS') . " )";
                        }
                        $subdealname = "";
                        $style = 'style="color:#000; padding:3px 0;"';
                        if ($row_deal['od_sub_deal_name'] != "") {
                            $sub_deal_name = "(" . $row_deal['od_sub_deal_name'] . ")";
                        }
                        $deal_name = html_entity_decode(appendPlainText($row_deal['deal_name' . $_SESSION['lang_fld_prefix']])) . ' ' . $sub_deal_name;
                        $deal_desc = '<li ' . $style . '><strong>' . $deal_name . '</strong></li>';
                        if ($date != '') {
                            $deal_desc .= '<li ' . $style . '><strong>' . $date . '</strong></li>';
                        }
                        if ($row_deal['deal_desc' . $_SESSION['lang_fld_prefix']] != '') {
                            $deal_desc .= '<li ' . $style . '><strong>' . $row_deal['deal_desc' . $_SESSION['lang_fld_prefix']] . '</strong></li>';
                        }
                        $totalQuantity = intval($row['od_qty']) + intval($row['od_gift_qty']);
                        if ($totalQuantity > 0 && $row['order_id'] != $row_deal['order_id']) {
                            $arr_replacements = array(
                                'xxuser_namexx' => $row['user_name'],
                                'xxis_giftedxx' => '',
                                'xxtippedxx' => t_lang('M_TXT_EMAIL_DEAL_TIPPED'),
                                'xxdeal_namexx' => appendPlainText($deal_name),
                                'xxamountxx' => CONF_CURRENCY . number_format(($row_deal['od_deal_price'] + $row_deal['order_shipping_charges']), 2) . CONF_CURRENCY_RIGHT,
                                'xxordered_coupon_qtyxx' => '1',
                                'xxdeal_highlightsxx' => $row_deal['deal_highlights' . $_SESSION['lang_fld_prefix']],
                                'xxdeal_descriptionxx' => $deal_desc,
                                'xxcompany_namexx' => $row_deal['company_name'],
                                'xxcompany_addressxx' => $row_deal['company_name'] . '<br/>
							' . $row_deal['company_address_line1'] . ',<br/>
							' . $row_deal['company_address_line2'] . '<br/>
							' . $row_deal['company_address_line3'] . ' ' . $row_deal['company_city'] . ' <br/>
							' . $row_deal['company_state'] . ' ' . $row_deal['country_name'] . '<br/>',
                                'xxcompany_zipxx' => $row_deal['company_address_zip'],
                                'xxcompany_phonexx' => $row_deal['company_phone'],
                                'xxcompany_emailxx' => $row_deal['company_email'],
                                'xxpurchase_datexx' => displayDate($row['order_date'], true),
                                'xxsite_namexx' => CONF_SITE_NAME,
                                'xxserver_namexx' => $_SERVER['SERVER_NAME'],
                                'xxwebrooturlxx' => CONF_WEBROOT_URL,
                                'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
                                'xxwebsiteurlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
                                'xxordered_coupon_qtyxx' => '1',
                                'xxinstructionsxx' => $instruction,
                                'xxvalidtillxx' => displayDate($row_deal['voucher_valid_till']),
                                'xxvalidfromxx' => displayDate($row_deal['voucher_valid_from']),
                                'xxsitenamexx' => CONF_SITE_NAME
                            );
                            $od_voucher_suffixes = explode(', ', $row['od_voucher_suffixes']);
                            foreach ($od_voucher_suffixes as $voucher) {
                                /* QR CODE */
                                $PNG_TEMP_DIR = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'qrcode/temp' . DIRECTORY_SEPARATOR;
                                //html PNG location prefix
                                $PNG_WEB_DIR = 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'qrcode/temp/';
                                if (!file_exists($PNG_TEMP_DIR)) {
                                    mkdir($PNG_TEMP_DIR);
                                }
                                $errorCorrectionLevel = 'L';
                                $matrixPointSize = 4;
                                $filename = $PNG_TEMP_DIR . 'qr_' . $row['order_id'] . $voucher . '.png';
                                if (CONF_QR_CODE == 1) {
                                    QRcode::png($row['order_id'] . $voucher, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
                                    $officeUse = '';
                                }
                                if (CONF_QR_CODE == 2) {
                                    QRcode::png('http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'merchant/voucher-detail.php?id=' . $row['order_id'] . $voucher, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
                                    $officeUse = 'For office use only';
                                }
                                /* QR CODE */
                                $arr_replacements['xxorderidxx'] = $row['order_id'] . $voucher;
                                $arr_replacements['xxqrcodexx'] = '<img src="' . $PNG_WEB_DIR . basename($filename) . '" />';
                                $arr_replacements['xxofficeusexx'] = $officeUse;
                                if (intval($row['od_gift_qty']) > 0 && intval($voucher) >= 5556 && intval($voucher) <= 9999) {
                                    if ($row_tpl_gift['tpl_status'] == 1) {
                                        $message = $row_tpl_gift['tpl_message'];
                                        $subject = $row_tpl_gift['tpl_subject'];
                                        $arr_replacements['xxfriendxx'] = $row['od_to_name'];
                                        $arr_replacements['xxmessagexx'] = $row['od_email_msg'];
                                        $arr_replacements['xxrecipientxx'] = $row['od_to_name'];
                                        $arr_replacements['xxemail_addressxx'] = $row['od_to_email'];
                                        foreach ($arr_replacements as $key => $val) {
                                            $subject = str_replace($key, $val, $subject);
                                            $message = str_replace($key, $val, $message);
                                        }
                                        sendMail($row['od_to_email'], $subject . ' ' . $row['order_id'] . $voucher, emailTemplateSuccess($message));
                                        $arr_replacements['xxis_giftedxx'] = 'This voucher is gifted to ' . $row['od_to_name'] . ', a notification has been sent to email address: ' . $row['od_to_email'] . ' regarding this.';
                                    } else {
                                        $arr_replacements['xxis_giftedxx'] = 'This voucher is gifted to ' . $row['od_to_name'];
                                    }
                                }
                                $arr_replacements['xxrecipientxx'] = $row['user_name'];
                                $arr_replacements['xxemail_addressxx'] = $row['user_email'];
                                $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
                                $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
                                foreach ($arr_replacements as $key => $val) {
                                    $subject = str_replace($key, $val, $subject);
                                    $message = str_replace($key, $val, $message);
                                }
                                if ($row_tpl['tpl_status'] == 1) {
                                    $headers = '';
                                    sendMail($row['user_email'], $subject, emailTemplateSuccess($message), $headers);
                                }
                            }
                        }
                        /* CODE FOR REFER COMMISION */
                    }
                    /* Notify users and update commissions ends */
                }
            }
            return true;
        }

    }

    function notifyPurchaseToReferrer($order_referrer_id)
    {
        if (intval($order_referrer_id) > 0) {
            $checkPer1 = $this->db->query("select en_friend_buy_deal from tbl_email_notification where en_user_id=" . intval($order_referrer_id));
            $row_per1 = $this->db->fetch($checkPer1);
            if ($row_per1['en_friend_buy_deal'] == 1) {
                $rs1 = $this->db->query("select * from tbl_email_templates where tpl_id=43");
                $row_tpl = $this->db->fetch($rs1);
                if ($row_tpl['tpl_status'] == 1) {
                    $rsFriend = $this->db->query("select user_name,user_email from tbl_users where user_id=" . intval($order_referrer_id));
                    if (!$row2 = $this->db->fetch($rsFriend)) {
                        $error = 'Invalid Referrer.';
                        return false;
                    }
                    $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
                    $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
                    $arr_replacements = array(
                        'xxuser_namexx' => $row2['user_name'],
                        'xxsite_namexx' => CONF_SITE_NAME,
                        'xxserver_namexx' => $_SERVER['SERVER_NAME'],
                        'xxwebrooturlxx' => CONF_WEBROOT_URL,
                        'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL
                    );
                    foreach ($arr_replacements as $key => $val) {
                        $subject = str_replace($key, $val, $subject);
                        $message = str_replace($key, $val, $message);
                    }
                    sendMail($row2['user_email'], $subject, emailTemplate($message), $headers);
                    return true;
                }
            }
        }
        return false;
    }

    /*         * now not used */

    function updateCharityWallet($orderid)
    {
        $srch = new SearchBase('tbl_order_deals', 'od');
        $srch->addCondition('od_order_id', '=', $orderid);
        $srch->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id=d.deal_id', 'd');
        $srch->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=c.company_id', 'c');
        $srch->joinTable('tbl_countries', 'INNER JOIN', 'count.country_id=c.company_country', 'count');
        $srch->joinTable('tbl_company_addresses', 'INNER JOIN', 'od.od_company_address_id =ca.company_address_id', 'ca');
        $srch->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id=o.order_id', 'o');
        $srch->joinTable('tbl_states', 'LEFT JOIN', 'state.state_id=c.company_state', 'state');
        $srch->joinTable('tbl_users', 'INNER JOIN', 'o.order_user_id=u.user_id', 'u');
        $srch->addMultipleFields(array('d.deal_min_coupons', 'd.deal_tipped_at', 'd.deal_id', 'd.deal_name', 'd.deal_status', 'd.deal_highlights', 'd.deal_desc', 'd.voucher_valid_till', 'd.voucher_valid_from', 'd.deal_charity_discount', 'd.deal_charity_discount_is_percent', 'd.deal_redeeming_instructions', 'c.company_name', 'c.company_email', 'c.company_phone', 'c.company_address1', 'c.company_address2', 'c.company_address3', 'c.company_city', 'c.company_zip', 'c.company_country', 'count.country_name', 'ca.company_address_line1', 'ca.company_address_line2', 'ca.company_address_line3', 'ca.company_address_zip',
            'od.od_to_email', 'od.od_to_name', 'od.od_email_msg', 'u.user_name', 'o.order_id', 'o.order_date', 'o.order_charity_id', 'o.order_referrer_id', 'o.order_payment_mode',
            'od_deal_price', 'od_qty', 'od_gift_qty', 'od_deal_charity_id', 'u.user_id', 'u.user_email', 'u.user_member_id', 'state.state_name' . $_SESSION['lang_fld_prefix'] . ' as company_state'
        ));
        $rs = $srch->getResultSet();
        while ($row_deal = $this->db->fetch($rs)) {
            if ($row_deal['deal_charity_discount_is_percent'] == 1) {
                $charityAmount = ((($row_deal['od_deal_price'] * ($row_deal['od_qty'] + $row_deal['od_gift_qty'])) / 100) * $row_deal['deal_charity_discount']);
            } else {
                $charityAmount = $row_deal['deal_charity_discount'];
            }
            if ($row_deal['od_deal_charity_id'] > 0) {
                $this->db->insert_from_array('tbl_charity_history', array(
                    'ch_user_id' => $row_deal['user_id'],
                    'ch_order_id' => $row_deal['order_id'],
                    'ch_charity_id' => $row_deal['od_deal_charity_id'],
                    'ch_deal_id' => $row_deal['deal_id'],
                    'ch_particulars' => 'Charity on deal ' . $row_deal['deal_name'] . ' having quantity ' . ($row_deal['od_qty'] + $row_deal['od_gift_qty']) . '@' . $charityAmount,
                    'ch_amount' => $charityAmount,
                    'ch_time' => 'mysql_func_now()'
                        ), true);
            }
        }
        return true;
    }

    /**
     * @$customTime : Custom order cancellation time in seconds
     * @$ignoreTime : Pending orders regardless of order create datetime
     * @limit : Number of records to return. Pass 0 to return all records
     */
    function getAllPendingOrders($customTime = '', $ignoreTime = false, $limit = 10)
    {
        $srch = new SearchBase('tbl_orders', 'o');
        $srch->addCondition('o.order_payment_status', '=', 0);
        if (!$ignoreTime) {
            echo $currentDateTime = date('Y-m-d H:i:s');
            if (!empty($customTime)) {
                $specificDateTime = date('Y-m-d H:i', strtotime($currentDateTime) - $customTime);
            } else {
                $specificDateTime = date('Y-m-d H:i', strtotime($currentDateTime) - ORDER_CANCELLATION_TIME);
            }
            $condition = "'" . $specificDateTime . ':00' . "' >= DATE_FORMAT(o.order_date, '%Y-%m-%d %H:%i:00') ";
            $srch->addDirectCondition($condition);
        }
        if (intval($limit) > 0) {
            $srch->setPageSize($limit);
        } else {
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
        }
        $srch->addMultipleFields(array('o.order_id', 'o.order_date', 'o.order_user_id', 'o.order_payment_mode', 'o.order_payment_status'));
        $rs = $srch->getResultSet();
        //echo $srch->getQuery();
        $totalRecords = $this->db->total_records($rs);
        if ($totalRecords > 0) {
            return $this->db->fetch_all($rs);
        }
        return false;
    }

    function markOrderCancelled($orderid)
    {
        global $db;
        if (empty($orderid) || strlen($orderid) < 13) {
            return false;
        }
        $data = array(
            'order_payment_status' => (-1)
        );
        $where = array(
            'smt' => 'order_payment_status = ? AND order_id = ?',
            'vals' => array(0, $orderid)
        );
        return $db->update_from_array('tbl_orders', $data, $where);
    }
}
