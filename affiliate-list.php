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
$start_date = date('Y-m-d', strtotime('-30 days')) . ' 00:00:00';
$end_date = date('Y-m-d') . ' 23:59:59';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
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
$page = is_numeric($_GET['page']) ? $_GET['page'] : 1;
$pagesize = 50;
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$srch->addCondition('w.wh_time', 'BETWEEN', [$start_date, $end_date]);
$srch->addMultipleFields(['w.wh_affiliate_id', 'date(w.wh_time) as thedate', 'sum(w.wh_amount) as totalAmountAffiliate', 'w.wh_amount', 'w.wh_trans_type', 'w.wh_buyer_id', 'd.deal_name', 'w.wh_buyer_id', 'd.deal_id', 'w.wh_time', 'wh_untipped_deal_id']);
$wallet_data = $srch->getResultSet();
$pages = $srch->pages();
if ($pages > 1) {
    $pagestring = '<ul class="paging"><li class="space">' . t_lang('M_TXT_DISPLAYING_PAGE') . $page . t_lang('M_TXT_OF') . $pages . ' ';
}
if ($pages > 1) {
    $pagestring .= t_lang('M_TXT_GOTO') . '</li>' . getPageString('<li><a href="?page=xxpagexx">xxpagexx</a></li> ', $pages, $page, '<li><a   class="still" href="javascript:void(0);">xxpagexx</a></li> ', '...') . '</ul>';
}
$total_records = $db->total_records($wallet_data);
$trans_type_arr = [];
$trans_type_arr['A'] = 'Affiliate';
/** search form * */
$frm = new Form('search_commissions');
$frm->setFieldsPerRow(2);
$frm->captionInSameCell(true);
$frm->setExtra('class="siteForm"');
$frm->setTableProperties('class="formwrap__table " ');
$fld_start_date = $frm->addDateField(t_lang('M_TXT_FROM') . ':', 'start_date', '', 'start_date', ' class="width200"');
$fld_start_date->value = displayDate($start_date);
$fld_start_date->html_before_field = '<div class="field--date">';
$fld_start_date->html_after_field = '</div>';
$fld_end_date = $frm->addDateField(t_lang('M_TXT_TILL') . ':', 'end_date', '', 'end_date', ' class="width200"');
$fld_end_date->value = displayDate($end_date);
$fld_end_date->html_before_field = '<div class="field--date">';
$fld_end_date->html_after_field = '</div>';
$fld = $frm->addSubmitButton('', 'btn_search', t_lang('M_TXT_SEARCH'), '', ' ');
$fld->merge_caption = true;
require_once './header.php';
if (isset($verification_status) && $verification_status > 0) {
    if ($verification_status == 1) {
        $eMsg = t_lang('M_TXT_UPDATE_YOUR_PASSWORD');
    }
}
?>
<section class="pagebar">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-7 col-sm-7">
                <h3><?php echo t_lang('M_TXT_DEAL_PRODUCT_WISE_REPORTS'); ?> </h3>
                <ul class="breadcrumb">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL); ?>"><?php echo t_lang('M_TXT_HOME'); ?></a></li>
                    <li><?php echo t_lang('M_TXT_DEAL_PRODUCT_WISE_REPORTS'); ?> </li>
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
                            <th><?php echo t_lang('M_TXT_DEAL_NAME'); ?> </th>
                            <th><?php echo t_lang('M_TXT_COMMISSION_EARNINGS'); ?> </th>
                            <th><?php echo t_lang('M_TXT_SALES'); ?> </th>
                        </tr>
                    </thead>   
                    <tbody>
                        <?php
                        if ($total_records != 0) {
                            $total_commission = 0;
                            $totalAmountAffiliate = 0;
                            while ($row = $db->fetch($wallet_data)) {
                                $totalAmountAffiliate += $row['totalAmountAffiliate'];
                                $sql = $db->query("SELECT user_id FROM tbl_users WHERE user_affiliate_id = " . $row['wh_affiliate_id']);
                                while ($user_data = $db->fetch($sql)) {
                                    $users[] = $user_data['user_id'];
                                }
                                $date1 = displayDate($row['thedate'], false, true, $_SESSION['logged_user']['user_timezone']);
                                $dateArr = explode(',', $date1);
                                $monthDay = explode(' ', trim($dateArr[0]));
                                $yearTime = explode(' ', trim($dateArr[1]));
                                $newDate = $monthDay[1] . ' ' . $monthDay[0] . ' ' . $yearTime[1] . ' ' . $yearTime[0];
                                ?>
                                <tr>
                                    <td><span class="caption__cell"><?php echo t_lang('M_TXT_DATE'); ?></span><?php echo $newDate; ?></td>
                                    <td><span class="caption__cell"><?php echo t_lang('M_TXT_DEAL_NAME'); ?> </span><?php echo $row['deal_name']; ?></td>
                                    <td><span class="caption__cell"><?php echo t_lang('M_TXT_COMMISSION_EARNINGS'); ?></span><?php echo amount($row['totalAmountAffiliate'], 2); ?></td>
                                    <?php
                                    $srch = new SearchBase('tbl_users', 'u');
                                    $srch->joinTable('tbl_orders', 'INNER JOIN', 'u.user_id=o.order_user_id and date(o.order_date)="' . $row['thedate'] . '" and  o.order_payment_status!=0', 'o');
                                    $srch->joinTable('tbl_order_deals', 'INNER JOIN', 'o.order_id=od.od_order_id and  od.od_deal_id =' . $row['wh_untipped_deal_id'], 'od');
                                    $srch->addCondition('u.user_affiliate_id', '=', $row['wh_affiliate_id']);
                                    $srch->addMultipleFields(array('od.*', 'o.*', "SUM((od.od_qty+od.od_gift_qty)*od.od_deal_price) as totalAmount"));
                                    $data = $srch->getResultSet();
                                    $totalAmount = 0;
                                    while ($amountRow = $db->fetch($data)) {
                                        $totalAmount += $amountRow['totalAmount'];
                                        $totalAmountDisplay += $amountRow['totalAmount'];
                                    }
                                    $total_commission += $row['totalAmount'];
                                    ?>
                                    <td><span class="caption__cell"><?php echo t_lang('M_TXT_SALES'); ?></span><?php echo amount($totalAmount); ?>
                                    </td>
                                </tr>
                                <?php
                            }
                            echo '<tr style="font-weight:bold">';
                            echo '<td>&nbsp;</td>';
                            echo '<td style="font-weight:bold">' . t_lang('M_TXT_TOTAL_SALES') . '</td>';
                            echo '<td  style="font-weight:bold"><span class="caption__cell">' . t_lang('M_TXT_COMMISSION_EARNINGS') . '</span>' . CONF_CURRENCY . number_format($totalAmountAffiliate, 2) . CONF_CURRENCY_RIGHT . '</td>';
                            echo '<td   style="font-weight:bold"><span class="caption__cell">' . t_lang('M_TXT_SALES') . '</span>' . CONF_CURRENCY . number_format($totalAmountDisplay, 2) . CONF_CURRENCY_RIGHT . '</td>';
                            echo '</tr>';
                        } else {
                            echo '<tr ><td colspan="4"  align="center">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
                        }
                        ?>
                    </tbody>   
                </table>
                <?php if (isset($pagestring) && strlen($pagestring) > 0) { ?>
                    <div class="tableWrapper_top">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr><td><?php echo $pagestring; ?></td></tr>
                        </table>
                    </div>
                <?php } ?>
            </div>    
        </div>
    </div>    
</section>
<?php
require_once './footer.php';
