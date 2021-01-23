<?php

class DealInfo
{

    private $db;
    private $error;
    private $flds;

    function __construct($deal_id, $set_deal_city_in_session = false)
    {
        if (!is_numeric($deal_id)) {
            $this->error = 'Invalid Id';
            return false;
        }
        $this->db = new Database();
        $srch = new SearchBase('tbl_deal_address_capacity', 'dac');
        $srch->addCondition('dac_deal_id', '=', $deal_id);
        $srch->joinTable('tbl_company_addresses', 'INNER JOIN', 'dac.dac_address_id = ca.company_address_id', 'ca');
        $srch->joinTable('tbl_deals', 'INNER JOIN', 'dac.dac_deal_id = d.deal_id', 'd');
        $srch->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=c.company_id and c.company_active=1 and c.company_deleted=0', 'c');
        $srch->addMultipleFields(['d.*', 'c.*', 'ca.*', 'dac.*']);
        $rs = $srch->getResultSet();
        if (!$row_deal = $this->db->fetch($rs)) {
            $this->error = t_lang('M_ERROR_INVALID_REQUEST');
            return false;
        }
        $this->flds = $row_deal;
        if ($this->flds['deal_is_subdeal'] == 1) {
            $subdealData = $this->fetchFirstSubdealData($deal_id);
            $this->flds['price'] = $subdealData['sdeal_original_price'] - (($subdealData['sdeal_discount_is_percentage'] == 1) ? $subdealData['sdeal_original_price'] * $subdealData['sdeal_discount'] / 100 : $subdealData['sdeal_discount']);
            $this->flds['deal_original_price'] = $subdealData['sdeal_original_price'];
            $this->flds['deal_discount_is_percent'] = $subdealData['sdeal_discount_is_percentage'];
            $this->flds['deal_discount'] = ($subdealData['sdeal_discount'] == '') ? 0 : $subdealData['sdeal_discount'];
        } else {
            $this->flds['price'] = $this->flds['deal_original_price'] - (($this->flds['deal_discount_is_percent'] == 1) ? $this->flds['deal_original_price'] * $this->flds['deal_discount'] / 100 : $this->flds['deal_discount']);
            $this->flds['deal_discount'] = ($this->flds['deal_discount'] == '') ? 0 : $this->flds['deal_discount'];
        }
        $this->flds['charity'] = $this->flds['deal_charity'];
        $srch = new SearchBase('tbl_order_deals', 'od');
        $srch->addCondition('od.od_deal_id', '=', $this->flds['deal_id']);
        $srch->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id=o.order_id', 'o');
        $probation_time = date('Y-m-d H:i:s', strtotime(ORDER_PROBATION_TIME));
        $srch->addFld("SUM(CASE WHEN o.order_payment_status=1  THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS sold");
        $srch->addFld("SUM(CASE WHEN o.order_payment_status=0 AND o.order_date>'" . $probation_time . "'  THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS payment_pending");
        $srch->addFld('od.od_deal_price');
        $srch->addFld("SUM(CASE WHEN o.order_payment_status=1  THEN (od.od_qty+od.od_gift_qty)*od_deal_price ELSE 0 END) AS saleAmount");
        $rs = $srch->getResultSet();
        if (!$row_sold = $this->db->fetch($rs)) {
            $row_sold = ['sold' => 0, 'payment_pending' => 0];
        }
        $this->flds['od_deal_price'] = $row_sold['od_deal_price'];
        $this->flds['sold'] = $row_sold['sold'];
        $this->flds['sold_amount'] = $row_sold['saleAmount'];
        $this->flds['sold_payment_pending'] = $row_sold['payment_pending'];
        if (!is_numeric($this->flds['sold'])) {
            $this->flds['sold'] = 0;
        }
        if (!is_numeric($this->flds['sold_payment_pending'])) {
            $this->flds['sold_payment_pending'] = 0;
        }
        if ($this->flds['deal_max_coupons'] > 0) {
            $this->flds['maxBuy'] = $this->flds['deal_max_coupons'] - $row_sold['sold'] - $row_sold['payment_pending'];
        } else {
            $this->flds['maxBuy'] = 1000;
        }
        if ($this->flds['deal_max_buy'] > 0) {
            if ($this->flds['maxBuy'] > $this->flds['deal_max_buy'])
                $this->flds['maxBuy'] = $this->flds['deal_max_buy'];
        }
        $this->flds['minBuy'] = $this->flds['deal_min_buy'];
        /* HANDLE THE CASE WHEN DEAL GET ID AND DISPLAY THE CITY NAME ON THE HEADER */
        $city_to_show = '';
        if ($_SESSION['lang_fld_prefix'] == '_lang1') {
            $city_to_show = ',city_name_lang1';
        }
        $rs = $this->db->query("select city_name" . $city_to_show . " from tbl_cities where city_id=" . intval($this->flds['deal_city']));
        $row = $this->db->fetch($rs);
        $this->flds['deal_city_name'] = $row['city_name'];
        if ($set_deal_city_in_session === true && intval($this->flds['deal_city']) > 0) {
            $_SESSION['cityname'] = $row['city_name'];
            $_SESSION['city_to_show'] = $row['city_name' . $_SESSION['lang_fld_prefix']];
            $_SESSION['city'] = $this->flds['deal_city'];
        }
        /* HANDLE THE CASE WHEN DEAL GET ID AND DISPLAY THE CITY NAME ON THE HEADER */
        //}
    }

    function getFields()
    {
        return $this->flds;
    }

    function getFldValue($fld)
    {
        if (isset($this->flds[$fld]))
            return $this->flds[$fld];
        return false;
    }

    function canBuyQty($qty)
    {
        if (!is_numeric($this->flds['deal_id'])) {
            $this->error = t_lang('M_ERROR_INVALID_REQUEST');
            return false;
        }
        if ($this->flds['deal_deleted'] == 1) {
            $this->error = t_lang('M_ERROR_INVALID_REQUEST');
            return false;
        }
        /* $currenttime=mktime(); */
        $currenttime = strtotime(dateForTimeZone(CONF_TIMEZONE));
        if ($currenttime < strtotime($this->flds['deal_start_time']) || $this->flds['deal_status'] > 2) {
            $this->error = t_lang('M_ERROR_DEAL_IS_NOT_OPEN');
            return false;
        }
        if ($currenttime > strtotime($this->flds['deal_end_time'])) {
            $this->error = t_lang('M_ERROR_SORRY_DEAL_EXPIRED');
            return false;
        }
        if ($this->flds['deal_max_coupons'] > 0) {
            if (($this->flds['sold'] + $qty) > $this->flds['deal_max_coupons']) {
                $this->error = t_lang('M_ERROR_USER_CANNOT_BUY_CONCATINATE') . ' ' . $qty . ' ' . t_lang('M_ERROR_MAX_COUPONS_CONCATINATE') . $this->flds['deal_max_coupons'] . ' ' . t_lang('M_ERROR_OUT_OF_WHICH_CONCATINATE') . ' ' . $row_sold['sold'] . ' ' . t_lang('M_ERROR_SOLD_OUT_CONCATINATE');
                /* $this->error='Oops! Sorry, You can not buy ' . $qty . ' coupons. Max coupons are ' . $this->flds['deal_max_coupons'] . ' out of which ' . $row_sold['sold'] . ' have been sold.'; */
                return false;
            }
            if (($this->flds['sold'] + $this->flds['sold_payment_pending'] + $qty) > $this->flds['deal_max_coupons']) {
                #$error='Oops! Sorry, You can not buy ' . $qty . ' coupons. Max coupons are ' . $this->flds['deal_max_coupons'] . ' out of which ' . $row_sold['sold'] . ' have been sold and ' . $row_sold['payment_pending'] . ' have payments pending. You can try back in a few minutes.';
                $this->error = t_lang('M_ERROR_ALREADY_PURCHASE_MAXIMUM_COUPONS');
                return false;
            }
        }
        return true;
    }

    function getError()
    {
        return $this->error;
    }

    function fetchSubDealdata($sdeal_id)
    {
        $subdeal = new SearchBase('tbl_sub_deals');
        $subdeal->addCondition('sdeal_id', '=', $sdeal_id);
        $subdeal->addCondition('sdeal_active', '=', 1);
        $result = $subdeal->getResultSet();
        $this->db = new Database();
        $row = $this->db->fetch($result);
        return $row;
    }

    function fetchFirstSubdealData($deal_id)
    {
        $subdeal = new SearchBase('tbl_sub_deals');
        $subdeal->addCondition('sdeal_deal_id', '=', $deal_id);
        $subdeal->addCondition('sdeal_active', '=', 1);
        //$subdeal->addCondition('sdeal_deal_id','=',$deal_id);
        $subdeal->addOrder('sdeal_original_price', 'asc');
        $result = $subdeal->getResultSet();
        $this->db = new Database();
        $row = $this->db->fetch($result);
        return $row;
    }

}
