<?php
require_once './application-top.php';
checkAdminPermission(5);
$srch = new SearchBase('tbl_representative', 'r');
$srch->addCondition('rep_id', '=', $_REQUEST['uid']);
$srch->addFld(['r.rep_commission']);
$rs_listing = $srch->getResultSet();
$rep = $db->fetch($rs_listing);
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$pagesize = 15;
$post = getPostedData();
$srch = new SearchBase('tbl_deals', 'd');
$srch->addCondition('deal_deleted', '=', 0);
$srch->joinTable('tbl_cities', 'INNER JOIN', 'd.deal_city=c.city_id', 'c');
$srch->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=company.company_id ', 'company');
$srch->addMultipleFields(['d.*', 'c.*', 'company.*']);
if ($_REQUEST['deal_company'] != '') {
    $srch->addCondition('deal_company', '=', $_REQUEST['deal_company']);
}
$srch->addOrder('d.deal_start_time', 'desc');
$srch->addOrder('d.deal_status');
$srch->addOrder('d.deal_name');
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
$pagestring .= createHiddenFormFromPost('frmPaging', '?', ['page', 'status', 'deal_company', 'uid'], ['page' => '', 'status' => $_REQUEST['status'], 'deal_company' => $_REQUEST['deal_company'], 'uid' => $_REQUEST['uid']]);
$pagestring .= '<div class="pagination"><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$arr_listing_fields = [
    'listserial' => t_lang('M_TXT_SR_NO'),
    'deal_name' => t_lang('M_TXT_DEAL_NAME'),
    'deal_tipped_at' => t_lang('M_TXT_DEAL_TIPPED_AT'),
    'deal_end_time' => t_lang('M_TXT_DEAL_END_TIME'),
    'total_sales' => t_lang('M_TXT_TOTAL_SALES'),
    'status' => t_lang('M_TXT_STATUS'),
    'setteled' => t_lang('M_TXT_SETTLED'),
    'total_commission' => t_lang('M_TXT_TOTAL_COMMISSION'),
];
require_once './header.php';
$arr_bread = [
    'index.php' => '<img alt="Home" src="images/home-icon.png">',
    'rep_list.php?uid=' . $_GET['uid'] => t_lang('M_TXT_COMMISSION_EARNINGS'),
    '' => t_lang('M_TXT_SALES'),
];
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_SALES'); ?></div>
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="redtext"><?php echo $msg->display(); ?> </div><br/><br/>
                <?php } if (isset($_SESSION['msgs'][0])) { ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?> 
    <table class="tbl_data" width="100%" id="category_listing">
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
        $totalComm = 0;
        for ($listserial = ($page - 1) * $pagesize + 1; $row = $db->fetch($rs_listing); $listserial++) {
            echo '<tr>';
            foreach ($arr_listing_fields as $key => $val) {
                echo '<td ' . (($arr_listing_fields['deal_name'] == $val) ? 'width=30%' : ' ') . ((($arr_listing_fields['deal_tipped_at'] == $val) || ($arr_listing_fields['deal_end_time'] == $val) ) ? ' width=15%' : ' ') . (($arr_listing_fields['listserial'] == $val) ? ' width=4%' : '') . '>';
                switch ($key) {
                    case 'listserial':
                        echo $listserial;
                        break;
                    case 'deal_name':
                        echo '<strong>' . $arr_lang_name[0] . '</strong>' . ' ' . $row['deal_name'] . '<br/>';
                        echo '<strong>' . $arr_lang_name[1] . '</strong>' . ' ' . $row['deal_name_lang1'];
                        break;
                    case 'deal_tipped_at':
                        if ($row['deal_tipped_at'] != '0000-00-00 00:00:00') {
                            echo displayDate($row['deal_tipped_at'], true);
                        } else {
                            echo '---';
                        }
                        break;
                    case 'deal_end_time':
                        echo displayDate($row['deal_end_time'], true);
                        break;
                    case 'status':
                        echo $arr_deal_status[$row['deal_status']];
                        break;
                    case 'total_sales':
                        $srch = new SearchBase('tbl_orders', 'o');
                        $srch->joinTable('tbl_order_deals', 'INNER JOIN', 'o.order_id=od.od_order_id ', 'od');
                        $srch->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id=d.deal_id ', 'd');
                        $srch->addCondition('o.order_payment_status', '!=', 0);
                        $srch->addCondition('od.od_deal_id', '=', $row['deal_id']);
                        $srch->addMultipleFields(array('od.*', 'o.*', 'd.deal_company', "SUM((od.od_qty+od.od_gift_qty)*od.od_deal_price) as totalAmount"));
                        $data = $srch->getResultSet();
                        $amountRow = $db->fetch($data);
                        if ($db->total_records($data) > 0) {
                            echo CONF_CURRENCY . number_format($amountRow['totalAmount'], 2) . CONF_CURRENCY_RIGHT;
                        }
                        break;
                    case 'total_commission':
                        $srch = new SearchBase('tbl_coupon_mark', 'cm');
                        $srch->joinTable('tbl_order_deals', 'INNER JOIN', 'cm.cm_order_id=od.od_order_id ', 'od');
                        $srch->joinTable('tbl_orders', 'INNER JOIN', 'cm.cm_order_id=o.order_id AND cm_deal_id=od_deal_id', 'o');
                        $srch->addCondition('o.order_payment_status', '!=', 0);
                        $srch->addCondition('cm.cm_status', '=', 1);
                        $srch->addCondition('cm.cm_deal_id', '=', $row['deal_id']);
                        $srch->addMultipleFields(array("SUM(od.od_deal_price) as PaidAmount"));
                        $data = $srch->getResultSet();
                        $amountRow = $db->fetch($data);
                        if ($db->total_records($data) > 0) {
                            $rep_com = $rep['rep_commission'] * $amountRow['PaidAmount'] / 100;
                            echo CONF_CURRENCY . number_format($rep_com, 2) . CONF_CURRENCY_RIGHT;
                        }
                        $totalComm += $rep_com;
                        break;
                    case 'setteled':
                        if ($row['deal_paid'] == 1) {
                            echo 'Yes';
                        }
                        if ($row['deal_paid'] == 0) {
                            echo 'No';
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
        if ($db->total_records($rs_listing) == 0) {
            echo '<tr><td colspan="' . count($arr_listing_fields) . '">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
        }
        ?>
    </table>
    <?php if ($pages > 1) { ?>
        <div class="footinfo">
            <aside class="grid_1">
                <?php echo $pagestring; ?>	 
            </aside>  
            <aside class="grid_2"><span class="info"><?php echo $pageStringContent; ?></span></aside>
        </div>
        <?php
    }
    ?>
</td>
<?php require_once './footer.php'; ?>
