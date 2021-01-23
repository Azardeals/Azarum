<?php
require_once 'application-top.php';
if (!isset($_SESSION['city']))
    redirectUser(CONF_WEBROOT_URL);
$get = getQueryStringData();
if (!is_numeric($get['deal'])) {
    $srch = new SearchBase('tbl_deals', 'd');
    $srch->addCondition('d.deal_city', '=', $_SESSION['city']);
    $srch->addCondition('d.deal_status', '=', 0);
    $srch->addCondition('d.deal_deleted', '=', 0);
    $srch->addCondition('d.deal_instant_deal', '!=', 1);
    $srch->joinTable('tbl_order_deals', 'LEFT OUTER JOIN', 'd.deal_id=od.od_deal_id', 'od');
    $srch->joinTable('tbl_orders', 'LEFT OUTER JOIN', 'od.od_order_id=o.order_id', 'o');
    $srch->addGroupBy('d.deal_id');
    $srch->addFld("SUM(CASE WHEN o.order_payment_status=1 or o.order_date>'" . date('Y-m-d H:i:s', strtotime('-30 MINUTE')) . "' THEN od.od_qty ELSE 0 END) AS sold");
    /* Consider user preferences */
    if (isUserLogged()) {
        $srch_temp = new SearchBase('tbl_deals', 'd');
        $srch_temp->addGroupBy('d.deal_id');
        $srch_temp->addCondition('d.deal_city', '=', $_SESSION['city']);
        $srch_temp->addCondition('deal_status', '=', 0);
        $srch_temp->addCondition('deal_deleted', '=', 0);
        $srch_temp->joinTable('tbl_deal_to_category', 'LEFT OUTER JOIN', 'd.deal_id=dc.dc_deal_id', 'dc');
        $srch_temp->joinTable('tbl_user_to_deal_cat', 'LEFT OUTER JOIN', 'dc.dc_cat_id=udc.udc_cat_id and udc.udc_user_id=' . $_SESSION['logged_user']['user_id'], 'udc');
        $srch_temp->addFld("SUM(CASE WHEN udc_user_id IS NULL THEN 0 ELSE 1 END) as cat_weight");
        $srch_temp->addFld('d.deal_id');
        $srch_temp->doNotCalculateRecords();
        $srch_temp->doNotLimitRecords();
        $srch->joinTable('(' . $srch_temp->getQuery() . ')', 'LEFT OUTER JOIN', 'd.deal_id=tmp.deal_id', 'tmp');
        $srch->addFld('tmp.cat_weight');
        $srch->addOrder('tmp.cat_weight', 'desc');
    }
    /* Consider user preferences ends */
    $srch->addOrder('deal_id', 'desc');
    $srch->addMultipleFields(array('d.deal_id', 'deal_max_coupons', 'deal_min_buy', 'deal_city'));
    //echo $srch->getQuery();
    $rs_deal_list = $srch->getResultSet();
    $countRecords = $srch->recordCount();
    if ($countRecords == 0) {
        $msg->addMsg($_SESSION['city_to_show'] . ' ' . t_lang('M_MSG_NO_UPCOMING_DEALS'));
        require_once './msgdie.php';
    }
}
require_once './header.php';
if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) {
    ?> 
    <div  id="msg">
        <div class="system-notice notice">
            <a class="close" href="javascript:void(0);" onclick="$(this).closest('#msg').hide(); return false;">
                <?php echo t_lang('M_TXT_HIDE'); ?>
            </a>
            <p id="message"><?php echo $msg->display(); ?> </p>
        </div>
    </div>
<?php } ?>
<!--bodyContainer start here-->
<div class="bodyContainer">
    <div class="containerTop">
        <h2><?php echo t_lang('M_TXT_UPCOMINGS_DEALS_IN'); ?> <?php echo $_SESSION['city_to_show']; ?></h2>
    </div>
    <div class="clear"></div>
    <div class="bodyWrapper">
        <div class="gap"></div>
        <ul class="listing_categories">
            <?php
            $count = 0;
            while ($row = $db->fetch($rs_deal_list)) {
                $count++;
                $deal_id_arr[] = $row['deal_id'];
                $deal = $row['deal_id'];
                $objDeal = new DealInfo($deal);
                if ($objDeal->getError() != '') {
                    $msg->addMsg('Invalid Request.');
                    require_once './msgdie.php';
                }
                if ($count % 3 == 0)
                    $classCity = 'class="nomarg_Right fr"';
                else
                    $classCity = '';
                ?>
                <?php $dealUrl = CONF_WEBROOT_URL . 'deal.php?deal=' . $objDeal->getFldValue('deal_id') . '&type=main'; ?>		
                <li <?php echo $classCity; ?>>
                    <div class="catescolum">
                        <div class="catescolum_top">
                            <h3><a href="<?php echo friendlyUrl($dealUrl); ?>"><?php echo substr($objDeal->getFldValue('deal_name' . $_SESSION['lang_fld_prefix']), 0, 90); ?></a></h3>
                        </div>
                        <div class="catescolum_wrap">
                            <!--onhover change-->
                            <div class="articles">
                                <div class="previews">
                                    <a href="<?php echo friendlyUrl($dealUrl); ?>"><img class="dealsPic"  src="<?php echo CONF_WEBROOT_URL . 'deal-image-crop.php?id=' . $objDeal->getFldValue('deal_id') . '&type=categorylist'; ?>" alt="<?php echo $objDeal->getFldValue('deal_name' . $_SESSION['lang_fld_prefix']); ?>"></a>
                                </div>
                                <div class="article-overs">
                                <!-- <h4><?php echo substr($objDeal->getFldValue('deal_name' . $_SESSION['lang_fld_prefix']), 0, 35); ?></h4> -->
                                    <p><?php echo substr($objDeal->getFldValue('deal_subtitle' . $_SESSION['lang_fld_prefix']), 0, 110); ?></p>
                                    <a href="<?php echo friendlyUrl($dealUrl); ?>" class="viewbutton"><?php echo t_lang('M_TXT_VIEW') . ' ' . t_lang('M_TXT_DEAL'); ?></a>
                                </div>
                            </div>
                            <!--onhover change-->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="timesTable">
                                <tr>
                                    <td><span><?php echo t_lang('M_TXT_SAVING'); ?></span><br /><?php echo (($objDeal->getFldValue('deal_discount_is_percent') == 1) ? '' : CONF_CURRENCY) . $objDeal->getFldValue('deal_discount') . (($objDeal->getFldValue('deal_discount_is_percent') == 1) ? '%' : ''); ?></td>
                                    <td><span><?php echo t_lang('M_TXT_PURCHASED'); ?></span><br /> <?php echo($objDeal->getFldValue('sold')); ?></td>
                                    <td><span><?php echo t_lang('M_TXT_REMAINING'); ?></span><br /><?php echo($objDeal->getFldValue('deal_max_coupons')) - ($objDeal->getFldValue('sold')); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>
<!--bodyContainer end here-->
<?php require_once './footer.php'; ?>
