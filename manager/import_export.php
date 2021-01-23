<?php
require_once './application-top.php';
checkAdminPermission(1);
require_once '../includes/navigation-functions.php';
require_once './header.php';
$arr_bread = array(
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    '' => t_lang('M_TXT_IMPORT_EXPORT_DEALS')
);
$get = getQueryStringData();
$post = getPostedData();
if (empty($get))
    $get['req'] = 'export';
if (!empty($post)) {
    require_once dirname(__DIR__) . '/site-classes/import_export.cls.php';
    $ie = new ImportExport();
    if ($get['req'] == 'export') {
        $ie->export(intval($post['batch_from']), intval($post['batch_to']), $post['export_type']);
    } else {
        $res = $ie->upload();
        if ($res === true) {
            $res = $ie->validateCsv($post['import_type']);
        }
        if ($res === true && isset($post['validate']) && $post['validate'] == true) {
            $msg->addMsg(t_lang('M_TXT_DATA_IS_VALID._YOU_CAN_PROCEED_WITH_THE_UPLOAD.'));
        } else if (is_array($res) && !empty($res)) {
            foreach ($res AS $err) {
                $msg->addError($err);
            }
        } else {
            if ($res == true) {
                $action_type = 0;
                if (isset($post['on_duplicate'])) {
                    $action_type = intval($post['on_duplicate']);
                }
                if ($post['import_type'] == 'location_capacity') {
                    $res = $ie->logEntriesFromCSVLocations($action_type, $post['import_type']);
                } else {
                    $res = $ie->logEntriesFromCSV($action_type, $post['import_type']);
                }
                if (!is_array($res) && intval($res) > 0) {
                    $msg->addMsg($res . ' ' . t_lang('M_TXT_RECORDS_INSERTED_OR_UPDATED_SUCCESSFULLY.'));
                } else if (is_array($res)) {
                    if (isset($res['skipped']) && $res['skipped'] > 0) {
                        $msg->addMsg($res['skipped'] . ' ' . t_lang('M_TXT_RECORDS_SKIPPED_SUCCESSFULLY.'));
                    }
                    if (isset($res['insert_update']) && $res['insert_update'] > 0) {
                        $msg->addMsg($res['insert_update'] . ' ' . t_lang('M_TXT_RECORDS_INSERTED_OR_UPDATED_SUCCESSFULLY.'));
                    }
                }
            } else {
                $msg->addError(t_lang('M_TXT_ERROR_UPLOADING_FILE'));
            }
        }
    }
}
if ($get['req'] == 'import') {
    $ie_form = new Form('_import_form', '_import_form');
    $ie_form->setValidatorJsObjectName('_import_form');
    $ie_form->setJsErrorDisplay('afterfield');
    $ie_form->setTableProperties('class="tbl_form" width="100%"');
    $select = array(
        'normal_deal' => t_lang('M_TXT_NORMAL_DEALS'),
        'digital_product' => t_lang('M_TXT_DIGITAL_PRODUCTS'),
        'physical_product' => t_lang('M_TXT_PHYSICAL_PRODUCTS'),
        'location_capacity' => t_lang('M_TXT_LOCATION_CAPACITY'),
    );
    $ie_form->addSelectBox(t_lang('M_TXT_IMPORT'), 'import_type', $select, array('normal_deal'));
    $ie_form->addFileUpload('CSV ' . t_lang('M_TXT_FILE'), 'import_file', '', ' accept=".csv" ');
    $select = array(
        0 => 'Skip Record',
        1 => 'Insert with New ID',
        2 => 'Update/Override Record',
    );
    $ie_form->addSelectBox(t_lang('M_TXT_ON_DUPLICATE_ID'), 'on_duplicate', $select, array(0));
    $ie_form->addCheckBox(t_lang('M_TXT_VALIDATE_RECORDS_WITHOUT_OVERRIDING_OR_INSERTING'), 'validate', true, '', ' title="' . t_lang('M_TXT_IF_THIS_OPTION_IS_CHECKED_DATA_NO_INSERTION_OR_UPDATE_WILL_TAKE_PLACE') . '"  onchange="updateSubmitText()" ', true);
    $ie_form->addSubmitButton('', 'submit', t_lang('M_TXT_UPLOAD'), 'sbmtbtn', ' onclick="requestPopup(this,\'' . t_lang('M_TXT_PLEASE_CONFIRM_YOUR_ACTION') . '\',1)" ');
    $help = '
	<h6>CSV Column Possible Values</h6>
	<ol>
		<li>For Normal Deals:
			<ul>
				<li>deal_type = 0</li>
				<li>deal_sub_type = 0</li>
			</ul>
		</li>
		<li>' . t_lang('M_TXT_FOR_PHYSICAL_PRODUCTS') . ':
			<ul>
				<li>deal_type = 1</li>
				<li>deal_sub_type = 0</li>
			</ul>
		</li>
		<li>For Digital Products:
			<ul>
				<li>deal_type = 1</li>
				<li>deal_sub_type = 1</li>
			</ul>
		</li>
		<li>deal_status:
			<ul>
				<li>0 for Upcoming</li>
				<li>1 for Active</li>
				<li>2 for Expired</li>
				<li>3 for Cancelled</li>
			</ul>
		</li>
		<li>deal_complete:
			<ul>
				<li>0 for Incomplete Deals</li>
				<li>1 for Complete Deals</li>
			</ul>
		</li>
	</ol>
	
	';
    $ie_form->addHTML($help, '_note_special', '', true);
} else {
    //deals and products
    $ie_form = new Form('_export_form', '_export_form');
    $ie_form->setValidatorJsObjectName('_export_form');
    $ie_form->setJsErrorDisplay('afterfield');
    $ie_form->setTableProperties('class="tbl_form" width="100%"');
    $select = array(
        'category' => t_lang('M_TXT_CATEGORIES'),
        'city' => t_lang('M_TXT_CITIES'),
        'normal_deal' => t_lang('M_TXT_NORMAL_DEALS'),
        'digital_product' => t_lang('M_TXT_DIGITAL_PRODUCTS'),
        'merchant' => t_lang('M_TXT_MERCHANT') . '/' . t_lang('M_TXT_COMPANIES'),
        'physical_product' => t_lang('M_TXT_PHYSICAL_PRODUCTS'),
        'tax' => t_lang('M_TXT_TAXES'),
        'deal_location_capacity' => t_lang('M_TXT_LOCATION_CAPACITY'),
    );
    $ie_form->addSelectBox(t_lang('M_TXT_EXPORT_TYPE'), 'export_type', $select, array('normal_deal'));
    $ie_form->addIntegerField(t_lang('M_TXT_BATCH_FROM'), 'batch_from', '0');
    $ie_form->addIntegerField(t_lang('M_TXT_BATCH_UP_TO'), 'batch_to', '0');
    $ie_form->addSubmitButton('', 'Submit', t_lang('M_TXT_EXPORT'));
}
?>
<ul class="nav-left-ul">
    <li><a href="import_export.php?req=import" class="<?php echo (('import' == $get['req']) ? 'selected' : '') ?>"><?php echo t_lang('M_TXT_IMPORT_DEALS') ?></a></li>
    <li><a href="import_export.php" class="<?php echo (('export' == $get['req']) ? 'selected' : '') ?>"><?php echo t_lang('M_TXT_EXPORT_DEALS') ?></a></li>
</ul>
</div></td>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_' . strtoupper($get['req']) . '_TOOL'); ?></div>
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?>
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide();return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="redtext"><?php echo stripcslashes($msg->display()); ?> </div>
                    <br/>
                    <br/>
                <?php } if (isset($_SESSION['msgs'][0])) { ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
    <div class="box"> 
        <div class="contents">
            <?php if ($get['req'] == 'import') { ?>
                <div class="content"><?php echo $ie_form->getFormHtml(); ?></div>
            <?php } else { ?>
                <div class="content">
                    <?php echo $ie_form->getFormHtml(); ?>
                    <div class="clear"></div>
                </div>
            <?php } ?>
        </div>
    </div>
</td>
<script type="text/javascript">
    function updateSubmitText() {
        if (jQuery('input[name=validate]').is(':checked')) {
            jQuery('#sbmtbtn').val('<?php echo t_lang('M_TXT_VALIDATE') ?>');
        } else {
            jQuery('#sbmtbtn').val('<?php echo t_lang('M_TXT_UPLOAD') ?>');
        }
    }
</script>
<?php
require_once './footer.php';
