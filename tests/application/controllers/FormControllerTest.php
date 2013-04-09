<?php

class FormControllerTest extends Zend_Test_PHPUnit_ControllerTestCase {

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
        $this->objectManager = new Application_Model_ObjectsManager();
        $this->dataMapper = new Application_Model_DataMapper();
        $this->dataMapper->dbLink->delete('item');
        $this->dataMapper->dbLink->delete('form');
        $this->dataMapper->dbLink->delete('privilege');
        $this->dataMapper->dbLink->delete('resource');
        $this->dataMapper->dbLink->delete('user_group');
        $this->dataMapper->dbLink->delete('scenario_entry');
        $this->dataMapper->dbLink->delete('scenario_assignment');
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
        $this->dataMapper->dbLink->delete('form');
        $this->dataMapper->dbLink->delete('element');
        $this->dataMapper->dbLink->delete('user_group');
        $this->dataMapper->dbLink->delete('privilege');
        $this->dataMapper->dbLink->delete('scenario_entry');
        $this->dataMapper->dbLink->delete('scenario_assignment');
        $this->dataMapper->dbLink->delete('scenario');
        $this->dataMapper->dbLink->delete('user');
        $this->dataMapper->dbLink->delete('position');
        $this->dataMapper->dbLink->delete('node');
    }

    public function testIndexAction() {
        $user = array('login' => 'user login2', 'password' => 'user password');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $params = array('action' => 'index', 'controller' => 'form');
        $urlParams = $this->urlizeOptions($params);
        $url = $this->url($urlParams);
        $this->dispatch($url);

        // assertions
        $this->assertController($urlParams['controller']);
        $this->assertAction($urlParams['action']);
    }

    public function testAddNewForm() {
//        $session = new Zend_Session_Namespace('Auth');
//        $session->auth = 1;
//        $session->login = 'admin';
        $user = array('login' => 'user login2', 'password' => 'user password');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();
        $session = new Zend_Session_Namespace('Auth');
        $this->assertEquals($session->login, 'user login2');
        $this->assertEquals($session->domainId, 1);
        $this->assertEquals($session->auth, 1);
        Zend_Debug::dump($session->userName);
        $itemArray1 = array('itemName' => 'item1', 'domainId' => 1, 'value' => 55.4, 'elementId' => $this->elementId1, 'active' => true);
        $itemArray2 = array('itemName' => 'item2', 'domainId' => 1, 'value' => 22.1, 'elementId' => $this->elementId2, 'active' => true);
        $formArray1 = array('userId' => $this->userId1, 'formName' => 'fName1', 'nodeId' => $this->nodeId1, 'items' => array(0 => $itemArray1, 1 => $itemArray2), 'domainId' => 1, 'active' => true);
        $params = array('controller' => 'form', 'action' => 'add-form');
        $this->request->setMethod('post');
        $this->request->setPost($formArray1);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $response = $this->getResponse();
        $this->assertController('form');
        $this->assertAction('add-form');
        $objectManager = new Application_Model_ObjectsManager();
        $forms = $objectManager->getAllForms();
        $this->assertEquals(count($forms), 1);
        $form = $objectManager->getForm($forms[0]->formId);
        $this->assertEquals($form->formName, $forms[0]->formName);
        $this->assertEquals($form->formName, 'fName1');
       $items = $form->items;
        $itemArray3 = $items[0]->toArray();
        unset($itemArray3['itemId']);
        unset($itemArray3['formId']);
        $itemArray4 = $items[1]->toArray();
        unset($itemArray4['itemId']);
        unset($itemArray4['formId']);
        $this->assertEquals(array(0 => $itemArray3, 1 => $itemArray4), array(0 => $itemArray1, 1 => $itemArray2));
    }

    public function testAddNewFormFromWeb() {
        $user = array('login' => 'user login2', 'password' => 'user password');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();
        $formArray1 = array('formName' => 'test', 'nodeId' => $this->nodeId1, 'domainId' => 1,
            'value_2' => 3, 'itemName_2' => 'we', 'value_1' => 1, 'itemName_1' => 'test',
            'elementId_1' => $this->elementId1, 'elementId_2' => $this->elementId2, 'userId' => $this->userId);
        $params = array('controller' => 'form', 'action' => 'add-form');
//        Zend_Debug::dump($formArray1);
        $this->request->setMethod('post');
        foreach ($formArray1 as $key => $value) {
            if ($value === null) {
                continue;
            }
            $this->request->setPost($key, $value);
        }
        $this->dispatch($this->url($this->urlizeOptions($params)));
//        $this->assertController('objects');
        $response = $this->getResponse();
//        Zend_Debug::dump($this->request->getPost());
//        echo $response->outputBody();
        $objectManager = new Application_Model_ObjectsManager();
        $forms = $objectManager->getAllForms();
//        $this->assertEquals('rr', $response->outputBody());
//        $this->assertEquals('tt', $forms);
        $form = $objectManager->getForm($forms[0]->formId);
        $this->assertEquals($form->formName, $forms[0]->formName);
        $this->assertEquals($form->formName, 'test');
    }

}

