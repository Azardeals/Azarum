<?php
require_once './application-top.php';
require_once '../includes/navigation-functions.php';
$arr_common_js[] = 'js/calendar.js';
$arr_common_js[] = 'js/calendar-en.js';
$arr_common_js[] = 'js/calendar-setup.js';
$arr_common_css[] = 'css/cal-css/calendar-win2k-cold-1.css';
checkAdminPermission(7);
$mainTableName = 'tbl_registration_credit_schemes';
$primaryKey = 'regscheme_id';
$colPrefix = 'regscheme_';
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$_REQUEST['status'] = (($_REQUEST['status']) ? $_REQUEST['status'] : 'active');
$Src_frm = new Form('Src_frm', 'Src_frm');
$Src_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$Src_frm->setFieldsPerRow(2);
$Src_frm->captionInSameCell(true);
$Src_frm->addTextBox(t_lang('M_TXT_OFFER_NAME'), 'regscheme_name', $_REQUEST['regscheme_name'], '', '');
$Src_frm->addHiddenField('', 'mode', 'search');
$Src_frm->addHiddenField('', 'status', $_REQUEST['status']);
$fld1 = $Src_frm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="medium" onclick=location.href="registration-offers.php"');
$fld = $Src_frm->addSubmitButton('', 'search', t_lang('M_TXT_SEARCH'), '', ' class="medium"')->attachField($fld1);
if (isset($_REQUEST['deletePer']) && $_REQUEST['deletePer'] != "") {
    if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
        $regscheme_id = $_REQUEST['deletePer'];
        $whr = ['smt' => 'regscheme_id = ?', 'vals' => [$regscheme_id], 'execute_mysql_functions' => false];
        $db->deleteRecords($mainTableName, $whr);
        $msg->addMsg(t_lang('M_TXT_RECORD_DELETED'));
        redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
$frm = new Form('regoffr_frm', 'regoffr_frm');
$frm->setTableProperties('border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$frm->setFieldsPerRow(1);
$frm->captionInSameCell(false);
$frm->setAction('?page=' . $page);
$frm->addRequiredField(t_lang('M_TXT_OFFER_NAME'), 'regscheme_name', '', 'regscheme_name');
$frm->addTextArea(t_lang('M_TXT_DESCRIPTION'), 'regscheme_description', '', 'regscheme_description')->requirements()->setRequired();
$frm->addFloatField(t_lang('M_TXT_AMOUNT'), 'regscheme_credit_amount', '', 'regscheme_credit_amount');
$frm->addIntegerField(t_lang('M_FRM_USERS_PER_DAY'), 'regscheme_to_users_per_day', '', 'regscheme_to_users_per_day')->requirements()->setRequired();
$fld = $frm->addDateField(t_lang('M_FRM_VALID_FROM'), 'regscheme_valid_from', '', 'regscheme_valid_from');
$fld->requirements()->setRequired();
$fld->html_before_field = '<div class="frm-dob fld-req">';
$fld->html_after_field = '</div>';
$fld = $frm->addDateField(t_lang('M_FRM_OFFER_VALID_TILL'), 'regscheme_valid_till', '', 'regscheme_valid_till');
$fld->requirements()->setRequired();
$fld->html_before_field = '<div class="frm-dob fld-req">';
$fld->html_after_field = '</div>';
$frm->addHiddenField('', 'status', $_REQUEST['status']);
$frm->addHiddenField('', 'regscheme_id');
$status = array(1 => t_lang('M_TXT_ACTIVE'), 0 => t_lang('M_TXT_INACTIVE'));
$frm->addSelectBox(t_lang('M_FRM_STATUS'), 'regscheme_active', $status, '');
$frm->setJsErrorDisplay('afterfield');
$frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SUBMIT'), '', 'class="medium"');
updateFormLang($frm);
$selected_state = [];
if (is_numeric($_REQUEST['edit'])) {
    if (checkAdminAddEditDeletePermission(7, '', 'edit')) {
        $record = new TableRecord('tbl_registration_credit_schemes');
        if (!$record->loadFromDb('regscheme_id=' . $_REQUEST['edit'], true)) {
            $msg->addError($record->getError());
        } else {
            $arr = $record->getFlds();
            $frm->addHiddenField('', 'regscheme_id', $arr['regscheme_id']);
            $arr['btn_submit'] = t_lang('M_TXT_UPDATE');
            fillForm($frm, $arr);
            $msg->addMsg(t_lang('M_TXT_Edit_THE_VALUES_AND_UPDATE'));
        }
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
if (isset($_POST['btn_submit'])) {
    $post = getPostedData();
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error) {
            $msg->addError($error);
        }
    } else {
        if ($post['regscheme_active'] == 1) {
            $db->query("update tbl_registration_credit_schemes set regscheme_active=0");
        }
        $record = new TableRecord('tbl_registration_credit_schemes');
        /* $record->assignValues($post); */
        $record->setFldValue('regscheme_added_on', 'mysql_func_now()');
        $arr_lang_independent_flds = array('regscheme_id', 'regscheme_name', 'regscheme_description', 'regscheme_credit_amount', 'regscheme_to_users_per_day', 'regscheme_valid_from', 'regscheme_valid_till', 'regscheme_added_on', 'regscheme_active', 'btn_submit');
        assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
        if ((checkAdminAddEditDeletePermission(7, '', 'edit'))) {
            if (((int) $post['regscheme_id']) > 0 || $post['regscheme_id'] == "0") {
                $success = $record->update('regscheme_id' . '=' . $post['regscheme_id']);
            }
        }
        if ((checkAdminAddEditDeletePermission(7, '', 'add'))) {
            if ($post['regscheme_id'] == '') {
                $success = $record->addNew();
            }
        }
        if ($success) {
            $regscheme_id = ($post[$primaryKey] > 0) ? $post[$primaryKey] : $record->getId();
            $msg->addMsg(t_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
            redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
        } else {
            $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
            fillForm($frm, $arr);
        }
    }
}
$srch = new SearchBase('tbl_registration_credit_schemes', 'tz');
if ($_REQUEST['status'] == 'active') {
    $srch->addCondition('regscheme_active', '=', 1);
} else if ($_REQUEST['status'] == 'deactive') {
    $srch->addCondition('regscheme_active', '=', 0);
} else {
    $srch->addCondition('regscheme_active', '=', 1);
}
if ($_POST['regscheme_name']) {
    $srch->addCondition('regscheme_name', 'LIKE', '%' . $_POST['regscheme_name'] . '%');
}
$srch->addOrder('regscheme_name');
$srch->addGroupBy('regscheme_id');
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$pagesize = 30;
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
$pagestring .= createHiddenFormFromPost('frmPaging', '?', array('page', 'status', 'zone'), array('page' => '', 'status' => $_REQUEST['affiliate'], 'zone' => $_REQUEST['zone']));
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
	' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$arr_listing_fields = array(
    'regscheme_name' => t_lang('M_FRM_NAME'),
    'regscheme_description' => t_lang('M_FRM_DESCRIPTION'),
    'regscheme_credit_amount' => t_lang('M_FRM_AMOUNT'),
    'regscheme_valid_from' => t_lang('M_FRM_VALID_FROM'),
    'regscheme_valid_till' => t_lang('M_FRM_VALID_TILL'),
    'action' => t_lang('M_TXT_ACTION')
);
require_once './header.php';
$arr_bread = [
    'index.php' => '<img alt="Home" src="images/home-icon.png">',
    'configurations.php' => t_lang('M_TXT_SETTINGS'),
    '' => t_lang('M_TXT_REGISTRATION_OFFERS')
];
echo '<script language="javascript">
	var cityDeletion = "' . addslashes(t_lang('M_MSG_CITY_DELETION_NOT_ALLOWED')) . '";
	var deleteCityMsg = "' . addslashes(t_lang('M_MSG_WANT_TO_DELETE_THIS_CITY')) . '";
	var txtload = "' . addslashes(t_lang('M_TXT_LOADING')) . '";
	</script>';
?>
<ul class="nav-left-ul">
    <li>    <a <?php if ($_REQUEST['status'] == 'active') echo 'class="selected"'; ?> href="registration-offers.php?status=active"><?php echo t_lang('M_TXT_ACTIVE_OFFER_LISTING'); ?></a></li>
    <li>    <a <?php if ($_REQUEST['status'] == 'deactive') echo 'class="selected"'; ?> href="registration-offers.php?status=deactive"><?php echo t_lang('M_TXT_INACTIVE_OFFER_LISTING'); ?></a></li>
   <!-- <li>    <a <?php if ($_REQUEST['status'] == 'deleted') echo 'class="selected"'; ?> href="tax-zones.php?status=deleted"><?php echo t_lang('M_TXT_DELETED_ZONE_LISTING'); ?> </a></li>-->
</ul>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"> <?php echo t_lang('M_TXT_REGISTRATION_OFFERS'); ?>
            <?php if (checkAdminAddEditDeletePermission(7, '', 'add')) { ?>
                <ul class="actions right">
                    <li class="droplink">
                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                        <div class="dropwrap">
                            <ul class="linksvertical">
                                <li> <a href="?page=<?php echo $page; ?>&add=new"><?php echo t_lang('M_TXT_ADD_NEW'); ?></a></li>
                            </ul>
                        </div>
                    </li>
                </ul>
            <?php } ?>	
        </div>
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide();
                        return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="redtext"><?php echo $msg->display(); ?> </div><br/><br/>
                <?php } if (isset($_SESSION['msgs'][0])) { ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?> 
    <?php
    if (is_numeric($_REQUEST['edit']) || $_REQUEST['add'] == 'new') {
        if ((checkAdminAddEditDeletePermission(7, '', 'add')) || (checkAdminAddEditDeletePermission(7, '', 'edit'))) {
            ?>
            <div class="box"><div class="title"> <?php echo t_lang('M_TXT_REGISTRATION_OFFERS'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
            <?php
        } else {
            die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        }
    } else {
        ?>
        <div class="box searchform_filter">
            <div class="title"> <?php echo t_lang('M_TXT_REGISTRATION_OFFERS'); ?> </div>
            <div class="content togglewrap" style="display:none;"><?php echo $Src_frm->getFormHtml(); ?></div>
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
            while ($row = $db->fetch($rs_listing)) {
                echo '<tr' . (($row['zone_active'] == 0) ? ' class="inactive"' : '') . '>';
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td ' . (($key == action) ? 'width="20%"' : '') . '>';
                    switch ($key) {
                        case 'regscheme_valid_till':
                            echo displayDate($row['regscheme_valid_till']);
                            break;
                        case 'regscheme_valid_from':
                            echo displayDate($row['regscheme_valid_from']);
                            break;
                        case 'action':
                            echo '<ul class="actions">';
                            if (checkAdminAddEditDeletePermission(7, '', 'add')) {
                                /* echo '<li><a href="registration-banner.php?reg_id=' . $row['regscheme_id'] . '&page=' . $page . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_MANAGE_BANNER') . '"><i class="ion-ios-gear icon"></i></a></li>'; */
                            }
                            if ($_REQUEST['status'] == 'active') {
                                if (checkAdminAddEditDeletePermission(7, '', 'edit')) {
                                    echo '<li><a href="?edit=' . $row['regscheme_id'] . '&page=' . $page . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                                }
                                /* if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
                                  echo '<li><a href="javascript:void(0);" title="' . t_lang('M_TXT_DELETE') . '" onclick="deleteZone(' . $row['regscheme_id'] . ',' . $page . ');"><i class="ion-android-delete icon"></i></a></li>';
                                  } */
                            } else {
                                if (checkAdminAddEditDeletePermission(7, '', 'edit')) {
                                    echo '<li><a href="?edit=' . $row['regscheme_id'] . '&page=' . $page . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                                }
                                if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
                                    echo '<li><a href="?deletePer=' . $row['regscheme_id'] . '&page=' . $page . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_DELETE') . '" onclick="requestPopup(this,\'' . t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD') . '\',1);"><i class="ion-android-delete icon"></i></a></li>';
                                }
                            }
                            echo '</ul>';
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
            <?php
        }
    }
    ?>
</td>
<?php require_once './footer.php'; ?>
