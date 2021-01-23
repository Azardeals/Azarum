<?php
require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
$cityname = ($_SESSION['cityname'] != "") ? convertStringToFriendlyUrl($_SESSION['cityname']) : 1;
if (!isCompanyUserLogged()) {
    redirectUser(CONF_WEBROOT_URL . 'merchant/login.php');
}
$company_id = $_SESSION['logged_user']['company_id'];
$charity = $db->query("SELECT SQL_CALC_FOUND_ROWS * FROM tbl_company_charity where charity_company_id=$company_id");
$row = $db->fetch($charity);
$fetchVal['charity_name'] = $row['charity_name'];
$fetchVal['charity_address1'] = $row['charity_address1'];
$fetchVal['charity_address2'] = $row['charity_address2'];
$fetchVal['charity_address3'] = $row['charity_address3'];
$fetchVal['charity_phone'] = $row['charity_phone'];
$fetchVal['charity_email_address'] = $row['charity_email_address'];
$fetchVal['charity_contact_person'] = $row['charity_contact_person'];
$fetchVal['charity_percentage'] = $row['charity_percentage'];
$fetchVal['charity_id'] = $row['charity_id'];
$arr = $_SESSION['logged_user'];
$fetchVal['charity_comapny_id'] = $_SESSION['logged_user']['company_id'];
$frm = getMBSFormByIdentifier('frmComapnyCharity');
$fld6 = $frm->getField('submit');
$fld6->extra = " class='inputbuttons'";
$frm->fill($fetchVal);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    if ($frm->validate($post)) {
        $arr_updates = array(
            'charity_name' => $post['charity_name'],
            'charity_phone' => $post['charity_phone'],
            'charity_email_address' => $post['charity_email_address'],
            'charity_address1' => $post['charity_address1'],
            'charity_address2' => $post['charity_address2'],
            'charity_address3' => $post['charity_address3'],
            'charity_company_id' => $company_id,
            'charity_percentage' => $post['charity_percentage'],
            'charity_contact_person' => $post['charity_contact_person'],
            'charity_id' => $post['charity_id']
        );
        $record = new TableRecord('tbl_company_charity');
        $record->assignValues($arr_updates);
        $record->setFldValue('charity_company_id', $company_id);
        $success = ($post['charity_id'] > 0) ? $record->update('charity_id=' . $post['charity_id']) : $record->addNew();
        if ($success) {
            $msg->addMsg(t_lang('M_TXT_CHARITY_SUCCESSFULLY_SET'));
            redirectUser(CONF_WEBROOT_URL . 'merchant/company-charity.php');
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
?>
</div></td>
<td class="right-portion"><?php //echo getAdminBreadCrumb($arr_bread);          ?>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
        <div class="box" id="messages">
            <div class="title-msg"><?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
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
    <div class="box"><div class="title"><?php echo t_lang('M_TXT_COMPANY_CHARITY'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
</td>
<?php
require_once './footer.php';
