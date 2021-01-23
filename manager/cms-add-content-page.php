<?php
require_once './application-top.php';
checkAdminPermission(1);
require_once './header.php';
if ($_REQUEST['add'] == "" && $_REQUEST['content'] == "") {
    header("Location:cms-page-detail.php");
    exit;
}
$page_content_frm = new Form('page_content_info', 'page_content_info');
$page_content_frm->addHiddenField('', 'mode', 'page_content_setup');
$page_content_frm->setAction('?');
$page_content_frm->setJsErrorDisplay('afterfield');
$page_content_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$page_content_frm->setFieldsPerRow(1);
$page_content_frm->captionInSameCell(false);
$page_content_frm->addSelectBox('M_FRM_SELECT_CONTENT_TYPE', 'cmsc_type', ['0' => 'html'], '', '')->requirements()->setRequired();
$fld = $page_content_frm->addHtmlEditor('M_TXT_CONTENT', 'cmsc_content', '');
$fld->html_before_field = '<div class="frm-editor">';
$fld->html_after_field = '</div>';
$page_content_frm->addHiddenField('', 'cmsc_display_order', '', '', 'readonly="readonly"');
$page_content_frm->addHiddenField('', 'cmsc_id', '', '', 'readonly="readonly"');
$page_content_frm->addHiddenField('', 'cmsc_page_id', $_GET['add'], '', 'readonly="readonly"');
$page_content_frm->addHiddenField('', 'add', $_GET['add'], '', 'readonly="readonly"');
$page_content_frm->addHiddenField('', 'hide', '001', '', 'readonly="readonly"');
$page_content_frm->addHiddenField('', 'content', $_GET['content'], '', 'readonly="readonly"');
$page_content_frm->addSubmitButton('', 'btn_submit_content', 'Add', '', ' class="inputbuttons" ');
updateFormLang($page_content_frm);
?>
<?php
$post = getPostedData();
if ($post['mode'] == 'page_content_setup') {
    $record = new TableRecord('tbl_cms_contents');
    $arr_lang_independent_flds = ['cmsc_type', 'cmsc_display_order', 'cmsc_id', 'cmsc_page_id', 'add', 'hide', 'content', 'mode', 'btn_submit'];
    assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
    if ($post['cmsc_id'] > 0) {
        if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
            if ($record->update('cmsc_id=' . $post['cmsc_id'])) {
                $cmsc_id = $post['cmsc_id'];
                $cmsc_page_id = $post['cmsc_page_id'];
                $hide = $post['hide'];
                $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
                header("Location:cms-page-detail.php?editcontent=$cmsc_page_id&hide=$hide");
                exit;
            } else {
                $msg->addError('Could not update. Error! ' . $record->getError());
            }
        } else {
            die('Unauthorized Access.');
        }
    } else {
        if ((checkAdminAddEditDeletePermission(1, '', 'add'))) {
            if ($record->addNew()) {
                $page_id = $post['cmsc_id'];
                $cmsc_page_id = $post['cmsc_page_id'];
                $hide = $post['hide'];
                $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
                header("Location:cms-page-detail.php?editcontent=$cmsc_page_id&hide=$hide");
                exit;
            } else {
                $msg->addError('Could not add. Error! ' . $record->getError());
            }
        } else {
            die('Unauthorized Access.');
        }
    }
    header("Location:cms-page-detail.php?edit=$page_id");
    exit;
}
if ($_GET['content'] > 0) {
    $record = new TableRecord('tbl_cms_contents');
    $record->loadFromDb('cmsc_id=' . $_GET['content'], true);
    $row = $record->getFlds();
    $row['btn_submit_content'] = t_lang('M_TXT_UPDATE');
    /* $page_content_frm->fill($row); */
    fillForm($page_content_frm, $row);
    $msg->addMsg(t_lang('M_TXT_Edit_THE_VALUES_AND_UPDATE'));
}
############################################################################
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0);' => 'CMS',
    'cms-page-detail.php?editcontent=' . $_GET['content'] . '&hide=001' => t_lang('M_TXT_PAGE_CONTENT')
];
?>	</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo $stat; ?> <?php echo t_lang('M_TXT_PAGE_CONTENT'); ?> </div>
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
    <div class="box">
        <div class="title"> <?php echo t_lang('M_TXT_PAGE_CONTENT'); ?> </div>
        <div class="content">
            <?php
            if ((checkAdminAddEditDeletePermission(1, '', 'add')) || (checkAdminAddEditDeletePermission(1, '', 'edit'))) {
                echo $page_content_frm->getFormHtml();
            }
            ?>
        </div>
    </div>
</td>
<?php
require_once './footer.php';
