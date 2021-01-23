<?php

class ModelClass
{

    protected $Api = null;
    protected $db;

    public function __construct($api)
    {
        global $db;
        $this->db = $db;
        $this->Api = $api;
    }

    final protected function prepareSuccessResponse($data)
    {
        $data = $this->cleanOutput($data);
        if (!is_array($data) || !array_key_exists('message', $data)) {
            $data = array('message' => $data);
        }
        return array('status' => 1, 'content' => $data);
    }

    final protected function prepareErrorResponse($data)
    {
        $data = $this->cleanOutput($data);
        if (!is_array($data) || !array_key_exists('message', $data)) {
            $data = array('message' => $data);
        }
        return array('status' => 0, 'content' => $data);
    }

    final protected function cleanOutput($data)
    {
        if (is_array($data)) {
            $cleaned_data = [];
            foreach ($data as $k => $v) {
                $cleaned_data[$k] = $this->cleanOutput($v);
            }
        } else {
            $cleaned_data = trim(preg_replace(array(
                '/<(.*?)<\/(.*?)>/is',
                '/<(.*?)>/is'
                            ), '', $data));
        }
        return $cleaned_data;
    }

    final protected function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

}
