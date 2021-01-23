<?php

loadModels(['MyAppModel']);

class Affiliate extends MyAppModel
{

    const DB_TBL = 'tbl_affiliate';
    const DB_TBL_PREFIX = 'affiliate_';
    const DB_TBL_PRIMARY_KEY = 'affiliate_id';
    const DB_AFFILIATE_WALLET_TBL = 'tbl_affiliate_wallet_history';

    public function __construct()
    {
        global $db;
    }

    public static function getSearchObject($langId = 0)
    {
        $srch = new SearchBase(static::DB_TBL, 'a');
        return $srch;
    }

    public function deleteAffilateHistory($affiliateId)
    {
        global $db;
        $db->deleteRecords(static::DB_TBL, ['smt' => static::DB_TBL_PREFIX . 'id = ?', 'vals' => [$affiliateId]]);
        $db->deleteRecords(static::DB_AFFILIATE_WALLET_TBL, ['smt' => 'wh_affiliate_id = ?', 'vals' => [$affiliateId]]);
        return true;
    }

    public static function getSearchForm($arr_user_status, $arr_sale_earning)
    {
        $srchForm = new Form('Src_frm', 'Src_frm');
        $srchForm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
        $srchForm->setFieldsPerRow(4);
        $srchForm->captionInSameCell(true);
        $srchForm->addTextBox(t_lang('M_FRM_KEYWORD'), 'keyword', '', '', '');
        $srchForm->addSelectBox(t_lang('M_FRM_STATUS'), 'affiliate_status', $arr_user_status, '', 'style="width: 160px;"', '--Select--', '');
        $srchForm->addSelectBox(t_lang('M_FRM_SALES'), 'sales_earning', $arr_sale_earning, '', 'style="width: 160px;"', '--Select--', '');
        $srchForm->addHiddenField('', 'mode', 'search');
        $srchForm->addHiddenField('', 'status', $_REQUEST['status']);
        $fld1 = $srchForm->addButton('&nbsp;', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="medium" onclick=location.href="affiliate.php"');
        $fld = $srchForm->addSubmitButton('&nbsp;', 'search', t_lang('M_TXT_SEARCH'), '', ' class="medium"')->attachField($fld1);
        return $srchForm;
    }

    public static function getForm($page = 1)
    {
        $frm = getMBSFormByIdentifier('frmAffiliate');
        $frm->setAction('?page=' . $page);
        $fld = $frm->getField('affiliate_bussiness_name');
        $fld->requirements()->setRequired(false);
        $fld = $frm->getField('submit');
        $fld->value = t_lang('M_TXT_SUBMIT');
        $fld->field_caption = '&nbsp;';
        return $frm;
    }

}
