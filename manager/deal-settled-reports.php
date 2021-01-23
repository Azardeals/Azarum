<?php
require_once './application-top.php';
checkAdminPermission(15);
$arr_common_js[] = 'js/calendar.js';
$arr_common_js[] = 'js/calendar-en.js';
$arr_common_js[] = 'js/calendar-setup.js';
$arr_common_css[] = 'css/cal-css/calendar-win2k-cold-1.css';
$arr_common_css[] = 'css/calender.css';
global $db;
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
$frm = new Form('Src_frm', 'Src_frm');
$frm->setTableProperties('class="tbl_form" width="100%"');
$fld = $frm->addDateField(t_lang('M_FRM_DEAL_PAID_STARTS_TIME'), 'cwh_start_time', '', 'cwh_start_time');
$fld->html_before_field = "<div class='frm-dob fld-req'>";
$fld->html_before_field = "</div>";
$fld->requirements()->setRequired(true);
$fld = $frm->addDateField(t_lang('M_FRM_DEAL_PAID_END_TIME'), 'cwh_end_time', '', 'cwh_end_time', '');
$fld->html_before_field = "<div class='frm-dob fld-req'>";
$fld->html_before_field = "</div>";
$fld->requirements()->setRequired(true);
$frm->setFieldsPerRow(3);
$frm->captionInSameCell(true);
$fld1 = $frm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="inputbuttons" onclick=window.location.reload();');
$fld = $frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SEARCH'), '', ' class="inputbuttons" ');
$fld->attachField($fld1);
$frm->setJsErrorDisplay('afterfield');
$post = getPostedData();
$_REQUEST['page'] = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
$pagesize = 10;
//calculateDealAmountPaidPayableToMerchant($row['company_id']);exit;
$srch = new SearchBase('tbl_coupon_mark', 'cm');
$srch->addDirectCondition('cm.cm_status IN(' . CONF_MERCHANT_VOUCHER . ')');
$srch->joinTable('tbl_deals', 'INNER JOIN', 'd.deal_id=cm.cm_deal_id AND d.deal_company=' . intval($_GET['company_id']), 'd');
$srch->joinTable('tbl_orders', 'INNER JOIN', 'o.order_id=cm.cm_order_id AND order_payment_status=1', 'o');
$srch->joinTable('tbl_order_deals', 'INNER JOIN', 'od.od_order_id=o.order_id AND od.od_deal_id=cm.cm_deal_id', 'od');
$srch->addFld('SUM(od_deal_price - (IFNULL(deal_commission_percent,0)/100*od_deal_price) - (CASE
                WHEN (d.`deal_charity_discount` IS NOT NULL) 
                THEN
                  (CASE
                WHEN d.deal_charity_discount_is_percent=1
                      THEN d. deal_charity_discount/100*od_deal_price
                      ELSE deal_charity_discount    
                  END )
                ELSE 0
                END))- deal_bonus as calculated_deal_amount');
$srch->joinTable('tbl_companies', 'LEFT OUTER JOIN', 'd.deal_company=c.company_id', 'c');
$srch->addMultipleFields(['cm_counpon_no', 'deal_paid', 'deal_status', 'deal_bonus', 'deal_id', 'deal_name' . $_SESSION['lang_fld_prefix'] . ' as deal_name', 'c.company_name' . $_SESSION['lang_fld_prefix'] . ' as company_name']);
$srch->addCondition('deal_paid', '=', 1);
if (isset($post['btn_submit'])) {
    $company_id = intval($_GET['company_id']);
    $data = ['company_id' => $company_id, 'cwh_start_time' => $post['cwh_start_time'], 'cwh_end_time' => $post['cwh_end_time']];
    $frm->fill($data);
    if (!empty($company_id)) {
        $srch->addCondition('deal_company', '=', $company_id);
    }
    if (!empty($post['cwh_start_time']) && !empty($post['cwh_end_time'])) {
        $srch->addCondition('d.deal_paid_date', 'BETWEEN', [date('Y-m-d', strtotime($post['cwh_start_time'])), date('Y-m-d', strtotime($post['cwh_end_time']))]);
    }
}
$srch->doNotCalculateRecords();
$srch->doNotLimitRecords();
$srch->addGroupBy('deal_id');
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$srch->addGroupBy('d.deal_id');
$srch->addOrder('d.deal_company');
$rs = $srch->getResultSet();
$pagestring = '';
$pagestring .= createHiddenFormFromPost('frmPaging', '?', ['page'], ['page' => $_REQUEST['page']]);
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$deals = $db->fetch_all($rs);
require_once './header.php';
?>
<ul id="content" class="nav-left-ul">
    <li> <a href="companies.php" class="selected"><?php echo t_lang('M_TXT_MERCHANTS'); ?></a> </li>
</ul>
</div>
</td>
<td class="right-portion">
    <?php
    $arr_bread = array(
        'index.php' => '<img alt="Home" src="images/home-icon.png">',
        'javascript:void(0)' => t_lang('M_TXT_DEAL_SETTELED_REPORT'),
        '' => t_lang('M_FRM_Merchants'),
    );
    echo getAdminBreadCrumb($arr_bread);
    ?>
    <?php echo $msg->display(); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_DEAL_SETTELED_REPORT'); ?> </div>
    </div>
    <div class="clear"></div> 
    <div class="box searchform_filter">
        <div class="title"><?php echo t_lang('M_TXT_DEAL_SETTELED_REPORT'); ?> </div>
        <div class="content togglewrap" style="display:none;"><?php echo $frm->getFormHtml(); ?></div>
    </div>
    <?php $total = 0; ?>
    <table width="100%" class="tbl_data">        
        <tr>
            <th width="30%"><?php echo t_lang('M_TXT_COMPANY_NAME'); ?></th>
            <th width="40%"><?php echo t_lang('M_TXT_DEAL_PRODUCT_NAME'); ?></th>
            <th width="30%"><?php echo t_lang('M_TXT_AMOUNT'); ?></th>
        </tr>
        <?php
        foreach ($deals as $deal):
            $total += $deal['calculated_deal_amount'];
            ?>
            <tr>            
                <td width="30%"><?php echo $deal['company_name']; ?></td>
                <td width="40%"><?php echo $deal['deal_name']; ?></td>
                <td width="30%"><?php echo amount($deal['calculated_deal_amount']); ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($deals)): ?>
            <tr><td colspan="3"><?php echo t_lang('M_TXT_NO_RECORD_FOUND') ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($deals)): ?>
            <tr><td colspan="2"><?php echo t_lang('M_TXT_TOTAL_AMOUNT') ?></td><td><?php echo amount($total) ?></td></tr>
        <?php endif; ?>
    </table>
    <?php if ($srch->pages() > 1) { ?>
        <div class="footinfo">
            <aside class="grid_1">
                <?php echo $pagestring; ?>	 
            </aside>  
            <aside class="grid_2"><span class="info"><?php echo $pageStringContent; ?></span></aside>
        </div>
    <?php } ?>
</td>
<?php require_once './footer.php'; ?>
