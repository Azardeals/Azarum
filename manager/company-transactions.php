<?php
require_once './application-top.php';
require_once '../includes/navigation-functions.php';
require_once '../includes/deal_functions.php';
checkAdminPermission(3);
$page = (is_numeric($_GET['page']) ? $_GET['page'] : 1);
if (!isset($_GET['company'])) {
    redirectUser('companies.php');
}
$post = getPostedData();
$Src_frm = new Form('Src_frm', 'Src_frm');
$Src_frm->setTableProperties(' class="tbl_form" width="100%"');
$Src_frm->setFieldsPerRow(2);
$Src_frm->captionInSameCell(false);
$Src_frm->addTextBox(t_lang('M_TXT_FILTER_TRANSACTIONS_DEAL_WISE'), 'deal-company', '', 'deal-company', '');
$Src_frm->addHiddenField('', 'mode', 'search');
$Src_frm->addHiddenField('', 'status', $_REQUEST['status']);
//Search Form
$frm = new Form('payfrm', 'payfrm');
$frm->setTableProperties('border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$frm->setFieldsPerRow(1);
$frm->captionInSameCell(false);
$frm->setJsErrorDisplay('afterfield');
$frm->setValidatorJsObjectName('frmValidator');
$frm->setExtra('onsubmit="submitAddTransaction(this, frmValidator); return(false);"');
$frm->addHTML('<strong>' . t_lang('M_TXT_DEBIT') . ':</strong> ' . t_lang('M_TXT_MAKE_PAYMENT_TO_MERCHANT') . '<br/>
<strong>' . t_lang('M_TXT_CREDIT') . ':</strong> ' . t_lang('M_TXT_WHEN_WANT_TO_GIVE_CREDIT'), '', '', '', true)->merge_caption = 2;
$frm->addRadioButtons(t_lang('M_TXT_ENTRY_TYPE'), 'entry_type', array('1' => t_lang('M_TXT_DEBIT'), '2' => t_lang('M_TXT_CREDIT')), '', 2, 'width="100%"', '');
$frm->addTextBox(t_lang('M_TXT_AMOUNT'), 'cwh_amount', '', '', '')->requirements()->setRequired();
$frm->addTextArea(t_lang('M_TXT_PARTICULARS'), 'cwh_particulars', '', '', '')->requirements()->setRequired();
$frm->addHiddenField('', 'cwh_company_id', $_GET['company']);
$frm->addHiddenField('', 'cwh_untipped_deal_id', $_GET['deal']);
$frm->addHiddenField('', 'mode', 'submitAddTransaction');
$fld = $frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SUBMIT'), '', ' class="inputbuttons"');
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($post['deal-company'])) {
    if (isset($post['entry_type'])) {
        $post = getPostedData();
        if (!$frm->validate($post)) {
            $errors = $frm->getValidationErrors();
            foreach ($errors as $error)
                $msg->addError($error);
        } else {
            $record = new TableRecord('tbl_company_wallet_history');
            $record->assignValues($post);
            if ($post['entry_type'] == 2) {
                $amount = $post['cwh_amount'];
            }
            if ($post['entry_type'] == 1) {
                $amount = (-1) * ($post['cwh_amount']);
            }
            $record->setFldValue('cwh_amount', $amount, true);
            $record->setFldValue('cwh_time', date('Y-m-d H:i:s'), true);
            $record->setFldValue('cwh_untipped_deal_id', $_GET['deal']);
            $success = $record->addNew();
            if ($success) {
                $rs = $db->query("select * from tbl_companies where company_id=" . $post['cwh_company_id']);
                $row = $db->fetch($rs);
                $rs1 = $db->query("select (sum(cwh_amount)) as balance from tbl_company_wallet_history where cwh_company_id=" . $post['cwh_company_id']);
                $row1 = $db->fetch($rs1);
                $rs_tpl = $db->query("select * from tbl_email_templates where tpl_id=30");
                $row_tpl = $db->fetch($rs_tpl);
                /* Notify User */
                $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
                $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
                if ($post['entry_type'] == 2) {
                    $amount = (-1) * ($post['cwh_amount']);
                }
                if ($post['entry_type'] == 1) {
                    $amount = $post['cwh_amount'];
                }
                $arr_replacements = array(
                    'xxcharity_namexx' => $row['company_name'],
                    'xxcharity_email_addressxx' => $row['company_email'],
                    'xxparticularsxx' => $post['cwh_particulars'],
                    'xxamountxx' => CONF_CURRENCY . number_format(($amount), 2) . CONF_CURRENCY_RIGHT,
                    'xxbalancexx' => $row1['balance'],
                    'xxsite_namexx' => CONF_SITE_NAME,
                    'xxserver_namexx' => $_SERVER['SERVER_NAME'],
                    'xxwebrooturlxx' => CONF_WEBROOT_URL,
                    'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL
                );
                foreach ($arr_replacements as $key => $val) {
                    $subject = str_replace($key, $val, $subject);
                    $message = str_replace($key, $val, $message);
                }
                if ($row_tpl['tpl_status'] == 1) {
                    sendMail($row['company_email'], $subject, emailTemplateSuccess($message), $headers);
                }
                /* Notify User Ends */
                $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
                redirectUser('company-transactions.php?company=' . $post['cwh_company_id']);
            } else {
                $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
                $frm->fill($post);
            }
        }
    } else {
        $msg->addError(t_lang('M_TXT_PLEASE_SELECT_ENTRY_TYPE_FIRST'));
    }
}
$srch = new SearchBase('tbl_company_wallet_history', 'cwh');
if ($_GET['deal'] > 0) {
    $srch->addCondition('cwh_untipped_deal_id', '=', $_GET['deal']);
} else {
    $srch->addCondition('cwh_company_id', '=', $_GET['company']);
}
$srch->addFld('cwh.*');
$srch->addFld('CASE WHEN cwh_amount > 0 THEN cwh_amount ELSE 0 END as added');
$srch->addFld('CASE WHEN cwh_amount <= 0 THEN ABS(cwh_amount) ELSE 0 END as used');
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
$arrs = getSettledUnsettledDealData($_GET['company']);
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
?>
</div></td>
<?php
if ($_GET['deal'] > 0) {
    $companyBalance = t_lang('M_TXT_DEAL_BALANCE');
    $companyPage = t_lang('M_TXT_DEAL_BALANCE');
} else {
    $companyBalance = t_lang('M_TXT_COMPANY_BALANCE');
    $companyPage = t_lang('M_TXT_COMPANY');
}
if ($_GET['deal'] > 0) {
    $arr_bread = [
        'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
        'deals.php?status=active' => t_lang('M_TXT_DEAL'),
        '' => t_lang('M_TXT_DEAL_BALANCE')
    ];
} else {
    $arr_bread = [
        'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
        'companies.php?status=active' => t_lang('M_TXT_COMPANY'),
        '' => t_lang('M_TXT_ACCOUNT_STATEMENT')
    ];
}
$arr = $db->fetch_all($rs_listing);
$balance = 0;
static $debit = 0, $credit = 0;
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
$companyName = $db->query("select company_name" . $_SESSION['lang_fld_prefix'] . " from tbl_companies where company_id=" . intval($_GET['company']));
$rowCom = $db->fetch($companyName);
?>
<script type="text/javascript" src="<?php echo CONF_WEBROOT_URL; ?>js/jquery.autocomplete.js"></script>
<link rel="stylesheet" href="<?php echo CONF_WEBROOT_URL; ?>css/jquery.autocomplete.css" type="text/css" />
<script type="text/javascript" charset="utf-8">
    var company_id =<?php echo $_REQUEST['company']; ?>;
    $(document).ready(function () {
        $("#deal-company").autocomplete(
                "autocomplete-deal.php",
                {
                    extraParams: {'company_id': company_id},
                    onItemSelect: function (li) {
                        /* alert(li.extra[0]); */
                        var deal_id = li.extra[0];
                        callAjax('company-ajax.php', 'mode=dealwisetransaction&company_id=' + company_id + '&deal_id=' + deal_id, function (t) {
                            $('#transaction-display').html(t);
                        });
                        callAjax('company-ajax.php', 'mode=getdealname&deal_id=' + deal_id, function (t) {
                            $('#deal-name').html(t);
                        });
                    }
                }
        );
    });
</script>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name">
            <?php
            if ($_GET['deal'] > 0) {
                $deal_id = $_GET['deal'];
                echo t_lang('M_TXT_DEAL_BALANCE') . ' : ' . htmlentities($rowCom['company_name' . $_SESSION['lang_fld_prefix']]);
            } else {
                $deal_id = 0;
                echo t_lang('M_TXT_ACCOUNT_STATEMENT') . ' : ' . htmlentities($rowCom['company_name' . $_SESSION['lang_fld_prefix']]);
            }
            ?>
            <?php if (checkAdminAddEditDeletePermission(3, '', 'add')) { ?> 
                <ul class="actions right">
                    <li class="droplink">
                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                        <div class="dropwrap">
                            <ul class="linksvertical">
                                <li><a href="javascript:void(0);" onclick="addTransaction(<?php echo $_GET['company']; ?>,<?php echo $_GET['deal'] ?? '0'; ?>, <?php echo $balance; ?>)" alt="When you want to give credit to the merchant for their sales."><?php echo t_lang('M_TXT_ADD_TRANSACTION'); ?></a>	</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            <?php } ?>
        </div>
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide();
                        return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
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
    <?php
    if ($_GET['mode'] == 'pay') {
        if (checkAdminAddEditDeletePermission(3, '', 'add')) {
            ?>
            <div class="box"><div class="title"> 
                    <?php
                    if ($_GET['deal'] > 0) {
                        echo t_lang('M_TXT_PAY_DEAL_WISE');
                    } else {
                        echo t_lang('M_TXT_PAY_TO_COMPANY');
                    }
                    ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
                    <?php
                }
            } else {
                ?>
                <?php
                if (!isset($_GET['deal'])) {
                    if ($db->total_records($rs_listing) > 0) {
                        ?>
                <div class="box searchform_filter"><div class="title"> <?php echo t_lang('M_TXT_FILTER_TRANSACTIONS_DEAL_WISE'); ?>  <?php echo t_lang('M_TXT_SEARCH'); ?>  </div><div class="content togglewrap" style="display:none;">	<?php echo $Src_frm->getFormHtml(); ?></div>	 </div>	
                <?php
            }
        }
        echo '<br/><span id="deal-name"></span>';
        ?>
        <table class="tbl_data" width="100%"  id="transaction-display">
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
            $balanceNew = 0;
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
    <?php } ?>
</td>
<?php
require_once './footer.php';
