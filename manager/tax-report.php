<?php
require_once './application-top.php';
checkAdminPermission(3);
global $arr_tax_received;
require_once '../includes/navigation-functions.php';
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
$fld1 = $Src_frm->addButton('&nbsp;', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="inputbuttons" onclick="location.href=\'tax-report.php\'"');
$fld = $Src_frm->addSubmitButton('&nbsp;', 'btn_search', t_lang('M_TXT_SEARCH'), '', ' class="inputbuttons"')->attachField($fld1);
$srch = new SearchBase('tbl_companies', 'c');
$srch->addCondition('company_deleted', '=', 0);
$srch->joinTable('tbl_deals', 'INNER JOIN', 'd.deal_company=c.company_id', 'd');
$srch->joinTable('tbl_order_deals', 'INNER JOIN', 'od.od_deal_id=d.deal_id', 'od');
$srch->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id=o.order_id', 'o');
$srch->addOrder('company_name' . $_SESSION['lang_fld_prefix']);
$srch->addGroupBy('od_deal_id');
$srch->addMultipleFields(array('company_name' . $_SESSION['lang_fld_prefix'], 'deal_name' . $_SESSION['lang_fld_prefix'], 'SUM(od_deal_price * od_qty) as deal_price', 'SUM(od_deal_tax_amount * od_qty) as tax', 'deal_commission_percent', 'SUM(CASE WHEN o.order_payment_status=1 THEN (od.od_qty+od.od_gift_qty)*od_deal_price ELSE 0 END) AS saleAmount'));
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
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
//die($srch->getQuery());
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
    'deal_name' => '',
    'deal_price' => '',
    'commission' => '',
    'tax' => '',
    'total_sales' => ''
];
require_once './header.php';
?>
<ul id="content" class="nav-left-ul">
    <li> <a href="tax-report.php" class="selected"><?php echo t_lang('M_TXT_TAX_REPORT'); ?></a> </li>
</ul>
</div>
</td>
<td class="right-portion">
    <?php
    $arr_bread = array(
        'index.php' => '<img alt="Home" src="images/home-icon.png">',
        'javascript:void(0)' => t_lang('M_TXT_REPORTS'),
        '' => t_lang('M_TXT_TAX_REPORT'),
    );
    echo getAdminBreadCrumb($arr_bread);
    ?>
    <?php echo $msg->display(); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_TAX_REPORT') . ' :: ' . t_lang('M_TXT_TAX_RECEIVED_TO') . ' <strong>' . $arr_tax_received[CONF_TAX_RECEIVED] . '</strong>'; ?></div>
    </div>
    <div class="clear"></div>	
    <div class="box searchform_filter">
        <div class="title"><?php echo t_lang('M_TXT_TAX_REPORT'); ?> </div>
        <div class="content togglewrap" style="display:none;"><?php echo $Src_frm->getFormHtml(); ?></div>
    </div>
    <table width="100%" class="tbl_data">
        <tr>
            <th width="20%"><?php echo t_lang('M_TXT_COMPANY_NAME'); ?></th>
            <th width="20%"><?php echo t_lang('M_TXT_DEAL_NAME'); ?></th>
            <th width="15%"><?php echo t_lang('M_TXT_DEAL_PRICE'); ?></th>
            <th width="15%"><?php echo t_lang('M_TXT_COMMISSION'); ?></th>
            <th width="10%"><?php echo t_lang('M_TXT_TAX'); ?></th>
            <th width="10%"><?php echo t_lang('M_TXT_TOTAL_SALES'); ?></th>
        </tr>
        <?php
        for ($listserial = ($page - 1) * $pagesize + 1; $row = $db->fetch($rs_listing); $listserial++) {
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
                        echo htmlentities($row['company_name' . $_SESSION['lang_fld_prefix']]);
                        break;
                    case 'deal_name':
                        echo htmlentities($row['deal_name' . $_SESSION['lang_fld_prefix']]);
                        break;
                    case 'deal_price':
                        echo CONF_CURRENCY . number_format($row['deal_price'], 2) . CONF_CURRENCY_RIGHT;
                        break;
                    case 'commission':
                        $commission = $row['saleAmount'] * $row['deal_commission_percent'] / 100;
                        $saleSummary = '<strong>@ ' . $row['deal_commission_percent'] . '% :</strong> ' . CONF_CURRENCY . number_format($commission, 2) . CONF_CURRENCY_RIGHT . '<br/>';
                        echo $saleSummary;
                        break;
                    case 'tax':
                        echo CONF_CURRENCY . number_format($row['tax'], 2) . CONF_CURRENCY_RIGHT;
                        break;
                    case 'total_sales':
                        $total_sales = $row['tax'] + $row['deal_price'];
                        echo CONF_CURRENCY . number_format($total_sales, 2) . CONF_CURRENCY_RIGHT;
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
            <aside class="grid_1"><?php echo $pagestring; ?></aside>  
            <aside class="grid_2"><span class="info"><?php echo $pageStringContent; ?></span></aside>
        </div>
    <?php } ?>
</td>
<?php require_once './footer.php'; ?>