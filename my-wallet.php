<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
require_once './includes/page-functions/voucher-functions.php';
require_once './header.php';
if (!isUserLogged()) {
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'login.php'));
}
$page = is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$pagesize = 20;
$regSceheme = new SearchBase('tbl_regscheme_offer_log', 'rol');
$regSceheme->addCondition('rofferlog_user_id', '=', $_SESSION['logged_user']['user_id']);
$regSceheme->joinTable('tbl_registration_credit_schemes', 'INNER JOIN', 'regscheme_id=rofferlog_scheme_id', 'rcs');
$rs_listing = $regSceheme->getResultSet();
$sch_records = $regSceheme->recordCount();
$row = $db->fetch($rs_listing);
if ($sch_records > 0) {
    $usergetBonus = $row['regscheme_name'];
    $usergetBonusAmount = $row['regscheme_credit_amount'];
}
$srch = fetchRecordObj($page, $pagesize);
$rs_listing = $srch->getResultSet();
$total_records = $srch->recordCount();
$pages = $srch->pages();
$srch_bal = purchasedhistoryBalanceObj();
if ($page > 1) {
    $new_start_limit = $pagesize * ($page - 1);
    $pagesize = $pagesize * ($pages);
    $new_limit = "limit $new_start_limit,$pagesize ";
} else {
    $new_limit = "";
}
$rs_left_bal = $db->query("SELECT sum(x.wh_amount) as left_balance_onwards from(" . $srch_bal->getQuery() . " $new_limit ) x");
$left_wallet_bal = $db->fetch($rs_left_bal);
$rs = $db->query("select user_wallet_amount from tbl_users where user_id=" . $_SESSION['logged_user']['user_id']);
$row = $db->fetch($rs);
$wallet_amount = $row['user_wallet_amount'];
?>
<!--bodyContainer start here-->
<section class="pagebar">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-7 col-sm-7">
                <h3><?php echo t_lang('M_TXT_PURCHASE_HISTORY') ?></h3>
                <ul class="breadcrumb">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL); ?>"><?php echo t_lang('M_TXT_HOME'); ?></a></li>
                    <li><?php echo t_lang('M_TXT_PURCHASE_HISTORY') ?></li>
                </ul>
            </aside>
        </div>
    </div>
</section> 
<?php
include './left-panel-links.php';
$arr = $db->fetch_all($rs_listing);
$balance = 0;
foreach ($arr as $key => $row) {
    $arr[$key]['balance'] = $left_wallet_bal['left_balance_onwards'];
    $arr[$key]['wh_time'] = displayDate($row['wh_time'], true, true, CONF_TIMEZONE);
    $arr[$key]['added'] = $row['added'];
    $arr[$key]['used'] = $row['used'];
    $left_wallet_bal['left_balance_onwards'] -= $row['wh_amount'];
}
$listserial = ($page - 1) * $pagesize + 1;
$balanceNew = 0;
$count = 0;
?> 
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <div class="col-md-12">
                <h2 class="section__subtitle hide__mobile hide__tab hide__ipad"><?php echo t_lang('M_TXT_PURCHASE_HISTORY'); ?></h2>
                <table class="table__data">
                    <thead>
                        <tr>
                            <th><?php echo t_lang('M_TXT_PARTICULARS'); ?></th>
                            <th><?php echo t_lang('M_TXT_DATE'); ?></th>
                            <th><?php echo t_lang('M_TXT_CREDIT'); ?></th>
                            <th><?php echo t_lang('M_TXT_DEBIT'); ?></th>
                            <th><?php echo t_lang('M_TXT_BALANCE'); ?></th>
                        </tr>
                    </thead>   
                    <tbody>
                        <?php foreach ($arr as $row) { ?>
                            <tr>
                                <td><span class="caption__cell"><?php echo t_lang('M_TXT_PARTICULARS'); ?></span><strong><?php echo convertLangTextToProperText($row['wh_particulars']); ?></strong></td>
                                <td><span class="caption__cell"><?php echo t_lang('M_TXT_DATE'); ?></span><?php echo $row['wh_time']; ?></td>
                                <td><span class="caption__cell"><?php echo t_lang('M_TXT_CREDIT'); ?></span><?php echo amount($row['added']); ?></td>
                                <td><span class="caption__cell"><?php echo t_lang('M_TXT_DEBIT'); ?></span><?php echo amount($row['used']); ?></td>
                                <td><span class="caption__cell"><?php echo t_lang('M_TXT_BALANCE'); ?></span><?php echo amount($row['balance']); ?></td>
                            </tr>
                            <?php
                        }
                        if ($total_records == 0) {
                            echo ' <tr> <td colspan=5>' . t_lang("M_TXT_NO_RECORD_FOUND") . '</td></tr>';
                        }
                        ?>
                    </tbody>   
                </table>
                <?php
                if ($total_records > 0) {
                    $pagesize = 20;
                    $pages = $srch->pages();
                    if ($pages > 1) {
                        echo createHiddenFormFromPost('frmPaging', '', array('page'), array('page' => ''));
                    }
                    require_once CONF_VIEW_PATH . 'pagination.php';
                }
                ?>
            </div>    
        </div>
    </div>    
</section>
<!--bodyContainer end here-->
<?php require_once './footer.php'; ?>