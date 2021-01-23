<?php
require_once './application-top.php';
checkAdminPermission(5);
$post = getPostedData();
loadModels(array('DealCategoryModel'));
$colPrefix = 'cat_';
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
/**
 * DEALCATEGORY CLASS SEARCH FORM 
 * */
$srchForm = DealCategory::getSearchForm();
/**
 * DEALCATEGORY CLASS DELETE 
 * */
if (is_numeric($_GET['delete'])) {
    if ((checkAdminAddEditDeletePermission(5, '', 'delete'))) {
        deleteCategory($_GET['delete']);
        redirectUser('?page=' . $page);
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
if ($_SESSION['lang_fld_prefix'] == '_lang1') {
    $get_cat_name = 'IF(CHAR_LENGTH(cat_name_lang1),cat_name_lang1,cat_name) as cat_name';
} else {
    $get_cat_name = 'cat_name';
}
if (isset($_GET['edit']) && ($_GET['edit'] > 0)) {
    $edit = $_GET['edit'];
    $rs = $db->query("select cat_code from tbl_deal_categories where cat_id=" . $edit);
    if (!$row = $db->fetch($rs)) {
        die('Invalid Request');
    }
    $code = $row['cat_code'];
    $rsc = $db->query("SELECT cat_id, " . $get_cat_name . ",cat_code,cat_parent_id FROM `tbl_deal_categories` WHERE `cat_code` NOT LIKE '$code%' ORDER BY cat_code asc, cat_display_order asc");
} else {
    $rsc = $db->query("SELECT  cat_id, " . $get_cat_name . ",cat_code,cat_parent_id FROM `tbl_deal_categories` where cat_active=1 ORDER BY cat_code asc, cat_display_order asc");
}
$parentArray = [];
$parentArray[0] = 'Select';
while ($arrs = $db->fetch($rsc)) {
    $checkCode = strlen($arrs['cat_code']) / 5;
    if ($checkCode == 1) {
        $arrow = "";
    }
    if ($checkCode > 1) {
        $arrow = "->";
    }
    $parentArray[$arrs['cat_id']] = str_repeat($arrow, $checkCode - 1) . " " . $arrs['cat_name'];
}
/**
 * DEALCATEGORY CLASS FORM 
 * */
$frm = DealCategory::getForm($parentArray);
updateFormLang($frm);
if (is_numeric($_GET['edit'])) {
    if ((checkAdminAddEditDeletePermission(5, '', 'edit'))) {
        $record = new TableRecord(DealCategory::DB_TBL);
        if (!$record->loadFromDb(DealCategory::DB_TBL_PRIMARY_KEY . '=' . $_GET['edit'], true)) {
            $msg->addError($record->getError());
        } else {
            $arr = $record->getFlds();
            $arr['btn_submit'] = t_lang('M_TXT_UPDATE');
            $frm->addHiddenField('', 'oldname', $arr['cat_name']);
            fillForm($frm, $arr);
            /*  $frm->fill($arr); */
            $msg->addMsg(t_lang('M_TXT_Edit_THE_VALUES_AND_UPDATE'));
        }
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
/**
 * DEALCATEGORY CLASS POSTED FORM
 * */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['btn_search']) && isset($_POST['btn_submit'])) {
    $post = getPostedData();
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error) {
            $msg->addError($error);
        }
    } else {
        $succeed = true;
        /* Images Validations if uploaded */
        if (is_uploaded_file($_FILES['cat_image']['tmp_name'])) {
            $ext = strtolower(strrchr($_FILES['cat_image']['name'], '.'));
            if ((!in_array($ext, array('.gif', '.jpg', '.jpeg', '.png'))) || ($_FILES['cat_image']['size'] > CONF_IMAGE_MAX_SIZE)) {
                $msg->addError(t_lang('M_TXT_CATEGORY') . ' ' . t_lang('M_TXT_IMAGE_NOT_SUPPORTED_OR_SIZE_EXCEEDED'));
                $succeed = false;
            }
        }
        if (true === $succeed) {
            if (is_uploaded_file($_FILES['cat_bg_image']['tmp_name'])) {
                $ext = strtolower(strrchr($_FILES['cat_bg_image']['name'], '.'));
                if ((!in_array($ext, array('.gif', '.jpg', '.jpeg', '.png'))) || ($_FILES['cat_bg_image']['size'] > CONF_IMAGE_MAX_SIZE)) {
                    $msg->addError(t_lang('M_TXT_CATEGORY_BACKGROUND') . ' ' . t_lang('M_TXT_IMAGE_NOT_SUPPORTED_OR_SIZE_EXCEEDED'));
                    $succeed = false;
                }
            }
        }
        if (true === $succeed) {
            $record = new TableRecord(DealCategory::DB_TBL);
            /* $record->assignValues($post); */
            $arr_lang_independent_flds = array('company_id', 'cat_display_order', 'cat_code', 'cat_parent_id', 'cat_is_featured', 'cat_layout', 'mode', 'btn_submit');
            $data['new_name'] = $post['cat_name'];
            assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
            if ((checkAdminAddEditDeletePermission(5, '', 'edit'))) {
                if ($post[DealCategory::DB_TBL_PRIMARY_KEY] > 0) {
                    $success = $record->update(DealCategory::DB_TBL_PRIMARY_KEY . '=' . $post[DealCategory::DB_TBL_PRIMARY_KEY]);
                }
            }
            if ((checkAdminAddEditDeletePermission(5, '', 'add'))) {
                if ($post[DealCategory::DB_TBL_PRIMARY_KEY] == '') {
                    $success = $record->addNew();
                }
            }
            if ($success) {
                $cat_id = ($post[DealCategory::DB_TBL_PRIMARY_KEY] > 0) ? $post[DealCategory::DB_TBL_PRIMARY_KEY] : $record->getId();
                if ($post[DealCategory::DB_TBL_PRIMARY_KEY] == '') {
                    $code = str_pad($cat_id, 5, '0', STR_PAD_LEFT);
                    $db->query("update tbl_deal_categories set cat_code='$code' where cat_id=$cat_id");
                }
                if (is_uploaded_file($_FILES['cat_image']['tmp_name'])) {
                    $flname = time() . '_' . $_FILES['cat_image']['name'];
                    if (!move_uploaded_file($_FILES['cat_image']['tmp_name'], CATEGORY_IMAGES_PATH . $flname)) {
                        $msg->addError(t_lang('M_TXT_FILE_COULD_NOT_SAVE'));
                    } else {
                        $getImg = $db->query("select * from tbl_deal_categories where cat_id='" . $cat_id . "'");
                        $imgRow = $db->fetch($getImg);
                        unlink(CATEGORY_IMAGES_PATH . $imgRow['cat_image' . $_SESSION['lang_fld_prefix']]);
                        $db->update_from_array('tbl_deal_categories', array('cat_image' . $_SESSION['lang_fld_prefix'] => $flname), 'cat_id=' . $cat_id);
                    }
                }
                if (is_uploaded_file($_FILES['cat_bg_image']['tmp_name'])) {
                    $flname = time() . '_' . $_FILES['cat_bg_image']['name'];
                    if (!move_uploaded_file($_FILES['cat_bg_image']['tmp_name'], BACKGROUND_IMAGES_PATH . $flname)) {
                        $msg->addError('File could not be saved.');
                    } else {
                        $getImg = $db->query("select * from tbl_deal_categories where cat_id='" . $cat_id . "'");
                        $imgRow = $db->fetch($getImg);
                        unlink(BACKGROUND_IMAGES_PATH . $imgRow['cat_bg_image' . $_SESSION['lang_fld_prefix']]);
                        $db->update_from_array('tbl_deal_categories', array('cat_bg_image' . $_SESSION['lang_fld_prefix'] => $flname), 'cat_id=' . $cat_id);
                    }
                }
                $rs = $db->query("select * from tbl_deal_categories where cat_id=" . $cat_id);
                if (!$row = $db->fetch($rs)) {
                    die('Invalid Request');
                }
                $old_code = $row['cat_code'];
                $new_code = getDealCategoryCode($cat_id, $row['cat_parent_id']);
                $qry = "update tbl_deal_categories set cat_code=REPLACE(cat_code, '" . $old_code . "', '" . $new_code . "')";
                if (!$db->query($qry)) {
                    $msg->addError($db->getError());
                }
                $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
                redirectUser();
            } else {
                $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
                fillForm($frm, $post);
            }
        } else {
            fillForm($frm, $post);
        }
    }
}
/**
 * DEALCATEGORY CLASS LISTING
 * */
$srch = DealCategory::getSearchObject();
$srch->joinTable('tbl_deal_categories', 'LEFT OUTER JOIN', 'p.cat_parent_id = m.cat_id', 'p');
$srch->addMultipleFields(['m.*', "CONCAT(CASE WHEN m.cat_parent_id = 0 THEN '' ELSE LPAD(p.cat_display_order, 7, '0') END, LPAD(m.cat_display_order, 7, '0')) AS display_order", 'COUNT(p.cat_id) AS child_count']);
if (isset($_REQUEST['cat']) && ($_REQUEST['cat']) > 0) {
    $catId = $_REQUEST['cat'];
    $srch->addCondition('m.cat_parent_id', '=', $catId);
} else {
    $srch->addCondition('m.cat_parent_id', '=', 0);
}
if ($post['mode'] == 'search') {
    if ($post['keyword'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('m.cat_name' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
    }
    $srchForm->fill($post);
}
$pagesize = 10;
$srch->addGroupBy('m.cat_id');
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$rs_listing = $srch->getResultSet();
$arr_listing = $db->fetch_all($rs_listing);
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
require_once './header.php';
$arr_bread = [
    'index.php' => '<img alt="Home" src="images/home-icon.png">',
    'deals.php' => t_lang('M_TXT_DEALS') . '/' . t_lang('M_TXT_PRODUCTS'),
    'deal-categories.php' => t_lang('M_FRM_ROOT_DEAL_CATEGORIES'),
];
if (isset($_REQUEST['cat']) && ($_REQUEST['cat']) > 0) {
    $srch = new SearchBase('tbl_deal_categories', 'm');
    $srch->addCondition('cat_id', '=', $_REQUEST['cat']);
    $get_cat_name = 'IF(CHAR_LENGTH(cat_name_lang1),cat_name_lang1,cat_name) as cat_name';
    $srch->addMultipleFields($get_cat_name);
    $rs = $srch->getResultSet();
    $arr = $db->fetch($rs);
    $arr_bread['deal-categories.php?cat=' . $_REQUEST['cat'] . ''] = $arr['cat_name'];
}
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php
            if (isset($_REQUEST['cat']) && ($_REQUEST['cat']) > 0) {
                echo '<a onclick="goBack()" href="javascript:void(0);" id="catBack" title="Back"><i class="ion-ios-arrow-back"></i></a> ';
                echo $arr['cat_name'] . ' ' . t_lang('M_FRM_DEAL_CATEGORIES');
            } else {
                echo t_lang('M_FRM_DEAL_CATEGORIES');
            }
            ?>
            <?php if ((checkAdminAddEditDeletePermission(5, '', 'add')) || (checkAdminAddEditDeletePermission(5, '', 'edit'))) { ?> 
                <ul class="actions right">
                    <li class="droplink">
                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                        <div class="dropwrap">
                            <ul class="linksvertical">
                                <?php if ((checkAdminAddEditDeletePermission(5, '', 'add'))) { ?>
                                    <li><a href="?page=<?php echo $page; ?>&add=new"><?php echo t_lang('M_TXT_ADD_NEW_CATEGORY'); ?></a></li>
                                <?php } ?>
                                <?php if (checkAdminAddEditDeletePermission(5, '', 'edit')) { ?> 
                                    <li>  <a href="category-display-order.php" ><?php echo t_lang('M_TXT_MANAGE_DISPLAY_ORDER'); ?></a> </li>
                                <?php } ?>
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
        if ((checkAdminAddEditDeletePermission(3, '', 'add')) || (checkAdminAddEditDeletePermission(3, '', 'edit'))) {
            ?>
            <div class="box"><div class="title"> <?php echo t_lang('M_FRM_DEAL_CATEGORIES'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
            <?php
        } else {
            die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        }
    } else {
        ?>
        <div class="box searchform_filter"><div class="title"> <?php echo t_lang('M_FRM_DEAL_CATEGORIES'); ?> </div><div class="content togglewrap" style="display:none;">	<?php echo $srchForm->getFormHtml(); ?>			
            </div></div>
        <div class="box tablebox">				
            <?php
            $arr_flds = array(
                'listserial' => t_lang('M_TXT_SR_NO'),
                'cat_name' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_NAME'),
                'child_count' => t_lang('M_TXT_Subcategories'),
                'action' => t_lang('M_TXT_ACTION')
            );
            ?> 				
            <table class="tbl_data" width="100%" id="category_listing1">				
                <thead><tr><?php
                        foreach ($arr_flds as $key => $val) {
                            echo '<th>' . $val . '</th>';
                        }
                        ?>
                    </tr></thead>				
                <?php
                $sr_no = $page == 1 ? 0 : $pageSize * ($page - 1);
                foreach ($arr_listing as $sn => $row) {
                    $sr_no++;
                    echo '<tr>';
                    foreach ($arr_flds as $key => $val) {
                        switch ($key) {
                            case 'listserial':
                                echo '<td>' . $sr_no . '</td>';
                                break;
                            case 'cat_name':
                                echo '<td>' . $row['cat_name' . $_SESSION['lang_fld_prefix']] . '</td>';
                                break;
                            case 'child_count':
                                if ($row[$key] == 0) {
                                    echo '<td>' . $row[$key] . '</td>';
                                } else {
                                    echo '<td><a href="deal-categories.php?cat=' . $row[DealCategory::DB_TBL_PRIMARY_KEY] . '">' . $row[$key] . '</a></td>';
                                }
                                break;
                            case 'action':
                                echo '<td><ul class="actions">';
                                if ((checkAdminAddEditDeletePermission(5, '', 'edit'))) {
                                    echo '<li><a href="?edit=' . $row[DealCategory::DB_TBL_PRIMARY_KEY] . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                                }
                                if ((checkAdminAddEditDeletePermission(5, '', 'delete'))) {
                                    echo '<li><a href="javascript:void(0);" title="' . t_lang('M_TXT_DELETE') . '" onclick="deleteCategory(' . $row[DealCategory::DB_TBL_PRIMARY_KEY] . ');"><i class="ion-android-delete icon"></i></a></li>';
                                    #echo '<a href="?delete=' . $row[DealCategory::DB_TBL_PRIMARY_KEY] . '" title="Delete" onclick="requestPopup(this,\'Are you sure to delete?\',1);" class="btn delete">Delete</a> ';
                                }
                                echo '</ul></td>';
                                break;
                            default:
                                echo $row[$key];
                                break;
                        }
                    }
                    echo '</tr>';
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
            <?php }
            ?>
        </div>
    <?php } ?>
</td>
<script type="text/javascript">
    $(document).ready(function () {
        //Table DND call
        $('#category_listing').tableDnD({
            onDrop: function (table, row) {
                var order = $.tableDnD.serialize('id');
                callAjax('cms-ajax.php', order + '&mode=REORDER_CATEGORY', function (t) {
                    $.facebox(t);
                });
            }
        });
    });
    txtcatdel = "<?php echo t_lang('M_TXT_CATEGORY_DELETION_NOT_ALLOWED'); ?>";
    txtsuredel = "<?php echo t_lang('M_TXT_ARE_YOU_SURE_TO_DELETE'); ?>";
    txtChildCatdel = "<?php echo t_lang('M_TXT_To_delete_this_category_you_must_first_remove_the_association'); ?>";
</script>	
<?php
require_once './footer.php';
