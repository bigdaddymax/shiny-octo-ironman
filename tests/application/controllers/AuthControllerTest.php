<?php

class AuthControllerTest extends Zend_Test_PHPUnit_ControllerTestCase {

    //put your code here
    private $userId;

    public function setup() {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        $this->objectManager1 = new Application_Model_ObjectsManager(1);
        $this->objectManager1->dbLink->delete('item');
        $this->objectManager1->dbLink->delete('comment');
        $this->objectManager1->dbLink->delete('approval_entry');
        $this->objectManager1->dbLink->delete('form');
        $this->objectManager1->dbLink->delete('privilege');
        $this->objectManager1->dbLink->delete('resource');
        $this->objectManager1->dbLink->delete('user_group');
        $this->objectManager1->dbLink->delete('scenario_entry');
        $this->objectManager1->dbLink->delete('scenario_assignment');
        $this->objectManager1->dbLink->delete('scenario');
        $this->objectManager1->dbLink->delete('domain_owner');
        $this->objectManager1->dbLink->delete('user');
        $this->objectManager1->dbLink->delete('position');
        $this->objectManager1->dbLink->delete('node');
        $this->objectManager1->dbLink->delete('element');
        $this->objectManager1->dbLink->delete('domain_owner');
        $this->objectManager1->dbLink->delete('user_group');
        $this->objectManager1->dbLink->delete('user');
        $this->objectManager1->dbLink->delete('contragent');
        $this->objectManager1->dbLink->delete('template');
        $this->objectManager1->dbLink->delete('domain');
        $this->objectManager1->dbLink->insert('domain', array('domainId'=>1, 'domainName'=>'Domain1', 'active'=>1));

        $nodeArray = array('nodeName' => 'First node', 'parentNodeId' => -1, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $nodeId = $this->objectManager1->saveObject($node);
        $this->assertTrue($node instanceof Application_Model_Node);
        $this->assertTrue(is_int($nodeId));
        $positionArray = array('positionName' => 'First position', 'nodeId' => $nodeId, 'domainId' => 1);
        $position = new Application_Model_Position($positionArray);
        $positionId = $this->objectManager1->saveObject($position);
        
        
        $userArray = array('userName' => 'oName', 'active' => false, 'domainId' => 1, 'login' => 'tLogin', 'positionId' => $positionId, 'groupId' => 2, 'password' => 'testp');
        $user = new Application_Model_User($userArray);
        $this->userId = $this->objectManager1->saveObject($user);
        $this->assertTrue(is_int($this->userId));
        parent::setUp();
    }

    public function tearDown() {
        $this->objectManager1->dbLink->delete('scenario_entry');
        $this->objectManager1->dbLink->delete('scenario_assignment');
        $this->objectManager1->dbLink->delete('scenario');
        $this->objectManager1->dbLink->delete('domain_owner');
        $this->objectManager1->dbLink->delete('approval_entry');
        $this->objectManager1->dbLink->delete('user_group');
        $this->objectManager1->dbLink->delete('user');

        
        $this->objectManager1->dbLink->delete('position');
        $this->objectManager1->dbLink->delete('node');
        $this->objectManager1->dbLink->delete('domain_owner');
        $this->objectManager1->dbLink->delete('user_group');
        $this->objectManager1->dbLink->delete('user');
        $this->objectManager1->dbLink->delete('contragent');
        $this->objectManager1->dbLink->delete('domain');
        $this->objectManager1->dbLink->insert('domain', array('domainId'=>1, 'domainName'=>'Domain1', 'active'=>1));
    }
    

    public function testUserAuth() {
        $user = array('login' => 'tLogin', 'password' => 'testp');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $session = new Zend_Session_Namespace('Auth');
        $this->assertTrue((bool)$session->auth);
    }

/**
 * @ignore
 */    
  
    public function testDefaultAdminAuth() {
        $inputArray = array('userName'=>'testName', 'email'=>'test@domain', 'password'=>'test_pwd', 'companyName'=>'New node name');
        $params = array('controller'=>'index', 'action'=>'new-domain');
        $this->request->setMethod('post');
        $this->request->setPost($inputArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertController('index');
        $this->assertAction('new-domain');
        $this->resetRequest();
        $this->resetResponse();
        $user = array('login' => 'test@domain', 'password' => 'test_pwd');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
       $session = new Zend_Session_Namespace('Auth');
       $this->assertEquals($session->userName, 'testName');
       $this->assertEquals($session->login, 'test@domain');
       $this->assertEquals($session->role, 'admin');
    }

}

?>
