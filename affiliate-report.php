<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
$arr_common_js[] = 'js/calendar.js';
$arr_common_js[] = 'js/calendar-en.js';
$arr_common_js[] = 'js/calendar-setup.js';
$arr_common_css[] = 'css/cal-css/calendar-win2k-cold-1.css';
if (!isAffiliateUserLogged()) {
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'affiliate-login.php'));
}
$affiliate_id = (int) $_SESSION['logged_user']['affiliate_id'];
/** get total amount of vouchers sold * */
$sql = $db->query("SELECT clicks_date FROM `tbl_referral_affiliate_clicks` c WHERE c.`clicks_affiliate_id` = '" . $affiliate_id . "' limit 0,1");
$row = $db->fetch($sql);
if (isset($row['clicks_date'])) {
    $start_date = $row['clicks_date'];
} else {
    $start_date = date('Y-m-d', strtotime('-30 days')) . ' 00:00:00';
}
$end_date = date('Y-m-d') . ' 23:59:59';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    //print_r($post);exit;
    $start_date = date('Y-m-d', strtotime($post['start_date'])) . ' 00:00:00';
    $end_date = date('Y-m-d', strtotime($post['end_date'])) . ' 23:59:59';
}
/* * ### referral and affiliate data ###* */
/** get number of referral and affiliate clicks * */
$srch = new SearchBase('tbl_referral_affiliate_clicks', 'c');
$srch->addCondition('c.clicks_affiliate_id', '=', $affiliate_id);
$srch->addCondition('c.clicks_date', 'BETWEEN', array($start_date, $end_date));
$srch->addOrder('clicks_date', 'asc');
$result = $srch->getResultSet();
$clicks_record = [];
while ($row = $db->fetch($result)) {
    $clicks_record[$row['clicks_date']]['affiliates'] = $row['clicks_affiliate'];
}
/** get number of signups * */
$srch = new SearchBase('tbl_users', 'u');
$srch->addGroupBy('date(u.user_regdate)');
$srch->addCondition('u.user_regdate', 'BETWEEN', array($start_date, $end_date));
$cnd = $srch->addDirectCondition('0');
$cnd->attachCondition('u.user_affiliate_id', '=', $affiliate_id, 'OR');
$srch->addMultipleFields(array('date(u.user_regdate) as thedate', 'COUNT(*) as total'));
$srch->addOrder('thedate', 'asc');
//echo $srch->getQuery();
$registration_data = $srch->getResultSet();
while ($row = $db->fetch($registration_data)) {
    if (!is_array($clicks_record[$row['thedate']])) {
        $clicks_record[$row['thedate']] = [];
    }
    $clicks_record[$row['thedate']]['registrations'] = $row['total'];
}
/** get total referral commission and total affiliate commission * */
$srch = new SearchBase('tbl_affiliate_wallet_history', 'w');
$srch->joinTable('tbl_deals', 'INNER JOIN', 'w.wh_untipped_deal_id=d.deal_id', 'd');
$srch->addGroupBy('date(w.wh_time)');
$srch->AddGroupBy('w.wh_untipped_deal_id');
$srch->addCondition('w.wh_affiliate_id', '=', $affiliate_id);
$cnd = $srch->addDirectCondition('0');
$cnd->attachCondition('w.wh_trans_type', '=', 'A', 'OR');
$srch->addCondition('w.wh_time', 'BETWEEN', array($start_date, $end_date));
$srch->addMultipleFields(array('date(w.wh_time) as thedate', "SUM(IF(w.wh_trans_type = 'A',w.wh_amount,0)) as affiliate_commission", 'w.wh_buyer_id', 'w.wh_affiliate_id', 'wh_untipped_deal_id'));
$srch->addOrder('thedate', 'asc');
$wallet_data = $srch->getResultSet();
while ($row = $db->fetch($wallet_data)) {
    if (!is_array($clicks_record[$row['thedate']])) {
        $clicks_record[$row['thedate']] = [];
    }
    $srch = new SearchBase('tbl_users', 'u');
    $srch->joinTable('tbl_orders', 'INNER JOIN', 'u.user_id=o.order_user_id and date(o.order_date)="' . $row['thedate'] . '" and  o.order_payment_status!=0', 'o');
    $srch->joinTable('tbl_order_deals', 'INNER JOIN', 'o.order_id=od.od_order_id', 'od');
    $srch->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id=d.deal_id ', 'd');
    $srch->addCondition('d.deal_tipped_at', '!=', '0000-00-00 00:00:00');
    $srch->addCondition('u.user_affiliate_id', '=', $row['wh_affiliate_id']);
    $srch->addMultipleFields(array('od.*', 'o.*', "SUM((od.od_qty+od.od_gift_qty)*od.od_deal_price) as totalAmount"));
    $srch->addOrder('o.order_date', 'asc');
    $data = $srch->getResultSet();
    /* echo $srch->getQuery();  */
    $totalAmount = 0;
    while ($amountRow = $db->fetch($data)) {
        $totalAmount += $amountRow['totalAmount'];
        $totalAmountDisplay += $amountRow['totalAmount'];
    }
    /* $clicks_record[$row['thedate']]['affiliate_commission'] = $row['affiliate_commission']; */
    $clicks_record[$row['thedate']]['affiliate_commission'] = $totalAmount;
}
/** get total amount of vouchers sold * */
$sql = $db->query("SELECT user_id FROM tbl_users WHERE user_affiliate_id = " . $affiliate_id);
$affiliate_users = [];
while ($row = $db->fetch($sql)) {
    $affiliate_users[] = $row['user_id'];
}
if (count($affiliate_users) != 0) {
    $in_str = '';
    foreach ($affiliate_users as $val) {
        $in_str .= ',' . $val;
    }
    $in_str = ltrim($in_str, ',');
    $sql_2 = $db->query("SELECT order_id, date(order_date) as thedate FROM tbl_orders WHERE order_user_id IN (" . $in_str . ") AND order_payment_status = 1 AND order_date BETWEEN '" . $start_date . "' AND '" . $end_date . "' GROUP BY order_date  ");
    $orders_arr = [];
    while ($row_2 = $db->fetch($sql_2)) {
        $orders_arr[$row_2['thedate']][] = $row_2['order_id'];
    }
    $total_affiliate_amount = 0;
    foreach ($orders_arr as $order_date => $arr) {
        $aff_amount = 0;
        foreach ($arr as $val) {
            $sql_3 = $db->query("SELECT (od_deal_price*od_qty) as amount FROM tbl_order_deals WHERE od_order_id = '" . $val . "'");
            $result_data = $db->fetch($sql_3);
            $aff_amount += $result_data['amount'];
        }
        $clicks_record[$order_date]['affiliate_amount'] = $aff_amount;
    }
}
/** calculates total for each column * */
$total_count_arr = [];
foreach ($clicks_record as $arr) {
    $total_count_arr['total_affiliates'] += $arr['affiliates'];
    $total_count_arr['total_signups'] += $arr['registrations'];
    /* 	$total_count_arr['total_affiliate_amount'] += $arr['affiliate_amount'];	 */
    $total_count_arr['total_affiliate_commission'] += $arr['affiliate_commission'];
}
/* * ###############* */
$arr_table_fields = array('Date', 'Sign-ups', 'Affiliate Clicks', 'Commission');
/** get user data * */
$srch_user = new SearchBase('tbl_affiliate', 'a');
$srch_user->addCondition('a.affiliate_id', '=', $affiliate_id);
$srch_user->joinTable('tbl_affiliate_wallet_history', 'LEFT JOIN', 'w.wh_affiliate_id = a.affiliate_id', 'w');
$srch_user->addMultipleFields(array('a.affiliate_fname', 'a.affiliate_email_address', 'a.affiliate_city', "SUM(IF(w.wh_trans_type = 'A',w.wh_amount,0)) as affiliate_commission"));
$user_result = $srch_user->getResultSet();
$user_row = $db->fetch($user_result);
/** search form * */
$frm = new Form('search_commissions');
$frm->setFieldsPerRow(2);
$frm->captionInSameCell(true);
$frm->setExtra('class="siteForm"');
$frm->setTableProperties(' class="formwrap__table" ');
$fld_start_date = $frm->addDateField(t_lang('M_TXT_FROM') . ':', 'start_date', '', 'start_date', ' class="width200" readonly');
$fld_start_date->value = displayDate($start_date);
$fld_start_date->html_before_field = '<div class="field--date">';
$fld_start_date->html_after_field = '</div>';
$fld_end_date = $frm->addDateField(t_lang('M_TXT_TILL') . ':', 'end_date', '', 'end_date', ' class="width200" readonly="readonly" readonly');
$fld_end_date->value = displayDate($end_date);
$fld_end_date->html_before_field = '<div class="field--date">';
$fld_end_date->html_after_field = '</div>';
$fld = $frm->addSubmitButton('', 'btn_search', t_lang('M_TXT_SEARCH'), '', ' class="button_large"');
$fld->merge_caption = true;
require_once './header.php';
if (isset($verification_status) && $verification_status > 0) {
    if ($verification_status == 1) {
        $msg->addMsg(t_lang('M_TXT_UPDATE_YOUR_PASSWORD'));
    }
}
?>
<section class="pagebar">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-7 col-sm-7">
                <h3><?php echo t_lang('M_TXT_AFFILIATE_REPORTS'); ?> </h3>
                <ul class="breadcrumb">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL); ?>"><?php echo t_lang('M_TXT_MY_ACCOUNT'); ?></a></li>
                    <li><?php echo t_lang('M_TXT_AFFILIATE_REPORTS'); ?> </li>
                </ul>
            </aside>
        </div>
    </div>
</section> 
<?php require_once './left-panel-links.php'; ?> 
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <div class="col-md-12">
                <h2 class="section__subtitle hide__mobile hide__tab hide__ipad"><?php echo t_lang('M_TXT_AFFILIATE_REPORTS'); ?> </h2>
                <div class="cover__grey">
                    <div class="formwrap">
                        <?php
                        echo $frm->getFormHtml();
                        ?>
                    </div>
                </div>
                <table class="table__data">
                    <thead>
                        <tr>
                            <th><?php echo t_lang('M_TXT_DATE'); ?></th>
                            <th><?php echo t_lang('M_TXT_AFFILIATE_CLICKS'); ?> </th>
                            <th><?php echo t_lang('M_TXT_SIGN_UPS'); ?> </th>
                            <th><?php echo t_lang('M_TXT_SALES'); ?> </th>
                        </tr>
                    </thead>   
                    <tbody>
                        <?php
                        if (count($clicks_record) != 0) {
                            ksort($clicks_record);
                            foreach ($clicks_record as $date => $arr) {
                                ?>
                                <tr>
                                    <td><span class="caption__cell"><?php echo t_lang('M_TXT_DATE'); ?></span><?php echo displayDate($date, false, true, $_SESSION['logged_user']['user_timezone']); ?></td>
                                    <td><span class="caption__cell"><?php echo t_lang('M_TXT_AFFILIATE_CLICKS'); ?> </span><?php echo $arr['affiliates'] != '' ? $arr['affiliates'] : 0; ?></td>
                                    <td><span class="caption__cell"><?php echo t_lang('M_TXT_SIGN_UPS'); ?></span><?php echo $arr['registrations'] != '' ? $arr['registrations'] : 0; ?></td>
                                    <td><span class="caption__cell"><?php echo t_lang('M_TXT_SALES'); ?></span><?php echo $arr['affiliate_commission'] != '' ? amount($arr['affiliate_commission']) : amount(0) ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr ><td colspan="4"  align="center">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
                        }
                        ?> 
                        <?php
                        if (!empty($total_count_arr)) {
                            echo '<tr style="font-weight:bold;">
                    <td style="font-weight:bold">' . t_lang('M_TXT_TOTAL') . '</td>';
                        }
                        foreach ($total_count_arr as $key => $val) {
                            if ($key == 'total_affiliate_amount' || $key == 'total_affiliate_commission') {
                                echo '<td   style="font-weight:bold"><span class="caption__cell">' . t_lang('M_TXT_SALES') . '</span>' . amount($val, 2) . '</td>';
                            } else {
                                $text = t_lang('M_TXT_SIGN_UPS');
                                if ($key == "total_affiliates") {
                                    $text = t_lang('M_TXT_AFFILIATE_CLICKS');
                                }
                                echo '<td  style="font-weight:bold"><span class="caption__cell">' . $text . '</span>' . $val . '</td>';
                            }
                        }
                        if (!empty($total_count_arr)) {
                            echo '</tr>';
                        }
                        ?>
                    </tbody>   
                </table>
                <?php if (isset($pagestring) && strlen($pagestring) > 0) { ?>
                    <div class="tableWrapper_top">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td>
                                    <?php echo $pagestring; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                <?php } ?>
            </div>    
        </div>
    </div>    
</section>	
<?php
require_once './footer.php';
