<?php
require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
if (!isCompanyUserLogged()) {
    redirectUser(CONF_WEBROOT_URL . 'merchant/login.php');
}
$deal_id = $_GET['deal_id'];
require_once './header.php';
?>
<!--body start here-->
<div class="tblheading"><?php echo t_lang('M_TXT_COMPANY_AMOUNT'); ?></div>
<ul class="tabs">
    <li > <a href="<?php echo (CONF_WEBROOT_URL . 'merchant/tipped-members.php?deal_id=' . $_GET['deal_id'] . '&status=paid&page=1' ) ?>" <?php echo $tabClass; ?>><?php echo t_lang('M_TXT_PAID'); ?></a></li>
    <li><a href="<?php echo (CONF_WEBROOT_URL . 'merchant/tipped-members.php?deal_id=' . $_GET['deal_id'] . '&status=pending&page=1' ) ?>" <?php echo $class; ?>><?php echo t_lang('M_TXT_PENDING'); ?></a></li>
    <li ><a href="<?php echo CONF_WEBROOT_URL . 'merchant/tipped-members.php?mode=downloadcsv&deal_id=' . $_GET['deal_id']; ?>" ><?php echo t_lang('M_TXT_DOWNLOAD_XLS'); ?></a></li>
    <li><a href="<?php echo (CONF_WEBROOT_URL . 'merchant/company-amount.php?deal_id=' . $_GET['deal_id']); ?>" class="active" ><?php echo t_lang('M_TXT_AMOUNTS'); ?></a></li>
    <li><a href="<?php echo (CONF_WEBROOT_URL . 'merchant/company-deals.php'); ?>" ><?php echo t_lang('M_TXT_DEALS'); ?></a></li>
    <li ><a href="<?php echo (CONF_WEBROOT_URL . 'merchant/merchant-account.php'); ?>" ><?php echo t_lang('M_TXT_MY_ACCOUNT'); ?></a></li>
</ul>
<div style="clear:both;"></div>
<?php echo $msg->display(); ?>
<div class="form"> <table class="tbl_forms" width="100%">
        <thead>
            <?php
            $objDeal = new DealInfo($_GET['deal_id']);
            echo '<tr>
                <th><strong>' . t_lang('M_TXT_SALE_SUMMARY_OF') . ' "' . $objDeal->getFldValue('deal_name') . '"</strong><br/></th>
               </tr>
               </thead>
               <tbody>
               <tr><td>';
            echo t_lang('M_TXT_SOLD_COUPONS') . ': ' . $objDeal->getFldValue('sold') . '<br/>';
            echo t_lang('M_TXT_DEAL_PRICE') . ': ' . $objDeal->getFldValue('price') . '<br/>';
            echo t_lang('M_TXT_SALE_AMOUNT') . ': ' . number_format($objDeal->getFldValue('price') * $objDeal->getFldValue('sold'), 2) . '<br/>';
            $commission = $objDeal->getFldValue('sold') * $objDeal->getFldValue('price') * $objDeal->getFldValue('deal_commission_percent') / 100;
            echo t_lang('M_FRM_COMMISSION') . ' @ ' . $objDeal->getFldValue('deal_commission_percent') . '%: ' . number_format($commission, 2) . '<br/>';
            echo t_lang('M_FRM_BONUS') . ': ' . $objDeal->getFldValue('deal_bonus') . '<br/>';
            echo t_lang('M_TXT_TOTAL_EARNING') . ': ' . number_format($commission + $objDeal->getFldValue('deal_bonus'), 2) . '<br/>';
            $srch = new SearchBase('tbl_deals', 'd');
            $srch->addCondition('deal_deleted', '=', 0);
            $srch->addCondition('deal_id', '=', $_GET['deal_id']);
            $srch->joinTable('tbl_companies', 'INNER JOIN',
                    'd.deal_company=company.company_id', 'company');
            $srch->joinTable('tbl_company_charity', 'INNER JOIN',
                    'company.company_id=charity.charity_company_id', 'charity');
            $srch->addMultipleFields(array('charity.charity_percentage'));
            $rs_listing = $srch->getResultSet();
            $row = $db->fetch($rs_listing);
            $charity_percentage = $row['charity_percentage'];
            if ($charity_percentage > 0) {
                $payAbleToMerchant = number_format($objDeal->getFldValue('sold') * $objDeal->getFldValue('price') - $commission - $objDeal->getFldValue('deal_bonus'), 2);
                echo t_lang('M_TXT_CHARITY') . ' @ ' . $charity_percentage . '<br/>';
                echo t_lang('M_TXT_PAY_TO_CHARITY') . ':&nbsp;';
                if ((($payAbleToMerchant * $charity_percentage) / 100) > 0) {
                    echo ( (($payAbleToMerchant * $charity_percentage) / 100));
                } else {
                    echo '0.00';
                }
                echo '<br/>';
                if ($payAbleToMerchant > 0) {
                    echo t_lang('M_TXT_PAYABLE_TO_MERCHANT') . ':&nbsp;';
                    if (($payAbleToMerchant - (($payAbleToMerchant * $charity_percentage) / 100)) > 0) {
                        echo ($payAbleToMerchant - (($payAbleToMerchant * $charity_percentage) / 100));
                    } else {
                        echo '0.00';
                    }
                    echo '<br/>';
                } else {
                    echo t_lang('M_TXT_PAYABLE_TO_MERCHANT') . ':&nbsp;';
                    if (($payAbleToMerchant + (($payAbleToMerchant * $charity_percentage) / 100)) > 0) {
                        echo ($payAbleToMerchant + (($payAbleToMerchant * $charity_percentage) / 100));
                    } else {
                        echo '0.00';
                    }
                    echo '<br/>';
                }
            } else {
                echo t_lang('M_TXT_PAYABLE_TO_MERCHANT') . ':&nbsp; ';
                if (number_format($objDeal->getFldValue('sold') * $objDeal->getFldValue('price') - $commission - $objDeal->getFldValue('deal_bonus'), 2) > 0) {
                    echo number_format($objDeal->getFldValue('sold') * $objDeal->getFldValue('price') - $commission - $objDeal->getFldValue('deal_bonus'), 2);
                } else {
                    echo '0.00';
                }
                echo '<br/>';
            }
            $tipped_at = displayDate($objDeal->getFldValue('deal_tipped_at'), true);
            if ($tipped_at == '') {
                echo '<div style="color: #f00;">' . t_lang('M_TXT_DEAL_IS_NOT_TIPPED_YET') . ($objDeal->getFldValue('deal_min_coupons') - $objDeal->getFldValue('sold')) . t_lang('M_TXT_MORE_TO_BE_SOLD') . '</div>';
            } else {
                echo '<br/>' . t_lang('M_TXT_TIPPED_AT') . ': ' . $tipped_at;
            }
            ?>
            </td></tr>
            </tbody>
    </table>  
</div> 
<div class="clear"></div>
<?php
require_once './footer.php';

