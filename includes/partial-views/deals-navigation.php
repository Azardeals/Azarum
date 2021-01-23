<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
?>
<li class="navchild">
    <a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'deal.php'); ?>"><?php echo t_lang('M_TXT_DEALS'); ?></a>
    <span class="link__mobilenav"></span>
    <div class="subnav">
        <span class="arrow"><span></span></span>
        <div class="subnav__wrapper">
            <div class="fixed_container">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="verticaltabs">
                            <ul class="verticaltabs__nav">
                                <?php echo printNav(0, 7); ?>
                            </ul>
                            <div class="verticaltabs__container">
                                <!--tabs0 start here-->
                                <div id="tabs-deal" class="verticaltabs__content">
                                    <?php
                                    $deal_list = alldealPageHtml(1, 'main-deal', '', $_SESSION['city'], '', '', 3, 'header');
                                    if (empty($deal_list)) {
                                        echo '<div class="col-md-12 "><div class="alert alert_info">' . unescape_attr(sprintf(t_lang(M_TXT_SORRY_NO_DEAL), '', $_SESSION['city_to_show'])) . '</div></div>';
                                    } else {
                                        ?>
                                        <h3><?php echo t_lang('M_TXT_MAIN_DEALS'); ?></h3>
                                        <ul class="grids_onethird">
                                            <?php
                                            foreach ($deal_list as $row) {
                                                $deal_id_arr[] = $row['deal_id'];
                                                $deal = $row['deal_id'];
                                                $objDeal = new DealInfo($deal);
                                                if ($objDeal->getError() != '') {
                                                    continue;
                                                }
                                                $deal = $objDeal->getFields();
                                                $deal['header'] = true;
                                                ?>
                                                <li>
                                                    <?php include dirname(__FILE__) . '/deal.php'; ?>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    <?php } ?>  
                                </div>
                                <!--tabs0 end here--> 
                                <!--tabs1 start here-->
                                <div id="tabs-instant-deal" class="verticaltabs__content">
                                    <?php
                                    $deal_list = alldealPageHtml(1, 'instant-deal', '', $_SESSION['city'], '', '', 3, 'header');
                                    if (empty($deal_list)) {
                                        echo '<div class="col-md-12 "><div class="alert alert_info">' . unescape_attr(sprintf(t_lang(M_TXT_SORRY_NO_DEAL), '', $_SESSION['city_to_show'])) . '</div></div>';
                                    } else {
                                        ?>
                                        <a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'instant-deal.php'); ?>" class="linknormal right"><?php echo t_lang('M_TXT_VIEW_ALL'); ?></a>
                                        <h3><?php echo t_lang('M_TXT_INSTANT_DEALS'); ?></h3>
                                        <ul class="grids_onethird">
                                            <?php
                                            foreach ($deal_list as $row) {
                                                $deal_id_arr[] = $row['deal_id'];
                                                $deal = $row['deal_id'];
                                                $objDeal = new DealInfo($deal);
                                                if ($objDeal->getError() != '') {
                                                    continue;
                                                }
                                                $deal = $objDeal->getFields();
                                                $deal['header'] = true;
                                                ?>
                                                <li>
                                                    <?php include dirname(__FILE__) . '/deal.php'; ?>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    <?php } ?>  
                                </div>
                                <!--tabs1 end here-->  
                                <!--tabs2 start here-->
                                <div id="tabs-all-deals" class="verticaltabs__content">
                                    <?php
                                    $deal_list = alldealPageHtml(1, 'all-deals', '', $_SESSION['city'], '', '', 3, 'header');
                                    if (empty($deal_list)) {
                                        //echo sprintf(t_lang(M_TXT_SORRY_NO_DEAL),'',$_SESSION['city_to_show']); 
                                        echo '<div class="col-md-12 "><div class="alert alert_info">' . unescape_attr(sprintf(t_lang(M_TXT_SORRY_NO_DEAL), '', $_SESSION['city_to_show'])) . '</div></div>';
                                    } else {
                                        ?>
                                        <a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'all-deals.php'); ?>" class="linknormal right"><?php echo t_lang('M_TXT_VIEW_ALL'); ?></a>
                                        <h3><?php echo t_lang('M_TXT_ALL_DEALS'); ?></h3>	
                                        <ul class="grids_onethird">
                                            <?php
                                            foreach ($deal_list as $row) {
                                                $deal = $row['deal_id'];
                                                $objDeal = new DealInfo($deal);
                                                if ($objDeal->getError() != '') {
                                                    continue;
                                                }
                                                $deal = $objDeal->getFields();
                                                $deal['header'] = true;
                                                ?>
                                                <li>
                                                    <?php include dirname(__FILE__) . '/deal.php'; ?>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    <?php } ?> 
                                </div>
                                <!--tabs2 end here-->
                                <!--tabs3 start here-->
                                <div id="tabs-city-deals" class="verticaltabs__content">
                                    <?php
                                    $deal_list = alldealPageHtml(1, 'city-deals', '', $_SESSION['city'], '', '', 3, 'header');
                                    if (empty($deal_list)) {
                                        echo '<div class="col-md-12 "><div class="alert alert_info">' . unescape_attr(sprintf(t_lang(M_TXT_SORRY_NO_DEAL), '', $_SESSION['city_to_show'])) . '</div></div>';
                                    } else {
                                        ?>
                                        <a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'city-deals.php'); ?>" class="linknormal right"><?php echo t_lang('M_TXT_VIEW_ALL'); ?></a>
                                        <h3><?php echo t_lang('M_TXT_CITY_DEALS'); ?></h3>	
                                        <ul class="grids_onethird">
                                            <?php
                                            foreach ($deal_list as $row) {
                                                $deal_id_arr[] = $row['deal_id'];
                                                $deal = $row['deal_id'];
                                                $objDeal = new DealInfo($deal);
                                                if ($objDeal->getError() != '') {
                                                    continue;
                                                }
                                                $deal = $objDeal->getFields();
                                                $deal['header'] = true;
                                                ?>
                                                <li>
                                                    <?php include dirname(__FILE__) . '/deal.php'; ?>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    <?php } ?>    
                                </div>
                                <!--tabs3 end here-->
                                <!--tabs4 start here-->
                                <div id="tabs-expired-deal" class="verticaltabs__content">
                                    <?php
                                    $deal_list = alldealPageHtml(1, 'expired-deal', '', '', '', '', 3, 'header');
                                    if (empty($deal_list)) {
                                        echo '<div class="col-md-12 "><div class="alert alert_info">' . unescape_attr(sprintf(t_lang(M_TXT_SORRY_NO_DEAL), '', $_SESSION['city_to_show'])) . '</div></div>';
                                    } else {
                                        ?>
                                        <a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'expired-deal.php'); ?>" class="linknormal right"><?php echo t_lang('M_TXT_VIEW_ALL'); ?></a>
                                        <h3><?php echo t_lang('M_TXT_EXPIRED_DEALS'); ?></h3>	
                                        <ul class="grids_onethird">
                                            <?php
                                            foreach ($deal_list as $row) {
                                                $objDeal = new DealInfo($row['deal_id']);
                                                if ($objDeal->getError() != '') {
                                                    continue;
                                                }
                                                $dealUrl = CONF_WEBROOT_URL . 'deal.php?deal=' . $objDeal->getFldValue('deal_id') . '&type=main';
                                                $deal = $objDeal->getFields();
                                                $deal['header'] = true;
                                                ?>
                                                <li>
                                                    <?php include dirname(__FILE__) . '/deal.php'; ?>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    <?php } ?>
                                </div>
                                <!--tabs4 end here-->
                            </div>
                        </div>    
                    </div>
                </div>
            </div>   
        </div> 
    </div>
</li>