<?php

loadModels(['MyAppModel']);

class TaxRate extends MyAppModel
{

    const DB_TBL = 'tbl_tax_rates';
    const DB_TBL_PREFIX = 'taxrate_';
    const DB_TBL_PRIMARY_KEY = 'taxrate_id';

    public function __construct()
    {
        global $db;
    }

    public static function getSearchObject($langId = 0)
    {
        $srch = new SearchBase(static::DB_TBL, 'tr');
        return $srch;
    }

    public function addUpdateRecord($arrLangIndependentFlds, $post)
    {
        $record = new TableRecord(static::DB_TBL);
        assignValuesToTableRecord($record, $arrLangIndependentFlds, $post);
        if ((checkAdminAddEditDeletePermission(4, '', 'edit'))) {
            if (((int) $post['taxrate_id']) > 0 || $post['taxrate_id'] == "0")
                $success = $record->update('taxrate_id' . '=' . $post['taxrate_id']);
        }
        if ((checkAdminAddEditDeletePermission(4, '', 'add'))) {
            if ($post['taxrate_id'] == '') {
                $success = $record->addNew();
            }
        }
        return $success;
    }

    public function canTaxRateDelete($rateId)
    {
        $srch = new SearchBase('tbl_tax_rules', 'tr');
        $srch->addCondition('taxrule_taxrate_id', '=', $rateId);
        $srch->addFld('taxrule_id');
        $rs = $srch->getResultSet();
        $total_record = $srch->recordCount();
        if ($total_record > 0) {
            return false;
        }
        return true;
    }

    public function deleteTaxRate($rateId)
    {
        global $db;
        $db->deleteRecords(static::DB_TBL, ['smt' => static::DB_TBL_PREFIX . 'id = ?', 'vals' => [$rateId]]);
        return true;
    }

    /**
     * TAX RATE SEARCH FORM 
     * */
    public static function getSearchForm()
    {
        $srchForm = new Form('Src_frm', 'Src_frm');
        $srchForm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
        $srchForm->setFieldsPerRow(3);
        $srchForm->captionInSameCell(true);
        $srchForm->addTextBox(t_lang('M_TXT_TAX_NAME'), 'zone', $_REQUEST['zone'], '', '');
        $srchForm->addHiddenField('', 'mode', 'search');
        $srchForm->addHiddenField('', 'status', $_REQUEST['status']);
        $fld1 = $srchForm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="medium" onclick=location.href="tax-rate.php"');
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
        $fld = $frm->addRequiredField(t_lang('M_TXT_TAX_NAME'), 'taxrate_name', '', 'tax_name');
        $fld->setUnique('tbl_tax_rates', 'taxrate_name', 'taxrate_id', 'taxrate_id', 'taxrate_id');
        $frm->addFloatField(t_lang('M_FRM_TAX_RATE'), 'taxrate_tax_rate', '', 'tax_rate', 'placeholder=' . t_lang('M_FRM_0.00'))->requirements()->setRequired();
        $srch = new SearchBase('tbl_tax_geo_zones', 'tgz');
        $srch->addCondition('tgz.geozone_active', '=', '1');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addFld('geozone_id');
        $srch->addFld('geozone_name');
        $srch->addOrder('geozone_name');
        $rs = $srch->getResultSet();
        $arr_options = $db->fetch_all_assoc($rs);
        $frm->addSelectBox(t_lang('M_FRM_ZONE'), 'taxrate_geozone_id', $arr_options, '', '', t_lang('M_TXT_SELECT'), 'taxrate_geozone_id')->requirements()->setRequired();
        $status = [1 => t_lang('M_TXT_ACTIVE'), 0 => t_lang('M_TXT_INACTIVE')];
        $frm->addSelectBox(t_lang('M_FRM_STATUS'), 'taxrate_active', $status, '');
        $frm->setJsErrorDisplay('afterfield');
        $frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SUBMIT'), '', 'class="medium"');
        return $frm;
    }

}
