<?php
require_once './application-top.php';
checkAdminPermission(1);
$primaryKey = 'blog_id';
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$pagesize = 30;
/** Search form * */
$Src_frm = new Form('Src_frm', 'Src_frm');
$Src_frm->setTableProperties('border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$Src_frm->setFieldsPerRow(2);
$Src_frm->captionInSameCell(true);
$Src_frm->addTextBox(t_lang('M_TXT_BLOG_TITLE'), 'blog', $_REQUEST['blog'], '', '');
$Src_frm->addHiddenField('', 'mode', 'search');
$Src_frm->addHiddenField('', 'status', $_REQUEST['status']);
$fld1 = $Src_frm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="medium" onclick=location.href="blogs.php"');
$fld = $Src_frm->addSubmitButton('', 'search', t_lang('M_TXT_SEARCH'), '', ' class="medium"')->attachField($fld1);
/* * ------* */
/** Blog form * */
$frm = getMBSFormByIdentifier('frmBlog');
$frm->setAction('?page=' . $page);
updateFormLang($frm);
$fld = $frm->getField('blog_description');
$fld->fldType = 'htmleditor';
$fld->assignEditor();
$fld->requirements()->setRequired();
$fld->requirements()->setCustomErrorMessage('Description is manadatory.');
$fld->html_before_field = '<div class="frm-editor">';
$fld->html_after_field = '</div>';
$fld = $frm->getField('btn_submit');
$fld->value = t_lang('M_TXT_SUBMIT');
/* * ------* */
$src = DEAL_IMAGES_URL . 'no-image.jpg';
if (is_numeric($_GET['edit'])) {
    if (checkAdminAddEditDeletePermission(1, '', 'edit')) {
        $record = new TableRecord('tbl_blogs');
        $src = CONF_WEBROOT_URL . 'blog-image.php?id=' . $_GET['edit'] . '&' . time();
        if (!$record->loadFromDb('blog_id=' . $_GET['edit'], true)) {
            $msg->addError($record->getError());
        } else {
            $arr = $record->getFlds();
            $arr['btn_submit'] = t_lang('M_TXT_UPDATE');
            $fld = $frm->getField('blog_image');
            $fld->extra = 'onchange="readURL(this);"';
            $fld->html_after_field = '<div class="blogImage_show"><img class="deal_image" src="' . $src . '" ></div>';
            fillForm($frm, $arr);
            $msg->addMsg(t_lang('M_TXT_Edit_THE_VALUES_AND_UPDATE'));
        }
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
if (is_numeric($_GET['delete'])) {
    if (checkAdminAddEditDeletePermission(1, '', 'delete')) {
        if (!$db->query('delete from tbl_blogs where blog_id=' . $_GET['delete'])) {
            $msg->addError($db->getError());
        } else {
            $msg->addMsg(t_lang('M_TXT_BLOG_DELETED'));
            redirectUser('?page=' . $page);
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
        if (is_uploaded_file($_FILES['blog_image']['tmp_name'])) {
            $ext = strtolower(strrchr($_FILES['blog_image']['name'], '.'));
            if ((!in_array($ext, ['.gif', '.jpg', '.jpeg', '.png'])) || ($_FILES['blog_image']['size'] > CONF_IMAGE_MAX_SIZE)) {
                $msg->addError(t_lang('M_TXT_BLOG') . ' ' . t_lang('M_TXT_IMAGE_NOT_SUPPORTED_OR_SIZE_EXCEEDED'));
                fillForm($frm, $post);
                $succeed = false;
            }
        }
        if (true === $succeed) {
            $record = new TableRecord('tbl_blogs');
            if ($post['blog_id'] == '') {
                $record->setFldValue('blog_admin_id', $_SESSION['admin_logged']['admin_id']);
            }
            if ($post['blog_id'] == '') {
                $record->setFldValue('blog_user_id', 0);
            }
            if ($post['blog_id'] == '') {
                $record->setFldValue('blog_added_on', date("Y-m-d H:i"));
            }
            $arr_lang_independent_flds = ['blog_id', 'blog_title', 'blog_description', 'blog_cat_id', 'blog_status', 'btn_submit', 'blog_approved_by_admin'];
            assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
            if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
                if ($post['blog_id'] > 0) {
                    $success = $record->update('blog_id' . '=' . $post['blog_id']);
                }
            }
            if ((checkAdminAddEditDeletePermission(1, '', 'add'))) {
                $record->setFldValue('blog_approved_by_admin', 1);
                if ($post['blog_id'] == '') {
                    $success = $record->addNew();
                }
            }
            if ($success) {
                $blog_id = ($post[$primaryKey] > 0) ? $post[$primaryKey] : $record->getId();
                if (is_uploaded_file($_FILES['blog_image']['tmp_name'])) {
                    $flname = time() . '_' . $_FILES['blog_image']['name'];
                    if (!move_uploaded_file($_FILES['blog_image']['tmp_name'], BLOG_IMAGES_PATH . $flname)) {
                        $msg->addError(t_lang('M_TXT_FILE_COULD_NOT_SAVE'));
                    } else {
                        $getImg = $db->query("select * from tbl_blogs where blog_id='" . $blog_id . "'");
                        $imgRow = $db->fetch($getImg);
                        unlink(BLOG_IMAGES_PATH . $imgRow['blog_image']);
                        $db->update_from_array('tbl_blogs', ['blog_image' => $flname], 'blog_id=' . $blog_id);
                    }
                }
                $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
                redirectUser();
            } else {
                $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
                fillForm($frm, $post);
            }
        }
    }
}
/** Get blogs list * */
$srch = new SearchBase('tbl_blogs', 'b');
$srch->joinTable('tbl_admin', 'LEFT OUTER JOIN', 'b.blog_admin_id=a.admin_id', 'a');
$srch->joinTable('tbl_users', 'LEFT OUTER JOIN', 'b.blog_user_id=u.user_id', 'u');
$srch->joinTable('tbl_blog_comments', 'LEFT OUTER JOIN', 'b.blog_id=c.comment_blog_id', 'c');
if ($_REQUEST['status'] == 'active') {
    $srch->addCondition('blog_status', '=', 1);
} else if ($_REQUEST['status'] == 'inactive') {
    $srch->addCondition('blog_status', '=', 0);
} else {
    $srch->addCondition('blog_status', '=', 1);
}
if ($_REQUEST['blog'] != '') {
    $srch->addCondition('blog_title' . $_SESSION['lang_fld_prefix'], 'LIKE', '%' . $_REQUEST['blog'] . '%');
}
$srch->addMultipleFields(['b.*']);
$srch->addFld('CASE b.blog_admin_id WHEN 0 THEN u.user_name ELSE a.admin_name END AS blog_posted_by');
$srch->addFld('COUNT(c.comment_id) AS total_comments');
$srch->addFld('SUM(CASE WHEN c.comment_approved_by_admin = 0 THEN 1 ELSE 0 END) AS unapproved_comments');
$srch->addGroupBy('blog_id');
$srch->addOrder('blog_added_on', 'desc');
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
	' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$arr_listing_fields = [
    'blog_title' . $_SESSION['lang_fld_prefix'] => t_lang('M_TXT_BLOG_TITLE'),
    'blog_posted_on' => t_lang('M_TXT_POSTED_ON'),
    'blog_posted_by' => t_lang('M_TXT_POSTED_BY'),
    'blog_image' => t_lang('M_TXT_IMAGE'),
    'blog_comments' => t_lang('M_TXT_TOTAL_COMMENTS') . ' (' . t_lang('M_TXT_UNAPPROVED') . ')',
    'action' => t_lang('M_TXT_ACTION')
];
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0);' => t_lang('M_TXT_CMS'),
    '' => t_lang('M_TXT_BLOGS')
];
require_once './header.php';
?>
<script type = "text/javascript">
    var txtchange = "<?php echo unescape_attr(t_lang('M_TXT_CHANGE_CANNOT_BE_UNDONE')); ?>";
    var txtaproveblog = "<?php echo unescape_attr(t_lang('M_TXT_ARE_YOU_SURE_TO_APPROVE_BLOG')); ?>";
</script>
<ul class="nav-left-ul">
    <li>
        <a <?php echo ($_REQUEST['status'] == 'active') ? 'class="selected"' : ''; ?> href="blogs.php?status=active"><?php echo t_lang('M_TXT_ACTIVE_BLOGS_LISTING') ?></a>
    </li>
    <li>
        <a <?php echo ($_REQUEST['status'] == 'inactive') ? 'class="selected"' : ''; ?> href="blogs.php?status=inactive"><?php echo t_lang('M_TXT_INACTIVE_BLOGS_LISTING') ?></a>
    </li>
</ul>
</div>
</td>					
<td class="right-portion">
    <?php echo getAdminBreadCrumb($arr_bread); ?>                
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_BLOG'); ?> 
            <?php if (checkAdminAddEditDeletePermission(1, '', 'add')) { ?>
                <ul class="actions right">
                    <li class="droplink">
                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                        <div class="dropwrap">
                            <ul class="linksvertical">
                                <li>
                                    <a href="?page=<?php echo $page; ?>&add=new"><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_TXT_BLOG'); ?></a>
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
                <div class="title"><?php echo t_lang('M_TXT_BLOGS'); ?> </div>
                <div class="content"><?php echo $frm->getFormHtml(); ?></div>
            </div>
            <?php
        } else {
            die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        }
    } else {
        ?>
        <div class="box searchform_filter">
            <div class="title"><?php echo t_lang('M_TXT_BLOGS'); ?></div>
            <div class="content togglewrap" style="display:none;">		<?php echo $Src_frm->getFormHtml(); ?></div>
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
            <tbody>
                <?php
                while ($row = $db->fetch($rs_listing)) {
                    $row['blog_title'] = htmlentities($row['blog_title'], ENT_QUOTES, 'UTF-8');
                    $row['blog_description'] = htmlentities($row['blog_description'], ENT_QUOTES, 'UTF-8');
                    //  $row['blog_posted_by']= htmlentities($row['blog_posted_by'], ENT_QUOTES, 'UTF-8');
                    echo '<tr' . (($row['blog_status'] == 0) ? ' class="inactive"' : '') . '>';
                    foreach ($arr_listing_fields as $key => $val) {
                        echo '<td ' . (($key == action) ? 'width="20%"' : '') . '>';
                        switch ($key) {
                            case 'blog_posted_on':
                                echo displayDateCustom($row['blog_added_on']);
                                break;
                            case 'blog_posted_by':
                                echo $row['blog_posted_by'];
                                break;
                            case 'blog_comments':
                                echo '<a href="blog_comments.php?id=' . $row['blog_id'] . '">' . $row['total_comments'] . '</a>';
                                echo $row['unapproved_comments'] > 0 ? ' (' . $row['unapproved_comments'] . ')' : '';
                                break;
                            case 'blog_title_lang1':
                                echo '<strong>' . $arr_lang_name[0] . '</strong>' . ' ' . htmlentities($row['blog_title'], ENT_QUOTES, 'UTF-8') . '<br/>';
                                echo '<strong>' . $arr_lang_name[1] . '</strong>' . ' ' . htmlentities($row['blog_title_lang1'], ENT_QUOTES, 'UTF-8');
                                break;
                            case 'blog_image':
                                if ($row['blog_image'] != "") {
                                    $src = CONF_WEBROOT_URL . 'blog-image.php?id=' . $row['blog_id'] . '&' . time();
                                } else {
                                    $src = DEAL_IMAGES_URL . "no-image.png";
                                }
                                echo '<img src="' . $src . '"  width="55">';
                                break;
                            case 'action':
                                echo '<ul class="actions">';
                                if (checkAdminAddEditDeletePermission(1, '', 'edit')) {
                                    echo '<li><a href="?edit=' . $row['blog_id'] . '&page=' . $page . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                                }
                                if (checkAdminAddEditDeletePermission(1, '', 'delete')) {
                                    echo '<li><a href="?delete=' . $row['blog_id'] . '&page=' . $page . '" title="' . t_lang('M_TXT_DELETE') . '" onclick="requestPopup(this,\'' . t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD') . '\',1);"><i class="ion-android-delete icon"></i></a></li>';
                                }
                                if (!$row['blog_approved_by_admin'] && $_SESSION['admin_logged']['admin_id'] == 1) {
                                    echo '<li><a href="javascript:void(0);" onClick="approveBlog(this,' . $row['blog_id'] . ');" title="' . t_lang('M_TXT_APPROVE_BLOG') . '"><i class="ion-checkmark-circled icon"></i></a></li>';
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
