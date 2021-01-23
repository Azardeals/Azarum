<?php
$class = "item";
if ($deal['deal_status'] == 2) {
    $class = "item item--sold";
}
$now = time(); // or your date as well
$start_date = strtotime($deal['deal_start_time']);
$datediff = $now - $start_date;
$old_day = floor($datediff / (60 * 60 * 24));
$dealUrl = CONF_WEBROOT_URL . 'deal.php?deal=' . $deal['deal_id'] . '&type=main';
$pagesize = 12;
$end_date = strtotime($deal['deal_end_time']);
$enddatediff = $end_date - $now;
$remaining_day = floor($enddatediff / (60 * 60 * 24));
?>
<div class="<?php echo $class; ?>">
    <div class="item__head">
        <a href="<?php echo friendlyUrl($dealUrl, $deal['deal_city_name']); ?>">
            <img class="item__pic" alt="" src="<?php echo CONF_WEBROOT_URL . 'deal-image-crop.php?id=' . $deal['deal_id'] . '&type=instant'; ?>">
            <span class="item__link">
                <?php if ($deal['deal_status'] == 2) { ?>
                    <span class="soldout"><?php echo t_lang('M_TXT_EXPIRED_DEAL'); ?></span>
                <?php } ?>	
                <i class="icon ion-plus"></i>
            </span>
        </a> 
        <?php if ($deal['deal_status'] != 2 && $old_day <= 30) { ?>
            <span class="item__lable"><?php echo t_lang('M_TXT_NEW'); ?></span>
        <?php } ?>
        <?php
        if (!isset($deal['header']) && ($searchtype != "notRequired")) {
            $type = "deal";
            if (isset($searchtype)) {
                $type = $searchtype;
            }
            $click = "fetchQuickViewHtmlJS(" . $deal['deal_id'] . ",'" . $type . "'," . $pagesize . ")";
            ?>
            <div class="btngroup positioned">
                <a href="javascript:void(0);" onclick="<?php echo $click; ?>" class="themebtn themebtn--org themebtn--small themebtn--block topRecentProduct_<?php echo $deal['deal_id'] ?>"><?php echo t_lang('M_TXT_QUICK_VIEW'); ?></a>
            </div>
        <?php } ?>
    </div>
    <div class="item__body">
        <?php if ($deal['deal_status'] != 2) { ?>
            <?php echo fetchfavUnfavIconHtml($deal['deal_id']); ?>
        <?php } ?>
        <span class="item__title"><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'deal.php?deal=' . $deal['deal_id'] . '&type=main'); ?>"> <?php echo appendPlainText(substr($deal['deal_name' . $_SESSION['lang_fld_prefix']], 0, 30)); ?></a></span>
        <div class="item__price">
            <?php if ($deal['deal_discount'] > 0) { ?>
                <span class="item__price_old"><?php echo amount($deal['deal_original_price']); ?></span>
            <?php } ?>
            <span class="item__price_standard"><?php echo amount($deal['price']); ?></span>
            <span class="item__price_discounted">
                <?php if ($deal['deal_discount'] > 0) { ?>
                    (<?php echo ($deal['deal_discount_is_percent'] == 1) ? $deal['deal_discount'] . '%' : amount($deal['deal_discount']); ?>)
                    <?php
                } else {
                    echo '&nbsp';
                }
                ?>
            </span>
        </div>
        <?php
        if (CONF_REVIEW_RATING_DEALS == 1) {
            /* if(!isset($deal['header'])){ */
            $reviewsRow = fetchDealRating($deal['deal_id']);
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
        }
        if ($enddatediff > 0) {
            ?>
            <span class="item__time">  <?php echo $remaining_day . ' ' . t_lang('M_TXT_DAYS') . ' ' . t_lang('M_TXT_LEFT'); ?></span>
            <?php
        }
        /*  } */
        ?>
    </div>
</div>