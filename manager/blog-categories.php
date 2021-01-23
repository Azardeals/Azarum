<?php
require_once './application-top.php';
checkAdminPermission(1);
$mainTableName = 'tbl_blog_categories';
$primaryKey = 'cat_id';
$colPrefix = 'cat_';
/** Category form * */
$frm = new Form('frmBlogCategory');
$frm->setExtra('class="siteForm"');
$frm->setTableProperties('border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$frm->setJsErrorDisplay('afterfield');
$frm->setAction('?');
$frm->addHiddenField('', 'cat_id', '', 'cat_id');
$fld = $frm->addRequiredField(t_lang('M_FRM_CATEGORY_NAME'), 'cat_name' . $_SESSION['lang_fld_prefix'], '', 'cat_name');
$catStatus = [1 => t_lang('M_TXT_ACTIVE'), 0 => t_lang('M_TXT_INACTIVE')];
$frm->addSelectBox(t_lang('M_FRM_STATUS'), 'cat_status', $catStatus, '', '', 'Select', 'cat_status');
$frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SUBMIT'), 'btn_submit');
/* * ***** */
/** Get categories list * */
$srch = new SearchBase('tbl_blog_categories', 'c');
$srch->addMultipleFields(['c.*']);
$srch->addOrder('cat_id', 'desc');
$rs_listing = $srch->getResultSet();
/* * ------* */
if (is_numeric($_GET['edit'])) {
    if (checkAdminAddEditDeletePermission(1, '', 'edit')) {
        $record = new TableRecord('tbl_blog_categories');
        if (!$record->loadFromDb('cat_id=' . $_GET['edit'], true)) {
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
        if (!$db->query('delete from tbl_blog_categories where cat_id=' . $_GET['delete'])) {
            $msg->addError($db->getError());
        } else {
            $msg->addMsg(t_lang('M_TXT_CATEGORY_DELETED'));
            redirectUser('?');
        }
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
    $post = getPostedData();
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error)
            $msg->addError($error);
    } else {
        $record = new TableRecord('tbl_blog_categories');
        $record->assignValues($post);
        if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
            if ($post['cat_id'] > 0)
                $success = $record->update('cat_id' . '=' . $post['cat_id']);
        }
        if ((checkAdminAddEditDeletePermission(1, '', 'add'))) {
            if ($post['cat_id'] == '')
                $success = $record->addNew();
        }
        if ($success) {
            $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
            redirectUser();
        } else {
            $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
            fillForm($frm, $post);
        }
    }
}
$arr_listing_fields = [
    'listserial' => t_lang('M_TXT_SR_NO'),
    'cat_name' => t_lang('M_FRM_NAME'),
    'cat_status' => t_lang('M_FRM_STATUS'),
    'action' => t_lang('M_TXT_ACTION')
];
$arr_bread = [
    'index.php' => '<img alt="Home" src="images/home-icon.png">',
    'javascript:void(0)' => t_lang('M_TXT_CMS'),
    '' => t_lang('M_FRM_BLOG_CATEGORIES')
];
require_once './header.php';
?>
</div></td>
<td class="right-portion">
    <?php echo getAdminBreadCrumb($arr_bread); ?>                
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_FRM_BLOG_CATEGORIES'); ?> 
            <?php if (checkAdminAddEditDeletePermission(1, '', 'add')) { ?>
                <ul class="actions right">
                    <li class="droplink">
                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                        <div class="dropwrap">
                            <ul class="linksvertical">
                                <li>
                                    <a href="?page=<?php echo $page; ?>&add=new"><?php echo t_lang('M_TXT_ADD_NEW'); ?></a>
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
                <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide();
                            return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a>
            </div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="greentext"><?php echo $msg->display(); ?> </div><br/><br/>
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
            <div class="box">
                <div class="title"><?php echo t_lang('M_FRM_BLOG_CATEGORIES'); ?> </div>
                <div class="content"><?php echo $frm->getFormHtml(); ?></div>
            </div>
            <?php
        } else {
            die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        }
    } else {
        ?>
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
            for ($listserial = ($page - 1) * $pagesize + 1; $row = $db->fetch($rs_listing); $listserial++) {
                if ($listserial % 2 == 0)
                    $even = 'even';
                else
                    $even = '';
                echo '<tr class=" ' . $even . ' " ' . (($row[$colPrefix . 'active'] == '0') ? ' class="inactive"' : '') . ' id = ' . $row['cat_id'] . '>';
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td>';
                    switch ($key) {
                        case 'listserial':
                            echo $listserial;
                            break;
                        case 'cat_name':
                            echo $row['cat_name'];
                            break;
                        case 'cat_status':
                            echo ($row['cat_status']) ? t_lang('M_TXT_ACTIVE') : t_lang('M_TXT_INACTIVE');
                            break;
                        case 'action':
                            echo '<ul class="actions">';
                            if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
                                echo '<li><a href="?edit=' . $row[$primaryKey] . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                            }
                            if ((checkAdminAddEditDeletePermission(1, '', 'delete'))) {
                                echo '<li><a href="?delete=' . $row['cat_id'] . '" title="' . t_lang('M_TXT_DELETE') . '" onclick="requestPopup(this,\'' . t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD') . '\',1);"><i class="ion-android-delete icon"></i></a></li>';
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
</td>
<?php require_once './footer.php'; ?>