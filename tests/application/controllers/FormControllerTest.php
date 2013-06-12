<?php

class FormControllerTest extends Zend_Test_PHPUnit_ControllerTestCase {

    private $objectManager;
    private $userId;
    private $userId1;
    private $nodeId;
    private $nodeId1;
    private $nodeId2;
    private $nodeId3;
    private $elementId1;
    private $elementId2;
    private $resourceId3;
    private $contragentId;

    public function setUp() {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        $this->objectManager = new Application_Model_ObjectsManager(1);
//        $this->objectManager = new Application_Model_DataMapper(1);
        $this->objectManager->dbLink->delete('item');
        $this->objectManager->dbLink->delete('comment');
        $this->objectManager->dbLink->delete('form');
        $this->objectManager->dbLink->delete('privilege');
        $this->objectManager->dbLink->delete('resource');
        $this->objectManager->dbLink->delete('user_group');
        $this->objectManager->dbLink->delete('scenario_entry');
        $this->objectManager->dbLink->delete('scenario_assignment');
        $this->objectManager->dbLink->delete('scenario');
        $this->objectManager->dbLink->delete('domain_owner');
        $this->objectManager->dbLink->delete('approval_entry');
        $this->objectManager->dbLink->delete('user');
        $this->objectManager->dbLink->delete('position');
        $this->objectManager->dbLink->delete('node');
        $this->objectManager->dbLink->delete('contragent');
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
        $this->nodeId = $this->objectManager->saveObject($node);

        $nodeArray3 = array('nodeName' => 'First object', 'parentNodeId' => $this->nodeId, 'domainId' => 1);
        $node3 = new Application_Model_Node($nodeArray3);
        $this->nodeId3 = $this->objectManager->saveObject($node3);
        $nodeArray1 = array('nodeName' => 'Second object', 'parentNodeId' => $this->nodeId, 'domainId' => 1);
        $node1 = new Application_Model_Node($nodeArray1);
        $this->nodeId1 = $this->objectManager->saveObject($node1);
        $nodeArray2 = array('nodeName' => 'Third bject', 'parentNodeId' => $this->nodeId3, 'domainId' => 1);
        $node2 = new Application_Model_Node($nodeArray2);
        $this->nodeId2 = $this->objectManager->saveObject($node2);

// CONTRAGENT
        $contragentArray = array('contragentName' => 'cName', 'domainId' => 1);
        $contragent = new Application_Model_Contragent($contragentArray);
        $this->assertTrue($contragent->isValid());
        $this->contragentId = $this->objectManager->saveObject($contragent);
        $this->assertTrue($contragent instanceof Application_Model_Contragent);
        $this->assertTrue(is_int($this->contragentId));

// ELEMENTS
        $elementArray = array('elementName' => 'eName', 'domainId' => 1, 'elementCode' => 34, 'expgroup'=>'OPEX');
        $element = new Application_Model_Element($elementArray);
        $this->assertTrue($element->isValid());
        $this->elementId1 = $this->objectManager->saveObject($element);
        $elementArray1 = array('elementName' => 'eName1', 'domainId' => 1, 'elementCode' => 44, 'expgroup'=>'OPEX');
        $element1 = new Application_Model_Element($elementArray1);
        $this->assertTrue($element1->isValid());
        $this->elementId2 = $this->objectManager->saveObject($element1);


// POSITIONS        
        $positionArray = array('positionName' => 'First position', 'nodeId' => $this->nodeId, 'domainId' => 1);
        $position = new Application_Model_Position($positionArray);
        $positionId = $this->objectManager->saveObject($position);
        $positionArray1 = array('positionName' => 'First position', 'nodeId' => $this->nodeId1, 'domainId' => 1);
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
        $privilegeArray = array('objectType' => 'node', 'objectId' => $this->nodeId, 'userId' => $this->userId, 'privilege' => 'approve', 'domainId' => 1);
        $privilege = new Application_Model_Privilege($privilegeArray);
        $this->objectManager->saveObject($privilege);
        $privilegeArray1 = array('objectType' => 'node', 'objectId' => $this->nodeId, 'userId' => $this->userId1, 'privilege' => 'read', 'domainId' => 1);
        $privilege1 = new Application_Model_Privilege($privilegeArray1);
        $this->objectManager->saveObject($privilege1);
        $privilegeArray2 = array('objectType' => 'node', 'objectId' => $this->nodeId2, 'userId' => $this->userId1, 'privilege' => 'write', 'domainId' => 1);
        $privilege2 = new Application_Model_Privilege($privilegeArray2);
        $this->objectManager->saveObject($privilege2);
        $privilegeArray3 = array('objectType' => 'resource', 'objectId' => $resourceId, 'userId' => $this->userId, 'privilege' => 'read', 'domainId' => 1);
        $privilege3 = new Application_Model_Privilege($privilegeArray3);
        $this->objectManager->saveObject($privilege3);
        $privilegeArray4 = array('objectType' => 'node', 'objectId' => $this->nodeId1, 'userId' => $this->userId1, 'privilege' => 'read', 'domainId' => 1);
        $privilege4 = new Application_Model_Privilege($privilegeArray4);
        $this->objectManager->saveObject($privilege4);
        $privilegeArray5 = array('objectType' => 'node', 'objectId' => $this->nodeId1, 'userId' => $this->userId1, 'privilege' => 'write', 'domainId' => 1);
        $privilege5 = new Application_Model_Privilege($privilegeArray5);
        $this->objectManager->saveObject($privilege5);
        $privilegeArray6 = array('objectType' => 'node', 'objectId' => $this->nodeId1, 'userId' => $this->userId1, 'privilege' => 'approve', 'domainId' => 1);
        $privilege6 = new Application_Model_Privilege($privilegeArray6);
        $this->objectManager->saveObject($privilege6);
        $privilegeArray7 = array('objectType' => 'node', 'objectId' => $this->nodeId1, 'userId' => $this->userId, 'privilege' => 'approve', 'domainId' => 1);
        $privilege7 = new Application_Model_Privilege($privilegeArray7);
        $this->objectManager->saveObject($privilege7);

// USERGROUPS        
        $usergroupArray = array('userId' => $this->userId, 'role' => 'admin', 'domainId' => 1, 'userGroupName' => 'administrators');
        $usergroup = new Application_Model_Usergroup($usergroupArray);
        $this->objectManager->saveObject($usergroup);
        $usergroupArray1 = array('userId' => $this->userId1, 'role' => 'manager', 'domainId' => 1, 'userGroupName' => 'managers');
        $usergroup1 = new Application_Model_Usergroup($usergroupArray1);
        $this->objectManager->saveObject($usergroup1);
// SCENARIO
        $entryArray1 = array('domainId' => 1, 'orderPos' => 1, 'userId' => $this->userId, 'active' => true);
        $entryArray2 = array('domainId' => 1, 'orderPos' => 2, 'userId' => $this->userId1, 'active' => true);
        $scenarioArray1 = array('scenarioName' => 'eName1', 'active' => false, 'domainId' => 1, 'entries' => array(0 => $entryArray1, 1 => $entryArray2));
        $this->scenario = new Application_Model_Scenario($scenarioArray1);
        $this->scenarioId = $this->objectManager->saveObject($this->scenario);
        $this->scenario = $this->objectManager->getObject('scenario', $this->scenarioId);

// Assignment
        $assignmentArray = array('domainId' => 1, 'nodeId' => $this->nodeId1, 'scenarioId' => $this->scenarioId);
        $assignment = new Application_Model_ScenarioAssignment($assignmentArray);
        $assignmentId = $this->objectManager->saveObject($assignment);
        $this->assertTrue(is_int($assignmentId));

        parent::setUp();
    }

    public function tearDown() {
        $this->objectManager->dbLink->delete('item');
        $this->objectManager->dbLink->delete('comment');
        $this->objectManager->dbLink->delete('form');
        $this->objectManager->dbLink->delete('element');
        $this->objectManager->dbLink->delete('user_group');
        $this->objectManager->dbLink->delete('privilege');
        $this->objectManager->dbLink->delete('scenario_entry');
        $this->objectManager->dbLink->delete('scenario_assignment');
        $this->objectManager->dbLink->delete('scenario');
        $this->objectManager->dbLink->delete('domain_owner');
        $this->objectManager->dbLink->delete('approval_entry');
        $this->objectManager->dbLink->delete('user');
        $this->objectManager->dbLink->delete('position');
        $this->objectManager->dbLink->delete('node');
        $this->objectManager->dbLink->delete('contragent');
    }

    public function testIndexAction() {
        $user = array('login' => 'user login2', 'password' => 'user password');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();
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
        $accessMapper = new Application_Model_AccessMapper($session->userId, 1);
        //       $this->assertTrue($this->accessMapper->isAllowed($session->login, 'node', 'write', $this->nodeId1));
        $itemArray1 = array('itemName' => 'item1', 'domainId' => 1, 'value' => 55.4, 'elementId' => $this->elementId1, 'active' => true);
        $itemArray2 = array('itemName' => 'item2', 'domainId' => 1, 'value' => 22.1, 'elementId' => $this->elementId2, 'active' => true);
        $formArray1 = array('userId' => $this->userId1, 'formName' => 'fName1', 'nodeId' => $this->nodeId2, 'items' => array(0 => $itemArray1, 1 => $itemArray2), 'domainId' => 1, 'active' => true, 'contragentName' => 'contr name', 'expgroup'=>'CAPEX');
        $params = array('controller' => 'form', 'action' => 'add-form');
        $this->request->setMethod('post');
        $this->request->setPost($formArray1);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $response = $this->getResponse();
//        Zend_Debug::dump($accessMapper->getAllowedObjectIds());
        $this->assertController('form');
        $this->assertAction('add-form');
        $objectManager = new Application_Model_ObjectsManager(1);
        $forms = $objectManager->getAllForms();
        $this->assertEquals(count($forms), 1);
        $form1 = current($forms);
        $form = $objectManager->getObject('form', $form1['form']->formId, $session->userId);
        $this->assertEquals($form->formName, $form1['form']->formName);
        $this->assertEquals($form->formName, 'fName1');
        $items = $form->items;
        $this->assertTrue(is_array($items));
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
        $session = new Zend_Session_Namespace('Auth');
        $this->assertEquals($session->userId, $this->userId1);
        $this->resetRequest();
        $this->resetResponse();
        $formArray1 = array('formName' => 'test', 'nodeId' => $this->nodeId2, 'domainId' => 1,
            'value_2' => 3, 'itemName_2' => 'we', 'value_1' => 1, 'itemName_1' => 'test',
            'elementId_1' => $this->elementId1, 'elementId_2' => $this->elementId2, 'userId' => $this->userId1, 'contragentName' => 'cntr name', 'expgroup'=>'CAPEX');
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
        $response = $this->getResponse();
        $data = json_decode($response->outputBody());
        $this->assertEquals($data->error, 0);
        $objectManager = new Application_Model_ObjectsManager(1);
        $form = $objectManager->getObject('form', $data->formId, $this->userId1);
        $this->assertEquals($form->active, 1);
        $this->assertEquals($form->public, 0);
        $form->public = 1;
        $id = $objectManager->saveObject($form);
        $form1 = $objectManager->getObject('form', $id, $this->userId1);
        $this->assertEquals($form->active, 1);
        $this->assertEquals($form->public, 1);
        $item1 = new Application_Model_Item(array('itemName' => 'test', 'elementId' => $this->elementId1, 'value' => 1, 'domainId' => 1, 'formId' => $id, 'itemId' => $form->items[1]->itemId));
        $item2 = new Application_Model_Item(array('itemName' => 'we', 'elementId' => $this->elementId2, 'value' => 3, 'domainId' => 1, 'formId' => $id, 'itemId' => $form->items[0]->itemId));
        $this->assertEquals($form->items, array(0 => $item2, 1 => $item1));
//        $this->assertController('objects');
        //       $response = $this->getResponse();
        //     echo $response->outputBody();
//        Zend_Debug::dump($this->request->getPost());
        //$accessMapper = new Application_Model_AccessMapper($this->userId1, 1);
        //Zend_Debug::dump($accessMapper->getAllowedObjectIds());
        $objectManager = new Application_Model_ObjectsManager(1);
        $forms = $objectManager->getAllForms();
//        $this->assertEquals('rr', $response->outputBody());
//        $this->assertEquals('tt', $forms);
        $form2 = current($forms);
        $form = $objectManager->getObject('form', $form2['form']->formId, $this->userId1);
        $this->assertEquals($form->formName, $form2['form']->formName);
        $this->assertEquals($form->formName, 'test');
    }

    public function testErrorResponseWhenAddForm() {
        $user = array('login' => 'user login2', 'password' => 'user password');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $session = new Zend_Session_Namespace('Auth');
        $this->assertEquals($session->userId, $this->userId1);
        $this->resetRequest();
        $this->resetResponse();
        $formArray1 = array('formName' => 'test', 'nodeId' => $this->nodeId2,
            'userId' => $this->userId1, 'contragentName' => 'cntr name', 'expgroup'=>'CAPEX');
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
        $response = $this->getResponse();
        $data = json_decode($response->outputBody());
        $this->assertEquals($data->error, 1);
    }

    public function testAddAndPublish() {
        $user = array('login' => 'user login2', 'password' => 'user password');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $session = new Zend_Session_Namespace('Auth');
        $this->assertEquals($session->userId, $this->userId1);
        $this->resetRequest();
        $this->resetResponse();

        // Add form via web
        $formArray1 = array('formName' => 'test', 'nodeId' => $this->nodeId2, 'domainId' => 1,
            'value_2' => 3, 'itemName_2' => 'we', 'value_1' => 1, 'itemName_1' => 'test',
            'elementId_1' => $this->elementId1, 'elementId_2' => $this->elementId2, 'userId' => $this->userId1, 'contragentName' => 'cntr name', 'expgroup'=>'CAPEX');
        $params = array('controller' => 'form', 'action' => 'add-form');
        $this->request->setMethod('post');
        foreach ($formArray1 as $key => $value) {
            if ($value === null) {
                continue;
            }
            $this->request->setPost($key, $value);
        }
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $response = $this->getResponse();
        $data = json_decode($response->outputBody());
        $objectManager = new Application_Model_ObjectsManager(1);
        $form = $objectManager->getObject('form', $data->formId, $this->userId1);
        $this->assertEquals($form->active, 1);
        $this->assertEquals($form->public, 0);
        $this->assertTrue($form->isValid());
        $this->resetRequest();
        $this->resetResponse();

// Publish form
        $params = array('controller' => 'form', 'action' => 'publish-form', 'formId' => $data->formId);
//        Zend_Debug::dump($formArray1);
        $this->request->setMethod('post');
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $response = $this->getResponse();
        $ddd = json_decode($response->outputBody());
        $this->assertEquals($ddd->error, 0);
        $this->assertController('form');
        $this->assertAction('publish-form');

        $form1 = $objectManager->getObject('form', $data->formId, $this->userId1);

        $this->assertTrue($form1->isValid());
        $this->assertEquals($form1->active, 1);
        $this->assertEquals($form1->public, 1);
        $item1 = new Application_Model_Item(array('itemName' => 'test', 'elementId' => $this->elementId1, 'value' => 1, 'domainId' => 1, 'formId' => $form1->formId, 'itemId' => $form1->items[1]->itemId));
        $item2 = new Application_Model_Item(array('itemName' => 'we', 'elementId' => $this->elementId2, 'value' => 3, 'domainId' => 1, 'formId' => $form1->formId, 'itemId' => $form1->items[0]->itemId));
        $this->assertEquals($form1->items, array(0 => $item2, 1 => $item1));
        $objectManager = new Application_Model_ObjectsManager(1);
        $forms = $objectManager->getAllForms();
        $form3 = current($forms);
        $form = $objectManager->getObject('form', $form3['form']->formId, $this->userId1);
        $this->assertEquals($form->formName, $form3['form']->formName);
        $this->assertEquals($form->formName, 'test');
    }

    public function testPublishInvalidForm() {
        $user = array('login' => 'user login2', 'password' => 'user password');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $session = new Zend_Session_Namespace('Auth');
        $this->assertEquals($session->userId, $this->userId1);
        $this->resetRequest();
        $this->resetResponse();

        // Add form via web
        $formArray1 = array('formName' => 'test', 'nodeId' => $this->nodeId2, 'domainId' => 1,
            'value_2' => 3, 'itemName_2' => 'we', 'value_1' => 1, 'itemName_1' => 'test',
            'elementId_1' => $this->elementId1, 'elementId_2' => $this->elementId2, 'userId' => $this->userId1, 'contragentName' => 'cntr name', 'expgroup'=>'CAPEX');
        $params = array('controller' => 'form', 'action' => 'add-form');
        $this->request->setMethod('post');
        foreach ($formArray1 as $key => $value) {
            if ($value === null) {
                continue;
            }
            $this->request->setPost($key, $value);
        }
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $response = $this->getResponse();
        $data = json_decode($response->outputBody());
        $objectManager = new Application_Model_ObjectsManager(1);
        $form = $objectManager->getObject('form', $data->formId, $this->userId1);
        $this->assertEquals($form->active, 1);
        $this->assertEquals($form->public, 0);
        $this->resetRequest();
        $this->resetResponse();

        $params = array('controller' => 'form', 'action' => 'publish-form', 'formId' => 111);
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));

        $response = $this->getResponse();

        $data = json_decode($response->outputBody());
        $this->assertEquals($data->error, 1);
    }

    public function testOpenFormAction() {
        $user = array('login' => 'user login2', 'password' => 'user password');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();

        $formArray1 = array('formName' => 'test', 'nodeId' => $this->nodeId2, 'domainId' => 1,
            'value_2' => 3, 'itemName_2' => 'we', 'value_1' => 1, 'itemName_1' => 'test',
            'elementId_1' => $this->elementId1, 'elementId_2' => $this->elementId2, 'userId' => $this->userId1, 'contragentName' => 'cntr name', 'expgroup'=>'CAPEX');
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
        $this->resetRequest();
        $this->resetResponse();

        $forms = $this->objectManager->getAllObjects('form');
        $form4 = current($forms);
        $formId = $form4['form']->formId;
        $params = array('action' => 'open-form', 'controller' => 'form', 'formId' => $formId);
        $urlParams = $this->urlizeOptions($params);
        $url = $this->url($urlParams);
        $this->dispatch($url);
// assertions
        $this->assertController($urlParams['controller']);
        $this->assertAction($urlParams['action']);
    }

    public function testEditFormAction() {
        $user = array('login' => 'user login2', 'password' => 'user password');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();

        $formArray1 = array('formName' => 'test', 'nodeId' => $this->nodeId2, 'domainId' => 1,
            'value_2' => 3, 'itemName_2' => 'we', 'value_1' => 1, 'itemName_1' => 'test',
            'elementId_1' => $this->elementId1, 'elementId_2' => $this->elementId2, 'userId' => $this->userId1, 'contragentName' => 'cntr name', 'expgroup'=>'CAPEX');
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
        $this->resetRequest();
        $this->resetResponse();

        $forms = $this->objectManager->getAllObjects('form');
        $form5 = current($forms);
        $formId = $form5['form']->formId;
        $params = array('action' => 'edit-form', 'controller' => 'form', 'formId' => $formId);
        $urlParams = $this->urlizeOptions($params);
        $url = $this->url($urlParams);
        $this->dispatch($url);
// assertions
        $this->assertController($urlParams['controller']);
        $this->assertAction($urlParams['action']);
    }

    public function testCommentForm() {
        $user = array('login' => 'user login2', 'password' => 'user password');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();

        $formArray1 = array('formName' => 'test', 'nodeId' => $this->nodeId2, 'domainId' => 1,
            'value_2' => 3, 'itemName_2' => 'we', 'value_1' => 1, 'itemName_1' => 'test',
            'elementId_1' => $this->elementId1, 'elementId_2' => $this->elementId2, 'userId' => $this->userId1, 'contragentName' => 'cntr name', 'expgroup'=>'CAPEX');
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
        $this->resetRequest();
        $this->resetResponse();

        $forms = $this->objectManager->getAllObjects('form');
        $form6 = current($forms);
        $formId = $form6['form']->formId;

        $commentArray = array('formId' => $formId, 'comment' => 'bla bla bla bla', 'parentCommentId' => -1, 'userId' => $this->userId, 'active' => 1, 'domainId' => 1);
        $params = array('controller' => 'form', 'action' => 'add-comment');
        $this->request->setMethod('post');
        $this->request->setPost($commentArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertController('form');
        $this->assertAction('add-comment');
        $comments = $this->objectManager->getAllObjects('comment');
        $comments[0]->date = null;
        $commentArray['commentId'] = $comments[0]->commentId;
        $this->assertTrue($comments[0] instanceof Application_Model_Comment);
        $this->assertEquals($comments[0]->toArray(), $commentArray);
    }

    public function testApprovalAllowance() {
        // Login
        $user = array('login' => 'user login2', 'password' => 'user password');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();
        // Create form
        $formArray1 = array('formName' => 'test', 'nodeId' => $this->nodeId1, 'domainId' => 1,
            'value_2' => 3, 'itemName_2' => 'we', 'value_1' => 1, 'itemName_1' => 'test',
            'elementId_1' => $this->elementId1, 'elementId_2' => $this->elementId2, 'userId' => $this->userId1, 'contragentName' => 'cntr name', 'expgroup'=>'CAPEX');
        $params = array('controller' => 'form', 'action' => 'add-form');
        $this->request->setMethod('post');
        foreach ($formArray1 as $key => $value) {
            if ($value === null) {
                continue;
            }
            $this->request->setPost($key, $value);
        }
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();
        // Get created form ID
        $forms = $this->objectManager->getAllObjects('form');
        $form7 = current($forms);
        
        $formId = $form7['form']->formId;

        // Check preview form action
        $params = array('controller' => 'form', 'action' => 'preview-form', 'formId' => $formId);
        $this->request->setMethod('get');
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertQuery('#publish');
        $this->resetRequest();
        $this->resetResponse();
        // Check publish-form action
        $params = array('controller' => 'form', 'action' => 'publish-form', 'formId' => $formId);
        $this->request->setMethod('post');
        $this->dispatch($this->url($this->urlizeOptions($params)));

        $this->resetRequest();
        $this->resetResponse();
        // Logoff
        $params = array('controller' => 'auth', 'action' => 'logoff');
        $this->request->setMethod('get');
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();

        // Login under first user 
        $user = array('login' => 'user login', 'password' => 'user password');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();
        // Check if this user is allowed approval of the form
        $params = array('controller' => 'form', 'action' => 'open-form', 'formId' => $formId);
        $this->request->setMethod('get');
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertQuery('#approve');
        $this->resetRequest();
        $this->resetResponse();

        // Test Approve action
        $params = array('controller' => 'form', 'action' => 'approve', 'formId' => $formId);
        $this->request->setMethod('post');
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertRedirect();
        $this->resetRequest();
        $this->resetResponse();
        $params = array('controller' => 'form', 'action' => 'open-form', 'formId' => $formId);
        $this->request->setMethod('get');
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertQuery('#approve');
        // Test decline action
        $this->resetRequest();
        $this->resetResponse();
        $params = array('controller' => 'form', 'action' => 'decline', 'formId' => $formId);
        $this->request->setMethod('post');
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertRedirect();
        // Test decline incorrect form
        $this->resetRequest();
        $this->resetResponse();
        $params = array('controller' => 'form', 'action' => 'decline', 'formId' => 555);
        $this->request->setMethod('post');
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $response = $this->getResponse();
        $data = json_decode($response->outputBody());
        $this->assertEquals($data->error, 1);
        $this->assertRedirect();

        $this->resetRequest();
        $this->resetResponse();
        $params = array('controller' => 'form', 'action' => 'approve', 'formId' => 555);
        $this->request->setMethod('post');
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $response = $this->getResponse();
        $data = json_decode($response->outputBody());
        $this->assertEquals($data->error, 1);
        $this->assertRedirect();
    }

}

