<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
require_once './includes/page-functions/deal-functions.php';
if (!isUserLogged()) {
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'login.php'));
}
require_once './header.php';
$page = is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$pagesize = 8;
$get = getQueryStringData();
$srch = fetchFavoriteDealList($page, $pagesize);
$rs_deal_list = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
if ($pages > 1) {
    $pagestring .= '<ul class="paging fr">';
    $merchantUrl = CONF_WEBROOT_URL . 'my-favorites-deals.php?page=xxpagexx';
    $pagestring .= '<li>' . t_lang('M_TXT_GOTO') . '</li>' . getPageString('<li><a href="' . friendlyUrl($merchantUrl) . '">xxpagexx</a> </li> ', $srch->pages(), $page, '<li ><a class="pagingActive" href="javascript:void(0);">xxpagexx</a></li>', ' ');
    $pagestring .= '</ul><div class="gap"></div>';
}
$total_records = $srch->recordCount();
?>
<!--bodyContainer start here-->
<section class="pagebar">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-7 col-sm-7">
                <h3><?php echo t_lang('M_TXT_FAVOURITE_DEALS_PRODUCTS'); ?></h3>
                <ul class="breadcrumb">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL); ?>"><?php echo t_lang('M_TXT_HOME'); ?></a></li>
                    <li><?php echo t_lang('M_TXT_FAVOURITE_DEALS_PRODUCTS'); ?></li>
                </ul>
            </aside>
        </div>
    </div>
</section> 
<?php include './left-panel-links.php'; ?> 
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <div class="col-sm-12">
                <h2 class="section__subtitle hide__mobile hide__tab hide__ipad"><?php echo t_lang('M_TXT_FAVOURITE_DEALS_PRODUCTS'); ?></h2>
                <div class="listing__items">
                    <div class="row">
                        <?php
                        if ($total_records <= 0) {
                            /*  $msg->addError( t_lang('M_MSG_NO_FAVOURITE_DEALS_FOUND')); */
                            echo '<div class="col-md-3 col-xs-6"><h6>' . t_lang("M_TXT_NO_RECORD_FOUND") . '</h6></div>';
                        } else {
                            while ($row = $db->fetch($rs_deal_list)) {
                                $deal_id_arr[] = $row['deal_id'];
                                $objDeal = new DealInfo($row['deal_id']);
                                if ($objDeal->getError() != '') {
                                    $query = "delete from tbl_users_favorite_deals where user_id =" . $_SESSION['logged_user']['user_id'] . " and deal_id=" . $row['deal_id'];
                                    $db->query($query);
                                    $msg->addMsg('Invalid Request.');
                                    Continue;
                                } else {
                                    $deal = $objDeal->getFields();
                                    $array = array('deal' => $deal, 'searchtype' => 'notRequired');
                                    echo '<div class="col-md-3 col-xs-6">';
                                    echo renderDealView('deal.php', $array);
                                    echo ' </div >';
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
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
 
