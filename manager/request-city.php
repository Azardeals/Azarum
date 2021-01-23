<?php
require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
if ($_SESSION['cityname'] != "") {
    $cityname = $_SESSION['cityname'];
} else {
    $cityname = 1;
}
if (!isCompanyUserLogged()) {
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . $cityname . '/merchant-login.php'));
}
$company_id = $_SESSION['logged_user']['company_id'];
$frm = getMBSFormByIdentifier('frmCityAdmin');
$frm->setTableProperties('class="tbl_forms" ');
$fld = $frm->getField('city_active');
$frm->removeField($fld);
$frm->setFieldsPerRow(3);
$company_name = $_SESSION['logged_user']['company_name'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    if ($frm->validate($post)) {
        $arr_updates = [
            'city_name' => $post['city_name'],
            'city_state' => $post['city_state'],
            'city_code' => $post['city_code'],
            'city_facebook_url' => $post['city_facebook_url'],
            'city_twitter_url' => $post['city_twitter_url'],
            'city_id' => $post['city_id']
        ];
        $record = new TableRecord('tbl_cities');
        $record->assignValues($arr_updates);
        $record->setFldValue('city_request', 1);
        $record->setFldValue('city_active', 1);
        $record->setFldValue('city_requested_id', $_SESSION['logged_user']['company_id']);
        $success = ($post['city_id'] > 0) ? $record->update('city_id=' . $post['city_id']) : $record->addNew();
        if ($success) {
            $msg->addMsg('City Requested Successfully ');
            /* Notify Admin  */
            $rs = $db->query("select * from tbl_email_templates where tpl_id=14");
            $row_tpl = $db->fetch($rs);
            $message = $row_tpl['tpl_message'];
            $subject = $row_tpl['tpl_subject'];
            $arr_replacements = [
                'xxname_of_companyxx' => $company_name,
                'xxcity_namexx' => $post['city_name'],
                'xxwebsiteurlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
                'xxcity_codexx' => $post['city_code'],
                'xxsitenamexx' => CONF_SITE_NAME
            ];
            foreach ($arr_replacements as $key => $val) {
                $subject = str_replace($key, $val, $subject);
                $message = str_replace($key, $val, $message);
            }
            sendMail(CONF_SITE_OWNER_EMAIL, $subject, emailTemplate(nl2br($message)), $headers);
            /* Notify Admin */
            redirectUser(CONF_WEBROOT_URL . 'merchant/request-city.php');
        } else {
            $msg->addError('Could not add/update! Error: ' . $record->getError());
            $frm->fill($post);
        }
    } else {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error) {
            $msg->addError($error);
        }
        $frm->fill($post);
    }
}
require_once './header.php';
?>
</div></td>
<!--body start here-->
<div class="tblheading">Request Cities</div>
<ul class="tabs">
    <li ><a href="<?php echo (CONF_WEBROOT_URL . 'merchant/merchant-account.php'); ?>">My Account</a></li>
    <li><a href="<?php echo (CONF_WEBROOT_URL . 'merchant/company-deals.php'); ?>">My Deals</a></li>
    <li ><a href="<?php echo (CONF_WEBROOT_URL . 'merchant/company-charity.php'); ?>">Charity</a></li>
    <li ><a href="<?php echo (CONF_WEBROOT_URL . 'merchant/request-city.php'); ?>"  class="active">Request To Add City</a></li> 
    <li ><a href="<?php echo (CONF_WEBROOT_URL . 'merchant/merchant-coupon-purchased.php'); ?>" >Update Total Amount Purchased</a></li> 
</ul>
<div style="clear:both;"></div>
<?php echo '<div class="form">' . $msg->display() . $frm->getFormHtml() . '</div>'; ?>
<div class="clear"></div>
<?php require_once './footer.php'; ?>