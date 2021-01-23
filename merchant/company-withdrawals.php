<?php
require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
if (!isCompanyUserLogged()) {
    redirectUser(CONF_WEBROOT_URL . 'merchant/login.php');
}

function getCompanyWalletBalance($company_id)
{
    global $db;
    $srch = new SearchBase('tbl_company_wallet_history');
    $srch->addCondition('cwh_company_id', '=', $company_id);
    $srch->addFld('SUM(cwh_amount) as balance');
    $srch->doNotCalculateRecords();
    $srch->doNotLimitRecords();
    $rs = $srch->getResultSet();
    return $db->fetch($rs)['balance'] ?? 0;
}

$company_id = $_SESSION['logged_user']['company_id'];
$post = getPostedData();
if (isset($post['witreq_amount'])) {

    if ($post['witreq_amount'] > getCompanyWalletBalance($company_id)) {
        $msg->addError(t_lang('M_TXT_YOU_DONT_HAVE_SUFFICIENT_BALANCE'));
        redirectUser();
    }

    $frm = getMBSFormByIdentifier('frmCompanyWithdraw');
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error) {
            $msg->addError($error);
        }
    } else {
        $addedOn = date('Y-m-d H:i:s');
        $record = new TableRecord('tbl_withdrawal_requests');
        $record->setFldValue('witreq_company_id', $company_id);
        $record->setFldValue('witreq_added_on', $addedOn);
        $record->setFldValue('witreq_updated_on', $addedOn);
        $record->assignValues($post);
        if ($record->addNew()) {
            $msg->addMsg(t_lang('M_TXT_REQUEST_SUBMITTED_SUCCESSFULL'));
        } else {
            $msg->addError(t_lang('M_TXT_COULD_NOT_SUBMIT'));
        }
    }
    redirectUser();die('here');
}

$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$pagesize = 100;

$srch = new SearchBase('tbl_withdrawal_requests');
$srch->addCondition('witreq_company_id', '=', $company_id);
$srch->addOrder('witreq_id', 'DESC');
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
//echo $srch->getQuery();die;
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();

$pagestring .= createHiddenFormFromPost('frmPaging', '?', ['page'], ['page' => '']);
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
    ' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$flds = [
    'listserial' => t_lang('M_WR_SR_NO'),
    'witreq_amount' => t_lang('M_WR_AMOUNT'),
    'witreq_status' => t_lang('M_WR_STATUS'),
    'witreq_detail' => t_lang('M_WR_DETAIL'),
    'witreq_added_on' => t_lang('M_WR_ADDED_ON'),
    'witreq_updated_on' => t_lang('M_WR_UPDATED_ON')
];
require_once './header.php';
$arr = $db->fetch_all($rs_listing);
$arr_bread = ['' => t_lang('M_WALLET_WITHDRAW_REQUESTS')];

function withdrawRequestStatus($key = null)
{
    $arr = [
        0 => t_lang('M_STATUS_PENDING'),
        1 => t_lang('M_STATUS_APPROVED'),
        2 => t_lang('M_STATUS_REJECTED'),
    ];
    if (null === $key) {
        return $arr;
    }
    return $arr[$key] ?? '';
}
?>
</div></td>
<td class="right-portion"> 
    <?php echo getMerchantBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name">
            <?php echo t_lang('M_WALLET_WITHDRAW_REQUESTS'); ?> 
            <small>(BALANCE: <?php echo getCompanyWalletBalance($company_id); ?>)</small>
            <button type="button" class="right" onclick="requestForm()">Place New Request</button>
        </div>
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
        <div class="right">
            <?php if (isset($_SESSION['errs'][0])) { ?>
                <div class="redtext"><?php echo $msg->display(); ?> </div>
            <?php } if (isset($_SESSION['msgs'][0])) { ?>
                <div class="greentext"> <?php echo $msg->display(); ?> </div>
            <?php } ?>
            <br/>
        </div>
    <?php } ?> 
    <table class="tbl_data" width="100%">
        <thead>
            <tr>
            <tr>
                <?php
                foreach ($flds as $key => $val) {
                    echo '<th>' . $val . '</th>';
                }
                ?>
            </tr>
            <?php
            $listserial = ($page - 1) * $pagesize + 1;
            foreach ($arr as $row) {
                echo '<tr>';
                foreach ($flds as $key => $val) {
                    echo '<td ' . (($key == 'added' || $key == 'used') ? ' ' : '') . '>';
                    switch ($key) {
                        case 'listserial':
                            echo $listserial;
                            break;
                        case 'witreq_detail':
                            echo nl2br($row[$key]);
                            break;
                        case 'witreq_amount':
                            echo CONF_CURRENCY . number_format(($row['witreq_amount']), 2) . CONF_CURRENCY_RIGHT;
                            break;
                        case 'witreq_status':
                            echo withdrawRequestStatus($row[$key]);
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
                echo '<tr><td colspan="' . count($flds) . '" >' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
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
?>
<script type="text/javascript">
    function requestForm() {
        jQuery.facebox(function () {
            callAjax('withdraw-request-form.php', '', function (t) {
                $.facebox(t);
            });
        });
    }

    function requestSetup(frm, v) {
        var v;
        v.validate();
        if (!v.isValid())
        {
            return false;
        }
        var data = getFrmData(frm);
        jQuery.facebox(function () {
            callAjax('withdraw-request-form.php', data, function (t) {
                $.facebox(t);
                $(document).bind('close.facebox', function () {
                    window.location.reload(true);
                });
            });
        });
    }
</script>