<?php

class ApprovalTest extends TestCase {

    private $objectManager;
    private $userId;
    private $userId1;
    private $userId2;
    private $nodeId;
    private $nodeId1;
    private $nodeId2;
    private $nodeId3;
    private $elementId1;
    private $elementId2;
    private $resourceId3;
    private $formId;
    private $scenario;
    private $scenarioId;
    private $contragentId;

    public function setUp() {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        $session = new Zend_Session_Namespace('Auth');
        $session->domainId = 1;
        $this->objectManager = new Application_Model_ObjectsManager(1);
        $this->objectManager->dbLink->delete('approval_entry');

        $this->objectManager->dbLink->delete('item');
        $this->objectManager->dbLink->delete('form');
        $this->objectManager->dbLink->delete('privilege');
        $this->objectManager->dbLink->delete('resource');
        $this->objectManager->dbLink->delete('user_group');
        $this->objectManager->dbLink->delete('scenario_entry');
        $this->objectManager->dbLink->delete('scenario_assignment');
        $this->objectManager->dbLink->delete('scenario');
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
        $userArray2 = array('userName' => 'user3', 'domainId' => 1, 'login' => 'user login3', 'password' => 'user password', 'positionId' => $positionId1);
        $user2 = new Application_Model_User($userArray2);
        $this->userId2 = $this->objectManager->saveObject($user2);

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
        $privilegeArray2 = array('objectType' => 'node', 'objectId' => $this->nodeId1, 'userId' => $this->userId1, 'privilege' => 'write', 'domainId' => 1);
        $privilege2 = new Application_Model_Privilege($privilegeArray2);
        $this->objectManager->saveObject($privilege2);
        $privilegeArray3 = array('objectType' => 'resource', 'objectId' => $resourceId, 'userId' => $this->userId, 'privilege' => 'read', 'domainId' => 1);
        $privilege3 = new Application_Model_Privilege($privilegeArray3);
        $this->objectManager->saveObject($privilege3);
        $privilegeArray4 = array('objectType' => 'node', 'objectId' => $this->nodeId, 'userId' => $this->userId1, 'privilege' => 'approve', 'domainId' => 1);
        $privilege4 = new Application_Model_Privilege($privilegeArray4);
        $this->objectManager->saveObject($privilege4);
        $privilegeArray5 = array('objectType' => 'node', 'objectId' => $this->nodeId1, 'userId' => $this->userId, 'privilege' => 'approve', 'domainId' => 1);
        $privilege5 = new Application_Model_Privilege($privilegeArray5);
        $this->objectManager->saveObject($privilege5);
        $privilegeArray6 = array('objectType' => 'node', 'objectId' => $this->nodeId1, 'userId' => $this->userId2, 'privilege' => 'approve', 'domainId' => 1);
        $privilege6 = new Application_Model_Privilege($privilegeArray6);
        $this->objectManager->saveObject($privilege6);

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
        $entryArray3 = array('domainId' => 1, 'orderPos' => 3, 'userId' => $this->userId2, 'active' => true);
        $scenarioArray1 = array('scenarioName' => 'eName1', 'active' => false, 'domainId' => 1, 'entries' => array(0 => $entryArray1, 1 => $entryArray2, 2 => $entryArray3));
        $this->scenario = new Application_Model_Scenario($scenarioArray1);
        $this->scenarioId = $this->objectManager->saveObject($this->scenario);
        $this->scenario = $this->objectManager->getObject('scenario', $this->scenarioId);

// Assignment
        $assignmentArray = array('domainId' => 1, 'nodeId' => $this->nodeId1, 'scenarioId' => $this->scenarioId);
        $assignment = new Application_Model_ScenarioAssignment($assignmentArray);
        $assignmentId = $this->objectManager->saveObject($assignment);
        $this->assertTrue(is_int($assignmentId));


// FORM
        $itemArray1 = array('itemName' => 'item1', 'domainId' => 1, 'value' => 55.4, 'elementId' => $this->elementId1, 'active' => true);
        $itemArray2 = array('itemName' => 'item2', 'domainId' => 1, 'value' => 22.1, 'elementId' => $this->elementId2, 'active' => true);
        $formArray1 = array('userId' => $this->userId1, 'formName' => 'fName1', 'nodeId' => $this->nodeId1, 'items' => array(0 => $itemArray1, 1 => $itemArray2), 'domainId' => 1, 'active' => true, 'contragentId' => $this->contragentId, 'expgroup'=>'CAPEX');
        $form = new Application_Model_Form($formArray1);
//        Zend_Debug::dump($form);
        $this->assertTrue($form->isValid());
        $this->formId = $this->objectManager->saveObject($form);

        parent::setUp();
    }

    public function tearDown() {
        $this->objectManager->dbLink->delete('approval_entry');
        $this->objectManager->dbLink->delete('item');
        $this->objectManager->dbLink->delete('form');
        $this->objectManager->dbLink->delete('element');
        $this->objectManager->dbLink->delete('user_group');
        $this->objectManager->dbLink->delete('privilege');
        $this->objectManager->dbLink->delete('scenario_entry');
        $this->objectManager->dbLink->delete('scenario_assignment');
        $this->objectManager->dbLink->delete('scenario');
        $this->objectManager->dbLink->delete('user');
        $this->objectManager->dbLink->delete('position');
        $this->objectManager->dbLink->delete('node');
        $this->objectManager->dbLink->delete('contragent');
    }

    public function testApprovalEntry() {
        $entryArray = array('domainId' => 1, 'userId' => $this->userId, 'formId' => $this->formId, 'decision' => 'approve');
        $entry = new Application_Model_ApprovalEntry($entryArray);
        $this->assertTrue($entry->isValid());
        $this->assertEquals($entry->userId, $this->userId);
        $this->assertEquals($entry->decision, 'approve');
        $entryId = $this->objectManager->saveObject($entry);
        $this->assertTrue(is_int($entryId));
    }

    public function testApproveAction() {
        $session = new Zend_Session_Namespace('Auth');
        $session->domainId = 1;
        $session->userId = $this->userId;
        $result = $this->objectManager->approveForm($this->formId, $this->userId, 'approve');
        $this->assertTrue(is_int($result));
        $session->userId = $this->userId1;
        $result1 = $this->objectManager->approveForm($this->formId, $this->userId1, 'approve');
        $this->assertTrue(is_int($result1));
        $result2 = $this->objectManager->approveForm($this->formId, $this->userId1, 'approve');
        $this->assertTrue(is_int($result2));
        $approvals = $this->objectManager->getAllObjects('ApprovalEntry', array(0 => array('column' => 'formId', 'operand' => $this->formId)));
        $this->assertEquals(count($approvals), 2);
        $this->assertTrue(!empty($approvals));
    }

    /**
     * @expectedException Exception
     */
    public function testWrongOrderApproveAction() {
        $session = new Zend_Session_Namespace('Auth');
        $session->domainId = 1;
        $result = $this->objectManager->approveForm($this->formId, $this->userId1, 'approve');
    }

    public function testOrderApproveAction() {
        $session = new Zend_Session_Namespace('Auth');
        $session->domainId = 1;

        $this->assertTrue($this->objectManager->isApprovalAllowed($this->formId, $this->userId));
        $this->assertFalse($this->objectManager->isApprovalAllowed($this->formId, $this->userId1));
        $this->assertFalse($this->objectManager->isApprovalAllowed($this->formId, $this->userId2));
        $this->objectManager->approveForm($this->formId, $this->userId, 'decline');
        $this->assertTrue($this->objectManager->isApprovalAllowed($this->formId, $this->userId));
        $this->assertTrue($this->objectManager->isApprovalAllowed($this->formId, $this->userId1));
        $this->assertFalse($this->objectManager->isApprovalAllowed($this->formId, $this->userId2));
        $this->objectManager->approveForm($this->formId, $this->userId, 'approve');
        $this->assertTrue($this->objectManager->isApprovalAllowed($this->formId, $this->userId));
        $this->assertTrue($this->objectManager->isApprovalAllowed($this->formId, $this->userId1));
        $this->assertFalse($this->objectManager->isApprovalAllowed($this->formId, $this->userId2));
        $this->objectManager->approveForm($this->formId, $this->userId1, 'decline');
        $this->assertFalse($this->objectManager->isApprovalAllowed($this->formId, $this->userId));
        $this->assertTrue($this->objectManager->isApprovalAllowed($this->formId, $this->userId1));
        $this->assertTrue($this->objectManager->isApprovalAllowed($this->formId, $this->userId2));
        $this->objectManager->approveForm($this->formId, $this->userId1, 'approve');
        $this->assertFalse($this->objectManager->isApprovalAllowed($this->formId, $this->userId));
        $this->assertTrue($this->objectManager->isApprovalAllowed($this->formId, $this->userId1));
        $this->assertTrue($this->objectManager->isApprovalAllowed($this->formId, $this->userId2));
        $this->objectManager->approveForm($this->formId, $this->userId2, 'decline');
        $this->assertFalse($this->objectManager->isApprovalAllowed($this->formId, $this->userId));
        $this->assertFalse($this->objectManager->isApprovalAllowed($this->formId, $this->userId1));
        $this->assertTrue($this->objectManager->isApprovalAllowed($this->formId, $this->userId2));
        $this->objectManager->approveForm($this->formId, $this->userId2, 'approve');
        $this->assertFalse($this->objectManager->isApprovalAllowed($this->formId, $this->userId));
        $this->assertFalse($this->objectManager->isApprovalAllowed($this->formId, $this->userId1));
        $this->assertTrue($this->objectManager->isApprovalAllowed($this->formId, $this->userId2));
    }
    
    public function testEmailListGeneration() {
        $emails = $this->objectManager->getEmailingList($this->formId);
        $this->assertEquals($emails, array('owner'=>'user login2', 'approval'=>'user login'));
        $appr = $this->objectManager->approveForm($this->formId, $this->userId, 'approve');
        $emails1 = $this->objectManager->getEmailingList($this->formId);
        $this->assertEquals($emails1, array('owner'=>'user login2', 'approval'=>'user login2'));
        $appr = $this->objectManager->approveForm($this->formId, $this->userId1, 'approve');
        $emails2 = $this->objectManager->getEmailingList($this->formId);
        $this->assertEquals($emails2, array('owner'=>'user login2', 'approval'=>'user login3'));
    }

}

