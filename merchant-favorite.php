<?php
require_once './application-top.php';
$arr_common_js[] = 'js/jquery.rating.js';
$arr_common_css[] = 'css/jquery.rating.css';
require_once './includes/navigation-functions.php';
require_once './includes/page-functions/merchant-functions.php';
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
    if (true === saveReview($frm, $post, $message)) {
        $msg->addMsg($message);
        redirectUser();
    } else {
        $msg->addError($message);
        $frm->fill($post);
    }
}
/* reviews posted end here */
require_once './header.php';
$company_locations = getCompanyLocations($companyrow['company_id']);
$count = 1;
$addressArray = [];
while ($row = $db->fetch($company_locations)) {
    $addressArray[$count]['address'] = $row['company_address_line1' . $_SESSION['lang_fld_prefix']] . ' ' . $row['company_address_line2' . $_SESSION['lang_fld_prefix']] . ' ' . $row['company_address_line3' . $_SESSION['lang_fld_prefix']] . ' ' . $row['company_address_zip'] . ' ' . $row['company_city'] . ' ' . $row['state_name' . $_SESSION['lang_fld_prefix']] . ' ' . $row['country_name'];
    $addressArray[$count]['html'] = $row['company_address_line1' . $_SESSION['lang_fld_prefix']] . '<br/>' . $row['company_address_line2' . $_SESSION['lang_fld_prefix']] . '<br/>' . $row['company_address_line3' . $_SESSION['lang_fld_prefix']] . '<br/>' . $row['company_address_zip'] . '<br/>' . $row['company_city'] . '<br/>' . $row['state_name' . $_SESSION['lang_fld_prefix']] . '<br/>' . $row['country_name'];
    $count++;
}
?>
<script type="text/javascript">
    var txtoops = "<?php echo addslashes(t_lang('M_TXT_INTERNAL_ERROR')); ?>";
    var txtreload = "<?php echo addslashes(t_lang('M_JS_PLEASE_RELOAD_AND_TRY')); ?>";
</script> 
<?php
if ($companyrow['company_logo' . $_SESSION['lang_fld_prefix']] == "") {
    $imagesrc = CONF_WEBROOT_URL . 'images/defaultLogo.jpg';
} else {
    $imagesrc = CONF_WEBROOT_URL . 'deal-image.php?company=' . $companyrow['company_id'] . '&mode=companyImages';
}
?>
<!--bodyContainer start here-->
<section class="pagebar">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-7 col-sm-7">
                <h3><?php echo $companyrow['company_name' . $_SESSION['lang_fld_prefix']]; ?></h3>
                <ul class="breadcrumb">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL); ?>"><?php echo t_lang('M_TXT_HOME'); ?></a></li>
                    <li><?php echo t_lang('M_TXT_MERCHANT'); ?></li>
                </ul>
            </aside>
        </div>
    </div>
</section> 
<section class="layout">
    <div class="fixed_container">
        <div class="layout__table">
            <aside class="layout__leftcell" <?php echo (( CONF_REVIEW_RATING_MERCHANT == 0 ) ? ' style=" border-right:0; " ' : '') ?>>
                <div class="panel">
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
                            if (CONF_REVIEW_RATING_MERCHANT == 1) {
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
                                <?php
                                if ($companyrow['reviews'] > 1) {
                                    $url = CONF_WEBROOT_URL . 'merchant-reviews.php?company=' . $get['company'];
                                    ?>
                                    <a class="txt__normal" href="<?php echo $url; ?>"> <?php echo t_lang('M_TXT_REVIEWS') . ' (' . $companyrow['reviews'] . ')' ?></a>
                                    <?php
                                }
                            }
                            ?>
                            <div class="links__inline">
                                <ul>
                                    <?php if ($companyrow['company_url'] != "") { ?>
                                        <li><i class="ion-android-globe icon"></i><?php echo($companyrow['company_url'] != "") ? '<a target="_blank" href=" http://' . $companyrow['company_url'] . '">' . $companyrow['company_url'] . '</a>' : '----'; ?> </li>
                                    <?php } if ($companyrow['company_phone'] != "") { ?>
                                        <li><i class="icon ion-android-phone-portrait"></i> <?php echo ($companyrow['company_phone'] != "") ? $companyrow['company_phone'] : '----'; ?></li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                        <div class="panel__middle clearfix">
                            <p><?php
                                if ($companyrow['company_profile_enabled'] == 1) {
                                    echo ($companyrow['company_profile' . $_SESSION['lang_fld_prefix']] != "") ? $companyrow['company_profile' . $_SESSION['lang_fld_prefix']] : '';
                                }
                                ?></p>
                            <span class="gap"></span>
                            <div class="panel__middle-foot clearfix">
                                <div class="grid_1">
                                    <a class="themebtn themebtn--small scroll" href="#merchantdeals"><?php echo t_lang('M_TXT_MERCHANT_DEALS'); ?></a>
                                </div>
                                <div class="grid_2">
                                    <ul class="list__socials">
                                        <?php if (($companyrow['company_facebook_url'] != "") || ($companyrow['company_twitter'] != "") || ($companyrow['company_linkedin'] != "")) { ?>
                                            <li><?php echo t_lang('M_TXT_GET_CONNECTED'); ?></li>
                                        <?php } ?>
                                        <?php if ($companyrow['company_facebook_url'] != "") { ?>
                                            <li><a href="http://<?php echo $companyrow['company_facebook_url']; ?>" target="_blank"><i class="icon ion-social-facebook"></i></a></li>
                                        <?php } ?>
                                        <?php if ($companyrow['company_twitter'] != "") { ?>
                                            <li><a href="http://<?php echo $companyrow['company_twitter']; ?>" target="_blank"><i class="icon ion-social-twitter"></i></a></li>
                                        <?php } ?>
                                        <?php if ($companyrow['company_linkedin'] != "") { ?>
                                            <li><a href="http://<?php echo $companyrow['company_linkedin']; ?>" target="_blank"><i class="icon ion-social-pinterest"></i></a></li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="panel__footer clearfix">
                            <div class="container__map" id= "mapCanvas" >
                            </div>
                        </div>       
                    </div>
            </aside>
            <?php if (CONF_REVIEW_RATING_MERCHANT == 1) { ?>
                <aside class="layout__rightcell">
                    <div class="panel__reviews">
                        <?php if ($companyrow['reviews'] > 0) { ?>
                            <div class="panel__block">
                                <h5><?php echo t_lang('M_TXT_REVIEWS_AND_RATINGS'); ?> </h5>
                                <a class="txt__normal" href="<?php echo CONF_WEBROOT_URL . 'merchant-reviews.php?company=' . $get['company']; ?>"><?php echo t_lang('M_TXT_REVIEWS') . ' (' . $companyrow['reviews'] . ')' ?></a>
                                <!--repeated-y -->
                                <div id="reviews" class="allreviews">
                                    <script>
                                        $(document).ready(function () {
                                            showReviews(1, '<?php echo $get['company']; ?>');
                                        });
                                    </script>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if (isUserLogged()) { ?>
                            <div class="panel__block">
                                <h5><?php echo t_lang('M_TXT_WRITE_FEEDBACK'); ?></h5>
                                <div class="formwrap">
                                    <?php
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
                                    ?> 
                                </div>
                            </div>
                        <?php } ?>  
                    </div>   
                </aside>
            <?php } ?>
        </div>
    </div>
</section>
<section class="page__container" id="merchantdeals">
    <div class="fixed_container">
        <div class="row">
            <?php require_once CONF_VIEW_PATH . 'left-filter-menu.php'; ?>
            <aside class="col-md-9">
                <a href="javascript:void(0);" name="show_deals"></a>
                <div class="section__head clearfix">
                    <h2><?php echo t_lang('M_TXT_MERCHANT_DEALS'); ?></h2>
                    <?php require_once CONF_VIEW_PATH . 'sort-filter-menu.php'; ?>
                </div>
                <div class="row__filter right_bar" style="display:none;">
                    <div class="row " >
                        <aside class="col-md-7 col-sm-7">
                            <ul class="tags__filter" id="filter" >
                                <li><?php echo t_lang('M_TXT_SHOW'); ?></li>
                            </ul>
                        </aside>
                        <aside class="col-md-5 col-sm-5 alignright">
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
<script type="text/javascript">
    var address = <?php echo json_encode($addressArray); ?>;
    console.log(address);
</script>                           
<?php require_once './footer.php'; ?>
