<?php

class IndexControllerTest extends Zend_Test_PHPUnit_ControllerTestCase {

    private $objectManager;

    public function setUp() {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        $this->objectManager = new Application_Model_ObjectsManager(-1);
        $this->objectManager->dbLink->delete('domain_owner');
        $this->objectManager->dbLink->delete('user_group');
        $this->objectManager->dbLink->delete('approval_entry');
        $this->objectManager->dbLink->delete('scenario_entry');
        $this->objectManager->dbLink->delete('user');
        $this->objectManager->dbLink->delete('domain_owner');
        $this->objectManager->dbLink->delete('position');
        $this->objectManager->dbLink->delete('node');
        $this->objectManager->dbLink->delete('resource');
        $this->objectManager->dbLink->delete('domain');
        parent::setUp();
    }

    public function tearDown() {
        $this->objectManager = new Application_Model_DataMapper(-1);
        $this->objectManager->dbLink->delete('domain_owner');
        $this->objectManager->dbLink->delete('user_group');
        $this->objectManager->dbLink->delete('approval_entry');
        $this->objectManager->dbLink->delete('scenario_entry');
        $this->objectManager->dbLink->delete('user');
        $this->objectManager->dbLink->delete('domain_owner');
        $this->objectManager->dbLink->delete('position');
        $this->objectManager->dbLink->delete('node');
        $this->objectManager->dbLink->delete('resource');
        $this->objectManager->dbLink->delete('domain');
        $this->objectManager->dbLink->insert('domain', array('domainId' => 1, 'domainName' => 'Domain1', 'active' => 1));
    }

    public function testIndexAction() {
        $params = array('action' => 'index', 'controller' => 'index', 'module' => 'default');
        $urlParams = $this->urlizeOptions($params);
        $url = $this->url($urlParams);
        $this->dispatch($url);

        // assertions
        $this->assertModule($urlParams['module']);
        $this->assertController($urlParams['controller']);
        $this->assertAction($urlParams['action']);
    }

    public function testUserFormValidators() {
        $inputArray = array();
        $params = array('controller' => 'index', 'action' => 'new-domain');
        $this->request->setMethod('post');
        $this->request->setPost($inputArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertController('index');
        $this->assertAction('new-domain');
        $this->assertQuery(".error", "Value is required and can't be empty");

        // Lets add a user with domain
        $this->resetRequest();
        $this->resetResponse();
        $inputArray = array('userName' => 'testName', 'email' => 'test@domain', 'password' => 'test_pwd', 'companyName' => 'New node name');
        $params = array('controller' => 'index', 'action' => 'new-domain');
        $this->request->setMethod('post');
        $this->request->setPost($inputArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertController('index');
        $this->assertAction('new-domain');
        $this->resetRequest();
        $this->resetResponse();

        // Now lets check if we can add user with existing name
        $inputArray = array('userName' => 'testName', 'email' => 'test2@domain', 'password' => 'test_pwd', 'companyName' => 'New node name');
        $params = array('controller' => 'index', 'action' => 'new-domain');
        $this->request->setMethod('post');
        $this->request->setPost($inputArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertController('index');
        $this->assertAction('new-domain');
//        $this->assertRedirectTo($this->url(array('controller'=>'index', 'action'=>'index')));
        $this->assertQuery(".error", "User %s already registered");
    }

    public function testNewDomainCreation() {
        $inputArray = array('userName' => 'testName', 'email' => 'test@domain', 'password' => 'test_pwd', 'companyName' => 'New node name');
        $params = array('controller' => 'index', 'action' => 'new-domain');
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
        $this->assertEquals($session->userName, 'testName');
        $objectManager = new Application_Model_ObjectsManager($session->domainId);
        $node = $objectManager->getAllObjects('Node');
        $user = $objectManager->getAllObjects('User');
        $domain = $objectManager->getAllObjects('Domain');
        $position = $objectManager->getAllObjects('Position');

        $this->assertEquals($position[0]->nodeId, $node[0]->nodeId);
        $this->assertEquals($user[0]->positionId, $position[0]->positionId);
        $this->assertEquals($node[0]->domainId, $domain[0]->domainId);
    }

}

