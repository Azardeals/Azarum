<?php
require_once './application-top.php';
require_once '../includes/navigation-functions.php';
$arr_common_js[] = 'js/calendar.js';
$arr_common_js[] = 'js/calendar-en.js';
$arr_common_js[] = 'js/calendar-setup.js';
$arr_common_css[] = 'css/cal-css/calendar-win2k-cold-1.css';
checkAdminPermission(15);
$post = getPostedData();
$Src_frm = new Form('Src_frm', 'Src_frm');
$Src_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$Src_frm->setFieldsPerRow(3);
$Src_frm->captionInSameCell(true);
$Src_frm->addTextBox(t_lang('M_FRM_KEYWORD'), 'keyword', '', '', '');
$Src_frm->addDateField(t_lang('M_FRM_TRANSACTION_START_DATE'), 'transaction_start_time', '', 'transaction_start_time', '');
$Src_frm->addDateField(t_lang('M_FRM_TRANSACTION_END_DATE'), 'transaction_end_time', '', 'transaction_end_time', '');
$Src_frm->addHiddenField('', 'mode', 'search');
$fld1 = $Src_frm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="inputbuttons" onclick=location.href="referral-history.php"');
$fld = $Src_frm->addSubmitButton('', 'btn_search', t_lang('M_TXT_SEARCH'), '', ' class="inputbuttons"');
$fld2 = $Src_frm->addSubmitButton('', 'btn_download', t_lang('M_TXT_DOWNLOAD_REPORT'), 'btn_submit');
$fld->attachField($fld1);
$fld1->attachField($fld2);
$srch = new SearchBase('tbl_referral_history', 'rh');
$srch->joinTable('tbl_users', 'LEFT OUTER JOIN', 'rh.rh_credited_to=credit.user_id', 'credit');
$srch->joinTable('tbl_users', 'LEFT OUTER JOIN', 'rh.rh_referral_user_id=ref.user_id', 'ref');
if ($post['mode'] == 'search' && isset($post['btn_search'])) {
    if ($post['keyword'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('credit.user_email', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('credit.user_name', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('ref.user_email', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('ref.user_name', 'like', '%' . $post['keyword'] . '%', 'OR');
    }
    if ($post['transaction_start_time'] != '') {
        $start_time = date('Y-m-d', strtotime($post['transaction_start_time']));
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition("mysql_func_date(rh.`rh_transaction_date`)", '>=', $start_time, 'OR', true);
    }
    if ($post['transaction_end_time'] != '') {
        $end_time = date('Y-m-d', strtotime($post['transaction_end_time']));
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition("mysql_func_date(rh.`rh_transaction_date`)", '<=', $end_time, 'OR', true);
    }
    $Src_frm->fill($post);
}
$srch->addMultipleFields(array('rh.*', 'credit.user_name as credit_user_name', 'credit.user_email as credit_user_email', 'ref.user_name as ref_user_name', 'ref.user_email as ref_user_email', 'ref.user_regdate as ref_user_regdate'));
$rs_listing = $srch->getResultSet();
$arr_listing_fields = array(
    'listserial' => t_lang('M_TXT_S_N'),
    'rh_amount' => t_lang('M_TXT_AMOUNT'),
    'rh_credited_to' => t_lang('M_TXT_CREDITTED_TO'),
    'rh_referral_user_id' => t_lang('M_TXT_REFERRED_USER'),
    'rh_transaction_date' => t_lang('M_TXT_TRANSACTION_DATE')
);
if ($post['btn_download']) {
    $output = "Amount, Credited To, Referred User, Transaction Date";
    $output .= "\n";
    $row = $db->fetch_all($rs_listing);
    foreach ($row as $data) {
        $output .= '"' . CONF_CURRENCY . number_format(($data['rh_amount']), 2) . CONF_CURRENCY_RIGHT . '","' . $data['credit_user_name'] . '(' . $data['credit_user_email'] . ')' . '","' . $data['ref_user_name'] . '(' . $data['ref_user_email'] . ')' . '","' . displayDate($data['rh_transaction_date'], true) . '"';
        $output .= "\n";
    }
    $filename = "referral_history.csv";
    header('Content-type: application/csv');
    header('Content-Disposition: attachment; filename=' . $filename);
    echo $output;
    exit;
}
require_once './header.php';
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0);' => t_lang('M_TXT_REPORTS'),
    '' => t_lang('M_TXT_REFERRAL_COMMISSION_TRANSACTION')
];
?>
<ul class="nav-left-ul">
    <li><a href="referral-history.php" class="selected"><?php echo t_lang('M_TXT_REFERRAL_COMMISSION_TRANSACTION'); ?></a></li>
</ul>
</div>
</td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_REFERRAL_HISTORY'); ?> </div>
    </div>
    <div class="clear"></div>
    <div class="box searchform_filter">
        <div class="title"><?php echo t_lang('M_TXT_REFERRAL_COMMISSION_TRANSACTION'); ?> </div>
        <div class="content togglewrap" style="display:none;"><?php echo $Src_frm->getFormHtml(); ?></div>
    </div>
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
                    foreach ($arr_listing_fields as $key => $val) {
                        echo '<th' . (($key == 'rh_amount' || $key == 'rh_transaction_date') ? '  width="15%"' : '') . '>' . $val . '</th>';
                    }
                    ?>
                </tr>
                <?php
                $arr = array_reverse($arr);
                $listserial = ($page - 1) * $pagesize + 1;
                $balanceNew = 0;
                while ($row = $db->fetch($rs_listing)) {
                    echo '<tr>';
                    foreach ($arr_listing_fields as $key => $val) {
                        echo '<td ' . (($key == 'added' || $key == 'used') ? ' ' : '') . '>';
                        switch ($key) {
                            case 'listserial':
                                echo $listserial;
                                break;
                            case 'rh_transaction_date':
                                echo displayDate($row[$key], true, true, '');
                                break;
                            case 'rh_credited_to':
                                echo $row['credit_user_name'] . '<br/>' . $row['credit_user_email'];
                                break;
                            case 'rh_referral_user_id':
                                echo '<strong>' . t_lang('M_TXT_USER_NAME') . ': </strong>' . $row['ref_user_name'] . '<br/><strong>' . t_lang('M_TXT_EMAIL_ADDRESS') . ': </strong>' . $row['ref_user_email'] . '<br/><strong>' . t_lang('M_TXT_REGISTRATION_DATE') . ': </strong>' . displayDate($row['ref_user_regdate'], true, true, '');
                                break;
                            case 'rh_amount':
                                echo CONF_CURRENCY . number_format(($row['rh_amount']), 2) . CONF_CURRENCY_RIGHT;
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
                if ($db->total_records($rs_listing) == 0) {
                    echo '<tr><td colspan="' . count($arr_listing_fields) . '" >' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
                }
                ?>
        </table>
    <?php } ?>
</td>
<?php require_once './footer.php'; ?>
