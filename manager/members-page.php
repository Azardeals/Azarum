<?php
require_once './application-top.php';
/* define configuration variables */
$rs1 = $db->query("select * from tbl_members_page");
while ($row1 = $db->fetch($rs1)) {
    define(strtoupper($row1['members_conf_name']), $row1['members_conf_value']);
}
/* end configuration variables */
checkAdminPermission(1);
require_once './header.php';
$frm = new Form('members_page', 'members_page');
$frm->setAction('?');
$frm->setTableProperties(' width="100%" border="0" cellspacing="0" cellpadding="0" class="tbl_form" ');
$frm->setFieldsPerRow(1);
$frm->captionInSameCell(false);
$frm->setJsErrorDisplay('afterfield');
$frm->setLeftColumnProperties(' style="padding: 10px;"');
if ($_SESSION['lang_fld_prefix'] == "") {
    $frm->addTextarea('M_FRM_BANNER_HEADER_TEXT', 'members_header_text', MEMBERS_HEADER_TEXT);
    $frm->addTextarea('M_FRM_BANNER_SUB_HEADING_TEXT', 'members_sub_heading_text', MEMBERS_SUB_HEADING_TEXT);
    $frm->addTextarea('M_FRM_BANNER_TEXT', 'members_heading_text', MEMBERS_HEADING_TEXT);
    $frm->addTextarea('M_FRM_BUTTON_TEXT', 'members_button_view_text', MEMBERS_BUTTON_VIEW_TEXT);
    $frm->addTextarea('M_FRM_LEFT_COLUMN_HEADING ', 'members_heading_select_a_deal', MEMBERS_HEADING_SELECT_A_DEAL);
    $frm->addTextarea('M_FRM_LEFT_COLUMN_HEADING_TEXT', 'members_heading_select_a_deal_text', MEMBERS_HEADING_SELECT_A_DEAL_TEXT);
    $frm->addTextarea('M_FRM_MIDLE_COLUMN_HEADING', 'members_heading_members_the_deal', MEMBERS_HEADING_MEMBERS_THE_DEAL);
    $frm->addTextarea('M_FRM_MIDLE_COLUMN_HEADING_TEXT', 'members_heading_members_the_deal_text', MEMBERS_HEADING_MEMBERS_THE_DEAL_TEXT);
    $frm->addTextarea('M_FRM_RIGHT_COLUMN_HEADING', 'members_heading_redeem_the_deal', MEMBERS_HEADING_REDEEM_THE_DEAL);
    $frm->addTextarea('M_FRM_RIGHT_COLUMN_HEADING_TEXT', 'members_heading_redeem_the_deal_text', MEMBERS_HEADING_REDEEM_THE_DEAL_TEXT);
} else {
    $frm->addTextarea('M_FRM_BANNER_HEADER_TEXT', 'members_header_text_lang1', MEMBERS_HEADER_TEXT_LANG1);
    $frm->addTextarea('M_FRM_BANNER_SUB_HEADING_TEXT', 'members_sub_heading_text_lang1', MEMBERS_SUB_HEADING_TEXT_LANG1);
    $frm->addTextarea('M_FRM_BANNER_TEXT', 'members_heading_text_lang1', MEMBERS_HEADING_TEXT_LANG1);
    $frm->addTextarea('M_FRM_BUTTON_TEXT', 'members_button_view_text_lang1', MEMBERS_BUTTON_VIEW_TEXT_LANG1);
    $frm->addTextarea('M_FRM_LEFT_COLUMN_HEADING ', 'members_heading_select_a_deal_lang1', MEMBERS_HEADING_SELECT_A_DEAL_LANG1);
    $frm->addTextarea('M_FRM_LEFT_COLUMN_HEADING_TEXT', 'members_heading_select_a_deal_text_lang1', MEMBERS_HEADING_SELECT_A_DEAL_TEXT_LANG1);
    $frm->addTextarea('M_FRM_MIDLE_COLUMN_HEADING', 'members_heading_members_the_deal_lang1', MEMBERS_HEADING_MEMBERS_THE_DEAL_LANG1);
    $frm->addTextarea('M_FRM_MIDLE_COLUMN_HEADING_TEXT', 'members_heading_members_the_deal_text_lang1', MEMBERS_HEADING_MEMBERS_THE_DEAL_TEXT_LANG1);
    $frm->addTextarea('M_FRM_RIGHT_COLUMN_HEADING', 'members_heading_redeem_the_deal_lang1', MEMBERS_HEADING_REDEEM_THE_DEAL_LANG1);
    $frm->addTextarea('M_FRM_RIGHT_COLUMN_HEADING_TEXT', 'members_heading_redeem_the_deal_text_lang1', MEMBERS_HEADING_REDEEM_THE_DEAL_TEXT_LANG1);
}
$frm->addHiddenField('', 'mode', 'extra');
$frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_UPDATE'), '', ' class="inputbuttons"');
updateFormLang($frm);
if ($_POST['mode'] == 'extra') {
    if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
        $post = getPostedData();
        $record = new TableRecord('tbl_members_page');
        $record->assignValues($post);
        foreach ($post as $key => $val) {
            $qry = "update tbl_members_page set members_conf_value=" . $db->quoteVariable($val) . " where members_conf_name=" . $db->quoteVariable($key);
            $db->query($qry);
        }
        $msg->addMsg(t_lang('M_MSG_RECORD_UPDATED_SUCCESSFULLY'));
        redirectUser('?');
    } else {
        die('Unauthorized Access.');
    }
}
$arr_bread = array(
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0)' => t_lang('M_TXT_SETTINGS'),
    '' => t_lang('M_TXT_MEMBER_PAGE')
);
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="redtext"><?php echo $msg->display(); ?> </div><br/><br/>
                <?php } if (isset($_SESSION['msgs'][0])) { ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
        <?php
    }
    if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
        echo '<div class="box"><div class="title"> ' . t_lang('M_TXT_UPDATE_MEMBER_PAGE_CONTENT') . ' </div><div class="content">' . $frm->getFormHtml() . '</div></div>';
    } else {
        die('Unauthorized Access.');
    }
    ?>
</td>
<?php
require_once './footer.php';


