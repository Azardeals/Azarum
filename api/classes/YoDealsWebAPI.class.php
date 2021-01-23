<?php

require_once dirname(__FILE__) . '/API.class.php';

final class YoDealsWebAPI extends API
{

    const PATH_TO_MODEL_CLASSES = __DIR__;

    private $api_token_exceptions = [];
    protected $city = 0;
    protected $lang = 0;
    protected $user = 5;

    public function __construct()
    {
        //Request parse
        $get = $this->_getQueryStringData();
        $this->lang = intval(array_key_exists('lang', $get) ? $get['lang'] : 0);
        $this->city = intval(array_key_exists('city', $get) ? $get['city'] : 0);
        $this->setInitialSystemVals();
        $request = (array_key_exists('request', $get) ? $get['request'] : '');
        //Pass The Request Vars to Api Class
        parent::__construct($request);
        $this->validateClientToken();
    }

    private function setInitialSystemVals()
    {
        selectCity($this->city);
        $_SESSION['language'] = $this->lang;
        setLangSessionVals();
    }

    public function validateClientToken()
    {
        $user = $this->_getClassObject('user');
        if ($this->request_method == 'POST' || $this->request_method == 'DELETE') {
            global $db;
            $request_data = $this->getRequestData();
            if (isset($request_data['token'])) {
                if (strlen($request_data['token']) < 32) {
                    throw new Exception('Invalid User Token');
                }
                $row = $user->checkAPITokenInDB($request_data['token']);
                if (!$row) {
                    throw new Exception('Invalid User Token');
                }
                if ($row) {
                    $srch = new SearchBase('tbl_users');
                    $srch->addCondition('user_id', '=', $row['uapitoken_user_id']);
                    $rs = $srch->getResultSet();
                    if ($db->total_records($rs) == 0) {
                        throw new Exception('User not found');
                    }
                    $row = $db->fetch($rs);
                    $_SESSION['logged_user'] = $row;
                }
            }
        }
    }

    protected function includeModelClass($class_name)
    {
        $models_path = self::PATH_TO_MODEL_CLASSES . '/models/';
        $parent_model_path = $models_path . 'model.cls.php';
        if (file_exists($parent_model_path) && is_file($parent_model_path)) {
            require_once($parent_model_path);
            $class_path = $models_path . $class_name . '.model.cls.php';
            if (file_exists($class_path) && is_file($class_path)) {
                require_once($class_path);
                return true;
            }
        }
        return false;
    }

}
