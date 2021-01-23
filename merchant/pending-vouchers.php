<?php
require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
require_once '../includes/site-functions.php';
require_once '../includes/buy-deal-functions.php';
require_once '../site-classes/order.cls.php';
require_once '../site-classes/deal-info.cls.php';
if (!isCompanyUserLogged()) {
    redirectUser(CONF_WEBROOT_URL . 'merchant/login.php');
}
include './update-deal-status.php';
$post = getPostedData();
//Search Form
$Src_frm = new Form('Src_frm', 'Src_frm');
$Src_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$Src_frm->setFieldsPerRow(3);
$Src_frm->captionInSameCell(true);
$Src_frm->addTextBox(t_lang('M_TXT_VOUCHER_CODE'), 'order_id', '', '', '');
$Src_frm->addTextBox(t_lang('M_FRM_EMAIL_ADDRESS'), 'user_email', '', '', '');
$Src_frm->addHiddenField('', 'mode', 'search');
$fld1 = $Src_frm->addButton('', 'btn_search', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="inputbuttons" onclick=location.href="tipped-members.php?deal_id=' . $_GET['deal_id'] . '"');
$fld = $Src_frm->addSubmitButton('', 'btn_cancel', t_lang('M_TXT_SEARCH'), '', ' class="inputbuttons"')->attachField($fld1);
$page = (is_numeric($_GET['page']) ? $_GET['page'] : 1);
$pagesize = 30;
/* get records from db */
$srch = new SearchBase('tbl_coupon_mark', 'cm');
$srch->joinTable('tbl_order_deals', 'INNER JOIN', "cm.cm_order_id=od.od_order_id AND od.od_voucher_suffixes LIKE CONCAT('%', cm.cm_counpon_no, '%')", 'od');
if ($_GET['deal_id'] > 0) {
    $srch->addCondition('od.od_deal_id', '=', $_GET['deal_id']);
}
$srch->addCondition('order_payment_status', '=', 0);
$srch->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id = d.deal_id AND d.deal_company=' . $_SESSION['logged_user']['company_id'], 'd');
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
    $Src_frm->fill($post);
}
/** search mode ends * */
$srch->addMultipleFields(array('od.od_order_id', 'od.od_to_name', 'u.user_name', 'u.user_email', 'o.order_date', 'o.order_payment_mode', 'o.order_payment_status', 'cm.cm_counpon_no', 'cm.cm_status', 'cm.cm_id', 'd.deal_id', 'd.voucher_valid_from', 'd.voucher_valid_till'));
if ($_GET['mode'] != 'downloadcsv') {
    $srch->setPageNumber($page);
    $srch->setPageSize($pagesize);
}
$srch->addOrder('o.order_date', 'desc');
$result = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) . ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
	' . getPageString('<li><a href="?deal_id=' . $_REQUEST['deal_id'] . '&page=xxpagexx" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$arr_listing_fields = array(
    'listserial' => t_lang('M_TXT_SR_NO'),
    'user_name' => t_lang('M_TXT_USER_NAME'),
    'order_id' => t_lang('M_TXT_VOUCHER_CODE'),
    'user_email' => t_lang('M_FRM_EMAIL_ADDRESS'),
    'od_qty' => t_lang('M_TXT_QUANTITY'),
    'order_date' => t_lang('M_TXT_ORDRED_DATE'),
    'order_payment_mode' => t_lang('M_TXT_MODE_OF_PAYMENT'),
    'order_payment_status' => t_lang('M_TXT_PAYMENT_STATUS')
);
require_once './header.php';
$arr_bread = array(
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'deals.php' => t_lang('M_TXT_DEALS'),
    '' => t_lang('M_TXT_TIPPED_MEMBERS_LISTING'),
);
?> 
<?php
$deal_specific = '';
if (intval($_GET['deal_id']) > 0)
    $deal_specific = '&deal_id=' . intval($_GET['deal_id']);
?>
<ul class="nav-left-ul">
    <li ><a href="tipped-members.php?mode=downloadcsv&deal_id=<?php echo $_GET['deal_id']; ?>" ><?php echo t_lang('M_TXT_DOWNLOAD_CSV'); ?> </a></li>
    <li><a href="tipped-members.php?mode=downloadpdf&deal_id=<?php echo $_GET['deal_id']; ?>" ><?php echo t_lang('M_TXT_DOWNLOAD_PDF'); ?></a></li>
    <li ><a href="tipped-members.php?status=active<?php echo $deal_specific; ?>" <?php if ($_REQUEST['status'] == 'active') echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_ACTIVE'); ?> </a></li>
    <li ><a href="tipped-members.php?status=used<?php echo $deal_specific; ?>" <?php if ($_REQUEST['status'] == 'used') echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_USED'); ?></a></li>
    <li ><a href="tipped-members.php?status=expired<?php echo $deal_specific; ?>" <?php if ($_REQUEST['status'] == 'expired') echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_EXPIRED'); ?> </a></li>
    <li ><a href="tipped-members.php?<?php echo $deal_specific; ?>"><?php echo t_lang('M_TXT_ALL_VOUCHERS'); ?> </a></li>
    <li ><a href="pending-vouchers.php?deal_id=<?php echo $_GET['deal_id']; ?>" class="selected"><?php echo t_lang('M_TXT_PENDING_VOUCHERS'); ?> </a></li>
</ul>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_PENDING_VOUCHERS_LISTING'); ?></div>       
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
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
    <div class="box searchform_filter"><div class="title"> <?php echo t_lang('M_TXT_PENDING_VOUCHERS_LISTING'); ?> </div><div class="content togglewrap" style="display:none;">		<?php echo $Src_frm->getFormHtml(); ?></div></div>
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
                    case 'order_payment_status':
                        echo '<span class="label label-info">' . t_lang('M_TXT_PENDING') . '</span>';
                        break;
                    case 'order_payment_mode':
                        if ($row[$key] == 1) {
                            echo 'Paypal';
                        } else if ($row[$key] == 2) {
                            echo 'AuthorizeNet';
                        } else if ($row[$key] == 4) {
                            echo 'CIM';
                        } else {
                            echo 'Wallet';
                        }
                        break;
                    case 'order_id':
                        echo $row['od_order_id'] . $row['cm_counpon_no'];
                        break;
                    case 'order_date':
                        echo displayDate($row['order_date'], true);
                        break;
                    case 'od_qty':
                        echo 1;
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
            echo '<tr><td colspan="' . count($arr_listing_fields) . '">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
        }
        ?>
    </table>
    <?php if ($srch->pages() > 1) { ?>
        <div class="footinfo">
            <aside class="grid_1"><?php echo $pagestring; ?></aside>  
            <aside class="grid_2"><span class="info"><?php echo $pageStringContent; ?></span></aside>
        </div>
    <?php } ?>
</td>
<?php
require_once './footer.php';
