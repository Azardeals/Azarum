<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
if (!isset($_SESSION['city'])) {
    redirectUser(CONF_WEBROOT_URL);
}
$get = getQueryStringData();
$verification_status = isset($get['s']) ? $get['s'] : '';
if ($verification_status == 1) {
    $msg->addMsg(t_lang('M_MSG_VERIFICATION_SUCCESS'));
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'all-deals.php'));
}
require_once './header.php';
?>
<!--bodyContainer start here-->
<section class="pagebar">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-7 col-sm-7">
                <h3> <?php echo t_lang('M_TXT_DEALS_LIST'); ?></h3>
                <ul class="breadcrumb">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL); ?>"><?php echo t_lang('M_TXT_HOME'); ?></a></li>
                    <li><?php echo ucwords($pagename); ?></li>
                </ul>
            </aside>
            <aside class="col-md-5 col-sm-5 side">
                <?php require_once CONF_VIEW_PATH . 'sort-filter-menu.php'; ?>
            </aside>
        </div>
    </div>
</section> 
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <?php require_once CONF_VIEW_PATH . 'left-filter-menu.php'; ?>
            <aside class="col-md-9">
                <div class="row__filter right_bar" style="display:none;">
                    <div class="row" >
                        <aside class="col-md-9 col-sm-9">
                            <ul class="tags__filter" id="filter" >
                                <li><?php echo t_lang('M_TXT_SHOW'); ?></li>
                            </ul>
                        </aside>
                        <aside class="col-md-3 col-sm-3 alignright">
                            <ul class="tags__filter">
                                <li class="clear" id="allfilter"><a href="javascript:void(0);" onclick="removeFilter(this)" ><?php echo t_lang('M_TXT_CLEAR_ALL'); ?></a></li>
                            </ul>
                        </aside>
                    </div>
                </div>
                <!--items list start here-->
                <div class="dealsContainer">
                </div>
                <!--items list end here-->
            </aside>
        </div>
    </div>    
</section>
<!--bodyContainer end here-->
<script type="text/javascript">
    $(document).ready(function () {
        getalldeals(1);
        $('#topcontrol').fadeOut();
    });
</script>
<?php
require_once './footer.php';
