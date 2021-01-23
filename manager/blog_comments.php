<?php
require_once './application-top.php';
checkAdminPermission(1);
$blog_id = (int) $_GET['id'];
$primaryKey = 'comment_id';
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$pagesize = 30;
/** Get blog details * */
$srch = new SearchBase('tbl_blogs', 'b');
$srch->addCondition('blog_id', '=', $blog_id);
$srch->addMultipleFields(array('b.blog_title'));
$rs_listing = $srch->getResultSet();
$blog_data = $db->fetch($rs_listing);
$blog_data['blog_title'] = htmlentities($blog_data['blog_title'], ENT_QUOTES, 'UTF-8');
// $row['blog_title']= htmlentities($row['blog_title'], ENT_QUOTES, 'UTF-8');
/* * ------* */
/** Search form * */
$Src_frm = new Form('Src_frm', 'Src_frm');
$Src_frm->setTableProperties('border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$Src_frm->setFieldsPerRow(2);
$Src_frm->captionInSameCell(true);
$Src_frm->addTextBox(t_lang('M_TXT_KEYWORDS'), 'comment_keywords', $_REQUEST['comment_keywords'], '', '');
$Src_frm->addHiddenField('', 'mode', 'search');
$fld1 = $Src_frm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="medium" onclick=location.href="blog_comments.php?id=' . $blog_id . '"');
$fld = $Src_frm->addSubmitButton('', 'search', t_lang('M_TXT_SEARCH'), '', ' class="medium"')->attachField($fld1);
/* * ------* */
/** Comment form * */
$frm = getMBSFormByIdentifier('frmBlogComment');
$frm->addHiddenField('', 'comment_blog_id', $blog_id, 'comment_blog_id');
$fld = $frm->getField('comment_description');
$fld->extra = ' style="width: 100%;"';
$frm->setAction('?page=' . $page);
updateFormLang($frm);
$fld = $frm->getField('btn_submit');
$fld->value = t_lang('M_TXT_SUBMIT');
/* * ------* */
if (is_numeric($_GET['edit'])) {
    if (checkAdminAddEditDeletePermission(1, '', 'edit')) {
        $record = new TableRecord('tbl_blog_comments');
        if (!$record->loadFromDb('comment_id=' . $_GET['edit'], true)) {
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
if (is_numeric($_GET['delete'])) {
    if (checkAdminAddEditDeletePermission(1, '', 'delete')) {
        if (!$db->query('delete from tbl_blog_comments where comment_id=' . $_GET['delete'])) {
            $msg->addError($db->getError());
        } else {
            $msg->addMsg(t_lang('M_TXT_COMMENT_DELETED'));
            redirectUser('?page=' . $page . '&id=' . $blog_id);
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
        $record = new TableRecord('tbl_blog_comments');
        if ($post['comment_id'] == '')
            $record->setFldValue('comment_admin_id', $_SESSION['admin_logged']['admin_id']);
        if ($post['comment_id'] == '')
            $record->setFldValue('comment_user_id', 0);
        if ($post['comment_id'] == '')
            $record->setFldValue('comment_posted_on', date("Y-m-d H:i"));
        if ($post['comment_id'] == '' && $_SESSION['admin_logged']['admin_id'] == 1)
            $record->setFldValue('comment_approved_by_admin', 1);
        $arr_lang_independent_flds = array('comment_id', 'comment_blog_id', 'comment_posted_on', 'btn_submit');
        assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
        if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
            if ($post['comment_id'] > 0)
                $success = $record->update('comment_id' . '=' . $post['comment_id']);
        }
        if ((checkAdminAddEditDeletePermission(1, '', 'add'))) {
            if ($post['comment_id'] == '')
                $success = $record->addNew();
        }
        if ($success) {
            $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
            redirectUser('?id=' . $post['comment_blog_id']);
        } else {
            $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
            fillForm($frm, $post);
        }
    }
}
/** Get blog-comments list * */
$srch = new SearchBase('tbl_blog_comments', 'c');
$srch->joinTable('tbl_admin', 'LEFT OUTER JOIN', 'c.comment_admin_id=a.admin_id', 'a');
$srch->joinTable('tbl_users', 'LEFT OUTER JOIN', 'c.comment_user_id=u.user_id', 'u');
$srch->addCondition('comment_blog_id', '=', $blog_id);
if ($_REQUEST['comment_keywords'] != '') {
    $srch->addCondition('comment_description', 'LIKE', '%' . $_REQUEST['comment_keywords'] . '%');
}
$srch->addMultipleFields(['c.*']);
$srch->addFld('CASE c.comment_admin_id WHEN 0 THEN u.user_name ELSE a.admin_name END AS comment_posted_by');
$srch->addOrder('comment_posted_on', 'desc');
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$rs_listing = $srch->getResultSet();
/* * ------* */
$pagestring = '';
$pages = $srch->pages();
$pagestring .= createHiddenFormFromPost('frmPaging', '?', ['page', 'status', 'blogs'], ['page' => '', 'status' => $_REQUEST['status'], 'blogs' => $_REQUEST['blogs']]);
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
	' . getPageString('<li><a href="?id=' . $blog_id . '&page=xxpagexx" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$arr_listing_fields = [
    'comment_description' . $_SESSION['lang_fld_prefix'] => t_lang('M_TXT_COMMENT_DESCRIPTION'),
    'comment_posted_on' => t_lang('M_TXT_POSTED_ON'),
    'comment_posted_by' => t_lang('M_TXT_POSTED_BY'),
    'action' => t_lang('M_TXT_ACTION')
];
$arr_bread = array(
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'blogs.php' => t_lang('M_TXT_BLOGS'),
    '' => $blog_data['blog_title']
);
require_once './header.php';
?>
<script type = "text/javascript">
    var txtchange = "<?php echo unescape_attr(t_lang('M_TXT_CHANGE_CANNOT_BE_UNDONE')); ?>";
    var txtaprovecom = "<?php echo unescape_attr(t_lang('M_TXT_ARE_YOU_SURE_TO_APPROVE_COMMENT')); ?>";
</script>
<ul class="nav-left-ul">
    <li>
        <a <?php if ($_REQUEST['status'] == 'active') echo 'class="selected"'; ?> href="blogs.php?status=active"><?php echo t_lang('M_TXT_ACTIVE_BLOGS_LISTING') ?></a>
    </li>
    <li>
        <a <?php if ($_REQUEST['status'] == 'inactive') echo 'class="selected"'; ?> href="blogs.php?status=inactive"><?php echo t_lang('M_TXT_INACTIVE_BLOGS_LISTING') ?></a>
    </li>
</ul>
</div>
</td>					
<td class="right-portion">
    <?php echo getAdminBreadCrumb($arr_bread); ?>                
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_COMMENTS'); ?>
            <?php if (checkAdminAddEditDeletePermission(1, '', 'add')) { ?>
                <ul class="actions right">
                    <li class="droplink">
                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                        <div class="dropwrap">
                            <ul class="linksvertical">
                                <li> 
                                    <a href="?page=<?php echo $page; ?>&add=new&id=<?php echo $blog_id; ?>"><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_TXT_COMMENT'); ?></a>
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
            <div class="title-msg"> 
                <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?>
                <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a>
            </div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="greentext"><?php echo $msg->display(); ?> </div><br/><br/>
                <?php } if (isset($_SESSION['msgs'][0])) { ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?> 
    <?php
    if (is_numeric($_REQUEST['edit']) || $_REQUEST['add'] == 'new') {
        if ((checkAdminAddEditDeletePermission(1, '', 'add')) || (checkAdminAddEditDeletePermission(1, '', 'edit'))) {
            ?>
            <div class="box">
                <div class="title"><?php echo t_lang('M_TXT_COMMENTS'); ?> </div>
                <div class="content"><?php echo $frm->getFormHtml(); ?></div>
            </div>
            <?php
        } else {
            die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        }
    } else {
        ?>
        <div class="box searchform_filter">
            <div class="title"><?php echo t_lang('M_TXT_COMMENTS'); ?></div>
            <div class="content togglewrap" style="display:none;">		<?php echo $Src_frm->getFormHtml(); ?>	</div>
        </div>				 
        <table class="tbl_data" width="100%">
            <thead>
                <tr><?php
                    foreach ($arr_listing_fields as $val)
                        echo '<th>' . $val . '</th>';
                    ?></tr>
            </thead>
            <tbody>
                <?php
                while ($row = $db->fetch($rs_listing)) {
                    $row['comment_description'] = htmlentities($row['comment_description'], ENT_QUOTES, 'UTF-8');
                    // $row['comment_posted_by'] = htmlentities($row['comment_posted_by'], ENT_QUOTES, 'UTF-8');
                    echo '<tr' . (($row['blog_status'] == 0) ? ' class="inactive"' : '') . '>';
                    foreach ($arr_listing_fields as $key => $val) {
                        echo '<td ' . (($key == action) ? 'width="20%"' : '') . '>';
                        switch ($key) {
                            case 'comment_description':
                                echo strlen($row['comment_description']) > 80 ? substr($row['comment_description'], 0, 80) . '...' : $row['comment_description'];
                                break;
                            case 'comment_posted_on':
                                echo displayDateCustom($row['comment_posted_on']);
                                break;
                            case 'comment_posted_by':
                                echo $row['comment_posted_by'];
                                break;
                            case 'action':
                                echo '<ul class="actions">';
                                if (checkAdminAddEditDeletePermission(1, '', 'edit')) {
                                    echo '<li><a href="?edit=' . $row['comment_id'] . '&page=' . $page . '&id=' . $blog_id . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                                }
                                if (checkAdminAddEditDeletePermission(1, '', 'delete')) {
                                    echo '<li><a href="?delete=' . $row['comment_id'] . '&page=' . $page . '&id=' . $blog_id . '" title="' . t_lang('M_TXT_DELETE') . '" onclick="requestPopup(this,\'' . t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD') . '\',1);"><i class="ion-android-delete icon"></i></a></li>';
                                }
                                if (!$row['comment_approved_by_admin'] && $_SESSION['admin_logged']['admin_id'] == 1) {
                                    echo '<li><a href="javascript:void(0);" onClick="approveComment(this,' . $row['comment_id'] . ');" title="' . t_lang('M_TXT_APPROVE_COMMENT') . '"><i class="ion-checkmark-circled icon"></i></a></li>';
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
            </tbody>
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
