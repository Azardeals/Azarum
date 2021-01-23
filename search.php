<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
require_once './header.php';
if (!isset($_SESSION['city'])) {
    redirectUser(CONF_WEBROOT_URL);
}
require_once './header.php';
$name = trim(urldecode($_REQUEST['q']));
$cat = trim(urldecode($_REQUEST['cat']));
if (($name == "") && ($cat == "")) {
    redirectUser(CONF_WEBROOT_URL . 'all-deals.php');
}
$dealArrCat = [];
if (!empty($name) && empty($cat)) {
    $srch = new SearchBase('tbl_deal_to_category', 'dtc');
    $srch->joinTable('tbl_deals', 'INNER JOIN', 'dtc.dc_deal_id=d.deal_id ', 'd');
    $srch->addCondition('d.deal_name' . $_SESSION["lang_fld_prefix"], 'like', '%' . $name . '%');
    $rs = $srch->getResultSet();
    if ($srch->recordCount() > 0) {
        ?>
        <script type="text/javascript">
            $(document).ready(function () {
                showDiv(1, "<?php echo urldecode($name); ?>", '', 'deal');
                showForOther(1, "<?php echo urldecode($name); ?>", '', 'deal');
            })
        </script>
        <?php
    } else {
        $flag = true;
    }
}
if (!empty($cat)) {
    $srch = new SearchBase('tbl_deal_categories', 'dc');
    $srch->addCondition('dc.cat_name' . $_SESSION["lang_fld_prefix"], 'like', $cat . '%');
    $rs = $srch->getResultSet();
    if ($srch->recordCount() > 0) {
        ?>
        <script type="text/javascript">
            $(document).ready(function () {
                showDiv(1, "<?php echo urldecode($name); ?>", "<?php echo urldecode($cat); ?>", 'cat');
                showForOther(1, "<?php echo urldecode($name); ?>", "<?php echo urldecode($cat); ?>", 'cat');
            }
            )
        </script>
        <?php
    } else {
        $flag1 = true;
    }
}
?>
<section class="pagebar">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-7 col-sm-7">
                <h3> <?php echo t_lang('M_TXT_DEALS_LIST'); ?></h3>
            </aside>
        </div>
    </div>
</section> 
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <div class="container-fluid">
                <!--items list start here-->
                <!--items list start here-->
                <?php echo '<h5>' . t_lang('M_TXT_SEARCH_RESULT_FOR') . ' " ' . htmlentities($name) . ' " ' . t_lang('M_TXT_IN') . "  " . $_SESSION['city_to_show'] . ' </h5>'; ?>
                <div class="dealsContainer" id="currentCity">
                    <?php
                    if ($flag) {
                        echo'<div class="col-md-12 "><div class="alert alert_info">' . unescape_attr(sprintf(t_lang(M_TXT_SORRY_NO_DEAL), '', $_SESSION['city_to_show'])) . '<a class="tobeContinue" href="' . CONF_WEBROOT_URL . '">  ' . t_lang('M_TXT_Continue_browsing_other_deals...') . '</a></div>';
                    }
                    ?>
                </div>
                <!-- for other City -->
                <?php echo '<h5>' . t_lang('M_TXT_SEARCH_RESULT_FOR') . ' " ' . htmlentities($name) . ' " ' . t_lang('M_TXT_IN') . "  " . t_lang("M_TXT_OTHER_CITY") . ' </h5>'; ?>
                <div class="dealsContainer" id="otherCity">
                    <?php
                    if ($flag1) {
                        echo'<div class="col-md-12 "><div class="alert alert_info">' . unescape_attr(sprintf(t_lang(M_TXT_SORRY_NO_DEAL), '', $_SESSION['city_to_show'])) . '<a class="tobeContinue" href="' . CONF_WEBROOT_URL . '">  ' . t_lang('M_TXT_Continue_browsing_other_deals...') . '</a></div>';
                    }
                    ?>
                </div>
                <!--items list end here-->
            </div>
        </div>
    </div>    
</section>
<!--bodyContainer end here-->
<!--bodyContainer end here-->
<script type="text/javascript">
    var dealIds = [];
    $(document).ready(function () {
        $('.tabLink').click(function () {
            $(this).toggleClass("active");
            $('.sectiondowncontainer').slideToggle("600");
            return false;
        });
        $('html').click(function () {
            $('.sectiondowncontainer').slideUp('slow');
            if ($('.tabLink').hasClass('active')) {
                $('.tabLink').removeClass('active');
            }
        });
    });
    function showDiv(page, deal, cat, forwhat) {
        ShowLoder($('#currentCity'));
        callAjax(webroot + 'common-ajax.php', 'mode=searchList&deal=' + deal + '&cat=' + cat + '&page=' + page + '&type=' + forwhat, function (t) {
            var ans = parseJsonData(t);
            $('#currentCity').html(ans.msg['html']);
            if (ans.msg['dealIds']) {
                dealIds = dealIds.concat(ans.msg['dealIds']);
                fetchdealids(dealIds);
            }
        });
    }
    function showForOther(page, deal, cat, forwhat) {
        ShowLoder($('#otherCity'));
        callAjax(webroot + 'common-ajax.php', 'mode=SEARCHLISTFOROTHER&deal=' + deal + '&cat=' + cat + '&page=' + page + '&type=' + forwhat, function (t) {
            var ans = parseJsonData(t);
            $('#otherCity').html(ans.msg['html']);
            if (ans.msg['dealIds']) {
                dealIds = dealIds.concat(ans.msg['dealIds']);
                fetchdealids(dealIds);
            }
        });
    }
</script>
<?php require_once './footer.php'; ?>
