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
$srch->addCondition('w.wh_affiliate_id', '=', $affiliate_id);
$cnd = $srch->addDirectCondition('0');
$cnd->attachCondition('w.wh_trans_type', '=', 'A', 'OR');
$srch->addCondition('w.wh_time', 'BETWEEN', [$start_date, $end_date]);
$srch->addMultipleFields(['w.wh_affiliate_id', 'date(w.wh_time) as thedate', 'w.wh_amount', 'w.wh_trans_type', 'w.wh_buyer_id']);
//echo $srch->getQuery();
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
$fld1 = $frm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="medium" onclick=location.href="affiliate-email-signup.php?uid=' . $affiliate_id . '"');
$fld = $frm->addSubmitButton('', 'search', t_lang('M_TXT_SEARCH'), '', ' class="medium"')->attachField($fld1);
require_once './header.php';
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0)' => t_lang('M_TXT_USERS'),
    'affiliate.php' => t_lang('M_TXT_AFFILIATE'),
    '' => t_lang('M_TXT_EMAIL_SIGN_UPS')
];
?>
<ul class="nav-left-ul">
    <li><a   href="affiliate_summary.php?uid=<?php echo $affiliate_id; ?>"><?php echo t_lang('M_TXT_SUMMARY_VIEW'); ?></a></li>
    <li><a href="affiliate_list.php?uid=<?php echo $affiliate_id; ?>"><?php echo t_lang('M_TXT_LIST_VIEW'); ?></a></li>
    <li><a href="affiliate-email-signup.php?uid=<?php echo $affiliate_id; ?>" class="selected"><?php echo t_lang('M_TXT_EMAIL_SIGN_UPS'); ?></a></li>
</ul>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_EMAIL_SIGN_UPS'); ?></div>
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
    <div class="box searchform_filter"><div class="title"> <?php echo t_lang('M_TXT_EMAIL_SIGN_UPS'); ?> </div><div class="content togglewrap" style="display:none;">		<?php echo $frm->getFormHtml(); ?>	
        </div></div>
    <table class="tbl_data" width="100%">
        <thead>
            <tr>
                <th width='25%'><?php echo t_lang('M_TXT_DATE'); ?></th>
                <!-- <th width='25%'>Commission Type</th> -->
                <th width='25%'><?php echo t_lang('M_TXT_PARTICULARS'); ?></th>
                <th width='*'><?php echo t_lang('M_TXT_COMMISSION_EARNINGS'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($total_records != 0) {
                $total_commission = 0;
                while ($row = $db->fetch($wallet_data)) {
                    $sql = $db->query("SELECT user_name FROM tbl_users WHERE user_id = " . $row['wh_buyer_id']);
                    $user_data = $db->fetch($sql);
                    echo '<tr>';
                    echo '<td>' . displayDate($row['thedate']) . '</td>';
                    /* echo '<td>'.$trans_type_arr[$row['wh_trans_type']].'</td>'; */
                    echo '<td>' . $user_data['user_name'] . t_lang('M_TXT_PURCHASED_A_DEAL') . '</td>';
                    echo '<td>' . CONF_CURRENCY . $row['wh_amount'] . CONF_CURRENCY_RIGHT . '</td>';
                    echo '</tr>';
                    $total_commission += $row['wh_amount'];
                }
                echo '<tr style="font-weight:bold">';
                echo '<td>&nbsp;</td>';
                /* echo '<td>&nbsp;</td>'; */
                echo '<td>' . t_lang('M_TXT_TOTAL_EARNING') . '</td>';
                echo '<td>' . CONF_CURRENCY . number_format($total_commission, 2) . CONF_CURRENCY_RIGHT . '</td>';
                echo '</tr>';
            } else {
                echo '<tr><td colspan="3">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
            }
            ?>
        </tbody>
    </table>
    <?php require_once './footer.php'; ?>
