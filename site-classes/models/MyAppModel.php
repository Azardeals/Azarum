<?php

class MyAppModel
{

    public function __construct()
    {
        global $db;
        $this->db = $db;
    }

    public static function recordExists($tableName, $filedName, $actionId)
    {
        $src = new SearchBase($tableName);
        $src->addCondition($filedName, '=', $actionId);
        $result = $src->getResultSet();
        if ($src->recordCount($result) <= 0) {
            return false;
        }
        return true;
    }

}
