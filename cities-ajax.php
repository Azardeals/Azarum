<?php

require_once './application-top.php';
$post = getPostedData();
switch (strtoupper($post['mode'])) {
    case 'GETSTATES':
        if (!is_numeric($post['country'])) {
            die('Select Country');
        }
        $srch = new SearchBase('tbl_states');
        $srch->addCondition('state_status', '=', 'A');
        $srch->addCondition('state_country', '=', $post['country']);
        $srch->addMultipleFields(array('state_id', 'state_name'));
        $rs = $srch->getResultSet();
        $arr_states = $db->fetch_all_assoc($rs);
        $selected = ($post['selected'] > 0) ? $post['selected'] : '0';
        $fld = new FormField('select', 'city_state', 'State');
        $fld->requirements()->setRequired();
        $fld->options = $arr_states;
        $fld->selectCaption = 'Select';
        $fld->value = $selected;
        $fld->extra = 'class="medium"';
        $str = $fld->getHTML();
        $str .= '<script type="text/javascript">//<![CDATA[';
        $str .= 'frmValidator_requirements.city_state=' . json_encode($fld->requirements()->getRequirementsArray()) . '; frmValidator.resetFields();';
        $str .= '//]]></script>';
        echo $str;
        break;
    case 'GETCHARITYSTATES':
        if (!is_numeric($post['country'])) {
            die('Select Country');
        }
        $srch = new SearchBase('tbl_states');
        $srch->addCondition('state_status', '=', 'A');
        $srch->addCondition('state_country', '=', $post['country']);
        $srch->addMultipleFields(array('state_id', 'state_name'));
        $rs = $srch->getResultSet();
        $arr_states = $db->fetch_all_assoc($rs);
        $selected = ($post['selected'] > 0) ? $post['selected'] : '0';
        $fld = new FormField('select', 'charity_state', 'State');
        $fld->requirements()->setRequired();
        $fld->options = $arr_states;
        $fld->selectCaption = t_lang('M_TXT_SELECT');
        $fld->value = $selected;
        $str = $fld->getHTML();
        $str .= '<script type="text/javascript">//<![CDATA[';
        $str .= 'frmValidator_requirements.charity_state=' . json_encode($fld->requirements()->getRequirementsArray()) . '; frmValidator.resetFields();';
        $str .= '//]]></script>';
        echo $str;
        break;
    case 'GETAFFILIATESTATES':
        if (!is_numeric($post['country'])) {
            die('Select Country');
        }
        $srch = new SearchBase('tbl_states');
        $srch->addCondition('state_status', '=', 'A');
        $srch->addCondition('state_country', '=', $post['country']);
        $srch->addMultipleFields(array('state_id', 'state_name'));
        $rs = $srch->getResultSet();
        $arr_states = $db->fetch_all_assoc($rs);
        $selected = ($post['selected'] > 0) ? $post['selected'] : '0';
        $fld = new FormField('select', 'affiliate_state', 'State');
        $fld->requirements()->setRequired();
        $fld->options = $arr_states;
        $fld->selectCaption = t_lang('M_TXT_SELECT');
        $fld->value = $selected;
        $str = $fld->getHTML();
        $str .= '<script type="text/javascript">//<![CDATA[';
        $str .= 'frmValidator_requirements.affiliate_state=' . json_encode($fld->requirements()->getRequirementsArray()) . '; frmValidator.resetFields();';
        $str .= '//]]></script>';
        echo $str;
        break;
}
