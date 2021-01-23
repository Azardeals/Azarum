<?php
require_once './application-top.php';
require_once '../includes/navigation-functions.php';
checkAdminPermission(5);
include './update-deal-status.php';
loadModels(array('VoucherModel'));
$arr_common_js[] = 'js/calendar.js';
$arr_common_js[] = 'js/calendar-en.js';
$arr_common_js[] = 'js/calendar-setup.js';
$arr_common_css[] = 'css/cal-css/calendar-win2k-cold-1.css';
$voucher_deal_id = (int) $_GET['deal_id'];
/**
 * VOUCHER CLASS
 * */
$deal_type = Voucher::getDealTypeArray();
/**
 * VOUCHER CLASS SEARCH FORM
 * */

$srchForm = Voucher::getSearchForm();
$post = getPostedData();
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$pagesize = 30;
/**
 * VOUCHER CLASS LISTING
 * */
$requestStatus = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$voucherObj = new Voucher();
$srch = $voucherObj->getOrdersListing($requestStatus);
if (((int) $_REQUEST['deal_id']) > 0) {
    $srch->addCondition('od.od_deal_id', '=', $_REQUEST['deal_id']);
}
/** SEARCH MODE * */
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
        $cnd->attachCondition('od.od_order_id', '=', $order_id, 'OR');
        $cnd->attachCondition('cm.cm_counpon_no', 'like', '%' . $voucher_no . '%', 'AND');
    }
    if ($post['user_email'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('u.user_email', '=', $post['user_email'], 'OR');
    }
    if ($post['cm_shipping_status'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('cm.cm_shipping_status', '=', $post['cm_shipping_status'], 'OR');
        $cnd->attachCondition('d.deal_type', '=', 1, 'AND');
    }
    if ($post['deal_type'] != '') {
        $type = explode('-', $post['deal_type']);
        $deal_type = $type[0];
        $deal_sub_type = $type[1];
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition("d.`deal_type`", '=', $deal_type, 'OR');
        $cnd->attachCondition("d.`deal_sub_type`", '=', $deal_sub_type, 'AND');
    }
    if ($post['order_start_time'] != '') {
        $start_time = date('Y-m-d', strtotime($post['order_start_time']));
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition("mysql_func_date(o.`order_date`)", '>=', $start_time, 'OR', true);
    }
    if ($post['order_end_time'] != '') {
        $end_time = date('Y-m-d', strtotime($post['order_end_time']));
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition("mysql_func_date(o.`order_date`)", '<=', $end_time, 'OR', true);
    }
    if ($post['order_payment_status'] != '') {
        $cnd = $srch->addDirectCondition('0');
        if ($post['order_payment_status'] == 2) {
            $srch->addHaving("active", '=', 0, 'AND');
            $cnd->attachCondition("order_payment_status", '=', 2, 'OR');
            $cnd->attachCondition("cm_status", '=', 3, 'AND');
        } else {
            $cnd->attachCondition("order_payment_status", '=', $post['order_payment_status'], 'OR');
        }
    }
    $srchForm->fill($post);
}
/** SEARCH MODE ENDS * */
$srch->addMultipleFields(array('od.od_order_id', 'od.od_to_name', 'u.user_name', 'u.user_email', 'o.order_date', 'o.order_payment_mode', 'o.order_payment_status', 'o.order_payment_capture', 'cm.cm_counpon_no', 'cm.cm_status', 'cm.cm_id', 'd.deal_id', 'd.deal_instant_deal', 'd.voucher_valid_from', 'd.voucher_valid_till', 'd.deal_type', 'cm.cm_shipping_status', 'cm.cm_shipping_date', 'cm.cm_shipping_details'));
if ($_GET['mode'] != 'downloadcsv' && $_GET['mode'] != 'downloadpdf') {
    $srch->setPageNumber($page);
    $srch->setPageSize($pagesize);
}
$srch->addOrder('o.order_date', 'desc');
$srch->addGroupBy('cm_id');
$result = $srch->getResultSet();
/**
 * VOUCHER CLASS PAGINATION
 * */
$pagestring = '';
$pages = $srch->pages();
$pagestring .= createHiddenFormFromPost('frmPaging', '?', array('page', 'status'), array('page' => '', 'status' => $_REQUEST['status'], 'deal_id' => $_REQUEST['deal_id']));
$pagestring .= '<div class="pagination"><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$arr_listing_fields = array(
    'listserial' => t_lang('M_TXT_S_N'),
    'user_name' => t_lang('M_TXT_USER_NAME'),
    'voucher_code' => t_lang('M_TXT_VOUCHER_CODE'),
    'order_id' => t_lang('M_TXT_ORDER_ID'),
    'user_email' => t_lang('M_FRM_EMAIL_ADDRESS'),
    'od_qty' => t_lang('M_TXT_QTY'),
    'order_date' => t_lang('M_TXT_ORDRED_DATE'),
    'shipping_details' => t_lang('M_TXT_SHIPPING_DETAILS'),
    'od_to_name' => t_lang('M_TXT_GIFTED_TO_FRIEND'),
    'order_payment_status' => t_lang('M_TXT_PAYMENT_STATUS'),
    'order_shipping_status' => t_lang('M_TXT_SHIPPING_STATUS'),
    'cm_status' => t_lang('M_TXT_VOUCHER_STATUS')
);
/**
 * VOUCHER ACTIONS
 * */
if(!empty($_GET['used'])) {
    $markAsUsedForm = new Form('frmMarkAsUsed');
    $markAsUsedForm->setAction('?deal_id='.$_GET['deal_id'].'&used='.$_GET['used'].'&submit_form=1'.'&tip='.$_GET['tip']);
    $markAsUsedForm->setJsErrorDisplay('afterfield');
    $markAsUsedForm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="tbl_form"');
    $markAsUsedForm->addTextBox(t_lang('M_TXT_Virtual_Code'), 'mark_as_used_code');

    $markAsUsedForm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SUBMIT'), '', 'class="inputbuttons"');
}

if ($_GET['tip'] == "1" && $_GET['used'] != "" && $_GET['submit_form'] == 1) {

    if(empty($_POST['mark_as_used_code'])){
        $msg->addError(t_lang('M_ERROR_INVALID_REQUEST'));
        if (!isset($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER'] == ""){
            redirectUser('tipped-members.php');
        }
        redirectUser($_SERVER['HTTP_REFERER']);
    }

    $markAsUsedCode = $_POST['mark_as_used_code'];

    if(!voucherMarkUsed($_GET['used'], true, '', 1, $markAsUsedCode)){
        redirectUser('tipped-members.php');
    }

    paidReferralCommission($_GET['used'], true);
    paidCharityCommission($_GET['used'], true);
    redirectUser('tipped-members.php?deal_id=' . $_GET['deal_id'] . '&page=' . $_GET['page'] . '&status=' . $_REQUEST['status']);
    exit();
}

if ($_GET['used'] != "" && $_GET['submit_form'] == 1) {

    if(empty($_POST['mark_as_used_code'])){
        $msg->addError(t_lang('M_ERROR_INVALID_REQUEST'));
        if (!isset($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER'] == ""){
            redirectUser('tipped-members.php');
        }
        redirectUser($_SERVER['HTTP_REFERER']);
    }

    $markAsUsedCode = $_POST['mark_as_used_code'];

    if(!voucherMarkUsed($_GET['used'], true, false, false, $markAsUsedCode)){
        redirectUser('tipped-members.php');
    }

    paidReferralCommission($_GET['used'], true);
    paidAffiliateCommission($_GET['used'], true);
    paidCharityCommission($_GET['used'], true);
    redirectUser('tipped-members.php?deal_id=' . $_GET['deal_id'] . '&page=' . $_GET['page'] . '&status=' . $_REQUEST['status']);
    exit();
}

if ($_GET['order'] != "") {
    $order_id = $_GET['order'];
    $rs2 = $db->query("select * from tbl_order_deals where od_order_id='" . $order_id . "'");
    while ($row2 = $db->fetch($rs2)) {
        $totalQuantity += ($row2['od_qty'] + $row2['od_gift_qty']);
        $priceQty = $row2['od_deal_price'];
        $deal_id = $row2['od_deal_id'];
    }
    $srch1 = new SearchBase('tbl_orders', 'o');
    $srch1->joinTable('tbl_users', 'INNER JOIN', "o.order_user_id=u.user_id ", 'u');
    $srch1->joinTable('tbl_order_deals', 'INNER JOIN', "o.order_id=od.od_order_id ", 'od');
    $srch1->joinTable('tbl_deals', 'INNER JOIN', "od.od_deal_id=d.deal_id ", 'd');
    $srch1->joinTable('tbl_order_shipping_details', 'LEFT OUTER JOIN', 'osd_order_id = o.order_id', 'osd');
    $srch1->joinTable('tbl_countries', 'LEFT OUTER JOIN', 'osd.osd_country_id=co.country_id', 'co');
    $srch1->joinTable('tbl_states', 'LEFT OUTER JOIN', 'osd.osd_state_id=state.state_id', 'state');
    $srch1->addCondition('o.order_payment_status', '=', 3);
    $srch1->addCondition('d.deal_instant_deal', '=', 1);
    $srch1->addCondition('o.order_id', '=', $order_id);
    $srch1->addCondition('o.order_payment_mode', '=', 4);
    $srch1->addCondition('o.order_payment_capture', '=', 0);
    $result1 = $srch1->getResultSet();
    $row1 = $db->fetch($result1);
    if ($db->total_records($result1) > 0) {
        ini_set('max_execution_time', '500');
        require_once '../site-classes/order.cls.php';
        require_once '../site-classes/deal-info.cls.php';
        require_once ("../cim-xml/vars.php");
        require_once ("../cim-xml/util.php");
        if (CONF_PAYMENT_PRODUCTION == 0) {
            $payMode = 'testMode';
        } else {
            $payMode = 'liveMode';
        }
        //build xml to post
        $content = "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
                "<createCustomerProfileTransactionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
                MerchantAuthenticationBlock() .
                "<transaction>" .
                "<profileTransCaptureOnly>" .
                "<amount>" . number_format(($row1['od_deal_price'] * $totalQuantity), 2) . "</amount>" . // should include tax, shipping, and everything.
                "<tax>
                    <amount>0.00</amount>
                    <name>WA state sales tax</name>
                    <description>Washington state sales tax</description>
                    </tax>" .
                "<lineItems>" .
                "<itemId>" . $row1['deal_id'] . "</itemId>" .
                "<name>name of item sold</name>" .
                "<description>Description of item sold</description>" .
                "<quantity>" . ($totalQuantity) . "</quantity>" .
                "<unitPrice>" . number_format($priceQty, 2) . "</unitPrice>" .
                "<taxable>false</taxable>" .
                "</lineItems>" .
                "<customerProfileId>" . $row1['user_customer_profile_id'] . "</customerProfileId>" .
                "<customerPaymentProfileId>" . $row1['order_payment_profile_id'] . "</customerPaymentProfileId>" .
                "<order>" .
                "<invoiceNumber>" . $order_id . "</invoiceNumber>" .
                "</order>" .
                "<taxExempt>false</taxExempt>
                    <recurringBilling>false</recurringBilling>
                    <cardCode>000</cardCode>
                    <approvalCode>" . $row1['order_approval_code'] . "</approvalCode>" .
                "</profileTransCaptureOnly>" .
                "</transaction>" .
                "</createCustomerProfileTransactionRequest>";
        $response = send_xml_request($content);
        /* echo '<pre>'.$content.'</pre>'; */
        $parsedresponse = parse_api_response($response);
        if ("Ok" == $parsedresponse->messages->resultCode) {
            if (isset($parsedresponse->directResponse)) {
                $directResponseFields = explode(",", $parsedresponse->directResponse);
                $responseCode = $directResponseFields[0]; // 1 = Approved 2 = Declined 3 = Error
                $responseReasonCode = $directResponseFields[2]; // See http://www.authorize.net/support/AIM_guide.pdf
                $responseReasonText = $directResponseFields[3];
                $approvalCode = $directResponseFields[4]; // Authorization code
                $transId = $directResponseFields[6];
            }
            $arr = [
                'ot_order_id' => $order_id,
                'ot_transaction_id' => $transId,
                'ot_transaction_status' => 1,
                'ot_gateway_response' => var_export($response, true)
            ];
            if (!$db->insert_from_array('tbl_order_transactions', $arr)) {
                $msg->addError(t_lang('M_ERROR_TRANSACTION_NOT_UPDATED') . $transId);
            } else {
                $db->query("UPDATE tbl_orders set order_payment_capture=1 , order_payment_status=1 where order_id='" . $order_id . "'");
                $msg->addMsg(t_lang('M_TXT_INFO_UPDATED'));
            }
        } else {
            $msg->addError($parsedresponse->messages->message->text . ' ');
        }
    } else {
        $msg->addError(t_lang('M_ERROR_INVALID_REQUEST'));
    }
    redirectUser('tipped-members.php?deal_id=' . $_GET['deal_id'] . '&page=' . $_GET['page'] . '&status=' . $_REQUEST['status']);
}
if ($_GET['refund'] != "") {
    $deal_id = $_GET['deal_id'];
    $ref_res = voucherRefund($_GET['refund']);
    /* function is placed in the site function.php and this function also used in the index-ajax.php file for refund from dashboard */
    redirectUser('tipped-members.php?deal_id=' . $_GET['deal_id'] . '&page=' . $_GET['page'] . '&status=' . $_REQUEST['status']);
}
$arr_listing = [
    'user_name' => t_lang('M_TXT_USER_NAME'),
    'order_id' => t_lang('M_TXT_VOUCHER_CODE'),
    'user_email' => t_lang('M_FRM_EMAIL_ADDRESS'),
    'od_qty' => t_lang('M_TXT_QUANTITY'),
    'order_date' => t_lang('M_TXT_ORDRED_DATE'),
    'shipping_details' => t_lang('M_TXT_SHIPPING_DETAILS'),
    'od_to_name' => t_lang('M_TXT_GIFTED_TO_FRIEND'),
    /* 'order_payment_mode'=>'Mode of Payment', */
    'order_payment_status' => t_lang('M_TXT_PAYMENT_STATUS'),
    'order_shipping_status' => t_lang('M_TXT_SHIPPING_STATUS'),
    'cm_status' => t_lang('M_TXT_VOUCHER_STATUS')
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
                case 'od_qty':
                    $arr[] = 1;
                    break;
                case 'order_payment_status':
                    if ($row[$key] == 1 && $row['active'] == 0 && $row['cm_status'] == 3) {
                        $arr[] = t_lang('M_TXT_REFUND_SENT');
                    } else if ($row[$key] == 1) {
                        $arr[] = t_lang('M_TXT_PAID');
                    } else if ($row[$key] == 0) {
                        $arr[] = t_lang('M_TXT_PENDING');
                    } else if ($row[$key] == 3) {
                        $arr[] = t_lang('M_TXT_AUTHORIZED');
                    } else {
                        $arr[] = t_lang('M_TXT_REFUND_SENT');
                    }
                    break;
                case 'order_shipping_status':
                    if ($row['deal_type'] == 1) {
                        switch ($row['cm_shipping_status']) {
                            case '0':
                                $arr[] = t_lang('M_TXT_PENDING');
                                break;
                            case '1':
                                $arr[] = t_lang('M_TXT_SHIPPED');
                                break;
                            case '2':
                                $arr[] = t_lang('M_TXT_DELIVERED');
                                break;
                        }
                    } else {
                        $arr[] = ' ';
                    }
                    break;
                case 'cm_status':
                    if ($row['used'] == 1) {
                        $arr[] = t_lang('M_TXT_USED');
                    }
                    if ($row['expired'] == 1 and $row['active'] == 0) {
                        $db->query("update tbl_coupon_mark set cm_status = 2 where cm_id=" . intval($row['cm_id']));
                        $arr[] = t_lang('M_TXT_EXPIRED');
                    }
                    if ($row['active'] == 1) {
                        $arr[] = t_lang('M_TXT_UNUSED');
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
if ($_GET['mode'] == 'downloadpdf') {
    include '../download-vouchers-pdf.php';
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false);
    header("Content-type: application/pdf");
    header("Content-Disposition: attachment; filename=voucher-list.pdf");
    header("Cache-control: private");
    header("Content-Transfer-Encoding: binary");
    exit;
}
require_once './header.php';
$arr_bread = array(
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'deals.php' => t_lang('M_TXT_DEALS') . '/' . t_lang('M_TXT_PRODUCTS'),
    '' => t_lang('M_TXT_TIPPED_MEMBERS_LISTING'),
);
if(!empty($_GET['used'])) {
    $arr_bread = array(
        'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
        'deals.php' => t_lang('M_TXT_DEALS') . '/' . t_lang('M_TXT_PRODUCTS'),
        'tipped-members.php' => t_lang('M_TXT_TIPPED_MEMBERS_LISTING'),
        '' => t_lang('M_TXT_MARK_USED'),
    );
}
?>
<script type = "text/javascript">
    var txtsuretorefund = "<?php echo addslashes(t_lang('M_TXT_ARE_YOU_SURE_TO_REFUND_BACK')); ?>";
</script>
<?php
$deal_specific = '';
if (intval($_GET['deal_id']) > 0) {
    $deal_specific = '&deal_id=' . intval($_GET['deal_id']);
}
?>
<ul class="nav-left-ul">
    <li ><a href="tipped-members.php?mode=downloadcsv&deal_id=<?php echo $_GET['deal_id']; ?>" target="_new"><?php echo t_lang('M_TXT_DOWNLOAD_CSV'); ?> </a></li>
    <li><a href="tipped-members.php?mode=downloadpdf&deal_id=<?php echo $_GET['deal_id']; ?>"  target="_new"><?php echo t_lang('M_TXT_DOWNLOAD_PDF'); ?></a></li>
    <li ><a href="tipped-members.php?status=active<?php echo $deal_specific; ?>" <?php if ($_REQUEST['status'] == 'active') echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_ACTIVE'); ?> </a></li>
    <li ><a href="tipped-members.php?status=used<?php echo $deal_specific; ?>" <?php if ($_REQUEST['status'] == 'used') echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_USED'); ?></a></li>
    <li ><a href="tipped-members.php?status=expired<?php echo $deal_specific; ?>" <?php if ($_REQUEST['status'] == 'expired') echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_EXPIRED'); ?> </a></li>
    <li ><a href="tipped-members.php?status=refunded<?php echo $deal_specific; ?>" <?php if ($_REQUEST['status'] == 'refunded') echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_REFUNDED'); ?> </a></li>
    <li ><a href="tipped-members.php?status=cancelled<?php echo $deal_specific; ?>" <?php if ($_REQUEST['status'] == 'cancelled') echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_CANCELLED'); ?> </a></li>
    <li ><a href="tipped-members.php" <?php if (!isset($_REQUEST['status'])) echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_ALL_VOUCHERS'); ?> </a></li>
    <li ><a href="pending-vouchers.php?deal_id=<?php echo $_GET['deal_id']; ?>"><?php echo t_lang('M_TXT_PENDING_VOUCHERS'); ?> </a></li>
</ul>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <?php if(!empty($_GET['used'])) { ?>
            <div class="page-name"><?php echo t_lang('M_TXT_MARK_USED'); ?> </div>
        <?php } else { ?>
            <div class="page-name"><?php echo t_lang('M_TXT_TIPPED_MEMBERS_LISTING'); ?> </div>
        <?php } ?>
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?>
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide();
                    return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="redtext"><?php echo $msg->display(); ?> </div><br/><br/>
                <?php } if (isset($_SESSION['msgs'][0])) { ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>

    <?php if(!empty($_GET['used'])) { ?>
        <div class="box">
            <div class="title">
                <?php echo t_lang('M_TXT_MARK_USED'); ?>
            </div>
            <div class="content">
                <?php echo $markAsUsedForm->getFormHtml(); ?>
            </div>
        </div>
    <?php } else { ?>
        <div class="box searchform_filter"><div class="title"> <?php echo t_lang('M_TXT_TIPPED_MEMBERS_LISTING'); ?> </div><div class="content togglewrap" style="display:none;">		<?php echo $srchForm->getFormHtml(); ?></div></div>
        <table class="tbl_data" width="100%">
            <thead>
                <tr>
                    <th colspan="12"><span style="color:#ff0000;"><?php echo t_lang('M_TXT_UNDER_VOUCHER_STATUS_MARK_USED'); ?></span>  </th>
                </tr>
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
                $row['shipping_details'] = htmlentities($row['shipping_details'], ENT_QUOTES, 'UTF-8');
                echo '<tr>';
                $row['od_to_name'] = htmlentities($row['od_to_name'], ENT_QUOTES, 'UTF-8');
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td>';
                    switch ($key) {
                        case 'listserial':
                            echo $listserial;
                            break;
                        case 'od_to_name':
                            echo (($row['od_to_name']) ? htmlentities($row['od_to_name'], ENT_QUOTES, 'UTF-8') : t_lang('M_TXT_SHORT_NOT_AVAILABLE'));
                            break;
                        case 'order_payment_status':
                            if ($row['used'] == 1) {
                                if ($row[$key] == 1) {
                                    echo '<span class="label label-success">' . t_lang('M_TXT_PAID') . '</span>';
                                } else if ($row[$key] == 0) {
                                    echo '<span class="label label-info">' . t_lang('M_TXT_PENDING') . '</span>';
                                } else if ($row[$key] == 3) {
                                    echo '<span class="label label-success">' . t_lang('M_TXT_AUTHORIZED') . '</span>';
                                } else {
                                    echo '<span class="label label-success">' . t_lang('M_TXT_REFUND_SENT') . '</span>';
                                }
                            } elseif ($row[$key] == 1) {
                                if ($row['active'] == 1) {
                                    if ($row['deal_type'] == 1 && $row['deal_sub_type'] == 1) {
                                        echo '<a href="javascript:void(0)"  class="btn green">' . t_lang('M_TXT_NOT_REFUNDABLE') . '</a> ';
                                        continue 2;
                                    }
                                    echo '<a href="javascript:void(0)" title="' . t_lang('M_TXT_REFUND') . '" onclick="return checkRefundAbility(\'' . $row['od_order_id'] . $row['cm_counpon_no'] . '\',' . intval($_GET['page']) . ',' . intval($_GET['deal_id']) . ');" class="btn green">' . t_lang('M_TXT_REFUND') . '</a> ';
                                } else if ($row['active'] == 0 && $row['cm_status'] == 3) {
                                    echo '<span class="label label-success">' . t_lang('M_TXT_REFUND_SENT') . '</span>';
                                } else {
                                    echo '<span class="label label-success">' . t_lang('M_TXT_PAID') . '</span>';
                                }
                            } else if ($row[$key] == 0) {
                                echo '<span class="label label-info">' . t_lang('M_TXT_PENDING') . '</span>';
                            } else if ($row[$key] == (-1)) {
                                echo '<span class="label label-danger">' . t_lang('M_TXT_CANCELLED') . '</span>';
                            } else if ($row[$key] == 3) {
                                echo '<span class="label label-success">' . t_lang('M_TXT_AUTHORIZED') . '</span>';
                            } else {
                                echo '<span class="label label-success">' . t_lang('M_TXT_REFUND_SENT') . '</span>';
                                $db->query("update tbl_coupon_mark set cm_status = 3 where cm_id=" . intval($row['cm_id']));
                            }
                            break;
                        case 'order_shipping_status':
                            if (($row['deal_type'] == 1 && $row['deal_sub_type'] == 0) && $row['order_payment_status'] != -1) {
                                switch ($row['cm_shipping_status']) {
                                    case '0':
                                        echo '<span class="label label-info ship_status_' . $row['od_order_id'] . $row['cm_counpon_no'] . ' ">' . t_lang('M_TXT_PENDING') . '</span>';
                                        break;
                                    case '1':
                                        echo '<span class="label label-primary ship_status_' . $row['od_order_id'] . $row['cm_counpon_no'] . ' ">' . t_lang('M_TXT_SHIPPED') . '</span>';
                                        break;
                                    case '2':
                                        echo '<span class="label label-success ship_status_' . $row['od_order_id'] . $row['cm_counpon_no'] . '">' . t_lang('M_TXT_DELIVERED') . '</span>';
                                        break;
                                }
                                if ($row['cm_status'] != 3 && $row['cm_shipping_status'] != 2) {
                                    if (checkAdminAddEditDeletePermission(5, '', 'edit')) {
                                        echo '<br/><br/><ul class="actions"><li><a href="javascript:void(0);" onclick="checkShippingDetails(\'' . $row['od_order_id'] . $row['cm_counpon_no'] . '\');" title="' . t_lang('M_TXT_VIEW_DETAILS') . '"><i class="ion-document-text icon"></i></a></li></ul>';
                                    }
                                }
                            } else {
                                echo t_lang('M_TXT_SHORT_NOT_AVAILABLE');
                            }
                            break;
                        case 'voucher_code':
                            echo $row['od_order_id'] . $row['cm_counpon_no'];
                            break;
                        case 'order_id':
                            echo $row['od_order_id'];
                            break;
                        case 'order_date':
                            echo displayDate($row['order_date'], true);
                            break;
                        case 'shipping_details':
                            if ($row['deal_type'] == 1 && $row['deal_sub_type'] == 0) {
                                if ($row[$key] != '') {
                                    echo '<ul class="actions center">
                                            <li>
                                                <a class="tooltip info" href="javascript:void(0)" title="' . t_lang('M_TXT_VIEW_SHIPPING_ADDRESS') . '">
                                                    <i class="ion-location icon"></i>
                                                    <span class="hovertxt">' . nl2br($row[$key]) . '</span>
                                                </a>
                                            </li>
                                        </ul>';
                                } else {
                                    echo t_lang('M_TXT_SHORT_NOT_AVAILABLE');
                                }
                            } else {
                                echo t_lang('M_TXT_SHORT_NOT_AVAILABLE');
                            }
                            break;
                        case 'cm_status':
                            if ($row['order_payment_status'] != -1 && $row['order_payment_status'] != 0) {
                                echo '<ul class="actions">';
                                /* We need not to display the "Mark Used" option for Products. */
                                if ($row['active'] == 1 && $row['deal_type'] != 1) {
                                    if ($row['canUse'] == 1 && $row['is_tipped']) {
                                        $title = '';
                                        if ($row['deal_instant_deal'] == 1 && $row['order_payment_mode'] == 4 && $row['order_payment_capture'] == 0) {
                                            $title = ' * ';
                                        }
                                        if (checkAdminAddEditDeletePermission(5, '', 'edit')) {
                                            echo '<li><a href="?deal_id=' . $row['deal_id'] . '&used=' . $row['cm_id'] . '&page=' . $_GET['page'] . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_MARK_USED') . $title . '" onclick=" requestPopup(this,\'' . t_lang('M_MSG_COUPON_CHANGE_STATUS_TO_USE') . '\',1);"><i class="ion-android-checkbox-outline icon"></i>';
                                            echo '</a></li>';
                                        }
                                    } else if (!$row['is_tipped'] && $row['deal_instant_deal'] == 1 && $row['order_payment_mode'] == 4 && $row['order_payment_capture'] == 0 && $row['order_payment_status'] == 3) {
                                        if ($row['deal_tip'] == 1) {
                                            if (checkAdminAddEditDeletePermission(5, '', 'edit')) {
                                                echo '<li><a href="?deal_id=' . $_GET['deal_id'] . '&used=' . $row['cm_id'] . '&tip=1&page=' . $_GET['page'] . '&status=' . $_REQUEST['status'] . '" title="*' . t_lang('M_TXT_MARK_USED') . '" onclick="requestPopup(this,\'' . t_lang('M_MSG_COUPON_CHANGE_STATUS_TO_USE') . '\',1);"><i class="ion-android-checkbox-outline icon"></i></a></li>';
                                            }
                                        } elseif ($row['deal_tip'] > 1) {
                                            $srch_deals = new SearchBase('tbl_order_deals', 'od');
                                            $srch_deals->addCondition('od.od_deal_id', '=', $row['deal_id']);
                                            $rs = $srch_deals->getResultSet();
                                            $count = $db->total_records($rs);
                                            if ($count < $row['deal_tip']) {
                                                echo '<li><a href="javascript:void(0);" onclick="requestPopup(this,\'' . t_lang('M_TXT_DEAL_IS_NOT_TIPPED_YET') . '\',0)" title="*' . t_lang('M_TXT_MARK_USED') . '"><i class="ion-alert icon"></i></a></li>';
                                            } else {
                                                if (checkAdminAddEditDeletePermission(5, '', 'edit')) {
                                                    echo '<li> <a   href="?deal_id=' . $_GET['deal_id'] . '&used=' . $row['cm_id'] . '&tip=1&page=' . $_GET['page'] . '&status=' . $_REQUEST['status'] . '" title="*' . t_lang('M_TXT_MARK_USED') . '" onclick="requestPopup(this,\' ' . t_lang('M_MSG_COUPON_CHANGE_STATUS_TO_USE') . '\',1);"><i class="ion-android-checkbox-outline icon"></i></a></li>';
                                                }
                                            }
                                        }
                                    } else {
                                        if (!$row['is_tipped']) {
                                            $messageAlert = t_lang('M_TXT_DEAL_IS_NOT_TIPPED_YET');
                                        } else {
                                            $messageDate = t_lang('M_MSG_VOUCHER_IS_ACTIVE_BUT_CANNOT_USE');
                                            $messageAlert = sprintf($messageDate, $row['voucher_valid_from']);
                                        }
                                        $title = '';
                                        if ($row['deal_instant_deal'] == 1 && $row['order_payment_mode'] == 4 && $row['order_payment_capture'] == 0) {
                                            $title = ' * ';
                                        }
                                        echo '<li><a href="javascript:void(0);" onclick="requestPopup(this,\'' . $messageAlert . '\',0)" title="' . t_lang('M_TXT_MARK_USED') . $title . '"><i class="ion-alert icon"></i>';
                                        echo'</a></li>';
                                    }
                                } else if (($row['active'] == 1 && $row['deal_type'] == 1 && $row['cm_shipping_status'] == 2) || ($row['active'] == 1 && $row['deal_type'] == 1 && $row['deal_sub_type'] == 1)) {
                                    //handle product commissions
                                    if (checkAdminAddEditDeletePermission(5, '', 'edit')) {
                                        echo '
                                                <ul class="actions">
                                                    <li>
                                                        <a href="?deal_id=' . $row['deal_id'] . '&used=' . $row['cm_id'] . '&page=' . $_GET['page'] . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_SETTLE_COMMISSION') . $title . '" onclick="requestPopup(this,\'' . t_lang('M_MSG_PRODUCT_SETTLE_COMMISSION') . '\',1);"><i class="ion-android-checkbox-outline icon"></i></a>
                                                    </li>
                                                </ul>';
                                    }
                                }
                                if ($row['used'] == 1) {
                                    echo '<span class="label label-success">' . t_lang('M_TXT_USED') . '</span> ';
                                    $title = '';
                                    if ($row['deal_instant_deal'] == 1 && $row['order_payment_mode'] == 4 && $row['order_payment_capture'] == 1) {
                                        $title = ' * ';
                                    }
                                    if ($row['deal_instant_deal'] == 1 && $row['order_payment_mode'] == 4 && $row['order_payment_capture'] == 0) {
                                        echo '<li><a  title="' . t_lang('M_TXT_PAYMENT_WILL_BE_CAPTURE_FOR_COMPLETE_ORDER') . $title . '" href="?deal_id=' . $_GET['deal_id'] . '&page=' . $_GET['page'] . '&order=' . $row['od_order_id'] . '"><i class="ion-alert-circled icon"></i></a></li>';
                                    }
                                }
                                if ($row['expired'] == 1 and $row['active'] == 0) {
                                    $db->query("update tbl_coupon_mark set cm_status = 2 where cm_id=" . intval($row['cm_id']));
                                    echo '<span class="label label-success">' . t_lang('M_TXT_EXPIRED') . '</span>';
                                }
                                if ($row['deal_type'] == 1 && $row['deal_sub_type'] == 1 && ($row['dpe_product_external_url'] == "" && $row['dpe_product_file_name'] == "")) {
                                    if ($row['used'] != 1) {
                                        $onclick = 'digitalProductSendLink("' . $row['user_email'] . '")';
                                        echo "<li><a onclick='" . $onclick . "' href='javascript:void(0);' title='" . t_lang('M_TXT_SEND_LINK') . "'><i class='ion-android-send icon'></i></a></li>";
                                    }
                                } else {
                                    echo '<li><a href="voucher-detail.php?id=' . $row['od_order_id'] . $row['cm_counpon_no'] . '" target="_blank" title="' . t_lang('M_TXT_VOUCHER_DETAIL') . '"><i class="ion-eye icon"></i></a></li>';
                                }
                                echo '</ul>';
                            } else {
                                echo t_lang('M_TXT_SHORT_NOT_AVAILABLE');
                            }
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
                <aside class="grid_1">
                    <?php echo $pagestring; ?>
                </aside>
                <aside class="grid_2"><span class="info"><?php echo $pageStringContent; ?></span></aside>
            </div>
        <?php } ?>
    <?php } ?>
</td>
<?php require_once('./footer.php'); ?>
