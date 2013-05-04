<?php

class AuthControllerTest extends Zend_Test_PHPUnit_ControllerTestCase {

    //put your code here
    private $userId;

    public function setup() {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        $this->dataMapper = new Application_Model_DataMapper(1);
        $this->dataMapper->dbLink->delete('item');
        $this->dataMapper->dbLink->delete('form');
        $this->dataMapper->dbLink->delete('privilege');
        $this->dataMapper->dbLink->delete('resource');
        $this->dataMapper->dbLink->delete('user_group');
        $this->dataMapper->dbLink->delete('scenario_entry');
        $this->dataMapper->dbLink->delete('scenario_assignment');
        $this->dataMapper->dbLink->delete('scenario');
        $this->dataMapper->dbLink->delete('domain_owner');
        $this->dataMapper->dbLink->delete('user');
        $this->dataMapper->dbLink->delete('position');
        $this->dataMapper->dbLink->delete('node');
        $this->dataMapper->dbLink->delete('domain_owner');
        $this->dataMapper->dbLink->delete('user_group');
        $this->dataMapper->dbLink->delete('user');
        $this->dataMapper->dbLink->delete('contragent');
        $this->dataMapper->dbLink->delete('domain');
        $this->dataMapper->dbLink->insert('domain', array('domainId'=>1, 'domainName'=>'Domain1', 'active'=>1));

        $nodeArray = array('nodeName' => 'First node', 'parentNodeId' => -1, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $nodeId = $this->dataMapper->saveObject($node);
        $positionArray = array('positionName' => 'First position', 'nodeId' => $nodeId, 'domainId' => 1);
        $position = new Application_Model_Position($positionArray);
        $positionId = $this->dataMapper->saveObject($position);
        
        
        $userArray = array('userId' => 3, 'userName' => 'oName', 'active' => false, 'domainId' => 1, 'login' => 'tLogin', 'positionId' => $positionId, 'groupId' => 2, 'password' => 'testp');
        $user = new Application_Model_User($userArray);
        $this->userId = $this->dataMapper->saveObject($user);
        parent::setUp();
    }

    public function tearDown() {
        $this->dataMapper->dbLink->delete('scenario_entry');
        $this->dataMapper->dbLink->delete('scenario_assignment');
        $this->dataMapper->dbLink->delete('scenario');
        $this->dataMapper->dbLink->delete('domain_owner');
        $this->dataMapper->dbLink->delete('user');
        $this->dataMapper->dbLink->delete('user_group');
        
        $this->dataMapper->dbLink->delete('position');
        $this->dataMapper->dbLink->delete('node');
        $this->dataMapper->dbLink->delete('domain_owner');
        $this->dataMapper->dbLink->delete('user_group');
        $this->dataMapper->dbLink->delete('user');
        $this->dataMapper->dbLink->delete('contragent');
        $this->dataMapper->dbLink->delete('domain');
        $this->dataMapper->dbLink->insert('domain', array('domainId'=>1, 'domainName'=>'Domain1', 'active'=>1));
    }
    

    public function testUserAuth() {
        $user = array('login' => 'tLogin', 'password' => 'testp');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $response = $this->getResponse();
//        echo $response->outputBody();
        $session = new Zend_Session_Namespace('Auth');
        $this->assertTrue((bool)$session->auth);
    }

/**
 * @ignore
 */    
  
    public function testDefaultAdminAuth() {
//        rer;
         $inputArray = array('userName'=>'testName', 'email'=>'test@domain', 'password'=>'test_pwd', 'companyName'=>'New node name');
        $params = array('controller'=>'index', 'action'=>'new-domain');
        $this->request->setMethod('post');
        $this->request->setPost($inputArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $response = $this->getResponse();
        echo $response->outputBody();
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
