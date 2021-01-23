<?php
require_once './application-top.php';
require_once './site-classes/user-info.cls.php';
checkAdminPermission(15);
global $db;
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
$frm->addSelectBox('Select Merchant', 'company_id', $companies);
$frm->addSelectBox('Select Category', 'category_id', $categories);
$frm->addSubmitButton('', 'btn_submit', 'Download Report', 'btn_submit');
$frm->setJsErrorDisplay('afterfield');
$post = getPostedData();
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
    // die('In Process');
    $company_id = intval($post['company_id']);
    $category_id = intval($post['category_id']);
    $data = ['company_id' => $company_id, 'category_id' => $category_id];
    $frm->fill($data);
    if (!empty($company_id)) {
        $srch->addCondition('company_id', '=', $company_id);
    }
    if (!empty($category_id)) {
        $srch->addCondition('cat_id', '=', $category_id);
    }
    $srch->addGroupBy('d.deal_id');
    $srch->addGroupBy('cat.cat_id');
    $srch->addOrder('company_id');
// echo $srch->getQuery();die;
    $rs = $srch->getResultSet();
    $output .= "Company Name,Deal Name,Deal Start Date,Deal End Date,No. of Vouchers on Deal,No. of Vouchers Acquired,No. of vouchers unacquired,Acquisition Rate";
    $output .= "\n";
    $deals = $db->fetch_all($rs);
    foreach ($deals as $deal) {
        $output .= '"' . $deal['company_name'] . '","' . $deal['deal_name'] . '","' . $deal['deal_start_time'] . '","' . $deal['deal_end_time'] . '","' . $deal['deal_max_coupons'] . '","' . $deal['acquired'] . '","' . $deal['unacquired'] . '","' . number_format($deal['acquired'] * 100 / $deal['deal_max_coupons'], 0) . '"';
        $output .= "\n";
    }
    $filename = "customers.csv";
    header('Content-type: application/csv');
    header('Content-Disposition: attachment; filename=' . $filename);
    echo $output;
    exit;
}
require_once './header.php';
?>
<td width="230" class="left_portion">
    <div class="left_nav">
        <ul id="content">
            <li> <a href="merchant-report.php" class="selected">Merchants</a> </li>
            <li> <a href="merchant-csv.php">Download Report</a> </li>
        </ul>
    </div>
</td>
<td class="right-portion">
    <?php
    $arr_bread = [
        'index.php' => '<img alt="Home" src="images/home-icon.png">',
        '' => t_lang('M_FRM_Merchants'),
    ];
    echo getAdminBreadCrumb($arr_bread);
    ?>
    <?php echo $msg->display(); ?>
    <div class="clear"></div>    
    <div class="box">
        <div class="title"><?php echo t_lang('M_TXT_MERCHANT_REPORT'); ?> </div>
        <div class="content"><?php echo $frm->getFormHtml(); ?></div>
    </div>
    <?php echo $pagestring; ?>	
    <div class="gap">&nbsp;</div>
    <?php //if(!empty($post)):  ?>
    <table width="100%" class="tbl_data">        
        <tr>
            <th width="10%">Company Name</th>
            <th width="10%">Deal Name</th>
            <th width="15%">Deal Start Date</th>
            <th width="15%">Deal End Date</th>
            <th width="15%">No. of Vouchers on Deal</th>
            <th width="15%">No. of Vouchers Acquired</th>
            <th width="10%">No. of vouchers unacquired</th>
            <th width="10%">Acquisition Rate</th>
        </tr>
        <?php foreach ($deals as $deal) { ?>
            <tr>            
                <td width="10%"><?php echo $deal['company_name']; ?></td>
                <td width="10%"><?php echo $deal['deal_name']; ?></td>
                <td width="15%"><?php echo $deal['deal_start_time']; ?></td>
                <td width="15%"><?php echo $deal['deal_end_time']; ?></td>
                <td width="15%"><?php echo $deal['deal_max_coupons']; ?></td>
                <td width="15%"><?php echo $deal['acquired']; ?></td>
                <td width="15%"><?php echo $deal['unacquired']; ?></td>
                <td width="15%"><?php echo number_format($deal['acquired'] * 100 / $deal['deal_max_coupons'], 0); ?>%</td>
            </tr>
        <?php } ?>
        <?php if (empty($deals)): ?>
            <tr><td colspan="10">No records found.</td></tr>
        <?php endif; ?>
        <?php ?>
    </table>
</td>
