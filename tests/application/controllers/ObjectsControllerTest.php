<?php

class ObjectsControllerTest extends Zend_Test_PHPUnit_ControllerTestCase {

    private $dataMapper;

    public function setUp() {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        parent::setUp();
        // Lets create new user
        $this->dataMapper = new Application_Model_DataMapper(-1);
        $this->dataMapper->dbLink->delete('item');
        $this->dataMapper->dbLink->delete('form');
        $this->dataMapper->dbLink->delete('scenario_assignment');
        $this->dataMapper->dbLink->delete('scenario_entry');
        $this->dataMapper->dbLink->delete('scenario');
        $this->dataMapper->dbLink->delete('privilege');
        $this->dataMapper->dbLink->delete('resource');
        $this->dataMapper->dbLink->delete('user_group');
        $this->dataMapper->dbLink->delete('user');
        $this->dataMapper->dbLink->delete('position');
        $this->dataMapper->dbLink->delete('node');
        $this->dataMapper->dbLink->delete('domain_owner');
        $this->dataMapper->dbLink->delete('user_group');
        $this->dataMapper->dbLink->delete('user');
        $this->dataMapper->dbLink->delete('domain');
        $userArray = array('userName'=>'testName', 'login'=>'login', 'email'=>'test@domain', 'password'=>'test_pwd');
        $params = array('controller'=>'index', 'action'=>'new-user');
        $this->request->setMethod('post');
        $this->request->setPost($userArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertController('index');
        $this->assertAction('new-user');
        // Add new domain
        $domainArray = array('domainName'=>'Domain Name');
        $params = array('controller'=>'index', 'action'=>'new-domain');
        $this->request->setMethod('post');
        $this->request->setPost($domainArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $session = new Zend_Session_Namespace('Auth');
        $this->dataMapper = new Application_Model_DataMapper($session->domainId);

        $this->resetRequest();
        $this->resetResponse();
    }

    public function tearDown() {
        $this->dataMapper->dbLink->delete('item');
        $this->dataMapper->dbLink->delete('form');
        $this->dataMapper->dbLink->delete('scenario_assignment');
        $this->dataMapper->dbLink->delete('scenario_entry');
        $this->dataMapper->dbLink->delete('scenario');
        $this->dataMapper->dbLink->delete('privilege');
        $this->dataMapper->dbLink->delete('resource');
        $this->dataMapper->dbLink->delete('user_group');
        $this->dataMapper->dbLink->delete('user');
        $this->dataMapper->dbLink->delete('position');
        $this->dataMapper->dbLink->delete('node');
        $this->dataMapper->dbLink->delete('domain');
        $this->dataMapper->dbLink->insert('domain', array('domainId'=>1, 'domainName'=>'Domain1', 'active'=>1));
    }

    public function testIndexAction() {
        $user = array('login' => 'login', 'password' => 'test_pwd');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $session=new Zend_Session_Namespace('Auth');
        $this->assertEquals($session->login, 'login');
        $this->assertEquals($session->role, 'admin');
        $params = array('action' => 'index', 'controller' => 'objects', 'objectType' => 'node');
        $urlParams = $this->urlizeOptions($params);
        $url = $this->url($urlParams);
        $this->dispatch($url);
        // assertions
        $this->assertController($urlParams['controller']);
        $this->assertAction($urlParams['action']);
    }

    public function testAddObject() {
        $user = array('login' => 'login', 'password' => 'test_pwd');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();
        $session = new Zend_Session_Namespace('Auth');
        $params = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray = array('objectType' => 'element', 'elementName' => 'testAddObject',
            'elementCode' => 44, 'domainId' => $session->domainId);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertController('objects');

        $dataMapper = new Application_Model_DataMapper($session->domainId);
        $elements = $dataMapper->getAllObjects('Application_Model_Element');
        $element = new Application_Model_Element(array('elementName' => 'testAddObject', 'elementCode' => 44, 'domainId' => $session->domainId));
        $element->elementId = $elements[0]->elementId;
        $this->assertEquals($elements, array(0 => $element));
        return $element;
    }

    /**
     * 
     * @depends testAddObject
     */
    public function testDeleteObject($element) {
        $session = new Zend_Session_Namespace('Auth');
        $session->auth = 1;
        $session->login = 'admin';
        $session->domainId = 1;
        $params1 = array('controller' => 'objects', 'action' => 'delete',
            'objectType' => 'element', 'elementId' => $element->elementId);
        $this->dispatch($this->url($this->urlizeOptions($params1)));
        $elements2 = $this->dataMapper->getAllObjects('Application_Model_Element');
        $this->assertTrue(empty($elements2));
    }

    public function testDeleteDependentObject() {
// Lets save basic node
        $user = array('login' => 'login', 'password' => 'test_pwd');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();
        $session = new Zend_Session_Namespace('Auth');
        $params = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode',
            'parentNodeId' => -1, 'domainId' => $session->domainId);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $dataMapper = new Application_Model_DataMapper($session->domainId);
        $nodes = $dataMapper->getAllObjects('Application_Model_Node');
        $nodeId = $nodes[0]->nodeId;
        $this->assertEquals(1, count($nodes));
// Lets create dependent node
        $this->resetRequest();
        $this->resetResponse();
        $params1 = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray1 = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode1',
            'parentNodeId' => $nodeId, 'domainId' => $session->domainId);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray1);
        $this->dispatch($this->url($this->urlizeOptions($params1)));
        $nodes2 = $dataMapper->getAllObjects('Application_Model_Node');
        $this->assertEquals(2, count($nodes2));
        $this->resetRequest();
        $this->resetResponse();

        $params3 = array('controller' => 'objects', 'action' => 'delete',
            'objectType' => 'node', 'nodeId' => $nodeId);

        $this->dispatch($this->url($this->urlizeOptions($params3)));
        $nodes3 = $dataMapper->getAllObjects('Application_Model_Node');
        $this->assertEquals(2, count($nodes3));
    }

    public function testDeleteIndependentObject() {
// Lets save basic node
        $user = array('login' => 'login', 'password' => 'test_pwd');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();
        $session = new Zend_Session_Namespace('Auth');
        $dataMapper = new Application_Model_DataMapper($session->domainId);

        $params = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode',
            'parentNodeId' => -1, 'domainId' => $session->domainId);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $nodes = $dataMapper->getAllObjects('Application_Model_Node');
        $nodeId = $nodes[0]->nodeId;
// Lets create dependent node
        $params1 = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray1 = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode1',
            'parentNodeId' => $nodeId, 'domainId' => 1);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray1);
        $this->dispatch($this->url($this->urlizeOptions($params1)));
        $nodes1 = $dataMapper->getAllObjects('Application_Model_Node');
        $nodeId1 = $nodes1[1]->nodeId;
// Lets create another independent node
        $params2 = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray2 = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode1',
            'parentNodeId' => 22, 'domainId' => 1);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray2);
        $this->dispatch($this->url($this->urlizeOptions($params2)));
//        $this->assertEquals($output2, 'tt');

        $nodes2 = $dataMapper->getAllObjects('Application_Model_Node');
        $this->assertEquals(3, count($nodes2));

        $params3 = array('controller' => 'objects', 'action' => 'delete',
            'objectType' => 'node', 'nodeId' => $nodeId1);
        $this->resetResponse();
        $this->dispatch($this->url($this->urlizeOptions($params3)));

//        $this->assertRedirect();

        $nodes3 = $dataMapper->getAllObjects('Application_Model_Node');
        $this->assertEquals(2, count($nodes3));
    }

    public function testDeleteIndependentObjectMixed() {
        $user = array('login' => 'login', 'password' => 'test_pwd');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();
        $session = new Zend_Session_Namespace('Auth');
        $dataMapper = new Application_Model_DataMapper($session->domainId);
 // Lets save basic node
        $params = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode',
            'parentNodeId' => -1, 'domainId' => $session->domainId);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $nodes = $dataMapper->getAllObjects('Application_Model_Node');
        $nodeId = $nodes[0]->nodeId;
// Lets create dependent node
        $params1 = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray1 = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode1',
            'parentNodeId' => $nodeId, 'domainId' => 1);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray1);
        $this->dispatch($this->url($this->urlizeOptions($params1)));
        $nodes1 = $dataMapper->getAllObjects('Application_Model_Node');
        $nodeId1 = $nodes1[1]->nodeId;
// Lets create another independent node
        $params2 = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray2 = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode1',
            'parentNodeId' => 22, 'domainId' => 1);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray2);
        $this->dispatch($this->url($this->urlizeOptions($params2)));
//        $this->assertEquals($output2, 'tt');

        $nodes2 = $dataMapper->getAllObjects('Application_Model_Node');
        $this->assertEquals(3, count($nodes2));

        $params3 = array('controller' => 'objects', 'action' => 'delete',
            'objectType' => 'node', 'nodeId' => $nodeId);
        $this->resetResponse();
        $this->dispatch($this->url($this->urlizeOptions($params3)));

        $nodes3 = $dataMapper->getAllObjects('Application_Model_Node');
        $this->assertEquals(3, count($nodes3));

        $params4 = array('controller' => 'objects', 'action' => 'delete',
            'objectType' => 'node', 'nodeId' => $nodeId1);
        $this->resetResponse();
        $this->dispatch($this->url($this->urlizeOptions($params4)));

        $nodes4 = $dataMapper->getAllObjects('Application_Model_Node');
        $this->assertEquals(2, count($nodes4));
    }

    public function testAddDeleteObjects() {
        $user = array('login' => 'login', 'password' => 'test_pwd');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $session = new Zend_Session_Namespace('Auth');
        $dataMapper = new Application_Model_DataMapper($session->domainId);
        $this->resetRequest();
        $this->resetResponse();

// Lets save basic node
        $params = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode',
            'parentNodeId' => -1, 'domainId' => $session->domainId);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $nodes = $dataMapper->getAllObjects('Application_Model_Node');
        $nodeId = $nodes[0]->nodeId;
// Lets create dependent node
        $params1 = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray1 = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode1',
            'parentNodeId' => $nodeId, 'domainId' => 1);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray1);
        $this->dispatch($this->url($this->urlizeOptions($params1)));
        $nodes1 = $dataMapper->getAllObjects('Application_Model_Node');
        $nodeId1 = $nodes1[1]->nodeId;
// Lets create another independent node
        $params2 = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray2 = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode1',
            'parentNodeId' => 22, 'domainId' => $session->domainId);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray2);
        $this->dispatch($this->url($this->urlizeOptions($params2)));
//        $this->assertEquals($output2, 'tt');

        $nodes2 = $dataMapper->getAllObjects('Application_Model_Node');
        $this->assertEquals(3, count($nodes2));

        $params3 = array('controller' => 'objects', 'action' => 'delete',
            'objectType' => 'node', 'nodeId' => $nodeId);
        $this->resetResponse();
        $this->dispatch($this->url($this->urlizeOptions($params3)));

        $nodes3 = $dataMapper->getAllObjects('Application_Model_Node');
        $this->assertEquals(3, count($nodes3));

        $params4 = array('controller' => 'objects', 'action' => 'delete',
            'objectType' => 'node', 'nodeId' => $nodeId1);
        $this->resetResponse();
        $this->dispatch($this->url($this->urlizeOptions($params4)));

        $nodes4 = $dataMapper->getAllObjects('Application_Model_Node');
        $this->assertEquals(2, count($nodes4));
    }

    public function testEditObject() {
        // Lets login as admin
        $user = array('login' => 'login', 'password' => 'test_pwd');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $session = new Zend_Session_Namespace('Auth');
        $dataMapper = new Application_Model_DataMapper($session->domainId);
        
        // Lets prepare everything we need
        $nodeArray = array('nodeName' => 'First node', 'parentNodeId' => -1, 'domainId' => $session->domainId);
        $node = new Application_Model_Node($nodeArray);
        $nodeId = $dataMapper->saveObject($node);
        $nodeArray1 = array('nodeName' => 'First node', 'parentNodeId' => -1, 'domainId' => $session->domainId);
        $node1 = new Application_Model_Node($nodeArray1);
        $nodeId1 = $dataMapper->saveObject($node1);
        $positionArray = array('positionName' => 'First position', 'nodeId' => $nodeId, 'domainId' => $session->domainId);
        $position = new Application_Model_Position($positionArray);
        $positionId = $dataMapper->saveObject($position);
        $userArray = array('userName' => 'user1', 'domainId' => $session->domainId, 'login' => 'user login', 'password' => 'user password', 'positionId' => $positionId);
        $user = new Application_Model_User($userArray);
        $userId = $dataMapper->saveObject($user);
        $entryArray = array( 'domainId'=>$session->domainId, 'userId'=>$userId, 'orderPos'=>1);
        $scenarioArray = array('scenarioName'=>'scenario 1', 'domainId'=>$session->domainId, 'entries'=>array(0=>$entryArray));
        $scenario = new Application_Model_Scenario($scenarioArray);
        $objectsManager = new Application_Model_ObjectsManager($session->domainId);
        $scenarioId = $objectsManager->saveScenario($scenario);
//        $assignmentArray = array('nodeId'=>$nodeId, 'scenarioId'=>$scenarioId, 'domainId'=>1);
//        $assignment = new Application_Model_ScenarioAssignment($assignmentArray);
//        $assignmentId = $this->dataMapper->saveObject($assignment);
//        $this->assertTrue(is_int($assignmentId));
                
        // Check if we are in
        $this->assertTrue(($session->auth == 1));
        $this->assertEquals($session->role, 'admin');
        
        $this->resetRequest();
        $this->resetResponse();
        
        $formArray = array('_nodeName' => 'Modified node name', '_parentNodeId'=>$nodeId1, '_scenarioId'=>$scenarioId, '_nodeId'=>$nodeId, 'objectType'=>'node');
        $params = array('controller'=>'objects', 'action'=>'edit-object');
        $this->request->setMethod('post');
        $this->request->setPost($formArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertController('objects');
        $this->assertAction('edit-object');
        $nodeEdited = $dataMapper->getObject($nodeId, 'Application_Model_Node');
        $this->assertEquals($nodeEdited->nodeName, 'Modified node name');
        $assignment = $dataMapper->getAllObjects('Application_Model_ScenarioAssignment', array(0=>array('column'=>'nodeId', 'operand'=>$nodeId)));
        $this->assertTrue($assignment[0] instanceof Application_Model_ScenarioAssignment);
        $this->assertEquals($assignment[0]->scenarioId, $scenarioId);
        
        $formArray1 = array('_nodeName' => 'Modified node name', '_parentNodeId'=>$nodeId1, '_scenarioId'=>-1, '_nodeId'=>$nodeId, 'objectType'=>'node');
        $params1 = array('controller'=>'objects', 'action'=>'edit-object');
        $this->request->setMethod('post');
        $this->request->setPost($formArray1);
        $this->dispatch($this->url($this->urlizeOptions($params1)));
        $assignment = $dataMapper->getAllObjects('Application_Model_ScenarioAssignment', array(0=>array('column'=>'nodeId', 'operand'=>$nodeId)));
        $this->assertFalse($assignment);
        $nodeEdited1 = $dataMapper->getObject($nodeId, 'Application_Model_Node');
        $this->assertEquals($nodeEdited1->parentNodeId, $nodeId1);
    }

}

