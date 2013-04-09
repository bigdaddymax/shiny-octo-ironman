<?php

abstract class BaseDBAbstract {
    public $config;
    public $dbLink;
    
    public function __construct()
    {
        $this->config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        $this->dbLink = Zend_Db::factory('Pdo_Mysql', $this->config->database->params);
    }
}
