<?php

class IndexControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
private $dataMapper;
    public function setUp()
    {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        $this->dataMapper = new Application_Model_DataMapper(-1);
        $this->dataMapper->dbLink->delete('domain_owner');
        $this->dataMapper->dbLink->delete('user_group');
        $this->dataMapper->dbLink->delete('user');
        $this->dataMapper->dbLink->delete('domain');
        parent::setUp();
    }
    
    public function tearDown(){
        $this->dataMapper = new Application_Model_DataMapper(-1);
        $this->dataMapper->dbLink->delete('domain_owner');
        $this->dataMapper->dbLink->delete('user_group');
        $this->dataMapper->dbLink->delete('user');
        $this->dataMapper->dbLink->delete('domain');
        $this->dataMapper->dbLink->insert('domain', array('domainId'=>1, 'domainName'=>'Domain1', 'active'=>1));
        
    }

    public function testIndexAction()
    {
        $params = array('action' => 'index', 'controller' => 'index', 'module' => 'default');
        $urlParams = $this->urlizeOptions($params);
        $url = $this->url($urlParams);
        $this->dispatch($url);
        
        // assertions
        $this->assertModule($urlParams['module']);
        $this->assertController($urlParams['controller']);
        $this->assertAction($urlParams['action']);
    }


    public function testNewUserRegistration(){
        $userArray = array('userName'=>'testName', 'login'=>'login', 'email'=>'test@domain', 'password'=>'test_pwd');
        $params = array('controller'=>'index', 'action'=>'new-user');
        $this->request->setMethod('post');
        $this->request->setPost($userArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertController('index');
        $this->assertAction('new-user');
        $session = new Zend_Session_Namespace('Auth');
        $this->assertEquals($session->newUser['userName'], 'testName');
        $this->assertEquals($session->newUser['login'], 'login');
        $this->assertEquals($session->newUser['password'], 'test_pwd');
        $this->assertEquals($session->newUser['email'], 'test@domain');
        return $session;
    }
    
    public function testNewDomainCreation(){
        $userArray = array('userName'=>'testName', 'login'=>'login', 'email'=>'test@domain', 'password'=>'test_pwd');
        $params = array('controller'=>'index', 'action'=>'new-user');
        $this->request->setMethod('post');
        $this->request->setPost($userArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertController('index');
        $this->assertAction('new-user');
        $session = new Zend_Session_Namespace('Auth');
        $domainArray = array('domainName'=>'Domain Name');
        $params = array('controller'=>'index', 'action'=>'new-domain');
        $this->request->setMethod('post');
        $this->request->setPost($domainArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
//        $response = $this->getResponse();
//        echo $response->outputBody();
        $domains = $this->dataMapper->dbLink->fetchAll('SELECT * FROM domain');
        $this->assertEquals($domains[0]['domainName'], 'Domain Name');
    }
}



