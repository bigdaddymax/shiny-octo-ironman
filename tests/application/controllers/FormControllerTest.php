<?php

class FormControllerTest extends Zend_Test_PHPUnit_ControllerTestCase {

    private $formManager;
    private $dataMapper;
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
    private $formId;

    public function setUp() {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        $this->formManager = new Application_Model_FormsManager(1);
        $auth = new Application_Model_Auth();
        $this->dataMapper = new Application_Model_DataMapper();
        $this->dataMapper->dbLink->delete('item');
        $this->dataMapper->dbLink->delete('comment');
        $this->dataMapper->dbLink->delete('approval_entry');
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
        $this->dataMapper->dbLink->delete('contragent');
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
        $this->nodeId = $this->formManager->saveObject($node);

        $nodeArray3 = array('nodeName' => 'First object', 'parentNodeId' => $this->nodeId, 'domainId' => 1);
        $node3 = new Application_Model_Node($nodeArray3);
        $this->nodeId3 = $this->formManager->saveObject($node3);
        $nodeArray1 = array('nodeName' => 'Second object', 'parentNodeId' => $this->nodeId, 'domainId' => 1);
        $node1 = new Application_Model_Node($nodeArray1);
        $this->nodeId1 = $this->formManager->saveObject($node1);
        $nodeArray2 = array('nodeName' => 'Third bject', 'parentNodeId' => $this->nodeId3, 'domainId' => 1);
        $node2 = new Application_Model_Node($nodeArray2);
        $this->nodeId2 = $this->formManager->saveObject($node2);

// CONTRAGENT
        $contragentArray = array('contragentName' => 'cName', 'domainId' => 1);
        $contragent = new Application_Model_Contragent($contragentArray);
        $this->assertTrue($contragent->isValid());
        $this->contragentId = $this->formManager->saveObject($contragent);
        $this->assertTrue($contragent instanceof Application_Model_Contragent);
        $this->assertTrue(is_int($this->contragentId));

// ELEMENTS
        $elementArray = array('elementName' => 'eName', 'domainId' => 1, 'elementCode' => 34, 'expgroup'=>'OPEX');
        $element = new Application_Model_Element($elementArray);
        $this->assertTrue($element->isValid());
        $this->elementId1 = $this->formManager->saveObject($element);
        $elementArray1 = array('elementName' => 'eName1', 'domainId' => 1, 'elementCode' => 44, 'expgroup'=>'OPEX');
        $element1 = new Application_Model_Element($elementArray1);
        $this->assertTrue($element1->isValid());
        $this->elementId2 = $this->formManager->saveObject($element1);


// POSITIONS        
        $positionArray = array('positionName' => 'First position', 'nodeId' => $this->nodeId, 'domainId' => 1);
        $position = new Application_Model_Position($positionArray);
        $positionId = $this->formManager->saveObject($position);
        $positionArray1 = array('positionName' => 'First position', 'nodeId' => $this->nodeId1, 'domainId' => 1);
        $position1 = new Application_Model_Position($positionArray1);
        $positionId1 = $this->formManager->saveObject($position1);

// USERS        
        $userArray = array('userName' => 'user1', 'domainId' => 1, 'login' => 'user@login', 'password' => $auth->hashPassword('user password'), 'positionId' => $positionId);
        $user = new Application_Model_User($userArray);
        $this->userId = $this->formManager->saveObject($user);
        $userArray1 = array('userName' => 'user2', 'domainId' => 1, 'login' => 'user@login2', 'password' => $auth->hashPassword('user password'), 'positionId' => $positionId1);
        $user1 = new Application_Model_User($userArray1);
        $this->userId1 = $this->formManager->saveObject($user1);

// RESOURCES
        $resourceArray = array('resourceName' => 'admin', 'domainId' => 1);
        $resource = new Application_Model_Resource($resourceArray);
        $resourceId = $this->formManager->saveObject($resource);

// PRIVILEGES        
        $privilegeArray = array('objectType' => 'node', 'objectId' => $this->nodeId, 'userId' => $this->userId, 'privilege' => 'approve', 'domainId' => 1);
        $privilege = new Application_Model_Privilege($privilegeArray);
        $this->formManager->saveObject($privilege);
        $privilegeArray1 = array('objectType' => 'node', 'objectId' => $this->nodeId, 'userId' => $this->userId1, 'privilege' => 'read', 'domainId' => 1);
        $privilege1 = new Application_Model_Privilege($privilegeArray1);
        $this->formManager->saveObject($privilege1);
        $privilegeArray2 = array('objectType' => 'node', 'objectId' => $this->nodeId2, 'userId' => $this->userId1, 'privilege' => 'write', 'domainId' => 1);
        $privilege2 = new Application_Model_Privilege($privilegeArray2);
        $this->formManager->saveObject($privilege2);
        $privilegeArray3 = array('objectType' => 'resource', 'objectId' => $resourceId, 'userId' => $this->userId, 'privilege' => 'read', 'domainId' => 1);
        $privilege3 = new Application_Model_Privilege($privilegeArray3);
        $this->formManager->saveObject($privilege3);
        $privilegeArray4 = array('objectType' => 'node', 'objectId' => $this->nodeId1, 'userId' => $this->userId1, 'privilege' => 'read', 'domainId' => 1);
        $privilege4 = new Application_Model_Privilege($privilegeArray4);
        $this->formManager->saveObject($privilege4);
        $privilegeArray5 = array('objectType' => 'node', 'objectId' => $this->nodeId1, 'userId' => $this->userId1, 'privilege' => 'write', 'domainId' => 1);
        $privilege5 = new Application_Model_Privilege($privilegeArray5);
        $this->formManager->saveObject($privilege5);
        $privilegeArray6 = array('objectType' => 'node', 'objectId' => $this->nodeId1, 'userId' => $this->userId1, 'privilege' => 'approve', 'domainId' => 1);
        $privilege6 = new Application_Model_Privilege($privilegeArray6);
        $this->formManager->saveObject($privilege6);
        $privilegeArray7 = array('objectType' => 'node', 'objectId' => $this->nodeId1, 'userId' => $this->userId, 'privilege' => 'approve', 'domainId' => 1);
        $privilege7 = new Application_Model_Privilege($privilegeArray7);
        $this->formManager->saveObject($privilege7);

// USERGROUPS        
        $usergroupArray = array('userId' => $this->userId, 'role' => 'admin', 'domainId' => 1, 'userGroupName' => 'administrators');
        $usergroup = new Application_Model_Usergroup($usergroupArray);
        $this->formManager->saveObject($usergroup);
        $usergroupArray1 = array('userId' => $this->userId1, 'role' => 'manager', 'domainId' => 1, 'userGroupName' => 'managers');
        $usergroup1 = new Application_Model_Usergroup($usergroupArray1);
        $this->formManager->saveObject($usergroup1);
// SCENARIO
        $entryArray1 = array('domainId' => 1, 'orderPos' => 1, 'userId' => $this->userId, 'active' => true);
        $entryArray2 = array('domainId' => 1, 'orderPos' => 2, 'userId' => $this->userId1, 'active' => true);
        $scenarioArray1 = array('scenarioName' => 'eName1', 'active' => false, 'domainId' => 1, 'entries' => array(0 => $entryArray1, 1 => $entryArray2));
        $this->scenario = new Application_Model_Scenario($scenarioArray1);
        $this->scenarioId = $this->formManager->saveObject($this->scenario);
        $this->scenario = $this->formManager->getObject('scenario', $this->scenarioId);

// Assignment
        $assignmentArray = array('domainId' => 1, 'nodeId' => $this->nodeId1, 'scenarioId' => $this->scenarioId);
        $assignment = new Application_Model_ScenarioAssignment($assignmentArray);
        $assignmentId = $this->formManager->saveObject($assignment);
        $this->assertTrue(is_int($assignmentId));
 // Template
        $templateArray = array('templateName'=>'test template', 'language' =>'ua', 'type'=>'approved_owner','body' =>'<!DOCTYPE html>
<html>
    <head>
        <title>Approval needed</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    </head>
    <body>
        <h3>Dear %name%</h3>
        <div>Invoice "%fname%" from %contragent% totally amounted %total% was just approved.</div>
        <div>Please review this invoice and take appropriate action</div>
        <div><a href="%link%">Click here to open invoice</a></div>
    </body>
</html>', 'domainId'=>1);
        $template = new Application_Model_Template($templateArray);
        $id = $this->formManager->saveObject($template);
        $this->assertTrue(is_int($id));
        $template->type = 'approved_next';
        $template->templateName = 'test template 2';
        $template->templateId = NULL;
        $id1 = $this->formManager->saveObject($template);
        $this->assertNotEquals($id1, $id);
        $this->assertTrue(is_int($id1));
        $templateArray = array('templateName'=>'test template', 'language' =>'ua', 'type'=>'approved_subj_owner','body' =>'Your invoice "%fname%" was approved.', 'domainId'=>1);
        $template = new Application_Model_Template($templateArray);
        $id2 = $this->formManager->saveObject($template);
        $this->assertTrue(is_int($id2));
        $this->assertNotEquals($id, $id2);
        $template->type = 'approved_subj_next';
        $template->body = 'Invoice "%fname%" was approved and needs your attention.';
        $template->templateId = NULL;
        $id3 = $this->formManager->saveObject($template);
        $this->assertTrue(is_int($id3));

        // Create form items
        $itemArray1 = array('itemName' => 'item1', 'domainId' => 1, 'value' => 55.4, 'userId' => $this->userId, 'elementId' => $this->elementId1);
        $item1 = new Application_Model_Item($itemArray1);
        $this->assertTrue($item1->isValid());
        $itemArray2 = array('itemName' => 'item2', 'domainId' => 1, 'value' => 22.1, 'userId' => $this->userId, 'elementId' => $this->elementId1);
        $item2 = new Application_Model_Item($itemArray2);
        $this->assertTrue($item2->isValid());

        // Create and save form
        $formArray1 = array('userId' => $this->userId, 'formName' => 'fName1', 'nodeId' => $this->nodeId, 'items' => array(0 => $item1, 1 => $item2), 'domainId' => 1, 'active' => true, 'contragentId' => $this->contragentId, 'expgroup' => 'OPEX');
        $form = new Application_Model_Form($formArray1, $this->userId);
        $this->assertTrue($form->isValid());
        $items = $form->items;
        $this->formId = $this->formManager->saveObject($form);
        parent::setUp();
    }

    public function tearDown() {
        $this->dataMapper->dbLink->delete('item');
        $this->dataMapper->dbLink->delete('comment');
        $this->dataMapper->dbLink->delete('approval_entry');
        $this->dataMapper->dbLink->delete('form');
        $this->dataMapper->dbLink->delete('element');
        $this->dataMapper->dbLink->delete('user_group');
        $this->dataMapper->dbLink->delete('privilege');
        $this->dataMapper->dbLink->delete('scenario_entry');
        $this->dataMapper->dbLink->delete('scenario_assignment');
        $this->dataMapper->dbLink->delete('scenario');
        $this->dataMapper->dbLink->delete('domain_owner');

        $this->dataMapper->dbLink->delete('user');
        $this->dataMapper->dbLink->delete('position');
        $this->dataMapper->dbLink->delete('node');
        $this->dataMapper->dbLink->delete('contragent');
        $this->dataMapper->dbLink->delete('template');
    }

    public function testIndexAction() {
        // Login via web
        $user = array('login' => 'user@login2', 'password' => 'user password');
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
        // Check for errors
        $this->assertController($urlParams['controller']);
        $this->assertAction($urlParams['action']);
        
        // Check for form entries
        $this->assertQuery('#form_' . $this->formId);
    }

    public function testAddNewFormPage(){
       // Login via web
        $user = array('login' => 'user@login2', 'password' => 'user password');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();
        
        $this->dispatch($this->url(array('controller'=>'form', 'action'=>'edit-form')));

        $this->assertQuery('#formName');
        $this->assertQuery('#contragentName');
        $this->assertQuery('#expgroup');
        $this->assertQueryContentContains('#expgroup', 'OPEX'); 
        $this->assertQueryContentContains('#expgroup', 'CAPEX'); 
        $this->assertQuery('#nodeId');
        $this->assertQueryContentContains('#nodeId', $this->nodeId2);
        $this->assertQueryContentContains('#nodeId', $this->nodeId1);
        $this->assertQueryContentContains('#nodeId', 'Third bject');
        $this->assertQueryContentContains('#nodeId', 'Second object');
    }
    
    public function testAddNewForm() {
        // Login via web
        $user = array('login' => 'user@login2', 'password' => 'user password');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->resetRequest();
        $this->resetResponse();
        
        // Check login
        $session = new Zend_Session_Namespace('Auth');
        $this->assertEquals($session->login, 'user@login2');
        $this->assertEquals($session->domainId, 1);
        $this->assertEquals($session->auth, 1);
        $accessMapper = new Application_Model_AccessMapper($session->userId, 1);

        //Add form using general API
        $itemArray1 = array('itemName' => 'item1', 'domainId' => 1, 'value' => 55.4, 'elementId' => $this->elementId1, 'active' => 1);
        $itemArray2 = array('itemName' => 'item2', 'domainId' => 1, 'value' => 22.1, 'elementId' => $this->elementId2, 'active' => 1);
        $formArray1 = array('userId' => $this->userId1, 'formName' => 'fName1','contragentId'=>$this->contragentId, 'nodeId' => $this->nodeId2, 'items' => array(0 => $itemArray1, 1 => $itemArray2), 'domainId' => 1, 'active' => 1, 'contragentName' => 'contr name', 'expgroup'=>'CAPEX');
        $params = array('controller' => 'form', 'action' => 'add-form');
        $this->request->setMethod('post');
        $this->request->setPost($formArray1);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertController('form');
        $this->assertAction('add-form');
        $response = $this->getResponse();
        $response = json_decode($response->outputBody());
        
        // Check added form
        $formManager = new Application_Model_FormsManager(1);
        $forms = $formManager->getAllObjects('form');
        $this->assertEquals(count($forms), 2);
        $form1 = $forms[1];
        $form = $formManager->getObject('form', $form1->formId, $session->userId);
        $this->assertEquals($form->formName, $form1->formName);
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
        // Login user through the web
        $user = array('login' => 'user@login2', 'password' => 'user password');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $session = new Zend_Session_Namespace('Auth');
        $this->assertEquals($session->userId, $this->userId1);
        
        // Prepare form's form data to add form via web
        $this->resetRequest();
        $this->resetResponse();
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
        
        // Check JSON response
        $response = $this->getResponse();
 //       echo ($response->outputBody());
        $response = json_decode($response->outputBody());
        $this->assertEquals($response->error, 0);
        
        // Get form from DB manually
        $formManager = new Application_Model_FormsManager(1);
        $form = $formManager->getObject('form', $response->formId, $this->userId1);
        $this->assertEquals($form->active, 1);
        $this->assertEquals($form->public, 0);
        
        //Make it published
        $form->public = 1;
        $id = $formManager->saveObject($form);
        $form1 = $formManager->getObject('form', $id, $this->userId1);
        $this->assertEquals($form->active, 1);
        $this->assertEquals($form->public, 1);
        $item1 = new Application_Model_Item(array('itemName' => 'test','active'=>1,  'elementId' => $this->elementId1, 'value' => 1, 'domainId' => 1, 'formId' => $id, 'itemId' => $form1->items[1]->itemId));
        $item2 = new Application_Model_Item(array('itemName' => 'we','active'=>1, 'elementId' => $this->elementId2, 'value' => 3, 'domainId' => 1, 'formId' => $id, 'itemId' => $form1->items[0]->itemId));
        $this->assertEquals($form1->items, array(0 => $item2, 1 => $item1));

        $formManager = new Application_Model_FormsManager(1);
        $forms = $formManager->getAllObjects('form');
//        $this->assertEquals('rr', $response->outputBody());
//        $this->assertEquals('tt', $forms);
        $form2 = $forms[1];
        $form = $formManager->getObject('form', $form2->formId, $this->userId1);
        $this->assertEquals($form->formName, $form2->formName);
        $this->assertEquals($form->formName, 'test');
    }

    public function testErrorResponseWhenAddForm() {
        //Login via web
        $user = array('login' => 'user@login2', 'password' => 'user password');
        $params = array('controller' => 'auth', 'action' => 'auth');
        $this->request->setMethod('post');
        $this->request->setPost($user);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $session = new Zend_Session_Namespace('Auth');
        $this->assertEquals($session->userId, $this->userId1);
        $this->resetRequest();
        $this->resetResponse();
        
        // Prepare form that misses items
        $formArray1 = array('formName' => 'test', 'nodeId' => $this->nodeId2,
            'userId' => $this->userId1, 'contragentName' => 'cntr name', 'expgroup'=>'CAPEX');
        $params = array('controller' => 'form', 'action' => 'add-form');

        $this->request->setMethod('post');
        foreach ($formArray1 as $key => $value) {
            if ($value === null) {
                continue;
            }
            $this->request->setPost($key, $value);
        }
        $this->dispatch($this->url($this->urlizeOptions($params)));
        
        // Check JSON response for error
        $response = $this->getResponse();
        $data = json_decode($response->outputBody());
        $this->assertEquals($data->error, 1);
    }

    public function testAddAndPublish() {
        $user = array('login' => 'user@login2', 'password' => 'user password');
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
        
        // Check response
        $response = $this->getResponse();
        $data = json_decode($response->outputBody());
        $formManager = new Application_Model_FormsManager(1);
        $form = $formManager->getObject('form', $data->formId, $this->userId1);
        $this->assertEquals($form->active, 1);
        $this->assertEquals($form->public, 0);
        $this->assertTrue($form->isValid());
        $this->resetRequest();
        $this->resetResponse();

// Publish form
        $params = array('controller' => 'form', 'action' => 'publish-form', 'formId' => $data->formId);
        $this->request->setMethod('post');
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertController('form');
        $this->assertAction('publish-form');

        // Check JSON response
        
        $response = $this->getResponse();
        $response = json_decode($response->outputBody());
        
        // Check results
        $form1 = $formManager->getObject('form', $response->formId, $this->userId1);

        $this->assertTrue($form1->isValid());
        $this->assertEquals($form1->active, 1);
        $this->assertEquals($form1->public, 1);
        $item1 = new Application_Model_Item(array('itemName' => 'test', 'elementId' => $this->elementId1, 'value' => 1, 'domainId' => 1, 'formId' => $form1->formId, 'itemId' => $form1->items[1]->itemId));
        $item2 = new Application_Model_Item(array('itemName' => 'we', 'elementId' => $this->elementId2, 'value' => 3, 'domainId' => 1, 'formId' => $form1->formId, 'itemId' => $form1->items[0]->itemId));
        $this->assertEquals($form1->items, array(0 => $item2, 1 => $item1));
        $formManager = new Application_Model_FormsManager(1);
        $forms = $formManager->getAllObjects('form');
        $form3 = $forms[1];
        $form = $formManager->getObject('form', $form3->formId, $this->userId1);
        $this->assertEquals($form->formName, $form3->formName);
        $this->assertEquals($form->formName, 'test');
    }

    public function testPublishInvalidForm() {
        $user = array('login' => 'user@login2', 'password' => 'user password');
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
        $formManager = new Application_Model_FormsManager(1);
        $form = $formManager->getObject('form', $data->formId, $this->userId1);
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
        $user = array('login' => 'user@login2', 'password' => 'user password');
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

        $forms = $this->formManager->getAllObjects('form');
        $form4 = current($forms);
        $formId = $form4->formId;
        $params = array('action' => 'open-form', 'controller' => 'form', 'formId' => $formId);
        $urlParams = $this->urlizeOptions($params);
        $url = $this->url($urlParams);
        $this->dispatch($url);
// assertions
        $this->assertController($urlParams['controller']);
        $this->assertAction($urlParams['action']);
    }

    public function testEditFormAction() {
        $user = array('login' => 'user@login2', 'password' => 'user password');
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

        $forms = $this->formManager->getAllObjects('form');
        $form5 = current($forms);
        $formId = $form5->formId;
        $params = array('action' => 'edit-form', 'controller' => 'form', 'formId' => $formId);
        $urlParams = $this->urlizeOptions($params);
        $url = $this->url($urlParams);
        $this->dispatch($url);
// assertions
        $this->assertController($urlParams['controller']);
        $this->assertAction($urlParams['action']);
    }

    public function testCommentForm() {
        $user = array('login' => 'user@login2', 'password' => 'user password');
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

        $forms = $this->formManager->getAllObjects('form');
        $form6 = current($forms);
        $formId = $form6->formId;

        $commentArray = array('formId' => $formId, 'comment' => 'bla bla bla bla', 'parentCommentId' => -1, 'active' => 1, 'domainId' => 1);
        $params = array('controller' => 'form', 'action' => 'add-comment');
        $this->request->setMethod('post');
        $this->request->setPost($commentArray);
        $this->dispatch($this->url($this->urlizeOptions($params)));
        $this->assertController('form');
        $this->assertAction('add-comment');
        $comments = $this->formManager->getAllObjects('comment');
        $comments[0]->date = null;
        $commentArray['commentId'] = $comments[0]->commentId;
        $commentArray['userId'] = $this->userId1;
        $this->assertTrue($comments[0] instanceof Application_Model_Comment);
        $this->assertEquals($comments[0]->toArray(), $commentArray);
    }

    public function testApprovalAllowance() {
        // Login as user2 (has write permition on node1)
        $user = array('login' => 'user@login2', 'password' => 'user password');
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
        $forms = $this->formManager->getAllObjects('form');
        $form7 = $forms[1];
        
        $formId = $form7->formId;

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
        $user = array('login' => 'user@login', 'password' => 'user password');
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
        //Test approve incorrect form
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

