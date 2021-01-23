<?php

loadModels(['MyAppModel']);

class Deal extends MyAppModel
{

    const DB_TBL = 'tbl_deals';
    const DB_TBL_PREFIX = 'deal_';
    const DB_TBL_PRIMARY_KEY = 'deal_id';

    public function __construct()
    {
        global $db;
    }

    public static function getSearchObject($langId = 0)
    {
        $srch = new SearchBase(static::DB_TBL, 'd');
        return $srch;
    }

    public static function getSearchForm()
    {
        
    }

    public static function getForm()
    {
        
    }

}
