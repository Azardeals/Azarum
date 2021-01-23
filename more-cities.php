<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
require_once './header.php';
if (!isset($_SESSION['city'])) {
    redirectUser(CONF_WEBROOT_URL);
}
$get = getQueryStringData();
$verification_status = (int) $get['s'];
if (isCompanyUserLogged()) {
    redirectUser(CONF_WEBROOT_URL . 'merchant/merchant-account.php');
}
if (isset($verification_status)) {
    if ($verification_status == 1) {
        $msg->addMsg(t_lang('M_MSG_VERIFICATION_SUCCESS'));
    }
}
?>
<section class="pagebar">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-7 col-sm-7">
                <h3><?php echo t_lang('M_TXT_MORE_CITIES'); ?></h3>
                <ul class="breadcrumb">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL); ?>"><?php echo t_lang('M_TXT_HOME'); ?></a></li>
                    <li><?php echo t_lang('M_TXT_MORE_CITIES'); ?></li>
                </ul>
            </aside>
        </div>
    </div>
</section> 
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <div class="col-md-12">
                <div class="container__bordered">
                    <div class="cover__grey">
                        <div class="formwrap">
                            <div class="siteForm">
                                <table class="formwrap__table">
                                    <tr>
                                        <td colspan="2">
                                            <div class="cover__search">
                                                <input type="text" class="filer_field fl" name="city_name"  onkeyup="return serachCity(this.value);" placeholder ="<?php echo t_lang('M_TXT_SEARCH_BY_CITY'); ?>">
                                            </div>    
                                        </td>
                                    </tr>
                                </table>
                            </div> 
                        </div>
                    </div>
                    <div class="space">
                        <h2 class="section__subtitle"><?php echo t_lang('M_TXT_MORE_CITIES'); ?></h2>
                        <div class="grids" id="displayStates">
                            <?php
                            $srch = new SearchBase('tbl_cities', 'c');
                            $srch->addCondition('city_active', '=', 1);
                            $srch->addCondition('city_deleted', '=', 0);
                            $srch->addCondition('city_request', '=', 0);
                            $srch->joinTable('tbl_states', 'INNER JOIN', 'c.city_state = s.state_id', 's');
                            $srch->addOrder('s.state_name');
                            $srch->addOrder('c.city_name');
                            $srch->addGroupBy('s.state_id');
                            $srch->doNotLimitRecords();
                            $srch->doNotCalculateRecords();
                            $rs = $srch->getResultSet();
                            $count = 0;
                            while ($row = $db->fetch($rs)) {
                                $count++;
                                echo '<div class="grids__item"><div class="grids__list"><div class="grids__head">' . $row['state_name' . $_SESSION['lang_fld_prefix']] . '</div><div class="grids__body"><ul class="list__vertical links">';
                                $srch = new SearchBase('tbl_deals', 'd');
                                $srch->addCondition('deal_status', '=', 1);
                                $srch->addCondition('deal_deleted', '=', 0);
                                $srch->addCondition('deal_complete', '=', 1);
                                $srch->addMultipleFields(array('deal_city', 'count(deal_id) as total'));
                                $srch->addGroupBy('deal_city');
                                $srch->doNotLimitRecords();
                                $srch->doNotCalculateRecords();
                                $qry_num_deals = $srch->getQuery();
                                $srch = new SearchBase('tbl_cities', 'c');
                                $srch->addCondition('city_active', '=', 1);
                                $srch->addCondition('city_deleted', '=', 0);
                                $srch->addCondition('city_request', '=', 0);
                                $srch->addCondition('city_state', '=', $row['state_id']);
                                $srch->addOrder('c.city_name');
                                $srch->addMultipleFields(array('city_id', 'city_name' . $_SESSION['lang_fld_prefix'], 'IF(qd.total >0, qd.total, "0" ) as total'));
                                $srch->joinTable('(' . $qry_num_deals . ')', 'LEFT JOIN', 'qd.deal_city = c.city_id', 'qd');
                                $srch->doNotLimitRecords();
                                $srch->doNotCalculateRecords();
                                $rs1 = $srch->getResultSet();
                                $countCity = 0;
                                while ($row1 = $db->fetch($rs1)) {
                                    $countCity++;
                                    $total = '';
                                    if ($row1['total'] > 0) {
                                        $total = "(" . $row1['total'] . ")";
                                    }
                                    if ($countCity % 3 == 0) {
                                        $classCity = 'class="nomarg_Right"';
                                    } else {
                                        $classCity = '';
                                    }
                                    echo '<li ' . $classCity . '><a href="javascript:void(0);" onclick="selectCity(' . $row1['city_id'] . ',' . CONF_FRIENDLY_URL . ');">' . $row1['city_name' . $_SESSION['lang_fld_prefix']] . " " . $total . '</a></li>';
                                }
                                echo'</ul></div></div></div>';
                            }
                            ?>
                        </div>
                    </div>
                    </section>
                    <section class="page__container">
                        <div class="fixed_container">
                            <div class="row">
                                <form name="page_search" id="page_search" method='post'>
                                    <input type="hidden" name="pagename" value="<?php echo $pagename; ?>">
                                </form>  
                                <div class="col-md-12">
                                    <?php echo '<h3>' . t_lang('M_TXT_DEALS') . '  ' . t_lang('M_TXT_IN') . "  " . t_lang("M_TXT_OTHER_CITY") . ' </h3>'; ?>
                                </div> 
                                <div class="container-fluid">
                                    <!--items list start here-->
                                    <div class="dealsContainer">
                                    </div>
                                    <!--items list end here-->
                                </div>
                            </div>
                        </div>
                    </section>
                    <script src="<?php echo CONF_WEBROOT_URL; ?>js/masonry.pkgd.js"></script>  
                    <script type="text/javascript">
                                                    $('.grids').masonry({
                                                        itemSelector: '.grids__item',
                                                    });
                                                    /* for select city form */
                                                    $('.add__newcity-link').click(function () {
                                                        $(this).toggleClass("active");
                                                        $('.citysearch__form').slideToggle("600");
                                                    });
                                                    var dealIds = [];
                                                    $(document).ready(function () {
                                                        getalldeals(1);
                                                    });
                                                    $(window).load(function () {
                                                        $('.paginglink').remove();
                                                    })
                    </script>
                    <?php require_once './footer.php'; ?>
