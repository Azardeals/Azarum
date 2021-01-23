<?php

loadModels(['MyAppModel']);

class Voucher extends MyAppModel
{

    const DB_TBL = 'tbl_coupon_mark';
    const DB_TBL_PREFIX = 'cm_';
    const TYPE_PENDING = 0;
    const TYPE_SHIPPED = 1;
    const TYPE_DELIVERED = 2;
    const DEAL_TYPE_DEAL = '0-0';
    const DEAL_TYPE_PRODUCT = '1-0';
    const DEAL_TYPE_BOOKING_REQUEST = '0-1';
    const DEAL_TYPE_ONLINE_BOOKING = '0-2';
    const DEAL_TYPE_DIGITAL_PRODUCT = '1-1';
    const PAYMENT_CANCELLED = '-1';
    const PAYMENT_PENDING = 0;
    const PAYMENT_PAID = 1;
    const PAYMENT_REFUND_SENT = 2;
    const PAYMENT_AUTHORIZED = 3;

    public function __construct()
    {
        global $db;
    }

    public static function getSearchObject($langId = 0)
    {
        $srch = new SearchBase(static::DB_TBL, 'cm');
        return $srch;
    }

    public function getOrdersListing($requestStatus = '')
    {
        $srch = self::getSearchObject();
        $srch->joinTable('tbl_order_deals', 'INNER JOIN', "cm.cm_order_id=od.od_order_id AND (od.od_voucher_suffixes LIKE CONCAT('%', cm.cm_counpon_no, '%')  OR od.od_cancelled_voucher_suffixes LIKE CONCAT('%', cm.cm_counpon_no, '%'))", 'od');
        switch ($requestStatus) {
            case 'active':
                //active and paid
                $cnd = $srch->addDirectCondition('0');
                $srch->addCondition('cm.cm_status', '=', 0);
                $cnd->attachCondition("order_payment_status", '=', 1, 'OR');
                break;
            case 'used':
                //used and paid
                $cnd = $srch->addDirectCondition('0');
                $srch->addCondition('cm.cm_status', '=', 1);
                $cnd->attachCondition("order_payment_status", '=', 1, 'OR');
                break;
            case 'expired':
                //active | used || Cancelled
                $cnd = $srch->addDirectCondition('0');
                $srch->addCondition('cm.cm_status', '=', 2);
                $cnd->attachCondition("order_payment_status", '=', (-1), 'OR');
                break;
            case 'refunded':
                //paid but refunded
                $cnd = $srch->addDirectCondition('0');
                $srch->addCondition('cm.cm_status', '=', 3);
                $cnd->attachCondition("order_payment_status", '=', 2, 'OR');
                break;
            case 'cancelled':
                //payment fail
                $cnd = $srch->addDirectCondition('0');
                $cnd->attachCondition("order_payment_status", '=', (-1), 'OR');
                break;
            default:
                //do nothing
                if (!isset($post['mode']) || $post['mode'] != 'search') {
                    $cnd = $srch->addDirectCondition('0');
                    $cnd->attachCondition("order_payment_status", '=', 1, 'OR');
                }
                break;
        }
        $srch->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id = d.deal_id ', 'd');
        $srch->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id = o.order_id', 'o');
        $srch->joinTable('tbl_users', 'INNER JOIN', 'o.order_user_id=u.user_id', 'u');
        $srch->joinTable('tbl_order_shipping_details', 'LEFT OUTER JOIN', 'osd_order_id = o.order_id', 'osd');
        $srch->joinTable('tbl_countries', 'LEFT OUTER JOIN', 'osd.osd_country_id=co.country_id', 'co');
        $srch->joinTable('tbl_states', 'LEFT OUTER JOIN', 'osd.osd_state_id=state.state_id', 'state');
        $srch->joinTable('tbl_digital_product_extras', 'LEFT JOIN', 'od.od_deal_id=dpe.dpe_deal_id', 'dpe');
        $srch->addFld('CONCAT(osd_recipient_name, "\n", osd_address_line1, ", ", osd_address_line2, "\n", osd_city_name, ", ", state.state_name, ", ", co.country_name ) as shipping_details');
        $srch->addFld('IF(deal_tipped_at, 1, 0) as is_tipped');
        $srch->addFld('deal_min_coupons as deal_tip');
        $srch->addFld('CASE WHEN d.voucher_valid_from <= now() THEN 1 ELSE 0 END as canUse');
        $srch->addFld('CASE WHEN d.voucher_valid_till >= now() and cm.cm_status=0 THEN 1 ELSE 0 END as active');
        $srch->addFld('CASE WHEN cm.cm_status=1 THEN 1 ELSE 0 END as used');
        $srch->addFld('CASE WHEN (d.voucher_valid_till < now()  and cm.cm_status=0) || cm.cm_status=2  THEN 1 ELSE 0 END as expired');
        $srch->addFld('CASE WHEN (d.voucher_valid_till < now()  and cm.cm_status=0) || cm.cm_status=2  THEN 1 ELSE 0 END as expired');
        $srch->addMultipleFields(['d.deal_sub_type', 'd.deal_type', 'dpe.dpe_product_file_name', 'dpe.dpe_product_external_url']);
        return $srch;
    }

    public static function getStatusArray()
    {
        return array(
            static::TYPE_PENDING => t_lang('M_TXT_PENDING'),
            static::TYPE_SHIPPED => t_lang('M_TXT_SHIPPED'),
            static::TYPE_DELIVERED => t_lang('M_TXT_DELIVERED'),
        );
    }

    public static function getDealTypeArray()
    {
        return array(
            static::DEAL_TYPE_DEAL => t_lang('M_TXT_DEAL'),
            static::DEAL_TYPE_BOOKING_REQUEST => t_lang('M_TXT_BOOKING_REQUEST'),
            static::DEAL_TYPE_ONLINE_BOOKING => t_lang('M_TXT_ONLINE_BOOKING'),
            static::DEAL_TYPE_DIGITAL_PRODUCT => t_lang('M_TXT_DIGITAL_PRODUCT'),
            static::DEAL_TYPE_PRODUCT => t_lang('M_TXT_PRODUCT'),
        );
    }

    public static function getPaymentStatusArray()
    {
        return array(
            static::PAYMENT_CANCELLED => t_lang('M_TXT_CANCELLED'),
            static::PAYMENT_PENDING => t_lang('M_TXT_PENDING'),
            static::PAYMENT_PAID => t_lang('M_TXT_PAID'),
            static::PAYMENT_REFUND_SENT => t_lang('M_TXT_REFUND_SENT'),
            static::PAYMENT_AUTHORIZED => t_lang('M_TXT_AUTHORIZED'),
        );
    }

    public static function getSearchForm()
    {
        $srchForm = new Form('Src_frm', 'Src_frm');
        $srchForm->setTableProperties('border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
        $srchForm->setFieldsPerRow(4);
        $srchForm->captionInSameCell(true);
        $srchForm->addTextBox(t_lang('M_TXT_VOUCHER_CODE'), 'order_id', '', '', '');
        $srchForm->addTextBox(t_lang('M_FRM_EMAIL_ADDRESS'), 'user_email', '', '', '');
        $srchForm->addSelectBox(t_lang('M_FRM_SHIPPING_STATUS'), 'cm_shipping_status', Voucher::getStatusArray(), '', '', 'Select', '');
        $srchForm->addSelectBox(t_lang('M_TXT_TYPE'), 'deal_type', Voucher::getDealTypeArray(), '', '', 'Select', '');
        $srchForm->addDateField(t_lang('M_FRM_ORDER_START_DATE'), 'order_start_time', '', 'order_start_time', '');
        $srchForm->addDateField(t_lang('M_FRM_ORDER_END_DATE'), 'order_end_time', '', 'order_end_time', '');
        $defaultDisplay = 1;
        $srchForm->addSelectBox(t_lang('M_FRM_PAYMENT_STATUS'), 'order_payment_status', Voucher::getPaymentStatusArray(), $defaultDisplay, '', 'Select', '');
        $srchForm->addHiddenField('', 'mode', 'search');
        $fld1 = $srchForm->addButton('', 'btn_search', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="inputbuttons" onclick=location.href="tipped-members.php"');
        $fld = $srchForm->addSubmitButton('', 'btn_cancel', t_lang('M_TXT_SEARCH'), '', ' class="inputbuttons"');
        $fld->attachField($fld1);
        return $srchForm;
    }

}
