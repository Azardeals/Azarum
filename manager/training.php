<?php
require_once './application-top.php';
require_once '../includes/navigation-functions.php';
checkAdminPermission(1);
$mainTableName = 'tbl_training_video';
$primaryKey = 'tv_id';
$colPrefix = 'tv_';
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$frm = new Form('frm_training', 'frm_training');
$frm->setAction('');
$frm->setTableProperties(' width="100%" cellpadding="0" cellspacing="0" class="tbl_form"');
$frm->setJsErrorDisplay('afterfield');
$frm->setLeftColumnProperties(' style="padding: 5px;"');
$frm->addHiddenField('', 'tv_id', '', 'tv_id');
$fld = $frm->addRequiredField(t_lang('M_FRM_VIDEO_CAPTION'), 'tv_title', '', 'tv_title', 'class="input"');
$fld = $frm->addTextArea(t_lang('M_FRM_VIDEO_LINK'), 'tv_link', '', 'tv_link', 'class="input"')->Requirements()->setRequired();
$fld = $frm->addSelectBox(t_lang('M_TXT_VIDEO_FOR'), 'tv_user', ['1' => t_lang('M_TXT_REPRESENTATIVE'), '2' => t_lang('M_TXT_MERCHANT')], 'tv_user', 'class="input"')->Requirements()->setRequired();
$fld = $frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SUBMIT'), '', ' class="medium"');

if (is_numeric($_GET['delete']) && $_GET['delete'] > 1) {
    if ((checkAdminAddEditDeletePermission(1, '', 'delete'))) {
        deleteTrainingVideo($_GET['delete']);
        redirectUser('?page=' . $page);
    } else {
        die(t_lang("M_TXT_UNAUTHORIZED_ACCESS"));
    }
}

if (is_numeric($_REQUEST['edit'])) {
    if (checkAdminAddEditDeletePermission(1, '', 'edit')) {
        $record = new TableRecord('tbl_training_video');
        if (!$record->loadFromDb('tv_id=' . $_REQUEST['edit'], true)) {
            $msg->addError($record->getError());
        } else {
            $arr = $record->getFlds();
            $arr['btn_submit'] = t_lang('M_TXT_UPDATE');
            $selected_state = $arr['city_state'];
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
        $record = new TableRecord('tbl_training_video');
        $arr_lang_independent_flds = ['tv_id', 'tv_status', 'tv_display_order', 'tv_link', 'btn_submit'];
        assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
        if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
            if ($post['tv_id'] > 0) {
                $success = $record->update('tv_id' . '=' . $post['tv_id']);
            }
        }
        if ((checkAdminAddEditDeletePermission(1, '', 'add'))) {
            if ($post['tv_id'] == '') {
                $record->setFldValue('tv_status', 1);
                $success = $record->addNew();
            }
        }
        if ($success) {
            $tv_id = ($post[$primaryKey] > 0) ? $post[$primaryKey] : $record->getId();
            $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
            redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
        } else {
            $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
            fillForm($frm, $arr);
        }
    }
}
$srch = new SearchBase('tbl_training_video', 'c');
$srch->addOrder('tv_display_order', 'asc');
$rs_listing = $srch->getResultSet();
$pages = $srch->pages();
$arr_listing_fields = [
    'tv_title' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_NAME'),
    'tv_user' => t_lang('M_TXT_VIDEO_FOR'),
    'action' => t_lang('M_TXT_ACTION')
];
require_once './header.php';
$arr_bread = [
    'index.php' => '<img alt="Home" src="images/home-icon.png">',
    'javascript:void(0)' => t_lang('M_TXT_CMS'),
    '' => t_lang('M_TXT_TRAINING_VIDEO')
];
?>
<script type="text/javascript">
    $(document).ready(function () {
<?php if (checkAdminAddEditDeletePermission(1, '', 'edit')) { ?>
            //Table DND call
            $('#nav-listing').tableDnD({
                onDrop: function (table, row) {
                    var order = $.tableDnD.serialize('id');
                    callAjax('cms-ajax.php', order + '&mode=REORDER_TRAINING', function (t) { });
                }
            });
<?php } ?>
    });
</script>
</div></td>
<td class="right-portion"> <?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_TRAINING_VIDEO'); ?>
            <?php if (checkAdminAddEditDeletePermission(1, '', 'add')) { ?>
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
                <?php } if (isset($_SESSION['msgs'][0])) { ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
    <?php if (is_numeric($_REQUEST['edit']) || $_REQUEST['add'] == 'new') { ?>
        <?php if ((checkAdminAddEditDeletePermission(1, '', 'add')) || (checkAdminAddEditDeletePermission(1, '', 'edit'))) { ?>
            <div class="box"><div class="title"> <?php echo t_lang('M_TXT_TRAINING_VIDEO'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
            <?php
        } else {
            die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        }
    } else {
        ?>
        <table class="tbl_data" width="100%" id="nav-listing">
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
                echo '<tr id = ' . $row['tv_id'] . '>';
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td ' . (($key == action) ? 'width="20%"' : '') . '>';
                    switch ($key) {
                        case 'tv_title':
                            echo $row['tv_title'] . '<br/>';
                            break;
                        case 'tv_user':
                            if ($row['tv_user'] == 1) {
                                echo t_lang('M_TXT_REPRESENTATIVE');
                            }
                            if ($row['tv_user'] == 2) {
                                echo t_lang('M_TXT_MERCHANT');
                            }
                            break;
                        case 'action':
                            if (checkAdminAddEditDeletePermission(1, '', 'edit')) {
                                echo '<ul class="actions">';
                                if (checkAdminAddEditDeletePermission(1, '', 'edit')) {
                                    echo '<li><a href="?edit=' . $row['tv_id'] . '&page=' . $page . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                                }
                                if (checkAdminAddEditDeletePermission(1, '', 'delete')) {
                                    echo '<li><a href="?delete=' . $row['tv_id'] . '&page=' . $page . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_DELETE') . '"><i class="ion-android-delete icon"></i></a></li>';
                                }
                                echo '</ul>';
                            }
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
    <?php } ?>
</td>
<?php
require_once './footer.php';
