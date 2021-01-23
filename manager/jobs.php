<?php
require_once './application-top.php';
$arr_common_js[] = 'js/calendar.js';
$arr_common_js[] = 'js/calendar-en.js';
$arr_common_js[] = 'js/calendar-setup.js';
$arr_common_css[] = 'css/cal-css/calendar-win2k-cold-1.css';
checkAdminPermission(1);
$pagesize = 30;
$mainTableName = 'tbl_jobs';
$primaryKey = 'jobs_id';
$colPrefix = 'news_';
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$Src_frm = new Form('Src_frm', 'Src_frm');
$Src_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$Src_frm->setFieldsPerRow(2);
$Src_frm->captionInSameCell(true);
$Src_frm->addTextBox(t_lang('M_FRM_JOB_TITLE'), 'job', $_REQUEST['job'], '', '');
$Src_frm->addHiddenField('', 'mode', 'search');
$Src_frm->addHiddenField('', 'status', $_REQUEST['status']);
$fld1 = $Src_frm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="medium" onclick=location.href="jobs.php"');
$fld = $Src_frm->addSubmitButton('', 'search', t_lang('M_TXT_SEARCH'), '', ' class="medium"')->attachField($fld1);
if (is_numeric($_GET['delete'])) {
    if (checkAdminAddEditDeletePermission(1, '', 'delete')) {
        if (!$db->query('delete from tbl_jobs where jobs_id=' . $_GET['delete'])) {
            $msg->addError($db->getError());
        } else {
            $msg->addMsg(t_lang('M_TXT_RECORD_DELETED'));
            redirectUser('?');
        }
    } else {
        die('Unauthorized Access.');
    }
}
$frm = getMBSFormByIdentifier('frmJobs');
$frm->setAction('?page=' . $page);
$fld = $frm->getField('jobs_city_id');
$fld->value = $_REQUEST['jobs_city_id'];
$fld1 = $frm->getField('btn_submit');
$fld1->value = t_lang('M_TXT_SUBMIT');
$fld2 = $frm->getField('jobs_category');
$cat_list = $db->query("select job_category_id, job_category_name" . $_SESSION['lang_fld_prefix'] . " as job_category_name from tbl_job_catagory");
$fld2->options = $db->fetch_all_assoc($cat_list);
$fld2->selectCaption = t_lang('M_TXT_SELECT');
$fld3 = $frm->getField('jobs_city_id');
$job_list = $db->query("select city_id, city_name" . $_SESSION['lang_fld_prefix'] . " from tbl_cities where city_active=1 and city_deleted=0 and city_request=0 order by city_name");
$fld3->selectCaption = t_lang('M_TXT_SELECT');
$fld3->options = $db->fetch_all_assoc($job_list);
//print_r($job_list);
updateFormLang($frm);
$fld = $frm->getField('jobs_description');
$fld->html_before_field = '<div class="frm-editor">';
$fld->html_after_field = '</div>';
//$frm->setJsErrorDisplay('summary');
if (is_numeric($_GET['edit'])) {
    if (checkAdminAddEditDeletePermission(1, '', 'edit')) {
        $record = new TableRecord('tbl_jobs');
        if (!$record->loadFromDb('jobs_id=' . $_GET['edit'], true)) {
            $msg->addError($record->getError());
        } else {
            $arr = $record->getFlds();
            $arr['btn_submit'] = t_lang('M_TXT_UPDATE');
            fillForm($frm, $arr);
            /*  $frm->fill($arr); */
            $msg->addMsg(t_lang('M_TXT_Edit_THE_VALUES_AND_UPDATE'));
        }
    } else {
        die('Unauthorized Access.');
    }
}
if (isset($_POST['btn_submit'])) {
    $post = getPostedData();
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error)
            $msg->addError($error);
    } else {
        $record = new TableRecord('tbl_jobs');
        /* $record->assignValues($post); */
        $arr_lang_independent_flds = array('jobs_id', 'jobs_status', 'jobs_date', 'jobs_category', 'jobs_city_id', 'mode', 'btn_submit');
        assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
        if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
            if ($post['jobs_id'] > 0)
                $success = $record->update('jobs_id' . '=' . $post['jobs_id']);
        }
        if ((checkAdminAddEditDeletePermission(1, '', 'add'))) {
            if ($post['jobs_id'] == '')
                $success = $record->addNew();
        }
        #$success=($post['jobs_id']>0)?$record->update('jobs_id' . '=' . $post['jobs_id']):$record->addNew();
        if ($success) {
            $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
            redirectUser('?');
        } else {
            $msg->addError('Could not add/update! Error: ' . $record->getError());
            /* $frm->fill($post); */
            fillForm($frm, $post);
        }
    }
}
$srch = new SearchBase('tbl_jobs', 'n');
if ($_REQUEST['status'] == 'active') {
    $srch->addCondition('jobs_status', '=', 1);
} else if ($_REQUEST['status'] == 'deactive') {
    $srch->addCondition('jobs_status', '=', 0);
} else {
    $srch->addCondition('jobs_status', '=', 1);
}
if ($_REQUEST['job'] != '') {
    $srch->addCondition('jobs_title' . $_SESSION['lang_fld_prefix'], 'LIKE', '%' . $_REQUEST['job'] . '%');
}
$srch->joinTable('tbl_cities', 'INNER JOIN', 'n.jobs_city_id=c.city_id', 'c');
$srch->joinTable('tbl_job_catagory', 'INNER JOIN', 'n.jobs_category=jc.job_category_id', 'jc');
$srch->addMultipleFields(array('n.*', 'c.city_name' . $_SESSION['lang_fld_prefix'], 'jc.job_category_name' . $_SESSION['lang_fld_prefix']));
$srch->addOrder('jobs_title');
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
    'jobs_title' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_TITLE'),
    'job_category_name' . $_SESSION['lang_fld_prefix'] => t_lang('M_TXT_CATAGORY'),
    'city_name' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_CITY'),
    'jobs_date' => t_lang('M_TXT_DATE'),
    'action' => t_lang('M_TXT_ACTION')
);
require_once './header.php';
$arr_bread = array(
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0);' => t_lang('M_TXT_CMS'),
    '' => t_lang('M_TXT_JOBS')
);
?>
<ul class="nav-left-ul">
    <li>    <a <?php if ($_REQUEST['status'] == 'active') echo 'class="selected"'; ?> href="jobs.php?status=active"> <?php echo t_lang('M_TXT_ACTIVE') . ' '; ?> <?php echo t_lang('M_TXT_JOBS'); ?> <?php echo t_lang('M_TXT_LISTING'); ?> </a></li>
    <li>    <a <?php if ($_REQUEST['status'] == 'deactive') echo 'class="selected"'; ?> href="jobs.php?status=deactive"> <?php echo t_lang('M_TXT_INACTIVE') . ' '; ?> <?php echo t_lang('M_TXT_JOBS'); ?> <?php echo t_lang('M_TXT_LISTING'); ?> </a></li>
</ul>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_JOBS'); ?> 
            <?php if (checkAdminAddEditDeletePermission(1, '', 'add')) { ?>
                <ul class="actions right">
                    <li class="droplink">
                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                        <div class="dropwrap">
                            <ul class="linksvertical">
                                <li>
                                    <a href="?page=<?php echo $page; ?>&add=new"><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_TXT_JOBS'); ?></a>
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
        if ((checkAdminAddEditDeletePermission(1, '', 'add')) || (checkAdminAddEditDeletePermission(1, '', 'edit'))) {
            ?>
            <div class="box"><div class="title"> <?php echo t_lang('M_TXT_JOBS'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
            <?php
        } else {
            die('Unauthorized Access.');
        }
    } else {
        ?>
        <div class="box searchform_filter"><div class="title"> <?php echo t_lang('M_TXT_JOBS'); ?>	 </div><div class="content togglewrap" style="display:none;">		<?php echo $Src_frm->getFormHtml(); ?>	</div></div>					 
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
                echo '<tr' . (($row['jobs_status'] == 0) ? ' class="inactive"' : '') . '>';
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td ' . (($key == action) ? 'width="20%"' : '') . '>';
                    switch ($key) {
                        case 'jobs_date':
                            echo displayDate($row['jobs_date'], true);
                            break;
                        case 'jobs_title_lang1':
                            echo '<strong>' . $arr_lang_name[0] . '</strong>' . ' ' . $row['jobs_title'] . '<br/>';
                            echo '<strong>' . $arr_lang_name[1] . '</strong>' . ' ' . $row['jobs_title_lang1'];
                            break;
                        case 'job_category_name_lang1':
                            echo $row['job_category_name_lang1'];
                            break;
                        case 'action':
                            echo '<ul class="actions">';
                            if (checkAdminAddEditDeletePermission(1, '', 'edit')) {
                                echo '<li><a href="?edit=' . $row['jobs_id'] . '&page=' . $page . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                            }
                            if (checkAdminAddEditDeletePermission(1, '', 'delete')) {
                                echo '<li><a href="?delete=' . $row['jobs_id'] . '&page=' . $page . '" title="' . t_lang('M_TXT_DELETE') . '" onclick="requestPopup(this,\'' . t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD') . '\',1);"><i class="ion-android-delete icon"></i></a></li>';
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
<?php
require_once './footer.php';
?>
