<?php
require_once './application-top.php';
checkAdminPermission(1);
if ($_GET['faq_category_id'] != "" || isset($_GET['faq_category_id'])) {
    $faq_category_id = $_GET['faq_category_id'];
} else {
    redirectUser('faq-categories.php');
}
if ($_GET['delete'] > 0) {
    if ((checkAdminAddEditDeletePermission(1, '', 'delete'))) {
        $db->query("update tbl_cms_faq set faq_deleted=1 where faq_id=" . $_GET['delete']);
        $msg->addMsg("Faq Deleted Successfully.");
    } else {
        die('Unauthorized Access.');
    }
}
$faq_content_listing = new SearchBase('tbl_cms_faq', 'cmspage');
$faq_content_listing->addCondition('faq_deleted', '=', 0);
$faq_content_listing->addCondition('faq_category_id', '=', $faq_category_id);
$faq_content_listing->addOrder('faq_display_order', 'asc');
$faq_content_listing->getQuery();
$faq_listing = $faq_content_listing->getResultSet();
$breadQry = $db->query("select * from tbl_cms_faq_categories where category_id=$faq_category_id");
$breadrow = $db->fetch($breadQry);
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0);' => t_lang('M_TXT_CMS'),
    'faq-categories.php' => t_lang('M_TXT_FAQ'),
    '' => $breadrow['category_name' . $_SESSION['lang_fld_prefix']]
];
require_once './header.php';
?>
</div></td>
<script type="text/javascript">
    $(document).ready(function () {
        //Table DND call
        $('#cms-faq-listing').tableDnD({
            onDrop: function (table, row) {
                var order = $.tableDnD.serialize('id');
                callAjax('cms-ajax.php', order + '&mode=REORDER_FAQ_QUES', function (t) {
                    $.facebox(t);
                });
            }
        });
    });
</script>
<div id="msgbox"></div>	
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_LIST_OF') ?> <?php echo t_lang('M_TXT_FAQ') ?>
            <?php if (checkAdminAddEditDeletePermission(1, '', 'add')) { ?>
                <ul class="actions right">
                    <li class="droplink">
                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                        <div class="dropwrap">
                            <ul class="linksvertical">
                                <li>
                                    <a href="cms-faq-detail.php?faq_category_id=<?php echo $faq_category_id; ?>&mode1=Add"><?php echo t_lang('M_TXT_ADD_NEW'); ?></a>
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
                <?php } if (isset($_SESSION['msgs'][0])) { ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?> 
    <table class="tbl_data" id="cms-faq-listing" width="100%">
        <thead>
            <tr>                      
                <th><?php echo t_lang('M_TXT_QUESTION_TITLE'); ?></th>
                <th><?php echo t_lang('M_TXT_FAQ_META_TITLE'); ?></th>						
                <th><?php echo t_lang('M_FRM_STATUS'); ?></th>												
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $db->fetch($faq_listing)) { ?>
                <tr id="<?php echo $row['faq_id'] ?>">
                    <td><?php
                        echo '<strong>' . $arr_lang_name[0] . '</strong>' . ' ' . $row['faq_question_title'] . '<br/>';
                        echo '<strong>' . $arr_lang_name[1] . '</strong>' . ' ' . $row['faq_question_title_lang1'];
                        ?></td>
                    <td><?php echo $row['faq_meta_title' . $_SESSION['lang_fld_prefix']]; ?></td>
                    <td><?php echo $row['faq_active'] == '1' ? '<span class="label label-primary">' . t_lang('M_TXT_ACTIVE') . '</span>' : '<span class="label label-danger">' . t_lang('M_TXT_INACTIVE') . '</span>'; ?></td>					  
                    <td> 
                        <?php if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) { ?>
                            <ul class="actions">
                                <li><a href="cms-faq-detail.php?faq_category_id=<?php echo $faq_category_id; ?>&edit1=<?php echo $row['faq_id']; ?>" title="<?php echo t_lang('M_TXT_EDIT'); ?>"><i class="ion-edit icon"></i></a></li>
                            <?php } ?>
                            <?php if ((checkAdminAddEditDeletePermission(1, '', 'delete'))) { ?>
                                <li><a href="?faq_category_id=<?php echo $faq_category_id; ?>&delete=<?php echo $row['faq_id']; ?>"  onClick="requestPopup(this, '<?php echo t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD'); ?>', 1);" title="<?php echo t_lang('M_TXT_DELETE'); ?>"><i class="ion-android-delete icon"></i></a></li>
                            </ul>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            <?php
            if ($db->total_records($faq_listing) == 0) {
                echo '<tr><td colspan="4">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
            }
            ?>
        </tbody>
    </table>
</td>
<?php
require_once './footer.php';
