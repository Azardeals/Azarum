<?php
require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
if ($_SESSION['cityname'] != "") {
    $cityname = $_SESSION['cityname'];
} else {
    $cityname = 1;
}
if (!isCompanyUserLogged()) {
    redirectUser(CONF_WEBROOT_URL . 'merchant/login.php');
}
$company_id = $_SESSION['logged_user']['company_id'];
$frm = getMBSFormByIdentifier('frmCityAdmin');
$fld = $frm->getField('city_active');
$frm->removeField($fld);
$fld = $frm->getField('city_facebook_url');
$frm->removeField($fld);
$fld = $frm->getField('city_twitter_url');
$frm->removeField($fld);
$fld = $frm->getField('city_bg_image');
$frm->removeField($fld);
$fld = $frm->getField('city_country');
$fld->selectCaption = t_lang('M_TXT_SELECT');
updateFormLang($frm);
$fld = $frm->getField('btn_submit');
$fld->value = t_lang('M_TXT_SUBMIT');
$fld = $frm->getField('city_deal_commission_percent');
$frm->removeField($fld);
$company_name = $_SESSION['logged_user']['company_name'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    if ($frm->validate($post)) {
        $arr_updates = array(
            'city_name' => $post['city_name'],
            'city_state' => $post['city_state'],
            'city_code' => $post['city_code'],
            'city_facebook_url' => $post['city_facebook_url'],
            'city_twitter_url' => $post['city_twitter_url'],
            'city_id' => $post['city_id']
        );
        $record = new TableRecord('tbl_cities');
        $arr_lang_independent_flds = array('city_id', 'city_state', 'city_facebook_url', 'city_twitter_url', 'city_active', 'city_request', 'city_requested_id', 'city_deleted', 'mode', 'btn_submit');
        assignValuesToTableRecord($record, $arr_lang_independent_flds, $arr_updates);
        $record->setFldValue('city_request', 1);
        $record->setFldValue('city_active', 1);
        $record->setFldValue('city_requested_id', $_SESSION['logged_user']['company_id']);
        $success = ($post['city_id'] > 0) ? $record->update('city_id=' . $post['city_id']) : $record->addNew();
        if ($success) {
            $msg->addMsg(t_lang('M_MSG_CITY_REQUESTED_SUCCESSFULLY'));
            $rs = $db->query("select * from tbl_email_templates where tpl_id=14");
            $row_tpl = $db->fetch($rs);
            $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
            $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
            $arr_replacements = array(
                'xxname_of_companyxx' => $company_name,
                'xxcity_namexx' => $post['city_name'],
                'xxwebsiteurlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
                'xxcity_codexx' => $post['city_code'],
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
                sendMail(CONF_SITE_OWNER_EMAIL, $subject . ' Request Id ( ' . time() . ' ) ', emailTemplate($message), $headers);
            }
            /* Notify Admin */
            redirectUser(CONF_WEBROOT_URL . 'merchant/request-city.php');
        } else {
            $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
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
$arr_bread = array('' => t_lang('M_TXT_CITIES'));
?>
</div></td>
<?php if ($_POST['city_country'] > 0) { ?>
    <script type="text/javascript">
        var selectedState = '<?php echo $_POST['city_state']; ?>';
        $(document).ready(function () {
            updateStates(document.frmCity.city_country.value);
        });</script>
<?php } ?>
<script type="text/javascript">
    var txtselectcountry = "<?php echo addslashes(t_lang('M_TXT_SELECT_COUNTRY_FIRST')); ?>";
    var txtload = "<?php echo addslashes(t_lang('M_TXT_LOADING')); ?>";
</script>
<td class="right-portion"><?php echo getMerchantBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_CITIES'); ?> </div>
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
                <?php } if (isset($_SESSION['msgs'][0])) { ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
    <div class="box"><div class="title"><?php echo t_lang('M_TXT_CITIES'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
</td>
<?php
require_once './footer.php';
exit;
