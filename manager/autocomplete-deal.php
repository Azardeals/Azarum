<?php

require_once './application-top.php';
require_once '../includes/navigation-functions.php';
$input = $_REQUEST["q"];
$company_id = intval($_REQUEST['company_id']);
$List = $db->query("SELECT * FROM tbl_deals WHERE deal_name LIKE '%$input%' and deal_company=" . $company_id);
$cat = '';
while ($row = $db->fetch($List)) {
    $cat .= $row['deal_name'] . "|" . $row['deal_id'] . "\n";
}
echo $cat;
