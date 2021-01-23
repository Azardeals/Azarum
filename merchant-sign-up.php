<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
if (isCompanyUserLogged()) {
    redirectUser(CONF_WEBROOT_URL . 'merchant/merchant-account.php');
}
if ($_GET['rep']) {
    $company_rep_id = $_GET['rep'];
    $repData = $db->query("select rep_fname,rep_lname,rep_email_address from tbl_representative where rep_status=1 AND rep_id=" . $_GET['rep']);
    $rowRep = $db->fetch($repData);
    $repName = $rowRep['rep_fname'] . ' ' . $rowRep['rep_lname'];
}
$rscountry = $db->query("select country_id, country_name" . $_SESSION['lang_fld_prefix'] . " as country_name from tbl_countries where country_status='A' order by country_name" . $_SESSION['lang_fld_prefix']);
$countryArray = ["" => html_entity_decode(t_lang('M_TEXT_SELECT'))];
while ($arrs = $db->fetch($rscountry)) {
    $countryArray[$arrs['country_id']] = $arrs['country_name'];
}
$frm = getMBSFormByIdentifier('frmMerchantSignUp');
$frm->setValidatorJsObjectName('merchantFormValidator');
$frm->captionInSameCell(true);
$frm->getRequiredStarPosition();
$frm->setRequiredStarWith('none');
$frm->setRequiredStarPosition('none');
$frm->addHiddenField('', 'company_rep_id', $company_rep_id, 'company_rep_id');
$fld = $frm->getField('company_name');
$fld->extra = 'placeholder="' . t_lang('M_TXT_COMPANY_NAME') . ' *"';
$fld->setRequiredStarPosition('none');
$fld = $frm->getField('company_email');
$fld->extra = "placeholder=" . t_lang('M_TXT_EMAIL') . "*";
$fld->setRequiredStarPosition('none');
$fld = $frm->getField('company_password');
$fld->extra = "placeholder=" . t_lang('M_TXT_PASSWORD') . "*";
$fld->setRequiredStarPosition('none');
$fld = $frm->getField('confirm_password');
$fld->extra = "placeholder='" . t_lang('M_TXT_CONFIRM_PASSWORD') . "*'";
$fld->setRequiredStarPosition('none');
$fld = $frm->getField('company_phone');
$fld->extra = 'placeholder="' . t_lang('M_TXT_COMPANY_PHONE') . '"';
$fld = $frm->getField('company_address1');
$fld->extra = 'placeholder="' . t_lang('M_TXT_COMPANY_ADDRESS1') . '*"';
$fld->setRequiredStarPosition('none');
$fld = $frm->getField('company_address2');
$fld->extra = 'placeholder="' . t_lang('M_TXT_COMPANY_ADDRESS2') . '"';
$fld = $frm->getField('company_address3');
$fld->extra = 'placeholder="' . t_lang('M_TXT_COMPANY_ADDRESS3') . '"';
$fld = $frm->getField('company_city');
$fld->extra = 'placeholder="' . t_lang('M_TXT_COMPANY_CITY') . '*"';
$fld->setRequiredStarPosition('none');
$fld = $frm->getField('company_zip');
$fld->extra = 'placeholder="' . t_lang('M_TXT_COMPANY_ZIP') . '*"';
$fld->setRequiredStarPosition('none');
$fld = $frm->getField('company_url');
$fld->extra = 'placeholder="' . t_lang('M_TXT_COMPANY_URL') . '"';
$fld = $frm->getField('company_logo');
$fld->extra = 'onchange="getValue(this)" title="' . t_lang("M_TXT_RECOMMENDED_LOGO_DIMENSION") . ' 16:9"';
$fld = $frm->getField('btn_submit');
$fld->extra = 'class="themebtn themebtn--large"';
$fld->value = t_lang('M_TXT_SIGN_UP');
$fld = $frm->getField('company_country');
$fld->setRequiredStarPosition('none');
$fld->options = $countryArray;
$fld->extra = 'onchange="updateStates(this.value);"';
$state = array("" => html_entity_decode(t_lang('M_TEXT_SELECT_COUNTRY_FIRST')));
$fld = $frm->getField('company_state');
$fld->setRequiredStarPosition('none');
$fld->fldType = 'select';
$fld->id = 'state_id';
$fld->options = $state;
$fld = $frm->getField('agree_terms');
$fld->setRequiredStarPosition('none');
$urlTerm = CONF_WEBROOT_URL . 'terms.php';
$urlPrivacy = CONF_WEBROOT_URL . 'privacy.php';
$fld->html_after_field = '<i class="input-helper"></i>' . t_lang('M_TXT_BY_REGISTER_YOU_AGREE') . ' <a href="' . $urlTerm . '" target="_blank">' . t_lang('M_TXT_TERMS_OF_USE') . '</a> and <a href="' . $urlPrivacy . '" target="_blank">' . t_lang('M_TXT_PRIVACY_POLICY') . '</a>';
$fld->extra = 'title="' . t_lang('M_FRM_CHECKBOX_TERMS') . '"';
$frm->setRequiredStarWith('none');
$frm->setRequiredStarPosition('none');
$frm->setOnSubmit('return merchantFormSubmit(this,merchantFormValidator); ');
updateFormLang($frm);
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
    $post = getPostedData();
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error) {
            $msg->addError($error);
        }
    } else {
        $succeed = true;
        /* Image Validations if uploaded */
        if (is_uploaded_file($_FILES['company_logo']['tmp_name'])) {
            $ext = strtolower(strrchr($_FILES['company_logo']['name'], '.'));
            if ((!in_array($ext, array('.gif', '.jpg', '.jpeg', '.png'))) || ($_FILES['company_logo']['size'] > CONF_IMAGE_MAX_SIZE)) {
                $msg->addError(t_lang('M_TXT_MERCHANT') . ' ' . t_lang('M_TXT_IMAGE_NOT_SUPPORTED_OR_SIZE_EXCEEDED'));
                $succeed = false;
            }
        }
        if (true === $succeed) {
            $record = new TableRecord('tbl_companies');
            /* $record->assignValues($post); */
            $arr_lang_independent_flds = array('company_id', 'company_password', 'company_email', 'company_phone', 'company_url', 'company_zip', 'company_country', 'company_rep_id', 'btn_submit');
            assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
            $record->setFldValue('company_password', md5($post['company_password']));
            $record->setFldValue('company_active', 0);
            $success = ($post['company_id'] > 0) ? $record->update('company_id' . '=' . $post['company_id']) : $record->addNew();
            if ($success) {
                $company_id = ($post['company_id'] > 0) ? $post['company_id'] : $record->getId();
                if ($post['company_id'] == "") {
                    ########## Email #####################
                    /* $headers  = 'MIME-Version: 1.0' . "\r\n";
                      $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                      $headers .= 'From: ' . CONF_SITE_NAME . ' <' . CONF_EMAILS_FROM . '>' . "\r\n"; */
                    $rs = $db->query("select * from tbl_email_templates where tpl_id=34");
                    $row_tpl = $db->fetch($rs);
                    $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
                    $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
                    $arr_replacements = array(
                        'xxcompany_namexx' => $post['company_name'],
                        'xxuser_namexx' => $post['company_email'],
                        'xxemail_addressxx' => $post['company_email'],
                        'xxloginurlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'merchant/',
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
                        sendMail($post['company_email'], $subject, emailTemplateSuccess($message), $headers);
                    }
                    $rs = $db->query("select * from tbl_email_templates where tpl_id=35");
                    $row_tpl = $db->fetch($rs);
                    $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
                    $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
                    $arr_replacements = array(
                        'xxcompany_namexx' => $post['company_name'],
                        'xxuser_namexx' => $post['company_email'],
                        'xxemail_addressxx' => $post['company_email'],
                        'xxpasswordxx' => str_repeat("*", strlen($post['company_password'])),
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
                        sendMail(CONF_SITE_OWNER_EMAIL, $subject, emailTemplateSuccess($message), $headers);
                    }
                    ##############################################
                }
                ################### COMPANY LOGO ###################
                if (is_uploaded_file($_FILES['company_logo']['tmp_name'])) {
                    $flname = time() . '_' . $_FILES['company_logo']['name'];
                    if (!move_uploaded_file($_FILES['company_logo']['tmp_name'], COMPANY_LOGO_PATH . $flname)) {
                        $msg->addError(t_lang('M_TXT_FILE_COULD_NOT_SAVE'));
                    } else {
                        $getImg = $db->query("select * from tbl_companies where company_id='" . $company_id . "'");
                        $imgRow = $db->fetch($getImg);
                        unlink(COMPANY_LOGO_PATH . $imgRow['company_logo' . $_SESSION['lang_fld_prefix']]);
                        $db->update_from_array('tbl_companies', array('company_logo' . $_SESSION['lang_fld_prefix'] => $flname), 'company_id=' . $company_id);
                    }
                }
                ################### COMPANY LOGO END ###################
                $msg->addMsg(t_lang('M_TXT_REGISTERATION_SUCCESSFUL'));
                redirectUser();
            } else {
                $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
            }
        }
        $selectedState = $post['company_state'];
        $company_country = $post['company_country'];
        fillForm($frm, $post);
    }
}
require_once './header.php';
?>
<!--bodyContainer start here-->
<section class="pagebar center">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-12">
                <h3><?php echo t_lang('M_TXT_MERCHANT_SIGN_UP'); ?></h3>
                <?php if ($_GET['rep']) { ?>
                    <h3 style="float:right;"><?php echo t_lang('M_TXT_REFERRED_BY') . ': ' . $repName; ?></h3>
                <?php } ?>
            </aside>
        </div>
    </div>
</section>
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel__centered">
                    <div class="panel__grey">
                        <?php echo $frm->getFormTag(); ?>
                        <div class="formwrap">
                            <table class="formwrap__table">
                                <tr>
                                    <td><?php echo $frm->getFieldHtml('company_name'); ?></td>
                                    <td><?php echo $frm->getFieldHtml('company_email'); ?>
                                        <?php echo $frm->getFieldHtml('company_rep_id'); ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo $frm->getFieldHtml('company_password'); ?></td>
                                    <td><?php echo $frm->getFieldHtml('confirm_password'); ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo $frm->getFieldHtml('company_phone'); ?></td>
                                    <td><?php echo $frm->getFieldHtml('company_address1'); ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo $frm->getFieldHtml('company_address2'); ?></td>
                                    <td><?php echo $frm->getFieldHtml('company_address3'); ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo $frm->getFieldHtml('company_country'); ?></td>
                                    <td><?php echo $frm->getFieldHtml('company_state'); ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo $frm->getFieldHtml('company_city'); ?></td>
                                    <td><?php echo $frm->getFieldHtml('company_zip'); ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo $frm->getFieldHtml('company_url'); ?></td>
                                    <td>
                                        <div class="fieldcover">
                                            <span id="uploadFile" class="filename"><?php echo t_lang('M_TXT_COMPANY_LOGO'); ?></span>
                                            <?php echo $frm->getFieldHtml('company_logo'); ?>
                                            <span class="filelabel"><?php echo t_lang('M_TXT_BROWSE_FILE'); ?></span>
                                        </div>  
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <label class="checkbox">
                                            <?php echo $frm->getFieldHtml('agree_terms'); ?>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="merchant__signup__action">
                                        <span class="btn__merchant__signup"><?php echo $frm->getFieldHtml('btn_submit'); ?></span> <span class="merchant__signin__links"> <?php echo t_lang('M_TXT_OR'); ?> <a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'merchant'); ?>"><?php echo t_lang('M_TXT_SIGN_IN_HERE'); ?></a></span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <?php echo $frm->getExternalJs(); ?>
                        </form> 
                    </div>
                </div>
            </div>
        </div>    
    </div>    
</section>
<script type="text/javascript">
    var selectedState = '<?php echo ($selectedState > 0 ? $selectedState : 0); ?>';
    var company_country = '<?php echo ($company_country > 0 ? $company_country : 0); ?>';
    var value = '<?php echo t_lang("M_TXT_SELECT_COUNTRY_FIRST"); ?>';
    var selectCountryFirst = '<option value="">' + value + '</option>';
    updateStates(<?php echo $company_country; ?>);
    function getValue(obj) {
        var value = $("input[name=company_logo]").val();
        $('.filename').text(value);
    }
    function merchantFormSubmit(frm, v)
    {
        if ($('#frm_mbs_id_frmMerchantSignUp').hasClass('submitted')) {
            return false;
        }
        v.validate();
        if (!v.isValid()) {
            return false;
        }
        $('#frm_mbs_id_frmMerchantSignUp').addClass('submitted');
    }
</script>
<?php
require_once './footer.php';
?>