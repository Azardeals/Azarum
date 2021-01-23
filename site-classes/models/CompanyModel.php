<?php

loadModels(['MyAppModel']);

class Company extends MyAppModel
{

    const DB_TBL = 'tbl_companies';
    const DB_TBL_PREFIX = 'company_';
    const DB_TBL_PRIMARY_KEY = 'company_id';
    const DB_TBL_REPRESENTATIVE = 'tbl_representative';

    public function __construct()
    {
        global $db;
    }

    public static function getSearchObject($langId = 0)
    {
        $srch = new SearchBase(static::DB_TBL, 'c');
        return $srch;
    }

    public static function getRepresentative($langId = 0)
    {
        global $db;
        $repArray = [];
        $srch = new SearchBase(static::DB_TBL_REPRESENTATIVE, 'r');
        $srch->addCondition('rep_status', '=', 1);
        $srch->addMultipleFields(['rep_id', 'concat(rep_fname," ",rep_lname) as name']);
        $result = $srch->getResultSet();
        $repArray = $db->fetch_all_assoc($result);
        return $repArray;
    }

    public static function getSearchForm()
    {
        $srcFrm = new Form('Src_frm', 'Src_frm');
        $srcFrm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
        $srcFrm->setFieldsPerRow(2);
        $srcFrm->captionInSameCell(true);
        $srcFrm->addTextBox(t_lang('M_FRM_KEYWORDS'), 'keyword', '', '', '');
        $srcFrm->addHiddenField('', 'mode', 'search');
        $srcFrm->addHiddenField('', 'status', $_REQUEST['status']);
        $fld1 = $srcFrm->addButton('&nbsp;', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="inputbuttons" onclick="location.href=\'companies.php\'"');
        $fld = $srcFrm->addSubmitButton('&nbsp;', 'btn_search', t_lang('M_TXT_SEARCH'), '', ' class="inputbuttons"')->attachField($fld1);
        return $srcFrm;
    }

    public static function getForm()
    {
        global $db;
        $frm = getMBSFormByIdentifier('frmCompany');
        $fld = $frm->getField('company_profile');
        $fld->html_before_field = '<div class="frm-editor">';
        $fld->html_after_field = '</div>';
        $fld = $frm->getField('company_rep_id');
        $fld->options = static::getRepresentative();
        $fld->selectCaption = t_lang('M_TXT_SELECT');
        $fld1 = $frm->getField('btn_submit');
        $fld1->value = t_lang('M_TXT_ADD');
        $rscountry = $db->query("select country_id, country_name" . $_SESSION['lang_fld_prefix'] . " as country_name from tbl_countries where country_status='A' order by country_name" . $_SESSION['lang_fld_prefix']);
        $countryArray = ["" => html_entity_decode(t_lang('M_TEXT_SELECT'))];
        while ($arrs = $db->fetch($rscountry)) {
            $countryArray[$arrs['country_id']] = $arrs['country_name'];
        }
        $fld = $frm->getField('company_google_map');
        $frm->removeField($fld);
        $fld = $frm->getField('company_country');
        $fld->options = $countryArray;
        $fld->extra = 'onchange="updateStates(this.value);"';
        $state = array("" => html_entity_decode(t_lang('M_TEXT_SELECT_COUNTRY_FIRST')));
        $fld = $frm->getField('company_state');
        $fld->fldType = 'select';
        $fld->id = 'state_id';
        $fld->options = $state;
        $frm->changeFieldPosition(11, 13);
        $fld = $frm->getField('company_city');
        $frm->changeFieldPosition(10, 13);
        $fld = $frm->getField('company_zip');
        $frm->changeFieldPosition(10, 13);
        $fld = $frm->getField('company_address1');
        $frm->changeFieldPosition($fld->getFormIndex(), $fld->getFormIndex() + 6);
        $fld = $frm->getField('company_address2');
        $frm->changeFieldPosition($fld->getFormIndex(), $fld->getFormIndex() + 6);
        $fld = $frm->getField('company_address3');
        $frm->changeFieldPosition($fld->getFormIndex(), $fld->getFormIndex() + 6);
        //echo $fld->setFormIndex(+1);
        $fld = $frm->addTextBox(t_lang('M_TXT_TIN'), 'company_tin');
        $frm->changeFieldPosition($fld->getFormIndex(), 15);
        $frm->addTextBox(t_lang('M_TXT_FACEBOOK_URL'), 'company_facebook_url');
        $frm->addTextBox(t_lang('M_TXT_TWITTER_USERNAME'), 'company_twitter');
        $frm->addTextBox(t_lang('M_TXT_LINKED_IN'), 'company_linkedin');
        $fld = $frm->getField('company_fb_apikey');
        $frm->removeField($fld);
        $fld = $frm->getField('company_fb_secret');
        $frm->removeField($fld);
        $fld = $frm->getField('company_fb_session');
        $frm->removeField($fld);
        $fld = $frm->getField('company_logo');
        $fld->extra = 'onchange="readURL(this);"';
        $src = COMPANY_LOGO_URL . 'no-image.jpg';
        $fld->html_after_field = '<div class="CompanyImage_show"><img class="deal_image" src="' . $src . '" ></div>';
        $fld = $frm->addButton('', 'btn_submit_cancel', t_lang('M_TXT_CANCEL'), '', ' class="inputbuttons" onclick=location.href="companies.php"')->attachField($fld1);
        $fld = $frm->getField('company_id');
        $fld->extra = 'id=company_id';
        $frm->setAction('?page=' . $page);
        if (CONF_ADMIN_COMMISSION_TYPE == 1 || CONF_ADMIN_COMMISSION_TYPE == 2) {
            $fld = $frm->getField('company_deal_commission_percent');
            $frm->removeField($fld);
        } else {
            $fld = $frm->getField('company_deal_commission_percent');
            $fld->requirements()->setRequired(true);
            $fld->requirements()->setFloatPositive(true);
            $fld->requirements()->setRange(1, 100);
        }
        return $frm;
    }

}
