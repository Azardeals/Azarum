<?php
require_once '../application-top.php';
require_once '../includes/navigation-functions.php';

if (!isRepresentativeUserLogged())
    redirectUser(CONF_WEBROOT_URL . 'representative/login.php');

$rep_id = $_SESSION['logged_user']['rep_id'];
$repersentative = $db->query("SELECT SQL_CALC_FOUND_ROWS * FROM tbl_representative where rep_id='$rep_id'");
$row = $db->fetch($repersentative);

$fetchVal['rep_fname'] = $row['rep_fname' . $_SESSION['lang_fld_prefix']];
$fetchVal['rep_lname'] = $row['rep_lname'];
$fetchVal['rep_email_address'] = $row['rep_email_address'];

$fetchVal['rep_address_line1'] = $row['rep_address_line1' . $_SESSION['lang_fld_prefix']];
$fetchVal['rep_address_line2'] = $row['rep_address_line2' . $_SESSION['lang_fld_prefix']];
$fetchVal['rep_address_line3'] = $row['rep_address_line3' . $_SESSION['lang_fld_prefix']];
$fetchVal['rep_zipcode'] = $row['rep_zipcode' . $_SESSION['lang_fld_prefix']];
$fetchVal['rep_city'] = $row['rep_city' . $_SESSION['lang_fld_prefix']];
$fetchVal['rep_bussiness_name'] = $row['rep_bussiness_name'];
$fetchVal['rep_phone'] = $row['rep_phone'];
$fetchVal['rep_payment_mode'] = $row['rep_payment_mode' . $_SESSION['lang_fld_prefix']];
$fetchVal['rep_paypal_id'] = $row['rep_paypal_id'];


/* $frm=getMBSFormByIdentifier('frmCompanies');

  $fld = $frm->getField('company_active');
  $frm->removeField($fld);

  $fld = $frm->getField('company_profile');
  $fld->merge_cells='3';
  $fld = $frm->getField('submit');
  $fld->extra='class="inputbuttons"';

  $arr=$_SESSION['logged_user'];
  $fetchVal['company_email']='<strong>' . $_SESSION['logged_user']['company_email'] . '</strong>';

  $frm->fill($fetchVal);
  updateFormLang($frm); */

$frm = new Form('frmRepresentative', 'frmRepresentative');
$frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$frm->setFieldsPerRow(1);
$frm->captionInSameCell(false);
$frm->setJsErrorDisplay('afterfield');
$frm->setValidatorJsObjectName('frmValidator');

$frm->addRequiredField('M_FRM_FIRST_NAME', 'rep_fname', '', 'rep_fname', '');
$frm->addTextBox('M_FRM_LAST_NAME', 'rep_lname', '', 'rep_lname', '');
$frm->addHTML('M_FRM_EMAIL_ADDRESS', 'rep_email_address', '', false);
$frm->addTextBox('M_FRM_BUSINESS_NAME', 'rep_bussiness_name', '', 'rep_bussiness_name', '');
$frm->addRequiredField('M_FRM_ADDRESS_LINE1', 'rep_address_line1', '', 'rep_address_line1', '');
$frm->addTextBox('M_FRM_ADDRESS_LINE2', 'rep_address_line2', '', 'rep_address_line2', '');
$frm->addTextBox('M_FRM_ADDRESS_LINE3', 'rep_address_line3', '', 'rep_address_line3', '');

/* $frm->addSelectBox( 'M_FRM_COUNTRY', 'rep_country', $countryArray,'','onchange="updateStates(this.value);" class="medium"','', 'rep_country');
  $fld = $frm->addHtml( 'M_FRM_STATE', 'state', '<span id="spn-state"></span>',false);

  $frm->addTextBox('M_FRM_CITY','rep_city','','rep_city','');
  $frm->addTextBox('M_FRM_COMMISSION','rep_commission','','rep_commission',''); */
$frm->addTextBox('M_FRM_ZIP_CODE', 'rep_zipcode', '', 'rep_zipcode', '');
/* $fld = $frm->addEmailField('M_FRM_EMAIL_ADDRESS','rep_email_address','','rep_email_address',''); */
/* $fld->setUnique( 'tbl_representative', 'rep_email_address', 'rep_id', 'rep_id', 'rep_id'); */
/* $fld->Requirements()->setRequired(); */
$frm->addTextBox('M_FRM_PHONE_NO', 'rep_phone', '', 'rep_phone', '');
$frm->addPasswordField('M_FRM_PASSWORD', 'password', '', 'password', '');
$fld = $frm->addPasswordField('M_FRM_CONFIRM_PASSWORD', 'cpassword', '', 'cpassword', '');
$fld->requirements()->setCompareWith('password', 'eq', 'M_FRM_PASSWORD');
$frm->addHiddenField('', 'rep_id', '', 'rep_id', '');
$frm->addTextBox('M_FRM_PAYPAL_ID', 'rep_paypal_id', '', 'rep_paypal_id', '');

$fld = $frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SUBMIT'), '', ' class="medium"');
$selected_state = 0;
$arr = $_SESSION['logged_user'];
$fetchVal['rep_email_address'] = '<strong>' . $_SESSION['logged_user']['rep_email_address'] . '</strong>';

$frm->fill($fetchVal);
updateFormLang($frm);


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();

    if ($post['rep_fname'] != "") {
        if ($frm->validate($post)) {
            $arr_updates = array(
                'rep_fname' => $post['rep_fname'],
                'rep_lname' => $post['rep_lname'],
                'rep_address_line1' => $post['rep_address_line1'],
                'rep_address_line2' => $post['rep_address_line2'],
                'rep_address_line3' => $post['rep_address_line3'],
                'rep_zipcode' => $post['rep_zipcode'],
                'rep_city' => $post['rep_city'],
                'rep_bussiness_name' => $post['rep_bussiness_name'],
                'rep_phone' => $post['rep_phone'],
                'rep_payment_mode' => $post['rep_payment_mode'],
                'rep_paypal_id' => $post['rep_paypal_id']
            );

            if ($post['password'] != '')
                $arr_updates['rep_password'] = md5($post['password']);

            $record = new TableRecord('tbl_representative');
            $record->setFldValue('rep_email_address', $_SESSION['logged_user']['rep_email_address']);
            $record->assignValues($arr_updates);

            if (!$record->update('rep_id=' . $_SESSION['logged_user']['rep_id'])) {
                $msg->addError($record->getError());
                $frm->fill($post);
            } else {
                if ($post['password'] != ''){
                    $msg->addMsg(t_lang('M_TXT_PASSWORD_AND_INFO_UPDATED'));
                }else{
                    $msg->addMsg(t_lang('M_TXT_INFO_UPDATED'));
                }

                $_SESSION['logged_user']['rep_fname'] = $post['rep_fname'];
                redirectUser();
            }
        }
        else {
            $errors = $frm->getValidationErrors();
            foreach ($errors as $error)
                $msg->addError($error);
            $frm->fill($post);
        }
    }
}


require_once './header.php';
$arr_bread = array(
    'my-account.php' => '<img class="home" alt="Home" src="images/home-icon.png">',

);
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread);   ?>
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
    <div class="box"> <div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
</td>
<?php
require_once './footer.php';
exit;
?>
