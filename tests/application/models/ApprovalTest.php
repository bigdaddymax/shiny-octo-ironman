<?php

class ApprovalTest extends TestCase {

    private $objectManager;
    private $dataMapper;
    private $userId;
    private $userId1;
    private $userId2;
    private $userId3;
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
        $this->objectManager = new Application_Model_FormsManager(1);
        $this->dataMapper = new Application_Model_DataMapper();
        $this->dataMapper->dbLink->delete('approval_entry');

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
        $userArray = array('userName' => 'user1', 'domainId' => 1, 'login' => 'user login1', 'password' => 'user password', 'positionId' => $positionId);
        $user1 = new Application_Model_User($userArray);
        $this->userId1 = $this->objectManager->saveObject($user1);
        $userArray1 = array('userName' => 'user2', 'domainId' => 1, 'login' => 'user login2', 'password' => 'user password', 'positionId' => $positionId1);
        $user2 = new Application_Model_User($userArray1);
        $this->userId2 = $this->objectManager->saveObject($user2);
        $userArray3 = array('userName' => 'user3', 'domainId' => 1, 'login' => 'user login3', 'password' => 'user password', 'positionId' => $positionId1);
        $user3 = new Application_Model_User($userArray3);
        $this->userId3 = $this->objectManager->saveObject($user3);
        $userArray4 = array('userName' => 'user4', 'domainId' => 1, 'login' => 'user login4', 'password' => 'user password', 'positionId' => $positionId1);
        $user4 = new Application_Model_User($userArray4);
        $this->userId4 = $this->objectManager->saveObject($user4);

// RESOURCES
        $resourceArray = array('resourceName' => 'admin', 'domainId' => 1);
        $resource = new Application_Model_Resource($resourceArray);
        $resourceId = $this->objectManager->saveObject($resource);

// PRIVILEGES        
        $privilegeArray = array('objectType' => 'node', 'objectId' => $this->nodeId, 'userId' => $this->userId1, 'privilege' => 'approve', 'domainId' => 1);
        $privilege = new Application_Model_Privilege($privilegeArray);
//        $this->objectManager->saveObject($privilege);
        $privilegeArray1 = array('objectType' => 'node', 'objectId' => $this->nodeId, 'userId' => $this->userId2, 'privilege' => 'read', 'domainId' => 1);
        $privilege1 = new Application_Model_Privilege($privilegeArray1);
        $this->objectManager->saveObject($privilege1);
        $privilegeArray4 = array('objectType' => 'node', 'objectId' => $this->nodeId, 'userId' => $this->userId2, 'privilege' => 'approve', 'domainId' => 1);
        $privilege4 = new Application_Model_Privilege($privilegeArray4);
        $this->objectManager->saveObject($privilege4);
                
// On node1 "user login2" can write, "user login", "user login3" and "user login4" can approve
        $privilegeArray2 = array('objectType' => 'node', 'objectId' => $this->nodeId1, 'userId' => $this->userId2, 'privilege' => 'write', 'domainId' => 1);
        $privilege2 = new Application_Model_Privilege($privilegeArray2);
        $this->objectManager->saveObject($privilege2);
        $privilegeArray5 = array('objectType' => 'node', 'objectId' => $this->nodeId1, 'userId' => $this->userId1, 'privilege' => 'approve', 'domainId' => 1);
        $privilege5 = new Application_Model_Privilege($privilegeArray5);
        $this->objectManager->saveObject($privilege5);
        $privilegeArray6 = array('objectType' => 'node', 'objectId' => $this->nodeId1, 'userId' => $this->userId4, 'privilege' => 'approve', 'domainId' => 1);
        $privilege6 = new Application_Model_Privilege($privilegeArray6);
        $this->objectManager->saveObject($privilege6);
        $privilegeArray7 = array('objectType' => 'node', 'objectId' => $this->nodeId1, 'userId' => $this->userId3, 'privilege' => 'approve', 'domainId' => 1);
        $privilege7 = new Application_Model_Privilege($privilegeArray7);
        $this->objectManager->saveObject($privilege7);
///        $privilegeArray3 = array('objectType' => 'resource', 'objectId' => $resourceId, 'userId' => $this->userId1, 'privilege' => 'read', 'domainId' => 1);
//        $privilege3 = new Application_Model_Privilege($privilegeArray3);
//        $this->objectManager->saveObject($privilege3);

// USERGROUPS        
        $usergroupArray = array('userId' => $this->userId1, 'role' => 'admin', 'domainId' => 1, 'userGroupName' => 'administrators');
        $usergroup = new Application_Model_Usergroup($usergroupArray);
        $this->objectManager->saveObject($usergroup);
        $usergroupArray1 = array('userId' => $this->userId1, 'role' => 'manager', 'domainId' => 1, 'userGroupName' => 'managers');
        $usergroup1 = new Application_Model_Usergroup($usergroupArray1);
        $this->objectManager->saveObject($usergroup1);

// SCENARIO
        $entryArray1 = array('domainId' => 1, 'orderPos' => 1, 'userId' => $this->userId1, 'active' => true);
        $entryArray2 = array('domainId' => 1, 'orderPos' => 2, 'userId' => $this->userId2, 'active' => true);
        $entryArray3 = array('domainId' => 1, 'orderPos' => 3, 'userId' => $this->userId3, 'active' => true);
        $entryArray4 = array('domainId' => 1, 'orderPos' => 4, 'userId' => $this->userId4, 'active' => true);
        $scenarioArray1 = array('scenarioName' => 'eName1', 'active' => false, 'domainId' => 1, 'entries' => array(0 => $entryArray1, 1 => $entryArray2, 2 => $entryArray3, 3 => $entryArray4));
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
        $formArray1 = array('userId' => $this->userId2, 'formName' => 'fName1', 'nodeId' => $this->nodeId1, 'items' => array(0 => $itemArray1, 1 => $itemArray2), 'domainId' => 1, 'active' => true, 'contragentId' => $this->contragentId, 'expgroup'=>'CAPEX');
        $form = new Application_Model_Form($formArray1);
//        Zend_Debug::dump($form);
        $this->assertTrue($form->isValid());
        $this->formId = $this->objectManager->saveObject($form);

        parent::setUp();
    }

    public function tearDown() {
        $this->dataMapper->dbLink->delete('approval_entry');
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
        $this->dataMapper->dbLink->delete('contragent');
    }

    public function testApprovalEntry() {
        $entryArray = array('domainId' => 1, 'userId' => $this->userId1, 'formId' => $this->formId, 'decision' => 'approve');
        $entry = new Application_Model_ApprovalEntry($entryArray);
        $this->assertTrue($entry->isValid());
        $this->assertEquals($entry->userId, $this->userId1);
        $this->assertEquals($entry->decision, 'approve');
        $entryId = $this->objectManager->saveObject($entry);
        $this->assertTrue(is_int($entryId));
    }

    public function testGetApprovalsStatus() {
        $approvalArray =  array(2=>array('userId'=>$this->userId1,
                                         'decision'=>null,
                                         'formId'=>$this->formId,
                                         'userName'=>'user1',
                                         'login'=>'user login1',
                                         'orderPos'=>'1',
                                         'date'=> NULL
                                         ),
                                1=>array('userId'=>$this->userId3,
                                         'decision'=>null,
                                         'formId'=>$this->formId,
                                         'userName'=>'user3',
                                         'login'=>'user login3',
                                         'orderPos'=>'3',
                                         'date'=> NULL
                                         ),
                                0=>array('userId'=>$this->userId4,
                                         'decision'=>null,
                                         'formId'=>$this->formId,
                                         'userName'=>'user4',
                                         'login'=>'user login4',
                                         'orderPos'=>'4',
                                         'date'=> NULL
                                         ),
            );
        $approvalsStatus = $this->dataMapper->getApprovalStatus($this->formId);
        $this->assertEquals($approvalsStatus, $approvalArray);
    }


    public function testApproveAction() {
        $session = new Zend_Session_Namespace('Auth');
        $session->domainId = 1;
        $session->userId = $this->userId1;
        $result = $this->objectManager->approveForm($this->formId, $this->userId1, 'approve');
        $this->assertTrue(is_int($result));
        $approve1 = $this->objectManager->getObject('approvalEntry', $result);
        $this->assertTrue($approve1 instanceof Application_Model_ApprovalEntry);
        $session->userId = $this->userId1;
        $result1 = $this->objectManager->approveForm($this->formId, $this->userId3, 'approve');
        $this->assertTrue(is_int($result1));
        $result2 = $this->objectManager->approveForm($this->formId, $this->userId3, 'approve');
        $this->assertTrue(is_int($result2));
        $approvals = $this->objectManager->getAllObjects('ApprovalEntry', array(0 => array('column' => 'formId', 'operand' => $this->formId)));
        $this->assertEquals(count($approvals), 2);
        $this->assertTrue(!empty($approvals));
    }

    /**
     * @expectedException WrongApprovalOrder
     */
    public function testWrongOrderApproveAction() {
        $session = new Zend_Session_Namespace('Auth');
        $session->domainId = 1;
        $result = $this->objectManager->approveForm($this->formId, $this->userId4, 'approve');
    }

    public function testOrderApproveAction() {
        $session = new Zend_Session_Namespace('Auth');
        $session->domainId = 1;

        $this->assertTrue($this->objectManager->isApprovalAllowed($this->formId, $this->userId1));
        $this->assertFalse($this->objectManager->isApprovalAllowed($this->formId, $this->userId3));
        $this->assertFalse($this->objectManager->isApprovalAllowed($this->formId, $this->userId4));
        $this->objectManager->approveForm($this->formId, $this->userId1, 'decline');
        $this->assertTrue($this->objectManager->isApprovalAllowed($this->formId, $this->userId1));
        $this->assertFalse($this->objectManager->isApprovalAllowed($this->formId, $this->userId3));
        $this->assertFalse($this->objectManager->isApprovalAllowed($this->formId, $this->userId4));
        $this->objectManager->approveForm($this->formId, $this->userId1, 'approve');

       $this->assertTrue($this->objectManager->isApprovalAllowed($this->formId, $this->userId1));
        $this->assertTrue($this->objectManager->isApprovalAllowed($this->formId, $this->userId3));
        $this->assertFalse($this->objectManager->isApprovalAllowed($this->formId, $this->userId4));
        $res = $this->objectManager->approveForm($this->formId, $this->userId3, 'decline');
          $app = $this->objectManager->getObject('approvalEntry', $res);   
         $this->assertFalse($this->objectManager->isApprovalAllowed($this->formId, $this->userId1));
        $this->assertTrue($this->objectManager->isApprovalAllowed($this->formId, $this->userId3));
        $this->assertFalse($this->objectManager->isApprovalAllowed($this->formId, $this->userId4));
         $this->objectManager->approveForm($this->formId, $this->userId3, 'approve');
        $this->assertFalse($this->objectManager->isApprovalAllowed($this->formId, $this->userId1));
        $this->assertTrue($this->objectManager->isApprovalAllowed($this->formId, $this->userId3));
        $this->assertTrue($this->objectManager->isApprovalAllowed($this->formId, $this->userId4));
        $this->objectManager->approveForm($this->formId, $this->userId4, 'decline');
        $this->assertFalse($this->objectManager->isApprovalAllowed($this->formId, $this->userId1));
        $this->assertFalse($this->objectManager->isApprovalAllowed($this->formId, $this->userId3));
        $this->assertTrue($this->objectManager->isApprovalAllowed($this->formId, $this->userId4));
        $this->objectManager->approveForm($this->formId, $this->userId4, 'approve');
        $this->assertFalse($this->objectManager->isApprovalAllowed($this->formId, $this->userId1));
        $this->assertFalse($this->objectManager->isApprovalAllowed($this->formId, $this->userId3));
        $this->assertTrue($this->objectManager->isApprovalAllowed($this->formId, $this->userId4));
    }
    
    public function testEmailListGeneration() {
        // Get emails of users that we should inform about form approval
        $emails = $this->objectManager->getEmailingList($this->formId, 'approve');
        $this->assertEquals($emails, array('owner'=>'user login2', 'other'=>array('user login1')));
        
        // Approve form and generate email list again
        $appr = $this->objectManager->approveForm($this->formId, $this->userId1, 'approve');
        $emails1 = $this->objectManager->getEmailingList($this->formId, 'approve');
        $this->assertEquals($emails1, array('owner'=>'user login2', 'other'=>array('user login3')));
        
        // Another approval
        $appr = $this->objectManager->approveForm($this->formId, $this->userId3, 'approve');
        $emails2 = $this->objectManager->getEmailingList($this->formId, 'approve');
        $this->assertEquals($emails2, array('owner'=>'user login2', 'other'=>array('user login4')));

        // Comment form and check emails
        $emails2_c = $this->objectManager->getEmailingList($this->formId, 'comment');
        $this->assertEquals($emails2_c, array('owner'=>'user login2', 'other' =>array('user login1', 'user login3')));
        
        // Another approval
        $appr = $this->objectManager->approveForm($this->formId, $this->userId3, 'approve');
        $emails3 = $this->objectManager->getEmailingList($this->formId, 'approve');
        $this->assertEquals($emails3, array('owner'=>'user login2', 'other'=>array('user login4')));
    }

}

