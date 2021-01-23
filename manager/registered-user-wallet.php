<?php
require_once './application-top.php';
require_once '../includes/navigation-functions.php';
checkAdminPermission(3);
$page = (is_numeric($_GET['page']) ? $_GET['page'] : 1);
if (!isset($_GET['user']) && intval($_GET['user']) <= 0) {
    redirectUser('registered-members.php');
}
$srch_user = new SearchBase('tbl_users');
$srch_user->addCondition('user_id', '=', intval($_GET['user']));
$srch_user->addFld('user_wallet_amount');
$srch_user->addFld('user_name');
$srch_user->addFld('user_lname');
$srch_user->addFld('user_email');
$su_rs = $srch_user->getResultSet();
if (!($wallet_user_data = $db->fetch($su_rs))) {
    $msg->addError("User details not found.");
    redirectUser('registered-members.php');
}
$srch = new SearchBase('tbl_user_wallet_history', 'wh');
$srch->addCondition('wh_user_id', '=', intval($_GET['user']));
$srch->addOrder('wh_time', 'asc');
$srch->addFld('CASE WHEN wh_amount > 0 THEN wh_amount ELSE 0 END as added');
$srch->addFld('CASE WHEN wh_amount <= 0 THEN ABS(wh_amount) ELSE 0 END as used');
$srch->addFld('wh.*');
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
$arr_listing_fields = [
    'listserial' => t_lang('M_TXT_S_N'),
    'wh_time' => t_lang('M_TXT_DATE'),
    'wh_particulars' => t_lang('M_TXT_PARTICULARS'),
    'added' => t_lang('M_TXT_CREDIT'),
    'used' => t_lang('M_TXT_DEBIT'),
    'balance' => t_lang('M_TXT_BALANCE'),
];
require_once './header.php';
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'registered-members.php' => t_lang('M_TXT_REGISTERED_USERS'),
    '' => t_lang('M_TXT_DEAL_BALANCE')
];
?>
<script language="javascript">
    var txtoops = "<?php echo addslashes(t_lang('M_TXT_INTERNAL_ERROR')); ?>";
    var txtreload = "<?php echo addslashes(t_lang('M_TXT_PLEASE_RELOAD_PAGE_AND_TRY_AGAIN')); ?>";
</script>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_ACCOUNT_STATEMENT') . ': ' . $wallet_user_data['user_name'] . ' ' . $wallet_user_data['user_lname'] . ' (' . $wallet_user_data['user_email'] . ') ' . t_lang('M_TXT_BALANCE') . ': ' . CONF_CURRENCY . $wallet_user_data['user_wallet_amount'] . CONF_CURRENCY_RIGHT; ?>
            <ul class="actions right">
                <li class="droplink">
                    <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                    <div class="dropwrap">
                        <ul class="linksvertical">
                            <li><a href="javascript:void(0);" onclick="userUpdateWallet(<?php echo intval($_GET['user']); ?>)" alt=""><?php echo t_lang('M_TXT_ADD_TRANSACTION'); ?></a>	</li>
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
            $arr = $db->fetch_all($rs_listing);
            $balance = 0;
            foreach ($arr as $key => $row) {
                $balance += $row['wh_amount'];
                $arr[$key]['wh_time'] = displayDate($row['wh_time'], true, true, CONF_TIMEZONE);
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
                        case 'wh_time':
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
                        case 'wh_particulars':
                            echo convertLangTextToProperText($row['wh_particulars']);
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
