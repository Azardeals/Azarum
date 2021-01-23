<?php
require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
if (!isCompanyUserLogged()) {
    redirectUser(CONF_WEBROOT_URL . 'merchant/login.php');
}
$company_id = $_SESSION['logged_user']['company_id'];
$post = getPostedData();
$srch = new SearchBase('tbl_company_wallet_history', 'cwh');
$srch->addCondition('cwh_company_id', '=', $company_id);
if ($_GET['deal'] > 0) {
    $srch->addCondition('cwh_untipped_deal_id', '=', $_GET['deal']);
}
$srch->addFld('cwh.*');
$srch->addFld('CASE WHEN cwh_amount > 0 THEN cwh_amount ELSE 0 END as added');
$srch->addFld('CASE WHEN cwh_amount <= 0 THEN ABS(cwh_amount) ELSE 0 END as used');
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
$arrs = getSettledUnsettledDealData($company_id);
$totalUnsettledPrice = $arrs['totalUnsettledPrice'];
$totalSettledPrice = $arrs['totalSettledPrice'];
$arr_listing_fields = array(
    'listserial' => t_lang('M_TXT_S_N'),
    'cwh_particulars' => t_lang('M_TXT_PARTICULARS'),
    'added' => t_lang('M_TXT_CREDIT'),
    'used' => t_lang('M_TXT_DEBIT'),
    'balance' => t_lang('M_TXT_BALANCE'),
    'cwh_time' => t_lang('M_TXT_DATE')
);
require_once './header.php';
$arr_bread = array('' => t_lang('M_TXT_COMPANY_BALANCE'));
$arr = $db->fetch_all($rs_listing);
static $debit = 0, $credit = 0, $balance = 0;
foreach ($arr as $key => $row) {
    $arr[$key]['cwh_time'] = displayDate($row['cwh_time'], true, true, '');
    $arr[$key]['added'] = $row['added'];
    $arr[$key]['used'] = $row['used'];
    $debit += $row['used'];
    $credit += $row['added'];
    $balance = ($totalSettledPrice + $credit) - $debit;
    $arr[$key]['balance'] = $balance;
}
/* As currently no transaction has been made in company account and whetever needs to be transferred is Settled Amount */
if (empty($arr)) {
    $balance = $totalSettledPrice;
}
?>
</div></td>
<td class="right-portion"> 
    <?php echo getMerchantBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_COMPANY_BALANCE') . ' <strong>(' . CONF_CURRENCY . number_format(($balance), 2) . CONF_CURRENCY_RIGHT . ')</strong>'; ?> </div>
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
    <table class="tbl_data" width="100%">
        <thead>
            <tr>
            <tr>
                <?php
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<th' . (($key == 'added' || $key == 'used' || $key == 'wh_time') ? '  width="15%"' : '') . (($key == 'balance' ) ? '   width="12%"' : '') . '>' . $val . '</th>';
                }
                ?>
            </tr>
            <?php
            $arr = array_reverse($arr);
            $listserial = ($page - 1) * $pagesize + 1;
            foreach ($arr as $row) {
                echo '<tr  >';
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td ' . (($key == 'added' || $key == 'used') ? ' ' : '') . '>';
                    switch ($key) {
                        case 'listserial':
                            echo $listserial;
                            break;
                        case 'cwh_time':
                            echo $row[$key];
                            break;
                        case 'added':
                            echo CONF_CURRENCY . number_format(($row['added']), 2) . CONF_CURRENCY_RIGHT;
                            break;
                        case 'used':
                            echo CONF_CURRENCY . number_format($row['used'], 2) . CONF_CURRENCY_RIGHT;
                            break;
                        case 'balance':
                            echo CONF_CURRENCY . number_format(($row['balance']), 2) . CONF_CURRENCY_RIGHT;
                            break;
                        default:
                            echo $row[$key];
                            break;
                    }
                    echo '</td>';
                }
                echo '</tr>';
                $listserial++;
            }
            if (count($arr) == 0) {
                echo '<tr><td colspan="' . count($arr_listing_fields) . '" >' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
            }
            ?>
    </table> 
</td>
<?php
require_once './footer.php';
