<?php
require_once './application-top.php';
checkAdminPermission(1);
require_once './header.php';
if ((isset($_GET['nav_id']) && ($_GET['nav_id'] != '')) || (isset($_GET['parent_id']) && ($_GET['parent_id'] != ''))) {
    $nav_id = $_GET['nav_id'];
    $parent_id = $_GET['parent_id'];
    $parent_code = $_GET['parent_id_code'];
} else if (isset($_GET['edit']) && ($_GET['edit'] != '')) {
    $edit = $_GET['edit'];
} else {
    redirectUser('navigation-management.php');
}
if ($_GET['delete'] > 0) {
    if ((checkAdminAddEditDeletePermission(1, '', 'delete'))) {
        $parentCheck = $db->query("SELECT SQL_CALC_FOUND_ROWS  nl.*, COUNT(nl_tmp.nl_id) as children FROM `tbl_nav_links` nl INNER JOIN `tbl_navigations` nav on nav.nav_id=" . $nav_id . " AND nav.nav_active=1 LEFT OUTER JOIN `tbl_nav_links` nl_tmp on nl_tmp.nl_code like CONCAT(nl.nl_code, '%') AND nl.nl_code != nl_tmp.nl_code and nl_tmp.nl_deleted=0 where nl.nl_id=" . $_GET['delete']);
        $result1 = $db->fetch($parentCheck);
        if (!($result1['children'] > 0) && $result1['is_fixed'] == 0) {
            $db->query("update tbl_nav_links set nl_deleted=1 where is_fixed = 0 and  nl_id=" . $_GET['delete']);
            $msg->addMsg("Navigation Link deleted.");
        } else {
            $msg->addMsg("Cannot delete this link.");
        }
    } else {
        die('Unauthorized Access.');
    }
}
if ($_GET['delete_parent'] > 0) {
    if ((checkAdminAddEditDeletePermission(1, '', 'delete'))) {
        $db->query("update tbl_nav_links set nl_deleted=1 where is_fixed = 0 and  nl_id=" . $_GET['delete_parent']);
        $msg->addMsg("Navigation Link deleted.");
    } else {
        die('Unauthorized Access.');
    }
}
$srch = new SearchBase('tbl_nav_links', 'nl');
$srch->joinTable('tbl_navigations', 'INNER JOIN', 'nav.nav_id=nl.nl_nav_id AND nav.nav_active=1', 'nav');
$srch->addCondition('nl.nl_nav_id', '=', $nav_id);
$srch->addCondition('nl.nl_deleted', '=', '0');
if ($isMultilevel == 1) {
    $srch->addOrder('nl.nl_code');
}
$srch->addOrder('nl.nl_display_order');
$srch->addFld('nav.nav_name');
$srch->addFld('nl.*');
$srch->joinTable('tbl_nav_links', 'LEFT OUTER JOIN', "nl_tmp.nl_code like CONCAT(nl.nl_code, '%') AND nl.nl_code != nl_tmp.nl_code and nl_tmp.nl_deleted=0", 'nl_tmp');
$srch->addGroupBy('nl.nl_id');
$srch->addFld('COUNT(nl_tmp.nl_id) as children');
$rs = $srch->getResultSet();
$cms_page = $db->query("Select  * from tbl_navigations where nav_active=1 and nav_id=$nav_id");
$cms_result = $db->fetch($cms_page);
$isMultilevel = $cms_result['nav_ismultilevel'];
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0);' => t_lang('M_TXT_CMS'),
    'navigation-management.php' => t_lang('M_TXT_LIST_OF_NAVIGATIONS'),
    '' => $cms_result['nav_name']
];
if ($isMultilevel == 0) {
    ?>
    <script type="text/javascript">
        $(document).ready(function () {
            //Table DND call
            $('#nav-listing').tableDnD({
                onDrop: function (table, row) {
                    var order = $.tableDnD.serialize('id');
                    callAjax('cms-ajax.php', order + '&mode=REORDER_NAVIGATION', function (t) {
                        $.facebox(t);
                    });
                }
            });
        });
    </script>
<?php } ?>
</div></td> 
<div id="msgbox"></div>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_LIST_OF'); ?> <?php echo $cms_result['nav_name'] ?>
            <?php if (checkAdminAddEditDeletePermission(1, '', 'add')) { ?>
                <ul class="actions right">
                    <li class="droplink">
                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                        <div class="dropwrap">
                            <ul class="linksvertical">
                                <li>
                                    <a href="add-navigation.php?nav_id=<?php echo $nav_id; ?>"><?php echo t_lang('M_TXT_ADD_NEW'); ?></a>
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
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide();
                        return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
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
    <table class="tbl_data" id="nav-listing" width="100%">
        <thead>
            <tr>                      
                <th width="55%"><?php echo t_lang('M_TXT_CAPTION'); ?></th>
                <th width="10%"><?php echo t_lang('M_TXT_TYPE'); ?></th>
                <?php if ($isMultilevel == 1) { ?>
                    <th width="20%">Manage Display Order</th>			
                <?php } ?>
                <th width="15%">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = $db->fetch($rs)) {
                if ($isMultilevel == 0) {
                    echo '<tr id = ' . $row['nl_id'] . '>';
                } else {
                    echo '<tr>';
                }
                ?>
            <td width="55%">
                <?php
                $level = strlen($row['nl_code']) / 5 - 1;
                for ($i = 0; $i < $level; $i++)
                    echo '&mdash;&raquo;&nbsp;';
                /* echo $row['nl_caption'.$_SESSION['lang_fld_prefix']]; */
                echo '<strong>' . $arr_lang_name[0] . '</strong>' . ' ' . $row['nl_caption'] . '<br/>';
                echo '<strong>' . $arr_lang_name[1] . '</strong>' . ' ' . $row['nl_caption_lang1'];
                ?>
            </td>
            <td width="10%">
                <?php
                echo $arr_nav_type[$row['nl_type']];
                ?>
            </td>
            <?php
            if ($isMultilevel == 1) {
                if (($row['children'] > 0) || $count == 1) {
                    ?>
                    <td width="20%">
                        <?php if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) { ?>
                            <ul class="actions"><li><a href="navigation-display-order.php?nav_id=<?php echo $nav_id; ?>&id=<?php echo $row['nl_id'] ?>" title="Manage Child Display Order"><i class="ion-drag icon"></i></a></li></ul>
                        <?php } ?>
                    </td>
                    <?php
                } else {
                    echo '<td width="15%">&nbsp;</td>';
                }
            }
            ?>
            <td>
                <ul class="actions">
                    <?php
                    if ($isMultilevel == 1) {
                        if ((checkAdminAddEditDeletePermission(1, '', 'add'))) {
                            ?>
                            <li><a href="add-navigation.php?nav_id=<?php echo $nav_id; ?>&parent_id=<?php echo $row['nl_id']; ?>&parent_code=<?php echo $row['nl_code']; ?>" title="Add Child Link"><i class="ion-android-add-circle icon"></i></a></li>
                            <?php
                        }
                    }
                    ?>
                    <?php
                    if ($row['is_fixed'] == 0) {
                        if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
                            ?>
                            <li><a href="add-navigation.php?nav_id=<?php echo $nav_id; ?>&edit=<?php echo $row['nl_id'] ?>" title="<?php echo t_lang('M_TXT_EDIT') ?>"><i class="ion-edit icon"></i></a></li>
                            <?php
                        }
                    }
                    ?>
                    <?php
                    if (!($row['children'] > 0) && $row['is_fixed'] == 0) {
                        if ((checkAdminAddEditDeletePermission(1, '', 'delete'))) {
                            ?>
                            <li><a href="navigation.php?nav_id=<?php echo $nav_id; ?>&delete=<?php echo $row['nl_id'] ?>" onClick="requestPopup(this, '<?php echo t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD'); ?>', 1)" title="<?php echo t_lang('M_TXT_DELETE') ?>"><i class="ion-android-delete icon"></i></a></li>
                                    <?php
                                }
                            }
                            ?>
                </ul>
            </td>
        </tr>
        <?php
    }
    if ($db->total_records($rs) == 0) {
        echo '<tr><td colspan="4">No Records Found.</td></tr>';
    }
    ?>
</tbody>
</table>
</td>
<?php require_once './footer.php'; ?>
