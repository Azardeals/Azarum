<?php
require_once './application-top.php';
require_once '../includes/navigation-functions.php';
loadModels(['TaxClassModel']);
checkAdminPermission(4);
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
/**
 * TAX CLASS SEARCH FORM 
 * */
$taxObj = new TaxClass();
$srchForm = TaxClass::getSearchForm();
/**
 * TAX CLASS DELETE CONDITION
 * */
if (is_numeric($_REQUEST['delete'])) {
    if (checkAdminAddEditDeletePermission(4, '', 'delete')) {
        $taxObj->deleteTaxClass($_REQUEST['delete']);
        $msg->addMsg(t_lang('M_TXT_RECORD_DELETED'));
        redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
/**
 * TAX RATE EDIT CONDITION
 * */
$str = '';
if (is_numeric($_REQUEST['edit'])) {
    $srch = TaxClass::getSearchTaxRulesObject();
    $srch->addCondition('taxrule_taxclass_id', '=', $_REQUEST['edit']);
    $rs = $srch->getResultSet();
    $count = 0;
    $taxrule_ids = [];
    while ($row = $db->fetch($rs)) {
        $srch = new SearchBase('tbl_tax_rates');
        $srch->addCondition('taxrate_active', '=', '1');
        $srch->addOrder('taxrate_name', 'asc');
        $srch->addMultipleFields(['taxrate_id', 'taxrate_name']);
        $rs1 = $srch->getResultSet();
        $arr_states = $db->fetch_all_assoc($rs1);
        $fld = new FormField('select', 'data[' . $count . '][taxrule_taxrate_id]', 'taxrule_taxrate_id');
        $fld->options = $arr_states;
        $fld->extra = 'class="taxrule_taxrate_id" onchange= "getsamevalue(this);"';
        $fld->selectCaption = t_lang('M_TXT_SELECT');
        $fld->value = $row['taxrule_taxrate_id'];
        //"2" => "Billing Address",
        $arrayBasedOn = ["1" => "Store Address", "3" => "Shipping Address"];
        $fld1 = new FormField('select', 'data[' . $count . '][taxrule_tax_based_on]', 'taxrule_tax_based_on');
        $fld1->options = $arrayBasedOn;
        $fld1->selectCaption = t_lang('M_TXT_SELECT');
        $fld1->value = $row['taxrule_tax_based_on'];
        $str .= '<tr id="tax-rule-row' . $row['taxrule_id'] . '"><td>' . $fld->getHTML() . '</td><td>' . $fld1->getHTML() . '</td><td class="left"><a class="button small" onclick="deleteTaxRuleRecord(' . $row['taxrule_id'] . ',' . $count . ')">' . t_lang('M_TXT_REMOVE') . '</a><input type="hidden" name="data[' . $count . '][taxrule_id]" value="' . $row['taxrule_id'] . '"></td>';
        $count++;
        $taxrule_ids[] = $row['taxrule_id'];
    }
}
/**
 * TAX CLASS UPDATE FORM FILL UP 
 * */
$frm = TaxClass::getForm();
$html = '<table class="" id="tax-rule" width="100%" cellspacing="0" cellpadding="0" border="0">
	<thead>
		<tr>
		  <th width="40%" style="background:none; background-color:#D8D8D8 !important">' . t_lang('M_TXT_TAX_RATE') . ':</th>
		  <th width="40%" style="background:none; background-color:#D8D8D8 !important">' . t_lang('M_TXT_BASED_ON') . ':</th>
		  <th width="20%"><span><a class="button small" onclick="addRule(0,0);">' . t_lang('M_TXT_ADD_RULE') . '</a></span></th>
		</tr>
	</thead>
	<tbody>' . $str . '</tbody>
    </table>';
$frm->addHTML('', '', $html, true);
$frm->setJsErrorDisplay('afterfield');
$frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SUBMIT'), '', 'class="medium"');
updateFormLang($frm);
/**
 * TAX CLASS EDIT CONDITION
 * */
if (is_numeric($_REQUEST['edit'])) {
    if (checkAdminAddEditDeletePermission(4, '', 'edit')) {
        $record = new TableRecord('tbl_tax_classes');
        if (!$record->loadFromDb('taxclass_id=' . $_REQUEST['edit'], true)) {
            $msg->addError($record->getError());
        } else {
            $arr = $record->getFlds();
            $frm->addHiddenField('', 'taxclass_id', $_REQUEST['edit']);
            fillForm($frm, $arr);
            $msg->addMsg(t_lang('M_TXT_Edit_THE_VALUES_AND_UPDATE'));
        }
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
/**
 * TAX CLASS POSTED FORM
 * */
if (isset($_POST['btn_submit'])) {
    $post = getPostedData();
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error) {
            $msg->addError($error);
        }
    } else {
        $arrLangIndependentFlds = array('taxclass_id', 'taxclass_name', 'taxclass_description', 'taxclass_active', 'btn_submit');
        $taxclassId = $taxObj->addUpdateRecord($arrLangIndependentFlds, $post);
        if ($taxclassId) {
            $record1 = new TableRecord('tbl_tax_rules');
            foreach ($post['data'] as $key1 => $value) {
                if ($value['taxrule_taxrate_id'] != "") {
                    $data[$key1]['taxrule_taxclass_id'] = $taxclassId;
                    $data[$key1]['taxrule_taxrate_id'] = $value['taxrule_taxrate_id'];
                    $data[$key1]['taxrule_tax_based_on'] = $value['taxrule_tax_based_on'];
                    $record1->assignValues($data[$key1]);
                    if (intval($value['taxrule_id']) > 0) {
                        $record1->update(array('smt' => 'taxrule_id = ?', 'vals' => array($value['taxrule_id']), 'execute_mysql_functions' => false));
                    } else {
                        $record1->addNew();
                    }
                }
            }
            $msg->addMsg(t_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
            redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
        } else {
            $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE'));
            fillForm($frm, $arr);
        }
    }
}
/**
 * TAX CLASS LISTING
 * */
$srch = TaxClass::getSearchObject();
if ($_REQUEST['status'] == 'active') {
    $srch->addCondition('taxclass_active', '=', 1);
} else if ($_REQUEST['status'] == 'deactive') {
    $srch->addCondition('taxclass_active', '=', 0);
} else {
    $srch->addCondition('taxclass_active', '=', 1);
}
if ($_POST['name']) {
    $srch->addCondition('taxclass_name', 'LIKE', '%' . $_POST['name'] . '%');
}
$srch->joinTable('tbl_tax_rules', 'LEFT JOIN', 'tc.taxclass_id=tr.taxrule_taxclass_id', 'tr');
$srch->joinTable('tbl_tax_rates', 'LEFT JOIN', 'trate.taxrate_id=tr.taxrule_taxrate_id', 'trate');
$srch->addMultipleFields(array('tc.*', 'tr.*', "GROUP_CONCAT(trate.taxrate_name SEPARATOR ',') as taxrate_name"));
$srch->addGroupBy('taxclass_id');
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$pagesize = 30;
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$srch->addOrder('tc.taxclass_name');
$rs_listing = $srch->getResultSet();
/**
 * TAX CLASS PAGINATION
 * */
$pagestring = '';
$pages = $srch->pages();
$pagestring .= createHiddenFormFromPost('frmPaging', '?', array('page', 'status', 'name'), array('page' => '', 'status' => $_REQUEST['affiliate'], 'name' => $_REQUEST['name']));
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
	' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$arr_listing_fields = array(
    'taxclass_name' => t_lang('M_FRM_NAME'),
    'taxclass_description' => t_lang('M_TXT_DESCRIPTION'),
    'taxrate_name' => t_lang('M_TXT_TAX_RATE_TYPE'),
    'action' => t_lang('M_TXT_ACTION')
);
require_once './header.php';
$arr_bread = array(
    'index.php' => '<img alt="Home" src="images/home-icon.png">',
    'deals.php' => t_lang('M_TXT_DEALS') . '/' . t_lang('M_TXT_PRODUCTS'),
    '' => t_lang('M_TXT_TAX_CLASS')
);
?>
<ul class="nav-left-ul">
    <li>    <a <?php if ($_REQUEST['status'] == 'active') echo 'class="selected"'; ?> href="tax-class.php?status=active"><?php echo t_lang('M_TXT_ACTIVE_TAX_CLASS_LISTING'); ?></a></li>
    <li>    <a <?php if ($_REQUEST['status'] == 'deactive') echo 'class="selected"'; ?> href="tax-class.php?status=deactive"><?php echo t_lang('M_TXT_INACTIVE_TAX_CLASS_LISTING'); ?></a></li>
</ul>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_TAX_CLASS'); ?> 
            <?php if (checkAdminAddEditDeletePermission(4, '', 'add')) { ?>
                <ul class="actions right">
                    <li class="droplink">
                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                        <div class="dropwrap">
                            <ul class="linksvertical">
                                <li>										 
                                    <a href="?page=<?php echo $page; ?>&add=new" ><?php echo t_lang('M_TXT_ADD_NEW'); ?></a>
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
    if (is_numeric($_REQUEST['edit']) || $_REQUEST['add'] == 'new') {
        ?>
        <?php if ((checkAdminAddEditDeletePermission(4, '', 'add')) || (checkAdminAddEditDeletePermission(4, '', 'edit'))) { ?>
            <div class="box"><div class="title"> <?php echo t_lang('M_TXT_TAX_CLASS'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
            <?php
        } else {
            die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        }
    } else {
        ?>
        <div class="box searchform_filter"><div class="title"> <?php echo t_lang('M_TXT_TAX_CLASS'); ?> </div><div class="content togglewrap" style="display:none;">		<?php echo $srchForm->getFormHtml(); ?>
            </div></div>
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
            while ($row = $db->fetch($rs_listing)) {
                echo '<tr' . (($row['tax_active'] == 0) ? ' class="inactive"' : '') . '>';
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td ' . (($key == action) ? 'width="20%"' : '') . '>';
                    switch ($key) {
                        case 'taxrate_name':
                            echo $row['taxrate_name'] . '<br/>';
                            break;
                        case 'taxrate_tax_rate':
                            echo $row['taxrate_tax_rate'] . ' % <br/>';
                            break;
                        case 'action':
                            echo '<ul class="actions">';
                            if ($_REQUEST['status'] == 'active') {
                                if (checkAdminAddEditDeletePermission(4, '', 'edit')) {
                                    echo '<li><a href="?edit=' . $row['taxclass_id'] . '&page=' . $page . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                                }
                                if (checkAdminAddEditDeletePermission(4, '', 'delete')) {
                                    echo '<li><a href="?delete=' . $row['taxclass_id'] . '&page=' . $page . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_DELETE') . '" onclick="requestPopup(this,\'' . t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD') . '\',1);"><i class="ion-android-delete icon"></i></a></li>';
                                }
                            } else {
                                if (checkAdminAddEditDeletePermission(4, '', 'edit')) {
                                    echo '<li><a href="?edit=' . $row['taxclass_id'] . '&page=' . $page . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                                }
                                if (checkAdminAddEditDeletePermission(4, '', 'delete')) {
                                    echo '<li><a href="?delete=' . $row['taxclass_id'] . '&page=' . $page . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_DELETE') . '" onclick="requestPopup(this,\'' . t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD') . '\',1);"><i class="ion-android-delete icon"></i></a></li>';
                                }
                            }
                            echo '</ul>';
                            break;
                        default:
                            echo $row[$key];
                            break;
                    }
                    echo '</td>';
                }
                echo '</tr>';
            }
            if ($db->total_records($rs_listing) == 0)
                echo '<tr><td colspan="' . count($arr_listing_fields) . '">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
            ?>
        </table>
    <?php } ?>
    <?php if (!isset($_GET['edit']) && $_GET['add'] != 'new' && ($srch->pages() > 1)) { ?>
        <div class="footinfo">
            <aside class="grid_1">
                <?php echo $pagestring; ?>	 
            </aside>  
            <aside class="grid_2"><span class="info"><?php echo $pageStringContent; ?></span></aside>
        </div>
    <?php } ?> 
</td>
<script>
    var deleteCityMsg = "<?php echo addslashes(t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD')); ?>";
    var remove = "<?php echo addslashes(t_lang('M_TXT_REMOVE')); ?>";
    var labelTaxRate = "<?php echo addslashes(t_lang('M_TXT_Please_change_tax_rate')); ?>";
</script>
<?php require_once './footer.php'; ?>
