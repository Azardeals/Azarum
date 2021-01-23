<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
require_once './includes/buy-deal-functions.php';
require_once './includes/page-functions/deal-functions.php';
require_once './site-classes/order.cls.php';
$arr_page_js[] = 'js/jquery.rating.js';
require_once './header.php';
if (!isset($_SESSION['city'])) {
    redirectUser(CONF_WEBROOT_URL);
}
if (isCompanyUserLogged()) {
    redirectUser(CONF_WEBROOT_URL . 'merchant/merchant-account.php');
}
require_once "./update-only-deal-status.php";
$get = getQueryStringData();
$is_logged = isUserLogged();
$arr_page_css[] = 'css/jquery.rating.css';
####### Update deal as main deal if only one exist #############
if ($_SESSION['city'] > 0) {
    if (!updateMainDealRequest($_SESSION['city'], $error)) {
        die($error);
    }
}
if (!is_numeric($get['deal'])) {
    if (!$dealId = fetchMainDealId($_SESSION['city'])) {
        $msg->addMsg(sprintf(unescape_attr(t_lang(M_TXT_SORRY_NO_DEAL)), '', $_SESSION['city_to_show']));
        $url = friendlyUrl(CONF_WEBROOT_URL . 'more-cities.php');
        redirectUser($url);
    } else {
        $deal_id = $dealId;
    }
} else {
    $deal_id = $get['deal'];
}
$objDeal = new DealInfo($deal_id);
$deal = $objDeal->getFields();
$deal_id = $deal_id;
$deal_name = $deal['deal_name'];
$cookiename = explode(" ", $deal_name);
$cName = $cookiename[0];
$deal_type = $deal['deal_type'];
$deal_sub_type = $deal['deal_sub_type'];
if ($objDeal->getError() != '') {
    $msg->addMsg(t_lang('M_ERROR_INVALID_REQUEST'));
    require_once './msgdie.php';
}
/* reviews posted start here */
if ($is_logged) {
    $frm = dealReviewForm($_SESSION['logged_user']['user_id'], $deal_id, $deal['deal_company']);
    ?>
    <?php
    if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['btn_submit_review'])) {
        $post = getPostedData();
        if ($_POST['rating'] == "") {
            $_POST['rating'] = "0";
        }
        $error = '';
        if (dealSaveReview($frm, $post, $error)) {
            $msg->addMsg(t_lang('M_TXT_REVIEW_POSTED'));
            redirectUser();
        } else {
            $msg->addError($error);
            $frm->fill($post);
        }
    }
}
/* reviews posted end here */
$maxBuy = 0;
date_default_timezone_set(CONF_TIMEZONE);
$timenow = strtotime(date('Y-m-d H:i:s'));
?>
<script language="javascript">
    var d = new Date();
    end_time = d.valueOf() +<?php echo((strtotime($deal['deal_end_time']) - $timenow) * 1000); ?>;
</script>
<link rel="stylesheet" type="text/css" href="<?php echo CONF_WEBROOT_URL; ?>css/jquery.rating.css" />
<section class="item__details">
<?php
/* common deal info section  */
$array = array('deal' => $deal, 'deal_id' => $deal_id);
echo renderDealView('main-deal-view.php', $array);
/* common deal info section  */
$reviewsRs = getReviews($deal_id);
?>
</section>

<section class="page__container fulldetails">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-8">
                <h2><?php echo t_lang('M_TXT_DETAILS'); ?></h2>

                <div class="tabspanel">
                    <ul class="tabs__flat normaltabs">
                        <li class="active"><a class="active" rel="tabs_content_1" href="javascript:void(0)"><?php echo t_lang('M_TXT_DESCRIPTION'); ?> </a></li>
                        <li><a rel="tabs_content_2" href="javascript:void(0)"><?php echo t_lang('M_TXT_HIGHLIGHTS'); ?></a></li>
                        <li><a rel="tabs_content_3" href="javascript:void(0)"><?php echo t_lang('M_TXT_FINE_PRINT'); ?> </a></li>
<?php if (CONF_REVIEW_RATING_DEALS == 1) { ?>
                            <li><a rel="tabs_content_4" href="javascript:void(0)" id="reviews"><?php echo t_lang('M_TXT_REVIEWS_AND_RATINGS'); ?></a></li>
                        <?php } ?>
                    </ul>

                    <div class="section__contents container__cms tabspanel__container">

                        <!--tabs1 start here-->
                        <span class="togglehead active" rel="tabs_content_1"><?php echo t_lang('M_TXT_DESCRIPTION'); ?></span>
                        <div id="tabs_content_1" class="tabspanel__content">
<?php if ($objDeal->getFldValue('deal_highlights' . $_SESSION['lang_fld_prefix']) != '') { ?>
                                <div class="borderedwrap hide__mobile">
                                    <h4><?php echo t_lang('M_TXT_HIGHLIGHTS'); ?></h4>
                                    <ul class="">
    <?php
    $data = subStringByWords($objDeal->getFldValue('deal_highlights' . $_SESSION['lang_fld_prefix']), 500);
    $arr_heighlights = explode("\n", $data);
    foreach ($arr_heighlights as $val)
        echo '<p>' . $val . '</p>';
    ?>
                                    </ul>
                                        <?php if (strlen($objDeal->getFldValue('deal_highlights' . $_SESSION['lang_fld_prefix'])) > 100) { ?>  

                                        <a href="javascript:void(0)" rel="tabs_content_2" id="deal_highlights" class="themebtn themebtn--small" ><?php echo t_lang('M_TXT_ALL'); ?></a>
    <?php } ?>
                                </div>
                                <?php } ?>
                            <h4><?php echo t_lang('M_TXT_DESCRIPTION'); ?></h4>
                            <p> <?php
                                if ($deal['deal_desc' . $_SESSION['lang_fld_prefix']] != '') {
                                    echo $deal['deal_desc' . $_SESSION['lang_fld_prefix']];
                                } else {
                                    echo t_lang('M_TXT_NO_CONTENT_FOUND');
                                }
                                ?></p>

                        </div>
                        <!--tabs1 end here-->


                        <!--tabs2 start here-->
                        <span class="togglehead" rel="tabs_content_2"><?php echo t_lang('M_TXT_HIGHLIGHTS'); ?></span>
                        <div id="tabs_content_2" class="tabspanel__content">
                            <h4><?php echo t_lang('M_TXT_HIGHLIGHTS'); ?></h4>
<?php if ($objDeal->getFldValue('deal_highlights' . $_SESSION['lang_fld_prefix']) != '') { ?>
                                <ul class="">
                                <?php
                                $arr_heighlights = explode("\n", $objDeal->getFldValue('deal_highlights' . $_SESSION['lang_fld_prefix']));
                                foreach ($arr_heighlights as $val)
                                    echo '<p>' . $val . '</p>';
                                ?>
                                </ul>
                                    <?php
                                } else {
                                    echo t_lang('M_TXT_NO_CONTENT_FOUND');
                                }
                                ?>
                        </div>
                        <!--tabs2 end here-->


                        <!--tabs3 start here-->
                        <span class="togglehead" rel="tabs_content_3"><?php echo t_lang('M_TXT_FINE_PRINT'); ?></span>
                        <div id="tabs_content_3" class="tabspanel__content">
                            <h4><?php echo t_lang('M_TXT_FINE_PRINT'); ?></h4>
<?php if ($objDeal->getFldValue('deal_fine_print' . $_SESSION['lang_fld_prefix']) != '') { ?>
                                <ul class="listing_bullets">
                                <?php
                                $arr_heighlights = explode("\n", $objDeal->getFldValue('deal_fine_print' . $_SESSION['lang_fld_prefix']));
                                foreach ($arr_heighlights as $val) {
                                    echo '' . $val . '';
                                }
                                ?>
                                </ul>
                                <?php } else echo t_lang('M_TXT_NO_CONTENT_FOUND'); ?>
                        </div>
                        <!--tabs3 end here-->

                        <!--tabs4 start here-->
                        <span class="togglehead" rel="tabs_content_4" ><?php echo t_lang('M_TXT_REVIEWS_AND_RATINGS'); ?></span>
                        <div id="tabs_content_4" class="tabspanel__content" >
<?php
//if (isUserLogged()) { 


if (CONF_REVIEW_RATING_DEALS == 1) {

    if ($reviewsRs->num_rows == 0) {
        ?> 
                                    <div class="block__empty">
                                        <h6><?php echo t_lang('M_TXT_NO_DEAL_REVIEW_FOUND'); ?></h6>
                                        <a href="javascript:void(0);" class="linknormal form__reviews_link"><?php echo t_lang('M_TXT_BE_THE_FIRST_TO_REVIEW_THIS_PRODUCT'); ?></a>
                                    </div>
                                    <span class="gap"></span><span class="gap"></span><span class="gap"></span>
    <?php } else { ?>
                                    <div class="sectiontop__row">
                                        <a class="themebtn themebtn--small right form__reviews_link" href="javascript:void(0);"><?php echo t_lang('M_TXT_WRITE_A_REVIEW'); ?></a>
                                        <h4><?php echo t_lang('M_TXT_REVIEWS_AND_RATINGS'); ?></h4>
                                    </div>    
    <?php } ?>
                                <div class="form__wrap form__reviews" style="display:none;">

    <?php if (isUserLogged()) { ?>
                                        <a href="javascript:void(0)" class="link__close form__reviews_link"></a>
                                        <div class="listrepeated">
                                            <aside class="grid_1">
                                                <figure class="avtar"><?php echo substr($_SESSION['logged_user']['user_name'], 0, 1); ?></figure>
                                            </aside>
                                            <aside class="grid_2">
                                                <h3 class="name"><?php echo htmlentities($_SESSION['logged_user']['user_name']); ?></h3>
        <?php
        if (CONF_POST_REVIEW_RATING_DEALS == 1) {
            $canPostReview = canPostDealReview($deal_id, $_SESSION['logged_user']['user_id']);
            if ($db->total_records($canPostReview) > 0) {
                echo $frm->getFormHtml();
            } else {
                echo t_lang('M_TXT_CANNOT_POST_REVIEWS');
            }
        }
        if (CONF_POST_REVIEW_RATING_DEALS == 0) {
            echo $frm->getFormHtml();
        }
        ?>     
                                            </aside>
                                        </div>
        <?php
    } else {
        echo sprintf(unescape_attr(t_lang('M_TXT_PLEASE_LOGIN_TO_VIEW_REVIEWS')), friendlyUrl(CONF_WEBROOT_URL . 'login.php'));
    }
    ?>
                                </div>

                                <div class="allreviews">

                                    <!--repeated-y -->
    <?php
    while ($reviewsRow = $db->fetch($reviewsRs)) {
        echo '<div class="mainListReviews" style="display: none;">';
        echo '<div class="listrepeated">';
        echo '<aside class="grid_1">
                                            <figure class="avtar">' . substr($reviewsRow['user_name'], 0, 1) . '</figure>
                                        </aside>';
        echo'<aside class="grid_2">
                                            <div class="ratingwrap">
                                                <div class="ratings"><ul>';
        for ($i = 0; $i < $reviewsRow['reviews_rating']; $i++) {
            echo '<li><img src="' . CONF_WEBROOT_URL . 'images/rating-full.png" alt=""></li>';
        }
        for ($j = 0; $j < 5 - $reviewsRow['reviews_rating']; $j++) {
            echo '<li><img src="' . CONF_WEBROOT_URL . 'images/rating-zero.png" alt=""></li>';
        }
        echo '</ul> </div>
                                            </div>
                                            <h3 class="name">' . $reviewsRow['user_name'] . ' ' . $reviewsRow['user_lname'] . ' ' . ' </h3>
                                            <span class="datetxt">' . date("F j, Y  g:i a", strToTime($reviewsRow['reviews_added_on'])) . '</span>
                                            <div class="reviewsdescription">
                                            <p>' . (htmlentities($reviewsRow['reviews_reviews' . $_SESSION['lang_fld_prfix']], ENT_QUOTES, 'UTF-8')) . '</p>
                                            </div>    
                                        </aside></div>';

        $replyRs = $db->query("select * from tbl_reviews as r INNER JOIN tbl_companies as c where c.company_id = r.reviews_deal_company_id and reviews_type=1 AND reviews_approval=1 AND reviews_parent_id=" . $reviewsRow['reviews_id']);
        $replyRow = $db->fetch($replyRs);
        if ($db->total_records($replyRs) > 0) {

            echo '<div class="listrepeated replied">
									<aside class="grid_1">
                                            <figure class="avtar">' . substr($replyRow['company_name'], 0, 1) . '</figure>
                                        </aside>
                                    <aside class="grid_2">
                                            <div class="ratingwrap">
                                                <div class="ratings"><img alt="" src="images/ratings_star.png"></div>
                                            </div>
                                            <h3 class="name">' . $replyRow['company_name'] . ' ' . '</h3>
                                            <span class="datetxt">' . date("F j, Y  g:i a", strToTime($replyRow['reviews_added_on'])) . '</span>
                                            <div class="reviewsdescription">
                                            <p>' . nl2br($replyRow['reviews_reviews']) . ' </p>
                                            </div>    
                                        </aside>
									
									</div>';
        }
        echo '</div>';
    } echo '<div id="loadMore" style="display:none;" class="aligncenter loadmore">
                       <a class="themebtn themebtn--large themebtn--grey" href="javascript:void(0)">Load More</a>
                    </div>';

    echo'</div>';
}
?>

                                <?php
                                /*  } else {
                                  echo sprintf(unescape_attr(t_lang('M_TXT_PLEASE_LOGIN_TO_VIEW_REVIEWS')), friendlyUrl(CONF_WEBROOT_URL . 'login.php'));
                                  } */
                                ?>
                            </div>
                            <!--tabs4 end here-->
                        </div>  
                    </div>  

            </aside>
<?php
$rs = fetchSimilarProducts($deal_id);
if ($rs->num_rows > 0) {
    ?>
                <aside class="col-md-4">
                    <h2><?php echo t_lang('M_TXT_SIMILAR_CATEGORY_ITEM') ?></h2>

                    <div class="items__verticaly">

    <?php
    while ($row = $db->fetch($rs)) {
        $objDeal1 = new DealInfo($row['deal_id']);
        if ($objDeal1->getError() != '') {
            continue;
        }
        $deal = $objDeal1->getFields();
        $deal['header'] = true;
        ?>

                            <?php include CONF_VIEW_PATH . '/deal.php'; ?>

                        <?php } ?>



                    </div>

                </aside>
<?php } ?>
        </div>
    </div>    
</section>

<!-- Spa Section Starts Here --> 
<section class="section section__featured">
    <div class="fixed_container">

<?php echo featuredDeal($deal_id); ?> 

    </div>
</section>

<script type="text/javascript">
    txtsessionexpire = "<?php echo addslashes(t_lang('M_MSG_SESSION_EXPIRE_PLEASE_LOGIN')); ?>";
    var checkin = "<?php echo addslashes(t_lang('M_TXT_Please_select_check_in_date')); ?>";
    var checkout = "<?php echo addslashes(t_lang('M_TXT_Please_select_check_out_date')); ?>";
    var deal_id = "<?php echo $deal_id; ?>";
    var deal_type = "<?php echo(isset($deal_type) ? $deal_type : '') ?>";
    var deal_sub_type = "<?php echo(isset($deal_sub_type) ? $deal_sub_type : '') ?>";


    /* for reviews form */
    $('.form__reviews_link').click(function () {
        $(this).toggleClass("active");
        $('.form__reviews').slideToggle();
    });
    $("#deal_highlights").click(function () {
        $(this).parents('.tabspanel:first').find(".tabspanel__content").hide();
        var activeTab = $(this).attr("rel");
        console.log(".normaltabs li a[rel='" + activeTab + "']");
        $("#" + activeTab).fadeIn();

        $(this).parents('.tabspanel:first').find(".normaltabs li a").removeClass("active");
        $(this).addClass("active");
        $(".togglehead").removeClass("active");
        $(".togglehead[href^='" + activeTab + "']").addClass("active");
        $(".normaltabs li a[rel='" + activeTab + "']").addClass('active');
        return false;
    });
    var txtreload = "<?php echo addslashes(t_lang('M_JS_PLEASE_RELOAD_AND_TRY')); ?>";

    $(document).ready(function () {
        $(".first").trigger('click');
    });
    $(document).ready(function () {
        /** Code of Review load more using Jquery **/
        $(".mainListReviews").slice(0, 5).show();
        if ($(".mainListReviews:hidden").length > 5) {
            $("#loadMore").show();
        }
        $("#loadMore").on('click', function (e) {
            e.preventDefault();
            $(".mainListReviews:hidden").slice(0, 5).slideDown();
            if ($(".mainListReviews:hidden").length == 0) {
                $("#loadMore").fadeOut('fast');
            }
        });
        /** End Code of Review load more using Jquery **/
        $(".first").trigger('click');
    });

    var dealIds = [];
    function getFeaturedDeals(catId) {
        data = 'category=' + catId;
        data += '&mode=pageSearch&pagesize=4&pagename=home';
        callAjax(webroot + 'common-ajax.php', data, function (t) {
            var ans = parseJsonData(t);
            $('.paginglink').remove();
            // $('.dealsContainer').append(ans.msg);
            dealIds = [];
            $('.dealsContainer').html(ans.msg['html']);
            dealIds = dealIds.concat(ans.msg['dealIds']);
            $('.paginglink').remove();
        });
    }
</script> 
<!--body end here-->
<?php
require_once './footer.php';
?>
