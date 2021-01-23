<?php

loadModels(['MyAppModel']);

class Representative extends MyAppModel
{

    const DB_TBL = 'tbl_representative';
    const DB_TBL_PREFIX = 'rep_';
    const DB_TBL_PRIMARY_KEY = 'rep_id';
    const DB_REPRESENTATIVE_WALLET_TBL = 'tbl_representative_wallet_history';

    public function __construct()
    {
        global $db;
    }

    public static function getSearchObject($langId = 0)
    {
        $srch = new SearchBase(static::DB_TBL, 'a');
        return $srch;
    }

    public function deleteRepresentativeHistory($repId)
    {
        global $db;
        $db->deleteRecords(static::DB_TBL, ['smt' => static::DB_TBL_PREFIX . 'id = ?', 'vals' => [$repId]]);
        $db->deleteRecords(static::DB_REPRESENTATIVE_WALLET_TBL, ['smt' => 'rwh_rep_id = ?', 'vals' => [$repId]]);
        return true;
    }

    public static function getSearchForm($arr_user_status, $arr_sale_earning)
    {
        $srchForm = new Form('Src_frm', 'Src_frm');
        $srchForm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
        $srchForm->setFieldsPerRow(4);
        $srchForm->captionInSameCell(true);
        $srchForm->addTextBox(t_lang('M_FRM_KEYWORD'), 'keyword', '', '', '');
        $srchForm->addSelectBox(t_lang('M_FRM_STATUS'), 'rep_status', $arr_user_status, '', 'style="width: 160px;"', '--Select--', '');
        $srchForm->addSelectBox(t_lang('M_FRM_SALES'), 'sales_earning', $arr_sale_earning, '', 'style="width: 160px;"', '--Select--', '');
        $srchForm->addHiddenField('', 'mode', 'search');
        $srchForm->addHiddenField('', 'status', $_REQUEST['status']);
        $fld1 = $srchForm->addButton('&nbsp;', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="medium" onclick=location.href="representative.php"');
        $fld = $srchForm->addSubmitButton('&nbsp;', 'search', t_lang('M_TXT_SEARCH'), '', ' class="medium"')->attachField($fld1);
        return $srchForm;
    }

    public static function getForm()
    {
        global $db;
        $frm = new Form('frmRepresentative', 'frmRepresentative');
        $frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
        $frm->setFieldsPerRow(1);
        $frm->captionInSameCell(false);
        $frm->setJsErrorDisplay('afterfield');
        $frm->setValidatorJsObjectName('frmValidator');
        $frm->addRequiredField('M_FRM_FIRST_NAME', 'rep_fname', '', 'rep_fname', '');
        $frm->addTextBox('M_FRM_LAST_NAME', 'rep_lname', '', 'rep_lname', '');
        $frm->addTextBox('M_FRM_BUSINESS_NAME', 'rep_bussiness_name', '', 'rep_bussiness_name', '');
        $frm->addRequiredField('M_FRM_ADDRESS_LINE1', 'rep_address_line1', '', 'rep_address_line1', '');
        $frm->addTextBox('M_FRM_ADDRESS_LINE2', 'rep_address_line2', '', 'rep_address_line2', '');
        $frm->addTextBox('M_FRM_ADDRESS_LINE3', 'rep_address_line3', '', 'rep_address_line3', '');
        $rscountry = $db->query("select country_id, country_name from tbl_countries where country_status='A' order by country_name");
        $countryArray = [];
        while ($arrs = $db->fetch($rscountry)) {
            $countryArray[$arrs['country_id']] = $arrs['country_name'];
        }
        $frm->addSelectBox('M_FRM_COUNTRY', 'rep_country', $countryArray, '', 'onchange="updateStates(this.value);" class="medium"', '', 'rep_country');
        $fld = $frm->addHtml('M_FRM_STATE', 'state', '<span id="spn-state"></span>', false);
        $frm->addTextBox('M_FRM_CITY', 'rep_city', '', 'rep_city', '');
        $frm->addTextBox('M_FRM_COMMISSION', 'rep_commission', '', 'rep_commission', '');
        $frm->addTextBox('M_FRM_ZIP_CODE', 'rep_zipcode', '', 'rep_zipcode', '');
        $fld = $frm->addEmailField('M_FRM_EMAIL_ADDRESS', 'rep_email_address', '', 'rep_email_address', '');
        $fld->setUnique('tbl_representative', 'rep_email_address', 'rep_id', 'rep_id', 'rep_id');
        $fld->Requirements()->setRequired();
        $frm->addPasswordField('M_FRM_PASSWORD', 'rep_password', '', 'rep_password', '')->Requirements()->setRequired();
        $frm->addTextBox('M_FRM_PHONE_NO', 'rep_phone', '', 'rep_phone', '');
        $frm->addTextBox('M_FRM_PAYPAL_ID', 'rep_paypal_id', '', 'rep_paypal_id', '');
        $frm->addHiddenField('', 'rep_id', '', 'rep_id', '');
        $fld = $frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SUBMIT'), '', ' class="medium"');
        return $frm;
    }

}
