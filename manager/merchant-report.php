<?php
require_once 'application-top.php';
require_once '../site-classes/user-info.cls.php';
checkAdminPermission(15);
global $db;
$post = getPostedData();
$srch = new SearchBase('tbl_companies');
$srch->addMultipleFields(['company_id', 'company_name']);
$rs = $srch->getResultSet();
$companies = $db->fetch_all_assoc($rs);
$srch = new SearchBase('tbl_deal_categories');
$srch->addMultipleFields(['cat_id', 'cat_name']);
$rs = $srch->getResultSet();
$categories = $db->fetch_all_assoc($rs);
$frm = new Form('frmMerchantReport');
$frm->setTableProperties('class="tbl_form" width="100%"');
$frm->addSelectBox(t_lang('M_FRM_SELECT_MERCHANT'), 'company_id', $companies);
$frm->addSelectBox(t_lang('M_FRM_SELECT_CATEGORY'), 'category_id', $categories);
$frm->addTextBox(t_lang('M_FRM_KEYWORD'), 'keyword', '', '', '');
$frm->addTextBox(t_lang('M_FRM_NUMBER_OF_VOUCHERS_ACQUIRED'), 'vouchers_acquired', '', '', '');
$frm->setFieldsPerRow(4);
$frm->captionInSameCell(true);
$fld1 = $frm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="inputbuttons" onclick=location.href="merchant-report.php"');
$fld = $frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SEARCH'), '', ' class="inputbuttons"');
$fld2 = $frm->addSubmitButton('', 'btn_download', t_lang('M_TXT_DOWNLOAD_REPORT'), 'btn_submit');
$fld->attachField($fld1);
$fld1->attachField($fld2);
$frm->setJsErrorDisplay('afterfield');
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
$pagesize = 10;
$srch = new SearchBase('tbl_companies', 'c');
$srch->joinTable('tbl_deals', 'INNER JOIN', 'c.company_id=d.deal_company', 'd');
$srch->joinTable('tbl_deal_to_category', 'INNER JOIN', 'd.deal_id=dcat.dc_deal_id', 'dcat');
$srch->joinTable('tbl_deal_categories', 'INNER JOIN', 'cat.cat_id=dcat.dc_cat_id', 'cat');
$srch->joinTable('tbl_order_deals', 'LEFT JOIN', 'd.deal_id = od.od_deal_id', 'od');
$srch->joinTable('tbl_orders', 'LEFT JOIN', 'od.od_order_id= o.order_id', 'o');
$srch->addMultipleFields([
    'distinct deal_id',
    'deal_name',
    'deal_start_time',
    'deal_end_time',
    'deal_city',
    'd.deal_max_coupons',
    'IF(od.od_deal_id, SUM(CASE WHEN o.order_payment_status=1 THEN od.od_qty+od.od_gift_qty ELSE 0 END), 0) AS acquired',
    '(d.deal_max_coupons - SUM(CASE WHEN o.order_payment_status=1 THEN od.od_qty+od.od_gift_qty ELSE 0 END)) AS unacquired',
    '(SELECT DATE_FORMAT(order_date, "%Y-%m-%d") FROM tbl_orders o1 INNER JOIN tbl_order_deals od1 ON od1.od_order_id=o1.order_id WHERE od1.od_deal_id = d.deal_id GROUP BY DATE_FORMAT(order_date, "%d %m %Y") ORDER BY COUNT(order_id) DESC LIMIT 1) AS best_day',
    '(SELECT DATE_FORMAT(order_date, "%H:%i") FROM tbl_orders o2 INNER JOIN tbl_order_deals od2 ON od2.od_order_id=o2.order_id WHERE od2.od_deal_id = d.deal_id GROUP BY DATE_FORMAT(order_date, "%H:%i") ORDER BY COUNT(order_id) DESC LIMIT 1) AS best_time',
    'company_id',
    'company_name',
    'order_id'
]);
if ($post['btn_submit']) {
    $company_id = intval($post['company_id']);
    $category_id = intval($post['category_id']);
    if ($post['keyword'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('d.deal_name' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
    }
    if ($post['vouchers_acquired'] != '') {
        $srch->addHaving('acquired', '=', $post['vouchers_acquired'], 'AND');
    }
    $frm->fill($post);
    if (!empty($company_id))
        $srch->addCondition('company_id', '=', $company_id);
    if (!empty($category_id))
        $srch->addCondition('cat_id', '=', $category_id);
}
if ($post['btn_download']) {
    $company_id = intval($post['company_id']);
    $category_id = intval($post['category_id']);
    if (!empty($company_id)) {
        $srch->addCondition('company_id', '=', $company_id);
    }
    if (!empty($category_id)) {
        $srch->addCondition('cat_id', '=', $category_id);
    }
    $srch->addGroupBy('d.deal_id');
    $srch->addGroupBy('cat.cat_id');
    $srch->addOrder('company_id');
    $rs = $srch->getResultSet();
    $output .= "Company Name,Deal Name,Deal Start Date,Deal End Date,No. of Vouchers on Deal,No. of Vouchers Acquired,No. of vouchers unacquired,Acquisition Rate";
    $output .= "\n";
    $deals = $db->fetch_all($rs);
    foreach ($deals as $deal) {
        $output .= '"' . $deal['company_name'] . '","' . $deal['deal_name'] . '","' . displayDate($deal['deal_start_time'], true) . '","' . displayDate($deal['deal_end_time'], true) . '","' . $deal['deal_max_coupons'] . '","' . $deal['acquired'] . '","' . $deal['unacquired'] . '","' . number_format($deal['acquired'] * 100 / $deal['deal_max_coupons'], 0) . '%"';
        $output .= "\n";
    }
    $filename = "merchants.csv";
    header('Content-type: application/csv');
    header('Content-Disposition: attachment; filename=' . $filename);
    echo $output;
    exit;
}
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$srch->addGroupBy('d.deal_id');
$srch->addGroupBy('cat.cat_id');
$srch->addOrder('od_id', 'DESC');
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
    <li> <a href="merchant-report.php" class="selected"><?php echo t_lang('M_TXT_MERCHANTS'); ?></a> </li>
    <!--  <li> <a href="merchant-csv.php">Download Report</a> </li> -->
</ul>
</div>
</td>
<td class="right-portion">
    <?php
    $arr_bread = [
        'index.php' => '<img alt="Home" src="images/home-icon.png">',
        'javascript:void(0)' => t_lang('M_TXT_REPORTS'),
        '' => t_lang('M_FRM_Merchants'),
    ];
    echo getAdminBreadCrumb($arr_bread);
    ?>
    <?php echo $msg->display(); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_MERCHANT_REPORT'); ?> </div>
    </div>
    <div class="clear"></div>
    <div class="box searchform_filter">
        <div class="title"><?php echo t_lang('M_TXT_MERCHANT_REPORT'); ?> </div>
        <div class="content togglewrap" style="display:none;"><?php echo $frm->getFormHtml(); ?></div>
    </div>
    <?php //if(!empty($post)):   ?>
    <table width="100%" class="tbl_data">
        <tr>
            <th width="10%"><?php echo t_lang('M_TXT_COMPANY_NAME'); ?></th>
            <th width="10%"><?php echo t_lang('M_TXT_DEAL_NAME'); ?></th>
            <th width="15%"><?php echo t_lang('M_TXT_DEAL_START_DATE'); ?></th>
            <th width="15%"><?php echo t_lang('M_TXT_DEAL_END_DATE'); ?></th>
            <th width="15%"><?php echo t_lang('M_TXT_NO._OF_VOUCHERS_ON_DEAL'); ?></th>
            <th width="15%"><?php echo t_lang('M_TXT_NO._OF_VOUCHERS_ACQUIRED'); ?></th>
            <th width="10%"><?php echo t_lang('M_TXT_NO._OF_VOUCHERS_UNACQUIRED'); ?></th>
            <th width="10%"><?php echo t_lang('M_TXT_ACQUISITION_RATE'); ?></th>
        </tr>
        <?php foreach ($deals as $deal) { ?>
            <tr>
                <td width="10%"><?php echo $deal['company_name']; ?></td>
                <td width="10%"><?php echo $deal['deal_name']; ?></td>
                <td width="15%"><?php echo displayDate($deal['deal_start_time'], true); ?></td>
                <td width="15%"><?php echo displayDate($deal['deal_end_time'], true); ?></td>
                <td width="15%"><?php echo $deal['deal_max_coupons']; ?></td>
                <td width="15%"><?php echo $deal['acquired']; ?></td>
                <td width="15%"><?php echo $deal['unacquired']; ?></td>
                <td width="15%"><?php echo number_format($deal['acquired'] * 100 / $deal['deal_max_coupons'], 0); ?>%</td>
            </tr>
        <?php } ?>
        <?php if (empty($deals)): ?>
            <tr><td colspan="10"><?php echo t_lang('M_TXT_NO_RECORD_FOUND') ?></td></tr>
        <?php endif; ?>
    </table>
    <?php //endif;  ?>
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
