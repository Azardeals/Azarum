<?php
require_once './application-top.php';
//$arr_common_js[] ='js/slick.js';
require_once './includes/navigation-functions.php';
require_once './includes/buy-deal-functions.php';
require_once './includes/page-functions/deal-functions.php';
require_once './site-classes/order.cls.php';
$deal_type = $deal['deal_type'];
$deal_sub_type = $deal['deal_sub_type'];
$timenow = strtotime(date('Y-m-d H:i:s'));
$is_logged = isUserLogged();
if ($deal_type == 1) {
    $options = '';
    $str = '';
    $options = getDealOptions($deal_id);
    /* Code to Show attributes/options of Products starts here */
    if (!empty($options)) {
        $str .= ' <ul class="action__options clearfix">';
        $count = 1;
        if (is_array($options) && count($options)) {
            foreach ($options as $key => $option) {
                $str .= '<li>';
                if ($option['option_type'] == 'select') {
                    $str .= '<div id="option-' . $option['deal_option_id'] . '" class="option">';
                    $str .= $option['option_name'] . ':';
                    if ($option['required']) {
                        $str .= '<span class="mandatory">*</span>';
                    }
                    $str .= '<br/>';
                    $change = "getproductAttributeValue(" . $deal_id . " ," . $option['deal_option_id'] . " ," . $key . ", this)";
                    $name = 'option[' . $option['deal_option_id'] . ']';
                    $str .= '<select name="' . $name . '" onchange="' . $change . '" class="level_' . $key . '">';
                    $str .= '<option value="">--Please Select--</option>';
                    if ($count == 1) {
                        foreach ($option['option_value'] as $option_value) {
                            $validateOptionHtml = '';
                            if (!checkProductQuantityAvaiable($deal_id, array($option['deal_option_id'] => $option_value['deal_option_value_id']))) {
                                $validateOptionHtml = ' disabled="disabled" style="color:red;" ';
                            }
                            $str .= '<option ' . $validateOptionHtml . ' value="' . $option_value['deal_option_value_id'] . '">' . $option_value['name'];
                            if ($option_value['price']) {
                                $str .= ' (' . $option_value['price_prefix'] . CONF_CURRENCY . $option_value['price'] . CONF_CURRENCY_RIGHT . ')';
                            }
                            $str .= '</option>';
                        }
                    }
                    $str .= '</select>';
                    $str .= '</div>';
                }
                $str .= '</li>';
                $count++;
            }
        }
        $str .= '</ul>';
    }
}
global $db;
$image_rs = $db->query("select * from tbl_deals_images where dimg_deal_id=" . $deal_id);
$totalImages = $db->total_records($image_rs);
$class = "colswrap";
if ($totalImages > 0) {
    $class = "colswrap showgallery";
}
?>
<div class="fixed_container">
    <div class="row">
        <!-- <div class="colswrap"> -->
        <div class="<?php echo $class; ?>">
            <aside class="col__left col-md-7 col-sm-7">
                <div class="item__gallery">
                    <div class="grid_left">
                        <ul class="item__main">
                            <li><figure class="main__pic zoomImg"> <img rel="" src="<?php echo CONF_WEBROOT_URL . 'deal-image.php?id=' . $deal_id . '&mode=homeSliderMainImage&time=' . time(); ?>" alt="<?php echo $deal['deal_name' . $_SESSION['lang_fld_prefix']] ?>" /></figure></li>
                            <?php
                            $thumbs = "";
                            while ($row = $db->fetch($image_rs)) {
                                echo '<li><figure class="main__pic zoomImg"><img rel="" src="' . CONF_WEBROOT_URL . 'deal-image.php?id=' . $row['dimg_id'] . '&mode=homeSliderImages&time=' . time() . '"  alt="' . $deal['deal_name'] . '"></figure></li>';
                                $thumbs .= '<li><figure class="item__thumb"><img  src="' . CONF_WEBROOT_URL . 'deal-image.php?id=' . $row['dimg_id'] . '&mode=thumbImages&time=' . time() . '"  alt="' . $deal['deal_name'] . '"></figure></li>';
                            }
                            ?>
                        </ul>
                    </div>
                    <?php if ($totalImages > 0) {
                        ?>
                        <div class="grid_right">
                            <ul class="item__thumbs item__thumbs2">
                                <li><figure class="item__thumb"> <img src="<?php echo CONF_WEBROOT_URL . 'deal-image.php?id=' . $deal_id . '&mode=mainthumbImages&time=' . time(); ?>" alt="<?php echo $deal['deal_name' . $_SESSION['lang_fld_prefix']] ?>" /></figure></li>
                                <?php
                                echo $thumbs;
                                ?>
                            </ul>
                        </div>
                    <?php } ?>
                </div>
            </aside>
            <aside class="col__right col-md-5 col-sm-5 siteForm">
                <span class="item__title"><?php echo $deal['deal_name' . $_SESSION['lang_fld_prefix']] ?></span>
                <div class="item__price">
                    <span class="item__price_old"><?php echo amount($deal['deal_original_price']); ?></span>
                    <span class="item__price_standard"><?php echo amount($deal['price']); ?></span>
                    <?php if ($deal['deal_discount'] > 0) { ?>
                        <span class="item__price_discounted">( <?php echo ($deal['deal_discount_is_percent'] == 1) ? $deal['deal_discount'] . '%' : amount($deal['deal_discount']); ?> )</span>
                    <?php } ?>
                </div>
                <div class="row__onehalf clearfix">
                    <div class="grid_1">
                        <div class="ratingwrap">
                            <?php
                            $reviewsRow = fetchDealRating($deal_id);
                            ?>
                            <div class="ratings">
                                <ul>
                                    <?php
                                    for ($i = 0; $i < $reviewsRow['rating']; $i++) {
                                        echo'<li><img src="' . CONF_WEBROOT_URL . 'images/rating-full.png" alt=""></li>';
                                    }
                                    for ($j = $reviewsRow['rating']; $j < 5; $j++) {
                                        echo'<li><img src="' . CONF_WEBROOT_URL . 'images/rating-zero.png" alt=""></li>';
                                    }
                                    ?>
                                </ul>
                            </div>
                            <?php
                            $reviewsRs = getReviews($deal_id);
                            if ($reviewsRs->num_rows > 0) {
                                ?>
                                <a href="#reviews" class="txt__normal"><?php echo t_lang('M_TXT_REVIEWS'); ?> (<?php echo $reviewsRs->num_rows; ?>)</a>
                            <?php } ?>
                        </div>
                        <?php if ($deal['sold'] > 0) { ?>
                            <p><?php echo t_lang('M_TXT_OVER'); ?> <?php echo($deal['sold']); ?>  - <?php echo t_lang('M_TXT_PURCHASED_OUT_OF'); ?>  <?php echo $deal['deal_max_coupons']; ?></p>
                        <?php } ?>
                    </div>
                    <div class="grid_2">
                        <div class="timer">
                            <span class="digit" id="spndaysleft">00</span><?php echo t_lang('M_TXT_DAY'); ?>.
                            <span class="digit" id="spnhrsleft">00</span><?php echo t_lang('M_TXT_HOURS'); ?>.
                            <span class="digit" id="spnmtsleft">00</span><?php echo t_lang('M_TXT_MINUTES'); ?>.
                            <span class="digit" id="spnscsleft">00</span><?php echo t_lang('M_TXT_SECONDS'); ?>.
                        </div>
                    </div>
                </div>
                <?php
                if ($deal_type == 1) {
                    echo $str;
                }
                ?>
                <ul class="action__options large clearfix">
                    <li>
                        <?php
                        if (($deal['deal_is_subdeal'] == 1) || ($deal['deal_is_subdeal'] == 0 && $deal['deal_sub_type'] >= 1 && $deal['deal_type'] == 0)) {
                            $dealUrl = CONF_WEBROOT_URL . 'deal.php?deal=' . $deal['deal_id'] . '&type=main';
                            ?>
                            <a href="<?php echo friendlyUrl($dealUrl, $deal['deal_city_name']); ?>" class="themebtn themebtn--large themebtn--org " ><?php echo t_lang('M_TXT_VIEW_DETAIL'); ?> </a>
                        <?php } else if ($deal['deal_status'] == 2) { ?>
                            <a href="javascript:void(0);" class="themebtn themebtn--large themebtn--org " ><?php echo t_lang('M_ERROR_SORRY_DEAL_EXPIRED'); ?> </a>
                        <?php } elseif ($deal['sold'] > $deal['deal_max_coupons']) { ?>
                            <a href="javascript:void(0);" class="themebtn themebtn--large themebtn--org " ><?php echo t_lang('M_ERROR_SORRY!_ALL_UNITS_SOLD'); ?> </a>
                        <?php } else { ?>
                            <a href="javascript:void(0);" class="themebtn themebtn--large themebtn--org "
                            <?php if ($deal['deal_status'] >= 3) { ?>
                                   onclick="$.facebox('<?php echo t_lang('M_ERROR_SORRY_DEAL_IS_CANCELED'); ?>');"
                               <?php } else if ($deal['deal_status'] == 2) { ?>
                                   onclick="$.facebox('<?php echo t_lang('M_ERROR_SORRY_DEAL_EXPIRED'); ?>');"
                               <?php } else if ($deal['deal_status'] == 0) { ?>
                                   onclick="$.facebox('<?php echo t_lang('M_ERROR_DEAL_IS_NOT_ACTIVE'); ?>');"
                                   <?php
                               } else {
                                   if (isset($_SESSION['logged_user']['affiliate_id']) && $_SESSION['logged_user']['affiliate_id'] > 0) {
                                       ?>
                                       onclick= "$.facebox('<?php echo t_lang('M_ERROR_AFFILIATE_HAVE_NO_PERMISSSION'); ?>');"
                                   <?php } else if ($deal['deal_is_subdeal'] == 1) { ?>
                                       onclick="displaySubdeal(<?php echo $deal_id; ?>);"
                                   <?php } else if ($deal['deal_is_subdeal'] == 0 && $deal['deal_sub_type'] >= 1 && $deal['deal_type'] == 0) { ?>
                                       onclick="displaySubdeal(<?php echo $deal_id; ?>);"
                                   <?php } else { ?>
                                       onclick="buyDeal(<?php echo $deal['deal_id']; ?>, false,<?php echo CONF_FRIENDLY_URL; ?>, frm_data, 0);"
                                       <?php
                                   }
                               }
                               ?> > <?php echo t_lang('M_TXT_ADD_TO_CART'); ?> </a>
                           <?php } ?>
                           <?php
                           if ($deal['deal_status'] != 2) {
                               echo fetchfavUnfavIconHtml($deal_id);
                           }
                           ?>
                    </li>
                    <li class="last">
                        <?php
                        if ($deal['deal_status'] == 1 && ($deal['deal_type'] == 0) && ($deal['deal_sub_type'] == 0 && $deal['deal_is_subdeal'] == 0 )) {
                            if (isset($_SESSION['logged_user']['affiliate_id']) > 0) {
                                ?>
                                <a href="javascript:void(0);" class="themebtn themebtn--large themebtn--grey" onclick= "$.facebox('<?php echo t_lang('M_ERROR_AFFILIATE_HAVE_NO_PERMISSSION'); ?>');" > <?php require_once CONF_VIEW_PATH . 'gift-svg.php' ?><?php echo t_lang('M_TXT_GIVE_AS_A_GIFT'); ?>
                                <?php } else { ?>
                                    <a href="javascript:void(0);" class="themebtn themebtn--large themebtn--grey" onclick="buyDeal(<?php echo $deal_id; ?>, true,<?php echo CONF_FRIENDLY_URL; ?>, frm_data, 0);
                                       " title="<?php echo t_lang('M_TXT_GIVE_AS_A_GIFT'); ?>">  <?php require_once CONF_VIEW_PATH . 'gift-svg.php' ?><?php echo t_lang('M_TXT_GIVE_AS_A_GIFT'); ?>
                                           <?php
                                       }
                                       ?>
                                </a>
                            <?php } ?>
                    </li>
                </ul>
                <div class="panelinfo">
                    <div class="panelinfo__top clearfix">
                        <div class="grid_1"><strong><?php echo t_lang('M_TXT_SELLER'); ?></strong></div>
                        <div class="grid_2" style="display:none">
                            <?php if (CONF_DEFAULT_LANGUAGE == 2 || $_SESSION['language'] == 2) { ?>
                                <script type="text/javascript">
                                    var addthis_config = {
                                        ui_language: "es"
                                    }
                                </script>
                            <?php } ?>
                            <ul class="list__socials">
                                <li><?php echo t_lang('M_TXT_SHARE_ON'); ?></li>
                                <li class="fb" ><a class='st_facebook_large' displayText='Facebook' href="javascript:void(0)"><i class="icon ion-social-facebook"></i></a></li>
                                <li><a class='st_twitter_large' displayText='Tweet' href="javascript:void(0)"><i class="icon ion-social-twitter"></i></a></li>
                                <li><a class='st_linkedin_large' displayText='LinkedIn' href="javascript:void(0)"><i class="icon ion-social-linkedin"></i></a></li>
                                <li><a class='st_pinterest_large' displayText='Pinterest' href="javascript:void(0)"><i class="icon ion-social-pinterest"></i></a></li>
                                <li><a class='st_email_large' displayText='Email' href="javascript:void(0)"><i class="icon ion-android-mail"></i></a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="info__seller clearfix panelinfo__bottom">
                        <?php
                        $srch = getCompanyDetail($deal['deal_id']);
                        $cityAddress = $srch->getResultSet();
                        $rowAddress = $db->fetch($cityAddress);
                        $merchantUrl = CONF_WEBROOT_URL . 'merchant-favorite.php?company=' . $deal['deal_company'] . '&page=1';
                        ?>
                        <div class="avtar"><a href="<?php echo friendlyUrl($merchantUrl); ?>">
                                <?php
                                echo '<img alt=""  class="vendorlogo" src="' . CONF_WEBROOT_URL . 'deal-image.php?id=' . $rowAddress['company_id'] . '&mode=companyLogo" >';
                                ?>
                            </a></div>
                        <div class="avtarinfo">
                            <h6><?php echo $rowAddress['company_name' . $_SESSION['lang_fld_prefix']]; ?></h6><a href="<?php echo friendlyUrl($merchantUrl); ?>" class="linknormal"><?php echo t_lang('M_TXT_VIEW_PROFILE'); ?></a>
                            <div class="btn-info">
                                <div class="ratingwrap">
                                    <div class="ratings">
                                        <?php
                                        $comreviewsRow = fetchCompanyRating($deal['deal_company']);
                                        echo ' <ul>';
                                        for ($i = 0; $i < $comreviewsRow['rating']; $i++) {
                                            echo'<li><img src="' . CONF_WEBROOT_URL . 'images/rating-full.png" alt=""></li>';
                                        }
                                        for ($j = $comreviewsRow['rating']; $j < 5; $j++) {
                                            echo'<li><img src="' . CONF_WEBROOT_URL . 'images/rating-zero.png" alt=""></li>';
                                        }
                                        echo'</ul>';
                                        ?>
                                    </div>
                                    <?php if ($is_logged) { ?>
                                        <a href="<?php echo friendlyUrl($merchantUrl); ?>" class="txt__normal">
                                        <?php } else { ?>
                                            <a href="javascript::void(0);" class="txt__normal" onclick="$.facebox('<?php echo t_lang('M_ERROR_PLEASE_SIGN_IN_TO_SEE_MERCHANT_REVIEWS'); ?>');">
                                            <?php } echo intval($rowAddress['reviews']) . ' ' . t_lang('M_TXT_REVIEWS'); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
            </aside>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?php echo CONF_WEBROOT_URL; ?>js/zoom.js"></script>
<script type="text/javascript">
                                                var end_time;
                                                initSlider();
                                                var frm_data;
                                                var subdeal_id;
                                                frm_data = $('.siteForm input[type=\'text\'], .siteForm input[type=\'hidden\'], .siteForm input[type=\'radio\']:checked, .siteForm input[type=\'checkbox\']:checked, .siteForm select, .siteForm textarea');
                                                function closeDiv() {
                                                    $('body').removeClass('hide__scroll');
                                                    $(".popup").not('[id="getaway-calender"]').remove();
                                                }
                                                var d = new Date();
                                                var end_time = d.valueOf() +<?php echo((strtotime($deal['deal_end_time']) - $timenow) * 1000); ?>;
                                                function updateSecsLeft() {
                                                    //     end_time = d.valueOf() +<?php echo((strtotime($row['deal_end_time']) - $timenow) * 1000); ?>;
                                                    var d = new Date();
                                                    var days;
                                                    var hours;
                                                    var mins;
                                                    var secs;
                                                    var remaining = (end_time - d.valueOf()) / 1000;
                                                    if (isNaN(remaining)) {
                                                        $('#timer').html('EXPIRED');
                                                        return;
                                                    }
                                                    days = Math.floor(remaining / (3600 * 24));
                                                    $('#spndaysleft').html(days);
                                                    remaining = remaining % (3600 * 24);
                                                    hours = Math.floor(remaining / 3600);
                                                    $('#spnhrsleft').html(hours);
                                                    remaining = remaining % 3600;
                                                    mins = Math.floor(remaining / 60);
                                                    $('#spnmtsleft').html(mins);
                                                    remaining = remaining % 60;
                                                    secs = Math.floor(remaining);
                                                    $('#spnscsleft').html(secs);
                                                    setTimeout('updateSecsLeft();', 1000);
                                                    $('#timeleft').html('<p>' + days + ' Days, ' + hours + ' Hours, ' + mins + ' minutes, <br/>' + secs + ' Seconds</p>');
                                                }
                                                function initSlider() {
                                                    $('.item__main').slick({
                                                        slidesToShow: 1,
                                                        slidesToScroll: 1,
                                                        infinite: false,
                                                        vertical: true,
                                                        arrows: false,
                                                        centerMode: true,
                                                        centerPadding: "50px",
                                                        autoplay: true,
                                                        asNavFor: '.item__thumbs2',
                                                        responsive: [{breakpoint: 1050, settings: {centerPadding: "0", centerMode: false, vertical: false, }}]
                                                    });
                                                    $('.zoomImg').zoom({magnify: 1, onZoomOut: 3});
                                                    $('.item__thumbs2').slick({
                                                        slidesToShow: 4,
                                                        infinite: false,
                                                        slidesToScroll: 1,
                                                        asNavFor: '.item__main',
                                                        vertical: true,
                                                        arrows: true,
                                                        focusOnSelect: true,
                                                        prevArrow: '<a data-role="none" class="slick-prev" aria-label="previous"></a>',
                                                        nextArrow: '<a data-role="none" class="slick-next" aria-label="next"></a>',
                                                        responsive: [
                                                            {breakpoint: 1050, settings: {centerPadding: "0", centerMode: false, vertical: false, }},
                                                            {breakpoint: 767, settings: {centerPadding: "0", centerMode: false, vertical: false, }}
                                                        ]
                                                    });
                                                }
                                                updateSecsLeft();
</script>
