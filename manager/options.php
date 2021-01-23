<?php
require_once './application-top.php';
checkAdminPermission(5);
$post = getPostedData();
loadModels(['OptionModel']);
/**
 * OPTION CLASS SEARCH FORM 
 * */
$srchForm = Option::getSearchForm();
$optionObj = new Option();
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
/**
 * OPTION CLASS DELETE CONDITION
 * */
if (is_numeric($_GET['delete'])) {
    if ((checkAdminAddEditDeletePermission(5, '', 'delete'))) {
        if (!$optionObj::recordExists(Option::DB_TBL, Option::DB_TBL_PRIMARY_KEY, $_GET['delete'])) {
            $msg->addError(t_lang('M_TXT_RECORD_NOT_EXIST'));
            redirectUser('options.php');
        }
        $success = $optionObj->deleteRestoreOption($_GET['delete'], 1);
        if ($success) {
            $msg->addMsg(T_lang('M_TXT_MARKED_AS_DELETED_SUCCESSFULL'));
        } else {
            $msg->addError(T_lang('M_TXT_DELETION_ERROR'));
        }
        redirectUser('options.php');
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
/**
 * OPTION CLASS DELETE CONDITION
 * */
if (is_numeric($_GET['restore'])) {
    if ((checkAdminAddEditDeletePermission(5, '', 'delete'))) {
        if (!$optionObj::recordExists(Option::DB_TBL, Option::DB_TBL_PRIMARY_KEY, $_GET['restore'])) {
            $msg->addError(t_lang('M_TXT_RECORD_NOT_EXIST'));
            redirectUser('options.php');
        }
        $success = $optionObj->deleteRestoreOption($_GET['restore'], 0);
        if ($success) {
            $msg->addMsg(T_lang('M_TXT_RESTORED_SUCCESSFULL'));
        } else {
            $msg->addError(T_lang('M_TXT_RESTORE_ERROR'));
        }
        redirectUser('options.php');
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
/**
 * OPTION CLASS FORM 
 * */
$frm = Option::getForm();
$option_value_row = 1;
$option_values = '<table id="option-value" class="tbl_form tbl_form_option" width="100%" border="0" cellspacing="0" cellpadding="0">';
$option_values .= '<tr><th><b>' . t_lang('M_TXT_OPTION_VALUE_NAME') . ':<span class="spn_must_field">*</span></b></th><th>' . t_lang('M_TXT_SORT_ORDER') . '</th><th>' . t_lang('M_TXT_ACTION') . '</th></tr>';
/**
 * OPTION CLASS EDIT CONDITION 
 * */
if (isset($_GET['edit']) && intval($_GET['edit']) > 0) {
    global $db;
    $option_id = intval($_GET['edit']);
    if (!isset($option_id) || intval($option_id) <= 0) {
        $msg->addError(t_lang('M_TXT_INVALID_REQUEST'));
        redirectUser('options.php');
    }
    if (!$optionObj::recordExists(Option::DB_TBL, Option::DB_TBL_PRIMARY_KEY, $option_id)) {
        $msg->addError(t_lang('M_TXT_RECORD_NOT_EXIST'));
        redirectUser('options.php');
    }
    /* GET OPTION VALUES */
    $arrValues = $optionObj->getOptionValues($option_id);
    foreach ($arrValues as $val) {
        $id = 'option-value-row' . $option_value_row;
        $click = "$('#$id').remove();";
        $click = "requestPopupAjax(this,\'' . t_lang('M_TXT_ARE_YOU_SURE_TO_DELETE?') . '\',1)";
        $click = "removeRecord(" . $val['option_value_id'] . ",'" . $id . "')";
        $option_values .= '<tr id="option-value-row' . $option_value_row . '"><td><input type="hidden" value="' . $val['option_value_id'] . '" name="option_value_id[]"><input type="text" value="' . $val['name'] . '" name="option_value_name[]"></td><td><input type="text" size="1" value="' . $val['sort_order'] . '" name="option_value_sort_order[]"></td><td><ul class="actions"><li><a onclick="requestPopupAjax(' . $val['option_value_id'] . ',\'' . t_lang('M_TXT_ARE_YOU_SURE_TO_DELETE?') . '\',1)" title="' . t_lang('M_TXT_REMOVE') . '"><i class="ion-minus icon"></i></a></li></ul></td></tr>';
        $option_value_row++;
    }
}
$option_values .= '<tfoot><tr>
	<td colspan="2"></td>
	<td><ul class="actions"><li><a onclick="addOptionValue()" title="' . t_lang('M_TXT_ADD_NEW_OPTION_VALUE') . '"><i class="ion-plus icon"></i></a></li></ul></td>
</tr></tfoot>';
$option_values .= '</table>';
$frm->addHTML('', '', $option_values, true);
$fld1 = $frm->addSubmitButton('', 'btn_submit', 'SUBMIT', 'btn_submit', '');
$fld = $frm->addButton('', 'btn_submit_cancel', t_lang('M_TXT_CANCEL'), '', ' class="inputbuttons" onclick=location.href="options.php"')->attachField($fld1);
$frm->setAction('?page=' . $page . '&add=new');
updateFormLang($frm);
/**
 * OPTION CLASS UPDATE FORM FILL UP 
 * */
if (is_numeric($_GET['edit'])) {
    if ((checkAdminAddEditDeletePermission(5, '', 'edit'))) {
        $record = new TableRecord(Option::DB_TBL);
        if (!$record->loadFromDb(Option::DB_TBL_PRIMARY_KEY . '=' . $_GET['edit'], true)) {
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
/**
 * OPTION CLASS POSTED FORM
 * */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['btn_search']) && isset($_POST['btn_submit'])) {
    $post = getPostedData();
    $post['option_type'] = "select";
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error) {
            $msg->addError($error);
        }
    } else if (count($post['option_value_name']) <= 0) {
        $msg->addError(t_lang('M_TXT_OPTION_VALUES_REQUIRED'));
        fillForm($frm, $post);
    } else {
        if (isset($post['option_name']) && ($_SESSION['lang_fld_prefix'] == '_lang1')) {
            $post['option_name' . $_SESSION['lang_fld_prefix']] = $post['option_name'];
            unset($post['option_name']);
        }
        $record = new TableRecord(Option::DB_TBL);
        $record->assignValues($post);
        if ((checkAdminAddEditDeletePermission(5, '', 'edit'))) {
            if ($post[Option::DB_TBL_PRIMARY_KEY] > 0) {
                $success = $record->update(Option::DB_TBL_PRIMARY_KEY . '=' . $post[Option::DB_TBL_PRIMARY_KEY]);
            }
        }
        if ((checkAdminAddEditDeletePermission(5, '', 'add'))) {
            if ($post[Option::DB_TBL_PRIMARY_KEY] == '') {
                $success = $record->addNew();
            }
        }
        if ($success) {
            $option_id = ($post[Option::DB_TBL_PRIMARY_KEY] > 0) ? $post[Option::DB_TBL_PRIMARY_KEY] : $record->getId();
            foreach ($post['option_value_name'] as $key => $value) {
                if ($value == '') {
                    continue;
                }
                $record = new TableRecord('tbl_option_values');
                $values_arr = ['option_id' => $option_id, 'option_value_id' => $post['option_value_id'][$key], 'name' => $value, 'sort_order' => $post['option_value_sort_order'][$key]];
                //print_r($values_arr);
                $record->assignValues($values_arr);
                $success = $record->addNew(['IGNORE'], $values_arr);
            }
            $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
            redirectUser('options.php');
        } else {
            $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
            /* $frm->fill($post); */
            fillForm($frm, $post);
        }
    }
}
/**
 * OPTION CLASS SEARCH
 * */
$srch = Option::getSearchObject();
$srch->addOrder('op.option_name');
$srch->addFld('op.*');
if ($post['mode'] == 'search') {
    if ($post['keyword'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('op.option_name' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
    }
    $srchForm->fill($post);
}
$pagesize = 15;
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
$pagestring .= createHiddenFormFromPost('frmPaging', '?', ['page'], ['page' => '']);
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
		' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$arr_listing_fields = [
    'listserial' => t_lang('M_TXT_SR_NO'),
    'option_name' . $_SESSION['lang_fld_prefix'] => t_lang('M_TXT_OPTION_NAME'),
    'status' => '', //t_lang('M_FRM_STATUS'),
    'action' => t_lang('M_TXT_ACTION')
];
require_once './header.php';
$arr_bread = [
    'index.php' => '<img alt="Home" src="images/home-icon.png">',
    'deals.php' => t_lang('M_TXT_DEALS') . '/' . t_lang('M_TXT_PRODUCTS'),
    '' => t_lang('M_TXT_OPTIONS')
];
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_OPTIONS'); ?> 
            <?php if (checkAdminAddEditDeletePermission(15, '', 'add')) { ?>
                <ul class="actions right">
                    <li class="droplink">
                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                        <div class="dropwrap">
                            <ul class="linksvertical">
                                <li>
                                    <a href="?page=<?php echo $page; ?>&add=new" ><?php echo t_lang('M_TXT_ADD_NEW_OPTION'); ?></a>
                                </li>
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
                <?php } if (isset($_SESSION['msgs'][0])) {
                    ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
        <?php
    }
    if (is_numeric($_REQUEST['edit']) || $_REQUEST['add'] == 'new') {
        if ((checkAdminAddEditDeletePermission(3, '', 'add')) || (checkAdminAddEditDeletePermission(3, '', 'edit'))) {
            ?>
            <div class="box"><div class="title"> <?php echo t_lang('M_TXT_OPTIONS'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
            <?php
        } else {
            die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        }
    } else {
        ?>
        <div class="box searchform_filter"><div class="title"> <?php echo t_lang('M_TXT_OPTIONS'); ?> </div><div class="content togglewrap" style="display:none;">	 <?php echo $srchForm->getFormHtml(); ?>
            </div></div>
        <table class="tbl_data" width="100%" id="category_listing1">
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
                echo '<tr class=" ' . $even . ' " ' . (($row[$colPrefix . 'active'] == '0') ? ' class="inactive"' : '') . ' id = ' . $row['cat_id'] . '>';
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td>';
                    switch ($key) {
                        case 'listserial':
                            echo $listserial;
                            break;
                        case 'option_name_lang1':
                            $level = strlen($row['cat_code']) / 5 - 1;
                            for ($i = 0; $i < $level; $i++) {
                                echo '&mdash;&raquo;&nbsp;';
                            }
                            echo '<strong>' . $arr_lang_name[0] . '</strong>' . ' ' . $row['option_name'] . '<br/>';
                            echo '<strong>' . $arr_lang_name[1] . '</strong>' . ' ' . $row['option_name_lang1'];
                            break;
                        case 'status':
                            echo ( $row['is_deleted'] == 1 ) ? '<span class="label label-success">' . t_lang('M_TXT_MARKED_AS_DELETED') . '</span>' : '';
                            break;
                        case 'action':
                            echo '<ul class="actions">';
                            if ((checkAdminAddEditDeletePermission(15, '', 'edit'))) {
                                echo '<li><a href="?edit=' . $row[Option::DB_TBL_PRIMARY_KEY] . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                            }
                            if (!($row['children'] > 0)) {
                                if ((checkAdminAddEditDeletePermission(15, '', 'delete')) && $row['is_deleted'] == 0) {
                                    echo '<li><a href="?delete=' . $row[Option::DB_TBL_PRIMARY_KEY] . '" title="' . t_lang('M_TXT_DELETE') . '" onclick="requestPopup(this,\'' . t_lang('M_TXT_Are_you_want_to_delete_the_option?') . '\',1);"><i class="ion-android-delete icon"></i></a></li>';
                                }
                                if ((checkAdminAddEditDeletePermission(15, '', 'delete')) && $row['is_deleted'] == 1) {
                                    echo '<li><a href="?restore=' . $row[Option::DB_TBL_PRIMARY_KEY] . '" title="' . t_lang('M_TXT_RESTORE') . '" onclick="requestPopup(this,\'' . t_lang('M_TXT_Are_you_want_to_restore_the_option?') . '\',1);"><i class="ion-archive icon"></i></a></li>';
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
    <?php } ?>
    <?php if (!isset($_GET['edit']) && $_GET['add'] != 'new' && ($srch->pages() > 1)) { ?>
        <div class="footinfo">
            <aside class="grid_1">
                <?php echo $pagestring; ?>	 
            </aside>  
            <aside class="grid_2"><span class="info"><?php echo $pageStringContent; ?></span></aside>
        </div>
    <?php } ?> 
</td>
<script type="text/javascript">
    $(document).ready(function () {
        addOptionValue();
    });
    txtoptiondel = "<?php echo t_lang('M_TXT_OPTION_DELETION_NOT_ALLOWED'); ?>";
    txtsuredel = "<?php echo t_lang('M_TXT_ARE_YOU_SURE_TO_DELETE'); ?>";
    txtRestore = "<?php echo t_lang('M_TXT_RESTORE_DELETION'); ?>";
    var remove = "<?php echo t_lang('M_TXT_REMOVE'); ?>";
    var option_value_row = <?php echo $option_value_row; ?>;
    function addOptionValue() {
        html = '<tr id="option-value-row' + option_value_row + '">';
        html += '    <td><input type="text" name="option_value_name[]" value="" />';
        html += '    </td>';
        html += '    <td><input type="text" name="option_value_sort_order[]" value="" size="1" /></td>';
        html += '    <td><ul class="actions"><li><a onclick="$(\'#option-value-row' + option_value_row + '\').remove();" title="' + remove + '"><i class="ion-minus icon"></i></a></li></ul></td>';
        html += '  </tr>';
        $('#option-value tfoot').before(html);
        option_value_row++;
    }
    function doRequiredAction(t) {
        optionValueId = t;
        row_id = '';
        callAjax('deals-ajax.php', 'mode=deleteOptionValue&option_value_id=' + optionValueId, function (t) {
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