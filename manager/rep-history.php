<?php
require_once './application-top.php';
require_once '../includes/navigation-functions.php';
checkAdminPermission(8);
$page = (is_numeric($_GET['page']) ? $_GET['page'] : 1);
$post = getPostedData();
$rep_id = $_GET['rep_id'];
//Search Form
$frm = new Form('payfrm', 'payfrm');
$frm->setTableProperties('border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$frm->setFieldsPerRow(1);
$frm->captionInSameCell(false);
$frm->setJsErrorDisplay('afterfield');
$frm->addHTML('<strong>Debit:</strong> When you actually make the payment to representative.<br/>
<strong>Credit:</strong> When you want to give credit to the representative for their commissions.', '', '', '', true)->merge_caption = 2;
$frm->addRadioButtons(t_lang('M_TXT_ENTRY_TYPE'), 'entry_type', array('1' => 'Debit', '2' => 'Credit'), '', 2, 'width="100%"', '');
$frm->addTextBox(t_lang('M_TXT_AMOUNT'), 'rwh_amount', '', '', '')->requirements()->setRequired();
$frm->addTextArea(t_lang('M_TXT_PARTICULARS'), 'rwh_particulars', '', '', '')->requirements()->setRequired();
$frm->addHiddenField('', 'rwh_rep_id', $rep_id);
$frm->addHiddenField('', 'rwh_untipped_deal_id', $_GET['deal_id']);
$frm->addHiddenField('', 'mode', 'pay');
$fld = $frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SUBMIT'), '', ' class="inputbuttons"');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error) {
            $msg->addError($error);
        }
    } else {
        if (!isset($post['entry_type'])) {
            $msg->addError(t_lang('M_TXT_PLEASE_SELECT_ENTRY_TYPE_FIRST'));
            redirectUser('?rep_id=' . $post['rwh_rep_id'] . '&mode=pay');
        }
        payToRepresentativeByAdmin($post);
        redirectUser('?rep_id=' . $post['rwh_rep_id']);
    }
}
$srch = new SearchBase('tbl_representative_wallet_history', 'rwh');
$srch->addCondition('rwh_rep_id', '=', $rep_id);
$srch->addFld('rwh.*');
$srch->addFld('CASE WHEN rwh_amount > 0 THEN rwh_amount ELSE 0 END as added');
$srch->addFld('CASE WHEN rwh_amount <= 0 THEN ABS(rwh_amount) ELSE 0 END as used');
$srch->addOrder('rwh_time', 'asc');
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
$arr_listing_fields = [
    'listserial' => t_lang('M_TXT_S_N'),
    'rwh_particulars' => t_lang('M_TXT_PARTICULARS'),
    'added' => t_lang('M_TXT_CREDIT'),
    'used' => t_lang('M_TXT_DEBIT'),
    'balance' => t_lang('M_TXT_BALANCE'),
    'rwh_time' => t_lang('M_TXT_DATE')
];
require_once './header.php';
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'representative.php?status=active' => t_lang('M_TXT_REPRESENTATIVE'),
    '' => t_lang('M_TXT_TRANSACTION_HISTORY')
];
$repData = $db->query("select rep_fname,rep_lname,rep_email_address from tbl_representative where rep_id=$rep_id");
$rowRep = $db->fetch($repData);
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_REPRESENTATIVE_HISTORY'); ?> 
            <ul class="actions right">
                <li class="droplink">
                    <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                    <div class="dropwrap">
                        <ul class="linksvertical">
                            <li><a href="?rep_id=<?php echo $_GET['rep_id']; ?>&mode=pay"><?php echo t_lang('M_TXT_ADD_TRANSACTION'); ?></a></li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
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
    <?php
    if ($_GET['mode'] == 'pay') {
        if (checkAdminAddEditDeletePermission(8, '', 'add')) {
            ?>
            <div class="box"><div class="title"> <?php echo t_lang('M_TXT_PAY_TO_REPRESENTATIVE'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
            <?php
        }
    } else {
        ?>
        <div class="box"><div class="title"><?php echo t_lang('M_TXT_REPRESENTATIVE_HISTORY'); ?> </div><div class="content"> 
                <?php echo '<strong>' . t_lang('M_FRM_NAME') . ': </strong>' . $rowRep['rep_fname'] . ' ' . $rowRep['rep_lname'] . '<br/><strong>' . t_lang('M_FRM_EMAIL_ADDRESS') . ': </strong>' . $rowRep['rep_email_address']; ?>
                <div class="gap">&nbsp;</div>
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
                        $arr = $db->fetch_all($rs_listing);
                        $balance = 0;
                        foreach ($arr as $key => $row) {
                            $balance += $row['rwh_amount'];
                            $arr[$key]['rwh_time'] = displayDate($row['rwh_time'], true, true, '');
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
                                    case 'rwh_time':
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
                        } else {
                            
                        }
                        ?>
                </table>
            </div></div>
    <?php } ?>
</td>
<?php require_once './footer.php'; ?>
