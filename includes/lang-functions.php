<?php

function assignValuesToTableRecord($obj, $arr_lang_independent_flds, $arr, $handleDates = true, $mysql_date_format = '', $mysql_datetime_format = '', $execute_mysql_functions = false)
{
    $arr_new_flds = [];
    foreach ($arr as $key => $val) {
        if (in_array($key, $arr_lang_independent_flds)) {
            $arr_new_flds[$key] = $val;
        } else {
            $arr_new_flds[$key . $_SESSION['lang_fld_prefix']] = $val;
        }
    }
    $obj->assignValues($arr_new_flds, $handleDates, $mysql_date_format, $mysql_datetime_format, $execute_mysql_functions);
}

function getFieldFromRow($row, $fld)
{
    if (isset($row[$fld . $_SESSION['lang_fld_prefix']]))
        return $row[$fld . $_SESSION['lang_fld_prefix']];
    return $row[$fld];
}

function getFieldFromRowImage($row, $fld)
{
    if (isset($row[$fld . $_SESSION['lang_fld_prefix']]))
        return $row[$fld . $_SESSION['lang_fld_prefix']];
    return $row[$fld];
}

function displayDateCustom($row)
{
    return displayDate($row, true);
}

function addSearchCondition()
{
    
}

function attachSearchCondition()
{
    
}

function getImage()
{
    
}

function checkLanguageSession()
{
    if ($_SESSION['lang_fld_prefix'] == '_lang1') {
        return true;
    }
    return false;
}

function fillForm($frm, $data)
{
    $frm->fill(getFormFillDataForLang($data));
}

function getFormFillDataForLang($data)
{
    foreach ($data as $key => $val) {
        if (is_array($val)) {
            $data[$key] = getFormFillDataForLang($val);
        } else {
            if (isset($data[$key . $_SESSION['lang_fld_prefix']])) {
                $data[$key] = $data[$key . $_SESSION['lang_fld_prefix']];
            }
        }
    }
    return $data;
}
