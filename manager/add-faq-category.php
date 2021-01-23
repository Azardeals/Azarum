<?php
require_once './application-top.php';
checkAdminPermission(1);
require_once './header.php';
if ((isset($_GET['mode']) && ($_GET['mode'] == 'Add')) || (isset($_GET['parent_id']) && ($_GET['parent_id'] != ''))) {
    $parent_id = $_GET['parent_id'];
    $parent_code = $_GET['parent_code'];
    $mode = $_GET['mode'];
} else if (isset($_GET['edit']) && ($_GET['edit'] > 0)) {
    $edit = $_GET['edit'];
    $rs = $db->query("select category_code from tbl_cms_faq_categories where category_id=" . $edit);
    if (!$row = $db->fetch($rs)) {
        die('Invalid Request');
    }
    $code = $row['category_code'];
} else {
    redirectUser('faq-categories.php');
}
$frm = new Form('frm_faq_category', 'frm_faq_category');
$frm->setAction('');
$frm->setJsErrorDisplay('afterfield');
$frm->setTableProperties(' width="100%" border="0"  cellpadding="0" cellspacing="0" class="tbl_form"');
$frm->setLeftColumnProperties(' style="padding: 5px;"');
$frm->addHiddenField('', 'category_id', '0', 'category_id');
$fld = $frm->addRequiredField('M_FRM_CATEGORY_NAME', 'category_name', '', 'category_name', 'class="input"');
if ($parent_id != "" && $parent_code != "" || $edit > 0) {
    if ($_SESSION['lang_fld_prefix'] == '_lang1') {
        $get_cat_name = 'IF(CHAR_LENGTH(category_name_lang1),category_name_lang1,category_name) as category_name';
    } else {
        $get_cat_name = 'category_name';
    }
    $rsc = $db->query("SELECT  category_id, " . $get_cat_name . ",category_code FROM `tbl_cms_faq_categories` WHERE `category_code` NOT LIKE '$code%' AND `category_deleted` = '0' AND `category_parent_id` = '0' ORDER BY category_code asc, category_display_order asc");
    $parentArray = [];
    while ($arrs = $db->fetch($rsc)) {
        $checkCode = strlen($arrs['category_code']) / 5;
        if ($checkCode == 1) {
            $arrow = "";
        }
        if ($checkCode > 1) {
            $arrow = "->";
        }
        $parentArray[$arrs['category_id']] = str_repeat($arrow, $checkCode - 1) . " " . $arrs['category_name'];
    }
    if ($parentArray != NULL) {
        $frm->addSelectBox('M_FRM_PARENT_CATEGORIES', 'category_parent_id', $parentArray, '', '', 'Select');
    }
} else {
    $frm->addHiddenField('', 'category_parent_id', 0, 'category_parent_id');
}
$frm->addTextBox('M_FRM_CATEGORY_META_TITLE', 'category_meta_title', '', 'category_meta_title', '');
$frm->addTextArea('M_FRM_CATEGORY_META_KEYWORDS', 'category_meta_keywords', '', 'category_meta_keywords', 'cols="45" rows="5"');
$frm->addTextArea('M_FRM_CATEGORY_META_DESCRIPTION', 'category_meta_description', '', 'category_meta_description', 'cols="45" rows="5"');
$frm->addTextArea('M_FRM_CATEGORY_SEARCH_KEYWORDS', 'category_search_keywords', '', 'category_search_keywords', 'cols="45" rows="5"');
if ($edit == "") {
    $frm->addHiddenField('', 'category_parent_id', $parent_id, 'category_parent_id');
}
$frm->addSelectBox('M_FRM_CATEGORY_STATUS', 'category_active', ['1' => 'Active', '0' => 'Inactive'], '', '', '');
$frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_ADD'), '', ' class="inputbuttons"');
updateFormLang($frm);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    if (!isset($post['category_parent_id'])) {
        $post['category_parent_id'] = 0;
    }
    $record = new TableRecord('tbl_cms_faq_categories');
    $arr_lang_independent_flds = ['category_id', 'category_parent_id', 'category_active', 'mode', 'btn_submit'];
    assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
    if ($post['category_id'] > 0) {
        if ($record->update('category_id=' . $post['category_id'])) {
            $rs = $db->query("select * from tbl_cms_faq_categories where category_id=" . $post['category_id']);
            if (!$row = $db->fetch($rs))
                die('Invalid Request');
            $old_code = $row['category_code'];
            $new_code = getCategoryCode($post['category_id'], $post['category_parent_id']);
            $qry = "update tbl_cms_faq_categories set category_code=REPLACE(category_code, '" . $old_code . "', '" . $new_code . "')";
            if (!$db->query($qry)) {
                $msg->addError($db->getError());
            } else {
                $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
            }
            header("Location:faq-categories.php");
            exit;
        } else {
            $msg->addError('Could not update. Error! ' . $record->getError());
        }
    } else {
        if ($record->addNew()) {
            $last_inserted_id = $record->getId();
            $arr['category_code'] = getCategoryCode($last_inserted_id, $_POST['category_parent_id']);
            $record = new TableRecord('tbl_cms_faq_categories');
            $record->assignValues($arr);
            $record->update('category_id=' . $last_inserted_id);
            $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
            header("Location:faq-categories.php");
            exit;
        } else {
            $msg->addError('Could not add. Error! ' . $record->getError());
        }
    }
}
if ($_GET['edit'] > 0) {
    $record = new TableRecord('tbl_cms_faq_categories');
    $record->loadFromDb('category_id=' . $_GET['edit'], true);
    $row = $record->getFlds();
    $row['btn_submit'] = t_lang('M_TXT_UPDATE');
    fillForm($frm, $row);
    $msg->addMsg(t_lang('M_TXT_Edit_THE_VALUES_AND_UPDATE'));
}
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0);' => t_lang('M_TXT_CMS'),
    'faq-categories.php' => t_lang('M_TXT_FAQ'),
    '' => t_lang('M_TXT_ADD_UPDATE'),
];
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_FAQ_CATEGORY_MANAGEMENT'); ?></div>
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
    <div class="box"><div class="title"> <?php echo t_lang('M_TXT_FAQ_CATEGORY_MANAGEMENT'); ?> </div><div class="content">
            <?php
            if (isset($_GET['edit']) OR isset($_GET['mode']) OR isset($_GET['parent_id'])) {
                echo $frm->getFormHtml();
            }
            ?>
        </div>
    </div>	 
</td>
<?php require_once './footer.php'; ?>
