<?php
require_once './application-top.php';
checkAdminPermission(7);
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$pagesize = 20;
$mainTableName = 'tbl_email_templates';
$primaryKey = 'tpl_id';
$colPrefix = 'tpl_';
//Search Form
$Src_frm = new Form('Src_frm', 'Src_frm');
$Src_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$Src_frm->setFieldsPerRow(2);
$Src_frm->captionInSameCell(true);
$Src_frm->addTextBox(t_lang('M_FRM_KEYWORDS'), 'keyword', '', '', '');
$Src_frm->addHiddenField('', 'mode', 'search');
$Src_frm->addHiddenField('', 'status', $_REQUEST['status']);
$fld1 = $Src_frm->addButton('&nbsp;', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="inputbuttons" onclick="location.href=\'email-templates.php\'"');
$fld = $Src_frm->addSubmitButton('&nbsp;', 'btn_search', t_lang('M_TXT_SEARCH'), '', ' class="inputbuttons"')->attachField($fld1);
$frm = getMBSFormByIdentifier('frmEmailTemplate');
$fld = $frm->getField('tpl_message');
$fld->html_before_field = '<div class="frm-editor">';
$fld->html_after_field = '</div>';
$frm->setAction('?');
$fld = $frm->getField('btn_submit');
$fld->value = t_lang('M_TXT_SUBMIT');
updateFormLang($frm);
if (is_numeric($_GET['edit'])) {
    if (checkAdminAddEditDeletePermission(7, '', 'edit')) {
        $record = new TableRecord($mainTableName);
        if (!$record->loadFromDb($primaryKey . '=' . $_GET['edit'], true)) {
            $msg->addError($record->getError());
        } else {
            $arr = $record->getFlds();
            $arr_replacements = explode(',', $arr['tpl_replacements']);
            $arr['tpl_replacements'] = '';
            foreach ($arr_replacements as $val) {
                $val = trim($val);
                if ($val !== '') {
                    $arr['tpl_replacements'] .= 'xx' . $val . 'xx => ' . $val . "\r\n";
                }
            }
            $arr['tpl_replacements'] = nl2br(trim($arr['tpl_replacements']));
            fillForm($frm, $arr);
            $msg->addMsg(t_lang('M_TXT_Edit_THE_VALUES_AND_UPDATE'));
        }
    } else {
        die('Unauthorized Access.');
    }
}
if (is_numeric($_GET['id'])) {
    if (checkAdminAddEditDeletePermission(7, '', 'edit')) {
        if ($_REQUEST['status'] == 'on') {
            $status = 1;
        }
        if ($_REQUEST['status'] == 'off') {
            $status = 0;
        }
        if (isset($status)) {
            $db->query('update tbl_email_templates set tpl_status =' . $status . ' where tpl_id=' . $_GET['id']);
            redirectUser('?');
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
        $record = new TableRecord($mainTableName);
        $arr_lang_independent_flds = ['tpl_id', 'tpl_status', 'tpl_replacements', 'mode', 'btn_submit'];
        assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
        if (checkAdminAddEditDeletePermission(7, '', 'edit')) {
            $success = ($post[$primaryKey] > 0) ? $record->update($primaryKey . '=' . $post[$primaryKey]) : $record->addNew();
        }
        if ($success) {
            $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
            redirectUser();
        } else {
            $msg->addError('Could not add/update! Error: ' . $record->getError());
            $frm->fill($post);
        }
    }
}
$srch = new SearchBase($mainTableName);
if ($post['mode'] == 'search') {
    if ($post['keyword'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('tpl_name', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('tpl_name' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('tpl_subject', 'like', '%' . $post['keyword'] . '%', 'OR');
    }
    $Src_frm->fill($post);
}
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pagestring .= createHiddenFormFromPost('frmPaging', '?', ['page'], ['page' => $page, 'status' => $_REQUEST['status']]);
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) . ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> ', $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</ul></div>';
$arr_listing_fields = [
    'listserial' => 'S.N.',
    'tpl_name' => t_lang('M_FRM_NAME'),
    'tpl_subject' . $_SESSION['lang_fld_prefix'] => t_lang('M_TXT_SUBJECT'),
    'tpl_status' => t_lang('M_FRM_STATUS'),
    'action' => t_lang('M_TXT_ACTION')
];
require_once './header.php';
$arr_bread = ['index.php' => '<img class="home" alt="Home" src="images/home-icon.png">', 'configurations.php' => t_lang('M_TXT_SETTINGS'), '' => t_lang('M_TXT_EMAIL_TEMPLATES')];
?>
<ul class="nav-left-ul">
    <li><a href="configurations.php" ><?php echo t_lang('M_TXT_GENERAL_SETTINGS'); ?></a></li>
    <li><a href="payment-settings.php"><?php echo t_lang('M_TXT_PAYMENT_GATEWAY_SETTINGS'); ?></a></li>
    <li><a href="email-templates.php" class="selected"><?php echo t_lang('M_TXT_EMAIL_TEMPLATES'); ?></a></li>
    <li><a href="language-managment.php"><?php echo t_lang('M_TXT_LANGUAGE_MANAGEMENT'); ?></a></li>
    <li><a href="cities.php" ><?php echo t_lang('M_TXT_CITIES_MANAGEMENT'); ?></a></li>
</ul>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_EMAIL_TEMPLATES'); ?></div>
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['msgs'][0]))) { ?>
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
            <div class="content">
                <?php if (isset($_SESSION['msgs'][0])) { ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
    <?php
    if (is_numeric($_REQUEST['edit'])) {
        if (checkAdminAddEditDeletePermission(7, '', 'edit')) {
            ?>
            <div class="box"><div class="title"> <?php echo t_lang('M_TXT_EMAIL_TEMPLATES'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
            <?php
        } else {
            die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        }
    } else {
        ?>
        <div class="box searchform_filter">
            <div class="title"> <?php echo t_lang('M_TXT_EMAIL_TEMPLATES'); ?> </div>
            <div class="content togglewrap" style="display:none;"><?php echo $Src_frm->getFormHtml(); ?></div>
        </div>
        <table class="tbl_data" width="100%">
            <thead>
                <tr>
                    <?php
                    foreach ($arr_listing_fields as $val) {
                        echo '<th>' . $val . '</th>';
                    }
                    ?>
                </tr>
            </thead>
            <?php
            for ($listserial = ($page - 1) * $pagesize + 1; $row = $db->fetch($rs_listing); $listserial++) {
                echo '<tr' . (($row[$colPrefix . 'active'] == '0') ? ' class="inactive"' : '') . '>';
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td>';
                    switch ($key) {
                        case 'listserial':
                            echo $listserial;
                            break;
                        case 'tpl_status':
                            echo $row[$key] == '1' ? '<span class="label label-primary">' . t_lang('M_TXT_ACTIVE') . '</span>' : '<span class="label label-danger">' . t_lang('M_TXT_INACTIVE') . '</span>';
                            break;
                        case 'tpl_subject_lang1':
                            echo '<strong>' . $arr_lang_name[0] . '</strong>' . ' ' . $row['tpl_subject'] . '<br/>';
                            echo '<strong>' . $arr_lang_name[1] . '</strong>' . ' ' . $row['tpl_subject_lang1'];
                            break;
                        case 'action':
                            echo '<ul class="actions">';
                            if (checkAdminAddEditDeletePermission(7, '', 'edit')) {
                                echo '<li><a href="?edit=' . $row[$primaryKey] . '&page=' . $page . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>
					<li><a rel="facebox" href=email-template-preview.php?tpl_id=' . $row[$primaryKey] . ' title="' . t_lang('M_TXT_PREVIEW') . '"><i class="ion-eye icon"></i></a></li>';
                            }
                            if ($row['tpl_status'] == 1) {
                                echo '<li><a title="' . t_lang('M_TXT_SEND_MAIL_OFF') . '"  href="?id=' . $row[$primaryKey] . '&status=off"><i class="ion-android-checkbox-blank icon"></i></a></li>';
                            }
                            if ($row['tpl_status'] == 0) {
                                echo '<li><a title="' . t_lang('M_TXT_SEND_MAIL_ON') . '"  href="?id=' . $row[$primaryKey] . '&status=on"><i class="ion-android-checkbox icon"></i></a></li>';
                            }
                            echo '</ul>';
                            break;
                        default:
                            echo $row[$key];
                    }
                    echo '</td>';
                }
                echo '</tr>';
            }
            if ($db->total_records($rs_listing) == 0) {
                echo '<tr><td colspan="' . count($arr_listing_fields) . '">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
            }
            ?>
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
<?php
require_once './footer.php';
