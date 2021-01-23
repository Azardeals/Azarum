<?php
require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
if (!isRepresentativeUserLogged())
    redirectUser(CONF_WEBROOT_URL . 'representative/login.php');
$rep_id = $_SESSION['logged_user']['rep_id'];

$page = (is_numeric($_GET['page']) ? $_GET['page'] : 1);

$post = getPostedData();

$srch = new SearchBase('tbl_representative_wallet_history', 'rwh');
$srch->addCondition('rwh_rep_id', '=', $rep_id);

$srch->addFld('rwh.*');
$srch->addFld('CASE WHEN rwh_amount > 0 THEN rwh_amount ELSE 0 END as added');
$srch->addFld('CASE WHEN rwh_amount <= 0 THEN ABS(rwh_amount) ELSE 0 END as used');
$srch->addOrder('rwh_time', 'asc');
/* $srch->setPageNumber($page);
  $srch->setPageSize($pagesize); */
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();


$arr_listing_fields = array(
    'listserial' => t_lang('M_TXT_S_N'),
    'rwh_particulars' => t_lang('M_TXT_PARTICULARS'),
    'added' => t_lang('M_TXT_CREDIT'),
    'used' => t_lang('M_TXT_DEBIT'),
    'balance' => t_lang('M_TXT_BALANCE'),
    'rwh_time' => t_lang('M_TXT_DATE')
);

require_once './header.php';
$arr_bread = array(
    'my-account.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    '' => t_lang('M_TXT_TRANSACTION_HISTORY')
);
?>
</div>
</td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
     <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_TRANSACTION_HISTORY'); ?> </div>
       </div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide();
                    return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
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
                    <tr>
                        <?php
                        foreach ($arr_listing_fields as $key => $val)
                            echo '<th' . (($key == 'added' || $key == 'used' || $key == 'rwh_time') ? '  width="15%"' : '') . (($key == 'balance' ) ? '   width="12%"' : '') . '>' . $val . '</th>';
                        ?>
                    </tr>
                    <?php
                    $arr = $db->fetch_all($rs_listing);
                    $balance = 0;
                    foreach ($arr as $key => $row) {
                        $balance +=$row['rwh_amount'];
                        $arr[$key]['rwh_time'] = displayDate($row['rwh_time'], true);
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
                                    echo displayDate($row[$key], true);
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
    


</td>
<?php
require_once './footer.php';
?>
