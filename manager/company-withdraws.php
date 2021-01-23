<?php
require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
checkAdminPermission(3);

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

$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$pagesize = 100;
$post = getPostedData();
if (isset($post['status']) && isset($post['id'])) {
    if (!in_array($post['status'], [1, 2])) {
        $msg->addError(t_lang('M_TXT_INVALID_REQUEST'));
    }
    $srch = new SearchBase('tbl_withdrawal_requests');
    $srch->addCondition('witreq_id', '=', $post['id']);
    $srch->addCondition('witreq_status', '=', 0);
    $row = $db->fetch($srch->getResultSet());
    if (empty($row)) {
        $msg->addError(t_lang('M_TXT_INVALID_REQUEST'));
    }
    if ($row['witreq_amount'] > getCompanyWalletBalance($row['witreq_company_id'])) {
        $msg->addError(t_lang('M_TXT_INSUFFICIENT_BALANCE'));
    }
    $record = new TableRecord('tbl_company_wallet_history');
    $record->setFldValue('cwh_particulars', 'Withdraw Request');
    $record->setFldValue('cwh_company_id', $row['witreq_company_id']);
    $record->setFldValue('cwh_amount', (-1) * $row['witreq_amount'], true);
    $record->setFldValue('cwh_time', date('Y-m-d H:i:s'), true);
    if (!$record->addNew()) {
        die(json_encode(['status' => 0, 'msg' => t_lang('M_TXT_COULD_NOT_SUBMIT')]));
    }

    $record = new TableRecord('tbl_withdrawal_requests');
    $record->setFldValue('witreq_status', $post['status']);
    $record->setFldValue('witreq_updated_on', date('Y-m-d H:i:s'));
    if ($record->update('witreq_id = ' . $post['id'])) {
        die(json_encode(['status' => 1, 'msg' => t_lang('M_TXT_STATUS_UPDATED_SUCCESSFULL')]));
    } else {
        $msg->addError();
        die(json_encode(['status' => 0, 'msg' => t_lang('M_TXT_COULD_NOT_SUBMIT')]));
    }
}

$srch = new SearchBase('tbl_withdrawal_requests', 'witreq');
$srch->joinTable('tbl_companies', 'INNER JOIN', 'company.company_id = witreq.witreq_company_id', 'company');
$srch->addOrder('witreq_id', 'DESC');
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
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
    'company_name' => t_lang('M_WR_COMPANY'),
    'witreq_amount' => t_lang('M_WR_AMOUNT'),
    'witreq_status' => t_lang('M_WR_STATUS'),
    'witreq_detail' => t_lang('M_WR_DETAIL'),
    'witreq_added_on' => t_lang('M_WR_ADDED_ON'),
    'witreq_updated_on' => t_lang('M_WR_UPDATED_ON'),
    'action' => t_lang('M_WR_ACTION')
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
                        case 'action':
                            if ($row['witreq_status'] == 0) {
                                echo '<ul class="actions center">';
                                echo '<li><a href="javascript:void(0);" onclick="changeStatus(' . $row['witreq_id'] . ',1)"><i class="ion-ios-checkmark icon"></i></a></li>';
                                echo '<li><a href="javascript:void(0);" onclick="changeStatus(' . $row['witreq_id'] . ',2)"><i class="ion-android-close icon"></i></a></li>';
                                echo '</ul>';
                            }
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
<?php require_once './footer.php'; ?>
<script type="text/javascript">
    function changeStatus(id, status) {
        if (confirm('<?php echo t_lang('M_TXT_ARE_YOU_SURE_YOU_WANT_TO_CHANGE_STATUS'); ?>')) {
            callAjax('company-withdraws.php', 'id=' + id + '&status=' + status, function (response) {
                var res = JSON.parse(response);
                alert(res.msg);
                window.location.reload();
            });
        }
    }
</script>