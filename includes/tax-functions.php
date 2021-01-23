<?php

require_once dirname(__FILE__) . '/../application-top.php';
require_once dirname(__FILE__) . '/../includes/navigation-functions.php';

function getTaxStateRecord($geozoneId)
{
    global $msg;
    global $db;
    $srch = new SearchBase('tbl_geo_zone_location', 'gzl');
    $srch->doNotCalculateRecords();
    $srch->doNotLimitRecords();
    $srch->addCondition('gzl.zoneloc_geozone_id', '=', $geozoneId);
    $srch->addMultipleFields(array('zoneloc_state_id,zoneloc_state_id'));
    $rs = $srch->getResultSet();
    $row = $db->fetch_all_assoc($rs);
    if (!$row) {
        return false;
    } else {
        return $row;
    }
}

function getActiveTaxClass()
{
    global $msg;
    global $db;
    $srch = new SearchBase('tbl_tax_classes');
    $srch->addCondition('taxclass_active', '=', '1');
    $srch->addOrder('taxclass_name', 'asc');
    $srch->doNotCalculateRecords();
    $srch->doNotLimitRecords();
    $srch->addMultipleFields(array('taxclass_id', 'taxclass_name'));
    $rs = $srch->getResultSet();
    $row = $db->fetch_all_assoc($rs);
    if (!$row) {
        return false;
    } else {
        return $row;
    }
}

function getDealInfo($dealId)
{
    global $db;
    $srch = new SearchBase('tbl_deals', 'd');
    $srch->addCondition('deal_id', '=', $dealId);
    $srch->doNotCalculateRecords();
    $srch->doNotLimitRecords();
//	$srch->addFld('deal_taxclass_id');
    $rs = $srch->getResultSet();
    $row_deal = $db->fetch($rs);
    if (!$row_deal) {
        return false;
    } else {
        return $row_deal;
    }
}

function fetchShippingAddress()
{
    global $db;
    $srch = new SearchBase('tbl_user_addresses', 'u');
    $srch->addCondition('uaddr_user_id', '=', $_SESSION['logged_user']['user_id']);
    $srch->addCondition('uaddr_is_dafault', '=', 1);
    $srch->doNotCalculateRecords();
    $srch->doNotLimitRecords();
    $srch->addFld("GROUP_CONCAT(distinct(u.uaddr_state_id)SEPARATOR ',') as state_id");
    $srch->addFld("GROUP_CONCAT(distinct(u.uaddr_country_id)SEPARATOR ',') as country_id");
    $rs = $srch->getResultSet();
    $row_deal = $db->fetch($rs);
    if (!$row_deal) {
        return false;
    } else {
        return $row_deal;
    }
}

function fetchBillingAddress()
{
    global $db;
    $srch = new SearchBase('tbl_users_card_detail', 'u');
    $srch->addCondition('ucd_user_id', '=', $_SESSION['logged_user']['user_id']);
    $srch->doNotCalculateRecords();
    $srch->doNotLimitRecords();
    $srch->addFld("GROUP_CONCAT(distinct(u.ucd_state_id)SEPARATOR ',') as state_id");
    $srch->addFld("GROUP_CONCAT(distinct(u.ucd_country_id)SEPARATOR ',') as country_id");
    $rs = $srch->getResultSet();
    $row_deal = $db->fetch($rs);
    if (!$row_deal) {
        return false;
    } else {
        return $row_deal;
    }
}

function fetchStoreAddress($company_id)
{
    global $db;
    $srch = new SearchBase('tbl_companies', 'c');
    $srch->addCondition('company_id', '=', $company_id);
    $srch->doNotCalculateRecords();
    $srch->doNotLimitRecords();
    $srch->addFld("GROUP_CONCAT(distinct(c.company_state)SEPARATOR ',') as state_id");
    $srch->addFld("GROUP_CONCAT(distinct(c.company_country)SEPARATOR ',') as country_id");
    $rs = $srch->getResultSet();
    $row_deal = $db->fetch($rs);
    if (!$row_deal) {
        return false;
    } else {
        return $row_deal;
    }
}

function fetchStateListIDs($country_id)
{
    global $db;
    $srch = new SearchBase('tbl_states');
    $srch->addCondition('state_status', '=', 'A');
    $srch->addCondition('state_country', '=', $country_id);
    $srch->addOrder('state_name', 'asc');
    $srch->addMultipleFields(array('state_id', 'state_name' . $_SESSION['lang_fld_prefix']));
    $rs = $srch->getResultSet();
    $arr_states = $db->fetch_all_assoc($rs);
    if (!$arr_states) {
        return false;
    } else {
        return $arr_states;
    }
}
