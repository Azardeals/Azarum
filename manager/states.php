<?php
require_once './application-top.php';
require_once '../includes/navigation-functions.php';
checkAdminPermission(7);
$mainTableName = 'tbl_states';
$primaryKey = 'state_id';
$colPrefix = 'state_';
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);

$rscountry = $db->query("SELECT  country_id, country_name" . $_SESSION['lang_fld_prefix'] . "  FROM `tbl_countries` WHERE country_status='A' order by country_name asc");
$countryArray = [];
while ($arrs = $db->fetch($rscountry)) {
    $countryArray[$arrs['country_id']] = $arrs['country_name' . $_SESSION['lang_fld_prefix']];
}


$Src_frm = new Form('Src_frm', 'Src_frm');
$Src_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$Src_frm->setFieldsPerRow(3);
$Src_frm->captionInSameCell(true);
$Src_frm->addTextBox(t_lang('M_TXT_STATE_NAME'), 'state', $_REQUEST['state'], '', '');
$Src_frm->setJsErrorDisplay('afterfield');
$Src_frm->setRequiredStarWith('caption');
$Src_frm->addSelectBox(t_lang('M_TXT_COUNTRY_NAME'), 'country', $countryArray, $_REQUEST['country'], '', 'Select', 'country');
$Src_frm->addHiddenField('', 'mode', 'search');
$Src_frm->addHiddenField('', 'status', $_REQUEST['status']);
$fld1 = $Src_frm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="medium" onclick=location.href="states.php"');
$fld = $Src_frm->addSubmitButton('&nbsp;', 'search', t_lang('M_TXT_SEARCH'), '', ' class="medium"')->attachField($fld1);
if (is_numeric($_REQUEST['delete'])) {
    if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
        $cityRs = $db->query("Select * from tbl_cities where city_state=" . intval($_REQUEST['delete']) . " AND city_deleted = 0");
        if ($db->total_records($cityRs) == 0) {
            $db->query('DELETE from tbl_states where state_id=' . intval($_REQUEST['delete']));
            $msg->addMsg(t_lang('M_TXT_STATE_DELETED'));
        } else {
            $temp = t_lang('M_TXT_STATE_CANNOT_BE_DELETED_AS_SOME_CITIES_ARE_ASSOCIATED_WITH_IT');
            $msg->addError($temp);
        }
        redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}

if (isset($_REQUEST['inactive']) && $_REQUEST['inactive'] != "") {
    if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
        $cityRs = $db->query("Select * from tbl_cities where city_state=" . $db->quoteVariable($_REQUEST['inactive']) . " AND city_deleted = 0");
        if ($db->total_records($cityRs) == 0) {
            $db->query('UPDATE tbl_states set state_status="B" where state_id=' . $db->quoteVariable($_REQUEST['inactive']));
            $msg->addMsg(t_lang('M_TXT_STATE_UPDATED'));
        } else {
            $msg->addError(t_lang('M_TXT_STATE_CANNOT_DELETED_SOME_CITIES_ARE_ASSOCIATED_WITH_IT'));
        }
        redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}

if (isset($_REQUEST['active']) && $_REQUEST['active'] != "") {
    if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
        $db->query('UPDATE tbl_states set state_status="A" where state_id=' . $_REQUEST['active']);
        $msg->addMsg(t_lang('M_TXT_STATE_UPDATED'));

        redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}

$frm = new Form('frmStates', 'frmStates');
$frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$frm->setFieldsPerRow(1);
$frm->captionInSameCell(false);
$frm->addTextBox(t_lang('M_TXT_STATE_NAME'), 'state_name', $_REQUEST['state'], '', '')->requirements()->setRequired();
$frm->setJsErrorDisplay('afterfield');
$frm->addSelectBox(t_lang('M_TXT_COUNTRY_NAME'), 'state_country', $countryArray, $value, '', 'Select', 'state_country');
$frm->addSelectBox(t_lang('M_TXT_STATE_STATUS'), 'state_status', ['A' => 'Active', 'B' => 'Inactive'], $value, '', 'Select', 'state_country');

$frm->addHiddenField('', 'mode', 'search');
$frm->addHiddenField('', 'state_id', '', 'state_id');
$frm->addHiddenField('', 'status', $_REQUEST['status']);
$frm->addSubmitButton('&nbsp;', 'btn_submit', t_lang('M_TXT_SUBMIT'), '', ' class="medium"');
$selected_state = 0;

if (is_numeric($_REQUEST['edit'])) {
    if (checkAdminAddEditDeletePermission(7, '', 'edit')) {
        $record = new TableRecord('tbl_states');

        if (!$record->loadFromDb('state_id=' . $_REQUEST['edit'], true)) {
            $msg->addError($record->getError());
        } else {
            $arr = $record->getFlds();
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
        foreach ($errors as $error)
            $msg->addError($error);
    } else {
        $record = new TableRecord('tbl_states');
        /* $record->assignValues($post); */
        $arr_lang_independent_flds = ['state_id', 'state_country', 'state_status', 'mode', 'btn_submit'];
        assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
        if ((checkAdminAddEditDeletePermission(7, '', 'edit'))) {
            if ($post['state_id'] > 0)
                $success = $record->update('state_id' . '=' . $post['state_id']);
        }
        if ((checkAdminAddEditDeletePermission(7, '', 'add'))) {
            if ($post['state_id'] == '')
                $success = $record->addNew();
        }
        #$success=($post['state_id']>0)?$record->update('state_id' . '=' . $post['state_id']):$record->addNew();
        if ($success) {
            $state_id = ($post[$primaryKey] > 0) ? $post[$primaryKey] : $record->getId();

            $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
            redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
        } else {
            $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
            fillForm($frm, $arr);
        }
    }
}
$srch = new SearchBase('tbl_states', 's');
if ($_REQUEST['status'] == 'A') {
    $srch->addCondition('state_status', '=', 'A');
}
if ($_REQUEST['status'] == 'B') {
    $srch->addCondition('state_status', '=', 'B');
}

if ($_POST['state']) {
    $srch->addCondition('state_name' . $_SESSION['lang_fld_prefix'], 'LIKE', '%' . $_POST['state'] . '%');
}

if ($_POST['country']) {
    $srch->addCondition('state_country', '=', $_POST['country']);
}

$srch->joinTable('tbl_countries', 'INNER JOIN', 's.state_country=c.country_id', 'c');
$srch->addOrder('c.country_name');
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$pagesize = 50;
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();

$pagestring .= createHiddenFormFromPost('frmPaging', '?', ['page', 'state', 'country'], ['page' => '', 'state' => $_REQUEST['state'], 'country' => $_REQUEST['country']]);
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
		' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$html = '<input type="checkbox" id="select_all_ids"> <a class="selectAll" href="javascript:void(0);" title="' . t_lang('M_TXT_DELETE') . '" onclick="deleteMultipleStates();"><i class="ion-android-delete icon"></i></a>';

$arr_listing_fields = [
    'select' => $html,
    'state_name' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_NAME'),
    'state_country' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_COUNTRY'),
    'action' => t_lang('M_TXT_ACTION')
];
require_once './header.php';
$arr_bread = [
    'index.php' => '<img alt="Home" src="images/home-icon.png">',
    'configurations.php' => t_lang('M_TXT_SETTINGS'),
    '' => t_lang('M_TXT_STATES')
];
echo '<script language="javascript">selectCountryFirst="' . addslashes(t_lang('M_TXT_SELECT_COUNTRY_FIRST')) . '" </script>';

echo '<script language="javascript">
	selectedState=' . $selected_state . ';
	var stateDeletion = "' . addslashes(t_lang('M_MSG_STATE_DELETION_NOT_ALLOWED')) . '";
	var deleteCityMsg = "' . addslashes(t_lang('M_MSG_WANT_TO_DELETE_THIS_CITY')) . '";
	</script>';
?>
<ul class="nav-left-ul">
    <li>    <a <?php if ($_REQUEST['status'] == 'A') echo 'class="selected"'; ?> href="states.php?status=A"><?php echo t_lang('M_TXT_ACTIVE_STATE_LISTING'); ?></a></li>
    <li>    <a <?php if ($_REQUEST['status'] == 'B') echo 'class="selected"'; ?> href="states.php?status=B"><?php echo t_lang('M_TXT_INACTIVE_STATE_LISTING'); ?></a></li>



</ul>
</div></td>

<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>

    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_STATES'); ?> 
            <?php if (checkAdminAddEditDeletePermission(7, '', 'add')) { ?>
                <ul class="actions right">
                    <li class="droplink">
                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                        <div class="dropwrap">
                            <ul class="linksvertical">
                                <li><a href="?page=<?php echo $page; ?>&add=new"><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_TXT_STATE'); ?></a> </li>
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
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
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

        <?php if ((checkAdminAddEditDeletePermission(7, '', 'add')) || (checkAdminAddEditDeletePermission(7, '', 'edit'))) { ?>
            <div class="box"><div class="title"> <?php echo t_lang('M_TXT_STATES'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
            <?php
        } else {
            die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        }
    } else {
        ?>
        <div class="box searchform_filter"><div class="title"> <?php echo t_lang('M_TXT_STATES'); ?> </div><div class="content togglewrap" style="display:none;">		<?php echo $Src_frm->getFormHtml(); ?></div></div>

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
                echo '<tr' . (($row['state_status'] == 'B') ? ' class="inactive"' : '') . '>';
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td ' . (($key == action) ? 'width="20%"' : '') . '>';
                    switch ($key) {
                        case 'select':
                            echo '<input type="checkbox" name="states[]" value="' . $row['state_id'] . '">';
                            break;
                        case 'state_name' . $_SESSION['lang_fld_prefix']:
                            echo '<strong>' . $arr_lang_name[0] . '</strong>' . ' ' . $row['state_name'] . '<br/>';
                            echo '<strong>' . $arr_lang_name[1] . '</strong>' . ' ' . $row['state_name_lang1'];
                            break;

                        case 'state_country':
                            $countryRs = $db->query("Select * from tbl_countries where country_id=" . intval($row['state_country']));
                            $countryRow = $db->fetch($countryRs);
                            if ($db->total_records($countryRs) > 0) {
                                echo $countryRow['country_name'];
                            }
                            break;
                        case 'action':
                            echo '<ul class="actions">';
                            if (checkAdminAddEditDeletePermission(7, '', 'edit')) {
                                echo '<li><a href="?edit=' . $row['state_id'] . '&page=' . $page . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                                if ($row['state_status'] == 'A') {
                                    echo '<li><a href="?inactive=' . $row['state_id'] . '&page=' . $page . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_MARK_INACTIVE') . '"><i class="ion-android-checkbox-blank icon"></i></a></li>';
                                }
                                if ($row['state_status'] == 'B') {
                                    echo '<li><a href="?active=' . $row['state_id'] . '&page=' . $page . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_MARK_ACTIVE') . '"><i class="ion-android-checkbox icon"></i></a></li>';
                                }
                            }
                            if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
                                echo '<li><a href="?delete=' . $row['state_id'] . '&page=' . $page . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_DELETE') . '" onclick="requestPopup(this,\'' . t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD') . '\',1);"><i class="ion-android-delete icon"></i></a></li>';
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
<script type="text/javascript">
    function deleteMultipleStates() {
        if ($('[name="states[]"]:checked').length == 0) {
            requestPopup(1, '<?php echo (t_lang('M_MSG_please_select_at_least_one_state')); ?>', 0);
            return false;
        }
        requestPopupAjax(1, '<?php echo addslashes(t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD')); ?>', 1);
    }
    function doRequiredAction() {
        if ($('[name="states[]"]:checked').length == 0) {
            requestPopup(1, '<?php echo (t_lang('M_MSG_please_select_at_least_one_state')); ?>', 0);
            return false;
        }
        state_ids = $('.tbl_data input[type="checkbox"]').serialize();
        callAjax('cities-ajax.php', state_ids + '&mode=deleteMultipleStates', function (t) {
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
