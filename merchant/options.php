<?php
require_once '../application-top.php';
$post = getPostedData();
$mainTableName = 'tbl_options';
$primaryKey = 'option_id';
$colPrefix = '';
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$pagesize = 15;
$option_types = array('select' => 'SelectBox');
$Src_frm = new Form('Src_frm', 'Src_frm');
$Src_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$Src_frm->setFieldsPerRow(2);
$Src_frm->captionInSameCell(true);
$Src_frm->addTextBox(t_lang('M_FRM_KEYWORD'), 'keyword', '', '', '');
$Src_frm->addHiddenField('', 'mode', 'search');
$fld1 = $Src_frm->addButton('&nbsp;', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="inputbuttons" onclick=location.href="options.php"');
$fld = $Src_frm->addSubmitButton('&nbsp;', 'btn_search', t_lang('M_TXT_SEARCH'), '', ' class="inputbuttons"')->attachField($fld1);
if ($_SESSION['lang_fld_prefix'] == '_lang1') {
    $get_option_name = 'IF(CHAR_LENGTH(option_name_lang1),option_name_lang1,option_name) as option_name';
} else {
    $get_option_name = 'option_name';
}
$rsc = $db->query("SELECT option_id, " . $get_option_name . " FROM " . $mainTableName . " ORDER BY option_id asc");
$frm = new Form('frmOption', 'frmOptions');
$frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$frm->setFieldsPerRow(1);
$frm->captionInSameCell(false);
$frm->setJsErrorDisplay('afterfield');
$frm->addRequiredField('M_TXT_OPTION_NAME', 'option_name', '', 'option_name', '');
$frm->addSelectBox('M_TXT_TYPE', 'option_type', $option_types, '', '', '', '');
$frm->addHiddenField('', 'option_id', $_REQUEST['edit']);
$option_value_row = 1;
$option_values = '<table id="option-value" class="tbl_form" width="100%" border="0" cellspacing="0" cellpadding="0">';
$option_values .= '<tr><th><b>' . t_lang('M_TXT_OPTION_VALUE_NAME') . ':<span class="spn_must_field">*</span></b></th><th>' . t_lang('M_TXT_SORT_ORDER') . '</th><th>' . t_lang('M_TXT_ACTION') . '</th></tr>';
$option_values .= '<tfoot><tr>
                        <td colspan="2"></td>
                        <td><a onclick="addOptionValue()">' . t_lang('M_TXT_ADD_NEW_OPTION_VALUE') . '</a></td>
                </tr></tfoot>';
$option_values .= '</table>';
$frm->addHTML('', '', $option_values, true);
$fld1 = $frm->addSubmitButton('', 'btn_submit', 'SUBMIT', 'btn_submit', '');
$fld = $frm->addButton('', 'btn_submit_cancel', t_lang('M_TXT_CANCEL'), '', ' class="inputbuttons" onclick=location.href="options.php"')->attachField($fld1);
$frm->setAction('?page=' . $page . '&add=new');
updateFormLang($frm);
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['btn_search']) && isset($_POST['btn_submit'])) {
    $post = getPostedData();
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error) {
            $msg->addError($error);
        }
    } else if (count($post['option_value_name']) <= 0) {
        $msg->addError(t_lang('M_TXT_OPTION_VALUES_REQUIRED'));
        fillForm($frm, $post);
    } else {
        if (isset($post['option_name']) && ($_SESSION['lang_fld_prefix'] == '_lang1')) {
            $post['option_name' . $_SESSION['lang_fld_prefix']] = $post['option_name'];
            unset($post['option_name']);
        }
        $record = new TableRecord($mainTableName);
        $record->assignValues($post);
        if ($post[$primaryKey] == '') {
            $success = $record->addNew();
        }
        if ($success) {
            $option_id = ($post[$primaryKey] > 0) ? $post[$primaryKey] : $record->getId();
            foreach ($post['option_value_name'] as $key => $value) {
                if ($value == '') {
                    continue;
                }
                $record = new TableRecord('tbl_option_values');
                $values_arr = array('option_id' => $option_id, 'name' => $value, 'sort_order' => $post['option_value_sort_order'][$key]);
                $record->assignValues($values_arr);
                $success = $record->addNew();
            }
            $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
            redirectUser('options.php');
        } else {
            $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
            fillForm($frm, $post);
        }
    }
}
$srch = new SearchBase('tbl_options', 'op');
$srch->addOrder('op.option_name');
$srch->addFld('op.*');
if ($post['mode'] == 'search') {
    if ($post['keyword'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('op.option_name' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
    }
    $Src_frm->fill($post);
}
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
$pagestring .= createHiddenFormFromPost('frmPaging', '?', array('page'), array('page' => ''));
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
	' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$arr_listing_fields = array(
    'listserial' => t_lang('M_TXT_SR_NO'),
    'option_name' . $_SESSION['lang_fld_prefix'] => t_lang('M_TXT_OPTION_NAME'),
    'status' => t_lang('M_TXT_STATUS'),
);
require_once './header.php';
$arr_bread = array('company-deals.php' => t_lang('M_TXT_DEALS_PRODUCTS'), '' => t_lang('M_TXT_OPTIONS'));
?>
<script type="text/javascript">
    txtoptiondel = "<?php echo t_lang('M_TXT_OPTION_DELETION_NOT_ALLOWED'); ?>";
    txtsuredel = "<?php echo t_lang('M_TXT_ARE_YOU_SURE_TO_DELETE'); ?>";
    var option_value_row = <?php echo $option_value_row; ?>;
    var remove = "<?php echo t_lang('M_TXT_REMOVE'); ?>";
    function addOptionValue() {
        html = '<tbody id="option-value-row' + option_value_row + '">';
        html += '  <tr>';
        html += '    <td><input type="text" name="option_value_name[]" value="" />';
        html += '    </td>';
        html += '    <td><input type="text" name="option_value_sort_order[]" value="" size="1" /></td>';
        html += '    <td><a onclick="$(\'#option-value-row' + option_value_row + '\').remove();">' + remove + '</a></td>';
        html += '  </tr>';
        html += '</tbody>';
        $('#option-value tfoot').before(html);
        option_value_row++;
    }
</script>
</div></td>
<td class="right-portion"><?php echo getMerchantBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_OPTIONS'); ?> 
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
    <?php if ($_REQUEST['add'] == 'new') { ?>
        <div class="box"><div class="title"> <?php echo t_lang('M_TXT_OPTIONS'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
    <?php } else { ?>
        <div class="box searchform_filter"><div class="title"> <?php echo t_lang('M_TXT_OPTIONS'); ?> </div><div class="content togglewrap" style="display:none;">	<?php echo $Src_frm->getFormHtml(); ?></div></div>
        <table class="tbl_data" width="100%" id="category_listing1">
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
                if ($listserial % 2 == 0) {
                    $even = 'even';
                } else {
                    $even = '';
                }
                echo '<tr class=" ' . $even . ' " ' . (($row[$colPrefix . 'active'] == '0') ? ' class="inactive"' : '') . ' id = ' . $row['cat_id'] . '>';
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td>';
                    switch ($key) {
                        case 'listserial':
                            echo $listserial;
                            break;
                        case 'option_name_lang1':
                            $level = strlen($row['cat_code']) / 5 - 1;
                            for ($i = 0; $i < $level; $i++)
                                echo '&mdash;&raquo;&nbsp;';
                            echo '<strong>' . $arr_lang_name[0] . '</strong>' . ' ' . $row['option_name'] . '<br/>';
                            echo '<strong>' . $arr_lang_name[1] . '</strong>' . ' ' . $row['option_name_lang1'];
                            break;
                        case 'status':
                            echo ($row['is_deleted'] == 1) ? t_lang('M_TXT_MARKED_AS_DELETED') : t_lang('M_TXT_NOT_DELETED');
                            break;
                        default:
                            echo $row[$key];
                            break;
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
        <?php } ?>
    <?php } ?>
</td>
<?php
require_once './footer.php';

