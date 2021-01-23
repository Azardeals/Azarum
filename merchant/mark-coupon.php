<?php
require_once '../application-top.php';
include './update-deal-status.php';
if (!isCompanyUserLogged()) {
    redirectUser(CONF_WEBROOT_URL . 'merchant/login.php');
}
$post = getPostedData();
//Search Form
$Src_frm = new Form('Src_frm', 'Src_frm');
$Src_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_forms" ');
$Src_frm->setFieldsPerRow(3);
$Src_frm->captionInSameCell(true);
$Src_frm->addTextBox('Coupon No', 'order_id', '', '', '');
$Src_frm->addTextBox('Email Address', 'user_email', '', '', '');
$Src_frm->addHiddenField('', 'mode', 'search');
$fld = $Src_frm->addSubmitButton('', 'btn_search', 'Search', '', ' class="inputbuttons"');
$page = (is_numeric($_GET['page']) ? $_GET['page'] : 1);
$pagesize = 30;
$srch = new SearchBase('tbl_order_deals', 'od');
$srch->addCondition('od_deal_id', '=', $_GET['deal_id']);
$srch->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id=o.order_id', 'o');
$srch->joinTable('tbl_coupon_mark', 'INNER JOIN', 'o.order_id=cm.cm_order_id', 'cm');
$srch->joinTable('tbl_users', 'INNER JOIN', 'o.order_user_id=u.user_id', 'u');
$srch->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id=d.deal_id', 'd');
$srch->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=c.company_id and c.company_id=' . $_SESSION['logged_user']['company_id'], 'c');
$srch->addGroupBy('cm.cm_order_id');
$srch->addGroupBy('cm.cm_counpon_no');
if ($post['mode'] == 'search') {
    if ($post['order_id'] != '') {
        $id = $post['order_id'];
        $length = strlen($id);
        if ($length > 13) {
            $order_id = substr($id, 0, 13);
            $LastVouvherNo = ($length - 13);
            $voucher_no = substr($id, 13, $LastVouvherNo);
        } else {
            $order_id = $post['order_id'];
        }
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('od_order_id', '=', $order_id, 'OR ');
        $cnd->attachCondition('od_voucher_suffixes', 'like', '%' . $voucher_no . '%', 'AND');
    }
    if ($post['user_email'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('u.user_email', '=', $post['user_email'], 'OR');
    }
    $Src_frm->fill($post);
}
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
if ($pages > 1) {
    $pagestring .= 'Displaying Page ' . $page . ' of ' . $pages . ' Go to: ';
    $pagestring .= getPageString('<a href="?deal_id=' . $_GET['deal_id'] . '&page=xxpagexx">xxpagexx</a> ', $pages, $page, '<b>xxpagexx</b> ', '....');
}
$arr_listing_fields = array(
    'user_name' => 'User Name',
    'order_id' => 'Order Number',
    'cm_counpon_no' => 'Voucher Number',
    'user_email' => 'Email Address',
    'od_qty' => 'Quantity',
    'order_date' => 'Ordered Date',
    'od_to_name' => 'Gifted to Friend ',
    'order_payment_mode' => 'Mode of Payment',
    'cm_status' => 'Status',
    'action' => 'Option',
);
if ($_GET['used'] != "") {
    $cm_id = $_GET['used'];
    $db->query("update tbl_coupon_mark set cm_status = 1      where cm_id=" . $cm_id);
    redirectUser('mark-coupon.php?deal_id=' . $_GET['deal_id']);
}
$arr_listing = array('user_name' => 'User Name',
    'order_id' => 'Order Number',
    'cm_counpon_no' => 'Voucher Number',
    'user_email' => 'Email Address',
    'od_qty' => 'Quantity',
    'order_date' => 'Ordered Date',
    'od_to_name' => 'Gifted to Friend ',
    'order_payment_mode' => 'Mode of Payment',
    'cm_status' => 'Status'
);
if ($_GET['mode'] == 'downloadcsv') {
    global $db;
    $srch = new SearchBase('tbl_order_deals', 'od');
    $srch->addCondition('od_deal_id', '=', $_GET['deal_id']);
    $srch->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id=o.order_id', 'o');
    $srch->joinTable('tbl_coupon_mark', 'INNER JOIN', 'o.order_id=cm.cm_order_id', 'cm');
    $srch->joinTable('tbl_users', 'INNER JOIN', 'o.order_user_id=u.user_id', 'u');
    $srch->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id=d.deal_id', 'd');
    $srch->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=c.company_id and c.company_id=' . $_SESSION['logged_user']['company_id'], 'c');
    $srch->addGroupBy('cm.cm_order_id');
    $srch->addGroupBy('cm.cm_counpon_no');
    $rs_listing = $srch->getResultSet();
    $fname = time() . '_coupons.csv';
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false);
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"" . $fname . "\";");
    header("Content-Transfer-Encoding: binary");
    $fp = fopen(TEMP_XLS_PATH . $fname, 'w+');
    if (!$fp)
        die('Could not create file in temp-images directory. Please check permissions');
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
                case 'cm_status':
                    if ($row[$key] == 1) {
                        $arr[] = 'Used';
                    } else {
                        $arr[] = 'Unused';
                    }
                    break;
                default:
                    $arr[] = $row[$key];
                    break;
            }
        }
        if (count($arr) > 0)
            fputcsv($fp, $arr);
    }
    fclose($fp);
    header("Content-Length: " . filesize(TEMP_XLS_PATH . $fname));
    readfile(TEMP_XLS_PATH . $fname);
    exit;
}
require_once './header.php';
echo $msg->display();
echo '<div class="tblheading">Coupon Used Listing</div>';
echo $pagestring;
?> 
<div class="form"><?php if (!isset($_GET['add']) AND!isset($_GET['edit'])) echo $Src_frm->getFormHtml(); ?></div>
<ul class="tabs">
    <li ><a href="<?php echo CONF_WEBROOT_URL . 'merchant/mark-coupon.php?mode=downloadcsv&deal_id=' . $_GET['deal_id']; ?>" >Download CSV </a></li>
</ul>
<table class="tbl_listing" width="100%">
    <thead>
        <tr>
            <?php
            foreach ($arr_listing_fields as $val) {
                echo '<th>' . $val . '</th>';
            }
            ?>
        </tr>
    </thead>
    <?php
    for ($listserial = ($page - 1) * $pagesize + 1; $row = $db->fetch($rs_listing); $listserial++) {
        if (($row['od_qty'] + $row['od_gift_qty']) > 0) {
            $od_voucher_suffixes = explode(', ', $row['od_voucher_suffixes']);
            echo '<tr' . (($row[$colPrefix . 'active'] == '0') ? ' class="inactive"' : '') . ' id="' . $row['order_id'] . $voucher . '">';
            foreach ($arr_listing_fields as $key => $val) {
                $order_id = $row['order_id'];
                echo '<td>';
                switch ($key) {
                    case 'cm_status':
                        if ($row[$key] == 1) {
                            echo 'Used';
                        } else if ($row[$key] == 0) {
                            echo 'Not Used';
                        } else {
                            echo 'Refund';
                        }
                        break;
                    case 'cm_counpon_no':
                        echo $row[$key];
                        break;
                    case 'order_payment_mode':
                        if ($row[$key] == 1) {
                            echo 'Paypal';
                        } else if ($row[$key] == 2) {
                            echo 'AuthorizeNet';
                        } else {
                            echo 'Wallet';
                        }
                        break;
                    case 'order_id':
                        if (($row['od_qty']) > 0) {
                            echo $row['order_id'] . $row['cm_counpon_no'];
                        } else if (($row['od_gift_qty']) > 0) {
                            echo $row['order_id'] . $row['cm_counpon_no'];
                        } else {
                            echo $row['order_id'] . $row['cm_counpon_no'];
                        }
                        break;
                    case 'od_qty':
                        echo '1';
                        break;
                    case 'action':
                        if ($row['cm_status'] == 0) {//
                            $order = "'" . $row['order_id'] . $voucher . "'";
                            echo '&nbsp;<a   href="?deal_id=' . $_GET['deal_id'] . '&used=' . $row['cm_id'] . '" title="Update Payment Status To Used" onclick="requestPopup(this,\'' . t_lang('M_MSG_Are_you_sure_to_change_the_coupon_status_to_used_this_process_will_not_be_revert_back?') . '\',1);"><img src="images/Approve_user.png"></a> ';
                        }
                        break;
                    default:
                        echo $row[$key];
                        break;
                }
                echo '</td>';
            }
            echo '</tr>';
        }
    }
    if ($db->total_records($rs_listing) == 0) {
        echo '<tr><td colspan="' . count($arr_listing_fields) . '">No records found.</td></tr>';
    }
    ?>
</table>
<?php
require_once './footer.php';

