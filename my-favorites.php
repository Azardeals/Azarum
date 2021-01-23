<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
require_once './includes/page-functions/merchant-functions.php';
if (!isUserLogged()) {
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'login.php'));
}
require_once './header.php';
$page = is_numeric($_POST['page']) ? $_POST['page'] : 1;
$pagesize = 6;
$get = getQueryStringData();
$srch = fetchMerchantFavoriteList($page, $pagesize);
$rs_listing = $srch->getResultSet();
$pages = $srch->pages();
$total_records = $srch->recordCount();
//
?>
<!--bodyContainer start here-->
<section class="pagebar">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-7 col-sm-7">
                <h3><?php echo t_lang('M_TXT_FAVOURITE_MERCHANT'); ?></h3>
                <ul class="breadcrumb">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL); ?>"><?php echo t_lang('M_TXT_HOME'); ?></a></li>
                    <li><?php echo t_lang('M_TXT_FAVOURITE_MERCHANT'); ?></li>
                </ul>
            </aside>
        </div>
    </div>
</section> 
<?php require_once './left-panel-links.php'; ?> 
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <div class="col-sm-12">
                <h2 class="section__subtitle hide__mobile hide__tab hide__ipad"><?php echo t_lang('M_TXT_FAVOURITE_MERCHANT'); ?></h2>
                <ul class="grid__onethird">
                    <?php
                    if ($total_records <= 0) {
                        /* $msg->addError( t_lang('M_MSG_NO_FAVOURITE_MERCHANT_FOUND')); */
                        echo ' <li><h6>' . t_lang("M_TXT_NO_RECORD_FOUND") . '</h6></li>';
                    } else {
                        while ($companyrow = $db->fetch($rs_listing)) {
                            ?>
                            <li>
                                <?php
                                $deal = array('companyrow' => $companyrow);
                                echo renderDealView('merchant.php', $deal);
                                ?>
                            </li>
                            <?php
                        }
                    }
                    ?>
                </ul>
                <span class="gap"></span>
                <?php
                if ($pages > 1) {
                    echo createHiddenFormFromPost('frmPaging', '', array('page'), array('page' => ''));
                    require_once CONF_VIEW_PATH . 'pagination.php';
                }
                ?>
            </div>
        </div>
    </div>    
</section>
<!--bodyContainer end here-->
<?php require_once './footer.php'; ?>
