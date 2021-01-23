<?php
require_once './application-top.php';
require_once './securimage/securimage.php';
require_once './includes/navigation-functions.php';
require_once './site-classes/class.Email.php';
if (!isset($_REQUEST['jobs_id'])) {
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . "jobs.php"));
}
if (((int) $_REQUEST['jobs_id']) <= 0) {
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . "jobs.php"));
}
$rsJob = $db->query("select * from tbl_jobs where jobs_id=" . ((int) $_GET['jobs_id']));
while ($row1 = $db->fetch($rsJob)) {
    $jobs_title = $row1['jobs_title'];
}
/* end configuration variables */
$applyjob = new Form('applyjob', 'applyjob');
$applyjob->setExtra('class="siteForm"');
$applyjob->setTableProperties('class="formwrap__table" ');
$applyjob->setFieldsPerRow(2);
$applyjob->setRequiredStarWith('placeholder');
$applyjob->captionInSameCell(true);
$applyjob->addHiddenField('', 'job_id', '', 'job_id');
$applyjob->addHiddenField('', 'mode', '', 'mode');
$applyjob->setJsErrorDisplay('afterfield');
$fld = $applyjob->addRequiredField('', 'jobs_title', $jobs_title, 'jobs_title', 'placeholder="' . t_lang('M_FRM_JOB_TITLE') . '*" title="' . t_lang('M_FRM_JOB_TITLE') . '"');
$fld->merge_cells = 2;
$applyjob->addRequiredField('', 'fname', '', 'fname', 'placeholder="' . t_lang('M_FRM_FIRST_NAME') . '*" title="' . t_lang('M_FRM_FIRST_NAME') . '"');
$applyjob->addTextBox('', 'lname', '', 'lname', 'placeholder="' . t_lang('M_FRM_LAST_NAME') . '"');
$applyjob->addEmailField('', 'jobemail', '', 'jobemail', 'placeholder="' . t_lang('M_FRM_EMAIL_ADDRESS') . '*" title="' . t_lang('M_FRM_EMAIL_ADDRESS') . '"')->requirements()->setRequired(true);
$applyjob->addRequiredField('', 'phone', '', 'phone', 'placeholder="' . t_lang('M_FRM_PHONE') . '*" title="' . t_lang('M_FRM_PHONE') . '"');
$applyjob->addRequiredField('', 'address1', '', 'address1', 'placeholder="' . t_lang('M_FRM_ADDRESS_LINE1') . '*" title="' . t_lang('M_FRM_ADDRESS_LINE1') . '"');
$applyjob->addTextBox('', 'address2', '', 'address2', 'placeholder="' . t_lang('M_FRM_ADDRESS_LINE2') . '"');
$applyjob->addTextBox('', 'address3', '', 'address3', 'placeholder="' . t_lang('M_FRM_ADDRESS_LINE3') . '"');
$applyjob->addTextBox('', 'city', '', 'city', 'placeholder="' . t_lang('M_FRM_CITY') . '"');
$applyjob->addTextBox('', 'region', '', 'region', 'placeholder="' . t_lang('M_FRM_REGION') . '"');
$applyjob->addTextBox('', 'zip', '', 'zip', 'placeholder="' . t_lang('M_FRM_ZIP') . '"');
$rs = $db->query('select country_name, country_name from tbl_countries where country_status=\'A\'');
$arr = $db->fetch_all_assoc($rs);
$applyjob->addSelectBox('', 'country', $arr, 0, '', '', 'country', 'placeholder="' . t_lang('M_FRM_COUNTRY') . '"')->requirements()->setRequired(true);
$fld = $applyjob->addFileUpload('', 'resume', 'resume', 'placeholder="' . t_lang('M_FRM_RESUME') . '*" title="' . t_lang('M_FRM_RESUME') . '" onchange= getValue(this)');
$fld->html_before_field = '<div class="fieldcover"><span id="uploadFile" class="filename">' . t_lang('M_FRM_RESUME') . '*</span>';
$fld->html_after_field = '<span class="filelabel">' . t_lang('M_FRM_BROWSE_FILE') . '</span></div>';
$fld->requirements()->setRequired(true);
$fld = $applyjob->addTextArea('', 'cover_letter', '', 'cover_letter', 'placeholder="' . t_lang('M_FRM_COVER_LETTER') . '"');
$fld->merge_cells = 2;
$applyjob->addSubmitButton('', 'btn_submit', t_lang('M_FRM_SUBMIT_YOUR_APPLICATION'), 'btn_submit', 'class="themebtn themebtn--large"');
updateFormLang($applyjob);
if ($_POST['jobemail'] != "") {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
        $img = new Securimage();
        $post = getPostedData();
        /* EMAIL TO ADMIN AND USER */
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= 'From: ' . CONF_SITE_NAME . ' <' . CONF_EMAILS_FROM . '>' . "\r\n";
        $rs = $db->query("select * from tbl_email_templates where tpl_id=39"); /* aDMIN */
        $row_tpl = $db->fetch($rs);
        $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
        $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
        $arr_replacements = array(
            'xxjobs_titlexx' => $post['jobs_title'],
            'xxfirst_namexx' => $post['fname'] . ' ' . $post['lname'],
            'xxemail_addressxx' => $post['jobemail'],
            'xxphonexx' => $post['phone'],
            'xxaddress1xx' => $post['address1'] . ' ' . $post['address2'] . ' ' . $post['address3'],
            'xxcityxx' => $post['city'],
            'xxregionxx' => $post['region'],
            'xxzipxx' => $post['zip'],
            'xxcountryxx' => $post['country'],
            'xxcover_letterxx' => nl2br($post['cover_letter']),
            'xxsite_namexx' => CONF_SITE_NAME,
            'xxserver_namexx' => $_SERVER['SERVER_NAME'],
            'xxwebrooturlxx' => CONF_WEBROOT_URL,
            'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL
        );
        foreach ($arr_replacements as $key => $val) {
            $subject = str_replace($key, $val, $subject);
            $message = str_replace($key, $val, $message);
        }
        $from = CONF_EMAILS_FROM;
        $msgMail = new Email(CONF_SITE_OWNER_EMAIL, CONF_SITE_NAME . ' ' . $from, $subject);
        $msgMail->TextOnly = false;
        $msgMail->Content = emailTemplate($message);
        if (!$_FILES['resume']['name'] == "") {
            $filehere = $_FILES['resume']['name'];
            move_uploaded_file($_FILES['resume']['tmp_name'], TEMP_XLS_PATH . $filehere);
            $msgMail->Attach(TEMP_XLS_PATH . $filehere);
        }
        $SendSuccess = $msgMail->Send();
        $rs = $db->query("select * from tbl_email_templates where tpl_id=40"); /* User */
        $row_tpl = $db->fetch($rs);
        $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
        $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
        $arr_replacements = array(
            'xxjobs_titlexx' => $post['jobs_title'],
            'xxfirst_namexx' => $post['fname'] . ' ' . $post['lname'],
            'xxemail_addressxx' => $post['jobemail'],
            'xxphonexx' => $post['phone'],
            'xxaddress1xx' => $post['address1'] . ' ' . $post['address2'] . ' ' . $post['address3'],
            'xxcityxx' => $post['city'],
            'xxregionxx' => $post['region'],
            'xxzipxx' => $post['zip'],
            'xxcountryxx' => $post['country'],
            'xxcover_letterxx' => nl2br($post['cover_letter']),
            'xxsite_namexx' => CONF_SITE_NAME,
            'xxserver_namexx' => $_SERVER['SERVER_NAME'],
            'xxwebrooturlxx' => CONF_WEBROOT_URL,
            'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL
        );
        foreach ($arr_replacements as $key => $val) {
            $subject = str_replace($key, $val, $subject);
            $message = str_replace($key, $val, $message);
        }
        $from = CONF_EMAILS_FROM;
        $msgMail = new Email($post['jobemail'], CONF_SITE_NAME . ' ' . $from, $subject);
        $msgMail->TextOnly = false;
        $msgMail->Content = emailTemplate($message);
        if (!$_FILES['resume']['name'] == "") {
            $filehere = $_FILES['resume']['name'];
            move_uploaded_file($_FILES['resume']['tmp_name'], TEMP_XLS_PATH . $filehere);
            $msgMail->Attach(TEMP_XLS_PATH . $filehere);
        }
        $SendSuccess = $msgMail->Send();
        /* EMAIL TO ADMIN AND USER */
        $msg->addMsg(t_lang('M_TXT_MAIL_SENT'));
    }
}
$rs1 = $db->query("select * from tbl_extra_values");
while ($row1 = $db->fetch($rs1)) {
    define(strtoupper($row1['extra_conf_name']), $row1['extra_conf_val' . $_SESSION['lang_fld_prefix']]);
}
require_once './header.php';
?>
<section class="pagebar center">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-12">
                <h3><?php echo t_lang('M_TXT_JOB_DETAIL'); ?></h3>
            </aside>
        </div>
    </div>
</section> 
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-12">
                <div class="panel__centered">
                    <div class="cover__grey">
                        <h4><?php echo t_lang('M_TXT_APPLY_JOB'); ?></h4>
                        <a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'jobs.php'); ?>" class="themebtn themebtn--xsmall right "><?php echo t_lang('M_TXT_BACK'); ?></a>
                        <div class="formwrap">
                            <?php
                            /* echo $msg->display(); */
                            echo $applyjob->getFormHtml();
                            ?>	
                        </div>
                    </div>
                </div>
            </aside>
        </div>    
    </div>    
</section>
<!--bodyContainer start here-->
<script type="text/javascript">
    function getValue(obj) {
        var value = $("input[name=resume]").val();
        $('.filename').text(value);
    }
</script>
<!--bodyContainer end here-->
<?php require_once './footer.php'; ?>
 