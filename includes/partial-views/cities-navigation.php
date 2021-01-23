<?php
require_once './application-top.php';
$srch = new SearchBase('tbl_cities');
$srch->addFld("city_name" . $_SESSION['lang_fld_prefix']);
$srch->addFld('city_id');
$srch->addCondition('city_active', '=', 1);
$srch->addCondition('city_deleted', '=', 0);
$srch->addCondition('city_request', '=', 0);
$srch->addCondition('city_id', '= ', 0);
$srch->addOrder('city_name');
$qry1 = $srch->getResultset();
$count = $srch->recordCount();
$srch1 = new SearchBase('tbl_cities');
$srch1->addFld("city_name" . $_SESSION['lang_fld_prefix']);
$srch1->addFld('city_id');
$srch1->addCondition('city_active', '=', 1);
$srch1->addCondition('city_deleted', '=', 0);
$srch1->addCondition('city_id', '!= ', 0);
$srch1->addCondition('city_request', '=', 0);
$srch1->addOrder('city_name');
$qry = $srch1->getResultset();
if ($count > 0) {
    $city = $db->fetch($qry1);
    $mainCity = $city["city_name" . $_SESSION['lang_fld_prefix']];
} else {
    $rows = $db->fetch($qry);
    $mainCity = $rows["city_name" . $_SESSION['lang_fld_prefix']];
}
?>
<li class="dropdown dropdown--trigger-cities">
    <a href="javascript:void(0)" class="hide__mobile"><?php echo $_SESSION['cityname']; ?></a>
    <div class="dropsection dropsection--org sideleft dropdown--target-cities">
        <div class="dropsection__head fadedicon">
            <div class="selected_element" id="globalCitySelected" data-rel="<?php echo $_SESSION['city']; ?>">
                <?php echo t_lang('M_TXT_YOUR_SELECTED_CITY'); ?><span><?php echo $_SESSION['cityname']; ?></span>
            </div>
        </div>
        <div class="dropsection__body space">
            <div class="formrow">
                <span class="formrow__label"><?php echo t_lang('M_TXT_SELECT_CITY'); ?></span>
                <div class="selectors">
                    <a class="selector__link" href="javascript:void(0)"><?php echo $mainCity; ?></a>
                    <div class="selector__wrap" style="display:none;">
                        <ul class="linksvertical">
                            <?php if ($count > 0) { ?>
                                <li><a href="javascript:void(0);" onclick="selectCity('0', ' <?php echo CONF_FRIENDLY_URL; ?>');"><?php echo $mainCity; ?></a></li>
                                <?php
                            }
                            while ($rows = $db->fetch($qry)) {
                                if (!empty($rows["city_name" . $_SESSION['lang_fld_prefix']])) {
                                    $cities = $rows["city_name" . $_SESSION['lang_fld_prefix']];
                                    ?>
                                    <li><a href="javascript:void(0);" onclick="selectCity('<?php echo $rows['city_id']; ?>', ' <?php echo CONF_FRIENDLY_URL; ?>');"><?php echo $cities ?></a></li>
                                    <?php
                                }
                            }
                            ?> 
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>    
</li>