<?php

require_once './application-top.php';
checkAdminPermission(2);
$post = getPostedData();
switch (strtoupper($post['mode'])) {
    case 'GETSTATES':
        if (!is_numeric($post['country']))
            die(t_lang('M_TXT_SELECT') . ' ' . t_lang('M_FRM_COUNTRY'));
        $srch = new SearchBase('tbl_states');
        $srch->addCondition('state_status', '=', 'A');
        $srch->addCondition('state_country', '=', $post['country']);
        $srch->addOrder('state_name', 'asc');
        $srch->addMultipleFields(array('state_id', 'state_name' . $_SESSION['lang_fld_prefix']));
        $rs = $srch->getResultSet();
        $arr_states = $db->fetch_all_assoc($rs);
        $selected = ($post['selected'] > 0) ? $post['selected'] : '0';
        $fld = new FormField('select', 'city_state', 'State');
        $fld->requirements()->setRequired();
        $fld->options = $arr_states;
        $fld->selectCaption = t_lang('M_TXT_SELECT');
        $fld->value = $selected;
        $fld->html_after_field = '&nbsp; &nbsp; <ul class="actions"><li><a href="javascript:void(0);" onclick="addState(' . $post['country'] . ');" title="' . t_lang('M_TXT_ADD_NEW_STATE') . '"><i class="ion-plus-circled icon"></i></a></li></ul>';
        $str = $fld->getHTML();
        $str .= '<script type="text/javascript">//<![CDATA[
			';
        $str .= 'frmValidator_requirements.city_state=' . json_encode($fld->requirements()->getRequirementsArray()) . '; frmValidator.resetFields();';
        $str .= '
			//]]></script>';
        echo $str;
        break;
    case 'ADDSTATE':
        if (!is_numeric($post['country']))
            die(t_lang('M_TXT_SELECT') . ' ' . t_lang('M_FRM_COUNTRY'));
        $frm = new Form('frmStates', 'frmStates');
        $frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
        $frm->setFieldsPerRow(1);
        $frm->captionInSameCell(false);
        $frm->setJsErrorDisplay('afterfield');
        $frm->setValidatorJsObjectName('frmValidator');
        $frm->setExtra('onsubmit="submitAddState(this, frmValidator ,' . $post['country'] . '); return(false);"');
        $frm->addRequiredField(t_lang('M_TXT_STATE_NAME'), 'state_name', '', '', '');
        $frm->addSelectBox(t_lang('M_TXT_STATE_STATUS'), 'state_status', ['A' => 'Active', 'B' => 'Inactive'], $value, '', 'Select', 'state_country');
        $frm->addHiddenField('', 'state_country', $post['country'], 'state_country');
        $frm->addHiddenField('', 'mode', 'search');
        $frm->addHiddenField('', 'mode', 'submitState');
        $frm->addHiddenField('', 'state_id', '', 'state_id');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', t_lang('M_TXT_SUBMIT'), '', ' class="medium"');
        echo $frm->getFormHtml();
        break;
    case 'SUBMITSTATE':
        if (isset($_POST['btn_submit'])) {
            $post = getPostedData();
            $record = new TableRecord('tbl_states');
            $arr_lang_independent_flds = ['state_id', 'state_country', 'state_status', 'mode', 'btn_submit'];
            assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
            if ((checkAdminAddEditDeletePermission(7, '', 'add'))) {
                if ($post['state_id'] == '')
                    $success = $record->addNew();
            }
            if ($success) {
                $state_id = ($post['state_id'] > 0) ? $post['state_id'] : $record->getId();
                $msg->addMsg(t_lang('M_TXT_STATE_ADDED'));
                die('<div class="box" id="messages">
                    <div class="title-msg"> ' . t_lang('M_TXT_SYSTEM_MESSAGES') . '</div>
                    <div class="content">
                      <div class="greentext">' . $msg->display() . '</div>
                       
                    </div>
                  </div>');
            } else {
                $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
                fillForm($frm, $arr);
                die('<div class="box" id="messages">
                    <div class="title-msg"> ' . t_lang('M_TXT_SYSTEM_MESSAGES') . '</div>
                    <div class="content">
                      <div class="redtext">' . $msg->display() . '</div>
                       
                    </div>
                  </div>');
            }
        }
        break;
    case 'GETCHARITYSTATES':
        if (!is_numeric($post['country']))
            die(t_lang('M_TXT_SELECT') . ' ' . t_lang('M_FRM_COUNTRY'));
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
        $str .= '<script type="text/javascript">//<![CDATA[
			';
        $str .= 'frmValidator_requirements.charity_state=' . json_encode($fld->requirements()->getRequirementsArray()) . '; frmValidator.resetFields();';
        $str .= '
			//]]></script>';
        echo $str;
        break;
    case 'GETAFFILIATESTATES':
        if (!is_numeric($post['country']))
            die(t_lang('M_TXT_SELECT') . ' ' . t_lang('M_FRM_COUNTRY'));
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
        $str .= '<script type="text/javascript">//<![CDATA[
			';
        $str .= 'frmValidator_requirements.affiliate_state=' . json_encode($fld->requirements()->getRequirementsArray()) . '; frmValidator.resetFields();';
        $str .= '
			//]]></script>';
        echo $str;
        break;
    case 'GETREPRESENTATIVESTATES':
        if (!is_numeric($post['country']))
            die(t_lang('M_TXT_SELECT') . ' ' . t_lang('M_FRM_COUNTRY'));
        $srch = new SearchBase('tbl_states');
        $srch->addCondition('state_status', '=', 'A');
        $srch->addCondition('state_country', '=', $post['country']);
        $srch->addMultipleFields(array('state_id', 'state_name'));
        $rs = $srch->getResultSet();
        $arr_states = $db->fetch_all_assoc($rs);
        $selected = ($post['selected'] > 0) ? $post['selected'] : '0';
        $fld = new FormField('select', 'rep_state', 'State');
        $fld->requirements()->setRequired();
        $fld->options = $arr_states;
        $fld->selectCaption = t_lang('M_TXT_SELECT');
        $fld->value = $selected;
        $str = $fld->getHTML();
        $str .= '<script type="text/javascript">//<![CDATA[
			';
        $str .= 'frmValidator_requirements.rep_state=' . json_encode($fld->requirements()->getRequirementsArray()) . '; frmValidator.resetFields();';
        $str .= '
			//]]></script>';
        echo $str;
        break;
    case 'DELETECITY':
        /* $srch = new SearchBase('tbl_deals');
          $srch->addCondition('deal_city', '=', $post['city']);
          $srch->addCondition('deal_deleted', '=', 0);
          $rs = $srch->getResultSet();
          $total_count = $srch->recordCount($rs);
          echo $total_count;
         */
        echo canDeleteCity($post['city']);
        break;
    case 'DELETEZONE':
        global $db, $msg;
        $zoneId = $post['city'];
        if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
            $cityRs = $db->query("Select * from tbl_tax_rates where taxrate_geozone_id=" . intval($zoneId));
            if ($db->total_records($cityRs) == 0) {
                if (!$db->query("delete from tbl_tax_geo_zones  WHERE geozone_id =" . $zoneId)) {
                    $msg->addError($db->getError());
                } else {
                    $db->query("delete from tbl_geo_zone_location  WHERE zoneloc_geozone_id =" . $zoneId);
                    $msg->addMsg(t_lang('M_TXT_RECORD_DELETED'));
                }
            } else {
                $msg->addError(t_lang('M_TXT_ZONE_CANNOT_DELETED_SOME_TAX__RATE_ARE_ASSOCIATED_WITH_IT'));
            }
        } else {
            $msg->addError(t_lang("M_TXT_UNAUTHORIZED_ACCESS"));
        }
        break;
    case 'DELETECATEGORY':
        /* $srch = new SearchBase('tbl_deal_to_category','dtc');
          $srch->addCondition('dc_cat_id', '=', $post['category']);
          $rs = $srch->getResultSet();
          $total_count = $srch->recordCount($rs);
          echo $total_count; */
        echo canDeleteCategory($post['category']);
        break;
    case 'GETSTATESFORZONE':
        if (!is_numeric($post['country']))
            die(t_lang('M_TXT_SELECT') . ' ' . t_lang('M_FRM_COUNTRY'));
        $selectedZone = explode(",", $post['selected']);
        $srch = new SearchBase('tbl_states');
        $srch->addCondition('state_status', '=', 'A');
        $srch->addCondition('state_country', '=', $post['country']);
        $srch->addOrder('state_name', 'asc');
        $srch->addMultipleFields(array('state_id', 'state_name' . $_SESSION['lang_fld_prefix']));
        $rs = $srch->getResultSet();
        $arr_states = $db->fetch_all_assoc($rs);
        $selected = ($post['selected'] > 0) ? $post['selected'] : '0';
        $fld = new FormField('select', 'zoneloc_state_id[]', 'State');
        $fld->extra = "multiple";
        $fld->requirements()->setRequired();
        $fld->options = $arr_states;
        $fld->selectCaption = t_lang('M_TXT_SELECT');
        $fld->value = $selectedZone;
        //$fld->html_after_field='&nbsp; &nbsp; <a href="javascript:void(0);" class="btn gray" onclick="addState('.$post['country'].');">'.t_lang('M_TXT_ADD_NEW_STATE').'</a>';
        $str = $fld->getHTML();
        /*  $str .= '<script type="text/javascript">//<![CDATA[
          ';
          $str .= 'frmValidator_requirements.zoneloc_state_id=' . json_encode($fld->requirements()->getRequirementsArray()) . '; frmValidator.resetFields();';
          $str .= '
          //]]></script>'; */
        echo $str;
        break;
    case 'DELETEMULTIPLESTATES':
        if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
            $flag = true;
            global $db;
            foreach ($post['states'] as $key => $value) {
                $cityRs = $db->query("Select * from tbl_cities where city_state=" . intval($value) . " AND city_deleted = 0");
                if ($db->total_records($cityRs) == 0) {
                    $db->query('DELETE from tbl_states where state_id=' . intval($value));
                } else {
                    $flag = false;
                }
            }
            if ($flag) {
                dieJsonSuccess(t_lang('M_TXT_STATE_DELETED'));
            } else {
                dieJsonError(t_lang('M_TXT_STATE_CANNOT_DELETED_SOME_CITIES_ARE_ASSOCIATED_WITH_IT'));
            }
        }
        break;
    case 'DELETEMULTIPLECITIES':
        global $db;
        if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
            $flag = true;
            foreach ($post['cities'] as $key => $value) {
                $deals = canDeleteCity($value);
                if ($deals == 0) {
                    $db->update_from_array('tbl_cities', ['city_deleted' => 1], 'city_id=' . $value);
                } else {
                    $flag = false;
                    //$msg->addError();
                }
            }
            if ($flag) {
                dieJsonSuccess(t_lang('M_TXT_CITY_DELETED'));
            } else {
                dieJsonError(t_lang('M_MSG_CITY_DELETION_NOT_ALLOWED'));
            }
        }
        break;
    case 'DELETEMULTIPLECOUNTRIES':
        global $db;
        if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
            $flag = true;
            global $db;
            foreach ($post['country'] as $key => $value) {
                $cityRs = $db->query("Select * from tbl_states where state_country=" . intval($value));
                if ($db->total_records($cityRs) == 0) {
                    $db->query('DELETE from tbl_countries where country_id=' . intval($value));
                } else {
                    $flag = false;
                }
            }
            if ($flag) {
                dieJsonSuccess(t_lang('M_TXT_COUNTRY_DELETED'));
            } else {
                dieJsonError(t_lang('M_TXT_COUNTRY_CANNOT_DELETED_SOME_STATES_ARE_ASSOCIATED_WITH_IT'));
            }
        }
        break;
    case 'DELETEMULTIPLEZONES':
        global $db;
        if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
            $flag = true;
            global $db;
            foreach ($post['zone'] as $key => $value) {
                $cityRs = $db->query("Select * from tbl_tax_rates where taxrate_geozone_id=" . intval($value));
                if ($db->total_records($cityRs) == 0) {
                    if (!$db->query("delete from tbl_tax_geo_zones  WHERE geozone_id =" . $value)) {
                        dieJsonError($db->getError());
                    } else {
                        $db->query("delete from tbl_geo_zone_location  WHERE zoneloc_geozone_id =" . $value);
                    }
                } else {
                    $flag = false;
                }
            }
            if ($flag) {
                dieJsonSuccess(t_lang('M_TXT_ZONE_DELETED'));
            } else {
                dieJsonError(t_lang('M_TXT_ZONE_CANNOT_DELETED_SOME_TAX_RATE_ARE_ASSOCIATED_WITH_IT'));
            }
        }
        break;
    case 'DELETESUBSCRIBEDUSERS':
        global $db;
        if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
            $flag = true;
            global $db;
            foreach ($post['listing_id'] as $key => $value) {
                if (!$db->query("delete from tbl_newsletter_subscription  WHERE subs_id =" . $value)) {
                    dieJsonError($db->getError());
                }
            }
            dieJsonSuccess(t_lang('M_TXT_USERS_DELETED'));
        }
        break;
}
