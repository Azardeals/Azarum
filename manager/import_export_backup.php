<?php
require_once './application-top.php';
checkAdminPermission(1);
require_once '../includes/navigation-functions.php';
require_once './header.php';
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    '' => t_lang('M_TXT_IMPORT_EXPORT_DEALS')
];
$get = getQueryStringData();
$post = getPostedData();
if (empty($get))
    $get['req'] = 'export';
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
    <div class="box">
        <div class="contents">
            <?php
            if (!empty($post)) {
                require_once dirname(__DIR__) . '/site-classes/import_export.cls.php';
                $ie = new ImportExport();
                if ($get['req'] == 'export') {
                    $ie->export(intval($post['batch_from']), intval($post['batch_to']), $post['export_type']);
                } else {
                    $res = $ie->upload();
                    if ($res === true) {
                        $res = $ie->validateCsv();
                    }
                    if ($res === true && isset($post['validate']) && $post['validate'] == true) {
                        echo 'Data is valid. You can proceed with the upload.';
                    } else if (is_array($res) && !empty($res)) {
                        echo '<ul class="error errorlist width-100">';
                        foreach ($res AS $err) {
                            echo '<li>' . $err . '</li>';
                        }
                        echo '</ul>';
                    } else {
                        if ($res == true) {
                            if (isset($post['truncate']) && $post['truncate'] == true) {
                                $ie->delete_all();
                            }
                            $res = $ie->logEntriesFromCSV();
                            if (intval($res) > 0) {
                                echo $res . ' records inserted successfully.';
                            }
                        } else {
                            echo '<ul class="error errorlist width-100">';
                            echo '<li>Error uploading file.</li>';
                            echo '</ul>';
                        }
                    }
                }
            }
            if ($get['req'] == 'import') {
                $ie_form = new Form('import_export_form', 'import_export_form');
                $ie_form->setValidatorJsObjectName('import_export_form');
                $ie_form->setJsErrorDisplay('afterfield');
                $ie_form->setTableProperties('class="tbl_form" width="100%"');
                $ie_form->addFileUpload('Deals CSV', 'import_file', '', ' accept=".csv" ');
                $ie_form->addCheckBox(t_lang('M_TXT_VALIDATE_RECORDS_WITHOUT_OVERRIDING_OR_INSERTING'), 'validate', true, '', ' title="' . t_lang('M_TXT_IF_THIS_OPTION_IS_CHECKED_DATA_NO_INSERTION_OR_UPDATE_WILL_TAKE_PLACE') . '" ', true);
                $ie_form->addCheckBox(t_lang('M_TXT_TRUNCATE_BEFORE_INSERTION'), 'truncate', true, '', ' title="' . t_lang('M_TXT_DELETE_ALL_TABLE_RECORDS') . '" ');
                $ie_form->addHTML(ucfirst(strtolower(t_lang('M_TXT_IF_DEAL_ID_IS_ALREADY_PRESENT_IN_TABLE_CSV_COLUMN_VALUES_WILL_BE_SKIPPED'))), '_note', '', true);
                $ie_form->addSubmitButton('', 'submit', t_lang('M_TXT_UPLOAD'), '', ' onclick="requestPopup(this,\'' . t_lang('M_TXT_PLEASE_CONFIRM_YOUR_ACTION') . '\',0)" ');
                ?>
                <div class="content"><?php echo $ie_form->getFormHtml(); ?></div>
                <?php
            } else {
                //deals and products
                $ie_form = new Form('import_export_form', 'import_export_form');
                $ie_form->setValidatorJsObjectName('import_export_form');
                $ie_form->setJsErrorDisplay('afterfield');
                $ie_form->setTableProperties('class="tbl_form" width="100%"');
                $ie_form->addHTML('<h4>' . t_lang('M_TXT_EXPORT_DEALS') . '/' . t_lang('M_TXT_PRODUCTS') . '</h4>', '_exp_heading', '', true);
                $ie_form->addIntegerField(t_lang('M_TXT_BATCH_FROM'), 'batch_from', '0');
                $ie_form->addIntegerField(t_lang('M_TXT_BATCH_UP_TO'), 'batch_to', '0');
                $ie_form->addHiddenField('', 'export_type', 'deal');
                $ie_form->addSubmitButton('', 'Submit', t_lang('M_TXT_EXPORT'));
                //categories
                $ca_form = new Form('import_export_form_cat', 'import_export_form_cat');
                $ca_form->setValidatorJsObjectName('import_export_form_cat');
                $ca_form->setJsErrorDisplay('afterfield');
                $ca_form->setTableProperties('class="tbl_form" width="100%"');
                $ca_form->addHTML('<h4>' . t_lang('M_TXT_CATEGORIES') . '</h4>', '_exp_heading', '', true);
                $ca_form->addIntegerField(t_lang('M_TXT_BATCH_FROM'), 'batch_from', '0', '', ' disabled');
                $ca_form->addIntegerField(t_lang('M_TXT_BATCH_UP_TO'), 'batch_to', '0', '', ' disabled');
                $ca_form->addHiddenField('', 'export_type', 'category');
                $ca_form->addSubmitButton('', 'Submit', t_lang('M_TXT_EXPORT'));
                //cities
                $ci_form = new Form('import_export_form_ci', 'import_export_form_ci');
                $ci_form->setValidatorJsObjectName('import_export_form_ci');
                $ci_form->setJsErrorDisplay('afterfield');
                $ci_form->setTableProperties('class="tbl_form" width="100%"');
                $ci_form->addHTML('<h4>' . t_lang('M_TXT_CITIES') . '</h4>', '_exp_ci', '', true);
                $ci_form->addIntegerField(t_lang('M_TXT_BATCH_FROM'), 'batch_from', '0', '', ' disabled');
                $ci_form->addIntegerField(t_lang('M_TXT_BATCH_UP_TO'), 'batch_to', '0', '', ' disabled');
                $ci_form->addHiddenField('', 'export_type', 'city');
                $ci_form->addSubmitButton('', 'Submit', t_lang('M_TXT_EXPORT'));
                //Tax
                $tax_form = new Form('import_export_form_tax', 'import_export_form_tax');
                $tax_form->setValidatorJsObjectName('import_export_form_tax');
                $tax_form->setJsErrorDisplay('afterfield');
                $tax_form->setTableProperties('class="tbl_form" width="100%"');
                $tax_form->addHTML('<h4>' . t_lang('M_TXT_TAX CLASSES') . '</h4>', '_tax_class', '', true);
                $tax_form->addIntegerField(t_lang('M_TXT_BATCH_FROM'), 'batch_from', '0', '', ' disabled');
                $tax_form->addIntegerField(t_lang('M_TXT_BATCH_UP_TO'), 'batch_to', '0', '', ' disabled');
                $tax_form->addHiddenField('', 'export_type', 'tax');
                $tax_form->addSubmitButton('', 'Submit', t_lang('M_TXT_EXPORT'));
                //Companies
                $com = new Form('import_export_form_company', 'import_export_form_company');
                $com->setValidatorJsObjectName('import_export_form_company');
                $com->setJsErrorDisplay('afterfield');
                $com->setTableProperties('class="tbl_form" width="100%"');
                $com->addHTML('<h4>' . t_lang('M_TXT_MERCHANTS') . '/' . t_lang('M_TXT_COMPANIES') . '</h4>', '_merchants', '', true);
                $com->addIntegerField(t_lang('M_TXT_BATCH_FROM'), 'batch_from', '0', '', ' disabled');
                $com->addIntegerField(t_lang('M_TXT_BATCH_UP_TO'), 'batch_to', '0', '', ' disabled');
                $com->addHiddenField('', 'export_type', 'merchant');
                $com->addSubmitButton('', 'Submit', t_lang('M_TXT_EXPORT'));
                ?>
                <div class="content"><?php echo $ie_form->getFormHtml(); ?><div class="clear"></div></div>
                <div class="content"><?php echo $ca_form->getFormHtml(); ?><div class="clear"></div></div>
                <div class="content"><?php echo $ci_form->getFormHtml(); ?><div class="clear"></div></div>
                <div class="content"><?php echo $com->getFormHtml(); ?><div class="clear"></div></div>
                <div class="content"><?php echo $tax_form->getFormHtml(); ?><div class="clear"></div></div>
                <?php } ?>
        </div>
    </div>
</td>
<?php
require_once '. /footer.php';
