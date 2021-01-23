<?php
require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
if ($_SESSION['cityname'] != "") {
    $cityname = convertStringToFriendlyUrl($_SESSION['cityname']);
} else {
    $cityname = 1;
}
if (!isCompanyUserLogged()) {
    redirectUser(CONF_WEBROOT_URL . 'merchant/login.php');
}
$srch = new SearchBase('tbl_charity_history', 'ch');
$srch->addCondition('ch_charity_id', '=', $_GET['charity']);
$rs_listing = $srch->getResultSet();
$arr_listing_fields = array(
    'listserial' => 'S.N.',
    'ch_particulars' => t_lang('M_TXT_PARTICULARS'),
    'ch_amount' => t_lang('M_TXT_CREDIT'),
    'ch_debit' => t_lang('M_TXT_DEBIT'),
    'ch_balance' => t_lang('M_TXT_BALANCE'),
    'ch_time' => t_lang('M_TXT_DATE')
);
$arr_bread = array('charity.php' => t_lang('M_TXT_CHARITY'));
require_once './header.php';
?>
</div></td>
<td class="right-portion"> 
    <?php echo getMerchantBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_CHARITY_HISTORY'); ?></div>
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
                    <?php
                }
                if (isset($_SESSION['msgs'][0])) {
                    ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?> 
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
        $balance = 0;
        for ($listserial = ($page - 1) * $pagesize + 1; $row = $db->fetch($rs_listing); $listserial++) {
            $balance += $row['ch_amount'] - $row['ch_debit'];
            $Totalbalance = $balance;
            $charity_id = $row['charity_id'];
            echo '<tr' . (($row[$colPrefix . 'active'] == '0') ? ' class="inactive"' : '') . ' id = ' . $row['cat_id'] . '>';
            foreach ($arr_listing_fields as $key => $val) {
                echo '<td>';
                switch ($key) {
                    case 'listserial':
                        echo $listserial;
                        break;
                    case 'ch_balance':
                        echo CONF_CURRENCY . number_format(($balance), 2) . CONF_CURRENCY_RIGHT;
                        break;
                    case 'ch_amount':
                        echo CONF_CURRENCY . number_format(($row['ch_amount']), 2) . CONF_CURRENCY_RIGHT;
                        break;
                    case 'ch_debit':
                        echo CONF_CURRENCY . number_format(($row['ch_debit']), 2) . CONF_CURRENCY_RIGHT;
                        break;
                    case 'ch_time':
                        echo displayDate(($row['ch_time']), true);
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
</td>
<?php
require_once './footer.php';
?>
