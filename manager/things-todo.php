<?php
require_once './application-top.php';
$arr_common_js[] = 'js/calendar.js';
$arr_common_js[] = 'js/calendar-en.js';
$arr_common_js[] = 'js/calendar-setup.js';
$arr_common_css[] = 'css/cal-css/calendar-win2k-cold-1.css';
checkAdminPermission(7);
$mainTableName = 'tbl_things_todo';
$primaryKey = 'things_id';
$colPrefix = 'news_';
$pagesize = 10;
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$Src_frm = new Form('Src_frm', 'Src_frm');
$Src_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$Src_frm->setFieldsPerRow(2);
$Src_frm->captionInSameCell(true);
$Src_frm->addTextBox(t_lang('M_FRM_NAME'), 'things', $_REQUEST['things'], '', '');
$Src_frm->addHiddenField('', 'mode', 'search');
$Src_frm->addHiddenField('', 'things_city_id', $_REQUEST['things_city_id']);
$Src_frm->addHiddenField('', 'status', $_REQUEST['status']);
$fld1 = $Src_frm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="medium" onclick=location.href="things-todo.php?things_city_id=' . $_REQUEST['things_city_id'] . '"');
$fld = $Src_frm->addSubmitButton('', 'search', t_lang('M_TXT_SEARCH'), '', ' class="medium"')->attachField($fld1);
if ($_REQUEST['things_city_id'] > 0) {
    $things_city_id = $_REQUEST['things_city_id'];
} else {
    redirectUser('cities.php');
}
if (is_numeric($_GET['delete'])) {
    if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
        if (!$db->query('delete from tbl_things_todo where things_id=' . $_GET['delete'])) {
            $msg->addError($db->getError());
        } else {
            $msg->addMsg(t_lang('M_TXT_RECORD_DELETED'));
            redirectUser('?things_city_id=' . $_REQUEST['things_city_id'] . '&page=' . $page);
        }
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
$frm = getMBSFormByIdentifier('frmThingsTodo');
$frm->setAction('?things_city_id=' . $things_city_id . 'page=' . $page);
$fld = $frm->getField('things_city_id');
$fld->value = $_REQUEST['things_city_id'];
updateFormLang($frm);
$fld = $frm->getField('btn_submit');
$fld->value = t_lang('M_TXT_SUBMIT');
if (is_numeric($_GET['edit'])) {
    if (checkAdminAddEditDeletePermission(7, '', 'edit')) {
        $record = new TableRecord('tbl_things_todo');
        if (!$record->loadFromDb('things_id=' . $_GET['edit'], true)) {
            $msg->addError($record->getError());
        } else {
            $arr = $record->getFlds();
            $arr['btn_submit'] = t_lang('M_TXT_UPDATE');
            $frm->fill($arr);
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
        if (is_uploaded_file($_FILES['things_image']['tmp_name'])) {
            $ext = strtolower(strrchr($_FILES['things_image']['name'], '.'));
            if ((!in_array($ext, array('.gif', '.jpg', '.jpeg', '.png'))) || ($_FILES['things_image']['size'] > CONF_IMAGE_MAX_SIZE)) {
                $msg->addError(t_lang('M_TXT_THINGS') . ' ' . t_lang('M_TXT_IMAGE_NOT_SUPPORTED_OR_SIZE_EXCEEDED'));
                fillForm($frm, $post);
                $succeed = false;
            }
        }
        if (true === $succeed) {
            $record = new TableRecord('tbl_things_todo');
            $arr_lang_independent_flds = ['things_id', 'things_city_id', 'things_display_id', 'things_date', 'things_description', 'things_image', 'things_image_by', 'things_neighbourhood', 'things_status', 'mode', 'btn_submit'];
            assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
            if ((checkAdminAddEditDeletePermission(7, '', 'edit'))) {
                if ($post['things_id'] > 0) {
                    $success = $record->update('things_id' . '=' . $post['things_id']);
                }
            }
            if ((checkAdminAddEditDeletePermission(7, '', 'add'))) {
                if ($post['things_id'] == '') {
                    $success = $record->addNew();
                }
            }
            if ($success) {
                $things_id = ($post[$primaryKey] > 0) ? $post[$primaryKey] : $record->getId();
                if (is_uploaded_file($_FILES['things_image']['tmp_name'])) {
                    $flname = time() . '_' . $_FILES['things_image']['name'];
                    if (!move_uploaded_file($_FILES['things_image']['tmp_name'], THINGS_IMAGES_PATH . $flname)) {
                        $msg->addError(t_lang('M_TXT_FILE_COULD_NOT_SAVE'));
                    } else {
                        $getImg = $db->query("select * from tbl_things_todo where things_id='" . $things_id . "'");
                        $imgRow = $db->fetch($getImg);
                        unlink(THINGS_IMAGES_PATH . $imgRow['things_image' . $_SESSION['lang_fld_prefix']]);
                        $db->update_from_array('tbl_things_todo', ['things_image' => $flname], 'things_id=' . $things_id);
                    }
                }
                $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
                redirectUser('?things_city_id=' . $post['things_city_id']);
            } else {
                $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
                /* $frm->fill($post); */
                fillForm($frm, $post);
            }
        }
    }
}
$srch = new SearchBase('tbl_things_todo', 'n');
if ($_REQUEST['status'] == 'active') {
    $srch->addCondition('things_status', '=', 1);
} else if ($_REQUEST['status'] == 'deactive') {
    $srch->addCondition('things_status', '=', 0);
} else {
    $srch->addCondition('things_status', '=', 1);
}
if ($_REQUEST['things'] != '') {
    $srch->addCondition('things_title', 'LIKE', '%' . $_REQUEST['things'] . '%');
}
$srch->addCondition('things_city_id', '=', $_REQUEST['things_city_id']);
$srch->addMultipleFields(['n.*']);
$srch->addOrder('things_title');
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
$pagestring .= createHiddenFormFromPost('frmPaging', '?', ['page', 'status', 'things', 'things_city_id'], ['page' => '', 'status' => $_REQUEST['status'], 'things' => $_REQUEST['things'], 'things_city_id' => $_REQUEST['things_city_id']]);
$pagestring .= createHiddenFormFromPost('frmPaging', '?', ['page', 'status'], ['page' => '', 'status' => $_REQUEST['status']]);
$pagestring .= '<div class="pagination"><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a herf="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> ',
                $srch->pages(), $page, '<li class="selected"><a herf="javascript:void(0);" class="active">xxpagexx</a></li>');
$pagestring .= '</div>';
$arr_listing_fields = [
    'things_image' => t_lang('M_TXT_IMAGE'),
    'things_title' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_NAME'),
    'things_date' => t_lang('M_TXT_DATE'),
    'action' => t_lang('M_TXT_ACTION')
];
require_once './header.php';
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'cities.php' => t_lang('M_FRM_CITY'),
    '' => t_lang('M_TXT_THING_TO_DO')
];
?>
<ul class="nav-left-ul">
    <li>    <a <?php if ($_REQUEST['status'] == 'active') echo 'class="selected"'; ?> href="things-todo.php?things_city_id=<?php echo $_REQUEST['things_city_id']; ?>&status=active"><?php echo t_lang('M_TXT_ACTIVE_THINGS_LISTING') ?></a></li>
    <li>    <a <?php if ($_REQUEST['status'] == 'deactive') echo 'class="selected"'; ?> href="things-todo.php?things_city_id=<?php echo $_REQUEST['things_city_id']; ?>&status=deactive"><?php echo t_lang('M_TXT_INACTIVE_THINGS_LISTING') ?></a></li>
</ul>
</div></td>					
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_THING_TO_DO'); ?> 
            <?php if (checkAdminAddEditDeletePermission(7, '', 'add')) { ?>
                <ul class="actions right">
                    <li class="droplink">
                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                        <div class="dropwrap">
                            <ul class="linksvertical">
                                <li>										 
                                    <a href="?things_city_id=<?php echo $_REQUEST['things_city_id']; ?>&page=<?php echo $page; ?>&add=new"><?php echo t_lang('M_TXT_ADD_NEW'); ?></a>
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
    <?php
    if (is_numeric($_REQUEST['edit']) || $_REQUEST['add'] == 'new') {
        if ((checkAdminAddEditDeletePermission(7, '', 'add')) || (checkAdminAddEditDeletePermission(7, '', 'edit'))) {
            ?>
            <div class="box"><div class="title"> <?php echo t_lang('M_TXT_THING_TO_DO'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
            <?php
        } else {
            die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        }
    } else {
        ?>
        <div class="box searchform_filter"><div class="title"> <?php echo t_lang('M_TXT_THING_TO_DO'); ?>	 </div><div class="content togglewrap" style="display:none;"> <?php echo $Src_frm->getFormHtml(); ?> </div></div>
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
                echo '<tr' . (($row['things_status'] == 0) ? ' class="inactive"' : '') . '>';
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td>';
                    switch ($key) {
                        case 'things_image':
                            if ($row['things_image'] != "") {
                                echo '<img src="' . THINGS_IMAGES_URL . $row['things_image'] . '" width="55" height="60" />';
                            } else {
                                echo t_lang('M_TXT_NO_IMAGE_UPLOADED');
                            }
                            break;
                        case 'things_title_lang1':
                            echo '<strong>' . $arr_lang_name[0] . '</strong>' . ' ' . $row['things_title'] . '<br/>';
                            echo '<strong>' . $arr_lang_name[1] . '</strong>' . ' ' . $row['things_title_lang1'];
                        case 'action':
                            echo '<ul class="actions">';
                            if (checkAdminAddEditDeletePermission(7, '', 'edit')) {
                                echo '<li><a href="?things_city_id=' . $row['things_city_id'] . '&edit=' . $row['things_id'] . '&page=' . $page . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                            }
                            if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
                                echo '<li><a href="?things_city_id=' . $row['things_city_id'] . '&delete=' . $row['things_id'] . '&page=' . $page . '" title="' . t_lang('M_TXT_DELETE') . '" onclick="requestPopup(this,\'' . t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD') . '\',1);"><i class="ion-android-delete icon"></i></a></li>';
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
<?php
require_once './footer.php';
