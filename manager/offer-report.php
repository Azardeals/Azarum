<?php
require_once './application-top.php';
require_once './site-classes/user-info.cls.php';
checkAdminPermission(15);
global $db;
$srch = new SearchBase('tbl_companies');
$srch->addMultipleFields(['company_id', 'company_name']);
$rs = $srch->getResultSet();
$companies = $db->fetch_all_assoc($rs);
$frm = new Form('frmOfferReport');
$frm->setTableProperties('class="tbl_form" width="100%"');
$frm->addRequiredField('Offer Name', 'deal_name');
$frm->addSubmitButton('', 'btn_submit', 'Submit', 'btn_submit');
$frm->setJsErrorDisplay('afterfield');
$post = getPostedData();
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
$pagesize = 10;
$srch = new SearchBase('tbl_users', 'u');
$srch->joinTable('tbl_cities', 'INNER JOIN', 'u.user_city= city.city_id', 'city');
$srch->joinTable('tbl_orders', 'INNER JOIN', 'u.user_id = o.order_user_id', 'o');
$srch->joinTable('tbl_order_deals', 'INNER JOIN', 'o.order_id = od.od_order_id', 'od');
$srch->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id = d.deal_id', 'd');
$srch->addMultipleFields([
    'deal_id', 'deal_name',
    'ROUND(SUM(ROUND(DATEDIFF(NOW(),user_dob)/365,0))/COUNT(user_id),0) AS avg_age',
    'city_name', 'IF(user_gender="M", "Male", "Female") as gender', 'user_city', 'user_dob',
    'ROUND(DATEDIFF(NOW(),user_dob)/365,0) AS age',
    'CASE WHEN ROUND(DATEDIFF(NOW(),user_dob)/365,0) BETWEEN 0 AND 9 THEN "0-9" 
          WHEN ROUND(DATEDIFF(NOW(),user_dob)/365,0) BETWEEN 10 AND 19 THEN "10-19"
          WHEN ROUND(DATEDIFF(NOW(),user_dob)/365,0) BETWEEN 20 AND 29 THEN "20-29"
          WHEN ROUND(DATEDIFF(NOW(),user_dob)/365,0) BETWEEN 30 AND 39 THEN "30-39"
          WHEN ROUND(DATEDIFF(NOW(),user_dob)/365,0) BETWEEN 40 AND 49 THEN "40-49"
          WHEN ROUND(DATEDIFF(NOW(),user_dob)/365,0) BETWEEN 50 AND 59 THEN "50-59"
          WHEN ROUND(DATEDIFF(NOW(),user_dob)/365,0) BETWEEN 60 AND 69 THEN "60-69"
          WHEN ROUND(DATEDIFF(NOW(),user_dob)/365,0) BETWEEN 70 AND 79 THEN "70-79"
          WHEN ROUND(DATEDIFF(NOW(),user_dob)/365,0) BETWEEN 80 AND 89 THEN "80-89"
          WHEN ROUND(DATEDIFF(NOW(),user_dob)/365,0) BETWEEN 90 AND 99 THEN "90-99"
          END avg_age_range',
    'IF(od.od_deal_id, SUM(CASE WHEN o.order_payment_status=1 THEN od.od_qty+od.od_gift_qty ELSE 0 END), 0) AS acquired',
    '"" AS best_day',
    '"" AS best_time',
]);
if ($post['btn_submit']) {
    $deal_promo_code = $post['deal_promo_code'];
    $data = array('deal_promo_code' => $deal_promo_code, 'category_id' => $category_id);
    $frm->fill($data);
    $srch->addCondition('deal_promo_code', '=', $deal_promo_code);
}
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$srch->addGroupBy('deal_id,city_id, user_gender, avg_age_range');
$srch->addOrder('deal_id asc, city_id, user_gender DESC, avg_age_range');
$rs = $srch->getResultSet();
$pagestring = '';
if ($srch->pages() > 1) {
    $pagestring .= createHiddenFormFromPost('frmPaging', '?', ['page'], ['page' => $_REQUEST['page']]);
    $pagestring .= '<div class="pagination fr"><ul><li><a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
            ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a></li>';
    $pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ' </a></li>
    ' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                    , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
    $pagestring .= '</div>';
}
$deals = $db->fetch_all($rs);
$query = 'SELECT 
            CASE WHEN ROUND(DATEDIFF(NOW(),user_dob)/365,0) BETWEEN 0 AND 9 THEN "0-9" 
            WHEN ROUND(DATEDIFF(NOW(),user_dob)/365,0) BETWEEN 10 AND 19 THEN "10-19"
            WHEN ROUND(DATEDIFF(NOW(),user_dob)/365,0) BETWEEN 20 AND 29 THEN "20-29"
            WHEN ROUND(DATEDIFF(NOW(),user_dob)/365,0) BETWEEN 30 AND 39 THEN "30-39"
            WHEN ROUND(DATEDIFF(NOW(),user_dob)/365,0) BETWEEN 40 AND 49 THEN "40-49"
            WHEN ROUND(DATEDIFF(NOW(),user_dob)/365,0) BETWEEN 50 AND 59 THEN "50-59"
            WHEN ROUND(DATEDIFF(NOW(),user_dob)/365,0) BETWEEN 60 AND 69 THEN "60-69"
            WHEN ROUND(DATEDIFF(NOW(),user_dob)/365,0) BETWEEN 70 AND 79 THEN "70-79"
            WHEN ROUND(DATEDIFF(NOW(),user_dob)/365,0) BETWEEN 80 AND 89 THEN "80-89"
            WHEN ROUND(DATEDIFF(NOW(),user_dob)/365,0) BETWEEN 90 AND 99 THEN "90-99"
            END avg_age_range,
            IF(user_gender="M", "Male", "Female") as gender,
            user_city, deal_id,  COUNT(order_id),
            DATE_FORMAT(order_date, "%Y-%m-%d") AS best_day,
            DATE_FORMAT(order_date, "%H:%i") AS best_time
            FROM tbl_orders o
            INNER JOIN tbl_order_deals od ON o.order_id = od.od_order_id
            INNER JOIN tbl_deals d ON od.od_deal_id = d.deal_id            
            INNER JOIN tbl_users u ON u.user_id=o.order_user_id            
            GROUP BY deal_id, user_city, gender, avg_age_range
            ORDER BY deal_id, COUNT(order_id) DESC, avg_age_range';
$best = $db->fetch_all($db->query($query));
foreach ($deals AS $i => $deal) {
    foreach ($best as $b) {
        if ($deal['deal_id'] == $b['deal_id'] && $deal['gender'] == $b['gender'] && $deal['user_city'] == $b['user_city'] && $deal['avg_age_range'] == $b['avg_age_range']) {
            $deals[$i]['best_day'] = $b['best_day'];
            $deals[$i]['best_time'] = $b['best_time'];
        }
    }
}
require_once './header.php';
?>
<ul id="content" class="nav-left-ul">
    <li> <a href="merchant-report.php">Merchants</a> </li>
    <li> <a href="offer-report.php" class="selected">Offers</a> </li>
</ul>
</div>
</td>
<td class="right-portion">
    <?php
    $arr_bread = [
        'index.php' => '<img alt="Home" src="images/home-icon.png">',
        '' => t_lang('M_FRM_Offers'),
    ];
    echo getAdminBreadCrumb($arr_bread);
    ?>
    <?php echo $msg->display(); ?>
    <div class="clear"></div>    
    <div class="box">
        <div class="title"><?php echo t_lang('M_TXT_OFFER_REPORT'); ?> </div>
        <div class="content"><?php echo $frm->getFormHtml(); ?></div>
    </div>
    <?php echo $pagestring; ?>	
    <div class="gap">&nbsp;</div>
    <?php //if(!empty($post)):  ?>
    <table width="100%" class="tbl_data">        
        <tr>
            <th width="10%">Offer ID</th>
            <th width="10%">Customer City</th>
            <th width="15%">Customer Gender</th>
            <th width="15%">Customer Avg. Age</th>
            <th width="15%">Customer Avg. Age Range</th>
            <th width="10%">Best Day</th>
            <th width="10%">Best Time</th>
            <th width="15%">No. of Vouchers Acquired</th>
        </tr>
        <?php foreach ($deals as $deal): ?>
            <tr>            
                <td width="10%"><?php echo $deal['deal_name']; ?></td>
                <td width="15%"><?php echo $deal['city_name']; ?></td>
                <td width="15%"><?php echo $deal['gender']; ?></td>
                <td width="15%"><?php echo $deal['avg_age']; ?></td>
                <td width="15%"><?php echo $deal['avg_age_range']; ?></td>
                <td width="15%"><?php echo $deal['best_day']; ?></td>
                <td width="15%"><?php echo $deal['best_time']; ?></td>
                <td width="15%"><?php echo $deal['acquired']; ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($deals)): ?>
            <tr><td colspan="10">No records found.</td></tr>
        <?php endif; ?>
    </table>
</td>
<?php require_once './footer.php'; ?>
