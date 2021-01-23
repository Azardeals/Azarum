<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
$get = getQueryStringData();
if (!isset($get['company'])) {
    redirectUser(CONF_WEBROOT_URL);
}
if (is_numeric($get['company'])) {
    $srch = new SearchBase('tbl_companies', 'c');
    $srch->addCondition('c.company_id', '=', $get['company']);
    $srch->addCondition('company_active', '=', 1);
    $srch->addCondition('company_deleted', '=', 0);
    $rs_listing = $srch->getResultSet();
    $companyrow = $db->fetch($rs_listing);
    if ($db->total_records($rs_listing) == 0) {
        redirectUser(CONF_WEBROOT_URL);
    }
}
echo $companyrow['company_google_map'];
?>
<script type="text/javascript">
    $(document).ready(function () {
        init_map();
    });
</script>
