<?php

require_once 'application-top.php';
checkAdminPermission(7);
$post = getPostedData();
switch (strtoupper($post['mode'])) {
    case 'GETSTATES':
        if (!is_numeric($post['country']))
            die('Select Country');
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
        $str = $fld->getHTML();
        $str .= '<script type="text/javascript">//<![CDATA[';
        $str .= 'frmValidator_requirements.city_state=' . json_encode($fld->requirements()->getRequirementsArray()) . '; frmValidator.resetFields();';
        $str .= '//]]></script>';
        echo $str;
        break;
    case 'DEFAULTLANGUAGE':
        if (!is_numeric($post['lang']))
            die('Select Language');
        if ($post['lang'] == 2) {
            $_SESSION['lang_fld_prefix'] = '_lang1';
            $db->query("update tbl_configurations set conf_val = '2'   where conf_name='conf_default_language'");
            $_SESSION['language'] = 2;
            echo t_lang('M_TXT_LANGUAGE_UPDATED');
        } elseif ($post['lang'] == 1) {
            $_SESSION['lang_fld_prefix'] = '';
            $db->query("update tbl_configurations set conf_val = '1'   where conf_name='conf_default_language'");
            $_SESSION['language'] = 1;
            echo t_lang('M_TXT_LANGUAGE_UPDATED');
        } else {
            echo 'Invalid input.';
        }
        break;
}
