<?php
require_once './application-top.php';
/* define configuration variables */
$rs1 = $db->query("select * from tbl_extra_values");
while ($row1 = $db->fetch($rs1)) {
    define(strtoupper($row1['extra_conf_name']), $row1['extra_conf_val']);
}
/* end configuration variables */
if ($_POST['email_address'] != "") {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $post = getPostedData();
        $messageAdmin = 'Hello ' . CONF_EMAILS_FROM_NAME . ',
				There has been submission of Get Featured form on you site .Details are given below:
				<b>Name of the company: </b>' . $post['name_of_the_company'] . '
				<b>Nature of the Business: </b>' . $post['nature_of_the_business'] . '
				<b>Name of the Concerned Person: </b>' . $post['name_of_the_concerned_person'] . '
				<b>Contact Number: </b>' . $post['contact_number'] . '
				<b>Email Address: </b>' . $post['email_address'] . '
				<b>City: </b>' . $post['city'];
        /* EMAIL TO ADMIN AND USER */
        $rs = $db->query("select * from tbl_email_templates where tpl_id=21"); /* aDMIN */
        $row_tpl = $db->fetch($rs);
        $message = $row_tpl['tpl_message'];
        $subject = $row_tpl['tpl_subject'];
        $arr_replacements = array(
            'xxname_of_the_companyxx' => $post['name_of_the_company'],
            'xxnature_of_the_businessxx' => $post['nature_of_the_business'],
            'xxname_of_the_concerned_personxx' => $post['name_of_the_concerned_person'],
            'xxcontact_numberxx' => $post['contact_number'],
            'xxemail_addressxx' => $post['email_address'],
            'xxcityxx' => $post['city'],
            'xxsite_namexx' => CONF_SITE_NAME,
            'xxserver_namexx' => $_SERVER['SERVER_NAME'],
            'xxwebrooturlxx' => CONF_WEBROOT_URL,
            'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL
        );
        foreach ($arr_replacements as $key => $val) {
            $subject = str_replace($key, $val, $subject);
            $message = str_replace($key, $val, $message);
        }
        if ($row_tpl['tpl_status'] == 1) {
            sendMail(CONF_SITE_OWNER_EMAIL, $subject . ' ( Request ID: ' . time() . ' )', emailTemplate(($message)), $headers);
        }
        $rs = $db->query("select * from tbl_email_templates where tpl_id=22"); /* User */
        $row_tpl = $db->fetch($rs);
        $message = $row_tpl['tpl_message'];
        $subject = $row_tpl['tpl_subject'];
        $arr_replacements = array(
            'xxname_of_the_companyxx' => $post['name_of_the_company'],
            'xxemail_addressxx' => $post['email_address'],
            'xxsite_namexx' => CONF_SITE_NAME,
            'xxserver_namexx' => $_SERVER['SERVER_NAME'],
            'xxwebrooturlxx' => CONF_WEBROOT_URL,
            'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL
        );
        foreach ($arr_replacements as $key => $val) {
            $subject = str_replace($key, $val, $subject);
            $message = str_replace($key, $val, $message);
        }
        if ($row_tpl['tpl_status'] == 1) {
            sendMail($post['br_email'], $subject . ' ( Request ID: ' . time() . ' )', emailTemplate(($message)), $headers);
        }
        /* EMAIL TO ADMIN AND USER */
        $msg->addMsg('Mail Sent Successfully.');
    }
}
require_once './header.php';
$frmContact = getMBSFormByIdentifier('frmGetfeatured');
updateFormLang($frmContact);
$i = 0;
while ($fld = $frmContact->getFieldByNumber($i)) {
    setRequirementFieldCaption($fld);
    $i++;
}
?>
<div class="deal-cont">
    <div id="work-hd-sub">
        <div class="blue-hd">
            <div class="blue-hd-top"><?php echo $page_name; ?></div>
            <div class="blue-hd-btm"></div>
        </div>
    </div>
    <div class="terms-area-no-min">
        <?php echo EXTRA_GET_FEATURED; ?>
    </div>
    <div class="sign-up-area">
        <div class="signupForm_wrapper">
            <div class="formPic"></div>
            <div class="signupForm_wrap">
                <?php
                echo $msg->display();
                echo $frmContact->getFormHtml();
                ?>
            </div>
        </div>
    </div>
</div>
<div class="deal-cont-btm2"></div>
<?php require_once './footer.php'; ?>
