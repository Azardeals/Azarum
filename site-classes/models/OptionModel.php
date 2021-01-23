<?php

loadModels(['MyAppModel']);

class Option extends MyAppModel
{

    const DB_TBL = 'tbl_options';
    const DB_TBL_PREFIX = 'option_';
    const DB_TBL_PRIMARY_KEY = 'option_id';

    public function __construct()
    {
        global $db;
        $this->db = $db;
    }

    public static function getSearchObject($langId = 0)
    {
        $srch = new SearchBase(static::DB_TBL, 'op');
        return $srch;
    }

    public function deleteRestoreOption($optionId, $action)
    {
        $data = ['is_deleted' => $action];
        $record = new TableRecord(static::DB_TBL);
        $record->assignValues($data);
        $success = $record->update('option_id =' . $optionId);
        return $success;
    }

    public function getOptionValues($optionId)
    {
        $src = new SearchBase('tbl_option_values', 'op_val');
        $src->addCondition('op_val.option_id', '=', $optionId);
        $src->addMultipleFields(['op_val.option_value_id', 'op_val.option_id', 'op_val.name', 'op_val.sort_order']);
        $src->addOrder('op_val.sort_order');
        $src->doNotLimitRecords();
        $result = $src->getResultSet();
        $arrValues = $this->db->fetch_all($result);
        return $arrValues;
    }

    public static function getForm()
    {
        $frm = new Form('frmOption', 'frmOptions');
        $frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
        $frm->setFieldsPerRow(1);
        $frm->captionInSameCell(false);
        $frm->setJsErrorDisplay('afterfield');
        $fld = $frm->addRequiredField('M_TXT_OPTION_NAME', 'option_name', '', 'option_name', '');
        $optionTypes = ['select' => 'Dropdown List'];
        $frm->addSelectBox('M_TXT_TYPE', 'option_type', $optionTypes, '', 'disabled', '', '');
        $frm->addHiddenField('', 'option_id', $_REQUEST['edit']);
        return $frm;
    }

    public static function getSearchForm()
    {
        $srchForm = new Form('Src_frm', 'Src_frm');
        $srchForm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
        $srchForm->setFieldsPerRow(2);
        $srchForm->captionInSameCell(true);
        $srchForm->addTextBox(t_lang('M_FRM_KEYWORD'), 'keyword', '', '', '');
        $srchForm->addHiddenField('', 'mode', 'search');
        $fld1 = $srchForm->addButton('&nbsp;', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="inputbuttons" onclick=location.href="options.php"');
        $fld = $srchForm->addSubmitButton('&nbsp;', 'btn_search', t_lang('M_TXT_SEARCH'), '', ' class="inputbuttons"')->attachField($fld1);
        return $srchForm;
    }

}
