<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ControllerTestCase
 *
 * @author Max
 */
class TestCase extends Zend_Test_PHPUnit_ControllerTestCase
{
    public $application;

    public function setUp()
    {
        $this->bootstrap = array($this, 'appBootstrap');
        parent::setUp();
    }

    public function appBootstrap()
    {
        $this->application = new Zend_Application(APPLICATION_ENV,
              APPLICATION_PATH . '/configs/application.ini'
        );
        $this->application->bootstrap();

        $front = Zend_Controller_Front::getInstance();
        if($front->getParam('bootstrap') === null) {
            $front->setParam('bootstrap', $this->application->getBootstrap());
        }
        Zend_Session::$_unitTestEnabled = true;
        Zend_Session::start();
    }
    
    public function tearDown()
    {
        Zend_Controller_Front::getInstance()->resetInstance();
        $this->resetRequest();
        $this->resetResponse();

        $this->request->setPost(array());
        $this->request->setQuery(array());
    }

//    public function bootstrap()
//    {
//        $this->application->bootstrap();
//    }
}
?>
