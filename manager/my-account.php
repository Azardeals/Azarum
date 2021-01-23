<?php
require_once './application-top.php';
require_once './admin-info.cls.php';
$page = (is_numeric($_GET['page']) ? $_GET['page'] : 1);
$pagesize = 50;
$mainTableName = 'tbl_admin';
$primaryKey = 'admin_id';
$colPrefix = 'admin_';
//$frm=getMBSFormByIdentifier('frmCompany');
$frm = new Form('frmAdmin');
$frm->setAction('?page=' . $page);
$frm->setJsErrorDisplay('afterfield');
$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="tbl_form"');
$frm->addHiddenField('', 'admin_id', $_SESSION['admin_logged']['admin_id'], 'admin_id');
$fld = $frm->addRequiredField(t_lang('M_FRM_ADMIN_NAME'), 'admin_name', $_SESSION['admin_logged']['admin_name']);
$fld->requirements()->setUsername();
$fld->setRequiredStarWith('caption');
/* $fld= $frm->addFileUpload(t_lang('M_FRM_UPLOAD_IMAGE'), 'admin_avtar', 'admin_avtar'); */
$frm->addRequiredField(t_lang('M_FRM_EMAIL_ADDRESS'), 'admin_email', $_SESSION['admin_logged']['admin_email']);
$frm->addRequiredField(t_lang('M_FRM_CONTACT_NUMBER'), 'admin_phone');
$frm->addTextBox(t_lang('M_FRM_SKYPE'), 'admin_skype');
$frm->addTextBox(t_lang('M_FRM_TWITTER'), 'admin_twitter');
$frm->addSubmitButton('&nbsp;', 'btn_submit', t_lang('M_TXT_UPDATE'), '', 'class="inputbuttons"');
$record = new TableRecord($mainTableName);
if (!$record->loadFromDb($primaryKey . '=' . $_SESSION['admin_logged']['admin_id'], true)) {
    $msg->addError($record->getError());
} else {
    $data = $record->getFlds();
    fillForm($frm, $data);
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
    $post = getPostedData();
    if ($post['admin_id'] == $_SESSION['admin_logged']['admin_id']) {
        if (!$frm->validate($post)) {
            $errors = $frm->getValidationErrors();
            foreach ($errors as $error) {
                $msg->addError($error);
            }
        } else {
            $arr_updates = [];
            if (isset($post['admin_name'])) {
                $arr_updates['admin_name'] = $post['admin_name'];
            }
            $arr_updates['admin_skype'] = $post['admin_skype'];
            $arr_updates['admin_twitter'] = $post['admin_twitter'];
            $arr_updates['admin_phone'] = $post['admin_phone'];
            $arr_updates['admin_email'] = $post['admin_email'];
            if ($post['password'] != '') {
                $arr_updates['admin_password'] = md5($post['password']);
            }
            $record = new TableRecord($mainTableName);
            $record->assignValues($arr_updates);
            $success = ($post[$primaryKey] > 0 ) ? $record->update($primaryKey . '=' . $post[$primaryKey]) : $record->addNew(); // can not edit 1 which is superadmin
            $_SESSION['admin_logged']['admin_name'] = $post['admin_name'];
            if ($success) {
                $admin_id = ($post[$primaryKey] > 1 && $post[$primaryKey] != $_SESSION['admin_logged']['admin_id']) ? $post[$primaryKey] : $record->getId();
                $msg->addMsg(t_lang('M_TXT_INFO_UPDATED'));
                if ($post['password'] != '') {
                    $msg->addMsg(t_lang('M_TXT_PASSWORD_UPDATED'));
                }
                redirectUser();
            } else {
                $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
                /* $frm->fill($post); */
                fillForm($frm, $post);
            }
        }
    } else {
        $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
        /* $frm->fill($post); */
        fillForm($frm, $post);
    }
}
$con_frm = new Form('ContactfrmAdmin');
$con_frm->setAction('?page=' . $page);
$con_frm->setRequiredStarWith('caption');
$con_frm->setJsErrorDisplay('afterfield');
$con_frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="tbl_form"');
$con_frm->addHiddenField('', 'admin_id', $_SESSION['admin_logged']['admin_id'], 'admin_id');
$arr_options = getCountryAssociativeList();
$con_frm->addRequiredField(t_lang('M_FRM_ADDRESS1'), 'admaddress_address1');
$con_frm->addTextBox(t_lang('M_FRM_ADDRESS2'), 'admaddress_address2');
$con_frm->addRequiredField(t_lang('M_FRM_CITY'), 'admaddress_city');
$con_frm->addHiddenField('', 'admaddress_id');
$con_frm->addHiddenField('admin_id', 'admaddress_admin_id');
$con_frm->addRequiredField(t_lang('M_FRM_ZIPCODE'), 'admaddress_zip');
$con_frm->addSelectBox(t_lang('M_FRM_COUNTRY'), 'admin_country', $arr_options, 'admin_country', 'onchange="updateStates(this.value);"', t_lang('M_TXT_SELECT'), 'admin_country');
$con_frm->addSelectBox(t_lang('M_FRM_STATE'), 'admaddress_state', '', '', '', t_lang('M_TXT_SELECT'), 'state_id')->requirements()->setRequired();
$con_frm->addSubmitButton('&nbsp;', 'submit', t_lang('M_TXT_UPDATE'), '', 'class="inputbuttons"');
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $post = getPostedData();
    if ($post['admin_id'] == $_SESSION['admin_logged']['admin_id']) {
        $post['admaddress_admin_id'] = $_SESSION['admin_logged']['admin_id'];
        if (!$con_frm->validate($post)) {
            $errors = $con_frm->getValidationErrors();
            foreach ($errors as $error) {
                $msg->addError($error);
            }
        } else {
            $record = new TableRecord('tbl_admin_addresses');
            $record->assignValues($post);
            $primaryKey = 'admaddress_id';
            $success = ($post[$primaryKey] > 0 ) ? $record->update($primaryKey . '=' . $post[$primaryKey]) : $record->addNew(); // can not edit 1 which is superadmin
            if ($success) {
                $admin_id = ($post['admaddress_admin_id'] > 1 && $post['admaddress_admin_id'] != $_SESSION['admin_logged']['admin_id']) ? $post[$primaryKey] : $record->getId();
                $msg->addMsg(t_lang('M_TXT_INFO_UPDATED'));
                redirectUser();
            } else {
                $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
                fillForm($con_frm, $post);
            }
        }
    } else {
        $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
        fillForm($con_frm, $post);
    }
}
$record = new TableRecord('tbl_admin_addresses');
$id = "admaddress_admin_id";
if ($record->loadFromDb($id . '=' . $_SESSION['admin_logged']['admin_id'], true)) {
    $arr = $record->getFlds();
    $srch = new SearchBase('tbl_states', 's');
    $srch->addCondition('s.state_id', '=', $arr['admaddress_state']);
    $rs_listing = $srch->getResultSet();
    $data = $db->fetch($rs_listing);
    $country = $data['state_country'];
    $arr['admin_country'] = $country;
    fillForm($con_frm, $arr);
}
$selected_state = $arr['admaddress_state'];
require_once './header.php';
if (isset($_POST['ImageSubmit'])) {
    $admin_info = new adminInfo();
    $post = getPostedData();
    if (!$admin_info->SaveImage($post)) {
        
    }
}
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    '' => t_lang('M_TXT_ADMIN_USERS')
];
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"></div>
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="message error"><?php echo $msg->display(); ?> </div><br/><br/>
                <?php } if (isset($_SESSION['msgs'][0])) { ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?> 
    <div class="row">
        <div class="col-sm-12">  
            <h1><?php echo t_lang('M_TXT_MY_ACCOUNT'); ?></h1> 
            <div class="containerwhite">
                <?php
                $admin_info = new adminInfo();
                echo $admin_info->leftPanel();
                ?>  
                <aside class="grid_2">
                    <?php echo $admin_info->navigationLink('edit'); ?>
                    <div class="areabody">   
                        <div class="formhorizontal">
                            <div class="repeatedrow">
                                <h3><i class="ion-podium icon"></i><?php echo t_lang('M_TXT_PROFILE_INFORMATION'); ?></h3>
                                <div class="rowbody">
                                    <?php echo $frm->getFormHtml(); ?>
                                </div>
                            </div>
                            <div class="repeatedrow">
                                <h3><i class="icon ion-android-call"></i><?php echo t_lang('M_TXT_CONTACT_INFORMATION'); ?></h3>
                                <div class="rowbody">
                                    <?php echo $con_frm->getFormHtml(); ?>
                                </div>
                            </div>
                        </div>  
                    </div>
                </aside>  
            </div>
        </div> 
    </div>
</td>
<script type="text/javascript">
    var selectedState = ' <?php echo $selected_state; ?> ';
    selectCountryFirst = '<?php echo addslashes(t_lang('M_TXT_SELECT_COUNTRY_FIRST')); ?>';
    $(document).ready(function () {
        updateStates('<?php echo $country; ?>');
    });
    function imageUpload() {
        data = $('#imageUpload').serialize();
        callAjax('common-ajax.php', 'mode=SaveAdminImages&data=' + data, function (t) {
            var ans = parseJsonData(t);
            location.reload();
        });
    }
</script>
<?php require_once './footer.php'; ?>
