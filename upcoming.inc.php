<?php
$get = getQueryStringData();
//$upcomingDeal = $db->query("SELECT SQL_CALC_FOUND_ROWS d.*, c.* FROM tbl_deals d INNER JOIN tbl_companies c on d.deal_company=c.company_id WHERE deal_id <>$deal limit 0, 4");
$srch1 = new SearchBase('tbl_deals', 'd');
if (strpos($_SERVER['SCRIPT_FILENAME'], '/deal.php') != false) {
    $srch1->addCondition('deal_id', '<>', $objDeal->getFldValue('deal_id'));
    if ($row['deal_id'] != "") {
        $srch1->addCondition('deal_id', '<>', $row['deal_id']);
    }
} else {
    if ($row['deal_id'] != "") {
        $srch1->addCondition('deal_id', '<>', $row['deal_id']);
    }
}
$srch1->addCondition('deal_city', '=', $_SESSION['city']);
$srch1->addCondition('deal_status', '<', 2);
$srch1->addCondition('deal_start_time', '<=', 'mysql_func_now()', 'AND', true);
$srch1->addCondition('deal_end_time', '>=', 'mysql_func_now()', 'AND', true);
$srch1->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=c.company_id and c.company_active=1 and c.company_deleted=0', 'c');
$srch1->addMultipleFields(array('d.*', 'c.*'));
$srch1->addOrder('deal_status');
$upcomingDeal = $srch1->getResultSet();
if ($db->total_records($upcomingDeal) > 0) {
    ?>
    <div class="advertise_Wrapper">
        <div class="loopedSlider_gellery">
            <div id="loopedSlider_right1" >
                <ul>
                    <?php
                    while ($row1 = $db->fetch($upcomingDeal)) {
                        $dealUrl = CONF_WEBROOT_URL . 'deal.php?deal=' . $row1['deal_id'] . '&type=side';
                        ?>
                        <li> <a href="<?php echo friendlyUrl($dealUrl); ?>"><img src="<?php echo CONF_WEBROOT_URL . 'deal-image.php?id=' . $row1['deal_id'] . '&type=other'; ?>" alt="<?php echo $row1['deal_name']; ?>"    class="img_border"></a></li>
                    <?php } ?>
                </ul>
            </div>
            <span><a class="previous1" href="javascript:void(0);"><img alt="" src="<?php echo CONF_WEBROOT_URL ?>images/previous_arrow.png"></a></span>
            <span><a class="next1" href="javascript:void(0);"><img alt="" src="<?php echo CONF_WEBROOT_URL ?>images/next_arrow.png"></a></span>   
        </div>
    </div>
<?php } ?>