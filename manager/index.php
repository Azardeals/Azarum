<?php
require_once dirname(__FILE__) . '/header.php';
$is_admin_for_file_manager = 1;
?>
<script type="text/javascript" charset="utf-8">
    var cancelMsg = '<?php echo addslashes(t_lang('M_MSG_MESSAGE_WHEN_ADMIN_CANCEL_DEAL')); ?>';
</script>
<link rel="stylesheet" type="text/css" href="plugins/jquery.jqplot.css" />
<script language="javascript" type="text/javascript" src="plugins/jquery.jqplot.js"></script>
<script language="javascript" type="text/javascript" src="plugins/jqplot.barRenderer.js"></script>
<script language="javascript" type="text/javascript" src="plugins/jqplot.categoryAxisRenderer.js"></script>
<script language="javascript" type="text/javascript" src="plugins/jqplot.canvasAxisTickRenderer.js"></script>
<script language="javascript" type="text/javascript" src="plugins/jqplot.canvasTextRenderer.js"></script>
<script language="javascript" type="text/javascript" src="plugins/jqplot.pointLabels.js"></script> 
<link href="<?php echo CONF_WEBROOT_URL; ?>css/prettyPhoto.css" rel="stylesheet" type="text/css" />
<script src="<?php echo CONF_WEBROOT_URL; ?>js/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">
    $(document).ready(function () {
        $(" a[rel^='prettyPhoto']").prettyPhoto({theme: 'facebook', social_tools: false});
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
<link href="<?php echo CONF_WEBROOT_URL; ?>css/jquery.qtip.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="<?php echo CONF_WEBROOT_URL; ?>js/jquery.qtip-1.0.0-rc3.js"></script>
<script type="text/javascript" src="<?php echo CONF_WEBROOT_URL; ?>js/jquery.qtip.js"></script>
<script type="text/javascript">
// Create the tooltips only on document load
    $(document).ready(function ()
    {
        // By suppling no content attribute, the library uses each elements title attribute by default
        $('#content a[href][title]').qtip({
            content: {
                text: true // Use each elements title attribute
            },
            style: 'light' // Give it some style
        });
        // NOTE: You can even omit all options and simply replace the regular title tooltips like so:
        // $('#content a[href]').qtip();
    });
</script>
<?php
checkAdminPermission(16);
/* Orders Placed Count 	 */
if ($_REQUEST['city'] > 0) {
    $srch_temp = new SearchBase('tbl_orders', 'o');
    $srch_temp->joinTable('tbl_order_deals', 'INNER JOIN', 'o.order_id=od.od_order_id', 'od');
    $srch_temp->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id=d.deal_id and d.deal_city=' . $_REQUEST['city'], 'd');
    $srch_temp->addGroupBy('o.order_id');
    $srch_temp->doNotCalculateRecords();
    $srch_temp->doNotLimitRecords();
    $qry_tbl = $srch_temp->getQuery();
} else {
    $qry_tbl = 'tbl_orders';
}
$srch = new SearchBase('XXMYTABLEXX', 'o');
$srch->addCondition('o.order_payment_status', '=', 1);
$srch->addFld("SUM(CASE WHEN DATE(order_date) = DATE(NOW()) THEN 1 ELSE 0 END) as today_orders");
$srch->addFld("SUM(CASE WHEN WEEK(order_date) = WEEK(NOW()) AND YEAR(order_date) = YEAR(NOW()) THEN 1 ELSE 0 END) as thisweek_orders");
$srch->addFld("SUM(CASE WHEN DATEDIFF(NOW(), order_date) <= 7 THEN 1 ELSE 0 END) as day7_orders");
$srch->addFld("SUM(CASE WHEN MONTH(order_date) = MONTH(NOW()) AND YEAR(order_date) = YEAR(NOW()) THEN 1 ELSE 0 END) as thismonth_orders");
$prev_month = strtotime("-1 Month");
$srch->addFld("SUM(CASE WHEN MONTH(order_date) = " . date('m', $prev_month) . " AND YEAR(order_date) = " . date('Y', $prev_month) . " THEN 1 ELSE 0 END) as lastmonth_orders");
$srch->addFld("SUM(CASE WHEN DATE(o.order_date) BETWEEN DATE_ADD(NOW(), INTERVAL -3 MONTH) AND NOW() THEN 1 ELSE 0 END) as month3_orders");
$srch->addFld("SUM(CASE WHEN YEAR(o.order_date) = YEAR(NOW()) THEN 1 ELSE 0 END) as thisyear_orders");
$srch->addFld("COUNT(o.order_date) as total_order_count");
$qry = $srch->getQuery();
if ($_REQUEST['city'] > 0) {
    $qry = str_replace('`XXMYTABLEXX`', '(' . $qry_tbl . ')', $qry);
} else {
    $qry = str_replace('`XXMYTABLEXX`', $qry_tbl, $qry);
}
//die($qry);
$orderRs = $db->query("$qry");
$rowOrder = $db->fetch($orderRs);
//$srch->joinTable('tbl_order_deals', 'INNER JOIN', 'o.order_id=od.od_order_id', 'od');
/* Orders Placed Value */
if ($_REQUEST['city'] > 0) {
    $srch_temp1 = new SearchBase('tbl_orders', 'o');
    $srch_temp1->joinTable('tbl_order_deals', 'INNER JOIN', 'o.order_id=od.od_order_id', 'od');
    $srch_temp1->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id=d.deal_id and d.deal_city=' . $_REQUEST['city'], 'd');
    /* $srch_temp1->addGroupBy('o.order_id'); */
    $srch_temp1->doNotCalculateRecords();
    $srch_temp1->doNotLimitRecords();
    $qry_tbl1 = $srch_temp1->getQuery();
} else {
    $qry_tbl1 = 'tbl_orders';
}
$srch1 = new SearchBase('XXMYTABLEXX', 'o');
$srch1->addCondition('o.order_payment_status', '=', 1);
$srch1->joinTable('tbl_order_deals', 'INNER JOIN', 'o.order_id=od.od_order_id', 'od');
if ($_REQUEST['city'] > 0) {
    $srch1->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id=d.deal_id and d.deal_city=' . $_REQUEST['city'], 'd');
} else {
    $srch1->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id=d.deal_id ', 'd');
}
/* $cnd=$srch1->addCondition('d.deal_tipped_at', '!=','0000-00-00 00:00:00'); */
$srch1->addFld("SUM(CASE WHEN DATE(order_date) = DATE(NOW()) THEN (od.od_qty+od.od_gift_qty)*od.od_deal_price ELSE 0 END) as today_orders");
$srch1->addFld("SUM(CASE WHEN DATE(order_date) = DATE(NOW()) THEN (od.od_qty+od.od_gift_qty) ELSE 0 END) as today_orders_vouchers");
$srch1->addFld("SUM(CASE WHEN WEEK(order_date) = WEEK(NOW()) AND YEAR(order_date) = YEAR(NOW()) THEN (od.od_qty+od.od_gift_qty)*od.od_deal_price ELSE 0 END) as thisweek_orders");
$srch1->addFld("SUM(CASE WHEN WEEK(order_date) = WEEK(NOW()) AND YEAR(order_date) = YEAR(NOW()) THEN (od.od_qty+od.od_gift_qty) ELSE 0 END) as thisweek_orders_vouchers");
$srch1->addFld("SUM(CASE WHEN DATEDIFF(NOW(), order_date) <= 7 THEN (od.od_qty+od.od_gift_qty)*od.od_deal_price ELSE 0 END) as day7_orders");
$srch1->addFld("SUM(CASE WHEN DATEDIFF(NOW(), order_date) <= 7 THEN (od.od_qty+od.od_gift_qty) ELSE 0 END) as day7_orders_vouchers");
$srch1->addFld("SUM(CASE WHEN MONTH(order_date) = MONTH(NOW()) AND YEAR(order_date) = YEAR(NOW()) THEN (od.od_qty+od.od_gift_qty)*od.od_deal_price ELSE 0 END) as thismonth_orders");
$srch1->addFld("SUM(CASE WHEN MONTH(order_date) = MONTH(NOW()) AND YEAR(order_date) = YEAR(NOW()) THEN (od.od_qty+od.od_gift_qty) ELSE 0 END) as thismonth_orders_vouchers");
$prev_month = strtotime("-1 Month");
$srch1->addFld("SUM(CASE WHEN MONTH(order_date) = " . date('m', $prev_month) . " AND YEAR(order_date) = " . date('Y', $prev_month) . " THEN (od.od_qty+od.od_gift_qty)*od.od_deal_price ELSE 0 END) as lastmonth_orders");
$srch1->addFld("SUM(CASE WHEN MONTH(order_date) = " . date('m', $prev_month) . " AND YEAR(order_date) = " . date('Y', $prev_month) . " THEN (od.od_qty+od.od_gift_qty) ELSE 0 END) as lastmonth_orders_vouchers");
$srch1->addFld("SUM(CASE WHEN DATE(o.order_date) BETWEEN DATE_ADD(NOW(), INTERVAL -3 MONTH) AND NOW() THEN (od.od_qty+od.od_gift_qty)*od.od_deal_price ELSE 0 END) as month3_orders");
$srch1->addFld("SUM(CASE WHEN DATE(o.order_date) BETWEEN DATE_ADD(NOW(), INTERVAL -3 MONTH) AND NOW() THEN (od.od_qty+od.od_gift_qty) ELSE 0 END) as month3_orders_vouchers");
$srch1->addFld("SUM(CASE WHEN YEAR(o.order_date) = YEAR(NOW()) THEN (od.od_qty+od.od_gift_qty)*od.od_deal_price ELSE 0 END) as thisyear_orders");
$srch1->addFld("SUM(CASE WHEN YEAR(o.order_date) = YEAR(NOW()) THEN (od.od_qty+od.od_gift_qty) ELSE 0 END) as thisyear_orders_vouchers");
$srch1->addFld("SUM(od.od_qty+od.od_gift_qty) as total_orders_vouchers");
$qry1 = $srch1->getQuery();
if ($_REQUEST['city'] > 0) {
    $qry1 = str_replace('`XXMYTABLEXX`', '(' . $qry_tbl1 . ')', $qry1);
} else {
    $qry1 = str_replace('`XXMYTABLEXX`', $qry_tbl1, $qry1);
}
$orderPlacedValueRs = $db->query("$qry1");
$roworderPlacedValue = $db->fetch($orderPlacedValueRs);
/* Vouchers Redeemed */
if ($_REQUEST['city'] > 0) {
    $srch_tempre = new SearchBase('tbl_coupon_mark', 'cm');
    $srch_tempre->addCondition('cm.cm_status', '=', 1);
    $srch_tempre->joinTable('tbl_deals', 'INNER JOIN', 'cm.cm_deal_id=d.deal_id and d.deal_city=' . $_REQUEST['city'], 'd');
    $srch_tempre->doNotCalculateRecords();
    $srch_tempre->doNotLimitRecords();
    $qry_tblre = $srch_tempre->getQuery();
} else {
    $qry_tblre = 'tbl_coupon_mark';
}
$srchre = new SearchBase('XXMYTABLEXX', 'cm');
$srchre->addCondition('cm.cm_status', '=', 1);
$srchre->addFld("SUM(CASE WHEN DATE(cm_redeem_date) = DATE(NOW()) THEN 1 ELSE 0 END) as today_orders_vouchers_redem");
$srchre->addFld("SUM(CASE WHEN WEEK(cm_redeem_date) = WEEK(NOW()) AND YEAR(cm_redeem_date) = YEAR(NOW()) THEN 1 ELSE 0 END) as thisweek_orders_vouchers_redem");
$srchre->addFld("SUM(CASE WHEN DATEDIFF(NOW(), cm_redeem_date) <= 7 THEN 1 ELSE 0 END) as day7_orders_vouchers_redem");
$srchre->addFld("SUM(CASE WHEN MONTH(cm_redeem_date) = MONTH(NOW()) AND YEAR(cm_redeem_date) = YEAR(NOW()) THEN 1 ELSE 0 END) as thismonth_orders_vouchers_redem");
$prev_month = strtotime("-1 Month");
$srchre->addFld("SUM(CASE WHEN MONTH(cm_redeem_date) = " . date('m', $prev_month) . " AND YEAR(cm_redeem_date) = " . date('Y', $prev_month) . " THEN 1 ELSE 0 END) as lastmonth_orders_vouchers_redem");
$srchre->addFld("SUM(CASE WHEN DATE(cm.cm_redeem_date) BETWEEN DATE_ADD(NOW(), INTERVAL -3 MONTH) AND NOW() THEN 1 ELSE 0 END) as month3_orders_vouchers_redem");
$srchre->addFld("SUM(CASE WHEN YEAR(cm.cm_redeem_date) = YEAR(NOW()) THEN 1 ELSE 0 END) as thisyear_orders_vouchers_redem");
$qryre = $srchre->getQuery();
if ($_REQUEST['city'] > 0) {
    $qryre = str_replace('`XXMYTABLEXX`', '(' . $qry_tblre . ')', $qryre);
} else {
    $qryre = str_replace('`XXMYTABLEXX`', $qry_tblre, $qryre);
}
$voucherRedemRs = $db->query("$qryre");
$rowVoucherRedem = $db->fetch($voucherRedemRs);
/* Money Saved Value */
if ($_REQUEST['city'] > 0) {
    $srch_temp2 = new SearchBase('tbl_orders', 'o');
    $srch_temp2->joinTable('tbl_order_deals', 'INNER JOIN', 'o.order_id=od.od_order_id', 'od');
    $srch_temp2->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id=d.deal_id and d.deal_city=' . $_REQUEST['city'], 'd');
    /* $srch_temp2->addGroupBy('o.order_id'); */
    $srch_temp2->doNotCalculateRecords();
    $srch_temp2->doNotLimitRecords();
    $qry_tbl2 = $srch_temp2->getQuery();
} else {
    $qry_tbl2 = 'tbl_orders';
}
$srch2 = new SearchBase('XXMYTABLEXX', 'o');
$srch2->addCondition('o.order_payment_status', '=', 1);
$srch2->joinTable('tbl_order_deals', 'INNER JOIN', 'o.order_id=od.od_order_id', 'od');
if ($_REQUEST['city'] > 0) {
    $srch2->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id=d.deal_id and d.deal_city=' . $_REQUEST['city'], 'd');
} else {
    $srch2->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id=d.deal_id ', 'd');
    /* $cnd=$srch2->addCondition('d.deal_tipped_at', '!=','0000-00-00 00:00:00'); */
}
$srch2->addFld("SUM(CASE WHEN DATE(order_date) = DATE(NOW()) THEN ((od.od_qty+od.od_gift_qty)*(CASE d.`deal_discount_is_percent` WHEN '0'THEN d.`deal_discount`
WHEN '1' THEN d.`deal_original_price` * d.`deal_discount` /100 ELSE 0 END  )) ELSE 0 END) as today_orders_money_saved");
$srch2->addFld("SUM(CASE WHEN WEEK(order_date) = WEEK(NOW()) AND YEAR(order_date) = YEAR(NOW()) THEN (od.od_qty+od.od_gift_qty)*(CASE d.`deal_discount_is_percent` WHEN '0'THEN d.`deal_discount`
WHEN '1' THEN d.`deal_original_price` * d.`deal_discount` /100 ELSE 0 END  ) ELSE 0 END) as thisweek_orders_money_saved");
$srch2->addFld("SUM(CASE WHEN DATEDIFF(NOW(), order_date) <= 7 THEN (od.od_qty+od.od_gift_qty)*(CASE d.`deal_discount_is_percent` WHEN '0'THEN d.`deal_discount`
WHEN '1' THEN d.`deal_original_price` * d.`deal_discount` /100 ELSE 0 END  ) ELSE 0 END) as day7_orders_money_saved");
$srch2->addFld("SUM(CASE WHEN MONTH(order_date) = MONTH(NOW()) AND YEAR(order_date) = YEAR(NOW()) THEN (od.od_qty+od.od_gift_qty)*(CASE d.`deal_discount_is_percent` WHEN '0'THEN d.`deal_discount`
WHEN '1' THEN d.`deal_original_price` * d.`deal_discount` /100 ELSE 0 END  ) ELSE 0 END) as thismonth_orders_money_saved");
$prev_month = strtotime("-1 Month");
$srch2->addFld("SUM(CASE WHEN MONTH(order_date) = " . date('m', $prev_month) . " AND YEAR(order_date) = " . date('Y', $prev_month) . " THEN (od.od_qty+od.od_gift_qty)*(CASE d.`deal_discount_is_percent` WHEN '0'THEN d.`deal_discount`
WHEN '1' THEN d.`deal_original_price` * d.`deal_discount` /100 ELSE 0 END  ) ELSE 0 END) as lastmonth_orders_money_saved");
$srch2->addFld("SUM(CASE WHEN DATE(o.order_date) BETWEEN DATE_ADD(NOW(), INTERVAL -3 MONTH) AND NOW() THEN (od.od_qty+od.od_gift_qty)*(CASE d.`deal_discount_is_percent` WHEN '0'THEN d.`deal_discount`
WHEN '1' THEN d.`deal_original_price` * d.`deal_discount` /100 ELSE 0 END  ) ELSE 0 END) as month3_orders_money_saved");
$srch2->addFld("SUM(CASE WHEN YEAR(o.order_date) = YEAR(NOW()) THEN (od.od_qty+od.od_gift_qty)*(CASE d.`deal_discount_is_percent` WHEN '0'THEN d.`deal_discount`
WHEN '1' THEN d.`deal_original_price` * d.`deal_discount` /100 ELSE 0 END  ) ELSE 0 END) as thisyear_orders_money_saved");
$qry2 = $srch2->getQuery();
if ($_REQUEST['city'] > 0) {
    $qry2 = str_replace('`XXMYTABLEXX`', '(' . $qry_tbl2 . ')', $qry2);
} else {
    $qry2 = str_replace('`XXMYTABLEXX`', $qry_tbl2, $qry2);
}
//echo $qry2;
$moneySavedValueRs = $db->query("$qry2");
$rowmoneySavedValue = $db->fetch($moneySavedValueRs);
/* New Subscribers */
if ($_REQUEST['city'] > 0) {
    $srch_temp3 = new SearchBase('tbl_newsletter_subscription', 'ns');
    $srch_temp3->addCondition('ns.subs_city', '=', $_REQUEST['city']);
    $srch_temp3->doNotCalculateRecords();
    $srch_temp3->doNotLimitRecords();
    $qry_tbl3 = $srch_temp3->getQuery();
} else {
    $qry_tbl3 = 'tbl_newsletter_subscription';
}
$srch3 = new SearchBase('XXMYTABLEXX', 'ns');
$srch3->addFld("SUM(CASE WHEN DATE(subs_addedon) = DATE(NOW()) THEN 1 ELSE 0 END) as today_newsletter_subscribers");
$srch3->addFld("SUM(CASE WHEN WEEK(subs_addedon) = WEEK(NOW()) AND YEAR(subs_addedon) = YEAR(NOW()) THEN 1 ELSE 0 END) as thisweek_newsletter_subscribers");
$srch3->addFld("SUM(CASE WHEN DATEDIFF(NOW(), subs_addedon) <= 7 THEN 1 ELSE 0 END) as day7_newsletter_subscribers");
$srch3->addFld("SUM(CASE WHEN MONTH(subs_addedon) = MONTH(NOW()) AND YEAR(subs_addedon) = YEAR(NOW()) THEN 1 ELSE 0 END) as thismonth_newsletter_subscribers");
$prev_month = strtotime("-1 Month");
$srch3->addFld("SUM(CASE WHEN MONTH(subs_addedon) = " . intval(date('m', $prev_month)) . " AND YEAR(subs_addedon) = " . intval(date('Y', $prev_month)) . " THEN 1 ELSE 0 END) as lastmonth_newsletter_subscribers");
$srch3->addFld("SUM(CASE WHEN DATE(ns.subs_addedon) BETWEEN DATE_ADD(NOW(), INTERVAL -3 MONTH) AND NOW() THEN 1 ELSE 0 END) as month3_newsletter_subscribers");
$srch3->addFld("SUM(CASE WHEN YEAR(ns.subs_addedon) = YEAR(NOW()) THEN 1 ELSE 0 END) as thisyear_newsletter_subscribers");
$srch3->doNotCalculateRecords();
$srch3->doNotLimitRecords();
$qry3 = $srch3->getQuery();
if ($_REQUEST['city'] > 0) {
    $qry3 = str_replace('`XXMYTABLEXX`', '(' . $qry_tbl3 . ')', $qry3);
} else {
    $qry3 = str_replace('`XXMYTABLEXX`', $qry_tbl3, $qry3);
}
$newsLetterRs = $db->query("$qry3");
$newsLetterSub = $db->fetch($newsLetterRs);
/* New Users */
if ($_REQUEST['city'] > 0) {
    $srch_temp4 = new SearchBase('tbl_users', 'u');
    $srch_temp4->addCondition('u.user_city', '=', $_REQUEST['city']);
    $srch_temp4->doNotCalculateRecords();
    $srch_temp4->doNotLimitRecords();
    $qry_tbl4 = $srch_temp4->getQuery();
} else {
    $qry_tbl4 = 'tbl_users';
}
$srch4 = new SearchBase('XXMYTABLEXX', 'u');
$srch4->addCondition('u.user_active', '=', 1);
$srch4->addCondition('u.user_deleted', '=', 0);
$srch4->addFld("SUM(CASE WHEN DATE(user_regdate) = DATE(NOW()) THEN 1 ELSE 0 END) as today_users");
$srch4->addFld("SUM(CASE WHEN WEEK(user_regdate) = WEEK(NOW()) AND YEAR(user_regdate) = YEAR(NOW()) THEN 1 ELSE 0 END) as thisweek_users");
$srch4->addFld("SUM(CASE WHEN DATEDIFF(NOW(), user_regdate) <= 7 THEN 1 ELSE 0 END) as day7_users");
$srch4->addFld("SUM(CASE WHEN MONTH(user_regdate) = MONTH(NOW()) AND YEAR(user_regdate) = YEAR(NOW()) THEN 1 ELSE 0 END) as thismonth_users");
$prev_month = strtotime("-1 Month");
$srch4->addFld("SUM(CASE WHEN MONTH(user_regdate) = " . date('m', $prev_month) . " AND YEAR(user_regdate) = " . date('Y', $prev_month) . " THEN 1 ELSE 0 END) as lastmonth_users");
$srch4->addFld("SUM(CASE WHEN DATE(u.user_regdate) BETWEEN DATE_ADD(NOW(), INTERVAL -3 MONTH) AND NOW() THEN 1 ELSE 0 END) as month3_users");
$srch4->addFld("SUM(CASE WHEN YEAR(u.user_regdate) = YEAR(NOW()) THEN 1 ELSE 0 END) as thisyear_users");
$srch4->addFld("SUM(CASE WHEN (u.user_regdate) THEN 1 ELSE 0 END) as total_users");
$qry4 = $srch4->getQuery();
if ($_REQUEST['city'] > 0) {
    $qry4 = str_replace('`XXMYTABLEXX`', '(' . $qry_tbl4 . ')', $qry4);
} else {
    $qry4 = str_replace('`XXMYTABLEXX`', $qry_tbl4, $qry4);
}
$userRs = $db->query("$qry4");
$usersNew = $db->fetch($userRs);
/* New Deal */
if ($_REQUEST['city'] > 0) {
    $srch_temp5 = new SearchBase('tbl_deals', 'd');
    $srch_temp5->addCondition('d.deal_city', '=', $_REQUEST['city']);
    $srch_temp5->doNotCalculateRecords();
    $srch_temp5->doNotLimitRecords();
    $qry_tbl5 = $srch_temp5->getQuery();
} else {
    $qry_tbl5 = 'tbl_deals';
}
$srch5 = new SearchBase('XXMYTABLEXX', 'd');
$srch5->addCondition('d.deal_complete', '=', 1);
$srch5->addCondition('d.deal_deleted', '=', 0);
$srch5->addFld("SUM(CASE WHEN DATE(deal_addedon) = DATE(NOW()) THEN 1 ELSE 0 END) as today_deal");
$srch5->addFld("SUM(CASE WHEN WEEK(deal_addedon) = WEEK(NOW()) AND YEAR(deal_addedon) = YEAR(NOW()) THEN 1 ELSE 0 END) as thisweek_deal");
$srch5->addFld("SUM(CASE WHEN DATEDIFF(NOW(), deal_addedon) <= 7 THEN 1 ELSE 0 END) as day7_deal");
$srch5->addFld("SUM(CASE WHEN MONTH(deal_addedon) = MONTH(NOW()) AND YEAR(deal_addedon) = YEAR(NOW()) THEN 1 ELSE 0 END) as thismonth_deal");
$prev_month = strtotime("-1 Month");
$srch5->addFld("SUM(CASE WHEN MONTH(deal_addedon) = " . date('m', $prev_month) . " AND YEAR(deal_addedon) = " . date('Y', $prev_month) . " THEN 1 ELSE 0 END) as lastmonth_deal");
$srch5->addFld("SUM(CASE WHEN DATE(d.deal_addedon) BETWEEN DATE_ADD(NOW(), INTERVAL -3 MONTH) AND NOW() THEN 1 ELSE 0 END) as month3_deal");
$srch5->addFld("SUM(CASE WHEN YEAR(d.deal_addedon) = YEAR(NOW()) THEN 1 ELSE 0 END) as thisyear_deal");
$qry5 = $srch5->getQuery();
if ($_REQUEST['city'] > 0) {
    $qry5 = str_replace('`XXMYTABLEXX`', '(' . $qry_tbl5 . ')', $qry5);
} else {
    $qry5 = str_replace('`XXMYTABLEXX`', $qry_tbl5, $qry5);
}
$dealRs = $db->query("$qry5");
$dealNew = $db->fetch($dealRs);
/* Deal Made Public */
if ($_REQUEST['city'] > 0) {
    $srch_temp6 = new SearchBase('tbl_deals', 'd');
    $srch_temp6->addCondition('d.deal_city', '=', $_REQUEST['city']);
    $srch_temp6->doNotCalculateRecords();
    $srch_temp6->doNotLimitRecords();
    $qry_tbl6 = $srch_temp6->getQuery();
} else {
    $qry_tbl6 = 'tbl_deals';
}
$srch6 = new SearchBase('XXMYTABLEXX', 'd');
$srch6->addCondition('d.deal_complete', '=', 1);
$srch6->addCondition('d.deal_deleted', '=', 0);
$srch6->addCondition('d.deal_status', '=', 1);
$srch6->addFld("SUM(CASE WHEN DATE(deal_start_time) = DATE(NOW()) THEN 1 ELSE 0 END) as today_deal");
$srch6->addFld("SUM(CASE WHEN WEEK(deal_start_time) = WEEK(NOW()) AND YEAR(deal_start_time) = YEAR(NOW()) THEN 1 ELSE 0 END) as thisweek_deal");
$srch6->addFld("SUM(CASE WHEN DATEDIFF(NOW(), deal_start_time) <= 7 THEN 1 ELSE 0 END) as day7_deal");
$srch6->addFld("SUM(CASE WHEN MONTH(deal_start_time) = MONTH(NOW()) AND YEAR(deal_start_time) = YEAR(NOW()) THEN 1 ELSE 0 END) as thismonth_deal");
$prev_month = strtotime("-1 Month");
$srch6->addFld("SUM(CASE WHEN MONTH(deal_start_time) = " . date('m', $prev_month) . " AND YEAR(deal_start_time) = " . date('Y', $prev_month) . " THEN 1 ELSE 0 END) as lastmonth_deal");
$srch6->addFld("SUM(CASE WHEN DATE(d.deal_start_time) BETWEEN DATE_ADD(NOW(), INTERVAL -3 MONTH) AND NOW() THEN 1 ELSE 0 END) as month3_deal");
$srch6->addFld("SUM(CASE WHEN YEAR(d.deal_start_time) = YEAR(NOW()) THEN 1 ELSE 0 END) as thisyear_deal");
$qry6 = $srch6->getQuery();
if ($_REQUEST['city'] > 0) {
    $qry6 = str_replace('`XXMYTABLEXX`', '(' . $qry_tbl6 . ')', $qry6);
} else {
    $qry6 = str_replace('`XXMYTABLEXX`', $qry_tbl6, $qry6);
}
$dealActiveRs = $db->query("$qry6");
$dealActive = $db->fetch($dealActiveRs);
/* Deal Made Public */
if ($_REQUEST['city'] > 0) {
    $srch_temp7 = new SearchBase('tbl_affiliate_wallet_history', 'awh');
    $srch_temp7->joinTable('tbl_deals', 'INNER JOIN', 'awh.wh_untipped_deal_id=d.deal_id and d.deal_city=' . $_REQUEST['city'], 'd');
    $srch_temp7->doNotCalculateRecords();
    $srch_temp7->doNotLimitRecords();
    $qry_tbl7 = $srch_temp7->getQuery();
} else {
    $qry_tbl7 = 'tbl_affiliate_wallet_history';
}
$srch7 = new SearchBase('XXMYTABLEXX', 'awh');
$srch7->addFld("SUM(CASE WHEN DATE(wh_time) = DATE(NOW()) THEN wh_amount ELSE 0 END) as today_affiliate");
$srch7->addFld("SUM(CASE WHEN WEEK(wh_time) = WEEK(NOW()) AND YEAR(wh_time) = YEAR(NOW()) THEN wh_amount ELSE 0 END) as thisweek_affiliate");
$srch7->addFld("SUM(CASE WHEN DATEDIFF(NOW(), wh_time) <= 7 THEN wh_amount ELSE 0 END) as day7_affiliate");
$srch7->addFld("SUM(CASE WHEN MONTH(wh_time) = MONTH(NOW()) AND YEAR(wh_time) = YEAR(NOW()) THEN wh_amount ELSE 0 END) as thismonth_affiliate");
$prev_month = strtotime("-1 Month");
$srch7->addFld("SUM(CASE WHEN MONTH(wh_time) = " . date('m', $prev_month) . " AND YEAR(wh_time) = " . date('Y', $prev_month) . " THEN wh_amount ELSE 0 END) as lastmonth_affiliate");
$srch7->addFld("SUM(CASE WHEN DATE(awh.wh_time) BETWEEN DATE_ADD(NOW(), INTERVAL -3 MONTH) AND NOW() THEN wh_amount ELSE 0 END) as month3_affiliate");
$srch7->addFld("SUM(CASE WHEN YEAR(awh.wh_time) = YEAR(NOW()) THEN wh_amount ELSE 0 END) as thisyear_affiliate");
$qry7 = $srch7->getQuery();
if ($_REQUEST['city'] > 0) {
    $qry7 = str_replace('`XXMYTABLEXX`', '(' . $qry_tbl7 . ')', $qry7);
} else {
    $qry7 = str_replace('`XXMYTABLEXX`', $qry_tbl7, $qry7);
}
$affiliateRs = $db->query("$qry7"); //echo $qry7; exit;
$affiliateRow = $db->fetch($affiliateRs);
/* Charity Amount */
if ($_REQUEST['city'] > 0) {
    $srch_temp8 = new SearchBase('tbl_charity_history', 'ch');
    $srch_temp8->joinTable('tbl_deals', 'INNER JOIN', 'ch.ch_deal_id=d.deal_id and d.deal_city=' . $_REQUEST['city'], 'd');
    $srch_temp8->doNotCalculateRecords();
    $srch_temp8->doNotLimitRecords();
    $qry_tbl8 = $srch_temp8->getQuery();
} else {
    $qry_tbl8 = 'tbl_charity_history';
}
$srch8 = new SearchBase('XXMYTABLEXX', 'ch');
$srch8->addFld("SUM(CASE WHEN DATE(ch_time) = DATE(NOW()) THEN (ch_amount -ch_debit) ELSE 0 END) as today_charity");
$srch8->addFld("SUM(CASE WHEN WEEK(ch_time) = WEEK(NOW()) AND YEAR(ch_time) = YEAR(NOW()) THEN (ch_amount -ch_debit) ELSE 0 END) as thisweek_charity");
$srch8->addFld("SUM(CASE WHEN DATEDIFF(NOW(), ch_time) <= 7 THEN (ch_amount -ch_debit) ELSE 0 END) as day7_charity");
$srch8->addFld("SUM(CASE WHEN MONTH(ch_time) = MONTH(NOW()) AND YEAR(ch_time) = YEAR(NOW()) THEN (ch_amount -ch_debit) ELSE 0 END) as thismonth_charity");
$prev_month = strtotime("-1 Month");
$srch8->addFld("SUM(CASE WHEN MONTH(ch_time) = " . date('m', $prev_month) . " AND YEAR(ch_time) = " . date('Y', $prev_month) . " THEN (ch_amount -ch_debit) ELSE 0 END) as lastmonth_charity");
$srch8->addFld("SUM(CASE WHEN DATE(ch.ch_time) BETWEEN DATE_ADD(NOW(), INTERVAL -3 MONTH) AND NOW() THEN (ch_amount -ch_debit) ELSE 0 END) as month3_charity");
$srch8->addFld("SUM(CASE WHEN YEAR(ch.ch_time) = YEAR(NOW()) THEN (ch_amount -ch_debit) ELSE 0 END) as thisyear_charity");
$qry8 = $srch8->getQuery();
if ($_REQUEST['city'] > 0) {
    $qry8 = str_replace('`XXMYTABLEXX`', '(' . $qry_tbl8 . ')', $qry8);
} else {
    $qry8 = str_replace('`XXMYTABLEXX`', $qry_tbl8, $qry8);
}
$charityRs = $db->query("$qry8");
$charityRow = $db->fetch($charityRs);
/* Earning  Amount */
$srch99 = new SearchBase('tbl_deals', 'd');
$srch99->addCondition('deal_deleted', '=', 0);
if ($_REQUEST['city'] > 0) {
    $srch99->addCondition('d.deal_city', '=', $_REQUEST['city']);
}
/* $cnd=$srch99->addCondition('d.deal_tipped_at', '!=','0000-00-00 00:00:00'); */
$srch99->joinTable('tbl_cities', 'INNER JOIN', 'd.deal_city=c.city_id', 'c');
$srch99->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=company.company_id', 'company');
$rs_listingdeal = $srch99->getResultSet();
while ($row = $db->fetch($rs_listingdeal)) {
    $price = $row['deal_original_price'] - (($row['deal_discount_is_percent'] == 1) ? $row['deal_original_price'] * $row['deal_discount'] / 100 : $row['deal_discount']);
    $srch9 = new SearchBase('tbl_order_deals', 'od');
    $srch9->addCondition('od.od_deal_id', '=', $row['deal_id']);
    $srch9->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id=o.order_id', 'o');
    $srch9->addCondition('o.order_payment_status', '=', 1);
    $srch9->addFld("SUM(CASE WHEN DATE(order_date) = DATE(NOW()) THEN (((od.od_qty+od.od_gift_qty)*od.od_deal_price)) ELSE 0 END) as today_earning");
    $srch9->addFld("SUM(CASE WHEN WEEK(order_date) = WEEK(NOW()) AND YEAR(order_date) = YEAR(NOW()) THEN (((od.od_qty+od.od_gift_qty)*od.od_deal_price)) ELSE 0 END) as thisweek_earning");
    $srch9->addFld("SUM(CASE WHEN DATEDIFF(NOW(), order_date) <= 7 THEN (((od.od_qty+od.od_gift_qty)*od.od_deal_price)) ELSE 0 END) as day7_earning");
    $srch9->addFld("SUM(CASE WHEN MONTH(order_date) = MONTH(NOW()) AND YEAR(order_date) = YEAR(NOW()) THEN ((od.od_qty+od.od_gift_qty)*od.od_deal_price) ELSE 0 END) as thismonth_earning");
    $prev_month = strtotime("-1 Month");
    $srch9->addFld("SUM(CASE WHEN MONTH(order_date) = " . date('m', $prev_month) . " AND YEAR(order_date) = " . date('Y', $prev_month) . " THEN ((od.od_qty+od.od_gift_qty)*od.od_deal_price) ELSE 0 END) as lastmonth_earning");
    $srch9->addFld("SUM(CASE WHEN DATE(o.order_date) BETWEEN DATE_ADD(NOW(), INTERVAL -3 MONTH) AND NOW() THEN ((od.od_qty+od.od_gift_qty)*od.od_deal_price) ELSE 0 END) as month3_earning");
    $srch9->addFld("SUM(CASE WHEN YEAR(o.order_date) = YEAR(NOW()) THEN (((od.od_qty+od.od_gift_qty)*od.od_deal_price)) ELSE 0 END) as thisyear_earning");
    $srch9->addFld("SUM(((od.od_qty+od.od_gift_qty)*od.od_deal_price)) as total_earning");
    $qry9 = $srch9->getQuery();
    $earningRs = $db->query("$qry9");
    $rowearning = $db->fetch($earningRs);
    $todaycommission = $rowearning['today_earning'] * $row['deal_commission_percent'] / 100;
    if ($todaycommission > 0) {
        $today_earning += ($todaycommission + $row['deal_bonus'] );
    } else {
        $today_earning += 0;
    }
    $thisweekcommission = $rowearning['thisweek_earning'] * $row['deal_commission_percent'] / 100;
    if ($thisweekcommission > 0) {
        $thisweek_earning += ($thisweekcommission + $row['deal_bonus'] );
    } else {
        $thisweek_earning += 0;
    }
    $day7commission = $rowearning['day7_earning'] * $row['deal_commission_percent'] / 100;
    if ($day7commission > 0) {
        $day7_earning += ($day7commission + $row['deal_bonus'] );
    } else {
        $day7_earning += 0;
    }
    $thismonthcommission = $rowearning['thismonth_earning'] * $row['deal_commission_percent'] / 100;
    if ($thismonthcommission > 0) {
        $thismonth_earning += ($thismonthcommission + $row['deal_bonus'] );
    } else {
        $thismonth_earning += 0;
    }
    $lastmonthcommission = $rowearning['lastmonth_earning'] * $row['deal_commission_percent'] / 100;
    if ($lastmonthcommission > 0) {
        $lastmonth_earning += ($lastmonthcommission + $row['deal_bonus'] );
    } else {
        $lastmonth_earning += 0;
    }
    $month3commission = $rowearning['month3_earning'] * $row['deal_commission_percent'] / 100;
    if ($month3commission > 0) {
        $month3_earning += ($month3commission + $row['deal_bonus'] );
    } else {
        $month3_earning += 0;
    }
    $thisyearcommission = $rowearning['thisyear_earning'] * $row['deal_commission_percent'] / 100;
    if ($thisyearcommission > 0) {
        $thisyear_earning += ($thisyearcommission + $row['deal_bonus'] );
    } else {
        $thisyear_earning += 0;
    }
    $total_earning_commission = $rowearning['total_earning'] * $row['deal_commission_percent'] / 100;
    if ($total_earning_commission > 0) {
        $total_earning += ($total_earning_commission + $row['deal_bonus'] );
    } else {
        $total_earning += 0;
    }
}
/* Earning  Amount */
?>
<?php
if ($_GET['mode'] == 'cancel') {
    ?>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#full_summary').html('<div align="center"><div class="loader"><img align="center" src="images/ajax-loader10.gif" alt="Loading..."><br>Loading data...</div></div>');
            //regStatus		-- For Student Registration
            callAjax('index-ajax.php', 'mode=alldealPurchased', function (t) {
                $('#full_summary').addClass('index-ajax');
                $('#full_summary').html(t);
            });
            $('li').children().removeClass('selected');
        });
    </script>
    <?php
}
$post = getPostedData();
if (count($post) > 0 || $_REQUEST['refund'] != "") {
    if ($_REQUEST['page'] > 0) {
        $page = $_REQUEST['page'];
    }
    if ($post['page'] > 0) {
        $page = $post['page'];
    }
    ?>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#full_summary').html('<div align="center"><div class="loader"><img align="center" src="images/ajax-loader10.gif" alt="Loading..."><br>Loading data...</div></div>');
            callAjax('index-ajax.php', 'mode=alldealPurchased&company_name=<?php echo rawurlencode($post['company_name']); ?>&deal_name=<?php echo rawurlencode($post['deal_name']); ?>&user_name=<?php echo rawurlencode($post['user_name']); ?>&order_id=<?php echo rawurlencode($post['order_id']); ?>&user_email=<?php echo rawurlencode($post['user_email']); ?>&page=<?php echo rawurlencode($page); ?>&mode1=<?php echo rawurlencode($post['mode1']); ?>&refund=<?php echo rawurlencode($_REQUEST['refund']); ?>', function (t) {
                $('#full_summary').addClass('index-ajax');
                $('#full_summary').html(t);
            });
            $('li').children().removeClass('selected'); //remove all selected
        });
    </script>
<?php }
?>
<?php if (checkAdminAddEditDeletePermission(5, '', 'add')) { ?>
    <ul id="content" class="nav-left-ul">
        <li> <a onclick="totalCoupon('puchased');$(this).addClass('selected');" href="javascript:void(0)" title=" <?php echo t_lang('M_TXT_TOTAL_COUPON_TOOLTIP'); ?>" ><?php echo t_lang('M_FRM_TOTAL_COUPON'); ?></a> </li>
        <li> <a onclick="dealPurchased('dealPurchased');$(this).addClass('selected');" href="javascript:void(0)" class="" title="<?php echo t_lang('M_TXT_DEAL_PURCHASED_TOOLTIP'); ?>"> <?php echo t_lang('M_TXT_DEAL_PURCHASED'); ?></a> </li>
        <li> <a onclick="dealPurchased('alldealPurchased');$(this).addClass('selected');" href="javascript:void(0)" class="" title="<?php echo t_lang('M_TXT_ALL_DEAL_PURCHASED_TOOLTIP'); ?>"><?php echo t_lang('M_TXT_ALL_DEAL_PURCHASED'); ?></a> </li>
        <li> <a onclick="dealExpire('dealExpire');$(this).addClass('selected');" href="javascript:void(0)" class="" title="<?php echo t_lang('M_TXT_DEAL_EXPIRED_TOOLTIP'); ?>"> <?php echo t_lang('M_TXT_TODAY_EXPIRED_DEALS'); ?></a> </li>
        <li> <a onclick="totalSavedByMerchant('savedByMerchant');$(this).addClass('selected');" href="javascript:void(0)" class="" title="<?php echo t_lang('M_TXT_ACTUAL_COUPON_SAVED_TOOLTIP'); ?>"><?php echo t_lang('M_TXT_ACTUAL_COUPON_SAVED'); ?>  </a> </li>
    </ul>
    </div></td>
<?php } ?>
<td class="right-portion">
    <div class="div-inline ">
        <div class="page-name"> <?php echo t_lang('M_TXT_DASHBOARD'); ?> </div>
    </div>
    <div class="clear"></div>
    <div class="" id="full_summary" >  
        <div class="row">
            <ul class="cellgrid">
                <li>
                    <div class="flipbox green">
                        <div class="flipper">
                            <div class="front">
                                <div class="iconbox">
                                    <figure class="icon"><img alt="" src="<?php echo CONF_WEBROOT_URL; ?>manager/images/box_icon1.png"></figure>
                                    <span class="value"><span><?php echo t_lang('M_TXT_NEW_USERS'); ?></span><?php echo(($usersNew['thismonth_users'] == 0) ? '0' : $usersNew['thismonth_users']); ?></span>
                                </div>
                            </div>
                            <div class="back">
                                <div class="cell">
                                    <div class="group">
                                        <div class="col-sm-6"><span><?php echo t_lang('M_TXT_TOTAL_USERS'); ?></span><?php echo (($usersNew['total_users'] == NULL) ? '0' : $usersNew['total_users']); ?></div>
                                        <div class="col-sm-6"><span><?php echo t_lang('M_TXT_LAST_MONTH'); ?></span><?php echo (($usersNew['lastmonth_users'] == NULL) ? '0' : $usersNew['lastmonth_users']); ?></div>
                                    </div>
                                    <a class="themebtn btn-default btn-sm" href="<?php echo CONF_WEBROOT_URL . 'manager/registered-members.php' ?>"><?php echo t_lang('M_TXT_VIEW_SUMMARY'); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="flipbox orange">
                        <div class="flipper">
                            <div class="front">
                                <div class="iconbox">
                                    <figure class="icon"><img alt="" src="<?php echo CONF_WEBROOT_URL; ?>manager/images/box_icon2.png"></figure>
                                    <span class="value"><span><?php echo t_lang('M_TXT_VOUCHER_SOLD'); ?></span><?php echo(( $roworderPlacedValue['thismonth_orders_vouchers'] == NULL) ? '0' : $roworderPlacedValue['thismonth_orders_vouchers']) ?></span>
                                </div>
                            </div>
                            <div class="back">
                                <div class="cell">
                                    <div class="group">
                                        <div class="col-sm-6"><span><?php echo t_lang('M_TXT_TOTAL_VOUCHER_SOLD'); ?></span><?php echo(( $roworderPlacedValue['total_orders_vouchers'] == NULL) ? '0' : $roworderPlacedValue['total_orders_vouchers']); ?></div>
                                        <div class="col-sm-6"><span><?php echo t_lang('M_TXT_LAST_MONTH'); ?></span><?php echo (($roworderPlacedValue['lastmonth_orders_vouchers'] == NULL) ? '0' : $roworderPlacedValue['lastmonth_orders_vouchers']) ?></div>
                                    </div>
                                    <a class="themebtn btn-default btn-sm" href="<?php echo CONF_WEBROOT_URL . 'manager/tipped-members.php' ?>"><?php echo t_lang('M_TXT_VIEW_SUMMARY'); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="flipbox purple">
                        <div class="flipper">
                            <div class="front">
                                <div class="iconbox">
                                    <figure class="icon"><img alt="" src="<?php echo CONF_WEBROOT_URL; ?>manager/images/box_icon3.png"></figure>
                                    <span class="value"><span><?php echo t_lang('M_TXT_NEW_ORDERS'); ?></span><?php echo (($rowOrder['thismonth_orders'] == NULL) ? '0' : $rowOrder['thismonth_orders']) ?></span>
                                </div>
                            </div>
                            <div class="back">
                                <div class="cell">
                                    <div class="group">
                                        <div class="col-sm-6"><span><?php echo t_lang('M_TXT_TOTAL_ORDERS'); ?></span><?php echo (($rowOrder['total_order_count'] == NULL) ? '0' : $rowOrder['total_order_count']) ?></div>
                                        <div class="col-sm-6"><span><?php echo t_lang('M_TXT_LAST_MONTH'); ?></span><?php echo (($rowOrder['lastmonth_orders'] == NULL) ? '0' : $rowOrder['lastmonth_orders']) ?></div>
                                    </div>
                                    <a class="themebtn btn-default btn-sm" href="<?php echo CONF_WEBROOT_URL . 'manager/tipped-members.php' ?>"><?php echo t_lang('M_TXT_VIEW_SUMMARY'); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="flipbox darkgreen">
                        <div class="flipper">
                            <div class="front">
                                <div class="iconbox">
                                    <figure class="icon"><img alt="" src="<?php echo CONF_WEBROOT_URL; ?>manager/images/box_icon4.png"></figure>
                                    <span class="value"><span><?php echo t_lang('M_TXT_EARNINGS'); ?></span><?php echo CONF_CURRENCY . number_format($thismonth_earning, 2); ?></span>
                                </div>
                            </div>
                            <div class="back">
                                <div class="cell">
                                    <div class="group">
                                        <div class="col-sm-6"><span><?php echo t_lang('M_TXT_TOTAL_EARNINGS'); ?></span><?php echo CONF_CURRENCY . number_format($total_earning, 2); ?></div>
                                        <div class="col-sm-6"><span><?php echo t_lang('M_TXT_LAST_MONTH'); ?></span><?php echo CONF_CURRENCY . number_format($lastmonth_earning, 2); ?></div>
                                    </div>
                                    <a class="themebtn btn-default btn-sm" href="<?php echo CONF_WEBROOT_URL . 'manager/tipped-members.php' ?>"><?php echo t_lang('M_TXT_VIEW_SUMMARY'); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
            <!--<div class="col-sm-12">  
              <section class="section">
                  <div class="sectionhead"><h4>Sales Statistics </h4></div>
                  <div class="sectionbody">
                      <div class="chartwrap">
                        <img alt="" src="images/graph.png">
                      </div>
                  </div>
              </section>
            </div> -->
            <div class="fourcols"> 
                <?php
                $srch = new SearchBase('tbl_deals', 'd');
                $srch->addCondition('deal_deleted', '=', 0);
                $srch->joinTable('tbl_cities', 'INNER JOIN', 'd.deal_city=c.city_id', 'c');
                $srch->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=company.company_id', 'company');
                $srch->addMultipleFields(array('d.*', 'c.*', 'company.*'));
                $srch->addCondition('deal_status', '=', 5);
                $srch->addCondition('d.deal_complete', '=', 1);
                $srch->addOrder('c.city_name', 'asc');
                $srch->addOrder('d.deal_start_time', 'desc');
                $srch->addOrder('d.deal_status');
                $srch->addOrder('d.deal_name');
                $rs_listing = $srch->getResultSet();
                $pendingApproval = $srch->recordCount($rs_listing);
                ?>
                <div class="col-sm-3">
                    <div class="coloredbox whitewrap org">
                        <div class="top">
                            <span class="txtsmall"><?php echo t_lang('M_TXT_DEAL_PENDING_APPROVAL'); ?></span>
                            <i class="icon ion-pricetags"></i>
                        </div>
                        <div class="body">
                            <h3><?php echo $pendingApproval; ?> <span> <?php echo t_lang('M_TXT_PENDING_APPROVAL'); ?></span></h3>
                            <a href="<?php echo CONF_WEBROOT_URL . 'manager/deals.php?status=un-approval' ?>" class="themebtn btn-sm"><?php echo t_lang('M_TXT_VIEW_SUMMARY'); ?></a>
                        </div>
                    </div>
                </div>  
                <?php
                $srch = new SearchBase('tbl_reviews', 'c');
                $srch->addCondition('reviews_approval', '=', 0);
                $srch->addDirectCondition('reviews_added_on> DATE_SUB(NOW(), INTERVAL 1 WEEK)');
                $srch->addOrder('reviews_id', 'desc');
                $rs_listing = $srch->getResultSet();
                $weekly_review = $srch->recordCount($rs_listing);
                ?>
                <div class="col-sm-3">
                    <div class="coloredbox whitewrap green">
                        <div class="top">
                            <span class="txtsmall"><?php echo t_lang('M_TXT_REVIEWS_AND_RATINGS'); ?></span>
                            <i class="icon ion-chatboxes"></i>
                        </div>
                        <div class="body">
                            <h3><?php echo $weekly_review; ?> <span><?php echo t_lang('M_TXT_COMMENT_THIS_WEEK'); ?></span></h3>
                            <a href="<?php echo CONF_WEBROOT_URL . 'manager/pending-reviews.php' ?>" class="themebtn btn-sm"><?php echo t_lang('M_TXT_VIEW_SUMMARY'); ?></a>
                        </div>
                    </div>
                </div> 
                <?php
                $srch = new SearchBase('tbl_cities', 'c');
                $srch->addCondition('city_request', '=', 1);
                $srch->addCondition('city_deleted', '=', 0);
                $srch->joinTable('tbl_states', 'INNER JOIN', 'c.city_state=s.state_id', 's');
                $srch->joinTable('tbl_countries', 'INNER JOIN', 's.state_country=country.country_id', 'country');
                $srch->addMultipleFields(array('c.*', 'country.country_name' . $_SESSION['lang_fld_prefix'], 's.state_name' . $_SESSION['lang_fld_prefix']));
                $srch->addOrder('city_name' . $_SESSION['lang_fld_prefix']);
                $rs_listing = $srch->getResultSet();
                $cityRequest = $srch->recordCount($rs_listing);
                ?>
                <div class="col-sm-3">
                    <div class="coloredbox blue whitewrap">
                        <div class="top">
                            <span class="txtsmall"><?php echo t_lang('M_TXT_CITIES_REQUESTED_TO_ADD'); ?></span>
                            <span class="svgicon">
                                <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                     width="60.272px" height="60.272px" viewBox="0 0 60.272 60.272" style="enable-background:new 0 0 60.272 60.272;"
                                     xml:space="preserve">
                                <g>
                                <polygon fill="#fff" points="31.577,1.863 44.039,10.002 44.039,58.579 31.577,58.579 	"/>
                                <path fill="#fff" d="M45.015,36.875v21.449h15.258V41.749L45.015,36.875z M51.457,53.661l-4.662-1.357V48.28l4.662,1.692V53.661z
                                      M51.457,46.624l-4.662-1.358v-4.022l4.662,1.694V46.624z M57.306,55.354l-4.664-1.355v-4.026l4.664,1.695V55.354z M57.306,48.318
                                      l-4.663-1.356v-4.024l4.665,1.692v3.688H57.306z"/>
                                <path fill="#fff" d="M14.75,11.527v32.208l-2.203,1.439V34.417l-1.398,0.578v-10.73L0.974,28.757V39.21L0,39.614v18.965h30.154V1.694
                                      L14.75,11.527z M1.907,30.804l3.434-1.589v4.132l-3.434,1.526V30.804z M2.162,37.501l3.433-1.591v4.135l-3.433,1.524V37.501z
                                      M5.699,52.345l-3.433,1.528v-4.071l3.433-1.589V52.345z M5.699,46.049l-3.433,1.529v-4.071l3.433-1.588V46.049z M6.188,28.812
                                      l3.434-1.589v4.133l-3.434,1.525V28.812z M6.443,35.508l3.433-1.589v4.133l-3.433,1.527V35.508z M10.278,50.376l-3.434,1.527
                                      v-4.069l3.434-1.591V50.376z M10.278,44.081l-3.434,1.525v-4.067l3.434-1.59V44.081z M21.277,44.797l-4.749,2.973v-4.408
                                      l4.749-3.072V44.797z M21.277,37.349l-4.749,2.971V35.91l4.749-3.072V37.349z M21.277,29.899l-4.749,2.974v-4.411l4.749-3.074
                                      V29.899z M21.277,22.45l-4.749,2.97v-4.408l4.749-3.074V22.45z M21.277,14.999l-4.749,2.972v-4.409l4.749-3.073V14.999z
                                      M28.313,40.395l-5.51,3.448V39.3l5.51-3.566V40.395z M28.313,32.945l-5.51,3.449v-4.544l5.51-3.568V32.945z M28.313,25.495
                                      l-5.51,3.449v-4.542l5.51-3.569V25.495z M28.313,18.045l-5.51,3.449v-4.543l5.51-3.568V18.045z M28.313,10.594l-5.51,3.449V9.5
                                      l5.51-3.568V10.594z"/>
                                </g>
                                </svg>
                            </span>	
                        </div>
                        <div class="body">
                            <h3><?php echo $cityRequest; ?> <span> <?php echo t_lang('M_TXT_CITIES_REQUESTED_TO_ADD'); ?></span></h3>
                            <a href="<?php echo CONF_WEBROOT_URL . 'manager/cities.php?status=requested' ?>" class="themebtn btn-sm"><?php echo t_lang('M_TXT_VIEW_SUMMARY'); ?></a>
                        </div>
                    </div>
                </div> 
                <?php
                $srch = new SearchBase('tbl_company_charity', 'c');
                $srch->addCondition('charity_status', '=', 2);
                $srch->addOrder('charity_name' . $_SESSION['lang_fld_prefix']);
                $rs_listing = $srch->getResultSet();
                $charityRequested = $srch->recordCount($rs_listing);
                ?>
                <div class="col-sm-3">
                    <div class="coloredbox yellow whitewrap">
                        <div class="top">
                            <span class="txtsmall"><?php echo t_lang('M_TXT_CHARITY_REQUESTED_TO_ADD'); ?></span>
                            <span class="svgicon">
                                <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                     width="435.047px" height="435.047px" viewBox="0 0 435.047 435.047" style="enable-background:new 0 0 435.047 435.047;"
                                     xml:space="preserve">
                                <g>
                                <g>
                                <path fill="#fff" d="M202.011,205.437c-6.501-2.273-9.219-3.666-9.219-6.391c0-4.857,5.021-6.143,7.679-6.143c5.097,0,6.143,2.068,6.143,5.172
                                      v0.242c0,2.232,1.81,4.043,4.039,4.043h1.041c2.23,0,4.039-1.807,4.041-4.039l0.006-7.01c0-1.133-0.475-2.215-1.308-2.98
                                      c-2.535-2.326-5.821-3.617-10.003-3.924v-4.598c0-2.232-1.811-4.041-4.039-4.041h-0.974c-2.229,0-4.04,1.809-4.04,4.041v4.918
                                      c-6.099,1.684-11.879,7.248-11.879,14.643c0,8.82,7.777,11.482,14.64,13.83c7.115,2.438,10.091,4.021,10.091,7.508
                                      c0,5.041-5.234,6.386-8.326,6.386c-3.125,0-6.991-0.892-8.036-2.361c-0.457-0.654-0.765-1.926-0.915-3.785
                                      c-0.172-2.098-1.923-3.715-4.027-3.715h-0.821c-1.079,0-2.11,0.43-2.871,1.198c-0.758,0.764-1.18,1.799-1.17,2.877l0.063,7.948
                                      c0.013,1.802,1.216,3.371,2.95,3.858c0.175,0.05,0.408,0.103,0.644,0.154l0.192,0.041c3.603,1.006,6.862,1.877,9.467,2.207v6.691
                                      c0,2.23,1.811,4.039,4.04,4.039h0.974c2.229,0,4.039-1.809,4.039-4.039v-6.838c6.717-1.412,13.094-6.979,13.094-14.824
                                      C217.523,210.867,208.922,207.857,202.011,205.437z"/>
                                <path fill="#fff" d="M410.734,74.918V0c-21.198,9.292-61.076,22.585-79.635,23.641c-18.56,1.056-105.775-12.639-129.403-6.725
                                      c-19.016,4.76-123.429,83.334-154.574,105.002c-49.723,34.593-5.72,40.138,15.158,32.136
                                      c21.008-8.052,88.712-45.871,88.712-45.871c-0.214,7.662,0.944,18.508,7.797,30.219c0,0,7.75,10.864,17.037,21.499
                                      c-20.04,9.137-34.006,29.351-34.006,52.773c0,19.896,10.078,37.48,25.396,47.926H67.012c-5.549,0-10.048,4.498-10.048,10.047
                                      v154.35c0,5.553,4.499,10.051,10.048,10.051h265.562c5.548,0,10.047-4.498,10.047-10.051v-154.35
                                      c0-5.549-4.499-10.047-10.047-10.047H232.371c15.316-10.445,25.395-28.029,25.395-47.926c0-14.541-5.394-27.838-14.27-38.025
                                      c-0.106-0.089-0.2-0.186-0.289-0.284c14.482-7.218,29.704-20.151,66.559-35.225C371.839,113.751,392.863,84.917,410.734,74.918z
                                      M239.722,133.696c-11.633,8.212-19.572,16.233-24.203,23.19c-2.611-0.737-5.295-1.295-8.039-1.664
                                      c-2.399-7.765-6.52-17.979-12.799-31.504c0,0-2.882-6.779-1.932-14.885c2.37-9.058,10.55-18.772,33.664-21.755
                                      c38.92-5.022,44.443,14.654,26.855,35.14C249.515,126.107,244.982,129.983,239.722,133.696z M245.2,284.544
                                      c5.521,0,10.014,4.49,10.014,10.013c0,5.52-4.491,10.012-10.014,10.012h-90.814c-5.521,0-10.012-4.492-10.012-10.012
                                      c0-5.521,4.491-10.013,10.012-10.013H245.2z M199.793,255.376c-23.547,0-42.705-19.155-42.705-42.703
                                      c0-23.547,19.158-42.703,42.705-42.703c3.474,0,6.847,0.426,10.081,1.213c-0.005,6.768,5.262,10.629,13.948,9.771
                                      c1.334-0.131,2.628-0.316,3.893-0.551c9.042,7.838,14.78,19.393,14.78,32.27C242.496,236.221,223.34,255.376,199.793,255.376z"/>
                                </g>
                                </g>
                                </svg>
                            </span>	
                        </div>
                        <div class="body">
                            <h3><?php echo $charityRequested; ?> <span> <?php echo t_lang('M_TXT_CHARITY_REQUESTED_TO_ADD'); ?></span></h3>
                            <a href="<?php echo CONF_WEBROOT_URL . 'manager/charity.php?status=un-approved' ?>" class="themebtn btn-sm"><?php echo t_lang('M_TXT_VIEW_SUMMARY'); ?></a>
                        </div>
                    </div>
                </div> 
            </div>    
        </div>
        <script type="text/javascript">
            $('#city_selector').live("change", function () {
                window.location = "index.php?city=" + $(this).find("option:selected").val();
            });
            var txtoops = "<?php echo addslashes(t_lang('M_TXT_INTERNAL_ERROR')); ?>";
        </script>
        <?php
        /** get cities from db * */
        $srch_cities = new SearchBase('tbl_cities', 'c');
        $srch_cities->addCondition('city_deleted', '=', '0');
        $srch_cities->addCondition('city_active', '=', '1');
        $srch_cities->addCondition('city_request', '=', '0');
        $srch_cities->addOrder('city_name' . $_SESSION['lang_fld_prefix'], 'asc');
        $city_listing = $srch_cities->getResultSet();
        $countCity = 0;
        $cities_arr = [];
        while ($city_row = $db->fetch($city_listing)) {
            $cities_arr[$city_row['city_id']] = $city_row['city_name' . $_SESSION['lang_fld_prefix']];
            $countCity++;
            if ($countCity == 1) {
                $city = $city_row['city_id'];
            }
        }
        ?>	
        <div class="box"  > <div class="title"> <?php echo t_lang('M_TXT_STATISTICS'); ?>  
                <div class="select-cities">
                    <select name="city" id="city_selector">
                        <option value=""> <?php echo t_lang('M_TXT_SELECT_CITY'); ?></option>
                        <?php
                        foreach ($cities_arr as $key => $val) {
                            if ($_REQUEST['city'] == $key) {
                                $selected = 'selected="selected"';
                            } else {
                                $selected = '';
                            }
                            echo '<option value="' . $key . '" ' . $selected . '>' . $val . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="content">
                <table class="tbl_data" width="100%" id="content">
                    <thead>
                        <tr>
                            <th >&nbsp;</th>
                            <th><?php echo t_lang('M_TXT_TODAY'); ?></th>
                            <th><?php echo t_lang('M_TXT_THIS_WEEK'); ?></th>
                            <th><?php echo t_lang('M_TXT_LAST_7_DAYS'); ?></th>
                            <th><?php echo t_lang('M_TXT_THIS_MONTH'); ?></th>
                            <th><?php echo t_lang('M_TXT_PREVIOUS_MONTH'); ?></th>
                            <th><?php echo t_lang('M_TXT_LAST_3_MONTHS'); ?></th>
                            <th><?php echo t_lang('M_TXT_THIS_YEAR'); ?></th>
                        </tr> 
                    </thead>
                    <tr><th><?php echo t_lang('M_TXT_ORDER_PLACED_COUNT'); ?> <a href="javascript:void(0);"  class="fr" title=" <?php echo t_lang('M_TXT_ORDER_PLACED_COUNT_TEXT'); ?> <?php echo CONF_SITE_NAME; ?>">[?]</a></th><?php echo '<td>' . (($rowOrder['today_orders'] == NULL) ? '0' : $rowOrder['today_orders']) . '</td><td>' . (($rowOrder['thisweek_orders'] == NULL) ? '0' : $rowOrder['thisweek_orders']) . '</td><td>' . (($rowOrder['day7_orders'] == NULL) ? '0' : $rowOrder['day7_orders']) . '</td><td>' . (($rowOrder['thismonth_orders'] == NULL) ? '0' : $rowOrder['thismonth_orders']) . '</td><td>' . (($rowOrder['lastmonth_orders'] == NULL) ? '0' : $rowOrder['lastmonth_orders']) . '</td><td>' . (($rowOrder['month3_orders'] == NULL) ? '0' : $rowOrder['month3_orders']) . '</td><td>' . (($rowOrder['thisyear_orders'] == NULL) ? '0' : $rowOrder['thisyear_orders']) . '</td>'; ?></tr>
                    <tr><th><?php echo t_lang('M_TXT_ORDER_PLACED_VALUE'); ?> <a href="javascript:void(0);" class="fr" onmouseout ="hideInfo();" onmouseover="displayInfo(' <?php echo t_lang('M_TXT_ORDER_PLACED_VALUE_TEXT'); ?>  <?php echo CONF_SITE_NAME; ?>');" title=" <?php echo t_lang('M_TXT_ORDER_PLACED_VALUE_TEXT'); ?>  <?php echo CONF_SITE_NAME; ?>">[?]</a></th><?php echo '<td>' . CONF_CURRENCY . (($roworderPlacedValue['today_orders'] == NULL) ? '0.00' : $roworderPlacedValue['today_orders']) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . (($roworderPlacedValue['thisweek_orders'] == NULL) ? '0.00' : $roworderPlacedValue['thisweek_orders']) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . (($roworderPlacedValue['day7_orders'] == NULL) ? '0.00' : $roworderPlacedValue['day7_orders']) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . (($roworderPlacedValue['thismonth_orders'] == NULL) ? '0.00' : $roworderPlacedValue['thismonth_orders']) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . (($roworderPlacedValue['lastmonth_orders'] == NULL) ? '0.00' : $roworderPlacedValue['lastmonth_orders']) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . (($roworderPlacedValue['month3_orders'] == NULL) ? '0.00' : $roworderPlacedValue['month3_orders']) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . (($roworderPlacedValue['thisyear_orders'] == NULL) ? '0.00' : $roworderPlacedValue['thisyear_orders']) . CONF_CURRENCY_RIGHT . '</td>'; ?></tr>
                    <tr><th><?php echo t_lang('M_TXT_AVERAGE_ORDER_VALUE'); ?>  <a href="javascript:void(0);" onmouseout ="hideInfo();" onmouseover="displayInfo('<?php echo t_lang('M_TXT_AVERAGE_ORDER_VALUE_TEXT'); ?>');" class="fr" title=" <?php echo t_lang('M_TXT_AVERAGE_ORDER_VALUE_TEXT'); ?>">[?]</a></th>
                        <?php echo '<td>' . CONF_CURRENCY . (($roworderPlacedValue['today_orders'] == NULL || $rowOrder['today_orders'] == NULL) ? '0.00' : number_format(is_nan($roworderPlacedValue['today_orders'] / $rowOrder['today_orders']) ? '0.00' : ($roworderPlacedValue['today_orders'] / $rowOrder['today_orders']), 2)) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . ( ($roworderPlacedValue['thisweek_orders'] == NULL || $rowOrder['thisweek_orders'] == NULL) ? '0.00' : number_format(is_nan($roworderPlacedValue['thisweek_orders'] / $rowOrder['thisweek_orders']) ? '0.00' : ($roworderPlacedValue['thisweek_orders'] / $rowOrder['thisweek_orders']), 2)) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . (($roworderPlacedValue['day7_orders'] == NULL) ? '0.00' : number_format(is_nan($roworderPlacedValue['day7_orders'] / $rowOrder['day7_orders']) ? '0.00' : ($roworderPlacedValue['day7_orders'] / $rowOrder['day7_orders']), 2)) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . (($roworderPlacedValue['thismonth_orders'] == NULL) ? '0.00' : number_format(is_nan($roworderPlacedValue['thismonth_orders'] / $rowOrder['thismonth_orders']) ? '0.00' : ($roworderPlacedValue['thismonth_orders'] / $rowOrder['thismonth_orders']), 2)) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . (($roworderPlacedValue['lastmonth_orders'] == NULL) ? '0.00' : number_format(is_nan($roworderPlacedValue['lastmonth_orders'] / $rowOrder['lastmonth_orders']) ? '0.00' : $roworderPlacedValue['lastmonth_orders'] / $rowOrder['lastmonth_orders'], 2)) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . ( ($roworderPlacedValue['month3_orders'] == NULL) ? '0.00' : number_format(is_nan($roworderPlacedValue['month3_orders'] / $rowOrder['month3_orders']) ? '0.00' : ($roworderPlacedValue['month3_orders'] / $rowOrder['month3_orders']), 2)) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . (($roworderPlacedValue['thisyear_orders'] == NULL) ? '0.00' : number_format(is_nan($roworderPlacedValue['thisyear_orders'] / $rowOrder['thisyear_orders']) ? '0.00' : ($roworderPlacedValue['thisyear_orders'] / $rowOrder['thisyear_orders']), 2)) . CONF_CURRENCY_RIGHT . '</td>'; ?></tr>
                    <tr><th><?php echo t_lang('M_TXT_VOUCHER_SOLD'); ?>  <a href="javascript:void(0);" class="fr" onmouseout ="hideInfo();" onmouseover="displayInfo('<?php echo t_lang('M_TXT_VOUCHER_SOLD_TEXT'); ?> <?php echo CONF_SITE_NAME; ?>');" title="<?php echo t_lang('M_TXT_VOUCHER_SOLD_TEXT'); ?> <?php echo CONF_SITE_NAME; ?>">[?]</a></th><?php echo '<td>' . (($roworderPlacedValue['today_orders_vouchers'] == NULL) ? '0' : $roworderPlacedValue['today_orders_vouchers']) . '</td><td>' . (($roworderPlacedValue['thisweek_orders_vouchers'] == NULL) ? '0' : $roworderPlacedValue['thisweek_orders_vouchers']) . '</td><td>' . (($roworderPlacedValue['day7_orders_vouchers'] == NULL) ? '0' : $roworderPlacedValue['day7_orders_vouchers']) . '</td><td>' . (($roworderPlacedValue['thismonth_orders_vouchers'] == NULL) ? '0' : $roworderPlacedValue['thismonth_orders_vouchers']) . '</td><td>' . (($roworderPlacedValue['lastmonth_orders_vouchers'] == NULL) ? '0' : $roworderPlacedValue['lastmonth_orders_vouchers']) . '</td><td>' . (($roworderPlacedValue['month3_orders_vouchers'] == NULL) ? '0' : $roworderPlacedValue['month3_orders_vouchers']) . '</td><td>' . (($roworderPlacedValue['thisyear_orders_vouchers'] == NULL) ? '0' : $roworderPlacedValue['thisyear_orders_vouchers']) . '</td>'; ?></tr>
                    <tr><th><?php echo t_lang('M_TXT_VOUCHERS_REDEEMED'); ?>  <a href="javascript:void(0);" class="fr" onmouseout ="hideInfo();" onmouseover="displayInfo('<?php echo t_lang('M_TXT_VOUCHERS_REDEEMED_TEXT'); ?>');" title="<?php echo t_lang('M_TXT_VOUCHERS_REDEEMED_TEXT'); ?>">[?]</a></th><?php echo '<td>' . (($rowVoucherRedem['today_orders_vouchers_redem'] == NULL) ? '0' : $rowVoucherRedem['today_orders_vouchers_redem']) . '</td><td>' . (($rowVoucherRedem['thisweek_orders_vouchers_redem'] == NULL) ? '0' : $rowVoucherRedem['thisweek_orders_vouchers_redem']) . '</td><td>' . (($rowVoucherRedem['day7_orders_vouchers_redem'] == NULL) ? '0' : $rowVoucherRedem['day7_orders_vouchers_redem']) . '</td><td>' . (($rowVoucherRedem['thismonth_orders_vouchers_redem'] == NULL) ? '0' : $rowVoucherRedem['thismonth_orders_vouchers_redem']) . '</td><td>' . (($rowVoucherRedem['lastmonth_orders_vouchers_redem'] == NULL) ? '0' : $rowVoucherRedem['lastmonth_orders_vouchers_redem']) . '</td><td>' . (($rowVoucherRedem['month3_orders_vouchers_redem'] == NULL) ? '0' : $rowVoucherRedem['month3_orders_vouchers_redem']) . '</td><td>' . (($rowVoucherRedem['thisyear_orders_vouchers_redem'] == NULL) ? '0' : $rowVoucherRedem['thisyear_orders_vouchers_redem']) . '</td>'; ?></tr> 
                    <tr><th><?php echo t_lang('M_TXT_MONEY_SAVED'); ?> <a href="javascript:void(0);" class="fr" onmouseout ="hideInfo();" onmouseover="displayInfo('<?php echo t_lang('M_TXT_MONEY_SAVED_TEXT'); ?> <?php echo CONF_SITE_NAME; ?> <?php echo t_lang('M_TXT_MONEY_SAVED_TEXT_CONCATE'); ?>')" title="<?php echo t_lang('M_TXT_MONEY_SAVED_TEXT'); ?> <?php echo CONF_SITE_NAME; ?> <?php echo t_lang('M_TXT_MONEY_SAVED_TEXT_CONCATE'); ?>">[?]</a></th><?php echo '<td>' . CONF_CURRENCY . number_format($rowmoneySavedValue['today_orders_money_saved'], 2) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . number_format($rowmoneySavedValue['thisweek_orders_money_saved'], 2) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . number_format($rowmoneySavedValue['day7_orders_money_saved'], 2) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . number_format($rowmoneySavedValue['thismonth_orders_money_saved'], 2) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . number_format($rowmoneySavedValue['lastmonth_orders_money_saved'], 2) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . number_format($rowmoneySavedValue['month3_orders_money_saved'], 2) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . number_format($rowmoneySavedValue['thisyear_orders_money_saved'], 2) . CONF_CURRENCY_RIGHT . '</td>'; ?></tr>
                    <tr><th><?php echo t_lang('M_TXT_NEW_SUBSCRIBERS'); ?>  <a href="javascript:void(0);" class="fr" onmouseout ="hideInfo();" onmouseover="displayInfo('<?php echo t_lang('M_TXT_NEW_SUBSCRIBERS_TEXT'); ?>');" title="<?php echo t_lang('M_TXT_NEW_SUBSCRIBERS_TEXT'); ?>">[?]</a></th><?php echo '<td>' . (($newsLetterSub['today_newsletter_subscribers'] == NULL) ? '0' : $newsLetterSub['today_newsletter_subscribers']) . '</td><td>' . (($newsLetterSub['thisweek_newsletter_subscribers'] == NULL) ? '0' : $newsLetterSub['thisweek_newsletter_subscribers']) . '</td><td>' . (($newsLetterSub['day7_newsletter_subscribers'] == NULL) ? '0' : $newsLetterSub['day7_newsletter_subscribers']) . '</td><td>' . (($newsLetterSub['thismonth_newsletter_subscribers'] == NULL) ? '0' : $newsLetterSub['thismonth_newsletter_subscribers']) . '</td><td>' . (($newsLetterSub['lastmonth_newsletter_subscribers'] == NULL) ? '0' : $newsLetterSub['lastmonth_newsletter_subscribers']) . '</td><td>' . (($newsLetterSub['month3_newsletter_subscribers'] == NULL) ? '0' : $newsLetterSub['month3_newsletter_subscribers']) . '</td><td>' . (($newsLetterSub['thisyear_newsletter_subscribers'] == NULL) ? '0' : $newsLetterSub['thisyear_newsletter_subscribers']) . '</td>'; ?></tr> 
                    <tr><th><?php echo t_lang('M_TXT_NEW_USERS'); ?> <a href="javascript:void(0);" class="fr" onmouseout ="hideInfo();" onmouseover="displayInfo('<?php echo t_lang('M_TXT_NEW_USERS_TEXT'); ?> <?php echo CONF_SITE_NAME; ?>');" title="<?php echo t_lang('M_TXT_NEW_USERS_TEXT'); ?> <?php echo CONF_SITE_NAME; ?>">[?]</a></th><?php echo '<td>' . (($usersNew['today_users'] == NULL) ? '0' : $usersNew['today_users']) . '</td><td>' . (($usersNew['thisweek_users'] == NULL) ? '0' : $usersNew['thisweek_users']) . '</td><td>' . (($usersNew['day7_users'] == NULL) ? '0' : $usersNew['day7_users']) . '</td><td>' . (($usersNew['thismonth_users'] == NULL) ? '0' : $usersNew['thismonth_users']) . '</td><td>' . (($usersNew['lastmonth_users'] == NULL) ? '0' : $usersNew['lastmonth_users']) . '</td><td>' . (($usersNew['month3_users'] == NULL) ? '0' : $usersNew['month3_users']) . '</td><td>' . (($usersNew['thisyear_users'] == NULL) ? '0' : $usersNew['thisyear_users']) . '</td>'; ?></tr>
                    <tr><th><?php echo t_lang('M_TXT_NEW_DEAL_ADDED'); ?> <a href="javascript:void(0);" class="fr" onmouseout ="hideInfo();" onmouseover="displayInfo('<?php echo t_lang('M_TXT_NEW_DEAL_ADDED_TEXT'); ?>');"  title="<?php echo t_lang('M_TXT_NEW_DEAL_ADDED_TEXT'); ?>">[?]</a> </th><?php echo '<td>' . (($dealNew['today_deal'] == NULL) ? '0' : $dealNew['today_deal']) . '</td><td>' . (($dealNew['thisweek_deal'] == NULL) ? '0' : $dealNew['thisweek_deal']) . '</td><td>' . (($dealNew['day7_deal'] == NULL) ? '0' : $dealNew['day7_deal']) . '</td><td>' . (($dealNew['thismonth_deal'] == NULL) ? '0' : $dealNew['thismonth_deal']) . '</td><td>' . (($dealNew['lastmonth_deal'] == NULL) ? '0' : $dealNew['lastmonth_deal']) . '</td><td>' . (($dealNew['month3_deal'] == NULL) ? '0' : $dealNew['month3_deal']) . '</td><td>' . (($dealNew['thisyear_deal'] == NULL) ? '0' : $dealNew['thisyear_deal']) . '</td>'; ?></tr>
                    <tr><th><?php echo t_lang('M_TXT_DEAL_MADE_PUBLIC'); ?>  <a href="javascript:void(0);" class="fr" onmouseout ="hideInfo();" onmouseover="displayInfo('<?php echo t_lang('M_TXT_DEAL_MADE_PUBLIC_TEXT'); ?> <?php echo CONF_SITE_NAME; ?>)');"  title="<?php echo t_lang('M_TXT_DEAL_MADE_PUBLIC_TEXT'); ?> <?php echo CONF_SITE_NAME; ?>)">[?]</a></th><?php echo '<td>' . (($dealActive['today_deal'] == NULL) ? '0' : $dealActive['today_deal']) . '</td><td>' . (($dealActive['thisweek_deal'] == NULL) ? '0' : $dealActive['thisweek_deal']) . '</td><td>' . (($dealActive['day7_deal'] == NULL) ? '0' : $dealActive['day7_deal']) . '</td><td>' . (($dealActive['thismonth_deal'] == NULL) ? '0' : $dealActive['thismonth_deal']) . '</td><td>' . (($dealActive['lastmonth_deal'] == NULL) ? '0' : $dealActive['lastmonth_deal']) . '</td><td>' . (($dealActive['month3_deal'] == NULL) ? '0' : $dealActive['month3_deal']) . '</td><td>' . (($dealActive['thisyear_deal'] == NULL) ? '0' : $dealActive['thisyear_deal']) . '</td>'; ?></tr>
                    <tr><th><?php echo t_lang('M_TXT_AFFILIATE_COMMISSION'); ?>  <a href="javascript:void(0);" class="fr" onmouseout ="hideInfo();" onmouseover="displayInfo('<?php echo t_lang('M_TXT_AFFILIATE_COMMISSION_TEXT'); ?> <?php echo CONF_SITE_NAME; ?>');" title="<?php echo t_lang('M_TXT_AFFILIATE_COMMISSION_TEXT'); ?> <?php echo CONF_SITE_NAME; ?>">[?]</a></th><?php echo '<td>' . CONF_CURRENCY . (($affiliateRow['today_affiliate'] == 0) ? '0.00' : number_format($affiliateRow['today_affiliate'], 2)) . '</td><td>' . CONF_CURRENCY . (($affiliateRow['thisweek_affiliate'] == 0) ? '0.00' : number_format($affiliateRow['thisweek_affiliate'], 2)) . '</td><td>' . CONF_CURRENCY . (($affiliateRow['day7_affiliate'] == 0) ? '0.00' : number_format($affiliateRow['day7_affiliate'], 2)) . '</td><td>' . CONF_CURRENCY . (($affiliateRow['thismonth_affiliate'] == 0) ? '0.00' : number_format($affiliateRow['thismonth_affiliate'], 2)) . '</td><td>' . CONF_CURRENCY . (($affiliateRow['lastmonth_affiliate'] == 0) ? '0.00' : number_format($affiliateRow['lastmonth_affiliate'], 2)) . '</td><td>' . CONF_CURRENCY . (($affiliateRow['month3_affiliate'] == 0) ? '0.00' : number_format($affiliateRow['month3_affiliate'], 2)) . '</td><td>' . CONF_CURRENCY . (($affiliateRow['thisyear_affiliate'] == 0) ? '0.00' : number_format($affiliateRow['thisyear_affiliate'], 2)) . '</td>'; ?></tr>
                    <tr><th><?php echo t_lang('M_TXT_CHARITY_AMOUNT'); ?>  <a href="javascript:void(0);" class="fr" onmouseout ="hideInfo();" onmouseover="displayInfo('<?php echo t_lang('M_TXT_CHARITY_AMOUNT_TEXT'); ?> <?php echo CONF_SITE_NAME; ?>');" title="<?php echo t_lang('M_TXT_CHARITY_AMOUNT_TEXT'); ?> <?php echo CONF_SITE_NAME; ?>">[?]</a></th><?php echo '<td>' . CONF_CURRENCY . number_format(($charityRow['today_charity']), 2) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . number_format(($charityRow['thisweek_charity']), 2) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . number_format(($charityRow['day7_charity']), 2) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . number_format(($charityRow['thismonth_charity']), 2) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . number_format(($charityRow['lastmonth_charity']), 2) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . number_format(($charityRow['month3_charity']), 2) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . number_format(($charityRow['thisyear_charity']), 2) . CONF_CURRENCY_RIGHT . '</td>'; ?></tr>
                    <tr><th><?php echo t_lang('M_TXT_EARNINGS'); ?>  <a href="javascript:void(0);" class="fr"  onmouseout ="hideInfo();" onmouseover="displayInfo('<?php echo t_lang('M_TXT_EARNINGS_TEXT'); ?> <?php echo CONF_SITE_NAME; ?>');"  title="<?php echo t_lang('M_TXT_EARNINGS_TEXT'); ?> <?php echo CONF_SITE_NAME; ?>">[?]</a></th><?php echo '<td>' . CONF_CURRENCY . number_format($today_earning, 2) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . number_format($thisweek_earning, 2) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . number_format($day7_earning, 2) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . number_format($thismonth_earning, 2) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . number_format($lastmonth_earning, 2) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . number_format($month3_earning, 2) . CONF_CURRENCY_RIGHT . '</td><td>' . CONF_CURRENCY . number_format($thisyear_earning, 2) . CONF_CURRENCY_RIGHT . '</td>'; ?></tr>
                </table>
            </div></div>
    </div></td>
<?php require_once dirname(__FILE__) . '/footer.php'; ?>