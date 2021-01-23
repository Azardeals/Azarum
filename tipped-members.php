<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
if (!isCompanyUserLogged()) {
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'merchant-login.php'));
}
$page = (is_numeric($_GET['page']) ? $_GET['page'] : 1);
$pagesize = 50;
$arr_listing = [
    'order_id' => 'Order ID',
    'user_name' => 'User Name',
    'user_email' => 'Email Address',
    'od_qty' => 'Quantity',
    'order_date' => 'Ordered Date',
    'od_to_name' => 'Gifted to Friend ',
    'od_to_email' => 'Friend Email Address ',
    'order_payment_mode' => 'Mode of Payment',
    'order_payment_status' => 'Payment Status'
];
if ($_GET['mode'] == 'downloadcsv') {
    global $db;
    $srch = new SearchBase('tbl_order_deals', 'od');
    $srch->addCondition('od_deal_id', '=', $_GET['deal_id']);
    $srch->joinTable('tbl_orders', 'LEFT OUTER JOIN', 'od.od_order_id=o.order_id', 'o');
    $srch->joinTable('tbl_users', 'LEFT OUTER JOIN', 'o.order_user_id=u.user_id', 'u');
    $rs_listing = $srch->getResultSet();
    $fname = time() . '_subscribers.xls';
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false);
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"" . $fname . "\";");
    header("Content-Transfer-Encoding: binary");
    $fp = fopen(TEMP_XLS_PATH . $fname, 'w+');
    if (!$fp) {
        die('Could not create file in temp-images directory. Please check permissions');
    }
    fputcsv($fp, $arr_listing);
    while ($row = $db->fetch($rs_listing)) {
        $arr = [];
        foreach ($arr_listing as $key => $val) {
            switch ($key) {
                case 'order_id':
                    $arr[] = $row[$key];
                    break;
                case 'user_name':
                    $arr[] = $row[$key];
                    break;
                case 'user_email':
                    $arr[] = $row[$key];
                    break;
                case 'od_qty':
                    $arr[] = $row[$key];
                    break;
                case 'order_date':
                    $arr[] = $row[$key];
                    break;
                case 'od_to_name':
                    $arr[] = $row[$key];
                    break;
                case 'od_to_email':
                    $arr[] = $row[$key];
                    break;
                case 'order_payment_mode':
                    if ($row[$key] == 1) {
                        $arr[] = 'Paypal';
                    } else if ($row[$key] == 2) {
                        $arr[] = 'AuthorizeNet';
                    } else {
                        $arr[] = 'Wallet';
                    }
                    break;
                case 'order_payment_status':
                    if ($row[$key] == 1) {
                        $arr[] = 'Paid';
                    } else {
                        $arr[] = 'Pending';
                    }
                    break;
                default:
                    $arr[] = $row[$key];
                    break;
            }
        }
        if (count($arr) > 0) {
            fputcsv($fp, $arr);
        }
    }
    fclose($fp);
    header("Content-Length: " . filesize(TEMP_XLS_PATH . $fname));
    readfile(TEMP_XLS_PATH . $fname);
    exit;
}
$srch = new SearchBase('tbl_order_deals', 'od');
$srch->addCondition('od_deal_id', '=', $_GET['deal_id']);
$srch->joinTable('tbl_orders', 'LEFT OUTER JOIN', 'od.od_order_id=o.order_id', 'o');
$srch->joinTable('tbl_users', 'LEFT OUTER JOIN', 'o.order_user_id=u.user_id', 'u');
$status = $_GET['status'];
if ($status == 'paid') {
    $srch->addCondition('order_payment_status', '=', 1);
} else if ($status == 'pending') {
    $srch->addCondition('order_payment_status', '=', 0);
} else {
    $srch->addCondition('order_payment_status', '=', 1);
}
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
//echo $srch->getQuery();
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
if ($pages > 1) {
    $pagestring .= '<ul class="paging"><li class="space">';
    $pagestring .= 'Displaying Page ' . $page . ' of ' . $pages . ' Go to:</li> <ul class="paging">';
    $pagestring .= getPageString('<li><a href="' . friendlyUrl(CONF_WEBROOT_URL . 'tipped-members.php?deal_id=' . $_GET['deal_id'] . '&status=' . $_GET['status'] . '&page=xxpagexx') . '">xxpagexx</a></li> ', $pages, $page, '<li><a class="still" href="javascript:void(0);">xxpagexx</a></li> ', '....');
    $pagestring .= '</ul>';
}
$arr_listing_fields = [
    'listserial' => 'S.N.',
    'order_id' => 'Order ID',
    'user_name' => 'User Name',
    'user_email' => 'Email Address',
    'od_qty' => 'Quantity',
    'order_date' => 'Ordered Date',
    'od_to_name' => 'Gifted to Friend ',
    'od_to_email' => 'Friend Email Address ',
    'order_payment_mode' => 'Mode of Payment',
    'order_payment_status' => 'Payment Status'
];
require_once './header.php';
if ($_GET['status'] == "pending") {
    $tabClass = '';
    $class = 'class="current"';
} else {
    $tabStatus = $_GET['status'];
    $tabClass = 'class="current"';
    $class = ' ';
}
?>
<!--body start here-->
<div id="body">
    <div id="center_Wrapper">
        <div class="center_intro_Wrap">
            <ul class="intro_navs">
                <li ><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'tipped-members.php?deal_id=' . $_GET['deal_id'] . '&status=paid&page=1') ?>" <?php echo $tabClass; ?>><span><?php echo t_lang('M_TXT_PAID'); ?></span></a></li>
                <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'tipped-members.php?deal_id=' . $_GET['deal_id'] . '&status=pending&page=1') ?>" <?php echo $class; ?>><span><?php echo t_lang('M_TXT_PENDING'); ?></span></a></li>
                <li ><a href="<?php echo CONF_WEBROOT_URL . 'tipped-members.php?mode=downloadcsv&deal_id=' . $_GET['deal_id']; ?>" ><span><?php echo t_lang('M_TXT_DOWNLOAD_XLS'); ?></span> </a></li>
                <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'company-amount.php?deal_id=' . $_GET['deal_id']); ?>" ><span><?php echo t_lang('M_TXT_AMOUNTS'); ?></span></a></li>
                <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'company-deals.php'); ?>" ><span><?php echo t_lang('M_TXT_DEALS'); ?></span></a></li>
                <li ><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'merchant-account.php'); ?>" ><span><?php echo t_lang('M_TXT_MY_ACCOUNT'); ?></span></a></li>
            </ul>
        </div>
        <div class="center_Wrap">
            <!--account_area start here-->
            <div class="account_area">
                <div class="account_wrapper">
                    <div class="account_wrap" style="width:935px;">
                        <div class="account_tablewrap" style="width:935px;">
                            <?php
                            if (is_numeric($_GET['edit']) || $_GET['add'] == 'new') {
                                echo '<h2>Add Deal Images</h2>';
                                echo $frm->getFormHtml();
                            } else {
                                echo $pagestring;
                                ?>
                                <?php echo $msg->display(); ?>
                                <table width="100%" border="0" cellpadding="0" cellspacing="0" class="data_table" style="width:935px;">
                                    <thead>
                                        <tr>
                                            <?php
                                            foreach ($arr_listing_fields as $key => $val)
                                                echo '<th style="line-height:25px;padding:0 5px 0 5px!important;text-align:center;" ' . (($key == 'listserial' || $key == 'od_qty') ? ' width="5%"' : '') . (($key == 'order_payment_mode' || $key == 'order_id' || $key == 'user_name' || $key == 'order_payment_status' || $key == 'od_to_name' || $key == 'od_to_email') ? '  width="10%"' : '') . (($key == 'order_date' || $key == 'user_email' ) ? '   width="15%"' : '') . '>' . $val . '</th>';
                                            ?>
                                        </tr>
                                    </thead>
                                    <?php
                                    for ($listserial = ($page - 1) * $pagesize + 1; $row = $db->fetch($rs_listing); $listserial++) {
                                        echo '<tr' . (($row[$colPrefix . 'active'] == '0') ? ' class="inactive"' : '') . ' >';
                                        foreach ($arr_listing_fields as $key => $val) {
                                            echo '<td style="text-align:center;padding:0 5px 0 5px!important;">';
                                            switch ($key) {
                                                case 'listserial':
                                                    echo $listserial;
                                                    break;
                                                case 'order_payment_status':
                                                    if ($row[$key] == 1) {
                                                        echo 'Paid';
                                                    } else {
                                                        echo 'Pending';
                                                    }
                                                    //echo $arr_deal_status[$row[$key]];
                                                    break;
                                                case 'order_payment_mode':
                                                    if ($row[$key] == 1) {
                                                        echo 'Paypal';
                                                    } else if ($row[$key] == 2) {
                                                        echo 'AuthorizeNet';
                                                    } else {
                                                        echo 'Wallet';
                                                    }
                                                    // echo(($row[$key]=='')?'---':$row[$key]);
                                                    break;
                                                default:
                                                    echo $row[$key];
                                                    break;
                                            }
                                            echo '</td>';
                                        }
                                        echo '</tr>';
                                    }
                                    if ($db->total_records($rs_listing) == 0)
                                        echo '<tr><td colspan="' . count($arr_listing_fields) . '">No records found.</td></tr>';
                                    ?>
                                </table>  
                            <?php } ?>
                        </div> 
                    </div>
                </div>
            </div>
            <!--account_area end here-->
        </div>
        <img src="<?php echo CONF_WEBROOT_URL; ?>images/center_main_bottom.png" alt="" />
    </div>
    <div class="clear"></div>    
</div> 
<!--body end here-->      
<div class="clear"></div>
<?php require_once './footer.php'; ?>
