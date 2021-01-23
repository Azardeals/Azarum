<?php
require_once './application-top.php';
checkAdminPermission(5);
include './update-deal-status.php';
$post = getPostedData();
//Search Form
$Src_frm = new Form('Src_frm', 'Src_frm');
$Src_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%" ');
$Src_frm->setFieldsPerRow(1);
$Src_frm->captionInSameCell(false);
$Src_frm->addTextBox('Voucher Code', 'order_id', '', '', '');
$Src_frm->addTextBox('Email Address', 'user_email', '', '', '');
$Src_frm->addTextBox('Identity (Card DNI)', 'user_identity_card', '', '', '');
$Src_frm->addTextBox('Membership Number', 'user_member_id', '', '', '');
$Src_frm->addHiddenField('', 'mode', 'search');
$fld = $Src_frm->addSubmitButton('', 'btn_search', 'Search', '', ' class="inputbuttons"');
$page = (is_numeric($_GET['page']) ? $_GET['page'] : 1);
$pagesize = 30;
/* get records from db */
$srch = new SearchBase('tbl_coupon_mark', 'cm');
$srch->joinTable('tbl_order_deals', 'INNER JOIN', "cm.cm_order_id=od.od_order_id AND od.od_voucher_suffixes LIKE CONCAT('%', cm.cm_counpon_no, '%')", 'od');
$srch->addCondition('od.od_deal_id', '=', $_GET['deal_id']);
$srch->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id = o.order_id', 'o');
$srch->joinTable('tbl_users', 'INNER JOIN', 'o.order_user_id=u.user_id', 'u');
/** search mode * */
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
        $cnd->attachCondition('od.od_order_id', '=', $order_id, 'OR ');
        $cnd->attachCondition('cm.cm_counpon_no', 'like', '%' . $voucher_no . '%', 'AND');
    }
    if ($post['user_email'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('u.user_email', '=', $post['user_email'], 'OR');
    }
    if ($post['user_identity_card'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('u.user_identity_card', '=', $post['user_identity_card'], 'OR');
    }
    if ($post['user_member_id'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('u.user_member_id', '=', $post['user_member_id'], 'OR');
    }
    $Src_frm->fill($post);
}
/** search mode ends * */
$srch->addMultipleFields(array('od.od_order_id', 'od.od_to_name', 'u.user_name', 'u.user_email', 'o.order_date', 'o.order_payment_mode', 'cm.cm_id', 'cm.cm_status', 'cm.cm_counpon_no'));
if ($_GET['mode'] != 'downloadcsv') {
    $srch->setPageNumber($page);
    $srch->setPageSize($pagesize);
}
$srch->addOrder('o.order_date', 'desc');
$result = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
if ($pages > 1) {
    $pagestring .= '<div class="pagination fr"><ul><li><a href="javascript:void(0);">Displaying records ' . (($page - 1) * $pagesize + 1) .
            ' to ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' of ' . $srch->recordCount() . '</a></li>';
    $pagestring .= '<li><a href="javascript:void(0);">Goto Page: </a></li>
		' . getPageString('<li><a href="?deal_id=' . $_REQUEST['deal_id'] . '&page=xxpagexx"  >xxpagexx</a> </li> '
                    , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
    $pagestring .= '</div>';
}
$arr_listing_fields = [
    'listserial' => 'S.N.',
    'user_name' => 'User Name',
    'order_id' => 'Voucher Code',
    'user_email' => 'Email Address',
    'od_qty' => 'Quantity',
    'order_date' => 'Ordered Date',
    'od_to_name' => 'Gifted to Friend ',
    'order_payment_mode' => 'Mode of Payment',
    'cm_status' => 'Status',
    'action' => 'Option'
];
if ($_GET['used'] != "") {
    $cm_id = $_GET['used'];
    $db->query("update tbl_coupon_mark set cm_status = 1      where cm_id=" . $cm_id);
    redirectUser('mark-coupon.php?deal_id=' . $_GET['deal_id']);
}
$arr_listing = [
    'user_name' => 'User Name',
    'order_id' => 'Voucher Code',
    'user_email' => 'Email Address',
    'od_qty' => 'Quantity',
    'order_date' => 'Ordered Date',
    'od_to_name' => 'Gifted to Friend ',
    'order_payment_mode' => 'Mode of Payment',
    'cm_status' => 'Status'
];
if ($_GET['mode'] == 'downloadcsv') {
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
    while ($row = $db->fetch($result)) {
        $arr = [];
        foreach ($arr_listing as $key => $val) {
            switch ($key) {
                case 'order_id':
                    $arr[] = $row['od_order_id'] . $row['cm_counpon_no'];
                    break;
                case 'user_name':
                    $arr[] = $row[$key];
                    break;
                case 'user_email':
                    $arr[] = $row[$key];
                    break;
                case 'od_qty':
                    $arr[] = 1;
                    break;
                case 'order_date':
                    $arr[] = $row[$key];
                    break;
                case 'od_to_name':
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
?> 
<ul class="nav-left-ul">
    <li><a href="mark-coupon.php?mode=downloadcsv&deal_id=<?php echo $_GET['deal_id']; ?>" >Download CSV </a></li>
</ul>
</div></td>
<td class="right-portion"><?php //echo getAdminBreadCrumb($arr_bread);               ?>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
        <div class="box" id="messages">
            <div class="title-msg"> System messages <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;">Hide</a></div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="redtext"><?php echo $msg->display(); ?> </div>
                    <br/>
                    <br/>
                <?php } if (isset($_SESSION['msgs'][0])) { ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?> 
    <div class="box"><div class="title"> Coupon Used Listing </div><div class="content">		<?php echo $Src_frm->getFormHtml(); ?>
            <?php echo $pagestring; ?>		
            <div class="gap">&nbsp;</div>		 
            <table class="tbl_data" width="100%">
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
                for ($listserial = ($page - 1) * $pagesize + 1; $row = $db->fetch($result); $listserial++) {
                    foreach ($arr_listing_fields as $key => $val) {
                        echo '<td>';
                        switch ($key) {
                            case 'listserial':
                                echo $listserial;
                                break;
                            case 'cm_status':
                                if ($row[$key] == 1) {
                                    echo 'Used';
                                } else if ($row[$key] == 0) {
                                    echo 'Not Used';
                                } else {
                                    echo 'Refund';
                                }
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
                                echo $row['od_order_id'] . $row['cm_counpon_no'];
                                break;
                            case 'od_qty':
                                echo 1;
                                break;
                            case 'action':
                                if ($row['cm_status'] == 0) {
                                    $order = "'" . $row['order_id'] . $voucher . "'";
                                    echo '&nbsp;<a   href="?deal_id=' . $_GET['deal_id'] . '&used=' . $row['cm_id'] . '" title="Update Payment Status To Used" onclick="requestPopup(this,\'Are you sure to change the coupon status to used this process will not be revert back?\',1);" class="btn delete">Mark Used</a> ';
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
                if ($db->total_records($result) == 0) {
                    echo '<tr><td colspan="' . count($arr_listing_fields) . '">No records found.</td></tr>';
                }
                ?>
            </table>
        </div></div>
</td>
<?php require_once './footer.php'; ?>
