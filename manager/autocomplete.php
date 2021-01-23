<?php

require_once './application-top.php';
require_once '../includes/navigation-functions.php';
$input = $_REQUEST["q"];
$deal_id = intval($_REQUEST['deal_id']);
$List = $db->query("SELECT * FROM tbl_deal_to_category WHERE dc_deal_id=" . $deal_id);
while ($row1 = $db->fetch($List)) {
    $arr[] = $row1['dc_cat_id'];
}
if ($db->total_records($List) > 0) {
    $catList = $db->query("SELECT * FROM tbl_deal_categories WHERE (cat_name LIKE " . $db->quoteVariable('%' . $input . '%') . " OR cat_name_lang1 LIKE " . $db->quoteVariable('%' . $input . '%') . ") and cat_id NOT IN (" . implode(',', $arr) . ")");
} else {
    $catList = $db->query("SELECT * FROM tbl_deal_categories WHERE cat_name LIKE " . $db->quoteVariable('%' . $input . '%') . " OR cat_name_lang1 LIKE " . $db->quoteVariable('%' . $input . '%') . "");
}
$cat = '';
while ($row = $db->fetch($catList)) {
    $cat .= $row['cat_name' . $_SESSION['lang_fld_prefix']] . "|" . $row['cat_id'] . "\n";
}
echo $cat;
