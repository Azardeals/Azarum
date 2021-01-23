<?php
require_once './application-top.php';
require_once '../includes/navigation-functions.php';
checkAdminPermission(8);
$page = (is_numeric($_GET['page']) ? $_GET['page'] : 1);
$post = getPostedData();
$rs2 = $db->query("select (sum(ch_amount)-sum(ch_debit)) as totalamount from tbl_charity_history where ch_charity_id=" . $_GET['charity']);
$rowCharity2 = $db->fetch($rs2);
//Search Form
$frm = new Form('payfrm', 'payfrm');
$frm->setTableProperties('border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$frm->setFieldsPerRow(1);
$frm->captionInSameCell(false);
$frm->setJsErrorDisplay('afterfield');
$frm->addTextBox(t_lang('M_TXT_AMOUNT'), 'ch_debit', $rowCharity2['totalamount'], '', '')->requirements()->setRequired();
$frm->addTextArea(t_lang('M_TXT_PARTICULARS'), 'ch_particulars', '', '', '')->requirements()->setRequired();
$frm->addHiddenField('', 'ch_charity_id', $_GET['charity']);
$frm->addHiddenField('', 'mode', 'pay');
$frm->addHiddenField('', 'ch_amount', 0);
$fld = $frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SUBMIT'), '', ' class="inputbuttons"');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error)
            $msg->addError($error);
    } else {
        payToCharityByAdmin($post);
    }
}
$srch = new SearchBase('tbl_charity_history', 'ch');
$srch->addCondition('ch_charity_id', '=', $_GET['charity']);
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
$arr_listing_fields = [
    'listserial' => t_lang('M_TXT_S_N'),
    'ch_particulars' => t_lang('M_TXT_PARTICULARS'),
    'ch_amount' => t_lang('M_TXT_CREDIT'),
    'ch_debit' => t_lang('M_TXT_DEBIT'),
    'ch_balance' => t_lang('M_TXT_BALANCE'),
    'ch_time' => t_lang('M_TXT_DATE')
];
require_once './header.php';
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'charity.php?status=active' => t_lang('M_TXT_CHARITY'),
    '' => t_lang('M_TXT_CHARITY_HISTORY')
];
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
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
                    ?><div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?> 
    <?php
    if ($_GET['mode'] == 'pay') {
        if (checkAdminAddEditDeletePermission(8, '', 'add')) {
            ?>
            <div class="box"><div class="title"> <?php echo t_lang('M_TXT_PAY_TO_CHARITY'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
            <?php
        }
    } else {
        ?>	
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
            } else {
                echo '<tr><td colspan="3"></td><td>' . t_lang('M_TXT_TOTAL') . '</td><td colspan="2">' . CONF_CURRENCY . number_format(($Totalbalance), 2) . CONF_CURRENCY_RIGHT . '</td></tr>';
            }
            ?>
        </table>
    <?php } ?>
</td>
<?php
require_once './footer.php';
