<?php

class CityClass extends ModelClass
{

    public function __construct($api)
    {
        parent::__construct($api);
    }

    public function getList($args)
    {

        if ($this->Api->getRequestMethod() != 'GET') {
            return $this->prepareErrorResponse('Invalid Method For City List!');
        }
        $srch = new SearchBase('tbl_cities');
        $srch->addMultipleFields(array('city_id', 'city_name'));
        $srch->addCondition('city_active', '=', 1);
        $srch->addCondition('city_deleted', '=', 0);
        $srch->addCondition('city_request', '=', 0);
        $srch->addOrder('city_name', 'asc');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        if ($rows = $this->db->fetch_all($rs)) {
            return $this->prepareSuccessResponse($rows);
        }
        return $this->prepareErrorResponse('City list not found!');
    }

    public function get($args)
    {
        if ($this->Api->getRequestMethod() != 'GET') {
            return $this->prepareErrorResponse('Invalid Method For City Details!');
        }
        $city_id = 0;
        if (array_key_exists(0, $args) && is_numeric($args[0])) {
            $city_id = intval($args[0]);
        }
        if ($city_id < 1) {
            return $this->prepareErrorResponse('Invalid City Request!');
        }
        $srch = new SearchBase('tbl_cities');
        $srch->addCondition('city_id', '=', $city_id);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        if ($row = $this->db->fetch($rs)) {
            return $this->prepareSuccessResponse($row);
        }
        return $this->prepareErrorResponse('City list not found!');
    }

}
