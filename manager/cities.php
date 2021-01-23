<?php
require_once './application-top.php';
require_once '../includes/navigation-functions.php';
checkAdminPermission(7);
$mainTableName = 'tbl_cities';
$primaryKey = 'city_id';
$colPrefix = 'city_';
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$Src_frm = new Form('Src_frm', 'Src_frm');
$Src_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$Src_frm->setFieldsPerRow(2);
$Src_frm->captionInSameCell(true);
$Src_frm->addTextBox(t_lang('M_TXT_CITY_NAME'), 'city', $_REQUEST['city'], '', '');
$Src_frm->addHiddenField('', 'mode', 'search');
$Src_frm->addHiddenField('', 'status', $_REQUEST['status']);
$fld1 = $Src_frm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="medium" onclick=location.href="cities.php"');
$fld = $Src_frm->addSubmitButton('', 'search', t_lang('M_TXT_SEARCH'), '', ' class="medium"')->attachField($fld1);
if (is_numeric($_REQUEST['delete'])) {
    if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
        deleteCity($_REQUEST['delete']);
        /* function write in the site-function.php */
        redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
if (is_numeric($_REQUEST['restore'])) {
    if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
        restoreCity($_REQUEST['restore']);
        redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
if (is_numeric($_REQUEST['request'])) {
    if (!$db->update_from_array('tbl_cities', ['city_request' => 0], 'city_id=' . $_REQUEST['request'])) {
        $msg->addError($db->getError());
    } else {
        $msg->addMsg(t_lang('M_MSG_RECORD_UPDATED_SUCCESSFULLY'));
        /* Notify Company Merchant  */
        $rs = $db->query("select * from tbl_email_templates where tpl_id=15");
        $row_tpl = $db->fetch($rs);
        $srch = new SearchBase('tbl_cities', 'c');
        $srch->addCondition('city_id', '=', $_REQUEST['request']);
        $srch->joinTable('tbl_companies', 'INNER JOIN', 'c.city_requested_id=cp.company_id', 'cp');
        # $srch->addMultipleFields(array('od.od_qty', 'o.order_id'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        while ($row = $db->fetch($rs)) {
            $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
            $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
            $arr_replacements = [
                'xxcompany_namexx' => $row['company_name'],
                'xxcity_namexx' => $row['city_name'],
                'xxcity_codexx' => $row['city_code'],
                'xxstatusxx' => t_lang('M_TXT_APPROVED'),
                'xxsite_namexx' => CONF_SITE_NAME,
                'xxserver_namexx' => $_SERVER['SERVER_NAME'],
                'xxwebrooturlxx' => CONF_WEBROOT_URL,
                'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL
            ];
            foreach ($arr_replacements as $key => $val) {
                $subject = str_replace($key, $val, $subject);
                $message = str_replace($key, $val, $message);
            }
            if ($row_tpl['tpl_status'] == 1) {
                sendMail($row['company_email'], $subject, emailTemplate($message));
            }
        }
        /* Notify Company Merchant */
        redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
    }
}
if (isset($_REQUEST['deletePer']) && $_REQUEST['deletePer'] != "") {
    if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
        $city_id = $_REQUEST['deletePer'];
        deleteCityPermanent($city_id);
        redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
$frm = getMBSFormByIdentifier('frmCityAdmin');
$frm->setAction('?page=' . $page);
if (CONF_ADMIN_COMMISSION_TYPE == 1 || CONF_ADMIN_COMMISSION_TYPE == 3) {
    $fld = $frm->getField('city_deal_commission_percent');
    $frm->removeField($fld);
}
$fld = $frm->getField('city_bg_image');
$frm->removeField($fld);
/* $fld = $frm->getField('city_code');
  $frm->removeField($fld); */
$fld = $frm->getField('city_facebook_url');
$frm->removeField($fld);
$fld = $frm->getField('city_twitter_url');
$frm->removeField($fld);
$fld = $frm->getField('btn_submit');
$fld->value = t_lang('M_TXT_ADD');
$frm->addHiddenField('', 'status', $_REQUEST['status']);
//$frm->setJsErrorDisplay('summary');
updateFormLang($frm);
$selected_state = 0;
if (is_numeric($_REQUEST['edit'])) {
    if (checkAdminAddEditDeletePermission(7, '', 'edit')) {
        $record = new TableRecord('tbl_cities');
        if (!$record->loadFromDb('city_id=' . $_REQUEST['edit'], true)) {
            $msg->addError($record->getError());
        } else {
            $arr = $record->getFlds();
            $arr['oldname'] = $arr['city_name'];
            $frm->addHiddenField('', 'oldname', $arr['city_name']);
            $frm->addHiddenField('', 'oldname_lang1', $arr['city_name_lang1']);
            $rs = $db->query("select state_country from tbl_states where state_id=" . $arr['city_state']);
            $row = $db->fetch($rs);
            $arr['city_country'] = $row['state_country'];
            $arr['btn_submit'] = t_lang('M_TXT_UPDATE');
            $selected_state = $arr['city_state'];
            /* $frm->fill($arr); */
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
        $succeed = true;
        /* Image Validations if uploaded */
        if (is_uploaded_file($_FILES['city_bg_image']['tmp_name'])) {
            $ext = strtolower(strrchr($_FILES['city_bg_image']['name'], '.'));
            if ((!in_array($ext, ['.gif', '.jpg', '.jpeg', '.png'])) || ($_FILES['city_bg_image']['size'] > CONF_IMAGE_MAX_SIZE)) {
                $msg->addError(t_lang('M_TXT_CITY') . ' ' . t_lang('M_TXT_IMAGE_NOT_SUPPORTED_OR_SIZE_EXCEEDED'));
                fillForm($frm, $post);
                $succeed = false;
            }
        }
        if (true === $succeed) {
            $record = new TableRecord('tbl_cities');
            /* $record->assignValues($post); */
            $arr_lang_independent_flds = ['city_id', 'city_state', 'city_facebook_url', 'city_twitter_url', 'city_active', 'city_request', 'city_requested_id', 'city_deleted', 'mode', 'btn_submit'];
            if (!isset($post['oldname'])) {
                $post['city_name_lang1'] = $post['city_name'];
            }
            if (!isset($post['oldname_lang1']) && ($_SESSION['lang_fld_prefix'] == '_lang1')) {
                $record->setFldValue('city_name', $post['city_name']);
            }
            assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
            if ((checkAdminAddEditDeletePermission(7, '', 'edit'))) {
                if (((int) $post['city_id']) > 0 || $post['city_id'] == "0")
                    $success = $record->update('city_id' . '=' . $post['city_id']);
            }
            if ((checkAdminAddEditDeletePermission(7, '', 'add'))) {
                if ($post['city_id'] == '') {
                    $success = $record->addNew();
                }
            }
            if ($success) {
                $city_id = ($post[$primaryKey] >= 0) ? $post[$primaryKey] : $record->getId();
                if (is_uploaded_file($_FILES['city_bg_image']['tmp_name'])) {
                    $flname = time() . '_' . $_FILES['city_bg_image']['name'];
                    if (!move_uploaded_file($_FILES['city_bg_image']['tmp_name'], BACKGROUND_IMAGES_PATH . $flname)) {
                        $msg->addError(t_lang('M_TXT_FILE_COULD_NOT_SAVE_IMAGE'));
                    } else {
                        $db->update_from_array('tbl_cities', ['city_bg_image' => $flname], 'city_id=' . intval($city_id));
                        $msg->addMsg(t_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
                    }
                }
                redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
            } else {
                $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
                fillForm($frm, $arr);
            }
        }
    }
}
$srch = new SearchBase('tbl_cities', 'c');
if ($_REQUEST['status'] == 'deleted') {
    $srch->addCondition('city_deleted', '=', 1);
} else if ($_REQUEST['status'] == 'active') {
    $srch->addCondition('city_active', '=', 1);
    $srch->addCondition('city_deleted', '=', 0);
    $srch->addCondition('city_request', '=', 0);
} else if ($_REQUEST['status'] == 'deactive') {
    $srch->addCondition('city_active', '=', 0);
    $srch->addCondition('city_deleted', '=', 0);
    $srch->addCondition('city_request', '=', 0);
} else if ($_REQUEST['status'] == 'requested') {
    $srch->addCondition('city_request', '=', 1);
    $srch->addCondition('city_deleted', '=', 0);
} else {
    $srch->addCondition('city_deleted', '=', 0);
}
if ($_POST['city']) {
    $srch->addCondition('city_name' . $_SESSION['lang_fld_prefix'], 'LIKE', '%' . $_POST['city'] . '%');
}
$srch->joinTable('tbl_states', 'INNER JOIN', 'c.city_state=s.state_id', 's');
$srch->joinTable('tbl_countries', 'INNER JOIN', 's.state_country=country.country_id', 'country');
$srch->addMultipleFields(array('c.*', 'country.country_name' . $_SESSION['lang_fld_prefix'], 's.state_name' . $_SESSION['lang_fld_prefix']));
$srch->addOrder('city_name');
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$pagesize = 50;
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
$pagestring .= createHiddenFormFromPost('frmPaging', '?', ['page', 'status', 'city'], ['page' => '', 'status' => $_REQUEST['affiliate'], 'city' => $_REQUEST['city']]);
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
	' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
if ($_REQUEST['status'] != 'deleted') {
    if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
        $html = '<input type="checkbox" id="select_all_ids"> <a class="selectAll" href="javascript:void(0);" title="' . t_lang('M_TXT_DELETE') . '" onclick="deleteMultipleCities();"><i class="ion-android-delete icon"></i></a>';
    }
}
$arr_listing_fields = [
    'select' => $html,
    'city_name' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_NAME'),
    'city_code' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_CITY_CODE'),
    'state_name' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_STATE'),
    'country_name' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_COUNTRY'),
    'action' => t_lang('M_TXT_ACTION')
];
require_once './header.php';
$arr_bread = [
    'index.php' => '<img alt="Home" src="images/home-icon.png">',
    'configurations.php' => t_lang('M_TXT_SETTINGS'),
    '' => t_lang('M_TXT_CITIES')
];
echo '<script language="javascript">
	selectCountryFirst="' . addslashes(t_lang('M_TXT_SELECT_COUNTRY_FIRST')) . '"
	</script>';
echo '<script language="javascript">
	selectedState=' . $selected_state . ';
	var cityDeletion = "' . addslashes(t_lang('M_MSG_CITY_DELETION_NOT_ALLOWED')) . '";
	var deleteCityMsg = "' . addslashes(t_lang('M_MSG_WANT_TO_DELETE_THIS_CITY')) . '";
	var txtload = "' . addslashes(t_lang('M_TXT_LOADING')) . '";
	</script>';
?>
<ul class="nav-left-ul">
    <li>    <a <?php if ($_REQUEST['status'] == 'active') echo 'class="selected"'; ?> href="cities.php?status=active"><?php echo t_lang('M_TXT_ACTIVE_CITY_LISTING'); ?></a></li>
    <li>    <a <?php if ($_REQUEST['status'] == 'deactive') echo 'class="selected"'; ?> href="cities.php?status=deactive"><?php echo t_lang('M_TXT_INACTIVE_CITY_LISTING'); ?></a></li>
    <li>    <a <?php if ($_REQUEST['status'] == 'deleted') echo 'class="selected"'; ?> href="cities.php?status=deleted"><?php echo t_lang('M_TXT_DELETED_CITY_LISTING'); ?> </a></li>
    <li>    <a <?php if ($_REQUEST['status'] == 'requested') echo 'class="selected"'; ?> href="cities.php?status=requested"><?php echo t_lang('M_TXT_REQUESTED_CITY_LISTING'); ?> </a></li>
</ul>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"> <?php echo t_lang('M_TXT_CITIES'); ?> 
            <?php if (checkAdminAddEditDeletePermission(7, '', 'add')) { ?>
                <ul class="actions right">
                    <li class="droplink">
                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                        <div class="dropwrap">
                            <ul class="linksvertical">
                                <li><a href="?page=<?php echo $page; ?>&add=new"><?php echo t_lang('M_TXT_ADD_NEW'); ?></a></li>
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
    <?php
    if (is_numeric($_REQUEST['edit']) || $_REQUEST['add'] == 'new') {
        ?>
        <script type="text/javascript">
            $(document).ready(function () {
                updateStates(document.frmCity.city_country.value);
            });</script>
        <?php if ((checkAdminAddEditDeletePermission(7, '', 'add')) || (checkAdminAddEditDeletePermission(7, '', 'edit'))) { ?>
            <div class="box"><div class="title"> <?php echo t_lang('M_TXT_CITIES'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
            <?php
        } else {
            die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        }
    } else {
        ?>
        <div class="box searchform_filter"><div class="title"> <?php echo t_lang('M_TXT_CITIES'); ?> </div><div class="content togglewrap" style="display:none;"><?php echo $Src_frm->getFormHtml(); ?></div></div>
        <input type="hidden" value="<?php echo $db->total_records($rs_listing); ?>" id="total_records">
        <table class="tbl_data" width="100%">
            <thead>
                <tr>
                    <?php
                    foreach ($arr_listing_fields as $val)
                        echo '<th>' . $val . '</th>';
                    ?>
                </tr>
            </thead>
            <?php
            while ($row = $db->fetch($rs_listing)) {
                echo '<tr' . (($row['city_active'] == 0) ? ' class="inactive"' : '') . '>';
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td ' . (($key == action) ? 'width="20%"' : '') . '>';
                    switch ($key) {
                        case 'select':
                            echo '<input type="checkbox" name="cities[]" value="' . $row['city_id'] . '">';
                            break;
                        case 'city_name' . $_SESSION['lang_fld_prefix']:
                            echo '<strong>' . $arr_lang_name[0] . '</strong>' . ' ' . $row['city_name'] . '<br/>';
                            echo '<strong>' . $arr_lang_name[1] . '</strong>' . ' ' . $row['city_name_lang1'];
                            break;
                        case 'action':
                            echo '<ul class="actions">';
                            if ($_REQUEST['status'] == 'active') {
                                if (checkAdminAddEditDeletePermission(7, '', 'edit')) {
                                    echo '<li><a href="?edit=' . $row['city_id'] . '&page=' . $page . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                                }
                                if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
                                    echo '<li><a href="javascript:void(0);" title="' . t_lang('M_TXT_DELETE') . '" onclick="deleteCity(' . $row['city_id'] . ',' . $page . ');"><i class="ion-android-delete icon"></i></a></li>';
                                }
                            } else if ($_REQUEST['status'] == 'deleted') {
                                if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
                                    echo '<li><a href="?restore=' . $row['city_id'] . '&page=' . $page . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_RESTORE') . '"><i class="ion-archive icon"></i></a></li>';
                                    echo '<li><a href="?deletePer=' . $row['city_id'] . '&page=' . $page . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_DELETE_PERMANENTLY') . '" onclick="requestPopup(this,\'' . t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD') . '\',1);"><i class="ion-ios-trash icon"></i></a></li>';
                                }
                            } else if ($_REQUEST['status'] == 'requested') {
                                echo '<li><a href="?request=' . $row['city_id'] . '&page=' . $page . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_APPROVE_CITY') . '"><i class="ion-checkmark-circled icon"></i></a></li>';
                                if (checkAdminAddEditDeletePermission(7, '', 'edit')) {
                                    echo '<li><a href="?edit=' . $row['city_id'] . '&page=' . $page . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                                }
                                if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
                                    echo '<li><a href="javascript:void(0);" title="' . t_lang('M_TXT_DELETE') . '" onclick="deleteCity(' . $row['city_id'] . ',' . $page . ');"><i class="ion-android-delete icon"></i></a></li>';
                                }
                            } else {
                                if (checkAdminAddEditDeletePermission(7, '', 'edit')) {
                                    echo '<li><a href="?edit=' . $row['city_id'] . '&page=' . $page . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                                }
                                if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
                                    echo '<li><a href="javascript:void(0);" title="' . t_lang('M_TXT_DELETE') . '" onclick="deleteCity(' . $row['city_id'] . ',' . $page . ');"><i class="ion-android-delete icon"></i></a></li>';
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
            if ($db->total_records($rs_listing) == 0)
                echo '<tr><td colspan="' . count($arr_listing_fields) . '">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
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
<script>
    var city_alert = "<?php echo t_lang('M_TXT_SELECT_CITY_ALERT'); ?>";
</script>
<?php
//If set commission request is coming	
if (is_numeric($_REQUEST['commssion']) || $_REQUEST['commssion'] == 1) {
    ?>
    <script type="text/javascript">
        $(document).ready(function () {
            $('input[name^="city_deal_commission_percent"]').focus();
        });
    </script>
<?php } ?>
<?php
require_once './footer.php';
