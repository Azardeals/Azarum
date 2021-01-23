<?php

class CommonClass extends ModelClass
{

    const CLIENT_TOKEN_LENGTH = 32;

    public function __construct($api)
    {
        parent::__construct($api);
    }

    public function currency($args)
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'GET') {
            return $this->prepareErrorResponse('Invalid Method For Cart Class!');
        }
        $request_data = $this->Api->getRequestData();
        $currency['LEFT'] = CONF_CURRENCY;
        $currency['RIGHT'] = CONF_CURRENCY_RIGHT;
        return $this->prepareSuccessResponse($currency);
    }

}
