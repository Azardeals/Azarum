<?php
require_once './application-top.php';
require_once '../includes/navigation-functions.php';
checkAdminPermission(8);
global $db;
$page = (is_numeric($_GET['page']) ? $_GET['page'] : 1);
$post = getPostedData();
if (isset($_GET['affiliate'])) {
    $rs2 = $db->query("select (SUM(wh_amount)) as totalamount from tbl_affiliate_wallet_history where wh_affiliate_id=" . $_GET['affiliate']);
    $rowaffiliate2 = $db->fetch($rs2);
}
//Search Form
$frm = new Form('payfrm', 'payfrm');
$frm->setTableProperties('border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$frm->setFieldsPerRow(1);
$frm->captionInSameCell(false);
$frm->setJsErrorDisplay('afterfield');
$frm->addTextBox(t_lang('M_TXT_AMOUNT'), 'wh_amount', $rowaffiliate2['totalamount'], '', '')->requirements()->setRequired();
$frm->addTextArea(t_lang('M_TXT_PARTICULARS'), 'wh_particulars', '', '', '')->requirements()->setRequired();
$frm->addHiddenField('', 'wh_affiliate_id', $_GET['affiliate']);
$frm->addHiddenField('', 'wh_untipped_deal_id', $_GET['deal_id']);
$frm->addHiddenField('', 'mode', 'pay');
$fld = $frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SUBMIT'), '', ' class="inputbuttons"');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error)
            $msg->addError($error);
    } else {
        payToAffiliateByAdmin($post);
    }
}
$srch = new SearchBase('tbl_affiliate_wallet_history', 'wh');
$srch->addCondition('wh_affiliate_id', '=', (int) $_GET['affiliate']);
$srch->addFld('wh.*');
$srch->addFld('CASE WHEN wh_amount > 0 THEN wh_amount ELSE 0 END as added');
$srch->addFld('CASE WHEN wh_amount <= 0 THEN ABS(wh_amount) ELSE 0 END as used');
/* $srch->setPageNumber($page);
  $srch->setPageSize($pagesize); */
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
$arr_listing_fields = [
    'listserial' => t_lang('M_TXT_S_N'),
    'transaction_id' => t_lang('M_TXT_SYSTEM_TRANSACTION_ID'),
    'wh_particulars' => t_lang('M_TXT_PARTICULARS'),
    'added' => t_lang('M_TXT_CREDIT'),
    'used' => t_lang('M_TXT_DEBIT'),
    'balance' => t_lang('M_TXT_BALANCE'),
    'wh_time' => t_lang('M_TXT_DATE')
];
require_once './header.php';
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'affiliate.php?status=active' => t_lang('M_TXT_AFFILIATE'),
    '' => t_lang('M_TXT_TRANSACTIONS')
];
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline affiliate-header"> 
        <div class="page-name fl"><?php echo t_lang('M_TXT_AFFILIATE_TRANSACTIONS'); ?> 
            <?php
            $sql1 = $db->query("SELECT sum(wh_amount) as affiliateToalAmount FROM tbl_affiliate_wallet_history WHERE wh_affiliate_id = " . (int) $_GET['affiliate']);
            $amount_data = $db->fetch($sql1);
            echo '<div class="right">';
            if ($amount_data['affiliateToalAmount'] != '0.00') {
                echo '<ul class="actions"><li><a title="' . t_lang('M_TXT_ADD_TRANSACTION') . '" href="affiliate-history.php?affiliate=' . (int) $_GET['affiliate'] . '&mode=pay"><i class="ion-social-usd icon"></i></a></li></ul>';
            }
            echo '</div>';
            ?>
        </div>
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
    <?php
    if ($_GET['mode'] == 'pay') {
        if (checkAdminAddEditDeletePermission(8, '', 'add')) {
            ?>
            <div class="box"><div class="title"> <?php echo t_lang('M_TXT_PAY_TO_AFFILIATE'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
            <?php
        }
    } else {
        ?>
        <table class="tbl_data" width="100%">
            <thead>
                <tr>
                <tr>
                    <?php
                    foreach ($arr_listing_fields as $key => $val)
                        echo '<th' . (($key == 'added' || $key == 'used' || $key == 'wh_time') ? '  width="15%"' : '') . (($key == 'balance' ) ? '   width="12%"' : '') . '>' . $val . '</th>';
                    ?>
                </tr>
                <?php
                $arr = $db->fetch_all($rs_listing);
                $balance = 0;
                $M_TXT_AFFILIATE_COMMISSION_FOR = t_lang('M_TXT_AFFILIATE_COMMISSION_FOR');
                $M_TXT_COMMISSION_FOR_ORDERID = t_lang('M_TXT_COMMISSION_FOR_ORDERID');
                foreach ($arr as $key => $row) {
                    $balance += $row['wh_amount'];
                    $arr[$key]['wh_id'] = $row['wh_id'];
                    $arr[$key]['wh_time'] = $row['wh_time'];
                    $arr[$key]['added'] = $row['added'];
                    $arr[$key]['used'] = $row['used'];
                    $arr[$key]['balance'] = $balance;
                }
                $arr = array_reverse($arr);
                $listserial = ($page - 1) * $pagesize + 1;
                $balanceNew = 0;
                foreach ($arr as $row) {
                    /* $balanceNew = $total; */
                    echo '<tr  >';
                    foreach ($arr_listing_fields as $key => $val) {
                        echo '<td ' . (($key == 'added' || $key == 'used') ? ' ' : '') . '>';
                        switch ($key) {
                            case 'listserial':
                                echo $listserial;
                                break;
                            case 'transaction_id':
                                echo $row['wh_id'];
                                break;
                            case 'wh_time':
                                echo displayDate($row[$key], true, true, '');
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
                                $temp = $row[$key];
                                $temp = str_ireplace('M_TXT_AFFILIATE_COMMISSION_FOR', $M_TXT_AFFILIATE_COMMISSION_FOR, $temp);
                                $temp = str_ireplace('M_TXT_COMMISSION_FOR_ORDERID', $M_TXT_COMMISSION_FOR_ORDERID, $temp);
                                echo $temp;
                                break;
                        }
                        echo '</td>';
                    }
                    echo '</tr>';
                    $listserial++;
                }
                if (count($arr) == 0) {
                    echo '<tr><td colspan="' . count($arr_listing_fields) . '" >' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
                } else {
                    
                }
                ?>
        </table>
    <?php } ?>
</td>
<?php
require_once './footer.php';
?>
