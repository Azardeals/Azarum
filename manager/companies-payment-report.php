<?php
require_once './application-top.php';
checkAdminPermission(3);
require_once '../includes/navigation-functions.php';
require_once '../includes/deal_functions.php';
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$status = ($_REQUEST['status']) ? $_REQUEST['status'] : 'active';
$pagesize = 15;
$mainTableName = 'tbl_companies';
$primaryKey = 'company_id';
$colPrefix = 'company_';
$post = getPostedData();
//Search Form
$Src_frm = new Form('Src_frm', 'Src_frm');
$Src_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$Src_frm->setFieldsPerRow(2);
$Src_frm->captionInSameCell(true);
$Src_frm->addTextBox(t_lang('M_FRM_KEYWORDS'), 'keyword', '', '', '');
$Src_frm->addHiddenField('', 'mode', 'search');
$Src_frm->addHiddenField('', 'status', $_REQUEST['status']);
$fld1 = $Src_frm->addButton('&nbsp;', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="inputbuttons" onclick="location.href=\'companies-payment-report.php\'"');
$fld = $Src_frm->addSubmitButton('&nbsp;', 'btn_search', t_lang('M_TXT_SEARCH'), '', ' class="inputbuttons"')->attachField($fld1);
$srch = new SearchBase('tbl_companies', 'c');
if ($_REQUEST['rep'] > 0) {
    $srch->addCondition('company_rep_id', '=', $_REQUEST['rep']);
}
if ($_REQUEST['status'] == 'inactive') {
    $srch->addCondition('company_active', '=', 0);
    $srch->addCondition('company_deleted', '=', 0);
} else if ($_REQUEST['status'] == 'deleted') {
    $srch->addCondition('company_deleted', '=', 1);
} else if ($_REQUEST['status'] == 'active') {
    $srch->addCondition('company_active', '=', 1);
    $srch->addCondition('company_deleted', '=', 0);
} else {
    $srch->addCondition('company_deleted', '=', 0);
}
$srch->joinTable('tbl_countries', 'INNER JOIN', 'c.company_country=country.country_id', 'country');
$srch->joinTable('tbl_states', 'LEFT JOIN', 'st.state_id=c.company_state', 'st');
$srch->joinTable('tbl_deals', 'LEFT OUTER JOIN', 'd.deal_company=c.company_id AND deal_paid=0 AND deal_status=2 AND deal_deleted=0', 'd');
$srch->joinTable('tbl_representative', 'LEFT OUTER JOIN', 'r.rep_id=c.company_rep_id AND r.rep_id > 0', 'r');
$srch->joinTable('tbl_company_addresses', 'LEFT OUTER JOIN', 'ca.company_id=c.company_id', 'ca');
$srch->joinTable('(SELECT `cwh_company_id`, SUM(`cwh_amount`) as totalPaid FROM `tbl_company_wallet_history` WHERE 1 GROUP BY `cwh_company_id`)', 'LEFT OUTER JOIN', 'cwh.cwh_company_id=c.company_id', 'cwh');
$srch->addFld('COUNT(DISTINCT d.deal_id) AS total_unsetteled_deals');/** expired unsettled deals * */
$srch->addMultipleFields(['c.company_id', 'c.company_name' . $_SESSION['lang_fld_prefix'] . ' as company_name', 'c.company_email', 'c.company_active', 'c.company_rep_id', 'country.country_name', 'r.rep_fname', 'r.rep_lname', 'COUNT(DISTINCT ca.company_address_id) as total_company_address', 'totalPaid', 'st.*']);
if ($_SESSION['lang_fld_prefix'] == '_lang1') {
    $srch->addFld("CONCAT(company_address1_lang1, '<br/>', company_address2_lang1, '<br/>', company_address3_lang1, ' ', company_city_lang1, ' ', state_name_lang1, '-', company_zip, ' ', country_name_lang1) AS address");
} else {
    $srch->addFld("CONCAT(company_address1, '<br/>', company_address2, '<br/>', company_address3, ' ', company_city, ' ', state_name, '-', company_zip, ' ', country_name) AS address");
}
$srch->addOrder('company_name' . $_SESSION['lang_fld_prefix']);
if ($post['mode'] == 'search') {
    if ($post['keyword'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('c.company_email', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('c.company_name' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('c.company_address1' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('c.company_address2' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('c.company_address3' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
    }
    $Src_frm->fill($post);
}
$srch->addGroupBy('company_id');
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pagesize = 15;
$pagestring .= createHiddenFormFromPost('frmPaging', '?', ['page'], ['page' => $page, 'status' => $_REQUEST['status']]);
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
    ' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</ul></div>';
$arr_listing_fields = [
    'company_name' => '',
    'total_sales' => '',
    'unsettled_deals' => '',
    'settled_deals' => '',
    'credits' => '',
    'payable_amount' => '',
    'payout' => ''
];
require_once './header.php';
?>
<ul id="content" class="nav-left-ul">
    <li> <a href="companies-payment-report.php" class="selected"><?php echo t_lang('M_TXT_PAYMENT_REPORT'); ?></a> </li>
</ul>
</div>
</td>
<td class="right-portion">
    <?php
    $arr_bread = [
        'index.php' => '<img alt="Home" src="images/home-icon.png">',
        'javascript:void(0)' => t_lang('M_TXT_REPORTS'),
        '' => t_lang('M_TXT_COMPANIES_PAYMENT_REPORT'),
    ];
    echo getAdminBreadCrumb($arr_bread);
    ?>
    <?php echo $msg->display(); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_PAYMENT_REPORT'); ?> </div>
    </div>
    <div class="clear"></div>	
    <div class="box searchform_filter">
        <div class="title"><?php echo t_lang('M_TXT_PAYMENT_REPORT'); ?> </div>
        <div class="content togglewrap" style="display:none;"><?php echo $Src_frm->getFormHtml(); ?></div>
    </div>
    <table width="100%" class="tbl_data">        
        <tr>
            <th width="20%"><?php echo t_lang('M_TXT_COMPANY_NAME'); ?></th>
            <th width="10%"><?php echo t_lang('M_TXT_TOTAL_SALES'); ?></th>
            <th width="15%"><?php echo t_lang('M_TXT_UNSETTLED_DEALS_AMOUNT'); ?></th>
            <th width="15%"><?php echo t_lang('M_TXT_SETTLED_DEALS_AMOUNT'); ?></th>
            <th width="10%"><?php echo t_lang('M_TXT_CREDITS_BY_ADMIN'); ?></th>
            <th width="15%"><?php echo t_lang('M_TXT_TOTAL_PAYABLE_AMOUNT_OR_BALANCE'); ?></th>
            <th width="15%"><?php echo t_lang('M_TXT_TOTAL_AMOUNT_PAID_BY_ADMIN'); ?></th>
        </tr>
        <?php
        for ($listserial = ($page - 1) * $pagesize + 1; $row = $db->fetch($rs_listing); $listserial++) {
            /** SALES OF unsettled deals * */
            $totalUnsettledPrice = 0;
            $totalSettledPrice = 0;
            $arrs = getSettledUnsettledDealData($row['company_id']);
            $totalUnsettledPrice = $arrs['totalUnsettledPrice'];
            $totalSettledPrice = $arrs['totalSettledPrice'];
            $totalDebits = getTotalDebitsAmountForMerchant($row['company_id']);
            $totalcredits = getTotalCreditsAmountForMerchant($row['company_id']);
            $payable_amount = (($totalSettledPrice + $totalcredits) - $totalDebits);
            /** SALES OF  settled deals * */
            /*             * ****** */
            if ($listserial % 2 != 0) {
                $even = 'even';
            } else {
                $even = '';
            }
            echo '<tr class=" ' . $even . ' ">';
            $i = 0;
            foreach ($arr_listing_fields as $key => $val) {
                $td_even = '';
                if ($i % 2 == 0) {
                    $td_even = 'center';
                }
                echo '<td class=" ' . $td_even . ' ">';
                switch ($key) {
                    case 'company_name':
                        echo htmlentities($row['company_name']);
                        break;
                    case 'unsettled_deals':
                        echo CONF_CURRENCY . number_format($totalUnsettledPrice, 2) . CONF_CURRENCY_RIGHT;
                        break;
                    case 'settled_deals':
                        echo CONF_CURRENCY . number_format($totalSettledPrice, 2) . CONF_CURRENCY_RIGHT;
                        break;
                    case 'payable_amount':
                        echo CONF_CURRENCY . number_format($payable_amount, 2) . CONF_CURRENCY_RIGHT;
                        break;
                    case 'credits':
                        echo CONF_CURRENCY . number_format($totalcredits, 2) . CONF_CURRENCY_RIGHT;
                        break;
                    case 'total_sales':
                        $total_sales = $totalSettledPrice + $totalUnsettledPrice;
                        echo CONF_CURRENCY . number_format($total_sales, 2) . CONF_CURRENCY_RIGHT;
                        break;
                    case 'payout':
                        if ($totalDebits > 0) {
                            echo '<a href="company-transactions.php?company=' . $row[$primaryKey] . '" title="' . t_lang('M_TXT_CLICK_TO_VIEW_TRANSACTION_LIST') . '"><strong>' . CONF_CURRENCY . number_format($totalDebits, 2) . CONF_CURRENCY_RIGHT . '</strong></a>';
                        } else {
                            echo '<a href="company-transactions.php?company=' . $row[$primaryKey] . '" ><strong>' . CONF_CURRENCY . '0.00' . CONF_CURRENCY_RIGHT . '</strong></a>';
                        }
                        break;
                    default:
                        echo $row[$key];
                        break;
                }
                echo '</td>';
                $i++;
            }
            echo '</tr>';
        }
        if ($db->total_records($rs_listing) == 0) {
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
</td>
<?php
require_once './footer.php';
