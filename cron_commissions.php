<?php

require_once './application-top.php';
global $db, $msg;
$srch = new SearchBase('tbl_coupon_mark', 'cm');
$srch->joinTable('tbl_order_deals', 'INNER JOIN', "cm.cm_order_id=od.od_order_id AND od.od_voucher_suffixes LIKE CONCAT('%', cm.cm_counpon_no, '%')", 'od');
$srch->addCondition('cm.cm_status', 'IN', [0, 2]);
$srch->addCondition('d.voucher_valid_till', '<', date('Y-m-d H:i:s'));
$srch->addCondition('order_payment_status', '>', 0);
$srch->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id = d.deal_id', 'd');
$srch->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id = o.order_id', 'o');
$srch->joinTable('tbl_users', 'INNER JOIN', 'o.order_user_id=u.user_id', 'u');
$srch->addFld('CASE WHEN (d.voucher_valid_till < "' . date('Y-m-d H:i:s') . '"  and cm.cm_status=0) || cm.cm_status=2  THEN 1 ELSE 0 END as expired');
$srch->addMultipleFields(array('od.od_order_id', 'd.deal_id', 'd.deal_name', 'u.user_id', 'o.order_date', 'o.order_payment_mode', 'o.order_payment_status', 'o.order_payment_capture', 'cm.cm_counpon_no', 'cm.cm_status', 'u.user_referral_id', 'od.od_gift_qty', 'od.od_qty', 'u.user_affiliate_id', 'od.od_deal_price', 'od.od_deal_charity_id', 'd.deal_charity_discount_is_percent', 'd.deal_charity_discount'));
$srch->addOrder('o.order_date', 'desc');
$result = $srch->getResultSet();
while ($row = $db->fetch($result)) {
    if ($row['expired'] == 1 && $row['user_referral_id'] > 0) {
        $referAmount = (float) CONF_REFERRER_COMMISSION_PERCENT;
        $rs_first_rf_com = $db->query("select count(*) as total from tbl_referral_history where rh_credited_to = " . intval($row['user_referral_id']) . " and rh_referral_user_id = " . intval($row['user_id']));
        $rs_first_rf_com = $db->fetch($rs_first_rf_com);
        if ($rs_first_rf_com['total'] == 0) {
            $db->insert_from_array('tbl_referral_history', [
                'rh_amount' => $referAmount,
                'rh_credited_to' => $row['user_referral_id'],
                'rh_referral_user_id' => $row['user_id'],
                'rh_transaction_date' => 'mysql_func_now()'
                    ], true);
            if (!$db->insert_id()) {
                $msg->addMsg($db->getError());
            }
            $commission_percent = CONF_REFERRER_COMMISSION_PERCENT;
            $db->query("update tbl_users set user_wallet_amount = user_wallet_amount + " . $commission_percent . " where user_id=" . intval($row['user_referral_id']));
            $db->insert_from_array('tbl_user_wallet_history', [
                'wh_user_id' => $row['user_referral_id'],
                'wh_untipped_deal_id' => $row['deal_id'],
                'wh_particulars' => 'M_TXT_COMMISSION_FOR_ORDERID' . ' ' . $row['od_order_id'],
                'wh_amount' => $commission_percent,
                'wh_time' => 'mysql_func_now()'
                    ], true);
        }
    }
    if ($row['expired'] == 1 && $row['user_affiliate_id'] > 0) {
        $voucher_code = $row['od_order_id'] . $row['cm_counpon_no'];
        if (false === checkIfCommissionAlreadyPaid($voucher_code)) {
            /* Not using quantity because we already are crediting bucks corresponding to VoucherCode and not OrderId */
            //$totalQuantity=$row['od_qty']+$row['od_gift_qty'];
            $commission_to = (int) $row['user_affiliate_id'];
            $rsComm = $db->query("select affiliate_fname,affiliate_lname,affiliate_commission from tbl_affiliate where affiliate_status=1 AND affiliate_id=" . intval($commission_to));
            $rowComm = $db->fetch($rsComm);
            $commission_percent = (float) $rowComm['affiliate_commission'];
            if ($commission_percent > 0) {
                $arr = [
                    'wh_affiliate_id' => $commission_to,
                    'wh_untipped_deal_id' => $row['deal_id'],
                    'wh_particulars' => 'M_TXT_AFFILIATE_COMMISSION_FOR' . ' : ' . $row['deal_name'] . '[To: ' . $rowComm['affiliate_fname'] . ' ' . $rowComm['affiliate_lname'] . ']',
                    'wh_amount' => ($row['od_deal_price'] * $commission_percent / 100),
                    'wh_time' => 'mysql_func_now()',
                    'wh_trans_type' => 'A',
                    'wh_buyer_id' => $row['user_id'],
                    'wh_counpon_no' => $voucher_code
                ];
                $db->insert_from_array('tbl_affiliate_wallet_history', $arr, true);
            }
        }
    }
}
/* Check if Affiliate is already paid for a particular Coupon Number */

function checkIfCommissionAlreadyPaid($voucher_code)
{
    global $db;
    if (empty($voucher_code)) {
        return false;
    }
    $srch = new SearchBase('tbl_affiliate_wallet_history');
    $srch->addCondition('wh_counpon_no', '=', $voucher_code);
    $srch->setPageSize(1);
    $result = $srch->getResultSet();
    if ($row = $db->fetch($result)) {
        return true;
    }
    return false;
}
