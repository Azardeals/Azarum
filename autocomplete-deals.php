<?php

require_once './application-top.php';
require_once './includes/navigation-functions.php';
$input = $_REQUEST["term"];
$srch = new SearchBase('tbl_deals', 'd');
$srch->addCondition('deal_name' . $_SESSION["lang_fld_prefix"], 'LIKE', '%' . $input . '%');
$srch->addCondition('deal_start_time', '<=', date('Y-m-d H:i:s'), 'AND', true);
$srch->addCondition('deal_end_time', '>', date('Y-m-d H:i:s'), 'AND', true);
$srch->addCondition('deal_status', '=', 1);
$srch->addCondition('deal_complete', '=', 1);
$srch->addCondition('deal_deleted', '=', 0);
$srch->joinTable('tbl_deal_to_category', 'INNER JOIN', 'd.deal_id=tdoc.dc_deal_id', 'tdoc');
$srch->joinTable('tbl_deal_categories', 'LEFT JOIN', 'tdoc.dc_cat_id=tdc.cat_id', 'tdc');
$srch->addGroupBy('d.deal_id');
$srch->addGroupBy('tdc.cat_id');
$srch->addOrder("d.deal_name" . $_SESSION["lang_fld_prefix"]);
$srch->addOrder('tdc.cat_name' . $_SESSION["lang_fld_prefix"]);
$List = $srch->getResultSet();
$dealArray = [];
while ($row = $db->fetch($List)) {
    $cat1 = '<a class="highlight-suggestion-vertical" href="javascript:void(0);" >' . $row["cat_name"] . '</a>';
    $cat['label'] = $row['cat_name' . $_SESSION['lang_fld_prefix']];
    $cat['category'] = $row['deal_name' . $_SESSION['lang_fld_prefix']];
    $dealArray[] = $cat;
}
echo json_encode($dealArray);
