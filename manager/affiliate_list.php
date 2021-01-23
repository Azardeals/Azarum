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
/** get total referral commission and total affiliate commission * */
$srch = new SearchBase('tbl_affiliate_wallet_history', 'w');
$srch->joinTable('tbl_deals', 'INNER JOIN', 'w.wh_untipped_deal_id=d.deal_id', 'd');
$srch->addCondition('w.wh_affiliate_id', '=', $affiliate_id);
$srch->Addorder('w.wh_time', 'desc');
$srch->AddGroupBy('w.wh_untipped_deal_id');
$srch->AddGroupBy('date(w.wh_time)');
$cnd = $srch->addDirectCondition('0');
$cnd->attachCondition('w.wh_trans_type', '=', 'A', 'OR');
$srch->addCondition('w.wh_time', 'BETWEEN', [$start_date, $end_date]);
$srch->addMultipleFields(['w.wh_id', 'w.wh_affiliate_id', 'date(w.wh_time) as thedate', 'sum(w.wh_amount) as totalAmount', 'w.wh_amount', 'w.wh_trans_type', 'w.wh_buyer_id', 'd.deal_name', 'w.wh_buyer_id', 'd.deal_id', 'w.wh_time', 'wh_untipped_deal_id']);
$wallet_data = $srch->getResultSet();
$total_records = $db->total_records($wallet_data);
$trans_type_arr = [];
$trans_type_arr['A'] = 'Affiliate';
/** search form * */
$frm = new Form('search_commissions');
$frm->setFieldsPerRow(3);
$frm->captionInSameCell(true);
$frm->setTableProperties('class="tbl_form" width="100%"');
$fld_start_date = $frm->addDateField(t_lang('M_TXT_FROM'), 'start_date', '', 'start_date', '');
$fld_start_date->value = displayDate($start_date);
$fld_end_date = $frm->addDateField(t_lang('M_TXT_TILL'), 'end_date', '', 'end_date', '');
$fld_end_date->value = displayDate($end_date);
$fld1 = $frm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="medium" onclick=location.href="affiliate_list.php?uid=' . $affiliate_id . '"');
$fld = $frm->addSubmitButton('', 'search', t_lang('M_TXT_SEARCH'), '', ' class="medium"')->attachField($fld1);
require_once './header.php';
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0)' => t_lang('M_TXT_USERS'),
    'affiliate.php' => t_lang('M_TXT_AFFILIATE'),
    '' => t_lang('M_TXT_SALES')
];
?>
<ul class="nav-left-ul">
    <li><a href="affiliate_summary.php?uid=<?php echo $affiliate_id; ?>"><?php echo t_lang('M_TXT_SUMMARY_VIEW'); ?></a></li>
    <li><a class="selected" href="affiliate_list.php?uid=<?php echo $affiliate_id; ?>"><?php echo t_lang('M_TXT_LIST_VIEW'); ?></a></li>
</ul>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_AFFILIATE_SALES'); ?></div>
        <ul class="actions right">
            <li class="droplink">
                <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                <div class="dropwrap">
                    <ul class="linksvertical">
                        <li> 
                            <a href="affiliate-history.php?affiliate=<?php echo $_GET['uid'] ?>"><?php echo t_lang('M_TXT_VIEW_TRANSACTIONS') ?></a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
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
                <?php } if (isset($_SESSION['msgs'][0])) { ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
    <div class="box searchform_filter"><div class="title"> <?php echo t_lang('M_TXT_SEARCH'); ?> </div><div class="content togglewrap" style="display:none;">		<?php echo $frm->getFormHtml(); ?></div></div>	
    <table class="tbl_data" width="100%">
        <thead>
            <tr>
                <th width='25%'><?php echo t_lang('M_TXT_DATE'); ?></th>
                <th width='25%'><?php echo t_lang('M_TXT_DEAL_NAME'); ?></th>
                <th width="25%"><?php echo t_lang('M_TXT_EARNINGS'); ?> </th> 
                <th width='*'><?php echo t_lang('M_TXT_SALES'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($total_records != 0) {
                $total_commission = 0;
                $totalAmountAffiliate = 0;
                while ($row = $db->fetch($wallet_data)) {
                    $totalAmountAffiliate += $row['totalAmount'];
                    $sql = $db->query("SELECT user_id FROM tbl_users WHERE user_affiliate_id = " . $row['wh_affiliate_id']);
                    while ($user_data = $db->fetch($sql)) {
                        $users[] = $user_data['user_id'];
                    }
                    echo '<tr>';
                    echo '<td>' . displayDate($row['thedate']) . '</td>';
                    echo '<td>' . $row['deal_name'] . '</td>';
                    echo '<td>' . amount($row['totalAmount'], 2) . '</td>';
                    $srch = new SearchBase('tbl_users', 'u');
                    $srch->joinTable('tbl_orders', 'INNER JOIN', 'u.user_id=o.order_user_id and date(o.order_date)="' . $row['thedate'] . '" and  o.order_payment_status!=0', 'o');
                    $srch->joinTable('tbl_order_deals', 'INNER JOIN', 'o.order_id=od.od_order_id and  od.od_deal_id =' . $row['wh_untipped_deal_id'], 'od');
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
                    echo '<td>' . amount($totalAmount, 2) . ' </td>';
                    echo '</tr>';
                }
                echo '<tr style="font-weight:bold">';
                echo '<td>&nbsp;</td>';
                echo '<td>' . t_lang('M_TXT_TOTAL') . '</td>';
                echo '<td>' . CONF_CURRENCY . number_format($totalAmountAffiliate, 2) . CONF_CURRENCY_RIGHT . ' &nbsp;';
                echo '</td>';
                echo '<td>' . CONF_CURRENCY . number_format($totalAmountDisplay, 2) . CONF_CURRENCY_RIGHT . '</td>';
                echo '</tr>';
            } else {
                echo '<tr><td colspan="4">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
            }
            ?>
        </tbody>
    </table>
</td>
<?php require_once './footer.php'; ?>