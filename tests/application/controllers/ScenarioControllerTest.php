<?php

class ScenarioControllerTest extends Zend_Test_PHPUnit_ControllerTestCase {

    private $dataMapper;
    private $object;
    private $userId;
    private $userId1;
    private $nodeId;
    private $nodeId1;
    private $nodeId2;
    private $nodeId3;
    private $elementId1;
    private $elementId2;
    private $resourceId3;

    public function setUp() {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        $this->objectManager = new Application_Model_ObjectsManager(1);
        $this->dataMapper = new Application_Model_DataMapper(1);
        $this->dataMapper->dbLink->delete('item');
        $this->dataMapper->dbLink->delete('scenario_entry');
        $this->dataMapper->dbLink->delete('scenario');
        $this->dataMapper->dbLink->delete('privilege');
        $this->dataMapper->dbLink->delete('resource');
        $this->dataMapper->dbLink->delete('user_group');
        $this->dataMapper->dbLink->delete('scenario_entry');
        $this->dataMapper->dbLink->delete('scenario');
        $this->dataMapper->dbLink->delete('user');
        $this->dataMapper->dbLink->delete('position');
        $this->dataMapper->dbLink->delete('node');
        /*  Lets prepare some staff: node, node, position, user, access control 
         *    We have: 
         *    1. One node with ID nodeId
         *    2. Two nodes, connected to this node
         *    3. Two positions, connected to nodes respectively
         *    4. Two users on these positions
         *    5. ACLs should allow: first user has Approval privileges to first node (therefore, to both nodes
         *          second user has 'read' access to first node and 'write' access to second node.
         *          Also, privilege grants first user access to Administrative page. User2 is restricted from accessing this page and subpages.
         */
//NODES
        $nodeArray = array('nodeName' => 'First node', 'parentNodeId' => -1, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $nodeId = $this->dataMapper->saveObject($node);

        $nodeArray3 = array('nodeName' => 'First object', 'parentNodeId' => $nodeId, 'domainId' => 1);
        $node3 = new Application_Model_Node($nodeArray3);
        $nodeId3 = $this->dataMapper->saveObject($node3);
        $nodeArray1 = array('nodeName' => 'Second object', 'parentNodeId' => $nodeId, 'domainId' => 1);
        $node1 = new Application_Model_Node($nodeArray1);
        $nodeId1 = $this->dataMapper->saveObject($node1);
        $nodeArray2 = array('nodeName' => 'Third bject', 'parentNodeId' => $nodeId3, 'domainId' => 1);
        $node2 = new Application_Model_Node($nodeArray2);
        $nodeId2 = $this->dataMapper->saveObject($node2);

// ELEMENTS
        $elementArray = array('elementName' => 'eName', 'domainId' => 1, 'elementCode' => 34);
        $element = new Application_Model_Element($elementArray);
        $this->assertTrue($element->isValid());
        $this->elementId1 = $this->dataMapper->saveObject($element);
        $elementArray1 = array('elementName' => 'eName1', 'domainId' => 1, 'elementCode' => 44);
        $element1 = new Application_Model_Element($elementArray1);
        $this->assertTrue($element1->isValid());
        $this->elementId2 = $this->dataMapper->saveObject($element1);


// POSITIONS        
        $positionArray = array('positionName' => 'First position', 'nodeId' => $nodeId, 'domainId' => 1);
        $position = new Application_Model_Position($positionArray);
        $positionId = $this->dataMapper->saveObject($position);
        $positionArray1 = array('positionName' => 'First position', 'nodeId' => $nodeId1, 'domainId' => 1);
        $position1 = new Application_Model_Position($positionArray1);
        $positionId1 = $this->dataMapper->saveObject($position1);

// USERS        
        $userArray = array('userName' => 'user1', 'domainId' => 1, 'login' => 'user login', 'password' => 'user password', 'positionId' => $positionId);
        $user = new Application_Model_User($userArray);
        $this->userId = $this->dataMapper->saveObject($user);
        $userArray1 = array('userName' => 'user2', 'domainId' => 1, 'login' => 'user login2', 'password' => 'user password', 'positionId' => $positionId1);
        $user1 = new Application_Model_User($userArray1);
        $this->userId1 = $this->dataMapper->saveObject($user1);

// RESOURCES
        $resourceArray = array('resourceName' => 'admin', 'domainId' => 1);
        $resource = new Application_Model_Resource($resourceArray);
        $resourceId = $this->dataMapper->saveObject($resource);

// PRIVILEGES        
        $privilegeArray = array('objectType' => 'node', 'objectId' => $nodeId, 'userId' => $this->userId, 'privilege' => 'approve', 'domainId' => 1);
        $privilege = new Application_Model_Privilege($privilegeArray);
        $this->dataMapper->saveObject($privilege);
        $privilegeArray1 = array('objectType' => 'node', 'objectId' => $nodeId, 'userId' => $this->userId1, 'privilege' => 'read', 'domainId' => 1);
        $privilege1 = new Application_Model_Privilege($privilegeArray1);
        $this->dataMapper->saveObject($privilege1);
        $privilegeArray2 = array('objectType' => 'node', 'objectId' => $nodeId1, 'userId' => $this->userId1, 'privilege' => 'write', 'domainId' => 1);
        $privilege2 = new Application_Model_Privilege($privilegeArray2);
        $this->dataMapper->saveObject($privilege2);
        $privilegeArray3 = array('objectType' => 'resource', 'objectId' => $resourceId, 'userId' => $this->userId, 'privilege' => 'read', 'domainId' => 1);
        $privilege3 = new Application_Model_Privilege($privilegeArray3);
        $this->dataMapper->saveObject($privilege3);

// USERGROUPS        
        $usergroupArray = array('userId' => $this->userId, 'role' => 'admin', 'domainId' => 1, 'userGroupName' => 'administrators');
        $usergroup = new Application_Model_Usergroup($usergroupArray);
        $this->dataMapper->saveObject($usergroup);
        $usergroupArray1 = array('userId' => $this->userId1, 'role' => 'manager', 'domainId' => 1, 'userGroupName' => 'managers');
        $usergroup1 = new Application_Model_Usergroup($usergroupArray1);
        $this->dataMapper->saveObject($usergroup1);

        $this->nodeId = $nodeId;
        $this->nodeId1 = $nodeId;
        $this->nodeId2 = $nodeId1;
        $this->nodeId3 = $nodeId2;
        parent::setUp();
    }

    public function tearDown() {
        $this->dataMapper->dbLink->delete('item');
        $this->dataMapper->dbLink->delete('element');
        $this->dataMapper->dbLink->delete('user_group');
        $this->dataMapper->dbLink->delete('privilege');
        $this->dataMapper->dbLink->delete('scenario_entry');
        $this->dataMapper->dbLink->delete('scenario');
        $this->dataMapper->dbLink->delete('user');
        $this->dataMapper->dbLink->delete('position');
        $this->dataMapper->dbLink->delete('node');
    }

    public function testIndexAction() {
        $user = array('login' => 'user login', 'password' => 'user password');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $params = array('action' => 'index', 'controller' => 'scenario');
        $urlParams = $this->urlizeOptions($params);
        $url = $this->url($urlParams);
        $this->dispatch($url);
        $responce = $this->getResponse();
        echo $responce->outputBody();

        // assertions
        $this->assertController($urlParams['controller']);
        $this->assertAction($urlParams['action']);
    }

    /**
     * 
     * @expectedException InvalidArgumentException
     */
    
    public function testGetScenarioInvalid() {
        $objectsManager = new Application_Model_ObjectsManager(1);
        $scenario = $objectsManager->getScenario('r');
    }

    public function testAddNewScenario() {
//        $session = new Zend_Session_Namespace('Auth');
//        $session->auth = 1;
//        $session->login = 'admin';
        $user = array('login' => 'user login', 'password' => 'user password');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();
        $entryArray1 = array('domainId' => 1, 'orderPos' => 1, 'userId' => $this->userId, 'active' => true);
        $entryArray2 = array('domainId' => 1, 'orderPos' => 2, 'userId' => $this->userId1, 'active' => true);
        $scenarioArray1 = array('scenarioName' => 'eName1', 'active' => false, 'domainId' => 1, 'entries' => array(0 => $entryArray1, 1 => $entryArray2));
        $params = array('controller' => 'scenario', 'action' => 'add-scenario');
        $this->request->setMethod('post');
        $this->request->setPost($scenarioArray1);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertController('scenario');
        $objectManager = new Application_Model_ObjectsManager(1);
        $scenarios = $objectManager->getAllScenarios();
        $this->assertEquals(count($scenarios), 1);
        $scenario = $objectManager->getScenario($scenarios[0]->scenarioId);
        $this->assertEquals($scenario->scenarioName, $scenarios[0]->scenarioName);
        $this->assertEquals($scenario->scenarioName, 'eName1');
        $entries = $scenario->entries;
        $entryArray3 = $entries[0]->toArray();
        unset($entryArray3['scenarioEntryId']);
        unset($entryArray3['scenarioId']);
        $entryArray4 = $entries[1]->toArray();
        unset($entryArray4['scenarioEntryId']);
        unset($entryArray4['scenarioId']);
        $this->assertEquals(array(0 => $entryArray3, 1 => $entryArray4), array(0 => $entryArray1, 1 => $entryArray2));
    }

    public function testAddNewScenarioFromWeb() {
        $user = array('login' => 'user login', 'password' => 'user password');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();
        $scenarioArray1 = array('scenarioName' => 'test', 'nodeId' => $this->nodeId1, 'domainId' => 1,
            'orderPos_' . $this->userId1 => 1, 'orderPos_' . $this->userId => 2);
        $params = array('controller' => 'scenario', 'action' => 'add-scenario');
//        Zend_Debug::dump($scenarioArray1);
        $this->request->setMethod('post');
        foreach ($scenarioArray1 as $key => $value) {
            if ($value === null) {
                continue;
            }
            $this->request->setPost($key, $value);
        }
        $this->dispatch($this->url($this->urlizeOptions($params)));
//        $this->assertController('scenario');
//        $response = $this->getResponse();
        //Zend_Debug::dump($response);
//        Zend_Debug::dump($this->request->getPost());
//        echo $response->outputBody();
        $objectManager = new Application_Model_ObjectsManager(1);
        $scenarios = $objectManager->getAllScenarios();
//        $this->assertEquals('rr', $response->outputBody());
//        $this->assertEquals('tt', $scenarios);
        $scenario = $objectManager->getScenario($scenarios[0]->scenarioId);
        $this->assertEquals($scenario->scenarioName, $scenarios[0]->scenarioName);
        $this->assertEquals($scenario->scenarioName, 'test');
    }

    public function testDeleteScenario() {
        $user = array('login' => 'user login', 'password' => 'user password');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();
        $entry1 = new Application_Model_ScenarioEntry(array('userId' => $this->userId1, 'orderPos' => 1, 'domainId' => 1));
        $entry2 = new Application_Model_ScenarioEntry(array('userId' => $this->userId, 'orderPos' => 2, 'domainId' => 1));
        $scenarioArray = array('scenarioName' => 'test', 'entries' => array(0 => $entry1, 1 => $entry2), 'domainId' => 1, 'active' => true);
        $scenarioArray1 = array('scenarioName' => 'test', 'nodeId' => $this->nodeId1, 'domainId' => 1,
            'orderPos_' . $this->userId1 => 1, 'orderPos_' . $this->userId => 2);
        $params = array('controller' => 'scenario', 'action' => 'add-scenario');
//        Zend_Debug::dump($scenarioArray1);
        $this->request->setMethod('post');
        $this->request->setPost($scenarioArray1);
        $this->dispatch($this->url($this->urlizeOptions($params)));
//        $response = $this->getResponse();
//        echo $response->outputBody();
//        $response = $this->getResponse();
//        echo $response->outputBody();
        $objectManager = new Application_Model_ObjectsManager(1);
        $scenario = $objectManager->getAllScenarios(array(0 => array('operand' => 'test', 'column' => 'scenarioName')));
        $this->assertTrue(!empty($scenario));
        $this->assertEquals($scenario[0]->scenarioName, 'test');
        $entries = $scenario[0]->entries;
        $this->assertEquals($entries[0]->userId, $this->userId1);
        $scenarioTest = $scenario[0]->toArray();
        $scenarioTest['entries'][0]->scenarioId = null;
        $scenarioTest['entries'][1]->scenarioId = null;
        $scenarioTest['entries'][0]->scenarioEntryId = null;
        $scenarioTest['entries'][1]->scenarioEntryId = null;
        unset($scenarioTest['scenarioId']);
        $this->assertEquals($scenarioArray, $scenarioTest);

        $deleteFormData = array('scenarioId' => $scenario[0]->scenarioId, 'controller' => 'scenario', 'action' => 'delete-scenario');
        $this->resetRequest();
        $this->resetResponse();
        $this->request->setMethod('get');
        $this->dispatch($this->url($this->urlizeOptions($deleteFormData)));
//       $response = $this->getResponse();
//       echo $response->outputBody();
        $this->assertController('scenario');
        $this->assertAction('delete-scenario');

        $scenarioDeleted = $objectManager->getScenario($scenario[0]->scenarioId);
        $this->assertFalse($scenarioDeleted);
    }

}

