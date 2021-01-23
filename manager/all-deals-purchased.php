<?php
/* * ***
  Instruction :
  While making any change in this section, please make sure that changes are also made in "index-ajax.php(dealPurchased case)" file
 * * */
require_once './application-top.php';
checkAdminPermission(5);
require_once '../includes/navigation-functions.php';
$post = getPostedData();
$str = '';
$comDate = date("Y-m-d H:m:s", mktime(23, 0, 0, date('m'), date('d') - 1, date('Y')));
//Search Form
$rsc = $db->query("SELECT company_id, company_name  FROM `tbl_companies` WHERE company_active=1 and company_deleted = 0");
$companyArray = [];
while ($arrs = $db->fetch($rsc)) {
    $companyArray[$arrs['company_id']] = htmlentities($arrs['company_name'], ENT_QUOTES, 'UTF-8');
}
$dealrsc = $db->query("SELECT  deal_id, deal_name  FROM `tbl_deals` WHERE  deal_deleted = 0");
$dealArray = [];
while ($arrs = $db->fetch($dealrsc)) {
    $dealArray[htmlentities($arrs['deal_name'], ENT_QUOTES, 'UTF-8')] = $arrs['deal_name'];
}
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
$Src_frm = new Form('Src_frm', 'Src_frm');
$Src_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$Src_frm->setFieldsPerRow(3);
$Src_frm->captionInSameCell(true);
$Src_frm->addSelectBox(t_lang('M_FRM_COMPANY_NAME'), 'company_name', $companyArray, $value, '', 'Select', 'company_name');
$Src_frm->addSelectBox(t_lang('M_TXT_DEAL_NAME'), 'deal_name', $dealArray, $value, '', 'Select', 'deal_name');
$Src_frm->addTextBox(t_lang('M_TXT_USER_NAME'), 'user_name', '', '', '');
$Src_frm->addTextBox(t_lang('M_TXT_VOUCHER_CODE'), 'order_id', '', '', '');
$Src_frm->addTextBox(t_lang('M_FRM_EMAIL_ADDRESS'), 'user_email', '', '', '');
$Src_frm->addHiddenField('', 'mode1', 'search');
$fld1 = $Src_frm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="inputbuttons" onclick=location.href="all-deals-purchased.php"');
$fld = $Src_frm->addSubmitButton('', 'btn_search', t_lang('M_TXT_SEARCH'), '', '')->attachField($fld1);
$srch = new SearchBase('tbl_order_deals', 'od');
//paging
$srch->setPageSize(15);
$page = ($post['page'] > 1) ? $post['page'] : 1;
$srch->setPageNumber($page);
$srch->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id=o.order_id', 'o');
$srch->addCondition('order_payment_status', '>', 0);
$srch->joinTable('tbl_deals', 'INNER JOIN', 'd.deal_id=od.od_deal_id', 'd');
$srch->joinTable('tbl_companies', 'INNER JOIN', 'c.company_id=d.deal_company', 'c');
$srch->joinTable('tbl_coupon_mark', 'LEFT JOIN', 'cm.cm_order_id=od.od_order_id AND cm_deal_id = od_deal_id', 'cm');
$srch->joinTable('tbl_users', 'INNER JOIN', 'o.order_user_id=u.user_id', 'u');
if ($post['mode1'] == 'search' AND!isset($post['btn_cancel'])) {
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
    if ($post['company_name'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('d.deal_company', '=', $post['company_name'], 'OR');
    }
    if ($post['deal_name'] != '') {
        $deal = addslashes(html_entity_decode($post['deal_name'], ENT_QUOTES, 'UTF-8'));
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('d.deal_name' . $_SESSION['lang_fld_prefix'], 'like', $deal, 'OR');
    }
    if ($post['user_name'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('u.user_name', '=', $post['user_name'], 'OR');
    }
    $Src_frm->fill($post);
}
$srch->addCondition('od.od_voucher_suffixes', '!=', '');
$srch->addOrder('o.order_date', 'desc');
$srch->addMultipleFields(['cm.cm_id, cm_counpon_no as od_voucher_suffixes,company_name,deal_name' . $_SESSION["lang_fld_prefix"] . ',company_name' . $_SESSION["lang_fld_prefix"] . ',user_name,o.order_id,user_email,od_qty,o.order_date,od_to_name,order_payment_status,od_gift_qty']);
$rs_listing = $srch->getResultSet();
//paging
$pagestring = '';
$pagesize = 15;
$pagestring .= createHiddenFormFromPost('frmPaging', '?', ['page'], ['page' => '']);
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) . ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> ', $srch->pages(), $page, '<li class="selected"><a href="javascript:void(0);"  class="active">xxpagexx</a></li>', '<li>...</li>');
$pagestring .= '</div><div class="clear"></div>';
//paging
$arr_listing_fields = [
    'company_name' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_COMPNAY_NAME'),
    'deal_name' . $_SESSION['lang_fld_prefix'] => t_lang('M_TXT_DEAL') . '/' . t_lang('M_TXT_PRODUCT') . ' ' . t_lang('M_TXT_NAME'),
    'user_name' => t_lang('M_TXT_USER_NAME'),
    'order_id' => t_lang('M_TXT_VOUCHER_CODE'),
    'user_email' => t_lang('M_FRM_EMAIL_ADDRESS'),
    'od_qty' => t_lang('M_TXT_QUANTITY'),
    'order_date' => t_lang('M_TXT_ORDRED_DATE'),
    'od_to_name' => t_lang('M_TXT_GIFTED_TO_FRIEND'),
    'order_payment_status' => t_lang('M_TXT_PAYMENT_STATUS')
];
$str .= '<table cellspacing="0" cellpadding="0" border="0" width="100%" class="tbl_data table table-striped">
<tbody><tr>';
foreach ($arr_listing_fields as $key => $val) {
    $str .= '<th ' . (( $key == 'deal_name') ? ' width="20%" ' : 'width="10%"') . '>' . $val . '</th>';
}
for ($listserial = ($page - 1) * $pagesize + 1; $row = $db->fetch($rs_listing); $listserial++) {
    $row['od_to_name'] = htmlentities($row['od_to_name'], ENT_QUOTES, 'UTF-8');
    $row['deal_name'] = htmlentities($row['deal_name'], ENT_QUOTES, 'UTF-8');
    if (($row['od_qty'] + $row['od_gift_qty']) > 0) {
        $od_voucher_suffixes = explode(', ', $row['od_voucher_suffixes']);
        foreach ($od_voucher_suffixes as $voucher) {
            $str .= '<tr' . (($row[$colPrefix . 'active'] == '0') ? ' class="inactive"' : '') . '  id="' . $row['order_id'] . $voucher . '" >';
            foreach ($arr_listing_fields as $key => $val) {
                $order_id = $row['order_id'];
                $str .= '<td>';
                switch ($key) {
                    case 'order_payment_status':
                        if ($row[$key] == 1) {
                            $str .= '<span class="label label-success">' . t_lang('M_TXT_PAID') . '</span>';
                        } else if ($row[$key] == 0) {
                            $str .= '<span class="label label-info">' . t_lang('M_TXT_PENDING') . '</span>';
                        } else {
                            $str .= '<span class="label label-success">' . t_lang('M_TXT_REFUND') . '</span>';
                        }
                        break;
                    case 'order_id':
                        if (($row['od_qty']) > 0) {
                            $str .= $row['order_id'] . $voucher;
                        } else if (($row['od_gift_qty']) > 0) {
                            $str .= $row['order_id'] . $voucher;
                        } else {
                            $str .= $row['order_id'] . $voucher;
                        }
                        break;
                    case 'od_qty':
                        $str .= '1';
                        break;
                    case 'action':
                        $srch1 = new SearchBase('tbl_coupon_mark', 'cm');
                        $srch1->joinTable('tbl_order_deals', 'INNER JOIN', "cm.cm_order_id='" . $row['order_id'] . "' AND od.od_voucher_suffixes LIKE CONCAT('%', " . $voucher . ", '%')", 'od');
                        $srch1->addCondition('cm.cm_status', '=', 1);
                        $rs_listing1 = $srch1->getResultSet();
                        if ($db->total_records($rs_listing1) == 0) {
                            if ($row['order_payment_status'] == 1) {
                                $order = "'" . $row['order_id'] . $voucher . "'";
                                $order_id = "'" . $row['order_id'] . "'";
                                if (checkAdminAddEditDeletePermission(5, '', 'edit')) {
                                    $str .= '&nbsp;<a href="javascript:void(0);" title="' . t_lang('M_TXT_REFUND') . '" onclick="return checkRefundAbility(' . $order . ');" class="btn delete">' . t_lang('M_TXT_REFUND') . '</a> ';
                                }
                            }
                        }
                        break;
                    default:
                        $str .= $row[$key];
                        break;
                }
                $str .= '</td>';
            }
            $str .= '</tr>';
        }
    }
}
if ($db->total_records($rs_listing) == 0) {
    $str .= '<tr><td colspan="' . count($arr_listing_fields) . '">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
}
$str .= ' </tbody></table>';
if ($srch->pages() > 1) {
    $str .= '<div class="footinfo">';
    $str .= '<aside class="grid_1">';
    $str .= $pagestring;
    $str .= '</aside>';
    $str .= '<aside class="grid_2"><span class="info">' . $pageStringContent . '</span></aside>';
}
$str .= '</div>';
require_once './header.php';
?>
<ul id="content" class="nav-left-ul">
    <li> <a href="all-deals-purchased.php" class="selected"><?php echo t_lang('M_TXT_ALL_DEAL_PURCHASED'); ?></a> </li>
</ul>
</div>
</td>
<td class="right-portion">
    <?php
    $arr_bread = [
        'index.php' => '<img alt="Home" src="images/home-icon.png">',
        'javascript:void(0)' => t_lang('M_TXT_REPORTS'),
        '' => t_lang('M_TXT_ALL_DEAL_PURCHASED'),
    ];
    echo getAdminBreadCrumb($arr_bread);
    ?>
    <?php echo $msg->display(); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_ALL_DEAL_PURCHASED'); ?> </div>
    </div>
    <div class="clear"></div>	
    <div class="box searchform_filter">
        <div class="title"><?php echo t_lang('M_TXT_ALL_DEAL_PURCHASED'); ?> </div>
        <div class="content togglewrap" style="display:none;"><?php echo $Src_frm->getFormHtml(); ?></div>
    </div>
    <?php echo $str; ?>
</td>
<?php require_once './footer.php'; ?> 
