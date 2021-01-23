<?php
require_once './application-top.php';
$arr_common_js[] = 'js/slick.js';
require_once './includes/navigation-functions.php';
require_once './header.php';
global $db;
?>
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <section id="banner" class="sectionbanners inner">
                <div class="fixed_container">
                    <div class="row">
                        <div class="col-lg-9 col-sm-12">
                            <div class="bannerslider">
                                <ul class="slides">
                                    <?php
                                    $rows = fetchBannerDetail(6, 3);
                                    foreach ($rows as $key => $value) {
                                        $url_target = '';
                                        if ($value['banner_target'] != '') {
                                            $url_target = $value['banner_target'];
                                        }
                                        $src = CONF_WEBROOT_URL . 'banner-image-crop.php?banner=' . $value['banner_id'] . '&type=' . $value['banner_type'];
                                        ?>
                                        <li><a href="<?php echo $value['banner_url']; ?>" target="<?php echo $url_target ?>" class="slide--main"><img src="<?php echo $src; ?>" alt=""></a></li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-12">
                            <div class="groupbanners">
                                <ul>
                                    <?php
                                    $rows = fetchBannerDetail(2, 3);
                                    foreach ($rows as $key => $value) {
                                        $url_target = '';
                                        if ($value['banner_target'] != '') {
                                            $url_target = $value['banner_target'];
                                        }
                                        $src = CONF_WEBROOT_URL . 'banner-image-crop.php?banner=' . $value['banner_id'] . '&type=' . $value['banner_type'];
                                        ?>
                                        <li><a href="<?php echo $value['banner_url']; ?>" target="<?php echo $url_target ?>" class="slide--main"><img src="<?php echo $src; ?>" alt=""></a></li>
                                    <?php } ?>
                                </ul>    
                            </div>    
                        </div>
                    </div>    
                </div>    
            </section> 
            <div class="container-fluid">
                <?php if ($rs = fetchTopProducts(10)) { ?>
                    <section class="slide__wrap">
                        <h3><?php echo t_lang('M_TXT_TOP_SELLING_PRODUCTS'); ?></h3>
                        <?php $rows = $db->fetch_all($rs); ?>
                        <ul class="slider--onefourth">
                            <?php
                            foreach ($rows as $row) {
                                $objDeal = new DealInfo($row['deal_id'], false);
                                if ($objDeal->getError() != '') {
                                    echo $objDeal->getError();
                                }
                                $dealUrl = CONF_WEBROOT_URL . 'deal.php?deal=' . $objDeal->getFldValue('deal_id') . '&type=main';
                                $deal = $objDeal->getFields();
                                ?>
                                <li>
                                    <div class="itemcover">
                                        <?php
                                        $array = array('deal' => $deal, 'searchtype' => 'topSelling');
                                        echo renderDealView('deal.php', $array);
                                        ?>
                                    </div>
                                </li>
                            <?php } ?>     
                        </ul>
                    </section>
                <?php } ?>        
                <?php if ($rows = fetchTopRecentProducts(10)) { ?>
                    <section class="slide__wrap">
                        <h3><?php echo t_lang('M_TXT_LATEST_PRODUCTS'); ?></h3>
                        <ul class="slider--onefourth">
                            <?php
                            foreach ($rows as $row) {
                                $objDeal = new DealInfo($row['deal_id'], false);
                                if ($objDeal->getError() != '') {
                                    echo $objDeal->getError();
                                }
                                $dealUrl = CONF_WEBROOT_URL . 'deal.php?deal=' . $objDeal->getFldValue('deal_id') . '&type=main';
                                $deal = $objDeal->getFields();
                                ?>
                                <li>
                                    <div class="itemcover">
                                        <?php
                                        $array = array('deal' => $deal, 'searchtype' => 'topRecentProduct');
                                        echo renderDealView('deal.php', $array);
                                        ?>
                                    </div>
                                </li>
                            <?php } ?>     
                        </ul>
                    </section>
                <?php } ?>  
            </div>
        </div>
    </div>    
</section>
<script type="text/javascript">
    /* for Product banner  */
    $('.slides').slick({
        infinite: true,
        slidesToShow: 1,
        slidesToScroll: 1,
        adaptiveHeight: true,
        dots: true,
        autoplay: true,
        autoplaySpeed: 5000,
        pauseOnHover: false,
        arrows: true,
        prevArrow: '<a data-role="none" class="slick-prev" aria-label="previous"></a>',
        nextArrow: '<a data-role="none" class="slick-next" aria-label="next"></a>',
        responsive: [{breakpoint: 767, settings: {dots: false, }}]
    });
    /* for featured Produts  */
    $('.slider--onefourth').slick({
        infinite: false,
        slidesToShow: 4,
        slidesToScroll: 1,
        adaptiveHeight: true,
        dots: false,
        autoplay: false,
        autoplaySpeed: 5000,
        pauseOnHover: false,
        arrows: true,
        prevArrow: '<a data-role="none" class="slick-prev" aria-label="previous"></a>',
        nextArrow: '<a data-role="none" class="slick-next" aria-label="next"></a>',
        responsive: [
            {breakpoint: 990, settings: {slidesToShow: 3, }},
            {breakpoint: 767, settings: {slidesToShow: 2, }},
            {breakpoint: 400, settings: {slidesToShow: 1, }}
        ]
    });
</script>
<?php
require_once './footer.php';

