<?php require_once './includes/page-functions/merchant-functions.php'; ?>
<div class="box">
    <div class="box__head">
        <?php
        $merchantUrl = CONF_WEBROOT_URL . 'merchant-favorite.php?company=' . $companyrow['company_id'] . '&page=1';
        if ($companyrow['company_logo' . $_SESSION['lang_fld_prefix']] == "") {
            $src = '<img alt="" class="vendorlogo" src="' . CONF_WEBROOT_URL . 'images/defaultLogo.jpg" >';
        } else {
            $src = '<img alt=""  class="vendorlogo" src="' . CONF_WEBROOT_URL . 'deal-image.php?company=' . $companyrow['company_id'] . '&mode=companyImages" >';
        }
        ?>
        <a href="<?php echo friendlyUrl($merchantUrl); ?>"><?php echo $src; ?></a>
        <?php
        $user_id = $_SESSION['logged_user']['user_id'];
        if (($_SESSION['logged_user']['user_id'] > 0) && ($companyrow['company_id'] > 0)) {
            $totalRow = likeMerchant($companyrow['company_id']);
            if ($totalRow == 0) {
                ?>
                <span  id="likeMerchant_<?php echo $companyrow['company_id']; ?>" class="heart"><a href="javascript:void(0);" onclick="likeMerchantWithReload('<?php echo $companyrow['company_id']; ?>', 'like', 'company-detail')" class="heart__link" title="<?php echo t_lang('M_TXT_ADD_TO_FAVOURITES'); ?>"> </a><span class="heart__txt"><?php echo t_lang('M_TXT_ADD_TO_FAVOURITES'); ?></span></span>
            <?php } else { ?>
                <span  id="likeMerchant_<?php echo $companyrow['company_id']; ?>" class="heart active"> <a href="javascript:void(0);" onclick="likeMerchantWithReload('<?php echo $companyrow['company_id']; ?>', 'unlike', 'company-detail')" class="heart__link " title="<?php echo t_lang('M_TXT_REMOVE_FROM_FAVOURITES'); ?>">  </a><span class="heart__txt"><?php echo t_lang('M_TXT_REMOVE_FROM_FAVOURITES'); ?></span></span>
                <?php
            }
        } else {
            ?>
            <span  id="likeMerchant_<?php echo $companyrow['company_id']; ?>" class="heart"> <a href="javascript:void(0);" onclick="likeMerchant('<?php echo $companyrow['company_id']; ?>', 'like', 'company-detail')" class="heart__link" title="<?php echo t_lang('M_TXT_ADD_TO_FAVOURITES'); ?>"> </a><span class="heart__txt"><?php echo t_lang('M_TXT_ADD_TO_FAVOURITES'); ?></span></span>
        <?php } ?>
    </div>
    <div class="box__body">
        <span class="box__title"><a href="<?php echo friendlyUrl($merchantUrl); ?> "><?php echo $companyrow['company_name' . $_SESSION['lang_fld_prefix']]; ?></a></span>
        <span class="box__subtitle"><?php echo $companyrow['company_city' . $_SESSION['lang_fld_prefix']] . ' , ' . $companyrow['state_name' . $_SESSION['lang_fld_prefix']] . ' , ' . $companyrow['country_name' . $_SESSION['lang_fld_prefix']]; ?></span>
        <?php if (CONF_REVIEW_RATING_MERCHANT == 1) { ?>
            <div class="ratings">
                <?php
                echo "<ul>";
                $reviewsRow = fetchCompanyRating($companyrow['company_id']);
                for ($i = 0; $i < $reviewsRow['rating']; $i++) {
                    echo'<li><img src="' . CONF_WEBROOT_URL . 'images/rating-full.png" alt=""></li>';
                }
                for ($j = $reviewsRow['rating']; $j < 5; $j++) {
                    echo'<li><img src="' . CONF_WEBROOT_URL . 'images/rating-zero.png" alt=""></li>';
                }
                echo "</ul>";
                ?>
            </div>
        <?php } ?>
        <div class="box__description">
            <p>
                <?php if ($companyrow['company_url'] != "") { ?>
                    <a href="http://<?php echo $companyrow['company_url']; ?>"><?php echo $companyrow['company_url']; ?></a>
                    <br/> 
                <?php } ?>
                <a href="mailto:<?php echo $companyrow['company_email']; ?>"><?php echo $companyrow['company_email']; ?></a>
                <br/><?php echo($companyrow['company_phone'] != "") ? $companyrow['company_phone'] : ""; ?></p>
        </div>
        <a class="themebtn themebtn--large" href="<?php echo friendlyUrl($merchantUrl) . '#show_deals'; ?>"><?php echo t_lang('M_TXT_BROWSE_DEALS'); ?></a>
    </div>
</div>