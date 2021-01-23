<?php

class CountryClass extends ModelClass
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
        $srch = new SearchBase('tbl_countries');
        $srch->addMultipleFields(array('country_id', 'country_name'));
        $srch->addCondition('country_status', '=', 'A');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        if ($rows = $this->db->fetch_all($rs)) {
            return $this->prepareSuccessResponse($rows);
        }
        return $this->prepareErrorResponse('Country list not found!');
    }

    public function getStates($args)
    {
        if ($this->Api->getRequestMethod() != 'GET') {
            return $this->prepareErrorResponse('Invalid Method For State Details!');
        }
        $country_id = 0;
        if (array_key_exists(0, $args) && is_numeric($args[0])) {
            $country_id = intval($args[0]);
        }
        if ($country_id < 1) {
            return $this->prepareErrorResponse('Invalid State Request!');
        }
        $srch = new SearchBase('tbl_states');
        $srch->addCondition('state_country', '=', $country_id);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        if ($row = $this->db->fetch_all($rs)) {
            return $this->prepareSuccessResponse($row);
        }
        return $this->prepareErrorResponse('State list not found!');
    }

}
