<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
/* define configuration variables */
$rs1 = $db->query("select * from tbl_extra_values");
while ($row1 = $db->fetch($rs1)) {
    define(strtoupper($row1['extra_conf_name']), $row1['extra_conf_val' . $_SESSION['lang_fld_prefix']]);
}
/* end configuration variables */
$get = getQueryStringData();
$frm = getMBSFormByIdentifier('frmSuggestbussiness');
$fld = $frm->getField('br_address');
$fld->id = "br_address";
$fld->requirements()->setLength(5, 60);
$fld = $frm->getField('br_review');
$fld->id = "br_review";
$fld->requirements()->setLength(20, 500);
$fld = $frm->getField('br_person_lname');
$fld->requirements()->setRequired();
$fld->setRequiredStarWith('caption');
$fld->extra = "title='Last Name'";
$fld->field_caption = 'Your Last Name';
//select cat_name, cat_name from tbl_deal_categories order by cat_name
$arr = [];
$rs = $db->query('select cat_name' . $_SESSION['lang_fld_prefix'] . ' from tbl_deal_categories where cat_parent_id= 0 order by cat_name' . $_SESSION['lang_fld_prefix']);
while ($row = $db->fetch($rs)) {
    $arr[$row['cat_name' . $_SESSION['lang_fld_prefix']]] = '<i class="input-helper"></i>' . $row['cat_name' . $_SESSION['lang_fld_prefix']];
}
$fld = $frm->getField('br_category_type');
$fld->table_cols = 4;
$fld->options = $arr;
updateFormLang($frm);
$i = 0;
while ($fld = $frm->getFieldByNumber($i)) {
    $star = false;
    if ($i <= 9) {
        $star = true;
    }
    setRequirementFieldPlaceholder($fld, $star, 'Business Website');
    $i++;
}
$fld = $frm->getField('Submit');
$fld->extra = 'class="themebtn themebtn--large"';
$fld->value = t_lang('M_TXT_SUBMIT');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    if (!$frm->validate($post)) {
        $errors = getValidationErrMsg($frm);
        foreach ($errors as $error) {
            $msg->addError($error);
        }
    }
    if ($_POST['br_name'] != "") {
        $post = getPostedData();
        if (!$frm->validate($post)) {
            $errors = getValidationErrMsg($frm);
            foreach ($errors as $error) {
                $msg->addError($error);
            }
        } else {
            $record = new TableRecord('tbl_business_referral');
            $record->assignValues($post);
            $br_category_type = implode(",", $post['br_category_type']);
            $record->setFldValue('br_category_type', $br_category_type, '');
            $success = ($post['br_id'] > 0) ? $record->update('br_id=' . $post['br_id']) : $record->addNew();
            if ($success) {
                /* EMAIL TO ADMIN AND USER */
                /* $headers  = 'MIME-Version: 1.0' . "\r\n";
                  $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                  $headers .= 'From: ' . CONF_SITE_NAME . ' <' . CONF_EMAILS_FROM . '>' . "\r\n"; */
                $rs = $db->query("select * from tbl_email_templates where tpl_id=23"); /* aDMIN */
                $row_tpl = $db->fetch($rs);
                $total = count($post['br_category_type']);
                $count = 0;
                foreach ($post['br_category_type'] as $val) {
                    $count++;
                    $type .= $val;
                    if ($count != $total)
                        $type .= ', ';
                }
                $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
                $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
                $arr_replacements = array(
                    'xxfull_namexx' => $post['br_person_name'] . " " . $post['br_person_lname'],
                    'xxemail_addressxx' => $post['br_email'],
                    'xxphonexx' => $post['br_phone'],
                    'xxbusiness_namexx' => $post['br_name'],
                    'xxbusiness_websitexx' => $post['br_website'],
                    'xxbusiness_zipxx' => $post['br_zip'],
                    'xxbusiness_countryxx' => $post['br_country'],
                    'xxbusiness_catagoryxx' => $type,
                    'xxbusiness_addressxx' => nl2br($post['br_address']),
                    'xxbusiness_featurexx' => nl2br($post['br_review']),
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
                    sendMail(CONF_SITE_OWNER_EMAIL, $subject, emailTemplate(($message)), $headers);
                }
                $rs = $db->query("select * from tbl_email_templates where tpl_id=24"); /* User */
                $row_tpl = $db->fetch($rs);
                $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
                $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
                $arr_replacements = array(
                    'xxfull_namexx' => $post['br_person_name'] . " " . $post['br_person_lname'],
                    'xxemail_addressxx' => $post['br_email'],
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
                    sendMail($post['br_email'], $subject, emailTemplate(($message)), $headers);
                }
                /* EMAIL TO ADMIN AND USER */
                $msg->addMsg(t_lang('M_TXT_MAIL_SENT'));
                redirectUser('?');
            } else {
                $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
                $frm->fill($post);
            }
        }
    }
}
require_once './header.php';
?>
<!--bodyContainer start here-->
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
                    <?php echo $frm->getFormTag(); ?>
                    <div class="cover__form">
                        <h6><?php echo t_lang('M_FRM_BUSINESS_CONTACT_HEADING'); ?></h6>
                        <div class="formwrap">
                            <table class="formwrap__table">
                                <tr>
                                    <td><?php echo $frm->getFieldHtml('br_person_name'); ?></td>
                                    <td><?php echo $frm->getFieldHtml('br_person_lname'); ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo $frm->getFieldHtml('br_email'); ?></td>
                                    <td><?php echo $frm->getFieldHtml('br_phone'); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="cover__form">    
                        <h6><?php echo t_lang('M_FRM_BUSINESS_INFO_HEADING'); ?></h6>
                        <div class="formwrap">
                            <table class="formwrap__table">
                                <tr>
                                    <td><?php echo $frm->getFieldHtml('br_name'); ?></td>
                                    <td><?php echo $frm->getFieldHtml('br_website'); ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo $frm->getFieldHtml('br_country'); ?></td>
                                    <td><?php echo $frm->getFieldHtml('br_zip'); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <p class="txt__uppercase"><?php echo unescape_attr(t_lang('M_FRM_BUSINESS_CATEGORY_TYPE')); ?></p>	
                                        <?php echo html_entity_decode($frm->getFieldHtml('br_category_type')); ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>  
                    <div class="cover__form">
                        <h6><?php echo t_lang('M_FRM_BUSINESS_MORE_INFORMATION'); ?></h6>
                        <div class="formwrap">
                            <table class="formwrap__table">
                                <tr>
                                    <td><?php echo $frm->getFieldHtml('br_address'); ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo $frm->getFieldHtml('br_review'); ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo $frm->getFieldHtml('Submit'); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>   
                    <?php echo $frm->getExternalJS(); ?>   
                    </form> 
                </div>
            </div>
        </div>    
    </div>    
</section>
<!--bodyContainer end here-->
<script type="text/javascript">
    $(document).ready(function () {
        $('#br_review').on('input propertychange', function () {
            CharLimit(this, 500);
        });
        $('#br_address').on('input propertychange', function () {
            CharLimit(this, 60);
        });
    });
    function CharLimit(input, maxChar) {
        var len = $(input).val().length;
        if (len > maxChar) {
            $(input).val($(input).val().substring(0, maxChar));
        }
    }
</script>	
<?php require_once './footer.php'; ?>
