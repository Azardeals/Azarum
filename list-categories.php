<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
if (!isset($_SESSION['city'])) {
    redirectUser(CONF_WEBROOT_URL);
}
$get = getQueryStringData();
$subCat = $get['subcat'];
if (!isset($subCat) || intval($subCat) < 1) {
    redirectUser(CONF_WEBROOT_URL);
}
require_once './header.php';
?>
<div class="bodyContainer">
    <div class="containerTop">
        <h2><?php echo t_lang('M_TXT_DEAL_CATEGORIES_IN'); ?> <?php echo $_SESSION['city_to_show']; ?> </h2>
    </div>
    <div class="clear"></div>
    <div class="bodyWrapper">
        <div class="gap"></div>
        <?php
        $countDeal = 0;
        $srch = new SearchBase('tbl_deal_categories', 'dc');
        $srch->addCondition('dc.cat_active', '=', 1);
        $srch->addCondition('dc.cat_parent_id', '=', $subCat);
        $srch->addOrder('dc.cat_display_order', 'asc');
        $rs = $srch->getResultSet();
        $countCats = $srch->recordCount();
        if ($countCats > 0) {
            ?>
            <ul class="listing_cities">
                <?php
                while ($row = $db->fetch($rs)) {
                    $countDeal++;
                    if ($countDeal % 2 == 0)
                        $classDeal = 'class="nomarg_Right"';
                    else
                        $classDeal = '';
                    $categoryUrl = CONF_WEBROOT_URL . 'category-deal.php?cat=' . $row['cat_id'] . '&type=side';
                    ?>                	 
                    <li <?php echo $classDeal; ?>>
                        <a href="<?php echo friendlyUrl($categoryUrl); ?>">
                            <img src="<?php echo CONF_WEBROOT_URL . 'deal-image.php?cat=' . $row['cat_id'] . '&mode=dealcatimages'; ?>" alt="<?php echo $row['cat_name' . $_SESSION['lang_fld_prefix']]; ?>" />
                        </a>
                        <h5>
                            <a href="<?php echo friendlyUrl($categoryUrl); ?>" >
                                <?php echo $row['cat_name' . $_SESSION['lang_fld_prefix']]; ?>
                            </a>
                        </h5>
                    </li>						
                    <?php
                }
                ?>
            </ul>
            <?php
        } else {
            ?>
            <div class="clear"></div>
            <div class="gap"></div>
            <div  id="msg">
                <div class="system-notice error">
                    <a class="closeMsg" href="javascript:void(0);" onclick="$(this).closest('#msg').hide(); return false;"> 
                        <img src="<?php echo CONF_WEBROOT_URL; ?>images/cross.png">
                    </a>
                    <p id="message">
                        <?php echo t_lang('M_TXT_DEAL_CATEGORIS_NOT_FOUND'); ?> 
                    </p>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>
<?php require_once './footer.php'; ?>