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
        $elementArray = array('elementName' => 'eName', 'domainId' => 1, 'elementCode' => 34);
        $element = new Application_Model_Element($elementArray);
        $this->assertTrue($element->isValid());
        $this->elementId1 = $this->objectManager->saveObject($element);
        $elementArray1 = array('elementName' => 'eName1', 'domainId' => 1, 'elementCode' => 44);
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

// USERGROUPS        
        $usergroupArray = array('userId' => $this->userId, 'role' => 'admin', 'domainId' => 1, 'userGroupName' => 'administrators');
        $usergroup = new Application_Model_Usergroup($usergroupArray);
        $this->objectManager->saveObject($usergroup);
        $usergroupArray1 = array('userId' => $this->userId1, 'role' => 'manager', 'domainId' => 1, 'userGroupName' => 'managers');
        $usergroup1 = new Application_Model_Usergroup($usergroupArray1);
        $this->objectManager->saveObject($usergroup1);

        parent::setUp();
    }

    public function tearDown() {
        $this->objectManager->dbLink->delete('item');
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
        $formArray1 = array('userId' => $this->userId1, 'formName' => 'fName1', 'nodeId' => $this->nodeId2, 'items' => array(0 => $itemArray1, 1 => $itemArray2), 'domainId' => 1, 'active' => true, 'contragentName' =>'contr name');
        $params = array('controller' => 'form', 'action' => 'add-form');
        $this->request->setMethod('post');
        $this->request->setPost($formArray1);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $response = $this->getResponse();
//        Zend_Debug::dump($accessMapper->getAllowedObjectIds());
        echo $response->outputBody();
        $this->assertController('form');
        $this->assertAction('add-form');
        $objectManager = new Application_Model_ObjectsManager(1);
        $forms = $objectManager->getAllForms();
        $this->assertEquals(count($forms), 1);
        $form = $objectManager->getForm($forms[0]->formId, $this->userId1);
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
        $session = new Zend_Session_Namespace('Auth');
        $this->assertEquals($session->userId, $this->userId1);
        $this->resetRequest();
        $this->resetResponse();
        $formArray1 = array('formName' => 'test', 'nodeId' => $this->nodeId2, 'domainId' => 1,
            'value_2' => 3, 'itemName_2' => 'we', 'value_1' => 1, 'itemName_1' => 'test',
            'elementId_1' => $this->elementId1, 'elementId_2' => $this->elementId2, 'userId' => $this->userId1, 'contragentName' =>'cntr name');
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
        $objectManager = new Application_Model_ObjectsManager(1);
        $form = $objectManager->getForm($data->formId, $this->userId1);
        $this->assertEquals($form->active, true);
        $this->assertEquals($form->public, false);
        $form->public = 1;
        $id = $objectManager->saveForm($form, $this->userId1);
        $form1 = $objectManager->getForm($id, $this->userId1);
        $this->assertEquals($form->active, true);
        $this->assertEquals($form->public, true);

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
        $form = $objectManager->getForm($forms[0]->formId, $this->userId1);
        $this->assertEquals($form->formName, $forms[0]->formName);
        $this->assertEquals($form->formName, 'test');
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
            'elementId_1' => $this->elementId1, 'elementId_2' => $this->elementId2, 'userId' => $this->userId1, 'contragentName' =>'cntr name');
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
        $formId = $forms[0]->formId;
        $params = array('action' => 'open-form', 'controller' => 'form', 'formId'=>$formId);
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
            'elementId_1' => $this->elementId1, 'elementId_2' => $this->elementId2, 'userId' => $this->userId1, 'contragentName' =>'cntr name');
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
        $formId = $forms[0]->formId;
        $params = array('action' => 'edit-form', 'controller' => 'form', 'formId'=>$formId);
        $urlParams = $this->urlizeOptions($params);
        $url = $this->url($urlParams);
        $this->dispatch($url);
        // assertions
        $this->assertController($urlParams['controller']);
        $this->assertAction($urlParams['action']);
    }

}

