<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
require_once './includes/site-functions.php';
$verification_status = (int) $_GET['s'];
if (!isAffiliateUserLogged()) {
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'affiliate-login.php'));
}
require_once './header.php';
$affiliate_id = $_SESSION['logged_user']['affiliate_id'];
$image = $db->query("SELECT SQL_CALC_FOUND_ROWS * FROM tbl_affiliate where affiliate_id=$affiliate_id");
$row = $db->fetch($image);
$frm = getMBSFormByIdentifier('frmAffiliateAccount');
$frm->setRequiredStarWith('none');
$frm->setRequiredStarPosition('none');
$frm->setTableProperties('class="formwrap__table"');
$fld = $frm->getField('btn_submit');
$frm->fill($row);
$fld = $frm->getField('btn_submit');
$fld->value = t_lang('M_TXT_SUBMIT');
$fld = $frm->getField('affiliate_fname');
$fld->value = $row['affiliate_fname' . $_SESSION['lang_fld_prefix']];
$fld = $frm->getField('affiliate_lname');
$fld->value = $row['affiliate_lname' . $_SESSION['lang_fld_prefix']];
$fld = $frm->getField('affiliate_bussiness_name');
$fld->value = $row['affiliate_bussiness_name' . $_SESSION['lang_fld_prefix']];
$fld = $frm->getField('affiliate_address_line1');
$fld->value = $row['affiliate_address_line1' . $_SESSION['lang_fld_prefix']];
$fld = $frm->getField('affiliate_address_line2');
$fld->value = $row['affiliate_address_line2' . $_SESSION['lang_fld_prefix']];
$fld = $frm->getField('affiliate_address_line3');
$fld->value = $row['affiliate_address_line3' . $_SESSION['lang_fld_prefix']];
updateFormLang($frm);
$i = 0;
$array = array(0, 2, 3);
while ($fld = $frm->getFieldByNumber($i)) {
    $star = false;
    if (in_array($i, $array)) {
        $star = true;
    }
    if ($fld->fldType != "select") {
        setRequirementFieldPlaceholder($fld, $star);
    }
    $fld->requirements()->setCustomErrorMessage($fld->field_caption . ' is mandatory.');
    $fld->field_caption = '';
    $i++;
}
$arr['email'] = $_SESSION['logged_user']['affiliate_email_address'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    if ($post['affiliate_fname'] != "") {
        if ($frm->validate($post)) {
            $arr_updates = array(
                'affiliate_fname' => $post['affiliate_fname'],
                'affiliate_lname' => $post['affiliate_lname'],
                'affiliate_address_line1' => $post['affiliate_address_line1'],
                'affiliate_address_line2' => $post['affiliate_address_line2'],
                'affiliate_address_line3' => $post['affiliate_address_line3'],
                'affiliate_zipcode' => $post['affiliate_zipcode'],
                'affiliate_city' => $post['affiliate_city'],
                'affiliate_bussiness_name' => $post['affiliate_bussiness_name'],
                'affiliate_phone' => $post['affiliate_phone'],
                'affiliate_payment_mode' => $post['affiliate_payment_mode']
            );
            if ($post['password'] != '') {
                $arr_updates['affiliate_password'] = md5($post['password']);
            }
            $record = new TableRecord('tbl_affiliate');
            $record->setFldValue('affiliate_email_address', $_SESSION['logged_user']['affiliate_email_address']);
            $record->assignValues($arr_updates);
            if (!$record->update('affiliate_id=' . $_SESSION['logged_user']['affiliate_id'])) {
                $msg->addError($record->getError());
                $frm->fill($post);
            } else {
                $msg->addMsg(t_lang('M_TXT_INFO_UPDATED'));
                if ($post['password'] != '') {
                    $msg->addMsg(t_lang('M_TXT_PASSWORD_UPDATED'));
                }
                $_SESSION['logged_user']['affiliate_fname'] = $post['affiliate_fname'];
                redirectUser();
            }
        } else {
            $errors = $frm->getValidationErrors();
            foreach ($errors as $error) {
                $msg->addError($error);
            }
            $frm->fill($post);
        }
    }
}
if (isset($verification_status) && $verification_status > 0) {
    if ($verification_status == 1) {
        $msg->addMsg(t_lang('M_TXT_UPDATE_YOUR_PASSWORD'));
    }
}
?>
<section class="pagebar">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-7 col-sm-7">
                <h3><?php echo t_lang('M_TXT_MY_ACCOUNT') ?></h3>
                <ul class="breadcrumb">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL); ?>"><?php echo t_lang('M_TXT_HOME'); ?></a></li>
                    <li><?php echo t_lang('M_TXT_MY_ACCOUNT') ?></li>
                </ul>
            </aside>
        </div>
    </div>
</section> 
<?php require_once './left-panel-links.php';; ?> 
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <div class="col-md-12">
                <?php
                echo $frm->getFormHtml();
                ?>
            </div>
        </div>
    </div>
</section>
<?php
require_once './footer.php';
