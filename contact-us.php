<?php
require_once './application-top.php';
require_once './securimage/securimage.php';
require_once './includes/navigation-functions.php';
/* define configuration variables */
$rs1 = $db->query("select * from tbl_extra_values");
while ($row1 = $db->fetch($rs1)) {
    define(strtoupper($row1['extra_conf_name']), $row1['extra_conf_val' . $_SESSION['lang_fld_prefix']]);
}
/* end configuration variables */
$frmContact = getMBSFormByIdentifier('frmContactus');
$fld = $frmContact->getField('security_code');
$fld->html_after_field = '<tr><td colspan=2><img class="captchapic" src="' . CONF_WEBROOT_URL . 'securimage/securimage_show.php?sid=' . time() . '" id="security_image">' . ' <a href="javascript:void(0);" class="reloadlink" onclick="reloadSecureImage();"></a></td></tr>';
updateFormLang($frmContact);
$fld = $frmContact->getField('message');
$fld->extra = "title='message'";
$fld->requirements()->setRequired(true);
$i = 0;
while ($fld = $frmContact->getFieldByNumber($i)) {
    $star = false;
    if ($i <= 5) {
        $star = true;
    }
    setRequirementFieldPlaceholder($fld, $star);
    $i++;
}
$fld = $frmContact->getField('submit');
$fld->value = t_lang('M_TXT_SUBMIT');
$fld->extra = 'class="themebtn themebtn--large"';
if ($_POST['email_address'] != "") {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $img = new Securimage();
        $post = getPostedData();
        if (!$img->check($_POST['security_code'])) {
            $msg->addError(t_lang('M_TXT_INCORRECT_SECURITY_CODE'));
            $frmContact->fill($post);
        } else {
            $rs = $db->query("select * from tbl_email_templates where tpl_id=19"); /* aDMIN */
            $row_tpl = $db->fetch($rs);
            $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
            $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
            $arr_replacements = array(
                'xxfull_namexx' => $post['full_name'],
                'xxemail_addressxx' => $post['email_address'],
                'xxmessagexx' => nl2br($post['message']),
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
            $rs = $db->query("select * from tbl_email_templates where tpl_id=20"); /* User */
            $row_tpl = $db->fetch($rs);
            $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
            $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
            $arr_replacements = array(
                'xxfull_namexx' => $post['full_name'],
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
                sendMail($post['email_address'], $subject . ' - ' . time(), emailTemplate(($message)), $headers);
            }
            /* EMAIL TO ADMIN AND USER */
            $msg->addMsg(t_lang('M_TXT_MAIL_SENT'));
        }
    }
}
require_once './header.php';
?>
<!--bodyContainer start here-->
<!--body start here-->
<section class="pagebar center">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-12">
                <h3><?php echo $page_name; ?></h3>
                <ul class="grids__half list__inline positioned__right">  
                    <li><a href="javascript:void(0)" class="themebtn link__filter"><?php echo t_lang('M_TXT_QUICK_LINKS'); ?></a></li>
                </ul>
            </aside>
        </div>
    </div>
</section>
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <div class="col-md-3">
                <div class="filter__overlay"></div>
                <div class="fixed__panel section__filter">
                    <div id="fixed__panel">
                        <div class="block__bordered">
                            <h5><?php echo t_lang('M_TXT_QUICK_LINKS'); ?></h5>
                            <ul class="links__vertical uppercase">
                                <?php echo printNav(0, 8); ?>
                            </ul>
                        </div>
                    </div>    
                </div>    
            </div>
            <div class="col-md-9">
                <div class="panel__grey">
                    <aside class="grid_2">
                        <h5> <?php echo $page_name; ?></h5>
                        <div class="formwrap">
                            <?php echo $frmContact->getFormTag(); ?>
                            <table class="formwrap__table">
                                <tr>
                                    <td><?php echo $frmContact->getFieldHtml('full_name'); ?></td>
                                    <td><?php echo $frmContact->getFieldHtml('email_address'); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <?php echo $frmContact->getFieldHtml('message'); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"><?php echo $frmContact->getFieldHtml('security_code'); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <?php echo $frmContact->getFieldHtml('submit'); ?>
                                    </td>
                                </tr>
                            </table>
                            </form> 
                            <?php echo $frmContact->getExternalJs(); ?>
                        </div>
                    </aside> 
                    <?php echo EXTRA_LOCATION_CONTACT; ?>
                </div>
                <div class="panel__grey nobackground">
                    <h5><?php echo t_lang('M_MSG_MESSAGE_TO_OUR_CUSTOMERS'); ?></h5>
                    <?php echo unescape_attr(t_lang('M_TXT_CONTACT_US_MESSAGE')); ?>
                </div>
            </div>
        </div>    
    </div>    
</section>
<?php require_once './footer.php'; ?>
 