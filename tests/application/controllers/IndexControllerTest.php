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
        $this->dataMapper->dbLink->delete('domain_owner');
        $this->dataMapper->dbLink->delete('position');
        $this->dataMapper->dbLink->delete('node');
        $this->dataMapper->dbLink->delete('domain');
        parent::setUp();
    }
    
    public function tearDown(){
        $this->dataMapper = new Application_Model_DataMapper(-1);
        $this->dataMapper->dbLink->delete('domain_owner');
        $this->dataMapper->dbLink->delete('user_group');
        $this->dataMapper->dbLink->delete('user');
         $this->dataMapper->dbLink->delete('domain_owner');
        $this->dataMapper->dbLink->delete('position');
        $this->dataMapper->dbLink->delete('node');
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

  
    public function testNewDomainCreation(){
        $inputArray = array('userName'=>'testName', 'email'=>'test@domain', 'password'=>'test_pwd', 'companyName'=>'New node name');
        $params = array('controller'=>'index', 'action'=>'new-domain');
        $this->request->setMethod('post');
        $this->request->setPost($inputArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertController('index');
        $this->assertAction('new-domain');
        $this->resetRequest();
        $this->resetResponse();

        $userArray = array('login' => 'test@domain', 'password' => 'test_pwd');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($userArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();
        
        $session = new Zend_Session_Namespace('Auth');
        $this->assertEquals($session->userName, 'testName');;
        $dataMapper = new Application_Model_DataMapper($session->domainId);
        $node = $dataMapper->getAllObjects('Application_Model_Node');
        $user = $dataMapper->getAllObjects('Application_Model_User');
        $domain = $dataMapper->getAllObjects('Application_Model_Domain');
        $position = $dataMapper->getAllObjects('Application_Model_Position');

        $this->assertEquals($position[0]->nodeId, $node[0]->nodeId);
        $this->assertEquals($user[0]->positionId, $position[0]->positionId);
        $this->assertEquals($node[0]->domainId, $domain[0]->domainId);
    }
}



