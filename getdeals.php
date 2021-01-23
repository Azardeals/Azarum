<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
if (!isset($_SESSION['city'])) {
    redirectUser(CONF_WEBROOT_URL);
}
$get = getQueryStringData();
$page = is_numeric($_POST['page']) ? $_POST['page'] : 1;
$pagesize = 12;
$cityList = $db->query("select * from tbl_cities where city_active=1 and city_deleted=0");
while ($Cityrow = $db->fetch($cityList)) {
    if ($Cityrow['city_id'] == $_SESSION['city']) {
        $select = 'selected';
    } else {
        $select = '';
    }
    $cityOption .= '<option value="' . $Cityrow['city_id'] . '" ' . $select . '  >' . $Cityrow['city_name'] . '</option>';
}
if (!is_numeric($get['deal'])) {
    require_once './includes/page-functions/deal-functions.php';
    $srch = getExpiredDealIds($_SESSION['city'], $page, $pagesize);
    #$srch->getQuery();
    $rs_deal_list = $srch->getResultSet();
    $pagestring = '';
    $pages = $srch->pages();
    $pageno = $page + 1;
    if ($pages > 1) {
        $rescount = ((($pageno - 1) * $pagesize < $srch->recordCount()) ? $srch->recordCount() - (($pageno - 1) * $pagesize) : 0);
        $pagestring .= '<h3 class="textcenter"><span> Showing ' . ((($pageno - 1) * $pagesize > $srch->recordCount()) ? $srch->recordCount() : (($pageno - 1) * $pagesize)) . ' of ' . $srch->recordCount() . '</span></h3>';
        if ($rescount > 0) {
            $pagestring .= '<div class="aligncenter">';
            if (isset($_POST['seemore']) && $_POST['seemore'] == 'true') {
                $pagestring .= '<a href="javascript:void(0);" class="button red" onclick="return getmoredeals(' . $pageno . ',true);return false;"> See ' . $rescount . ' More</a>';
            }
            $pagestring .= '</div>';
        }
    }
    $countRecords = $srch->recordCount();
    if ($countRecords == 0) {
        $msg->addError($_SESSION['city_to_show'] . ' ' . t_lang('M_TXT_NO_EXPIRED_DEALS'));
        //  require_once 'msgdie.php';
        if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) {
            ?> 
            <div  id="msg">
                <div class="system-notice notice">
                    <a class="closeMsg" href="javascript:void(0);" onclick="$(this).closest('#msg').hide();
                                        return false;"> <img src="<?php echo CONF_WEBROOT_URL; ?>images/cross.png"></a>
                    <p id="message"><?php echo $msg->display(); ?> </p>
                </div>
            </div>
            <?php
        }
    }
}
?>
<!--bodyContainer start here-->
<?php
$count = 0;
while ($row = $db->fetch($rs_deal_list)) {
    $count++;
    $deal_id_arr[] = $row['deal_id'];
    $deal = $row['deal_id'];
    $objDeal = new DealInfo($deal);
    echo $objDeal->getError();
    if ($objDeal->getError() != '') {
        $msg->addMsg(t_lang('M_ERROR_INVALID_REQUEST'));
        require_once './msgdie.php';
    }
    if ($count % 4 == 0)
        $classCity = 'class="nomarg_Right fr"';
    else
        $classCity = '';
    ?>
    <?php $dealUrl = CONF_WEBROOT_URL . 'deal.php?deal=' . $objDeal->getFldValue('deal_id') . '&type=main'; ?>		
    <?php if ($count % 4 == 0) { ?>
        <div class="dealBox nomargin_right"> <?php } else { ?>
            <div class="dealBox"> <?php } ?>
            <div class="pic"><a href="<?php echo friendlyUrl($dealUrl); ?>"><img class="dealsPic"  src="<?php echo CONF_WEBROOT_URL . 'deal-image-crop.php?id=' . $objDeal->getFldValue('deal_id') . '&type=categorylist'; ?>" alt="<?php echo $objDeal->getFldValue('deal_name' . $_SESSION['lang_fld_prefix']); ?>"></a>
                <div class="soldWrap">
                    <span class="hang"></span>
                    <span class="sold">
                        <?php
                        if ($objDeal->getFldValue('deal_type') == 1) {
                            echo t_lang('M_TXT_SORRY_PRODUCT_SOLD_OUT');
                        } else {
                            echo t_lang('M_TXT_SORRY_DEAL_SOLD_OUT');
                        }
                        ?>
                    </span>
                </div>
            </div>
            <a class="dealname" href="<?php echo friendlyUrl($dealUrl); ?>"><h1><?php echo substr($objDeal->getFldValue('deal_name' . $_SESSION['lang_fld_prefix']), 0, 70); ?></h1></a>                       
            <div class="dealinfo"><h2><?php echo substr($objDeal->getFldValue('deal_subtitle' . $_SESSION['lang_fld_prefix']), 0, 35); ?></h2></div>
            <a href="<?php echo friendlyUrl($dealUrl); ?>" class="arrowLink"></a>
            <h4 class="price"><del>
                    <?php
                    if ($objDeal->getFldValue('deal_discount') > 0) {
                        echo CONF_CURRENCY;
                        echo number_format($objDeal->getFldValue('deal_original_price'), 2) . CONF_CURRENCY_RIGHT;
                    }
                    ?></del>
                <?php
                echo CONF_CURRENCY;
                echo number_format($objDeal->getFldValue('price'), 2) . CONF_CURRENCY_RIGHT;
                ?></h4>
        </div>
    <?php } ?>
    <div class="clear"></div>    
    <div class="paginglink">
        <?php echo $pagestring ?>
    </div>
    <div class="gap"></div> 		
</div>
<!--bodyContainer end here-->
