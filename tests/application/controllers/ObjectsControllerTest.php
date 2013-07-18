<?php

class ObjectsControllerTest extends Zend_Test_PHPUnit_ControllerTestCase {

    private $objectManager;
    private $scenarioId;

    public function setUp() {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        parent::setUp();
        // Lets create new user
        $this->objectManager = new Application_Model_ObjectsManager(-1);
        $this->objectManager->dbLink->delete('item');
        $this->objectManager->dbLink->delete('form');
        $this->objectManager->dbLink->delete('scenario_assignment');
        $this->objectManager->dbLink->delete('scenario_entry');
        $this->objectManager->dbLink->delete('scenario');
        $this->objectManager->dbLink->delete('privilege');
        $this->objectManager->dbLink->delete('resource');
        $this->objectManager->dbLink->delete('user_group');
        $this->objectManager->dbLink->delete('approval_entry');
        $this->objectManager->dbLink->delete('user');
        $this->objectManager->dbLink->delete('position');
        $this->objectManager->dbLink->delete('node');
        $this->objectManager->dbLink->delete('element');
        $this->objectManager->dbLink->delete('domain_owner');
        $this->objectManager->dbLink->delete('user_group');
        $this->objectManager->dbLink->delete('domain_owner');
        $this->objectManager->dbLink->delete('user');
        $this->objectManager->dbLink->delete('contragent');
        $this->objectManager->dbLink->delete('domain');
        $inputArray = array('userName'=>'testName', 'email'=>'test@domain', 'password'=>'test_pwd', 'companyName'=>'New node name');
        $params = array('controller'=>'index', 'action'=>'new-domain');
        $this->request->setMethod('post');
        $this->request->setPost($inputArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertController('index');
        $this->assertAction('new-domain');
        $this->resetRequest();
        $this->resetResponse();

    }

    public function tearDown() {
        $this->objectManager->dbLink->delete('item');
        $this->objectManager->dbLink->delete('form');
        $this->objectManager->dbLink->delete('scenario_assignment');
        $this->objectManager->dbLink->delete('scenario_entry');
        $this->objectManager->dbLink->delete('scenario');
        $this->objectManager->dbLink->delete('privilege');
        $this->objectManager->dbLink->delete('resource');
        $this->objectManager->dbLink->delete('user_group');
        $this->objectManager->dbLink->delete('domain_owner');
        $this->objectManager->dbLink->delete('approval_entry');
        $this->objectManager->dbLink->delete('user');
        $this->objectManager->dbLink->delete('position');
        $this->objectManager->dbLink->delete('node');
        $this->objectManager->dbLink->delete('element');
        $this->objectManager->dbLink->delete('contragent');
        $this->objectManager->dbLink->delete('domain');
        $this->objectManager->dbLink->insert('domain', array('domainId'=>1, 'domainName'=>'Domain1', 'active'=>1));
    }

    public function testIndexAction() {
        $userArray = array('login' => 'test@domain', 'password' => 'test_pwd');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($userArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();
        $session=new Zend_Session_Namespace('Auth');
        $this->assertEquals($session->login, 'test@domain');
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
        $user = array('login' => 'test@domain', 'password' => 'test_pwd');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();
        $session = new Zend_Session_Namespace('Auth');
        $params = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray = array('objectType' => 'element', 'elementName' => 'testAddObject',
            'elementCode' => 44, 'domainId' => $session->domainId, 'expgroup'=>'CAPEX');
        $this->request->setMethod('post');
        $this->request->setPost($objectArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertController('objects');

        $objectManager = new Application_Model_ObjectsManager($session->domainId);
        $elements = $objectManager->getAllObjects('Element');
        $element = new Application_Model_Element(array('elementName' => 'testAddObject', 'elementCode' => 44, 'domainId' => $session->domainId, 'expgroup'=>'CAPEX'));
        $element->elementId = $elements[0]->elementId;

        $this->assertEquals($elements, array(0 => $element));
        return $element;
    }

    public function testOpenObject() {
        $user = array('login' => 'test@domain', 'password' => 'test_pwd');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();
        $session = new Zend_Session_Namespace('Auth');
        $params = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray = array('objectType' => 'element', 'elementName' => 'testbject',
            'elementCode' => 44, 'domainId' => $session->domainId, 'expgroup'=>'OPEX');
        $this->request->setMethod('post');
        $this->request->setPost($objectArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertController('objects');
        $this->assertAction('add-object');
        $this->objectManager->setDomainId($session->domainId);
         $elements = $this->objectManager->getAllObjects('Element');
       
        $this->resetRequest();
        $this->resetResponse();
         $params1 = array('controller' => 'objects', 'action' => 'open-object',
            'objectType' => 'element', 'elementId' => $elements[0]->elementId, 'expgroup'=>'OPEX');
        $this->dispatch($this->url($this->urlizeOptions($params1)));
        $response = $this->getResponse();
        $this->assertController($params1['controller']);
        $this->assertAction($params1['action']);
    }

    /**
     * 
     * @depends testAddObject
     */
    public function testDeleteObject($element) {
        $user = array('login' => 'test@domain', 'password' => 'test_pwd');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();
         $params1 = array('controller' => 'objects', 'action' => 'delete',
            'objectType' => 'element', 'elementId' => $element->elementId);
        $this->dispatch($this->url($this->urlizeOptions($params1)));
        $elements2 = $this->objectManager->getAllObjects('Element');
        $this->assertTrue(empty($elements2));
    }

    public function testDeleteDependentObject() {
// Lets save basic node
        $user = array('login' => 'test@domain', 'password' => 'test_pwd');
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
        $objectManager = new Application_Model_ObjectsManager($session->domainId);
        $nodes = $objectManager->getAllObjects('Node');
        $nodeId = $nodes[0]->nodeId;
        $this->assertEquals(2, count($nodes));
// Lets create dependent node
        $this->resetRequest();
        $this->resetResponse();
        $params1 = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray1 = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode1',
            'parentNodeId' => $nodeId, 'domainId' => $session->domainId);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray1);
        $this->dispatch($this->url($this->urlizeOptions($params1)));
        $nodes2 = $objectManager->getAllObjects('Node');
        $this->assertEquals(3, count($nodes2));
        $this->resetRequest();
        $this->resetResponse();

        $params3 = array('controller' => 'objects', 'action' => 'delete',
            'objectType' => 'node', 'nodeId' => $nodeId);

        $this->dispatch($this->url($this->urlizeOptions($params3)));
        $nodes3 = $objectManager->getAllObjects('Node');
        $this->assertEquals(3, count($nodes3));
    }

    public function testDeleteIndependentObject() {
// Lets save basic node
        $user = array('login' => 'test@domain', 'password' => 'test_pwd');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();
        $session = new Zend_Session_Namespace('Auth');
        $objectManager = new Application_Model_ObjectsManager($session->domainId);

        $params = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode',
            'parentNodeId' => -1, 'domainId' => $session->domainId);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $nodes = $objectManager->getAllObjects('Node');
        $nodeId = $nodes[0]->nodeId;
// Lets create dependent node
        $params1 = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray1 = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode1',
            'parentNodeId' => $nodeId, 'domainId' => 1);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray1);
        $this->dispatch($this->url($this->urlizeOptions($params1)));
        $nodes1 = $objectManager->getAllObjects('Node');
        $nodeId1 = $nodes1[1]->nodeId;
// Lets create another independent node
        $params2 = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray2 = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode1',
            'parentNodeId' => 22, 'domainId' => 1);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray2);
        $this->dispatch($this->url($this->urlizeOptions($params2)));
//        $this->assertEquals($output2, 'tt');

        $nodes2 = $objectManager->getAllObjects('Node');
        $this->assertEquals(4, count($nodes2));

        $params3 = array('controller' => 'objects', 'action' => 'delete',
            'objectType' => 'node', 'nodeId' => $nodeId1);
        $this->resetResponse();
        $this->dispatch($this->url($this->urlizeOptions($params3)));
        $response = $this->getResponse();
        echo $response->outputBody();
//        $this->assertRedirect();

        $nodes3 = $objectManager->getAllObjects('Node');
        $this->assertEquals(3, count($nodes3));
    }

    public function testDeleteIndependentObjectMixed() {
        $user = array('login' => 'test@domain', 'password' => 'test_pwd');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();
        $session = new Zend_Session_Namespace('Auth');
        $objectManager = new Application_Model_ObjectsManager($session->domainId);
 // Lets save basic node
        $params = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode',
            'parentNodeId' => -1, 'domainId' => $session->domainId);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $nodes = $objectManager->getAllObjects('Node');
        $nodeId = $nodes[0]->nodeId;
// Lets create dependent node
        $params1 = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray1 = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode1',
            'parentNodeId' => $nodeId, 'domainId' => 1);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray1);
        $this->dispatch($this->url($this->urlizeOptions($params1)));
        $nodes1 = $objectManager->getAllObjects('Node');
        $nodeId1 = $nodes1[1]->nodeId;
// Lets create another independent node
        $params2 = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray2 = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode1',
            'parentNodeId' => 22, 'domainId' => $session->domainId);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray2);
        $this->dispatch($this->url($this->urlizeOptions($params2)));
//        $this->assertEquals($output2, 'tt');

        $nodes2 = $objectManager->getAllObjects('Node');
        $this->assertEquals(4, count($nodes2));

        $params3 = array('controller' => 'objects', 'action' => 'delete',
            'objectType' => 'node', 'nodeId' => $nodeId);
        $this->resetResponse();
        $this->dispatch($this->url($this->urlizeOptions($params3)));

        $nodes3 = $objectManager->getAllObjects('Node');
        $this->assertEquals(4, count($nodes3));

        $params4 = array('controller' => 'objects', 'action' => 'delete',
            'objectType' => 'node', 'nodeId' => $nodeId1);
        $this->resetResponse();
        $this->dispatch($this->url($this->urlizeOptions($params4)));

        $nodes4 = $objectManager->getAllObjects('Node');
        $this->assertEquals(3, count($nodes4));
    }

    public function testAddDeleteObjects() {
        $user = array('login' => 'test@domain', 'password' => 'test_pwd');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $session = new Zend_Session_Namespace('Auth');
        $objectManager = new Application_Model_ObjectsManager($session->domainId);
        $this->resetRequest();
        $this->resetResponse();

// Lets save basic node
        $params = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode',
            'parentNodeId' => -1, 'domainId' => $session->domainId);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $nodes = $objectManager->getAllObjects('Node');
        $nodeId = $nodes[0]->nodeId;
// Lets create dependent node
        $params1 = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray1 = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode1',
            'parentNodeId' => $nodeId, 'domainId' => 1);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray1);
        $this->dispatch($this->url($this->urlizeOptions($params1)));
        $nodes1 = $objectManager->getAllObjects('Node');
        $nodeId1 = $nodes1[1]->nodeId;
// Lets create another independent node
        $params2 = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray2 = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode1',
            'parentNodeId' => 22, 'domainId' => $session->domainId);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray2);
        $this->dispatch($this->url($this->urlizeOptions($params2)));
//        $this->assertEquals($output2, 'tt');

        $nodes2 = $objectManager->getAllObjects('Node');
        $this->assertEquals(4, count($nodes2));

        $params3 = array('controller' => 'objects', 'action' => 'delete',
            'objectType' => 'node', 'nodeId' => $nodeId);
        $this->resetResponse();
        $this->dispatch($this->url($this->urlizeOptions($params3)));

        $nodes3 = $objectManager->getAllObjects('Node');
        $this->assertEquals(4, count($nodes3));

        $params4 = array('controller' => 'objects', 'action' => 'delete',
            'objectType' => 'node', 'nodeId' => $nodeId1);
        $this->resetResponse();
        $this->dispatch($this->url($this->urlizeOptions($params4)));

        $nodes4 = $objectManager->getAllObjects('Node');
        $this->assertEquals(3, count($nodes4));
    }

    public function testEditObject() {
        // Lets login as admin
        $user = array('login' => 'test@domain', 'password' => 'test_pwd');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $session = new Zend_Session_Namespace('Auth');
        $objectManager = new Application_Model_ObjectsManager($session->domainId);
        
        // Lets prepare everything we need
        $nodeArray = array('nodeName' => 'First node', 'parentNodeId' => -1, 'domainId' => $session->domainId);
        $node = new Application_Model_Node($nodeArray);
        $nodeId = $objectManager->saveObject($node);
        $nodeArray1 = array('nodeName' => 'First node', 'parentNodeId' => -1, 'domainId' => $session->domainId);
        $node1 = new Application_Model_Node($nodeArray1);
        $nodeId1 = $objectManager->saveObject($node1);
        $positionArray = array('positionName' => 'First position', 'nodeId' => $nodeId, 'domainId' => $session->domainId);
        $position = new Application_Model_Position($positionArray);
        $positionId = $objectManager->saveObject($position);
        $userArray = array('userName' => 'user1', 'domainId' => $session->domainId, 'login' => 'user login', 'password' => 'user password', 'positionId' => $positionId);
        $user = new Application_Model_User($userArray);
        $userId = $objectManager->saveObject($user);
        $entryArray = array( 'domainId'=>$session->domainId, 'userId'=>$userId, 'orderPos'=>1);
        $scenarioArray = array('scenarioName'=>'scenario 1', 'domainId'=>$session->domainId, 'entries'=>array(0=>$entryArray));
        $scenario = new Application_Model_Scenario($scenarioArray);
        $objectsManager = new Application_Model_ObjectsManager($session->domainId);
        $scenarioId = $objectsManager->saveObject($scenario);
        $assignmentArray = array('nodeId'=>$nodeId, 'scenarioId'=>$scenarioId, 'domainId'=>$session->domainId);
        $assignment = new Application_Model_ScenarioAssignment($assignmentArray);
        $assignmentId = $this->objectManager->saveObject($assignment);
        $this->assertTrue(is_int($assignmentId));
                
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
        $nodeEdited = $objectManager->getObject('node', $nodeId);
        $this->assertEquals($nodeEdited->nodeName, 'Modified node name');
        $assignment = $objectManager->getAllObjects('ScenarioAssignment', array(0=>array('column'=>'nodeId', 'operand'=>$nodeId)));
        $this->assertTrue($assignment[0] instanceof Application_Model_ScenarioAssignment);
        $this->assertEquals($assignment[0]->scenarioId, $scenarioId);
   
        
        $this->resetRequest();
        $this->resetResponse();
        
        $formArray1 = array('_nodeName' => 'Modified node name', '_parentNodeId'=>$nodeId1, '_scenarioId'=>$scenarioId, '_nodeId'=>$nodeId, 'objectType'=>'node');
        $params1 = array('controller'=>'objects', 'action'=>'edit-object');
        $this->request->setMethod('post');
        $this->request->setPost($formArray1);
        $this->dispatch($this->url($this->urlizeOptions($params1)));
        $this->assertController('objects');
        $this->assertAction('edit-object');
        $assignment = $objectManager->getAllObjects('ScenarioAssignment', array(0=>array('column'=>'nodeId', 'operand'=>$nodeId)));
        $this->assertTrue($assignment[0] instanceof Application_Model_ScenarioAssignment);
        $editedNode = $objectManager->getObject('node', $nodeId);
        $this->assertTrue($editedNode instanceof Application_Model_Node);
        $editedArray = array('_nodeId'=>$nodeId, '_nodeName'=>$editedNode->nodeName, '_parentNodeId'=>$editedNode->parentNodeId, '_scenarioId'=>$assignment[0]->scenarioId, 'objectType'=>'node');
        $this->assertEquals($formArray1, $editedArray);
        $nodeEdited1 = $objectManager->getObject('Node', $nodeId);
        $this->assertEquals($nodeEdited1->parentNodeId, $nodeId1);
    }

    public function testOpenUser(){
        $userArray = array('login' => 'test@domain', 'password' => 'test_pwd');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($userArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();
        $session=new Zend_Session_Namespace('Auth');
        $this->objectManager = new Application_Model_ObjectsManager($session->domainId);
        $users = $this->objectManager->getAllObjects('user');
        Zend_Debug::dump($users);
        $this->assertTrue($users[0] instanceof Application_Model_User);
        $params = array('controller'=>'objects', 'action'=>'open-object', 'objectType'=>'user', 'userId'=>$users[0]->userId);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertController('objects');
        $this->assertAction('open-object');
        $this->assertQueryContentContains('#_positions', '/administrator/');
    }
}

