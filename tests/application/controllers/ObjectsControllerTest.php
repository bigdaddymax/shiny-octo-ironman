<?php

class ObjectsControllerTest extends Zend_Test_PHPUnit_ControllerTestCase {

    private $dataMapper;

    public function setUp() {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        $this->dataMapper = new Application_Model_DataMapper();
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
        parent::setUp();
        $session = new Zend_Session_Namespace('Auth');
        $session->domainId = 1;
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
    }

    public function testIndexAction() {
        $session = new Zend_Session_Namespace('Auth');
        $session->auth = 1;
        $session->login = 'admin';
        $params = array('action' => 'index', 'controller' => 'objects', 'objectType' => 'node');
        $urlParams = $this->urlizeOptions($params);
        $url = $this->url($urlParams);
        $this->dispatch($url);

        // assertions
        $this->assertController($urlParams['controller']);
        $this->assertAction($urlParams['action']);
    }

    public function testAddObject() {
        $user = array('login' => 'admin', 'password' => 'admin');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $params = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray = array('objectType' => 'element', 'elementName' => 'testAddObject',
            'elementCode' => 44, 'domainId' => 1);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertController('objects');
        $elements = $this->dataMapper->getAllObjects('Application_Model_Element');
        $element = new Application_Model_Element(array('elementName' => 'testAddObject', 'elementCode' => 44, 'domainId' => 1));
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
        $params1 = array('controller' => 'objects', 'action' => 'delete',
            'objectType' => 'element', 'elementId' => $element->elementId);
        $this->dispatch($this->url($this->urlizeOptions($params1)));
        $elements2 = $this->dataMapper->getAllObjects('Application_Model_Element');
        $this->assertTrue(empty($elements2));
    }

    public function testDeleteDependentObject() {
// Lets save basic node
        $session = new Zend_Session_Namespace('Auth');
        $session->auth = 1;
        $session->login = 'admin';
        $params = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode',
            'parentNodeId' => -1, 'domainId' => 1);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $nodes = $this->dataMapper->getAllObjects('Application_Model_Node');
        $nodeId = $nodes[0]->nodeId;
// Lets create dependent node
        $params1 = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray1 = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode1',
            'parentNodeId' => $nodeId, 'domainId' => 1);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray1);
        $this->dispatch($this->url($this->urlizeOptions($params1)));
        $nodes2 = $this->dataMapper->getAllObjects('Application_Model_Node');
        $this->assertEquals(2, count($nodes2));

        $params3 = array('controller' => 'objects', 'action' => 'delete',
            'objectType' => 'node', 'nodeId' => $nodeId);

        $this->dispatch($this->url($this->urlizeOptions($params3)));
        $nodes3 = $this->dataMapper->getAllObjects('Application_Model_Node');
        $this->assertEquals(2, count($nodes3));
    }

    public function testDeleteIndependentObject() {
// Lets save basic node
        $session = new Zend_Session_Namespace('Auth');
        $session->auth = 1;
        $session->login = 'admin';
        $params = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode',
            'parentNodeId' => -1, 'domainId' => 1);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $nodes = $this->dataMapper->getAllObjects('Application_Model_Node');
        $nodeId = $nodes[0]->nodeId;
// Lets create dependent node
        $params1 = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray1 = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode1',
            'parentNodeId' => $nodeId, 'domainId' => 1);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray1);
        $this->dispatch($this->url($this->urlizeOptions($params1)));
        $nodes1 = $this->dataMapper->getAllObjects('Application_Model_Node');
        $nodeId1 = $nodes1[1]->nodeId;
// Lets create another independent node
        $params2 = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray2 = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode1',
            'parentNodeId' => 22, 'domainId' => 1);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray2);
        $this->dispatch($this->url($this->urlizeOptions($params2)));
//        $this->assertEquals($output2, 'tt');

        $nodes2 = $this->dataMapper->getAllObjects('Application_Model_Node');
        $this->assertEquals(3, count($nodes2));

        $params3 = array('controller' => 'objects', 'action' => 'delete',
            'objectType' => 'node', 'nodeId' => $nodeId1);
        $this->resetResponse();
        $this->dispatch($this->url($this->urlizeOptions($params3)));

//        $this->assertRedirect();

        $nodes3 = $this->dataMapper->getAllObjects('Application_Model_Node');
        $this->assertEquals(2, count($nodes3));
    }

    public function testDeleteIndependentObjectMixed() {
        $session = new Zend_Session_Namespace('Auth');
        $session->auth = 1;
        $session->login = 'admin';
// Lets save basic node
        $params = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode',
            'parentNodeId' => -1, 'domainId' => 1);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $nodes = $this->dataMapper->getAllObjects('Application_Model_Node');
        $nodeId = $nodes[0]->nodeId;
// Lets create dependent node
        $params1 = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray1 = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode1',
            'parentNodeId' => $nodeId, 'domainId' => 1);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray1);
        $this->dispatch($this->url($this->urlizeOptions($params1)));
        $nodes1 = $this->dataMapper->getAllObjects('Application_Model_Node');
        $nodeId1 = $nodes1[1]->nodeId;
// Lets create another independent node
        $params2 = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray2 = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode1',
            'parentNodeId' => 22, 'domainId' => 1);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray2);
        $this->dispatch($this->url($this->urlizeOptions($params2)));
//        $this->assertEquals($output2, 'tt');

        $nodes2 = $this->dataMapper->getAllObjects('Application_Model_Node');
        $this->assertEquals(3, count($nodes2));

        $params3 = array('controller' => 'objects', 'action' => 'delete',
            'objectType' => 'node', 'nodeId' => $nodeId);
        $this->resetResponse();
        $this->dispatch($this->url($this->urlizeOptions($params3)));

        $nodes3 = $this->dataMapper->getAllObjects('Application_Model_Node');
        $this->assertEquals(3, count($nodes3));

        $params4 = array('controller' => 'objects', 'action' => 'delete',
            'objectType' => 'node', 'nodeId' => $nodeId1);
        $this->resetResponse();
        $this->dispatch($this->url($this->urlizeOptions($params4)));

        $nodes4 = $this->dataMapper->getAllObjects('Application_Model_Node');
        $this->assertEquals(2, count($nodes4));
    }

    public function testAddDeleteObjects() {
        $session = new Zend_Session_Namespace('Auth');
        $session->auth = 1;
        $session->login = 'admin';
// Lets save basic node
        $params = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode',
            'parentNodeId' => -1, 'domainId' => 1);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $nodes = $this->dataMapper->getAllObjects('Application_Model_Node');
        $nodeId = $nodes[0]->nodeId;
// Lets create dependent node
        $params1 = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray1 = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode1',
            'parentNodeId' => $nodeId, 'domainId' => 1);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray1);
        $this->dispatch($this->url($this->urlizeOptions($params1)));
        $nodes1 = $this->dataMapper->getAllObjects('Application_Model_Node');
        $nodeId1 = $nodes1[1]->nodeId;
// Lets create another independent node
        $params2 = array('controller' => 'objects', 'action' => 'add-object');
        $objectArray2 = array('objectType' => 'node', 'nodeName' => 'testAddObjectNode1',
            'parentNodeId' => 22, 'domainId' => 1);
        $this->request->setMethod('post');
        $this->request->setPost($objectArray2);
        $this->dispatch($this->url($this->urlizeOptions($params2)));
//        $this->assertEquals($output2, 'tt');

        $nodes2 = $this->dataMapper->getAllObjects('Application_Model_Node');
        $this->assertEquals(3, count($nodes2));

        $params3 = array('controller' => 'objects', 'action' => 'delete',
            'objectType' => 'node', 'nodeId' => $nodeId);
        $this->resetResponse();
        $this->dispatch($this->url($this->urlizeOptions($params3)));

        $nodes3 = $this->dataMapper->getAllObjects('Application_Model_Node');
        $this->assertEquals(3, count($nodes3));

        $params4 = array('controller' => 'objects', 'action' => 'delete',
            'objectType' => 'node', 'nodeId' => $nodeId1);
        $this->resetResponse();
        $this->dispatch($this->url($this->urlizeOptions($params4)));

        $nodes4 = $this->dataMapper->getAllObjects('Application_Model_Node');
        $this->assertEquals(2, count($nodes4));
    }

    public function testEditObject() {
        
        // Lets prepare everything we need
        $nodeArray = array('nodeName' => 'First node', 'parentNodeId' => -1, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $nodeId = $this->dataMapper->saveObject($node);
        $nodeArray1 = array('nodeName' => 'First node', 'parentNodeId' => -1, 'domainId' => 1);
        $node1 = new Application_Model_Node($nodeArray1);
        $nodeId1 = $this->dataMapper->saveObject($node1);
        $positionArray = array('positionName' => 'First position', 'nodeId' => $nodeId, 'domainId' => 1);
        $position = new Application_Model_Position($positionArray);
        $positionId = $this->dataMapper->saveObject($position);
        $userArray = array('userName' => 'user1', 'domainId' => 1, 'login' => 'user login', 'password' => 'user password', 'positionId' => $positionId);
        $user = new Application_Model_User($userArray);
        $userId = $this->dataMapper->saveObject($user);
        $entryArray = array( 'domainId'=>1, 'userId'=>$userId, 'orderPos'=>1);
        $scenarioArray = array('scenarioName'=>'scenario 1', 'domainId'=>1, 'entries'=>array(0=>$entryArray));
        $scenario = new Application_Model_Scenario($scenarioArray);
        $objectsManager = new Application_Model_ObjectsManager();
        $scenarioId = $objectsManager->saveScenario($scenario);
//        $assignmentArray = array('nodeId'=>$nodeId, 'scenarioId'=>$scenarioId, 'domainId'=>1);
//        $assignment = new Application_Model_ScenarioAssignment($assignmentArray);
//        $assignmentId = $this->dataMapper->saveObject($assignment);
//        $this->assertTrue(is_int($assignmentId));
        
        // Lets login as admin
        $user = array('login' => 'admin', 'password' => 'admin');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $session = new Zend_Session_Namespace('Auth');
        
        // Check if we are in
        $this->assertTrue(($session->auth == 1));
        $this->assertEquals($session->admin, 1);
        
        $this->resetRequest();
        $this->resetResponse();
        
        $formArray = array('_nodeName' => 'Modified node name', '_parentNodeId'=>$nodeId1, '_scenarioId'=>$scenarioId, '_nodeId'=>$nodeId, 'objectType'=>'node');
        $params = array('controller'=>'objects', 'action'=>'edit-object');
        $this->request->setMethod('post');
        $this->request->setPost($formArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertController('objects');
        $this->assertAction('edit-object');
        $nodeEdited = $this->dataMapper->getObject($nodeId, 'Application_Model_Node');
        $this->assertEquals($nodeEdited->nodeName, 'Modified node name');
        $assignment = $this->dataMapper->getAllObjects('Application_Model_ScenarioAssignment', array(0=>array('column'=>'nodeId', 'operand'=>$nodeId)));
        $this->assertTrue($assignment[0] instanceof Application_Model_ScenarioAssignment);
        $this->assertEquals($assignment[0]->scenarioId, $scenarioId);
        
        $formArray1 = array('_nodeName' => 'Modified node name', '_parentNodeId'=>$nodeId1, '_scenarioId'=>-1, '_nodeId'=>$nodeId, 'objectType'=>'node');
        $params1 = array('controller'=>'objects', 'action'=>'edit-object');
        $this->request->setMethod('post');
        $this->request->setPost($formArray1);
        $this->dispatch($this->url($this->urlizeOptions($params1)));
        $assignment = $this->dataMapper->getAllObjects('Application_Model_ScenarioAssignment', array(0=>array('column'=>'nodeId', 'operand'=>$nodeId)));
        $this->assertFalse($assignment);
        $nodeEdited1 = $this->dataMapper->getObject($nodeId, 'Application_Model_Node');
        $this->assertEquals($nodeEdited1->parentNodeId, $nodeId1);
    }

}

