<?php

abstract class API
{

    protected $request_data = [];
    protected $request_method = '';
    protected $model = '';
    protected $action = '';
    protected $args = [];
    protected $file = null;

    public function __construct($request = '')
    {
        //Please do not change the sequence of these func calls
        $this->setHeaders();
        $this->setRequestMethod();
        $this->parseRequest($request);
        $this->setRequestData();
    }

    private function setHeaders()
    {
        header("Content-Type: application/json; charset=utf-8");
    }

    private function setRequestMethod()
    {
        $this->request_method = $_SERVER['REQUEST_METHOD'];
        if ($this->request_method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $this->request_method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $this->request_method = 'PUT';
            } else {
                throw new Exception("Unexpected Header!");
            }
        }
    }

    private function parseRequest($request = '')
    {
        $this->args = explode('/', rtrim($request, '/'));
        if (sizeof($this->args) < 1) {
            throw new Exception('Unsupported ' . ucfirst($this->request_method) . ' Request!');
        }
        $this->model = array_shift($this->args);
        if (array_key_exists(0, $this->args) && is_string($this->args[0])) {
            $this->action = array_shift($this->args);
        }
    }

    private function setRequestData()
    {
        switch ($this->request_method) {
            case 'DELETE':
            case 'POST':
                $this->request_data = $this->_getPostedData();
                break;
            case 'GET':
                $this->request_data = $this->_getQueryStringData();
                break;
            case 'PUT':
                $this->request_data = $this->_getQueryStringData();
                $this->file = file_get_contents("php://input");
                break;
            default:
                throw new Exception('Invalid Request Method!');
                break;
        }
    }

    final protected function _checkMagicQuotesOn()
    {
        if (get_magic_quotes_gpc()) {
            return true;
        }
        return false;
    }

    final protected function _getPostedData()
    {
        $clean_slashes = false;
        if ($this->_checkMagicQuotesOn()) {
            $clean_slashes = true;
        }
        if (function_exists('getPostedData')) {
            $post = getPostedData();
            $clean_slashes = false;
        } else {
            $post = $_POST;
        }
        return $this->_cleanInputs($post, $clean_slashes);
    }

    final protected function _getQueryStringData()
    {
        $clean_slashes = false;
        if ($this->_checkMagicQuotesOn()) {
            $clean_slashes = true;
        }
        if (function_exists('getQueryStringData')) {
            $get = getQueryStringData();
            $clean_slashes = false;
        } else {
            $get = $_GET;
        }
        return $this->_cleanInputs($get, $clean_slashes);
    }

    final protected function _cleanInputs($data, $clean_slashes)
    {
        $cleaned_data = [];
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $cleaned_data[$k] = $this->_cleanInputs($v, $clean_slashes);
            }
        } else {
            if ($clean_slashes === true) {
                $data = stripslashes($data);
            }
            $cleaned_data = trim(strip_tags($data));
        }
        return $cleaned_data;
    }

    final protected function _getClassObject($model)
    {
        $class_name = ucfirst($model) . 'Class';
        if ($this->includeModelClass($model) && class_exists($class_name)) {
            return (new $class_name($this));
        }
        return false;
    }

    final public function executeRequest()
    {
        $obj = $this->_getClassObject($this->model);
        if (is_object($obj) && method_exists($obj, $this->action)) {
            return $this->_response($obj->{$this->action}($this->args));
        }
        return $this->_errorResponse("Content Not Found: $this->model", 404);
    }

    private function _errorResponse($data, $status = 404)
    {
        return $this->_response(array('error' => array('message' => $data, 'code' => $status)));
    }

    private function _response($data, $status = 200)
    {
        header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
        ob_end_clean();
        if (function_exists('convertToJson')) {
            return convertToJson($data);
        }
        return json_encode($data);
    }

    private function _requestStatus($code)
    {
        $status = array(
            200 => 'OK',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        );
        return (array_key_exists($code, $status) ? $status[$code] : $status[500]);
    }

    final public function getRequestMethod()
    {
        return $this->request_method;
    }

    final public function getRequestData()
    {
        return $this->request_data;
    }

    /* This method should return bool true or false after including required model class. */

    abstract protected function includeModelClass($class_name);
    /* This method should return response after validating token. */

    abstract protected function validateClientToken();
}
