<?php
require_once './application-top.php';
require_once '../site-classes/calender.php';
$arr_common_js[] = 'js/calendar.js';
$arr_common_js[] = 'js/calendar-en.js';
$arr_common_js[] = 'js/calendar-setup.js';
$arr_common_css[] = 'css/cal-css/calendar-win2k-cold-1.css';
$arr_common_css[] = 'css/calender.css';
checkAdminPermission(12);
$mainTableName = 'tbl_press_release';
$primaryKey = 'pr_id';
$colPrefix = 'news_';
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$pagesize = 15;
$Src_frm = new Form('Src_frm', 'Src_frm');
$Src_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$Src_frm->setFieldsPerRow(2);
$Src_frm->captionInSameCell(true);
$Src_frm->addTextBox(t_lang('M_FRM_PRESS_TITLE'), 'press', $_REQUEST['press'], '', '');
$Src_frm->addHiddenField('', 'mode', 'search');
$Src_frm->addHiddenField('', 'status', $_REQUEST['status']);
$fld1 = $Src_frm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="medium" onclick=location.href="press-release.php"');
$fld = $Src_frm->addSubmitButton('', 'search', t_lang('M_TXT_SEARCH'), '', ' class="medium"')->attachField($fld1);
if (is_numeric($_GET['delete'])) {
    if (checkAdminAddEditDeletePermission(12, '', 'delete')) {
        if (!$db->query('delete from tbl_press_release where pr_id=' . $_GET['delete'])) {
            $msg->addError($db->getError());
        } else {
            $msg->addMsg(t_lang('M_TXT_RECORD_DELETED'));
            redirectUser('?page=' . $page);
        }
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
$frm = getMBSFormByIdentifier('frmPressRelease');
$frm->setAction('?page=' . $page);
updateFormLang($frm);
$fld = $frm->getField('btn_submit');
$fld->value = t_lang('M_TXT_SUBMIT');
$fld->field_caption = '&nbsp;';
//$frm->setJsErrorDisplay('summary');
$fld = $frm->getField('pr_description');
$fld->html_before_field = '<div class="frm-editor">';
$fld->html_after_field = '</div>';
if (is_numeric($_GET['edit'])) {
    if (checkAdminAddEditDeletePermission(12, '', 'edit')) {
        $record = new TableRecord('tbl_press_release');
        if (!$record->loadFromDb('pr_id=' . $_GET['edit'], true)) {
            $msg->addError($record->getError());
        } else {
            $arr = $record->getFlds();
            $arr['btn_submit'] = t_lang('M_TXT_UPDATE');
            fillForm($frm, $arr);
            /*  $frm->fill($arr); */
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
        $record = new TableRecord('tbl_press_release');
        /* $record->assignValues($post); */
        $arr_lang_independent_flds = array('pr_id', 'pr_date', 'pr_status', 'mode', 'btn_submit');
        assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
        if ((checkAdminAddEditDeletePermission(12, '', 'edit'))) {
            if ($post['pr_id'] > 0)
                $success = $record->update('pr_id' . '=' . $post['pr_id']);
        }
        if ((checkAdminAddEditDeletePermission(12, '', 'add'))) {
            if ($post['pr_id'] == '')
                $success = $record->addNew();
        }
        #$success=($post['pr_id']>0)?$record->update('pr_id' . '=' . $post['pr_id']):$record->addNew();
        if ($success) {
            $pr_id = ($post[$primaryKey] > 0) ? $post[$primaryKey] : $record->getId();
            $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
            redirectUser();
        } else {
            $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
            /* $frm->fill($post); */
            fillForm($frm, $post);
        }
    }
}
$srch = new SearchBase('tbl_press_release', 'n');
if ($_REQUEST['status'] == 'active') {
    $srch->addCondition('pr_status', '=', 1);
} else if ($_REQUEST['status'] == 'deactive') {
    $srch->addCondition('pr_status', '=', 0);
} else {
    $srch->addCondition('pr_status', '=', 1);
}
if ($_REQUEST['press'] != '') {
    $srch->addCondition('pr_title' . $_SESSION['lang_fld_prefix'], 'LIKE', '%' . $_REQUEST['press'] . '%');
}
$srch->addMultipleFields(array('n.*'));
$srch->addOrder('pr_title');
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
$pagestring .= createHiddenFormFromPost('frmPaging', '?', array('page', 'status', 'job'), array('page' => '', 'status' => $_REQUEST['status'], 'job' => $_REQUEST['job']));
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
	' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$arr_listing_fields = array(
    'pr_title' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_TITLE'),
    'pr_date' => t_lang('M_TXT_DATE'),
    'action' => t_lang('M_TXT_ACTION')
);
require_once './header.php';
$arr_bread = array(
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0);' => t_lang('M_TXT_CMS'),
    '' => t_lang('M_TXT_PRESS_RELEASE')
);
?>
<ul class="nav-left-ul">
    <li>    <a <?php if ($_REQUEST['status'] == 'active') echo 'class="selected"'; ?> href="press-release.php?status=active"><?php echo t_lang('M_TXT_ACTIVE') . ' '; ?> <?php echo t_lang('M_TXT_PRESS_RELEASE'); ?> <?php echo t_lang('M_TXT_LISTING'); ?></a></li>
    <li>    <a <?php if ($_REQUEST['status'] == 'deactive') echo 'class="selected"'; ?> href="press-release.php?status=deactive"> <?php echo t_lang('M_TXT_INACTIVE'); ?> <?php echo t_lang('M_TXT_PRESS_RELEASE'); ?> <?php echo t_lang('M_TXT_LISTING'); ?> </a></li>
</ul>
</div></td>					
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_PRESS_RELEASE'); ?>
            <?php if (checkAdminAddEditDeletePermission(12, '', 'add')) { ?>
                <ul class="actions right">
                    <li class="droplink">
                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                        <div class="dropwrap">
                            <ul class="linksvertical">
                                <li> 
                                    <a href="?page=<?php echo $page; ?>&add=new"><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_TXT_PRESS_RELEASE'); ?></a>
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
                    <div class="message error"><?php echo $msg->display(); ?> </div>
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
        if ((checkAdminAddEditDeletePermission(12, '', 'add')) || (checkAdminAddEditDeletePermission(12, '', 'edit'))) {
            ?>
            <div class="box"><div class="title"> <?php echo t_lang('M_TXT_PRESS_RELEASE'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
            <?php
        } else {
            die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        }
    } else {
        ?>
        <div class="box searchform_filter"><div class="title"> <?php echo t_lang('M_TXT_PRESS_RELEASE'); ?>	 </div><div class="content togglewrap" style="display:none;">		<?php echo $Src_frm->getFormHtml(); ?></div></div>					 
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
                echo '<tr' . (($row['pr_status'] == 0) ? ' class="inactive"' : '') . '>';
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td ' . (($key == action) ? 'width="20%"' : '') . '>';
                    switch ($key) {
                        case 'pr_date':
                            echo displayDate($row['pr_date'], true);
                            break;
                        case 'pr_title_lang1':
                            echo '<strong>' . $arr_lang_name[0] . '</strong>' . ' ' . $row['pr_title'] . '<br/>';
                            echo '<strong>' . $arr_lang_name[1] . '</strong>' . ' ' . $row['pr_title_lang1'];
                            break;
                        case 'action':
                            echo '<ul class="actions">';
                            if (checkAdminAddEditDeletePermission(12, '', 'edit')) {
                                echo '<li><a href="?edit=' . $row['pr_id'] . '&page=' . $page . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                            }
                            if (checkAdminAddEditDeletePermission(12, '', 'delete')) {
                                echo '<li><a href="?delete=' . $row['pr_id'] . '&page=' . $page . '" title="' . t_lang('M_TXT_DELETE') . '" onclick="requestPopup(this,\'' . t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD') . '\',1);"><i class="ion-android-delete icon"></i></a></li>';
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
