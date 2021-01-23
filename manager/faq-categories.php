<?php
require_once './application-top.php';
checkAdminPermission(1);
require_once './header.php';
if ((isset($_GET['parent_id']) && ($_GET['parent_id'] != ''))) {
    $parent_id = $_GET['parent_id'];
    $parent_code = $_GET['parent_id_code'];
}
if (isset($_GET['edit']) && ($_GET['edit'] != '')) {
    $edit = $_GET['edit'];
}
if ($_GET['delete'] > 0) {
    if ((checkAdminAddEditDeletePermission(1, '', 'delete'))) {
        $parentCheck = $db->query("SELECT SQL_CALC_FOUND_ROWS   COUNT(fc_tmp.category_id) as children FROM `tbl_cms_faq_categories` fc   LEFT OUTER JOIN `tbl_cms_faq_categories` fc_tmp on fc_tmp.category_code like CONCAT(fc.category_code, '%') AND fc.category_code != fc_tmp.category_code and fc_tmp.category_deleted=0 where fc.category_id=" . $_GET['delete']);
        $result1 = $db->fetch($parentCheck);
        if (!($result1['children'] > 0)) {
            $db->query("update tbl_cms_faq_categories set category_deleted=1 where category_id=" . $_GET['delete']);
            $msg->addMsg("Category deleted.");
        } else {
            $msg->addMsg("Cannot delete this Category.");
        }
    } else {
        die('Unauthorized Access.');
    }
}
if ($_GET['delete_parent'] > 0) {
    if ((checkAdminAddEditDeletePermission(1, '', 'delete'))) {
        $db->query("update tbl_cms_faq_categories set category_deleted=1 where category_id=" . $_GET['delete_parent']);
        $msg->addMsg("Category  deleted successfully.");
    } else {
        die('Unauthorized Access.');
    }
}
$srch = new SearchBase('tbl_cms_faq_categories', 'fc');
$srch->addCondition('fc.category_deleted', '=', '0');
$srch->addOrder('fc.category_code');
$srch->addOrder('fc.category_display_order');
$srch->addFld('fc.*');
$srch->joinTable('tbl_cms_faq_categories', 'LEFT OUTER JOIN', "fc_tmp.category_code like CONCAT(fc.category_code, '%') AND fc.category_code != fc_tmp.category_code and fc_tmp.category_deleted=0", 'fc_tmp');
$srch->addGroupBy('fc.category_id');
$srch->addFld('COUNT(fc_tmp.category_id) as children');
$rs = $srch->getResultSet();
//echo $srch->getQuery();
?>
<?php
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0);' => t_lang('M_TXT_CMS'),
    '' => t_lang('M_TXT_FAQ')
];
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_LIST_OF_FAQ_CATEGORIES'); ?> 
            <ul class="actions right">
                <li class="droplink">
                    <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                    <div class="dropwrap">
                        <ul class="linksvertical">
                            <li> <a href="faq-display-order.php"><?php echo t_lang('M_TXT_MANAGE_DISPLAY_ORDER'); ?></a></li>
                            <?php if (checkAdminAddEditDeletePermission(1, '', 'add')) { ?> 
                                <li><a href="add-faq-category.php?mode=Add"><?php echo t_lang('M_TXT_ADD_NEW'); ?></a></li>
                            <?php } ?>
                        </ul>
                    </div>
                </li>
            </ul>
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
                <?php } if (isset($_SESSION['msgs'][0])) { ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?> 
    <table class="tbl_data" id="cms-listing" width="100%">
        <thead>
            <tr>                      
                <th width="55%"><?php echo t_lang('M_FRM_TITLE'); ?></th>
                <th width="10%"><?php echo t_lang('M_FRM_STATUS'); ?></th>
                <th width="20%">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $count = 0;
            while ($row = $db->fetch($rs)) {
                $count++;
                ?>
                <tr >
                    <td width="55%">
                        <?php
                        $level = strlen($row['category_code']) / 5 - 1;
                        for ($i = 0; $i < $level; $i++)
                            echo '&mdash;&raquo;&nbsp;';//&mdash;&raquo;&nbsp;
                        echo '<strong>' . $arr_lang_name[0] . '</strong>' . ' ' . $row['category_name'] . '<br/>';
                        echo '<strong>' . $arr_lang_name[1] . '</strong>' . ' ' . $row['category_name_lang1'];
                        ?>
                    </td>
                    <td width="10%">
                        <?php
                        echo $row['category_active'] == '1' ? '<span class="label label-primary">' . t_lang('M_TXT_ACTIVE') . '</span>' : '<span class="label label-danger">' . t_lang('M_TXT_INACTIVE') . '</span>';
                        ?>
                    </td>
                    <td>
                        <ul class="actions">
                            <?php if ((checkAdminAddEditDeletePermission(1, '', 'add'))) { ?>
                                <?php if ($row['category_id'] != 1 && 0) { ?>
                                    <li><a href="add-faq-category.php?parent_id=<?php echo $row['category_id']; ?>&parent_code=<?php echo $row['category_code']; ?>" title="<?php echo t_lang('M_TXT_ADD_CHILD_CATEGORY') ?>"><i class="ion-android-add-circle icon"></i></a></li>
                                <?php } ?>
                            <?php } ?>
                            <?php if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) { ?>
                                <li><a href="add-faq-category.php?edit=<?php echo $row['category_id'] ?>" title="<?php echo t_lang('M_TXT_EDIT') ?>"><i class="ion-edit icon"></i></a></li>
                                <li><a href="cms-faq-listing.php?faq_category_id=<?php echo $row['category_id'] ?>" title="<?php echo t_lang('M_TXT_FAQ_LISTING'); ?>"><i class="ion-ios-list icon"></i></a></li></a>
                            <?php } ?>
                            <?php if (!($row['children'] > 0)) { ?>
                                <?php if ((checkAdminAddEditDeletePermission(1, '', 'delete'))) { ?>
                                    <?php if ($row['category_id'] != 1) { ?>
                                        <li><a href="faq-categories.php?delete=<?php echo $row['category_id'] ?>" onClick="requestPopup(this, '<?php echo t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD'); ?>', 1);" title="<?php echo t_lang('M_TXT_DELETE') ?>"><i class="ion-android-delete icon"></i></a></li>
                                            <?php } ?>
                                            <?php
                                        }
                                    }
                                    ?>
                        </ul>
                    </td>
                </tr>
                <?php
            } if ($db->total_records($rs) == 0) {
                echo '<tr><td colspan="4">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
            }
            ?>
        </tbody>
    </table>
</td>
<?php require_once './footer.php'; ?>
