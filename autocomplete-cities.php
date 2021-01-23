<?php

require_once './application-top.php';
require_once './includes/navigation-functions.php';
$input = $_REQUEST["term"];
$srch = new SearchBase('tbl_cities', 'c');
$srch->addCondition('city_active', '=', 1);
$srch->addCondition('city_deleted', '=', 0);
$srch->addCondition('city_request', '=', 0);
$srch->addCondition('city_name' . $_SESSION['lang_fld_prefix'], 'LIKE', $input . '%');
$srch->joinTable('tbl_states', 'INNER JOIN', 'c.city_state=st.state_id', 'st');
$srch->joinTable('tbl_countries', 'INNER JOIN', 'country.country_id=st.state_country', 'country');
$srch->addOrder('c.city_name');
$srch->doNotLimitRecords();
$srch->doNotCalculateRecords();
$rs = $srch->getResultSet();
$count = 0;
$cityArray = [];
while ($row = $db->fetch($rs)) {
    $cat['label'] = $row['city_name' . $_SESSION['lang_fld_prefix']] . ' || ' . $row['state_name' . $_SESSION['lang_fld_prefix']] . ' || ' . $row['country_name' . $_SESSION['lang_fld_prefix']];
    //$cat['value']='<input type= "hidden"  name="city_id[]" value="'.$row['city_id'].'">';
    $cat['value'] = $row['city_id'];
    $cityArray[] = $cat;
}
echo json_encode($cityArray);
