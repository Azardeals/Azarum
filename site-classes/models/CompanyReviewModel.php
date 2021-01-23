<?php

loadModels(['MyAppModel']);

class CompanyReview extends MyAppModel
{

    const DB_TBL = 'tbl_reviews';
    const DB_TBL_PREFIX = 'reviews_';
    const DB_TBL_PRIMARY_KEY = 'reviews_id';

    public function __construct()
    {
        global $db;
    }

    public static function getSearchObject($langId = 0)
    {
        $srch = new SearchBase(static::DB_TBL, 'd');
        return $srch;
    }

    public function updateStatus($reviewId, $status)
    {
        $data = ['reviews_approval' => $status];
        $record = new TableRecord(static::DB_TBL);
        $record->assignValues($data);
        $success = $record->update('reviews_id =' . $reviewId);
        return $success;
    }

    public static function getCompanies()
    {
        global $db;
        $companyArray = [];
        $srch = new SearchBase('tbl_companies');
        $srch->addCondition("company_active", "=", 1);
        $srch->addCondition("company_deleted", "=", 0);
        $srch->addMultipleFields(['company_id', "IF(CHAR_LENGTH(company_name" . $_SESSION['lang_fld_prefix'] . "),company_name" . $_SESSION['lang_fld_prefix'] . ",company_name) as company_name"]);
        $srch->addOrder("company_name" . $_SESSION['lang_fld_prefix'], 'asc');
        $result = $srch->getResultSet();
        $companyArray = $db->fetch_all_assoc($result);
        return $companyArray;
    }

    public static function getSearchForm()
    {
        $arr_rating = array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5);
        $srcFrm = new Form('Src_frm', 'Src_frm');
        $srcFrm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
        $srcFrm->setFieldsPerRow(4);
        $srcFrm->captionInSameCell(true);
        $srcFrm->addSelectBox(t_lang('M_FRM_COMPNAY_NAME'), 'deal_company', Static::getCompanies(), '', '', t_lang('M_TXT_SELECT'), 'deal_company');
        $srcFrm->addSelectBox(t_lang('M_FRM_RATING'), 'rating', $arr_rating, '', '', t_lang('M_TXT_SELECT'));
        $srcFrm->addHiddenField('', 'mode', 'search');
        $fld1 = $srcFrm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="inputbuttons" onclick=location.href="company-review.php"');
        $fld = $srcFrm->addSubmitButton('', 'btn_search', t_lang('M_TXT_SEARCH'), '', ' class="inputbuttons"');
        $fld->attachField($fld1);
        return $srcFrm;
    }

    public static function getForm()
    {
        $frm = new Form('frmReview', 'frmReview');
        $frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
        $frm->setFieldsPerRow(1);
        $frm->captionInSameCell(false);
        $frm->addTextArea(t_lang('M_FRM_REVIEWS'), 'reviews_reviews', '', '', 'class="field--large"')->requirements()->setRequired();
        $frm->setJsErrorDisplay('afterfield');
        $frm->addHiddenField('', 'reviews_company_id', '', 'reviews_company_id');
        $frm->addHiddenField('', 'reviews_id', '', 'reviews_id');
        $frm->addHiddenField('', 'reviews_user_id', '', 'reviews_user_id');
        $frm->addHiddenField('', 'reviews_id', '', 'reviews_id');
        $frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SUBMIT'), '', ' class="inputbuttons"');
        return $frm;
    }

}
