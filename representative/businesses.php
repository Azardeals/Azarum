<?php
require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
if (!isRepresentativeUserLogged()) {
    redirectUser(CONF_WEBROOT_URL . 'representative/login.php');
}
$post = getPostedData();
//Search Form
$Src_frm = new Form('Src_frm', 'Src_frm');
$Src_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$Src_frm->setFieldsPerRow(2);
$Src_frm->captionInSameCell(true);
$Src_frm->addTextBox(t_lang('M_FRM_KEYWORDS'), 'keyword', '', '', '');
$Src_frm->addHiddenField('', 'mode', 'search');
$fld1 = $Src_frm->addButton('&nbsp;', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="inputbuttons" onclick=location.href="businesses.php"');
$fld = $Src_frm->addSubmitButton('&nbsp;', 'btn_search', t_lang('M_TXT_SEARCH'), '', ' class="inputbuttons"')->attachField($fld1);
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$pagesize = 15;
$mainTableName = 'tbl_companies';
$primaryKey = 'company_id';
$colPrefix = 'company_';
/* --------------------------------------- */

function canEditCompany($rep_id, $company_id)
{
    global $db;
    $rep_id = intval($rep_id);
    $company_id = intval($company_id);
    if ($rep_id < 1 || $company_id < 1) {
        return false;
    }
    $srch = new SearchBase('tbl_companies', 'c');
    $srch->addCondition('company_deleted', '=', 0);
    $srch->addCondition('company_rep_id', '=', $rep_id);
    $srch->addCondition('company_id', '=', $company_id);
    $srch->addFld('company_rep_id');
    $rs = $srch->getResultSet();
    if (!$row = $db->fetch($rs)) {
        return false;
    }
    if ($row['company_rep_id'] != $rep_id) {
        return false;
    }
    return true;
}

/* --------------------------------------- */
if (isset($_REQUEST['edit']) && !canEditCompany($_SESSION['logged_user']['rep_id'], $_REQUEST['edit'])) {
    redirectUser(CONF_WEBROOT_URL . 'representative/logout.php');
}
if (is_numeric($_REQUEST['delete'])) {
    if (checkAdminAddEditDeletePermission(3, '', 'delete') && !canEditCompany($_SESSION['logged_user']['rep_id'], $_REQUEST['delete'])) {
        deleteCompany($_REQUEST['delete']);
        /* function write in the site-function.php */
        redirectUser('?page=' . $page);
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
$frm = getMBSFormByIdentifier('frmCompany');
$fld = $frm->getField('company_rep_id');
$frm->removeField($fld);
$fld = $frm->getField('company_active');
$frm->removeField($fld);
$frm->addHiddenField('', 'company_rep_id', $_SESSION['logged_user']['rep_id']);
/*  */
$fld1 = $frm->getField('btn_submit');
$fld1->value = t_lang('M_TXT_ADD');
$fld = $frm->getField('company_profile');
$fld->html_before_field = '<div class="frm-editor">';
$fld->html_after_field = '</div>';
$frm->setAction('?page=' . $page);
updateFormLang($frm);
if (is_numeric($_GET['edit'])) {
    $record = new TableRecord($mainTableName);
    if (!$record->loadFromDb($primaryKey . '=' . $_GET['edit'], true)) {
        $msg->addError($record->getError());
    } else {
        $arr = $record->getFlds();
        $arr['btn_submit'] = t_lang('M_TXT_UPDATE');
        $arr['company_password'] = '';
        fillForm($frm, $arr);
        $msg->addMsg(t_lang('M_TXT_Edit_THE_VALUES_AND_UPDATE'));
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['btn_search']) && isset($_POST['btn_submit'])) {
    $post = getPostedData();
    if (isset($post['company_id']) && intval($post['company_id']) && !canEditCompany($_SESSION['logged_user']['rep_id'], $post['company_id'])) {
        $msg->addError(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        redirectUser(CONF_WEBROOT_URL . 'representative/logout.php');
    }
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error)
            $msg->addError($error);
    } else {
        $succeed = true;
        /* Image Validations if uploaded */
        if (is_uploaded_file($_FILES['company_logo']['tmp_name'])) {
            $ext = strtolower(strrchr($_FILES['company_logo']['name'], '.'));
            if ((!in_array($ext, array('.gif', '.jpg', '.jpeg', '.png'))) || ($_FILES['company_logo']['size'] > CONF_IMAGE_MAX_SIZE)) {
                $msg->addError(t_lang('M_TXT_COMPANY_LOGO') . ' ' . t_lang('M_TXT_IMAGE_NOT_SUPPORTED_OR_SIZE_EXCEEDED'));
                fillForm($frm, $post);
                $succeed = false;
            }
        }
        if (true === $succeed) {
            $record = new TableRecord($mainTableName);
            $arr_lang_independent_flds = array('company_id', 'company_password', 'company_email', 'company_phone', 'company_url', 'company_zip', 'company_country', 'company_profile_enabled', 'company_paypal_account', 'company_google_map', 'company_active', 'company_deleted', 'mode', 'btn_submit');
            assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
            if ($post['company_password'] != '') {
                $code = $post['company_password'];
                $record->setFldValue('company_password', md5($post['company_password']), '');
            }
            $success = ($post[$primaryKey] > 0) ? $record->update($primaryKey . '=' . $post[$primaryKey]) : $record->addNew();
            if ($success) {
                $company_id = ($post['company_id'] > 0) ? $post['company_id'] : $record->getId();
                if ($post['company_id'] == "") {
                    ########## Email #####################
                    $headers = 'MIME-Version: 1.0' . "\r\n";
                    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                    $headers .= 'From: ' . CONF_SITE_NAME . ' <' . CONF_EMAILS_FROM . '>' . "\r\n";
                    $rs = $db->query("select * from tbl_email_templates where tpl_id=8");
                    $row_tpl = $db->fetch($rs);
                    $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
                    $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
                    $arr_replacements = array(
                        'xxcompany_namexx' => $post['company_name'],
                        'xxuser_namexx' => $post['company_email'],
                        'xxemail_addressxx' => $post['company_email'],
                        'xxpasswordxx' => $code,
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
                        mail($post['company_email'], $subject, emailTemplateSuccess($message), $headers);
                    }
                    ##############################################
                }
                ################### COMPANY LOGO ###################
                if (is_uploaded_file($_FILES['company_logo']['tmp_name'])) {
                    $ext = strtolower(strrchr($_FILES['company_logo']['name'], '.'));
                    if (!in_array($ext, array('.gif', '.jpg', '.jpeg', '.png'))) {
                        $msg->addError(t_lang('M_ERROR_IMAGE_COULD_NOT_SAVED_NOT_SUPPORTED'));
                    } else {
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
                }
                ################### COMPANY LOGO END ###################
                ################### CHECK REDIRECTION IF THE MULTIPLE ADDRESSES ARE NULL###################
                $srchAdd = new SearchBase('tbl_company_addresses', 'ca');
                $srchAdd->addCondition('company_id', '=', $company_id);
                $rs_listingAdd = $srchAdd->getResultSet();
                #########################CHECK REDIRECTION IF THE MULTIPLE ADDRESSES ARE NULL##############
                $msg->addMsg(t_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
                redirectUser('?');
            } else {
                $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
                fillForm($frm, $post);
            }
        }
    }
}
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$pagesize = 15;
$srch = new SearchBase('tbl_companies', 'c');
$srch->addCondition('company_deleted', '=', 0);
$srch->addCondition('company_rep_id', '=', $_SESSION['logged_user']['rep_id']);
$srch->joinTable('tbl_countries', 'INNER JOIN', 'c.company_country=country.country_id', 'country');
$srch->joinTable('tbl_states', 'LEFT JOIN', 'state.state_id=c.company_state', 'state');
$srch->addMultipleFields(array('c.*', 'country.country_name'));
$srch->addFld("CONCAT_WS('', company_address1, '<br/>', company_address2, '<br/>', company_address3, ' ', company_city, ' ', state.state_name, '-',c.company_zip, ' ', country.country_name) AS address");
$srch->addOrder('company_name');
if ($post['mode'] == 'search') {
    if ($post['keyword'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('c.company_email', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('c.company_name' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('c.company_address1' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('c.company_address2' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('c.company_address3' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
    }
    $Src_frm->fill($post);
}
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
$pagestring .= createHiddenFormFromPost('frmPaging', '?', array('page'), array('page' => ''));
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent .= '<a href="javascript:void(0);"> ' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) . ' ' . t_lang('M_TXT_TO') . ' '
        . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a 	href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> ', $srch->pages(), $page, '<li class="selected"><a href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$arr_listing_fields = array(
    'listserial' => t_lang('M_TXT_SR_NO'),
    'company_name' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_NAME'),
    'address' => t_lang('M_TXT_ADDRESS'),
    'company_email' => t_lang('M_FRM_EMAIL'),
    'company_active' => t_lang('M_FRM_STATUS'),
    'actions' => t_lang('M_FRM_ACTIONS')
);
$arr_bread = array('my-account.php' => '<img class="home" alt="Home" src="images/home-icon.png">', '' => t_lang('M_TXT_BUSINESSES'));
require_once './header.php';
?>
<script type="text/javascript">
    compntinact = "<?php echo addslashes(t_lang('M_TXT_COMPANY_CANNOT_BE_INACTIVE')); ?>";
</script>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_BUSINESSES'); ?> </div>
        <ul class="actions right">
            <li class="droplink">
                <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                <div class="dropwrap">
                    <ul class="linksvertical">
                        <li><a href="?page=<?php echo $page; ?>&add=new"><?php echo t_lang('M_TXT_ADD_NEW'); ?> </a></li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide();return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
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
    <?php if (is_numeric($_REQUEST['edit']) || $_REQUEST['add'] == 'new') { ?>
        <div class="box"><div class="title"> <?php echo t_lang('M_TXT_BUSINESSES'); ?> </div><div class="content"><?php
                $fld1 = $frm->getField('btn_submit');
                $frm->changeFieldPosition($fld1->getFormIndex(), $fld1->getFormIndex() + 5);
                $fld = $frm->getField('company_country');
                $fld->extra = 'onchange="updateStates(this.value);"';
                $srch = new SearchBase('tbl_states');
                $srch->addCondition('state_status', '=', 'A');
                $srch->addCondition('state_country', '=', $row['rep_country']);
                $srch->addMultipleFields(array('state_id', 'state_name'));
                $rs = $srch->getResultSet();
                $arr_states = $db->fetch_all_assoc($rs);
                $fld = $frm->getField('company_state');
                $fld->fldType = 'select';
                $fld->id = 'state_id';
                $fld->options = $arr_states;
                echo $frm->getFormHtml();
                ?></div></div>
    <?php } else { ?>
        <div class="box searchform_filter">
            <div class="title"> <?php echo t_lang('M_TXT_SEARCH'); ?> </div>
            <div class="content togglewrap" style="display:none;">		<?php echo $Src_frm->getFormHtml(); ?>
            </div>
        </div>
        <table class="tbl_data" width="100%">
            <thead>
                <tr>
                    <?php
                    foreach ($arr_listing_fields as $val) {
                        echo '<th>' . $val . '</th>';
                    }
                    ?>
                </tr>
            </thead>
            <?php
            for ($listserial = ($page - 1) * $pagesize + 1; $row = $db->fetch($rs_listing); $listserial++) {
                if ($listserial % 2 == 0) {
                    $even = 'even';
                } else {
                    $even = '';
                }
                echo '<tr class=" ' . $even . ' ">';
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td class="center">';
                    //print_r($row);
                    switch ($key) {
                        case 'listserial':
                            echo $listserial;
                            break;
                        case 'company_name':
                            echo $row['company_name'] . '<br/>';
                            $srchRep = new SearchBase('tbl_representative', 'r');
                            $srchRep->addCondition('rep_id', '=', $row['company_rep_id']);
                            $rs_listingRep = $srchRep->getResultSet();
                            $rowRep = $db->fetch($rs_listingRep);
                            if ($srchRep->recordCount($rs_listingRep) > 0) {
                                echo '<strong>' . t_lang('M_TXT_COMPANY_/_REPRESENTATIVE') . ':</strong>' . ' ' . $rowRep['rep_fname'] . ' ' . $rowRep['rep_lname'];
                            }
                            break;
                        case 'company_name_lang1':
                            echo '<strong>' . $arr_lang_name[0] . '</strong>' . ' ' . $row['company_name'] . '<br/>';
                            echo '<strong>' . $arr_lang_name[1] . '</strong>' . ' ' . $row['company_name_lang1'];
                            $srchRep = new SearchBase('tbl_representative', 'r');
                            $srchRep->addCondition('rep_id', '=', $row['company_rep_id']);
                            $rs_listingRep = $srchRep->getResultSet();
                            $rowRep = $db->fetch($rs_listingRep);
                            if ($srchRep->recordCount($rs_listingRep) > 0) {
                                echo '<br/><strong>' . t_lang('M_TXT_COMPANY_/_REPRESENTATIVE') . ':</strong>' . ' ' . $rowRep['rep_fname'] . ' ' . $rowRep['rep_lname'];
                            }
                            break;
                        case 'company_active':
                            echo '<span id="comment-status' . $row[$primaryKey] . '"> ';
                            if ($row['company_active'] == 1) {
                                echo '<span class="statustab addmarg"  onclick="activeCompany(' . $row[$primaryKey] . ',0);">
                                            <span class="switch-labels" data-off="Active" data-on="Inactive"></span>
                                            <span class="switch-handles"></span>
                                    </span>';
                            }
                            if ($row['company_active'] == 0) {
                                echo '<span class="statustab addmarg active"  onclick="activeCompany(' . $row[$primaryKey] . ',1);">
                                            <span class="switch-labels" data-off="Active" data-on="Inactive"></span>
                                            <span class="switch-handles"></span>
                                    </span>';
                            }
                            echo '</span>';
                            break;
                        case 'actions':
                            echo '<ul class="actions"><li><a href="?edit=' . $row[$primaryKey] . '&page=' . $page . '" title="' . t_lang('M_TXT_EDIT') . '" ><i class="ion-edit icon"></i></a></li></ul> ';
                            break;
                        default:
                            echo $row[$key];
                            break;
                    }
                    echo '</td>';
                }
                echo '</tr>';
            }
            if ($db->total_records($rs_listing) == 0) {
                echo '<tr><td colspan="' . count($arr_listing_fields) . '">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
            }
            ?>
        </table>
        <?php if ($srch->pages() > 1) { ?>
            <div class="footinfo">
                <aside class="grid_1">
                    <?php echo $pagestring; ?>	 
                </aside>  
                <aside class="grid_2"><span class="info"><?php echo $pageStringContent; ?></span></aside>
            </div>
        <?php } ?>     
    </td>
<?php } ?> 
<script>
    var selectedState = 0;
</script>       
<?php
require_once './footer.php';
