<?php
require_once './application-top.php';
// do not show reviews detail page if the review is disbaled for merchants
if (CONF_REVIEW_RATING_MERCHANT != 1) {
    header("HTTP/1.1 404 Not Found");
    header("Location: /404.php");
    exit;
}
$arr_common_js[] = 'js/jquery.rating.js';
$arr_common_css[] = 'css/jquery.rating.css';
require_once './includes/navigation-functions.php';
require_once './includes/page-functions/merchant-functions.php';
require_once './header.php';
if (!isset($_SESSION['city'])) {
    redirectUser(CONF_WEBROOT_URL);
}
$page = is_numeric($_GET['page']) ? $_GET['page'] : 1;
$pagesize = 12;
$get = getQueryStringData();
if (!isset($get['company'])) {
    redirectUser(CONF_WEBROOT_URL);
}
if (is_numeric($get['company'])) {
    $srch = merchantSearchObj();
    $srch->addCondition('c.company_id', '=', $get['company']);
    $rs_listing = $srch->getResultSet();
    $companyrow = $db->fetch($rs_listing);
    if ($db->total_records($rs_listing) == 0) {
        redirectUser(CONF_WEBROOT_URL);
    }
}
/* reviews posted start here */
$frm = getReviewForm($get['company']);
if (isset($_POST['btn_submit_review']) && CONF_REVIEW_RATING_MERCHANT == 1) {
    $frm = getReviewForm($get['company']);
    $post = getPostedData();
    $error = '';
    if (saveReview($frm, $post, $error)) {
        $msg->addMsg($error);
        redirectUser();
    } else {
        $msg->addError($error);
        $frm->fill($post);
    }
}
/* reviews posted end here */
?>
<?php
if ($companyrow['company_logo' . $_SESSION['lang_fld_prefix']] == "") {
    $imagesrc = CONF_WEBROOT_URL . 'images/defaultLogo.jpg';
} else {
    $imagesrc = CONF_WEBROOT_URL . 'deal-image.php?company=' . $companyrow['company_id'] . '&mode=companyImages';
}
?>
<?php $merchantUrl = CONF_WEBROOT_URL . 'merchant-favorite.php?company=' . $get['company'] . '&page=1'; ?>
<section class="pagebar">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-7 col-sm-7">
                <h3><?php echo $companyrow['company_name' . $_SESSION['lang_fld_prefix']]; ?></h3>
                <ul class="breadcrumb">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL); ?>"><?php echo t_lang('M_TXT_HOME'); ?></a></li>
                    <li><a href="<?php echo friendlyUrl($merchantUrl); ?>"><?php echo t_lang('M_TXT_MERCHANT'); ?></a></li>
                    <li><?php echo t_lang('M_TXT_MERCHANT_REVIEWS'); ?></li>
                </ul>
            </aside>
            <aside class="col-md-5 col-sm-5 alignright">
                <a class="themebtn themebtn--small back" href="<?php echo friendlyUrl($merchantUrl); ?>"><?php echo t_lang('M_TXT_BACK'); ?></a>
            </aside>
        </div>
    </div>
</section> 
<section class="page__container">
    <div class="fixed_container">
        <div class="layout__compact">
            <div class="panel__upper clearfix">
                <figure class="panel__logo"><img alt="" src="<?php echo $imagesrc; ?> "></figure>
                <div class="panel__title"> 
                    <h2><?php echo $companyrow['company_name' . $_SESSION['lang_fld_prefix']]; ?></h2>
                    <?php
                    $user_id = $_SESSION['logged_user']['user_id'];
                    if (($_SESSION['logged_user']['user_id'] > 0) && ($get['company'] > 0)) {
                        $totalRow = likeMerchant($get['company']);
                        if ($totalRow == 0) {
                            ?>
                            <span id="likeMerchant_<?php echo $get['company']; ?>" class="heart"><a href="javascript:void(0);" onclick="likeMerchant('<?php echo $get['company']; ?>', 'like', 'company-detail')" class="heart__link" title="<?php echo t_lang('M_TXT_ADD_TO_FAVOURITES'); ?>"> </a><span class="heart__txt"><?php echo t_lang('M_TXT_ADD_TO_FAVOURITES'); ?></span></span>
                        <?php } else { ?>
                            <span id="likeMerchant_<?php echo $get['company']; ?>" class="heart active"> <a href="javascript:void(0);" onclick="likeMerchant('<?php echo $get['company']; ?>', 'unlike', 'company-detail')" class="heart__link " title="<?php echo t_lang('M_TXT_REMOVE_FROM_FAVOURITES'); ?>">  </a><span class="heart__txt"><?php echo t_lang('M_TXT_REMOVE_FROM_FAVOURITES'); ?></span></span>
                            <?php
                        }
                    } else {
                        ?>
                        <span id="likeMerchant_<?php echo $get['company']; ?>" class="heart"> <a href="javascript:void(0);" onclick="likeMerchant('<?php echo $get['company']; ?>', 'like', 'company-detail')" class="heart__link" title="<?php echo t_lang('M_TXT_ADD_TO_FAVOURITES'); ?>"> </a><span class="heart__txt"><?php echo t_lang('M_TXT_ADD_TO_FAVOURITES'); ?></span></span>
                    <?php } ?>
                </div>
                <div class="ratingwrap">
                    <?php
                    if (CONF_POST_REVIEW_RATING_MERCHANT == 1) {
                        $reviewsRow = fetchCompanyRating($companyrow['company_id']);
                        ?>
                        <div class=" ratings star-ratings">
                            <ul>
                                <?php
                                for ($i = 0; $i < $reviewsRow['rating']; $i++) {
                                    echo '<li><img src="' . CONF_WEBROOT_URL . 'images/rating-full.png" alt=""></li>';
                                }
                                for ($j = $reviewsRow['rating']; $j < 5; $j++) {
                                    echo '<li><img src="' . CONF_WEBROOT_URL . 'images/rating-zero.png" alt=""></li>';
                                }
                                ?>
                            </ul>
                        </div> 
                        <a href="<?php echo (CONF_WEBROOT_URL . 'merchant-reviews.php?company=' . $get['company']); ?>" class="txt__normal"> <?php echo t_lang('M_TXT_REVIEWS') . ' (' . $companyrow['reviews'] . ')' ?></a>
                    <?php } ?>
                </div>
                <div class="links__inline">
                    <ul><?php if ($companyrow['company_url']) { ?>
                            <li><a href="#" ><i class="ion-android-globe icon"></i><?php echo($companyrow['company_url'] != "") ? '<a target="_blank" href=" http://' . $companyrow['company_url'] . '">' . $companyrow['company_url'] . '</a>' : '----'; ?> </a></li>
                        <?php } if ($companyrow['company_phone']) { ?>
                            <li><i class="icon ion-android-phone-portrait"></i> <?php echo ($companyrow['company_phone'] != "") ? $companyrow['company_phone'] : '----'; ?></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
            <div class="sectiontop__row">
                <a class="themebtn themebtn--small right form__reviews_link" href="javascript:void(0);"><?php echo t_lang('M_TXT_WRITE_A_REVIEW'); ?></a>
                <h4><?php echo t_lang('M_TXT_REVIEWS_AND_RATINGS'); ?></h4>
            </div> 
            <div class="form__wrap form__reviews" style="display:none;">
                <a href="javascript:void(0)" class="link__close form__reviews_link"></a>
                <div class="listrepeated">
                    <aside class="grid_1">
                        <figure class="avtar"><?php echo substr(htmlentities($_SESSION['logged_user']['user_name']), 0, 1); ?></figure>
                    </aside>
                    <aside class="grid_2">
                        <h3 class="name"><?php echo htmlentities($_SESSION['logged_user']['user_name']) . ' ' . htmlentities($_SESSION['logged_user']['user_lname']) ?></h3>
                        <?php
                        if (isUserLogged()) {
                            if (CONF_POST_REVIEW_RATING_MERCHANT == 1) {
                                $canReview = canPostReview($get['company'], $_SESSION['logged_user']['user_id']);
                                if ($db->total_records($canReview) > 0) {
                                    echo $frm->getFormHtml();
                                } else {
                                    echo '<span style="color:green;">' . t_lang('M_TXT_SORRY_NOT_ALLOWED_TO_POST_REVIEWS_OF_MERCHANT') . '</span>';
                                }
                            } else {
                                echo $frm->getFormHtml();
                            }
                        } else {
                            ?>
                            <div class="form__wrap1 form__reviews1" style="overflow: hidden;">
                                <?php echo t_lang('M_TXT_Please'); ?><a href="/login/"> Sign in</a> <?php echo t_lang('M_TXT_to_view_Reviews_and_Ratings'); ?>
                            </div>
                        <?php }
                        ?> 
                    </aside>
                </div>
            </div>
            <?php if (CONF_REVIEW_RATING_MERCHANT == 1) { ?>
                <div id="reviews" class="allreviews"></div>
            <?php } ?>  
        </div>
    </div>    
</section>
<script type="text/javascript">
    $(document).ready(function () {
        showReviews(1, '<?php echo $get['company']; ?>');
    });
    $('.form__reviews_link').click(function () {
        $(this).toggleClass("active");
        $('.form__reviews').slideToggle();
    });
    function showReviews(page, comapnyId) {
        callAjax(webroot + 'common-ajax.php', 'mode=ShowReviews&page=' + page + '&comapnyId=' + comapnyId, function (t) {
            var ans = parseJsonData(t);
            $('.loadmore').remove();
            $('#reviews').append(ans.msg);
        });
    }
</script>
<?php
include "./footer.php";
