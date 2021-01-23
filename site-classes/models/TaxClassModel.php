<?php

loadModels(array('MyAppModel'));

class TaxClass extends MyAppModel
{

    const DB_TBL = 'tbl_tax_classes';
    const DB_TBL_PREFIX = 'taxclass_';
    const DB_TBL_PRIMARY_KEY = 'taxclass_id';
    const DB_TAX_RULES_TBL = 'tbl_tax_rules';

    public function __construct()
    {
        global $db;
    }

    public static function getSearchObject($langId = 0)
    {
        $srch = new SearchBase(static::DB_TBL, 'tc');
        return $srch;
    }

    public static function getSearchTaxRulesObject($langId = 0)
    {
        $srch = new SearchBase(static::DB_TAX_RULES_TBL);
        return $srch;
    }

    public function deleteTaxClass($classId)
    {
        global $db;
        $db->deleteRecords(static::DB_TBL, ['smt' => static::DB_TBL_PREFIX . 'id = ?', 'vals' => [$classId]]);
        $db->deleteRecords(static::DB_TAX_RULES_TBL, ['smt' => 'taxrule_taxclass_id = ?', 'vals' => [$classId]]);
        return true;
    }

    public function addUpdateRecord($arrLangIndependentFlds, $post)
    {
        $record = new TableRecord(static::DB_TBL);
        $record->setFldValue('taxclass_added_on', 'mysql_func_NOW()', true);
        assignValuesToTableRecord($record, $arrLangIndependentFlds, $post);
        if ((checkAdminAddEditDeletePermission(4, '', 'edit'))) {
            if (((int) $post['taxclass_id']) > 0 || $post['taxclass_id'] == "0")
                $success = $record->update('taxclass_id' . '=' . $post['taxclass_id']);
        }
        if ((checkAdminAddEditDeletePermission(4, '', 'add'))) {
            if ($post['taxclass_id'] == '') {
                $success = $record->addNew();
            }
        }
        $taxclassId = ($post['taxclass_id'] > 0) ? $post['taxclass_id'] : $record->getId();
        return $taxclassId;
    }

    /**
     * TAX CLASS SEARCH FORM 
     * */
    public static function getSearchForm()
    {
        $srchForm = new Form('Src_frm', 'Src_frm');
        $srchForm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
        $srchForm->setFieldsPerRow(3);
        $srchForm->captionInSameCell(true);
        $srchForm->addTextBox(t_lang('M_TXT_TAX_NAME'), 'name', $_REQUEST['name'], '', '');
        $srchForm->addHiddenField('', 'mode', 'search');
        $srchForm->addHiddenField('', 'status', $_REQUEST['status']);
        $fld1 = $srchForm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="medium" onclick=location.href="tax-class.php"');
        $fld = $srchForm->addSubmitButton('', 'search', t_lang('M_TXT_SEARCH'), '', ' class="medium"')->attachField($fld1);
        return $srchForm;
    }

    public static function getForm()
    {
        global $db;
        $frm = new Form('taxrate_frm', 'taxrate_frm');
        $frm->setTableProperties('border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
        $frm->setFieldsPerRow(1);
        $frm->captionInSameCell(false);
        $frm->setAction('?page=' . $page);
        $fld = $frm->addRequiredField(t_lang('M_FRM_TAX_CLASS_NAME'), 'taxclass_name', '', 'taxclass_name');
        $fld->setUnique('tbl_tax_classes', 'taxclass_name', 'taxclass_id', 'taxclass_id', 'taxclass_id');
        $frm->addRequiredField(t_lang('M_TXT_DESCRIPTION'), 'taxclass_description', '', 'taxclass_description');
        $status = [1 => t_lang('M_TXT_ACTIVE'), 0 => t_lang('M_TXT_INACTIVE')];
        $frm->addSelectBox(t_lang('M_FRM_STATUS'), 'taxclass_active', $status, '');
        return $frm;
    }

}
