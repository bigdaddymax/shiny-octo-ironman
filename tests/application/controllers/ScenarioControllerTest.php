<?php

class ScenarioControllerTest extends Zend_Test_PHPUnit_ControllerTestCase {

    private $objectManager;
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
//        $this->objectManager = new Application_Model_DataMapper(1);
        $this->objectManager->dbLink->delete('item');
        $this->objectManager->dbLink->delete('scenario_entry');
        $this->objectManager->dbLink->delete('scenario');
        $this->objectManager->dbLink->delete('privilege');
        $this->objectManager->dbLink->delete('resource');
        $this->objectManager->dbLink->delete('user_group');
        $this->objectManager->dbLink->delete('scenario_entry');
        $this->objectManager->dbLink->delete('scenario');
        $this->objectManager->dbLink->delete('approval_entry');
        $this->objectManager->dbLink->delete('user');
        $this->objectManager->dbLink->delete('position');
        $this->objectManager->dbLink->delete('node');
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
        $nodeId = $this->objectManager->saveObject($node);

        $nodeArray3 = array('nodeName' => 'First object', 'parentNodeId' => $nodeId, 'domainId' => 1);
        $node3 = new Application_Model_Node($nodeArray3);
        $nodeId3 = $this->objectManager->saveObject($node3);
        $nodeArray1 = array('nodeName' => 'Second object', 'parentNodeId' => $nodeId, 'domainId' => 1);
        $node1 = new Application_Model_Node($nodeArray1);
        $nodeId1 = $this->objectManager->saveObject($node1);
        $nodeArray2 = array('nodeName' => 'Third bject', 'parentNodeId' => $nodeId3, 'domainId' => 1);
        $node2 = new Application_Model_Node($nodeArray2);
        $nodeId2 = $this->objectManager->saveObject($node2);

// ELEMENTS
        $elementArray = array('elementName' => 'eName', 'domainId' => 1, 'elementCode' => 34, 'expgroup' => 'OPEX');
        $element = new Application_Model_Element($elementArray);
        $this->assertTrue($element->isValid());
        $this->elementId1 = $this->objectManager->saveObject($element);
        $elementArray1 = array('elementName' => 'eName1', 'domainId' => 1, 'elementCode' => 44, 'expgroup' => 'OPEX');
        $element1 = new Application_Model_Element($elementArray1);
        $this->assertTrue($element1->isValid());
        $this->elementId2 = $this->objectManager->saveObject($element1);


// POSITIONS        
        $positionArray = array('positionName' => 'First position', 'nodeId' => $nodeId, 'domainId' => 1);
        $position = new Application_Model_Position($positionArray);
        $positionId = $this->objectManager->saveObject($position);
        $positionArray1 = array('positionName' => 'First position', 'nodeId' => $nodeId1, 'domainId' => 1);
        $position1 = new Application_Model_Position($positionArray1);
        $positionId1 = $this->objectManager->saveObject($position1);

// USERS        
        $userArray = array('userName' => 'user1', 'domainId' => 1, 'login' => 'user login', 'password' => 'user password', 'positionId' => $positionId);
        $user = new Application_Model_User($userArray);
        $this->userId = $this->objectManager->saveObject($user);
        $userArray1 = array('userName' => 'user2', 'domainId' => 1, 'login' => 'user login2', 'password' => 'user password', 'positionId' => $positionId1);
        $user1 = new Application_Model_User($userArray1);
        $this->userId1 = $this->objectManager->saveObject($user1);

// RESOURCES
        $resourceArray = array('resourceName' => 'admin', 'domainId' => 1);
        $resource = new Application_Model_Resource($resourceArray);
        $resourceId = $this->objectManager->saveObject($resource);

// PRIVILEGES        
        $privilegeArray = array('objectType' => 'node', 'objectId' => $nodeId, 'userId' => $this->userId, 'privilege' => 'approve', 'domainId' => 1);
        $privilege = new Application_Model_Privilege($privilegeArray);
        $this->objectManager->saveObject($privilege);
        $privilegeArray1 = array('objectType' => 'node', 'objectId' => $nodeId, 'userId' => $this->userId1, 'privilege' => 'read', 'domainId' => 1);
        $privilege1 = new Application_Model_Privilege($privilegeArray1);
        $this->objectManager->saveObject($privilege1);
        $privilegeArray2 = array('objectType' => 'node', 'objectId' => $nodeId1, 'userId' => $this->userId1, 'privilege' => 'write', 'domainId' => 1);
        $privilege2 = new Application_Model_Privilege($privilegeArray2);
        $this->objectManager->saveObject($privilege2);
        $privilegeArray3 = array('objectType' => 'resource', 'objectId' => $resourceId, 'userId' => $this->userId, 'privilege' => 'read', 'domainId' => 1);
        $privilege3 = new Application_Model_Privilege($privilegeArray3);
        $this->objectManager->saveObject($privilege3);

// USERGROUPS        
        $usergroupArray = array('userId' => $this->userId, 'role' => 'admin', 'domainId' => 1, 'userGroupName' => 'administrators');
        $usergroup = new Application_Model_Usergroup($usergroupArray);
        $this->objectManager->saveObject($usergroup);
        $usergroupArray1 = array('userId' => $this->userId1, 'role' => 'manager', 'domainId' => 1, 'userGroupName' => 'managers');
        $usergroup1 = new Application_Model_Usergroup($usergroupArray1);
        $this->objectManager->saveObject($usergroup1);

        $this->nodeId = $nodeId;
        $this->nodeId1 = $nodeId;
        $this->nodeId2 = $nodeId1;
        $this->nodeId3 = $nodeId2;
        parent::setUp();
    }

    public function tearDown() {
        $this->objectManager->dbLink->delete('item');
        $this->objectManager->dbLink->delete('element');
        $this->objectManager->dbLink->delete('user_group');
        $this->objectManager->dbLink->delete('privilege');
        $this->objectManager->dbLink->delete('scenario_entry');
        $this->objectManager->dbLink->delete('scenario');
        $this->objectManager->dbLink->delete('approval_entry');
        $this->objectManager->dbLink->delete('user');
        $this->objectManager->dbLink->delete('position');
        $this->objectManager->dbLink->delete('node');
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
        $scenario = $objectsManager->getObject('scenario', 'r');
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
        $entryArray1 = array('orderPos' => 1, 'userId' => $this->userId, 'active' => 1, 'domainId' => 1);
        $entryArray2 = array('orderPos' => 2, 'userId' => $this->userId1, 'active' => 1, 'domainId' => 1);
        $scenarioArray1 = array('scenarioName' => 'eName1', 'active' => false, 'domainId' => 1, 'entries' => array(0 => $entryArray1, 1 => $entryArray2));
        $params = array('controller' => 'scenario', 'action' => 'save-scenario');
        $this->request->setMethod('post');
        $this->request->setPost($scenarioArray1);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertController('scenario');
        $this->assertAction('save-scenario');
        $entries = $this->objectManager->getAllObjects('scenarioEntry', array(0 => array('column' => 'userId', 'operand' => $this->userId)));
        $scenarios = $this->objectManager->getAllObjects('scenario');
        $this->assertEquals(count($scenarios), 1);
        $scenario = $this->objectManager->getObject('scenario', $scenarios[0]->scenarioId);
        $this->assertEquals($scenario->scenarioName, $scenarios[0]->scenarioName);
        $this->assertEquals($scenario->scenarioName, 'eName1');
        $entries = $scenario->entries;
        $this->assertTrue(is_array($entries));
        $this->assertTrue($entries[0]->isValid());
        $this->assertTrue($entries[1]->isValid());
        $entryArray3 = $entries[0]->toArray();
        unset($entryArray3['scenarioEntryId']);
        unset($entryArray3['scenarioId']);
        $entryArray4 = $entries[1]->toArray();
        unset($entryArray4['scenarioEntryId']);
        unset($entryArray4['scenarioId']);
        $this->assertEquals($entryArray1, $entryArray3);
        $this->assertEquals($entryArray2, $entryArray4);
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
        $params = array('controller' => 'scenario', 'action' => 'save-scenario');
//        Zend_Debug::dump($scenarioArray1);
        $this->request->setMethod('post');
        foreach ($scenarioArray1 as $key => $value) {
            if ($value === null) {
                continue;
            }
            $this->request->setPost($key, $value);
        }
        $entryArray0 = array('orderPos' => 2, 'userId' => $this->userId, 'domainId' => 1, 'active' => 1);
        $entryArray1 = array('orderPos' => 1, 'userId' => $this->userId1, 'domainId' => 1, 'active' => 1);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $objectManager = new Application_Model_ObjectsManager(1);
        $scenarios = $objectManager->getAllObjects('scenario');
        $scenario = $objectManager->getObject('scenario', $scenarios[0]->scenarioId);
        $this->assertEquals($scenario->scenarioName, $scenarios[0]->scenarioName);
        $this->assertEquals($scenario->scenarioName, 'test');
        $entries = $scenario->entries;
        $realEntry1 = $entries[0]->toArray();
        unset($realEntry1['scenarioEntryId']);
        unset($realEntry1['scenarioId']);
        $realEntry0 = $entries[1]->toArray();
        unset($realEntry0['scenarioEntryId']);
        unset($realEntry0['scenarioId']);
        $this->assertEquals($entryArray1, $realEntry1);
        $this->assertEquals($entryArray0, $realEntry0);
    }

    public function testEditScenario() {
        $user = array('login' => 'user login', 'password' => 'user password');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();
        $scenarioArray1 = array('scenarioName' => 'test', 'nodeId' => $this->nodeId1, 'domainId' => 1,
            'orderPos_' . $this->userId1 => 1, 'orderPos_' . $this->userId => 2);
        $params = array('controller' => 'scenario', 'action' => 'save-scenario');
//        Zend_Debug::dump($scenarioArray1);
        $this->request->setMethod('post');
        foreach ($scenarioArray1 as $key => $value) {
            if ($value === null) {
                continue;
            }
            $this->request->setPost($key, $value);
        }
        $entryArray0 = array('orderPos' => 2, 'userId' => $this->userId, 'domainId' => 1, 'active' => 1);
        $entryArray1 = array('orderPos' => 1, 'userId' => $this->userId1, 'domainId' => 1, 'active' => 1);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $objectManager = new Application_Model_ObjectsManager(1);
        $scenarios = $objectManager->getAllObjects('scenario');
        $scenario = $objectManager->getObject('scenario', $scenarios[0]->scenarioId);
        $this->assertEquals($scenario->scenarioName, $scenarios[0]->scenarioName);
        $this->assertEquals($scenario->scenarioName, 'test');
        $entries = $scenario->entries;
        $realEntry1 = $entries[0]->toArray();
        unset($realEntry1['scenarioEntryId']);
        unset($realEntry1['scenarioId']);
        $realEntry0 = $entries[1]->toArray();
        unset($realEntry0['scenarioEntryId']);
        unset($realEntry0['scenarioId']);
        $this->assertEquals($entryArray1, $realEntry1);
        $this->assertEquals($entryArray0, $realEntry0);

        $this->resetRequest();
        $this->resetResponse();
        $scenarioArray2 = array('scenarioName' => 'test', 'nodeId' => $this->nodeId1, 'domainId' => 1,
            'orderPos_' . $this->userId1 => 1, 'scenarioId' => $scenario->scenarioId);
        $params = array('controller' => 'scenario', 'action' => 'save-scenario');
//        Zend_Debug::dump($scenarioArray1);
        $this->request->setMethod('post');
        foreach ($scenarioArray2 as $key => $value) {
            if ($value === null) {
                continue;
            }
            $this->request->setPost($key, $value);
        }
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $scenarios1 = $this->objectManager->getAllObjects('scenario');
        $this->assertEquals(count($scenarios1), 1);
        $entryArray1 = array('orderPos' => 1, 'userId' => $this->userId1, 'domainId' => 1, 'active' => 1);
        $entries = $scenarios1[0]->entries;
        $realEntries = $entries[0]->toArray();
        unset($realEntries['scenarioEntryId']);
        unset($realEntries['scenarioId']);
        $this->assertEquals($realEntries, $entryArray1);

        $this->resetRequest();
        $this->resetResponse();

        $params = array('controller' => 'scenario', 'action' => 'edit-scenario', 'scenarioId' => $scenarios1[0]->scenarioId);
//        Zend_Debug::dump($scenarioArray1);
        $this->request->setMethod('post');
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertController('scenario');
        $this->assertAction('edit-scenario');
        $this->assertQuery('#entries');
    }

    /**
     * @expectedException Exception
     */
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
        $scenario = $objectManager->getAllObjects('scenario', array(0 => array('operand' => 'test', 'column' => 'scenarioName')));
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

        $scenarioDeleted = $objectManager->getObject('scenario', $scenario[0]->scenarioId);
        $this->assertFalse($scenarioDeleted);
    }

}

