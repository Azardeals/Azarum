<?php
require_once './application-top.php';
require_once '../includes/navigation-functions.php';
require_once '../includes/tax-functions.php';
checkAdminPermission(7);
$mainTableName = 'tbl_tax_geo_zones';
$primaryKey = 'geozone_id';
$colPrefix = 'geozone_';
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$Src_frm = new Form('Src_frm', 'Src_frm');
$Src_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$Src_frm->setFieldsPerRow(2);
$Src_frm->captionInSameCell(true);
$Src_frm->addTextBox(t_lang('M_TXT_ZONES_NAME'), 'zone', $_REQUEST['zone'], '', '');
$Src_frm->addHiddenField('', 'mode', 'search');
$Src_frm->addHiddenField('', 'status', $_REQUEST['status']);
$fld1 = $Src_frm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="medium" onclick=location.href="tax-zones.php"');
$fld = $Src_frm->addSubmitButton('', 'search', t_lang('M_TXT_SEARCH'), '', ' class="medium"')->attachField($fld1);
if (is_numeric($_REQUEST['restore'])) {
    if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
        restoreZone($_REQUEST['restore']);
        redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
if (isset($_REQUEST['deletePer']) && $_REQUEST['deletePer'] != "") {
    if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
        $geozone_id = $_REQUEST['deletePer'];
        deleteZonePermanent($geozone_id);
        redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
$frm = new Form('zone_frm', 'zone_frm');
$frm->setTableProperties('border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$frm->setFieldsPerRow(1);
$frm->captionInSameCell(false);
$frm->setAction('?page=' . $page);
$fld = $frm->addRequiredField(t_lang('M_FRM_ZONE_NAME'), 'geozone_name', '', 'geozone_name');
$fld->setUnique('tbl_tax_geo_zones', 'geozone_name', 'geozone_id', 'geozone_id', 'geozone_id');
$frm->addRequiredField(t_lang('M_FRM_ZONE_DESCRIPTION'), 'geozone_description', '', 'geozone_description');
$srch = new SearchBase('tbl_countries', 'c');
$srch->addCondition('c.country_status', '=', 'A');
$srch->doNotCalculateRecords();
$srch->doNotLimitRecords();
$srch->addFld('country_id');
$srch->addFld('country_name' . $_SESSION['lang_fld_prefix']);
$srch->addOrder('country_name');
$rs = $srch->getResultSet();
$arr_options = $db->fetch_all_assoc($rs);
$frm->addSelectBox(t_lang('M_FRM_COUNTRY'), 'zoneloc_country_id', $arr_options, '', 'onchange="updateStates(this.value);"', t_lang('M_TXT_SELECT'), '	zoneloc_country_id');
$frm->addHTML(t_lang('M_FRM_STATE'), 'zoneloc_state_id', '<span id="spn-state">Select Country First</span>', '');
$frm->addHiddenField('', 'status', $_REQUEST['status']);
$frm->addHiddenField('', 'geozone');
$status = array(1 => t_lang('M_TXT_ACTIVE'), 0 => t_lang('M_TXT_INACTIVE'));
$frm->addSelectBox(t_lang('M_FRM_STATUS'), 'geozone_active', $status, '');
$frm->setJsErrorDisplay('afterfield');
$frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SUBMIT'), '', 'class="medium"');
updateFormLang($frm);
$selected_state = [];
if (is_numeric($_REQUEST['edit'])) {
    if (checkAdminAddEditDeletePermission(7, '', 'edit')) {
        $record = new TableRecord('tbl_tax_geo_zones');
        if (!$record->loadFromDb('geozone_id=' . $_REQUEST['edit'], true)) {
            $msg->addError($record->getError());
        } else {
            $arr = $record->getFlds();
            $frm->addHiddenField('', 'oldname', $arr['geozone_name']);
            $frm->addHiddenField('', 'geozone_id', $arr['geozone_id']);
            $rs = $db->query("select * from tbl_geo_zone_location where zoneloc_geozone_id=" . $arr['geozone_id']);
            while ($row = $db->fetch($rs)) {
                $arr['zoneloc_country_id'] = $row['zoneloc_country_id'];
                $selected_state[] = $row['zoneloc_state_id'];
            }
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
        $record = new TableRecord('tbl_tax_geo_zones');
        $arr_lang_independent_flds = ['geozone_id', 'geozone_description', 'geozone_name', 'geozone_active', 'btn_submit'];
        assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
        if ((checkAdminAddEditDeletePermission(7, '', 'edit'))) {
            if (((int) $post['geozone_id']) > 0 || $post['geozone_id'] == "0")
                $success = $record->update('geozone_id' . '=' . $post['geozone_id']);
            $geostatesArray = getTaxStateRecord($post['geozone_id']);
        }
        if ((checkAdminAddEditDeletePermission(7, '', 'add'))) {
            if ($post['geozone_id'] == '') {
                $success = $record->addNew();
                $geostatesArray = [];
            }
        }
        if ($success) {
            $geozone_id = ($post[$primaryKey] > 0) ? $post[$primaryKey] : $record->getId();
            $delStateArray = array_diff($geostatesArray, $post['zoneloc_state_id']);
            $addStateArray = array_diff($post['zoneloc_state_id'], $geostatesArray);
            if (!empty($delStateArray)) {
                foreach ($delStateArray as $key => $value) {
                    $db->deleteRecords('tbl_geo_zone_location', ['smt' => 'zoneloc_geozone_id = ? and zoneloc_state_id = ?', 'vals' => [$geozone_id, $value], 'execute_mysql_functions' => false]);
                }
            }
            if ($post['zoneloc_state_id'] == "") {
                $addStateArray = array_keys(fetchStateListIDs($_POST['zoneloc_country_id']));
            }
            $record1 = new TableRecord('tbl_geo_zone_location');
            foreach ($addStateArray as $key => $value) {
                $data[$key]['zoneloc_geozone_id'] = $geozone_id;
                $data[$key]['zoneloc_country_id'] = $post['zoneloc_country_id'];
                $data[$key]['zoneloc_state_id'] = $value;
                $record1->assignValues($data[$key]);
                $record1->addNew(['IGNORE'], $data[$key]);
            }
            $msg->addMsg(t_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
            redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
        } else {
            $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
            fillForm($frm, $arr);
        }
    }
}
$srch = new SearchBase('tbl_tax_geo_zones', 'tz');
if ($_REQUEST['status'] == 'deleted') {
    //$srch->addCondition('geozone_deleted', '=', 1);
} else if ($_REQUEST['status'] == 'active') {
    $srch->addCondition('geozone_active', '=', 1);
} else if ($_REQUEST['status'] == 'deactive') {
    $srch->addCondition('geozone_active', '=', 0);
} else {
    $srch->addCondition('geozone_active', '=', 1);
}
if ($_POST['zone']) {
    $srch->addCondition('geozone_name', 'LIKE', '%' . $_POST['zone'] . '%');
}
$srch->joinTable('tbl_geo_zone_location', 'LEFT JOIN', 'tz.geozone_id=gzl.zoneloc_geozone_id', 'gzl');
$srch->joinTable('tbl_states', 'LEFT JOIN', 'gzl.zoneloc_state_id=s.state_id', 's');
$srch->joinTable('tbl_countries', 'LEFT JOIN', 'gzl.zoneloc_country_id=country.country_id', 'country');
$srch->addMultipleFields(array("tz.*", "country.country_name" . $_SESSION["lang_fld_prefix"], "country.country_id",
    "GROUP_CONCAT( distinct( s.state_name" . $_SESSION['lang_fld_prefix'] . ")SEPARATOR ',') as state_name",
    "GROUP_CONCAT( distinct( s.state_id)SEPARATOR ',') as state_ids"));
$srch->addOrder('geozone_name');
$srch->addGroupBy('geozone_id');
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$pagesize = 30;
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
$pagestring .= createHiddenFormFromPost('frmPaging', '?', ['page', 'status', 'zone'], ['page' => '', 'status' => $_REQUEST['affiliate'], 'zone' => $_REQUEST['zone']]);
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
	' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$html = '<input type="checkbox" id="select_all_ids"> <a class="selectAll" href="javascript:void(0);" title="' . t_lang('M_TXT_DELETE') . '" onclick="deleteMultipleRecords();"><i class="ion-android-delete icon"></i></a>';
$arr_listing_fields = [
    'select' => $html,
    'geozone_name' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_NAME'),
    'geozone_description' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_DESCRIPTION'),
    'geozone_state' => t_lang('M_FRM_STATE'),
    'geozone_country' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_COUNTRY'),
    'action' => t_lang('M_TXT_ACTION')
];
require_once './header.php';
$arr_bread = [
    'index.php' => '<img alt="Home" src="images/home-icon.png">',
    'configurations.php' => t_lang('M_TXT_SETTINGS'),
    '' => t_lang('M_TXT_ZONES')
];
echo '<script language="javascript"> selectCountryFirst="' . addslashes(t_lang('M_TXT_SELECT_COUNTRY_FIRST')) . '" </script>';
echo '<script language="javascript">
	var selectedState=' . json_encode($selected_state) . ';
	
	var cityDeletion = "' . addslashes(t_lang('M_MSG_CITY_DELETION_NOT_ALLOWED')) . '";
	var deleteCityMsg = "' . addslashes(t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD')) . '";
	var txtload = "' . addslashes(t_lang('M_TXT_LOADING')) . '";
	</script>';
?>
<ul class="nav-left-ul">
    <li>    <a <?php if ($_REQUEST['status'] == 'active') echo 'class="selected"'; ?> href="tax-zones.php?status=active"><?php echo t_lang('M_TXT_ACTIVE_ZONE_LISTING'); ?></a></li>
    <li>    <a <?php if ($_REQUEST['status'] == 'deactive') echo 'class="selected"'; ?> href="tax-zones.php?status=deactive"><?php echo t_lang('M_TXT_INACTIVE_ZONE_LISTING'); ?></a></li>
   <!-- <li>    <a <?php if ($_REQUEST['status'] == 'deleted') echo 'class="selected"'; ?> href="tax-zones.php?status=deleted"><?php echo t_lang('M_TXT_DELETED_ZONE_LISTING'); ?> </a></li>-->
</ul>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_ZONES'); ?>
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
                updateStates(document.zone_frm.zoneloc_country_id.value);
            });</script>
        <?php if ((checkAdminAddEditDeletePermission(7, '', 'add')) || (checkAdminAddEditDeletePermission(7, '', 'edit'))) { ?>
            <div class="box"><div class="title"> <?php echo t_lang('M_TXT_ZONES'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
            <?php
        } else {
            die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        }
    } else {
        ?>
        <div class="box searchform_filter"><div class="title"> <?php echo t_lang('M_TXT_ZONES'); ?> </div><div class="content togglewrap" style="display:none;">		<?php echo $Src_frm->getFormHtml(); ?></div></div>
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
                    echo '<td>';
                    switch ($key) {
                        case 'select':
                            echo '<input type="checkbox" name="zone[]" value="' . $row['geozone_id'] . '">';
                            break;
                        case 'geozone_name' . $_SESSION['lang_fld_prefix']:
                            echo $row['geozone_name'] . '<br/>';
                            break;
                        case 'geozone_state':
                            $sateidArray = array_keys(fetchStateListIDs($row['country_id']));
                            // print_r($row['state_ids']);
                            $db_states = explode(",", $row['state_ids']);
                            if (empty(array_diff($sateidArray, $db_states))) {
                                echo t_lang('M_TXT_ALL');
                            } else {
                                echo $row['state_name'] . '<br/>';
                            }
                            break;
                        case 'geozone_country' . $_SESSION['lang_fld_prefix']:
                            echo $row['country_name'] . '<br/>';
                            break;
                        case 'action':
                            echo '<ul class="actions">';
                            if ($_REQUEST['status'] == 'active') {
                                if (checkAdminAddEditDeletePermission(7, '', 'edit')) {
                                    echo '<li><a href="?edit=' . $row['geozone_id'] . '&page=' . $page . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                                }
                                if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
                                    echo '<li><a href="javascript:void(0);" title="' . t_lang('M_TXT_DELETE') . '" onclick="deleteZone(' . $row['geozone_id'] . ',' . $page . ');"><i class="ion-android-delete icon"></i></a></li>';
                                }
                            } else {
                                if (checkAdminAddEditDeletePermission(7, '', 'edit')) {
                                    echo '<li><a href="?edit=' . $row['geozone_id'] . '&page=' . $page . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                                }
                                if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
                                    echo '<li><a href="javascript:void(0);" title="' . t_lang('M_TXT_DELETE') . '" onclick="deleteZone(' . $row['geozone_id'] . ',' . $page . ');"><i class="ion-android-delete icon"></i></a></li>';
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
<script>
    function deleteMultipleRecords() {
        if ($('[name="zone[]"]:checked').length == 0) {
            requestPopup(this, '<?php echo (t_lang('M_MSG_please_select_at_least_one_zone')); ?>', 0);
            return false;
        }
        requestPopupAjax(1, '<?php echo addslashes(t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD')); ?>', 1);
    }
    function doRequiredAction() {
        if ($('[name="zone[]"]:checked').length == 0) {
            requestPopup(this, '<?php echo (t_lang('M_MSG_please_select_at_least_one_zone')); ?>', 0);
            return false;
        }
        zone_ids = $('.tbl_data input[type="checkbox"]').serialize();
        callAjax('cities-ajax.php', zone_ids + '&mode=deleteMultipleZones', function (t) {
            var ans = parseJsonData(t);
            if (ans) {
                jQuery.facebox(function () {
                    $.facebox(ans.msg)
                    setTimeout(function () {
                        location.reload()
                    }, 1500);
                });
            }
        });
    }
</script>
<?php require_once './footer.php'; ?>
