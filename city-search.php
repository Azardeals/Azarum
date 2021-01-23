<?php

require_once './application-top.php';
require_once './includes/navigation-functions.php';
$input = $_REQUEST["term"];
$srch = new SearchBase('tbl_newsletter_subscription', 'ns');
$cnd = $srch->addDirectCondition('0');
$cnd->attachCondition('ns.subs_email', '=', $_SESSION['logged_user']['user_email'], 'OR');
$cnd->attachCondition('ns.subs_user_id', '=', $_SESSION['logged_user']['user_id']);
$srch->addFld('subs_city');
$rs2 = $srch->getResultSet();
$result = $db->fetch_all($rs2);
$cityIdArray = [];
foreach ($result as $key => $value) {
    $cityIdArray[] = $value['subs_city'];
}
$srch = new SearchBase('tbl_cities', 'c');
$srch->addCondition('city_active', '=', 1);
$srch->addCondition('city_deleted', '=', 0);
$srch->addCondition('city_request', '=', 0);
if (!empty($cityIdArray)) {
    $srch->addCondition('city_id', 'NOT IN', $cityIdArray);
}
$srch->addCondition('city_name' . $_SESSION['lang_fld_prefix'], 'LIKE', $input . '%');
$srch->addOrder('c.city_name');
$srch->doNotLimitRecords();
$srch->doNotCalculateRecords();
$rs = $srch->getResultSet();
$count = 0;
$cityArray = [];
while ($row = $db->fetch($rs)) {
    $cat['label'] = $row['city_name' . $_SESSION['lang_fld_prefix']];
    $cat['value'] = $row['city_id'];
    $cityArray[] = $cat;
}
echo json_encode($cityArray);
