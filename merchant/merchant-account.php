<?php
require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
if (!isCompanyUserLogged()) {
    redirectUser(CONF_WEBROOT_URL . 'merchant/login.php');
}
$company_id = $_SESSION['logged_user']['company_id'];
$image = $db->query("SELECT * FROM tbl_companies where company_id=$company_id");
$row = $db->fetch($image);
$fetchVal['company_name'] = $row['company_name' . $_SESSION['lang_fld_prefix']];
$fetchVal['company_phone'] = $row['company_phone'];
$fetchVal['company_email'] = $row['company_email'];
$fetchVal['company_url'] = $row['company_url'];
$fetchVal['company_address1'] = $row['company_address1' . $_SESSION['lang_fld_prefix']];
$fetchVal['company_address2'] = $row['company_address2' . $_SESSION['lang_fld_prefix']];
$fetchVal['company_address3'] = $row['company_address3' . $_SESSION['lang_fld_prefix']];
$fetchVal['company_city'] = $row['company_city' . $_SESSION['lang_fld_prefix']];
$fetchVal['company_state'] = $row['company_state'];
$fetchVal['company_zip'] = $row['company_zip'];
$fetchVal['company_country'] = $row['company_country'];
$fetchVal['company_profile'] = $row['company_profile' . $_SESSION['lang_fld_prefix']];
$fetchVal['company_profile_enabled'] = $row['company_profile_enabled'];
$fetchVal['company_paypal_account'] = $row['company_paypal_account'];
//$fetchVal['company_google_map'] = $row['company_google_map'];
$fetchVal['company_logo'] = $row['company_logo' . $_SESSION['lang_fld_prefix']];
$fetchVal['company_deal_commission_percent'] = $row['company_deal_commission_percent'];
$fetchVal['company_tin'] = $row['company_tin'];
$fetchVal['company_facebook_url'] = $row['company_facebook_url'];
$fetchVal['company_twitter'] = $row['company_twitter'];
$fetchVal['company_linkedin'] = $row['company_linkedin'];
$frm = getMBSFormByIdentifier('frmMerchantAccount');
$fld = $frm->getField('company_country');
$fld->extra = 'onchange="updateStates(this.value);"';
$srch = new SearchBase('tbl_states');
$srch->addCondition('state_status', '=', 'A');
$srch->addCondition('state_country', '=', $row['company_country']);
$srch->addMultipleFields(array('state_id', 'state_name'));
$rs = $srch->getResultSet();
$arr_states = $db->fetch_all_assoc($rs);
$fld = $frm->getField('company_state');
$fld->fldType = 'select';
$fld->id = 'state_id';
$fld->options = $arr_states;
$frm->changeFieldPosition(12, 9);
$fld = $frm->getField('company_city');
$frm->changeFieldPosition(10, 11);
$fld = $frm->getField('company_zip');
$fld = $frm->getField('company_address1');
$frm->changeFieldPosition($fld->getFormIndex(), $fld->getFormIndex() + 5);
$fld = $frm->getField('company_address2');
$frm->changeFieldPosition($fld->getFormIndex(), $fld->getFormIndex() + 5);
$fld = $frm->getField('company_address3');
$frm->changeFieldPosition($fld->getFormIndex(), $fld->getFormIndex() + 5);
$fld = $frm->getField('company_active');
$frm->removeField($fld);
$fld = $frm->getField('company_google_map');
$frm->removeField($fld);
if (CONF_ADMIN_COMMISSION_TYPE == 1 || CONF_ADMIN_COMMISSION_TYPE == 2) {
    $fld = $frm->getField('company_deal_commission_percent');
    $frm->removeField($fld);
} else {
    $fld = $frm->getField('company_deal_commission_percent');
    $fld->extra = "readOnly";
    $fld->html = '<strong>' . $fld->value . '</strong>';
    $fld->field_caption = t_lang('M_TXT_ADMIN_/PORTAL_FEE') . ' <span>(%)</span>';
}
$fld = $frm->getField('company_profile');
$fld->merge_cells = '3';
$fld = $frm->getField('company_logo');
$fld->extra = ' title="' . t_lang("M_TXT_RECOMMENDED_LOGO_DIMENSION") . ' 16:9"';
$fld->extra = 'onchange="readURL(this);"';
$logo_file = COMPANY_LOGO_URL . $row['company_logo' . $_SESSION['lang_fld_prefix']];
if ($row['company_logo' . $_SESSION['lang_fld_prefix']] == "") {
    $logo_file = CONF_WEBROOT_URL . 'images/defaultLogo.jpg';
}
$fld->html_after_field = '<div ><img src="' . $logo_file . '" width="75" height="75" class="deal_image"></div>';
$fld = $frm->getField('company_profile');
$fld->html_before_field = '<div class="frm-editor">';
$fld->html_after_field = '</div>';
$arr = $_SESSION['logged_user'];
$fetchVal['company_email'] = '<strong>' . $_SESSION['logged_user']['company_email'] . '</strong>';
$fld = $frm->addRequiredField(t_lang('M_TXT_TIN'), 'company_tin');
$frm->changeFieldPosition($fld->getFormIndex(), 15);
$fld = $frm->getField('company_phone');
$fld->requirements()->setRequired(false);
$fld = $frm->addTextBox(t_lang('M_TXT_FACEBOOK_URL'), 'company_facebook_url');
$frm->changeFieldPosition($fld->getFormIndex(), 16);
$fld = $frm->addTextBox(t_lang('M_TXT_TWITTER_USERNAME'), 'company_twitter');
$frm->changeFieldPosition($fld->getFormIndex(), 17);
$fld = $frm->addTextBox(t_lang('M_TXT_LINKED_IN'), 'company_linkedin');
$frm->changeFieldPosition($fld->getFormIndex(), 18);
$frm->fill($fetchVal);
updateFormLang($frm);
$fld = $frm->getField('submit');
$fld->value = t_lang('M_TXT_SUBMIT');
$fld->getFormIndex();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    if ($post['company_name'] != "") {
        if ($frm->validate($post)) {
            $succeed = true;
            /* Image Validations if uploaded */
            if (is_uploaded_file($_FILES['company_logo']['tmp_name'])) {
                $ext = strtolower(strrchr($_FILES['company_logo']['name'], '.'));
                if ((!in_array($ext, array('.gif', '.jpg', '.jpeg', '.png'))) || ($_FILES['company_logo']['size'] > CONF_IMAGE_MAX_SIZE)) {
                    $msg->addError(t_lang('M_TXT_COMPANY_LOGO') . ' ' . t_lang('M_TXT_IMAGE_NOT_SUPPORTED_OR_SIZE_EXCEEDED'));
                    $succeed = false;
                    fillForm($frm, $post);
                }
            }
            if (true === $succeed) {
                $arr_updates = array(
                    'company_name' => $post['company_name'],
                    'company_phone' => $post['company_phone'],
                    'company_url' => $post['company_url'],
                    'company_address1' => $post['company_address1'],
                    'company_address2' => $post['company_address2'],
                    'company_address3' => $post['company_address3'],
                    'company_city' => $post['company_city'],
                    'company_state' => $post['company_state'],
                    'company_zip' => $post['company_zip'],
                    'company_country' => $post['company_country'],
                    'company_profile' => $post['company_profile'],
                    'company_profile_enabled' => $post['company_profile_enabled'],
                    'company_paypal_account' => $post['company_paypal_account'],
                    'company_deal_commission_percent' => $post['company_deal_commission_percent'],
                    'company_tin' => $post['company_tin'],
                    'company_facebook_url' => $post['company_facebook_url'],
                    'company_twitter' => $post['company_twitter'],
                    'company_linkedin' => $post['company_linkedin'],
                );
                if ($post['company_password'] != '') {
                    $arr_updates['company_password'] = md5($post['company_password']);
                }
                if ($post['company_logo' . $_SESSION['lang_fld_prefix']] != '') {
                    $arr_updates['company_logo' . $_SESSION['lang_fld_prefix']] = ($post['company_logo' . $_SESSION['lang_fld_prefix']]);
                }
                $record = new TableRecord('tbl_companies');
                $arr_lang_independent_flds = array('company_id', 'company_password', 'company_email', 'company_tin', 'company_state', 'company_phone', 'company_url', 'company_zip', 'company_country', 'company_profile_enabled', 'company_paypal_account', 'company_active', 'company_deleted', 'mode', 'btn_submit', 'company_deal_commission_percent', 'company_facebook_url', 'company_twitter', 'company_linkedin');
                assignValuesToTableRecord($record, $arr_lang_independent_flds, $arr_updates);
                $company_id = $_SESSION['logged_user']['company_id'];
                if (!$record->update('company_id=' . $_SESSION['logged_user']['company_id'])) {
                    $msg->addError($record->getError());
                    fillForm($frm, $post);
                } else {
                    ################### COMPANY LOGO ###################
                    if (($_FILES['company_logo']['tmp_name'] != "") && is_uploaded_file($_FILES['company_logo']['tmp_name'])) {
                        $flname = time() . '_' . $_FILES['company_logo']['name'];
                        if (!move_uploaded_file($_FILES['company_logo']['tmp_name'], COMPANY_LOGO_PATH . $flname)) {
                            $msg->addError(t_lang('M_TXT_FILE_COULD_NOT_SAVE'));
                        } else {
                            if ($row['company_logo'] != "") {
                                unlink(COMPANY_LOGO_PATH . $row['company_logo']);
                            }
                            $db->update_from_array('tbl_companies', array('company_logo' . $_SESSION['lang_fld_prefix'] => $flname), 'company_id=' . $company_id);
                        }
                    }
                    ################### COMPANY LOGO END ###################
                    if ($post['company_password'] != '') {
                        $msg->addMsg(t_lang('M_TXT_PASSWORD_AND_INFO_UPDATED'));
                    } else {
                        $msg->addMsg(t_lang('M_TXT_INFO_UPDATED'));
                    }
                    $_SESSION['logged_user']['company_name'] = $post['company_name'];
                    redirectUser();
                }
            }
        } else {
            $errors = $frm->getValidationErrors();
            foreach ($errors as $error) {
                $msg->addError($error);
            }
            fillForm($frm, $post);
        }
    }
}
require_once './header.php';
$arr_bread = array('' => t_lang('M_TXT_MY_ACCOUNT'));
?>
</div></td>
<td class="right-portion"><?php echo getMerchantBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_MY_ACCOUNT'); ?> </div>
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?>
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide();
                    return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="redtext"><?php echo $msg->display(); ?> </div>
                    <br/>
                    <br/>
                    <?php
                }
                if (isset($_SESSION['msgs'][0])) {
                    ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
    <div class="box"><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
</td>
<script type="text/javascript">     var selectedState = 0;</script>
<?php
require_once './footer.php';
exit;
