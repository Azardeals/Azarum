<?php
require_once './application-top.php';
checkAdminPermission(1);
require_once './header.php';
if ($_REQUEST['faq_category_id'] != "" && $_REQUEST['editcontent'] != "" && $_REQUEST['hide'] != "" || $_REQUEST['editgal']) {
    $faq_category_id = $_REQUEST['faq_category_id'];
    $editcontent = $_REQUEST['editcontent'];
    $hide = $_REQUEST['hide'];
} else {
    header("Location:faq-categories.php");
    exit;
}
$faq_content_frm = new Form('page_faq_gallery', 'page_faq_gallery');
$faq_content_frm->addHiddenField('', 'mode', 'faq_gallery_setup');
$faq_content_frm->setAction('?');
$faq_content_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%" ');
$faq_content_frm->setFieldsPerRow(1);
$faq_content_frm->captionInSameCell(false);
//,'1'=>'video gallery'
$faq_content_frm->addSelectBox('M_TXT_PLEASE_SELECT_GALLERY_TYPE', 'cmsfg_type', ['0' => 'image gallery'], '', ' onchange="return test1();"', '');
$faq_content_frm->addHiddenField('', 'cmsfg_display_order', '', '', 'readonly="readonly"');
$faq_content_frm->addHiddenField('', 'cmsfg_id', '', '', 'readonly="readonly"');
$faq_content_frm->addHiddenField('', 'faq_category_id', $faq_category_id, '', 'readonly="readonly"');
$faq_content_frm->addHiddenField('', 'cmsfg_faq_id', $editcontent, '', 'readonly="readonly"');
$faq_content_frm->addHiddenField('', 'hide', $hide, '', 'readonly="readonly"');
$faq_content_frm->addHiddenField('', 'editcontent', $editcontent, '', 'readonly="readonly"');
$faq_content_frm->addSubmitButton('', 'btn_submit_content', t_lang('M_TXT_ADD'), '', ' class="inputbuttons" ');
updateFormLang($faq_content_frm);
?>
<?php
$post = getPostedData();
if ($post['mode'] == 'faq_gallery_setup') {
    $record = new TableRecord('tbl_cms_faq_gallery');
    $record->assignValues($post);
    if ($post['cmsfg_id'] > 0) {
        if ($record->update('cmsfg_id=' . $post['cmsfg_id'])) {
            $cmsfg_id = $post['cmsfg_id'];
            $cmsfg_faq_id = $post['cmsfg_faq_id'];
            //$hide = $post['hide'];
            $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
            header("Location:cms-faq-detail.php?faq_category_id=$faq_category_id&editcontent=$editcontent&hide=$hide");
            exit;
        } else {
            $msg->addError('Could not update. Error! ' . $record->getError());
        }
    } else {
        if ($record->addNew()) {
            $page_id = $post['cmsfg_id'];
            $cmsfg_faq_id = $post['cmsfg_faq_id'];
            $hide = $post['hide'];
            $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
            header("Location:cms-faq-detail.php?faq_category_id=$faq_category_id&editcontent=$editcontent&hide=$hide");
            exit;
        } else {
            $msg->addError('Could not add. Error! ' . $record->getError());
        }
    }
    header("Location:cms-faq-detail.php?faq_category_id=$faq_category_id&editcontent=$editcontent&hide=$hide");
    exit;
}
if ($_GET['editgal'] > 0) {
    $record = new TableRecord('tbl_cms_faq_gallery');
    $record->loadFromDb('cmsfg_id=' . $_GET['editgal'], true);
    $row = $record->getFlds();
    $row['btn_submit_content'] = t_lang('M_TXT_UPDATE');
    $faq_content_frm->fill($row);
    $msg->addMsg(t_lang('M_TXT_Edit_THE_VALUES_AND_UPDATE'));
}
############################################################################
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0);' => t_lang('M_TXT_CMS'),
    'faq-categories.php' => t_lang('M_TXT_FAQ'),
    'cms-faq-detail.php?faq_category_id=' . $_GET['faq_category_id'] . '&editcontent=' . $_GET['editcontent'] . '&hide=001' => 'Page content',
    '' => t_lang('M_TXT_ADD_UPDATE')
];
?>	</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_FAQ'); ?> <?php echo t_lang('M_TXT_CONTENT'); ?></div>
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
    <div class="box">
        <div class="title"> <?php echo t_lang('M_TXT_FAQ'); ?> <?php echo t_lang('M_TXT_CONTENT'); ?> </div>
        <div class="content">
            <?php
            if ($_GET['editgal'] > 0 || $_GET['mode1'] == 'Add') {
                echo $msg->display();
            } echo $faq_content_frm->getFormHtml();
            ?>
        </div>
    </div>
</td>
<?php require_once './footer.php'; ?>
