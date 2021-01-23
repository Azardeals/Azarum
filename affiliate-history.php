<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
if (!isAffiliateUserLogged()) {
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'affiliate-login.php'));
}
require_once './header.php';
$pagesize = 500;
$affiliate_id = (int) $_SESSION['logged_user']['affiliate_id'];
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$post = getPostedData();
$rs2 = $db->query("select (sum(wh_amount)) as totalamount from tbl_affiliate_wallet_history where   wh_affiliate_id=" . $affiliate_id);
$rowaffiliate2 = $db->fetch($rs2);
$srch = new SearchBase('tbl_affiliate_wallet_history', 'wh');
$srch->addCondition('wh_affiliate_id', '=', $affiliate_id);
$srch->addFld('wh.*');
$srch->addFld('CASE WHEN wh_amount > 0 THEN wh_amount ELSE 0 END as added');
$srch->addFld('CASE WHEN wh_amount <= 0 THEN ABS(wh_amount) ELSE 0 END as used');
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
$arr_listing_fields = [
    'listserial' => t_lang('M_TXT_S_N'),
    'wh_particulars' => t_lang('M_TXT_PARTICULARS'),
    'added' => t_lang('M_TXT_CREDIT'),
    'used' => t_lang('M_TXT_DEBIT'),
    'balance' => t_lang('M_TXT_BALANCE'),
    'wh_time' => t_lang('M_TXT_DATE')
];
if (isset($verification_status) && $verification_status > 0) {
    if ($verification_status == 1) {
        $msg->addMsg(t_lang('M_TXT_UPDATE_YOUR_PASSWORD'));
    }
}
$arr = $db->fetch_all($rs_listing);
$balance = 0;
foreach ($arr as $key => $row) {
    $balance += $row['wh_amount'];
    $arr[$key]['wh_time'] = displayDate($row['wh_time'], true, false, '');
    $arr[$key]['added'] = $row['added'];
    $arr[$key]['used'] = $row['used'];
    $arr[$key]['balance'] = $balance;
}
$arr = array_reverse($arr);
?>
<section class="pagebar">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-7 col-sm-7">
                <h3><?php echo t_lang('M_TXT_AFFILIATE_BALANCE'); ?></h3>
                <ul class="breadcrumb">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL); ?>"><?php echo t_lang('M_TXT_HOME'); ?></a></li>
                    <li><?php echo t_lang('M_TXT_AFFILIATE_BALANCE'); ?></li>
                </ul>
            </aside>
        </div>
    </div>
</section> 
<?php require_once './left-panel-links.php'; ?>
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <div class="col-md-12">
                <h2 class="section__subtitle hide__mobile hide__tab hide__ipad"><?php echo t_lang('M_TXT_AFFILIATE_BALANCE'); ?></h2>
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
                        <?php
                        if (!empty($arr)) {
                            foreach ($arr as $row) {
                                ?>
                                <tr>
                                    <td><span class="caption__cell"><?php echo t_lang('M_TXT_PARTICULARS'); ?></span><strong><?php echo convertLangTextToProperText($row['wh_particulars']); ?></strong></td>
                                    <td><span class="caption__cell"><?php echo t_lang('M_TXT_DATE'); ?></span><?php echo $row['wh_time']; ?></td>
                                    <td><span class="caption__cell"><?php echo t_lang('M_TXT_CREDIT'); ?></span><?php echo amount($row['added']); ?></td>
                                    <td><span class="caption__cell"><?php echo t_lang('M_TXT_DEBIT'); ?></span><?php echo amount($row['used']); ?></td>
                                    <td><span class="caption__cell"><?php echo t_lang('M_TXT_BALANCE'); ?></span><?php echo amount($row['balance']); ?></td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="5">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
                <?php
                $pages = $srch->pages();
                if ($pages > 1) {
                    echo createHiddenFormFromPost('frmPaging', '', array('page'), array('page' => ''));
                    $total_records = $srch->recordCount();
                    require_once CONF_VIEW_PATH . 'pagination.php';
                }
                ?>
            </div>    
        </div>
    </div>    
</section>
<?php
require_once './footer.php';
