<?php
require_once './application-top.php';
checkAdminPermission(1);
require_once './header.php';
if ($_GET['nav_id'] == "" && $_GET['edit'] == "") {
    redirectUser('navigation.php');
}
$nav_id = $_GET['nav_id'];
if ((isset($_GET['parent_id']) && ($_GET['parent_id'] != ''))) {
    $nav_id = $_GET['nav_id'];
    $parent_id = $_GET['parent_id'];
    $parent_code = $_GET['parent_code'];
} else if (isset($_GET['edit']) && ($_GET['edit'] > 0)) {
    $edit = $_GET['edit'];
    $rs = $db->query("select nl_code from tbl_nav_links where nl_id=" . $edit);
    if (!$row = $db->fetch($rs))
        die('Invalid Request');
    $code = $row['nl_code'];
} else if ($_GET['nav_id'] != "") {
    $nav_id = $_GET['nav_id'];
} else {
    redirectUser('navigation.php');
}
$cms_page = $db->query("Select page_id , page_name from tbl_cms_pages where page_active=1 and page_deleted = 0 order by page_name asc");
$cms_result = $db->fetch_all_assoc($cms_page);
$navCheckMultilevel = $db->query("Select * from tbl_navigations where nav_id = " . $nav_id . " and nav_active= 1");
$nav_result = $db->fetch($navCheckMultilevel);
$isMultilevel = $nav_result['nav_ismultilevel'];
$frm = new Form('frm_navigation', 'frm_navigation');
$frm->setAction('');
$frm->setTableProperties(' width="100%" cellpadding="0" cellspacing="0" class="tbl_form"');
$frm->setJsErrorDisplay('afterfield');
$frm->setLeftColumnProperties(' style="padding: 5px;"');
$frm->addHiddenField('', 'nl_id', '0', 'nl_id');
$fld = $frm->addRequiredField(t_lang('M_FRM_NAVIGATION_CAPTION'), 'nl_caption', '', 'nl_caption', 'class="input"');
$frm->addSelectBox(t_lang('M_FRM_NAVIGATION_TYPE'), 'nl_type', $arr_nav_type, '', 'onchange="return test1();"', 'Select')->requirements()->setRequired();
if ($_GET['edit'] == "" && $_GET['parent_id'] == "") {
    $NavOrder = $db->query("Select max(nl_display_order ) as MaxOrder from tbl_nav_links where nl_nav_id ='" . $nav_id . "' and  nl_parent_id = 0 and nl_deleted = 0");
    $nav_result = $db->fetch($NavOrder);
    $MaxOrder = $nav_result['MaxOrder'];
    $frm->addHiddenField('', 'nl_display_order', $MaxOrder + 1, 'nl_display_order');
}
if ($parent_id != "" && $parent_code != "" || $edit > 0) {
    $srch = new SearchBase('tbl_nav_links');
    if ($code != '')
        $srch->addCondition('nl_code', 'NOT LIKE', $code . '%');
    $srch->addCondition('nl_deleted', '=', 0);
    $srch->addCondition('nl_nav_id', '=', $nav_id);
    $srch->addMultipleFields(['nl_id', 'nl_caption']);
    $srch->addOrder('nl_code');
    $srch->addOrder('nl_display_order');
    $parent_page = $srch->getResultSet();
    $srch->getQuery();
    $parent_result = $db->fetch_all_assoc($parent_page);
    $rsc = $db->query("SELECT  nl_id, nl_caption,nl_code,nl_nav_id FROM `tbl_nav_links` WHERE  nl_nav_id = $nav_id AND `nl_code` NOT LIKE '$code%' AND `nl_deleted` = '0'   ORDER BY nl_code asc, nl_display_order asc");
    $parentArray = [];
    while ($arrs = $db->fetch($rsc)) {
        $checkCode = strlen($arrs['nl_code']) / 5;
        if ($checkCode == 1) {
            $arrow = "";
        }
        if ($checkCode > 1) {
            $arrow = "->";
        }
        $parentArray[$arrs['nl_id']] = str_repeat($arrow, $checkCode - 1) . " " . $arrs['nl_caption'];
    }
    if ($edit > 0 && $isMultilevel == 1) {
        $frm->addSelectBox('Parent Pages', 'nl_parent_id', $parentArray, '', '', 'Select');
    }
}
if ($edit != "") {
    $hide_show_div = $db->query("Select * from tbl_nav_links where nl_deleted = 0 and nl_id =$edit ");
    $div_result = $db->fetch($hide_show_div);
    if ($div_result['nl_type'] == 0) {
        $fld = $frm->addSelectBox('<div id="div_id1_c" >' . t_lang('M_FRM_CMS_PAGE_LIST') . '</div>', 'nl_cms_page_id', $cms_result, 'nl_cms_page_id', ' ', 'Select');
        $fld->html_before_field = '<div id="div_id1" >';
        $fld->html_after_field = '</div>';
        $frm->addTextArea('<div id="editor_hide" style="display:none;">' . t_lang('M_FRM_HTML_FOR_CUSTOM_PAGES') . '</div>', 'nl_html' . $_SESSION['lang_fld_prefix'], '', 'nl_html' . $_SESSION['lang_fld_prefix'], 'class="textarea" rows="5" col="25" ');
        $frm->getField('nl_html' . $_SESSION['lang_fld_prefix'])->html_before_field = '<div id="editor_hide1" style="display:none;">';
        $frm->getField('nl_html' . $_SESSION['lang_fld_prefix'])->html_after_field = '</div>';
        $frm->addTextBox('<div id="urlShow"  style="display:none;" >' . t_lang('M_FRM_EXTERNAL_URL') . '</div>', 'nl_html1', '', 'nl_html1', 'class="input"');
        $frm->getField('nl_html1')->html_before_field = '<div id="urlHide"  style="display:none;" >';
        $frm->getField('nl_html1')->html_after_field = '</div>';
        $frm->addSelectBox('<div id="dropHide" >' . t_lang('M_FRM_LINK_TARGET') . '</div>', 'nl_target', array('_self' => 'Current Window', '_blank' => 'New Window'), '', '', 'Select');
        $frm->getField('nl_target')->html_before_field = '<div id="dropShow" >';
        $frm->getField('nl_target')->html_after_field = '</div>';
    }
    if ($div_result['nl_type'] == 1) {
        $fld = $frm->addSelectBox('<div id="div_id1_c" style="display:none;">' . t_lang('M_FRM_CMS_PAGE_LIST') . '</div>', 'nl_cms_page_id', $cms_result, 'nl_cms_page_id', ' ', 'Select');
        $fld->html_before_field = '<div id="div_id1" style="display:none;">';
        $fld->html_after_field = '</div>';
        $frm->addTextArea('<div id="editor_hide" >' . t_lang('M_FRM_HTML_OF_CUSTOM_LINKS') . '</div>', 'nl_html' . $_SESSION['lang_fld_prefix'], '', 'nl_html' . $_SESSION['lang_fld_prefix'], 'class="textarea" rows="5" col="25" ');
        $frm->getField('nl_html' . $_SESSION['lang_fld_prefix'])->html_before_field = '<div id="editor_hide1" >';
        $frm->getField('nl_html' . $_SESSION['lang_fld_prefix'])->html_after_field = '</div>';
        $frm->addTextBox('<div id="urlShow"  style="display:none;" >' . t_lang('M_FRM_EXTERNAL_URL') . '</div>', 'nl_html1', '', 'nl_html1', 'class="input"');
        $frm->getField('nl_html1')->html_before_field = '<div id="urlHide"  style="display:none;" >';
        $frm->getField('nl_html1')->html_after_field = '</div>';
        $frm->addSelectBox('<div id="dropHide" >' . t_lang('M_FRM_LINK_TARGET') . '</div>', 'nl_target', array('_self' => 'Current Window', '_blank' => 'New Window'), '', '', 'Select');
        $frm->getField('nl_target')->html_before_field = '<div id="dropShow" >';
        $frm->getField('nl_target')->html_after_field = '</div>';
    }
    if ($div_result['nl_type'] == 2) {
        $fld = $frm->addSelectBox('<div id="div_id1_c" style="display:none;">' . t_lang('M_FRM_CMS_PAGE_LIST') . '</div>', 'nl_cms_page_id', $cms_result, 'nl_cms_page_id', ' ', 'Select');
        $fld->html_before_field = '<div id="div_id1" style="display:none;">';
        $fld->html_after_field = '</div>';
        $frm->addTextArea('<div id="editor_hide" style="display:none;">' . t_lang('M_FRM_HTML_FOR_CUSTOM_PAGES') . '</div>', 'nl_html' . $_SESSION['lang_fld_prefix'], '', 'nl_html' . $_SESSION['lang_fld_prefix'], 'class="textarea" rows="5" col="25" ');
        $frm->getField('nl_html' . $_SESSION['lang_fld_prefix'])->html_before_field = '<div id="editor_hide1" style="display:none;">';
        $frm->getField('nl_html' . $_SESSION['lang_fld_prefix'])->html_after_field = '</div>';
        $frm->addTextBox('<div id="urlShow" >' . t_lang('M_FRM_EXTERNAL_URL') . '</div>', 'nl_html1', $div_result['nl_html'], 'nl_html1', 'class="input"');
        $frm->getField('nl_html1')->html_before_field = '<div id="urlHide" >';
        $frm->getField('nl_html1')->html_after_field = '</div>';
        $frm->addSelectBox('<div id="dropHide" >' . t_lang('M_FRM_LINK_TARGET') . '</div>', 'nl_target', array('_self' => 'Current Window', '_blank' => 'New Window'), '', '', 'Select');
        $frm->getField('nl_target')->html_before_field = '<div id="dropShow" >';
        $frm->getField('nl_target')->html_after_field = '</div>';
    }
} else {
    $fld = $frm->addSelectBox('<div id="div_id1_c" style="display:none;">' . t_lang('M_FRM_CMS_PAGE_LIST') . '</div>', 'nl_cms_page_id', $cms_result, 'nl_cms_page_id', ' ', 'Select', 'nl_cms_page_id');
    $fld->html_before_field = '<div id="div_id1" style="display:none;">';
    $fld->html_after_field = '</div>';
    $frm->addTextArea('<div id="editor_hide" style="display:none;">' . t_lang('M_FRM_HTML_OF_CUSTOM_LINKS') . '</div>', 'nl_html' . $_SESSION['lang_fld_prefix'], '', 'nl_html' . $_SESSION['lang_fld_prefix'], 'class="textarea" rows="5" col="25" ');
    $frm->getField('nl_html' . $_SESSION['lang_fld_prefix'])->html_before_field = '<div id="editor_hide1" style="display:none;">';
    $frm->getField('nl_html' . $_SESSION['lang_fld_prefix'])->html_after_field = '</div>';
    $frm->addTextBox('<div id="urlShow" style="display:none;"> ' . t_lang('M_FRM_EXTERNAL_URL') . '</div>', 'nl_html1', '', 'nl_html1', 'class="input"');
    $frm->getField('nl_html1')->html_before_field = '<div id="urlHide" style="display:none;">';
    $frm->getField('nl_html1')->html_after_field = '</div>';
    $frm->addSelectBox('<div id="dropHide" style="display:none;">' . t_lang('M_FRM_LINK_TARGET') . '</div>', 'nl_target', array('_self' => 'Current Window', '_blank' => 'New Window'), '', '', 'Select');
    $frm->getField('nl_target')->html_before_field = '<div id="dropShow" style="display:none;">';
    $frm->getField('nl_target')->html_after_field = '</div>';
    if ($post['nl_type'] == 0) {
        
    } else {
        $frm->addHiddenField('', 'nl_cms_page_id', 0, 'nl_cms_page_id');
    }
}
$frm->addHiddenField('', 'nl_nav_id', $nav_id, 'nl_nav_id');
if ($edit == "") {
    $frm->addHiddenField('', 'nl_parent_id', $parent_id, 'nl_parent_id');
}
$frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_ADD'), '', ' class="inputbuttons"');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    $record = new TableRecord('tbl_nav_links');
    $arr_lang_independent_flds = array('nl_id', 'nl_nav_id', 'nl_parent_id', 'nl_html1', 'nl_html' . $_SESSION['lang_fld_prefix'], 'nl_target', 'nl_type', 'mode', 'btn_submit');
    assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
    if ($post['nl_type'] == 2) {
        $nl_html = $post['nl_html1'];
        $record->setFldValue('nl_html', $nl_html);
        $record->setFldValue('nl_cms_page_id', 0);
    }
///////////////////Bullets///////////////////////
    if ($_FILES['nl_bullet_image']['name'] != "") {
        $item_path = time() . "_thumb_" . $_FILES['nl_bullet_image']['name'];
        if (!move_uploaded_file($_FILES['nl_bullet_image']['tmp_name'], NAVIGATION_BULLETS_PATH . $item_path)) {
            die('Could not save file.');
        }
        $record->setFldValue('nl_bullet_image' . $_SESSION['lang_fld_prefix'], $item_path);
        if ($post['nl_id'] > 0) {
            $getImg = $db->query("select nl_bullet_image from tbl_nav_links 
						where nl_id='" . $post['nl_id'] . "'");
            $imgRow = $db->fetch($getImg);
            unlink(NAVIGATION_BULLETS_PATH . $imgRow['nl_bullet_image']);
        }
    }
    if ($_FILES['nl_bullet_image_hover']['name'] != "") {
        $item_path = time() . "_thumb_" . $_FILES['nl_bullet_image_hover']['name'];
        if (!move_uploaded_file($_FILES['nl_bullet_image_hover']['tmp_name'], NAVIGATION_BULLETS_PATH . $item_path)) {
            die('Could not save file.');
        }
        $record->setFldValue('nl_bullet_image_hover' . $_SESSION['lang_fld_prefix'], $item_path);
        if ($post['nl_id'] > 0) {
            $getImg = $db->query("select nl_bullet_image_hover from tbl_nav_links 
						where nl_id='" . $post['nl_id'] . "'");
            $imgRow = $db->fetch($getImg);
            unlink(NAVIGATION_BULLETS_PATH . $imgRow['nl_bullet_image_hover']);
        }
    }
///////////////////////////////////////////////////////
    if ($post['nl_id'] > 0) {
        if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
            if ($post['nl_parent_id'] == "") {
                $post['nl_parent_id'] = 0;
                $record->setFldValue('nl_parent_id', 0);
            }
            if ($record->update('nl_id=' . $post['nl_id'])) {
                $rs = $db->query("select * from tbl_nav_links where nl_id=" . $post['nl_id']);
                if (!$row = $db->fetch($rs))
                    die('Invalid Request');
                $old_code = $row['nl_code'];
                $new_code = getNavCode($post['nl_id'], $post['nl_parent_id']);
                $qry = "update tbl_nav_links set nl_code=REPLACE(nl_code, '" . $old_code . "', '" . $new_code . "')";
                if (!$db->query($qry)) {
                    $msg->addError($db->getError());
                } else {
                    $msg->addMsg(t_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
                }
                header("Location:navigation.php?nav_id=" . $post['nl_nav_id']);
                exit;
            } else {
                $msg->addError('Could not update. Error! ' . $record->getError());
            }
            //$db->query("SELECT REPLACE('$code','$replaceingCode', $nl_code)");
        } else {
            die(t_lang("M_TXT_UNAUTHORIZED_ACCESS"));
        }
    } else {
        if ((checkAdminAddEditDeletePermission(1, '', 'add'))) {
            if ($post['nl_type'] == 0) {
                $nl_cms_page_id = $_POST['nl_cms_page_id'];
                //die($nl_cms_page_id);
                $record->setFldValue('nl_cms_page_id', $post['nl_cms_page_id']);
            }
            if ($record->addNew()) {
                $last_inserted_id = $record->getId();
                $arr['nl_code'] = getNavCode($last_inserted_id, $_POST['nl_parent_id']);
                $record = new TableRecord('tbl_nav_links');
                $record->assignValues($arr);
                $record->update('nl_id=' . $last_inserted_id);
                $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
                header("Location:navigation.php?nav_id=" . $_POST['nl_nav_id']);
                exit;
                //print_r($arr['nl_code']);
            } else {
                $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
            }
        } else {
            die(t_lang("M_TXT_UNAUTHORIZED_ACCESS"));
        }
    }
}
if ($_GET['edit'] > 0) {
    if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
        $record = new TableRecord('tbl_nav_links');
        $record->loadFromDb('nl_id=' . $_GET['edit'], true);
        $row = $record->getFlds();
        $row['btn_submit'] = t_lang('M_TXT_UPDATE');
        fillForm($frm, $row);
        $msg->addMsg(t_lang('M_TXT_Edit_THE_VALUES_AND_UPDATE'));
    } else {
        die(t_lang("M_TXT_UNAUTHORIZED_ACCESS"));
    }
}
$arr_bread = array(
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0);' => t_lang('M_TXT_CMS'),
    'navigation-management.php' => t_lang('M_TXT_LIST_OF_NAVIGATIONS'),
    'navigation.php?nav_id=' . $_GET['nav_id'] => $nav_result['nav_name'],
    '' => t_lang('M_TXT_ADD') . '/' . t_lang('M_TXT_EDIT'),
);
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_NAVIGATION_MANAGEMENT'); ?></div>
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
    <div class="box"><div class="title"> <?php echo t_lang('M_TXT_NAVIGATION_MANAGEMENT'); ?> </div><div class="content"><?php
            if (isset($_GET['edit']) OR isset($_GET['nav_id'])) {
                if ((checkAdminAddEditDeletePermission(1, '', 'add')) || (checkAdminAddEditDeletePermission(1, '', 'edit'))) {
                    echo $frm->getFormHtml();
                }
            }
            ?></div></div>			 
</td>
<?php
require_once './footer.php';
