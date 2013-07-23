<?php

class JSONExceptions extends Zend_Exception {

    protected $errorCode;

    public function __construct($msg = '', $code = 0, \Exception $previous = null) {
        parent::__construct($msg, $code, $previous);
    }

    public function errorToArray() {
        return array('error' => 1,
            'message' => $this->message,
            'code' => $this->code,
            'errorCode' => $this->errorCode);
    }

}

class DependantObjectDeletionAttempt extends JSONExceptions {

    public function __construct($msg = '', $code = 0, \Exception $previous = null) {
        //Lets try to translate error message
        $registry = Zend_Registry::getInstance();
        $translate = $registry->get('Zend_Translate');
        $this->message = $translate->_('Cannot delete object - other objects depend on it');
        $this->errorCode = $code;
        $this->code = 409;

        // Check if we have to print debug info about error
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        if ($config->app->exceptions_passthrough) {
            $this->message .= '<pre>' . $msg . '</pre>';
            if ($previous) {
                $this->message .= '<pre>' . $previous->getTraceAsString() . '</pre>';
            }
        }
        parent::__construct($this->message, $this->code, $previous);
    }

}
