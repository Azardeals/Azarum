<?php
require_once './application-top.php';
/* define configuration variables */
$rs1 = $db->query("select * from tbl_extra_values");
while ($row1 = $db->fetch($rs1)) {
    define(strtoupper($row1['extra_conf_name']), $row1['extra_conf_val' . $_SESSION['lang_fld_prefix']]);
}
/* end configuration variables */
checkAdminPermission(1);
require_once './header.php';
$frm = new Form('extra_page', 'extra_page');
$frm->setAction('?');
$frm->setTableProperties(' width="100%" border="0" cellspacing="0" cellpadding="0" class="tbl_form" ');
$frm->setFieldsPerRow(1);
$frm->captionInSameCell(false);
$frm->setJsErrorDisplay('afterfield');
$frm->setLeftColumnProperties(' style="padding: 10px;"');
$frm->addTextArea('M_TXT_HOME_META_TITLE', 'extra_home_page_meta_title', EXTRA_HOME_PAGE_META_TITLE, 'extra_home_page_meta_title', 'rows="5" cols="100"')->merge_caption = true;
$frm->addTextArea('M_TXT_HOME_META_KEYWORDS', 'extra_home_page_meta_keywords', EXTRA_HOME_PAGE_META_KEYWORDS, 'extra_home_page_meta_keywords', 'rows="5" cols="100"')->merge_caption = true;
$frm->addTextArea('M_TXT_HOME_META_DESC', 'extra_home_page_meta_description', EXTRA_HOME_PAGE_META_DESCRIPTION, 'extra_home_page_meta_description', 'rows="5" cols="100"')->merge_caption = true;
$__fld = $frm->addHtmlEditor('M_FRM_CHECKBOX_TERMS', 'extra_terms_condition', EXTRA_TERMS_CONDITION);
$__fld->html_before_field = '<div class="frm-editor">';
$__fld->html_after_field = '</div>';
$__fld = $frm->addHtmlEditor('M_TXT_PRIVACY_POLICY', 'extra_privacy_policy', EXTRA_PRIVACY_POLICY);
$__fld->html_before_field = '<div class="frm-editor">';
$__fld->html_after_field = '</div>';
$__fld = $frm->addHtmlEditor('M_TXT_HOW_IT_WORKS', 'extra_how_heading1', EXTRA_HOW_HEADING1);
$__fld->html_before_field = '<div class="frm-editor">';
$__fld->html_after_field = '</div>';
$__fld = $frm->addHtmlEditor('M_TXT_ABOUT_US', 'extra_about_us', EXTRA_ABOUT_US);
$__fld->html_before_field = '<div class="frm-editor">';
$__fld->html_after_field = '</div>';
$__fld = $frm->addHtmlEditor('M_FRM_LOCATION_CONTACT', 'extra_location_contact', EXTRA_LOCATION_CONTACT);
$__fld->html_before_field = '<div class="frm-editor">';
$__fld->html_after_field = '</div>';
$__fld = $frm->addHtmlEditor('M_TXT_PRESS_RELEASE', 'extra_press_content', EXTRA_PRESS_CONTENT);
$__fld->html_before_field = '<div class="frm-editor">';
$__fld->html_after_field = '</div>';
$frm->addHiddenField('', 'mode', 'extra');
$frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_UPDATE'), '', ' class="inputbuttons"');
updateFormLang($frm);
if ($_POST['mode'] == 'extra') {
    if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
        $post = getPostedData();
        $record = new TableRecord('tbl_extra_values');
        $record->assignValues($post);
        foreach ($post as $key => $val) {
            $qry = "update tbl_extra_values set extra_conf_val" . $_SESSION['lang_fld_prefix'] . "=" . $db->quoteVariable($val) . " where extra_conf_name=" . $db->quoteVariable($key);
            $db->query($qry);
        }
        $msg->addMsg(t_lang('M_MSG_RECORD_UPDATED_SUCCESSFULLY'));
        redirectUser('?');
//	header("Location:home-page-banner.php");	exit;
    } else {
        die('Unauthorized Access.');
    }
}
$arr_bread = array(
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0)' => t_lang('M_TXT_CMS'),
    '' => t_lang('M_TXT_EXTRA_PAGES')
);
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_UPDATE'); ?> <?php echo t_lang('M_TXT_CONTENT'); ?> </div>
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
    if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
        $frm->captionInSameCell(false);
        echo '<div class="box"><div class="title"> ' . t_lang('M_TXT_UPDATE') . ' ' . t_lang('M_TXT_CONTENT') . ' </div><div class="content">' . $frm->getFormHtml() . '</div></div>';
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
    ?>
</td>
<?php
require_once './footer.php';
