<?php

loadModels(['MyAppModel']);

class DealCategory extends MyAppModel
{

    const DB_TBL = 'tbl_deal_categories';
    const DB_TBL_PREFIX = 'cat_';
    const DB_TBL_PRIMARY_KEY = 'cat_id';

    public function __construct()
    {
        global $db;
    }

    public static function getSearchObject($langId = 0)
    {
        $srch = new SearchBase(static::DB_TBL, 'm');
        return $srch;
    }

    public static function getSearchForm()
    {
        $srchForm = new Form('Src_frm', 'Src_frm');
        $srchForm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
        $srchForm->setFieldsPerRow(3);
        $srchForm->captionInSameCell(true);
        $srchForm->addTextBox(t_lang('M_FRM_KEYWORD'), 'keyword', '', '', '');
        $srchForm->addHiddenField('', 'mode', 'search');
        $fld1 = $srchForm->addButton('&nbsp;', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="inputbuttons" onclick=location.href="deal-categories.php"');
        $fld = $srchForm->addSubmitButton('&nbsp;', 'btn_search', t_lang('M_TXT_SEARCH'), '', ' class="inputbuttons"')->attachField($fld1);
        return $srchForm;
    }

    public static function getForm($parentArray = [])
    {
        $frm = getMBSFormByIdentifier('frmDealCategories');
        $fld = $frm->getField('cat_name');
        $fld->setUnique('tbl_deal_categories', 'cat_name' . $_SESSION['lang_fld_prefix'], 'cat_id', 'cat_id', 'cat_id');
        $fld = $frm->getField('cat_image');
        $fld->field_caption = unescape_attr(t_lang('M_FRM_CATEGORY_IMAGE'));
        $frm->removeField($fld);
        $fld = $frm->getField('cat_bg_image');
        $frm->removeField($fld);
        $fld = $frm->getField('cat_layout');
        $frm->removeField($fld);
        $fld1 = $frm->getField('cat_parent_id');
        $fld1->field_caption = t_lang('M_TXT_PARENT_CATEGORY');
        $fld1->options = $parentArray;
        $fld1 = $frm->getField('btn_submit');
        $fld = $frm->addButton('', 'btn_submit_cancel', t_lang('M_TXT_CANCEL'), '', ' class="inputbuttons" onclick=location.href="deal-categories.php"')->attachField($fld1);
        $frm->setAction('?page=' . $page);
        return $frm;
    }

}
