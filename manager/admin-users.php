<?php
require_once './application-top.php';
checkAdminPermission(9);
$page = (is_numeric($_GET['page']) ? $_GET['page'] : 1);
$pagesize = 50;
$mainTableName = 'tbl_admin';
$primaryKey = 'admin_id';
$colPrefix = 'admin_';
/**
 * CODE DELETE ADMIN SUB USER	
 * */
if (is_numeric($_GET['delete']) && $_GET['delete'] > 1 && $_GET['delete'] != $_SESSION['admin_logged']['admin_id']) {
    if ((checkAdminAddEditDeletePermission(9, '', 'delete'))) {
        deleteAdminUser($_GET['delete']);
        redirectUser('?page=' . $page);
    } else {
        die(t_lang("M_TXT_UNAUTHORIZED_ACCESS"));
    }
}
/**
 * SUB ADMIN USERS FORM 
 * */
$frm = new Form('frmAdmin');
$frm->setJsErrorDisplay('afterfield');
$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="tbl_form"');
$frm->addHiddenField('', 'admin_id', '', 'admin_id');
$fld = $frm->addRequiredField(t_lang('M_TXT_USERNAME'), 'admin_username');
$fld->requirements()->setUsername();
$fld->requirements()->setCustomErrorMessage(t_lang('M_TXT_Username_must_start_with_a_letter_and_can_contain_only_alphanumeric_characters_(letters, _, ., digits)_and_must_be_four_characters_long.'));
$fld->setUnique($mainTableName, 'admin_username', $primaryKey, 'admin_id', 'admin_id');
$fld = $frm->addPasswordField(t_lang('M_FRM_PASSWORD'), 'admin_password');
$fld = $fld->requirements()->setRequired();
$frm->addRequiredField(t_lang('M_FRM_NAME'), 'admin_name');
$fld1 = $frm->addEmailField(t_lang('M_FRM_EMAIL_ADDRESS'), 'admin_email');
$fld1->requirements()->setRequired();
$fld1->setUnique($mainTableName, 'admin_email', $primaryKey, 'admin_id', 'admin_id');
$frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SUBMIT'), '', 'class="inputbuttons"');
/**
 * SUB ADMIN USERS PERMISSION PAGE
 * */
if (is_numeric($_GET['permission']) && $_GET['permission'] > 1 && $_GET['permission'] != $_SESSION['admin_logged']['admin_id']) {
    if ($_SESSION['admin_logged']['admin_id'] != 1) {
        $srch = new SearchBase('tbl_admin_permissions');
        $srch->addCondition('ap_admin_id', '=', $_SESSION['admin_logged']['admin_id']);
        $rs = $srch->getResultSet();
        $arr_my_permissions = [];
        while ($row = $db->fetch($rs))
            $arr_my_permissions[$row['ap_permission_id']] = $row;
    }
    /* FETCH THE PERMISSION FIELDS */
    $rs_permissions = $db->query('select permission_id , permission_name' . $_SESSION['lang_fld_prefix'] . ' from tbl_permissions order by permission_order');
    $arr_permissions = $db->fetch_all_assoc($rs_permissions);
    foreach ($arr_permissions as $key => $val) {
        if ($_SESSION['admin_logged']['admin_id'] != 1) {
            $enabled = is_array($arr_my_permissions[$key]);
        } else {
            $enabled = true;
        }
        $fld = $frm->addCheckbox($val, 'ap_permission_view[' . $key . ']', 1, '', (($enabled) ? '' : ' disabled="disabled"'));
        $fld = $frm->getField('ap_permission_view[' . $key . ']');
        $fld->html_before_field = '<strong>&nbsp;&nbsp;&nbsp;&nbsp;' . t_lang('M_TXT_VIEW') . ':&nbsp;&nbsp;&nbsp;&nbsp;</strong>';
        if ($_SESSION['admin_logged']['admin_id'] != 1) {
            $enabled = ($arr_my_permissions[$key]['ap_permission_add'] == 1);
        }
        $fld1 = $frm->addCheckbox($val, 'ap_permission_add[' . $key . ']', 1, '', (($enabled) ? '' : ' disabled="disabled"'));
        $fld1 = $frm->getField('ap_permission_add[' . $key . ']');
        $fld1->html_before_field = '<strong>&nbsp;&nbsp;&nbsp;&nbsp;' . t_lang('M_TXT_ADD') . ':&nbsp;&nbsp;&nbsp;&nbsp;</strong>';
        $fld->attachField($fld1);
        if ($_SESSION['admin_logged']['admin_id'] != 1) {
            $enabled = ($arr_my_permissions[$key]['ap_permission_edit'] == 1);
        }
        $fld1 = $frm->addCheckbox($val, 'ap_permission_edit[' . $key . ']', 1, '', (($enabled) ? '' : ' disabled="disabled"'));
        $fld1 = $frm->getField('ap_permission_edit[' . $key . ']');
        $fld1->html_before_field = '<strong>&nbsp;&nbsp;&nbsp;&nbsp;' . ucfirst(t_lang('M_TXT_EDIT')) . ':&nbsp;&nbsp;&nbsp;&nbsp;</strong>';
        $fld->attachField($fld1);
        if ($_SESSION['admin_logged']['admin_id'] != 1) {
            $enabled = ($arr_my_permissions[$key]['ap_permission_delete'] == 1);
        }
        $fld1 = $frm->addCheckbox($val, 'ap_permission_delete[' . $key . ']', 1, '', (($enabled) ? '' : ' disabled="disabled"'));
        $fld1 = $frm->getField('ap_permission_delete[' . $key . ']');
        $fld1->html_before_field = '<strong>&nbsp;&nbsp;&nbsp;&nbsp;' . ucfirst(strtolower(t_lang('M_TXT_DELETE'))) . ':&nbsp;&nbsp;&nbsp;&nbsp;</strong>';
        $fld->attachField($fld1);
    }
    $fld = $frm->addHtml('<span style="color:red;">' . t_lang('M_TXT_ADMIN_NOTE') . ' </span>', '', '');
    $fld->merge_caption = 2;
    $frm->addSubmitButton('', 'btn_submitPermission', t_lang('M_TXT_SUBMIT'), '', 'class="inputbuttons"');
    $fld = $frm->getField('admin_password');
    $fld->requirements()->setRequired(false);
    $frm->removeField($fld);
    $fld = $frm->getField('admin_username');
    $frm->removeField($fld);
    $fld = $frm->getField('admin_name');
    $frm->removeField($fld);
    $fld = $frm->getField('admin_email');
    $frm->removeField($fld);
    $fld = $frm->getField('btn_submit');
    $frm->removeField($fld);
    $frm->addHiddenField('', 'skip_password', 1);
    if ((checkAdminAddEditDeletePermission(9, '', 'edit'))) {
        editAdminUser($_GET['permission']);
        /* function is placed in the site-function.php file */
    } else {
        die(t_lang("M_TXT_UNAUTHORIZED_ACCESS"));
    }
}
/**
 * SUB ADMIN USERS EDIT PAGE
 * */
if (is_numeric($_GET['edit']) && $_GET['edit'] > 1 && $_GET['edit'] != $_SESSION['admin_logged']['admin_id']) {
    //remove password field in case of editing the form 
    $fld = $frm->getField('admin_password');
    $fld->requirements()->setRequired(false);
    $frm->removeField($fld);
    $frm->addHiddenField('', 'skip_password', 1);
    if ((checkAdminAddEditDeletePermission(9, '', 'edit'))) {
        editAdminUser($_GET['edit']);
    } else {
        die(t_lang("M_TXT_UNAUTHORIZED_ACCESS"));
    }
}
/**
 * SUB ADMIN USERS ADD/EDIT SUBMITION FORM
 * */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
    $post = getPostedData();
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error)
            $msg->addError($error);
    } else {
        addAdminUser($post);
        redirectUser('?page=' . $page);
        /* function is placed in the site-function.php file */
    }
}
/**
 * SUB ADMIN USERS ADD/EDIT PERMISSIONS SUBMITION (MANAUALLY OR FROM DROPDOWN) FORM
 * */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_POST['btn_applyPermission']) || isset($_POST['btn_submitPermission']) )) {
    $post = getPostedData();
    if (isset($_POST['btn_applyPermission'])) {
        $permsnType = $post['permissionForAll'];
        $keys = array_keys($arr_permissions);
        $modules = array_fill_keys($keys, '1');
        if ($permsnType == 1) {
            $post['ap_permission_view'] = $modules;
        } else if ($permsnType == 2) {
            $post['ap_permission_view'] = $modules;
            $post['ap_permission_add'] = $modules;
            $post['ap_permission_edit'] = $modules;
            $post['ap_permission_delete'] = $modules;
        }
        $post['admin_id'] = $_GET['permission'];
    }
    addAdminUserPermission($post);
    redirectUser();
}
/**
 * SUB ADMIN USERS PAGINATION
 * */
$srch = new SearchBase($mainTableName);
$srch->addCondition('admin_id', '>', 1);
$srch->addCondition('admin_id', '!=', $_SESSION['admin_logged']['admin_id']);
$srch->addOrder('admin_name');
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$rs_listing = $srch->getResultSet();
if ((is_numeric($_GET['edit']) && $_GET['edit'] > 1 && $_GET['edit'] != $_SESSION['admin_logged']['admin_id'] ) || (is_numeric($_GET['permission']) && $_GET['permission'] > 1 && $_GET['permission'] != $_SESSION['admin_logged']['admin_id'] )) {
    $id = ($_GET['edit']) ? $_GET['edit'] : $_GET['permission'];
    $srch->addCondition('admin_id', '=', $id);
    $rst = $srch->getResultSet();
    $user = $db->fetch($rst);
}
$pagestring = '';
$pages = $srch->pages();
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div><div class="tblheading" style="margin:-4px 0px;padding:5px 0 6px 6px;"> &nbsp;</div>';
$arr_listing_fields = [
    'listserial' => t_lang('M_TXT_SR_NO'),
    'admin_name' => t_lang('M_FRM_NAME'),
    'admin_username' => t_lang('M_TXT_USERNAME'),
    'admin_email' => t_lang('M_FRM_EMAIL'),
    'action' => t_lang('M_TXT_ACTION')
];
require_once './header.php';
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'admin-users.php' => t_lang('M_TXT_ADMIN_USERS')
];
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_ADMIN_USERS'); ?> 
            <?php if (checkAdminAddEditDeletePermission(9, '', 'add')) { ?> 
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
                    <div class="greentext"> <?php echo $msg->getHtml(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?> 
    <?php
    if (is_numeric($_REQUEST['edit']) || is_numeric($_REQUEST['permission']) || $_REQUEST['add'] == 'new') {
        if ((checkAdminAddEditDeletePermission(9, '', 'add')) || (checkAdminAddEditDeletePermission(9, '', 'edit'))) {
            ?>
            <div class="box"><div class="title"><?php
                    if (isset($user['admin_name'])) {
                        echo $user['admin_name'] . ' ' . t_lang('M_TXT_ADMIN_USER');
                    } else {
                        echo t_lang('M_TXT_ADMIN_USERS');
                    }
                    ?> </div>
                <div class="content">
                    <?php if (is_numeric($_REQUEST['permission'])) { ?>	
                        <div class="box searchform_filter" style="background:#efefef;">
                            <div class="content togglewrap" style="overflow: hidden; display: block;">
                                <div id="validationsummary_Src_frm"></div>
                                <form method="post" action="" name="Src_frm" id="Src_frm">
                                    <table border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%">
                                        <tbody>
                                            <tr>
                                                <td class="first-child">
                                                    <?php echo t_lang('M_TXT_Select_Permission_For_All_Modules'); ?><br/>
                                                    <select class="permissionForAll" data-field-caption="Select Permission For All Modules" data-fatreq="{&quot;required&quot;:true}" name="permissionForAll">
                                                        <option value=""><?php echo t_lang('M_TXT_Select'); ?></option>
                                                        <option value="0"><?php echo t_lang('M_TXT_None'); ?></option>
                                                        <option value="1"><?php echo t_lang('M_TXT_Read_Only'); ?></option>
                                                        <option value="2"><?php echo t_lang('M_TXT_Read_And_Write'); ?></option>
                                                    </select>
                                                </td>
                                                <td><br/><input type="submit" name="btn_applyPermission" class="inputbuttons" title="" value="<?php echo t_lang('M_TXT_Apply_to_All'); ?>"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </form>
                            </div>
                        </div>					
                    <?php } ?>
                    <?php echo $frm->getFormHtml(); ?>
                </div></div>
            <?php
        } else {
            die(t_lang("M_TXT_UNAUTHORIZED_ACCESS"));
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
                echo '<tr' . (($row[$colPrefix . 'active'] == '0') ? ' class="inactive"' : '') . '>';
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td ' . (($key == action) ? 'width="20%"' : '') . '>';
                    switch ($key) {
                        case 'listserial':
                            echo $listserial;
                            break;
                        case 'action':
                            echo '<ul class="actions">';
                            echo '<li><a href="?edit=' . $row[$primaryKey] . '&page=' . $page . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                            echo '<li><a href="?permission=' . $row[$primaryKey] . '&page=' . $page . '" title="' . t_lang('M_TXT_permission') . '"><i class="ion-ios-eye icon"></i></a></li>';
                            echo '<li><a href="?delete=' . $row[$primaryKey] . '&page=' . $page . '" title="' . t_lang('M_TXT_DELETE') . '" onclick="requestPopup(this,\'' . t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD') . '\',1);"><i class="ion-android-delete icon"></i></a></li>';
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
                <aside class="grid_1"><?php echo $pagestring; ?></aside>  
                <aside class="grid_2"><span class="info"><?php echo $pageStringContent; ?></span></aside>
            </div>
            <?php
        }
    }
    ?>
    <strong><?php echo t_lang('M_TXT_SUPER_ADMIN_NOTE'); ?></strong>
</td>
<?php require_once './footer.php'; ?>
