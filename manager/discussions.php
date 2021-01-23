<?php
require_once './application-top.php';
checkAdminPermission(5);
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
$srch = new SearchBase('tbl_deal_discussions', 'dd');
$srch->addOrder('comment_id', 'desc');
$srch->joinTable('tbl_deals', 'INNER JOIN', 'dd.comment_deal_id=d.deal_id ', 'd');
//paging
$srch->setPageSize(15);
$srch->setPageNumber($page);
//paging
//$srch->addFld('dd.*','d.*');
$navigation_listing = $srch->getResultSet();
$pagestring = '';
$frm = getMBSFormByIdentifier('frmDealDiscussion');
$frm->setAction('?page=' . $page);
$frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$fld2 = $frm->getField('submit');
$fld2->html_after_field = '';
$fld2->extra = 'class="inputbuttons buttons"';
$pagestring .= createHiddenFormFromPost('frmPaging', '?', array('page', 'status'), array('page' => '', 'status' => $_REQUEST['status']));
$pagestring .= '<div class="pagination"><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
if (isset($_GET['delete']) && $_GET['delete'] != "") {
    if ((checkAdminAddEditDeletePermission(5, '', 'delete'))) {
        $comment_id = $_GET['delete'];
        $db->query("DELETE FROM tbl_deal_discussions WHERE comment_id =$comment_id");
        $msg->addMsg(t_lang('M_TXT_RECORD_DELETED'));
        redirectUser('?page=' . $page);
    } else {
        die('Unauthorized Access.');
    }
}
if (is_numeric($_GET['edit'])) {
    if ((checkAdminAddEditDeletePermission(5, '', 'edit'))) {
        $record = new TableRecord('tbl_deal_discussions');
        $record->setFldValue('comment_comments', nl2br($_POST['comment_comments']));
        if (!$record->loadFromDb('comment_id=' . $_GET['edit'], true)) {
            $msg->addError($record->getError());
        } else {
            $arr = $record->getFlds();
            $rs = $db->query("select * from tbl_deal_discussions where comment_id=" . $arr['comment_id']);
            $row = $db->fetch($rs);
            $arr['comment_title'] = $row['comment_title'];
            $arr['btn_submit'] = t_lang('M_TXT_UPDATE');
            fillForm($frm, $arr);
            /*  $frm->fill($arr); */
            $msg->addMsg(t_lang('M_TXT_Edit_THE_VALUES_AND_UPDATE'));
        }
    } else {
        die('Unauthorized Access.');
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error)
            $msg->addError($error);
    } else {
        $record = new TableRecord('tbl_deal_discussions');
        /* $record->assignValues($post); */
        $arr_lang_independent_flds = array('comment_id', 'comment_deal_id', 'comment_user_id', 'comment_posted_on', 'comment_approved', 'mode', 'btn_submit');
        assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
        $record->setFldValue('comment_comments', nl2br($_POST['comment_comments']));
        $success = ($post['comment_id'] > 0) ? $record->update('comment_id' . '=' . $post['comment_id']) : $record->addNew();
        if ($success) {
            $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
            redirectUser();
        } else {
            $msg->addError('Could not add/update! Error: ' . $record->getError());
            /* $frm->fill($post); */
            fillForm($frm, $post);
        }
    }
}
require_once './header.php';
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0)' => t_lang('M_TXT_DEALS'),
    '' => t_lang('M_TXT_APPROVE_COMMENT')
];
?>
<script type="text/javascript" charset="utf-8">
    var txtsuredel = "<?php echo addslashes(t_lang('M_TXT_ARE_YOU_SURE_TO_DELETE')); ?>";
</script> 
</div>
</td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_COMMENTS'); ?> <?php echo t_lang('M_TXT_LISTING'); ?></div>
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
    <?php } if (is_numeric($_REQUEST['edit']) || $_REQUEST['add'] == 'new') { ?>
        <?php if ((checkAdminAddEditDeletePermission(5, '', 'add')) || (checkAdminAddEditDeletePermission(5, '', 'edit'))) { ?>
            <div class="box"><div class="title"> <?php echo t_lang('M_TXT_COMMENTS'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
            <?php
        } else {
            die('Unauthorized Access.');
        }
    } else {
        ?>
        <table class="tbl_data" width="100%">
            <thead>
                <tr>
                    <th ><?php echo t_lang('M_TXT_DEAL_NAME'); ?></th>
                    <th ><?php echo t_lang('M_TXT_COMMENT_TITLE'); ?></th>
                    <th ><?php echo ucfirst(t_lang('M_TXT_COMMENTS')); ?></th>
                    <th><?php echo t_lang('M_TXT_COMMENT_POSTED_ON'); ?></th>
                    <th><?php echo t_lang('M_TXT_STATUS'); ?></th>
                    <th><?php echo t_lang('M_TXT_ACTION'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $db->fetch($navigation_listing)) { ?>
                    <tr>	
                        <td width="20%"><?php echo $row['deal_name']; ?></td>
                        <td width="20%"><?php
                            echo '<strong>' . $arr_lang_name[0] . '</strong>' . ' ' . $row['comment_title'] . '<br/>';
                            echo '<strong>' . $arr_lang_name[1] . '</strong>' . ' ' . $row['comment_title_lang1'];
                            ?></td>
                        <td width="20%" <?php if ($row['comment_comments'] == "") echo 'style="background-color:#eeefff;"'; ?>>						<?php
                            if ($row['comment_comments'] == "")
                                echo 'Admin comments pending.';
                            else
                                echo $row['comment_comments'];
                            ?></td>
                        <td width="15%"><?php echo displayDate($row['comment_posted_on'], true, '', ''); ?></td>
                        <td width="5%" id="comment<?php echo $row['comment_id'] ?>"><?php
                            if ($row['comment_approved'] == 1) {
                                echo t_lang('M_TXT_APPROVED');
                            }
                            if ($row['comment_approved'] == 0) {
                                echo t_lang('M_TXT_DISAPPROVED');
                            }
                            if ($row['comment_approved'] == 2) {
                                echo t_lang('M_TXT_PENDING');
                            }
                            ?></td>
                        <td width="20%" id="comment-status<?php echo $row['comment_id'] ?>"> 
                            <?php if ($row['comment_approved'] == 0) { ?>
                                <?php if ((checkAdminAddEditDeletePermission(5, '', 'edit'))) { ?>
                                    <a href="javascript:void(0);" onclick="approveComment(<?php echo $row['comment_id'] ?>, 1);" class="btn green"><?php echo t_lang('M_TXT_APPROVE_COMMENT'); ?></a> 
                                    <a href="javascript:void(0);" onclick="approveComment(<?php echo $row['comment_id'] ?>, 2);" class="btn"> <?php echo t_lang('M_TXT_PENDING'); ?></a> 
                                <?php } ?>
                            <?php } else if ($row['comment_approved'] == 1) { ?>	
                                <?php if ((checkAdminAddEditDeletePermission(5, '', 'edit'))) { ?>
                                    <a href="javascript:void(0);" onclick="approveComment(<?php echo $row['comment_id'] ?>, 0);" class="btn delete"><?php echo t_lang('M_TXT_DISAPPROVE_COMMENT'); ?></a> 
                                    <a href="javascript:void(0);" onclick="approveComment(<?php echo $row['comment_id'] ?>, 2);" class="btn"> <?php echo t_lang('M_TXT_PENDING'); ?></a> 
                                <?php } ?>
                            <?php } else if ($row['comment_approved'] == 2) { ?>
                                <?php if ((checkAdminAddEditDeletePermission(5, '', 'edit'))) { ?>
                                    <a href="javascript:void(0);" onclick="approveComment(<?php echo $row['comment_id'] ?>, 1);" class="btn green"><?php echo t_lang('M_TXT_APPROVE_COMMENT'); ?></a> 
                                    <a href="javascript:void(0);" onclick="approveComment(<?php echo $row['comment_id'] ?>, 0);" class="btn delete"><?php echo t_lang('M_TXT_DISAPPROVE_COMMENT'); ?></a> 
                                <?php } ?>
                            <?php } ?>
                            <?php if ((checkAdminAddEditDeletePermission(5, '', 'delete'))) { ?>
                                <a href="discussions.php?delete=<?php echo $row['comment_id'] ?>" onclick="requestPopup(this, '<?php echo t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD'); ?>', 1);" class="btn delete"><?php echo t_lang('M_TXT_DELETE'); ?></a> 
                            <?php } ?>
                            <?php if ((checkAdminAddEditDeletePermission(5, '', 'edit'))) { ?>
                                <a href="discussions.php?edit=<?php echo $row['comment_id'] . '&page=' . $page; ?>" class="btn gray"><?php echo t_lang('M_TXT_EDIT'); ?></a> 
                            <?php } ?>
                        </td>
                    </tr>
                    <?php
                }
                if ($db->total_records($navigation_listing) == 0) {
                    echo '<tr><td colspan="6">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
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
