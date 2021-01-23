<?php
require_once './application-top.php';
$arr_common_js[] = 'js/calendar.js';
$arr_common_js[] = 'js/calendar-en.js';
$arr_common_js[] = 'js/calendar-setup.js';
$arr_common_css[] = 'css/cal-css/calendar-win2k-cold-1.css';
$affiliate_id = (int) $_GET['uid'];
$start_date = date('Y-m-d', strtotime('-30 days')) . ' 00:00:00';
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
$srch->addCondition('c.clicks_date', 'BETWEEN', [$start_date, $end_date]);
$result = $srch->getResultSet();
$clicks_record = [];
while ($row = $db->fetch($result)) {
    $clicks_record[$row['clicks_date']]['affiliates'] = $row['clicks_affiliate'];
}
/** get number of signups * */
$srch = new SearchBase('tbl_users', 'u');
$srch->addGroupBy('date(u.user_regdate)');
$srch->addCondition('u.user_regdate', 'BETWEEN', [$start_date, $end_date]);
$cnd = $srch->addDirectCondition('0');
$cnd->attachCondition('u.user_affiliate_id', '=', $affiliate_id, 'OR');
$srch->addMultipleFields(['date(u.user_regdate) as thedate', 'COUNT(*) as total']);
$registration_data = $srch->getResultSet();
while ($row = $db->fetch($registration_data)) {
    if (!is_array($clicks_record[$row['thedate']])) {
        $clicks_record[$row['thedate']] = [];
    }
    $clicks_record[$row['thedate']]['registrations'] = $row['total'];
}
/** get number of newsletter signups * */
$srch = new SearchBase('tbl_newsletter_subscription', 'ns');
$srch->addGroupBy('date(ns.subs_addedon)');
$srch->addCondition('ns.subs_addedon', 'BETWEEN', [$start_date, $end_date]);
$cnd = $srch->addDirectCondition('0');
$cnd->attachCondition('ns.subs_affiliate_id', '=', $affiliate_id, 'OR');
$srch->addMultipleFields(['date(ns.subs_addedon) as thedate', 'COUNT(*) as total']);
$newsletter_data = $srch->getResultSet();
while ($row1 = $db->fetch($newsletter_data)) {
    if (!is_array($clicks_record[$row1['thedate']]))
        $clicks_record[$row1['thedate']] = [];
    $clicks_record[$row1['thedate']]['newsletter'] = $row1['total'];
}
/** get total referral commission and total affiliate commission * */
$srch = new SearchBase('tbl_affiliate_wallet_history', 'w');
$srch->addGroupBy('date(w.wh_time)');
$srch->AddGroupBy('w.wh_untipped_deal_id');
$srch->addCondition('w.wh_affiliate_id', '=', $affiliate_id);
$cnd = $srch->addDirectCondition('0');
$cnd->attachCondition('w.wh_trans_type', '=', 'A', 'OR');
$srch->addCondition('w.wh_time', 'BETWEEN', [$start_date, $end_date]);
$srch->addMultipleFields(['date(w.wh_time) as thedate', "SUM(IF(w.wh_trans_type = 'A',w.wh_amount,0)) as affiliate_commission", 'w.wh_buyer_id', 'w.wh_affiliate_id']);
$wallet_data = $srch->getResultSet();
while ($row = $db->fetch($wallet_data)) {
    if (!is_array($clicks_record[$row['thedate']]))
        $clicks_record[$row['thedate']] = [];
    $srch = new SearchBase('tbl_users', 'u');
    $srch->joinTable('tbl_orders', 'INNER JOIN', 'u.user_id=o.order_user_id and date(o.order_date)="' . $row['thedate'] . '" and  o.order_payment_status!=0', 'o');
    $srch->joinTable('tbl_order_deals', 'INNER JOIN', 'o.order_id=od.od_order_id', 'od');
    $srch->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id=d.deal_id ', 'd');
    $srch->addCondition('d.deal_tipped_at', '!=', '0000-00-00 00:00:00');
    $srch->addCondition('u.user_affiliate_id', '=', $row['wh_affiliate_id']);
    $srch->addMultipleFields(['od.*', 'o.*', "SUM((od.od_qty+od.od_gift_qty)*od.od_deal_price) as totalAmount"]);
    $data = $srch->getResultSet();
    $totalAmount = 0;
    while ($amountRow = $db->fetch($data)) {
        $totalAmount += $amountRow['totalAmount'];
        $totalAmountDisplay += $amountRow['totalAmount'];
    }
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
    $total_count_arr['total_signups'] += $arr['registrations'];
    $total_count_arr['total_newsletter_signup'] += $arr['newsletter'];
    $total_count_arr['total_affiliates'] += $arr['affiliates'];
    /* 	$total_count_arr['total_affiliate_amount'] += $arr['affiliate_amount'];	 */
    $total_count_arr['total_affiliate_commission'] += $arr['affiliate_commission'];
}
/* * ###############* */
$arr_table_fields = ['Date', 'Sign-ups', 'Affiliate Clicks', 'Newsletter Sign Up', 'Commission'];
/** get user data * */
$srch_user = new SearchBase('tbl_affiliate', 'a');
$srch_user->addCondition('a.affiliate_id', '=', $affiliate_id);
$srch_user->joinTable('tbl_affiliate_wallet_history', 'LEFT JOIN', 'w.wh_affiliate_id = a.affiliate_id', 'w');
$srch_user->addMultipleFields(['a.affiliate_fname', 'a.affiliate_email_address', 'a.affiliate_city', "SUM(IF(w.wh_trans_type = 'A',w.wh_amount,0)) as affiliate_commission"]);
$user_result = $srch_user->getResultSet();
$user_row = $db->fetch($user_result);
/** search form * */
$frm = new Form('search_commissions');
$frm->setFieldsPerRow(3);
$frm->captionInSameCell(true);
$frm->setTableProperties('class="tbl_form" width="100%"');
$fld_start_date = $frm->addDateField(t_lang('M_TXT_FROM'), 'start_date', '', 'start_date', '');
$fld_start_date->value = displayDate($start_date);
$fld_end_date = $frm->addDateField(t_lang('M_TXT_TILL'), 'end_date', '', 'end_date', '');
$fld_end_date->value = displayDate($end_date);
$fld1 = $frm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="medium" onclick=location.href="affiliate_summary.php?uid=' . $affiliate_id . '"');
$fld = $frm->addSubmitButton('', 'search', t_lang('M_TXT_SEARCH'), '', ' class="medium"')->attachField($fld1);
require_once './header.php';
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0)' => t_lang('M_TXT_USERS'),
    'affiliate.php' => t_lang('M_TXT_AFFILIATE'),
    '' => t_lang('M_TXT_AFFILIATE_PERFORMANCE_REPORT')
];
?>
<ul class="nav-left-ul">
    <li><a  class="selected" href="affiliate_summary.php?uid=<?php echo $affiliate_id; ?>"><?php echo t_lang('M_TXT_SUMMARY_VIEW'); ?></a></li>
    <li><a href="affiliate_list.php?uid=<?php echo $affiliate_id; ?>"><?php echo t_lang('M_TXT_LIST_VIEW'); ?></a></li>
</ul>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_AFFILIATE_PERFORMANCE_REPORT'); ?></div>
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="message error"><?php echo $msg->display(); ?> </div>
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
    <?php } ?>
    <div class="box searchform_filter"><div class="title"> <?php echo t_lang('M_TXT_SEARCH'); ?> </div><div class="content togglewrap" style="display:none;">		<?php echo $frm->getFormHtml(); ?></div></div>
    <table class="tbl_data affiliateslist" width="100%">
        <thead>
            <tr>
                <th width='14%'><?php echo t_lang('M_TXT_DATE'); ?></th>
                <!-- <th width='18%'>Referral Clicks</th> -->
                <th width='10%'><?php echo t_lang('M_TXT_SIGN_UPS'); ?></th>
                <th width="20%" ><?php echo t_lang('M_TXT_NEWSLETTER_SIGN_UP'); ?></th>
                <!-- <th width='11%'>Commission</th>-->
                <th width='18%'><?php echo t_lang('M_TXT_AFFILIATE_CLICKS'); ?></th>
                <!-- <th width='18%'>Vouchers Sold</th> -->
                <th width='*'><?php echo t_lang('M_TXT_SALES'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (count($clicks_record) != 0) {
                foreach ($clicks_record as $date => $arr) {
                    echo '<tr>';
                    foreach ($arr_table_fields as $key => $value) {
                        echo '<td>';
                        switch ($key) {
                            case 0:
                                echo displayDate($date);
                                break;
                            case 1:
                                echo $arr['registrations'] != '' ? $arr['registrations'] : 0;
                                break;
                            case 2:
                                echo $arr['newsletter'] != '' ? $arr['newsletter'] : 0;
                                break;
                            case 3:
                                echo $arr['affiliates'] != '' ? $arr['affiliates'] : 0;
                                break;
                            case 4:
                                echo $arr['affiliate_commission'] != '' ? CONF_CURRENCY . $arr['affiliate_commission'] . CONF_CURRENCY_RIGHT : CONF_CURRENCY . '0.00' .
                                        CONF_CURRENCY_RIGHT;
                                break;
                        }
                        echo '</td>';
                    }
                    echo '</tr>';
                }
                echo '<tr style="font-weight:bold;">
				<td>Total</td>';
                foreach ($total_count_arr as $key => $val) {
                    if ($key == 'total_affiliate_amount' || $key == 'total_affiliate_commission') {
                        echo '<td>' . CONF_CURRENCY . number_format($val, 2) . CONF_CURRENCY_RIGHT . '</td>';
                    } else {
                        echo '<td>' . $val . '</td>';
                    }
                }
            } else {
                echo '<tr><td colspan="5">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
            }
            ?>
            </tr>
        </tbody>
    </table>
    <?php require_once './footer.php'; ?>