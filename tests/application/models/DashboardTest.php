<?php

class DashboardTest extends TestCase {

    private $formManager;
    private $userId;
    private $userId1;
    private $formId1;
    private $formId2;
    private $formId3;
    private $formId4;
    private $contragentId;
    private $nodeId;
    private $nodeId1;

    public function setUp()
    {

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
        $nodeArray = array(
            'nodeName'     => 'First node',
            'parentNodeId' => -1,
            'domainId'     => 1);
        $node = new Application_Model_Node($nodeArray);
        $this->nodeId = $this->formManager->saveObject($node);

        $nodeArray3 = array(
            'nodeName'     => 'First object',
            'parentNodeId' => $this->nodeId,
            'domainId'     => 1);
        $node3 = new Application_Model_Node($nodeArray3);
        $this->nodeId3 = $this->formManager->saveObject($node3);
        $nodeArray1 = array(
            'nodeName'     => 'Second object',
            'parentNodeId' => $this->nodeId,
            'domainId'     => 1);
        $node1 = new Application_Model_Node($nodeArray1);
        $this->nodeId1 = $this->formManager->saveObject($node1);
        $nodeArray2 = array(
            'nodeName'     => 'Third bject',
            'parentNodeId' => $this->nodeId3,
            'domainId'     => 1);
        $node2 = new Application_Model_Node($nodeArray2);
        $this->nodeId2 = $this->formManager->saveObject($node2);

// CONTRAGENT
        $contragentArray = array(
            'contragentName' => 'cName',
            'domainId'       => 1);
        $contragent = new Application_Model_Contragent($contragentArray);
        $this->assertTrue($contragent->isValid());
        $this->contragentId = $this->formManager->saveObject($contragent);
        $this->assertTrue($contragent instanceof Application_Model_Contragent);
        $this->assertTrue(is_int($this->contragentId));

// ELEMENTS
        $elementArray = array(
            'elementName' => 'eName',
            'domainId'    => 1,
            'elementCode' => 34,
            'expgroup'    => 'OPEX');
        $element = new Application_Model_Element($elementArray);
        $this->assertTrue($element->isValid());
        $this->elementId1 = $this->formManager->saveObject($element);
        $elementArray1 = array(
            'elementName' => 'eName1',
            'domainId'    => 1,
            'elementCode' => 44,
            'expgroup'    => 'OPEX');
        $element1 = new Application_Model_Element($elementArray1);
        $this->assertTrue($element1->isValid());
        $this->elementId2 = $this->formManager->saveObject($element1);


// POSITIONS        
        $positionArray = array(
            'positionName' => 'First position',
            'nodeId'       => $this->nodeId,
            'domainId'     => 1);
        $position = new Application_Model_Position($positionArray);
        $positionId = $this->formManager->saveObject($position);
        $positionArray1 = array(
            'positionName' => 'First position',
            'nodeId'       => $this->nodeId1,
            'domainId'     => 1);
        $position1 = new Application_Model_Position($positionArray1);
        $positionId1 = $this->formManager->saveObject($position1);

// USERS        
        $userArray = array(
            'userName'   => 'user1',
            'domainId'   => 1,
            'login'      => 'user@login',
            'password'   => $auth->hashPassword('user password'),
            'positionId' => $positionId);
        $user = new Application_Model_User($userArray);
        $this->userId = $this->formManager->saveObject($user);
        $userArray1 = array(
            'userName'   => 'user2',
            'domainId'   => 1,
            'login'      => 'user@login2',
            'password'   => $auth->hashPassword('user password'),
            'positionId' => $positionId1);
        $user1 = new Application_Model_User($userArray1);
        $this->userId1 = $this->formManager->saveObject($user1);

// RESOURCES
        $resourceArray = array(
            'resourceName' => 'admin',
            'domainId'     => 1);
        $resource = new Application_Model_Resource($resourceArray);
        $resourceId = $this->formManager->saveObject($resource);

// PRIVILEGES        
        $privilegeArray = array(
            'objectType' => 'node',
            'objectId'   => $this->nodeId,
            'userId'     => $this->userId,
            'privilege'  => 'approve',
            'domainId'   => 1);
        $privilege = new Application_Model_Privilege($privilegeArray);
        $this->formManager->saveObject($privilege);
        $privilegeArray1 = array(
            'objectType' => 'node',
            'objectId'   => $this->nodeId,
            'userId'     => $this->userId1,
            'privilege'  => 'read',
            'domainId'   => 1);
        $privilege1 = new Application_Model_Privilege($privilegeArray1);
        $this->formManager->saveObject($privilege1);
        $privilegeArray2 = array(
            'objectType' => 'node',
            'objectId'   => $this->nodeId2,
            'userId'     => $this->userId1,
            'privilege'  => 'write',
            'domainId'   => 1);
        $privilege2 = new Application_Model_Privilege($privilegeArray2);
        $this->formManager->saveObject($privilege2);
        $privilegeArray3 = array(
            'objectType' => 'resource',
            'objectId'   => $resourceId,
            'userId'     => $this->userId,
            'privilege'  => 'read',
            'domainId'   => 1);
        $privilege3 = new Application_Model_Privilege($privilegeArray3);
        $this->formManager->saveObject($privilege3);
        $privilegeArray4 = array(
            'objectType' => 'node',
            'objectId'   => $this->nodeId1,
            'userId'     => $this->userId1,
            'privilege'  => 'read',
            'domainId'   => 1);
        $privilege4 = new Application_Model_Privilege($privilegeArray4);
        $this->formManager->saveObject($privilege4);
        $privilegeArray5 = array(
            'objectType' => 'node',
            'objectId'   => $this->nodeId1,
            'userId'     => $this->userId1,
            'privilege'  => 'write',
            'domainId'   => 1);
        $privilege5 = new Application_Model_Privilege($privilegeArray5);
        $this->formManager->saveObject($privilege5);
        $privilegeArray6 = array(
            'objectType' => 'node',
            'objectId'   => $this->nodeId1,
            'userId'     => $this->userId1,
            'privilege'  => 'approve',
            'domainId'   => 1);
        $privilege6 = new Application_Model_Privilege($privilegeArray6);
        $this->formManager->saveObject($privilege6);
        $privilegeArray7 = array(
            'objectType' => 'node',
            'objectId'   => $this->nodeId1,
            'userId'     => $this->userId,
            'privilege'  => 'approve',
            'domainId'   => 1);
        $privilege7 = new Application_Model_Privilege($privilegeArray7);
        $this->formManager->saveObject($privilege7);

// USERGROUPS        
        $usergroupArray = array(
            'userId'        => $this->userId,
            'role'          => 'admin',
            'domainId'      => 1,
            'userGroupName' => 'administrators');
        $usergroup = new Application_Model_Usergroup($usergroupArray);
        $this->formManager->saveObject($usergroup);
        $usergroupArray1 = array(
            'userId'        => $this->userId1,
            'role'          => 'manager',
            'domainId'      => 1,
            'userGroupName' => 'managers');
        $usergroup1 = new Application_Model_Usergroup($usergroupArray1);
        $this->formManager->saveObject($usergroup1);
// SCENARIO
        $entryArray1 = array(
            'domainId' => 1,
            'orderPos' => 1,
            'userId'   => $this->userId,
            'active'   => true);
        $entryArray2 = array(
            'domainId' => 1,
            'orderPos' => 2,
            'userId'   => $this->userId1,
            'active'   => true);
        $scenarioArray1 = array(
            'scenarioName' => 'eName1',
            'active'       => false,
            'domainId'     => 1,
            'entries'      => array(
                0 => $entryArray1,
                1 => $entryArray2));
        $this->scenario = new Application_Model_Scenario($scenarioArray1);
        $this->scenarioId = $this->formManager->saveObject($this->scenario);
        $this->scenario = $this->formManager->getObject('scenario', $this->scenarioId);

// Assignment
        $assignmentArray = array(
            'domainId'   => 1,
            'nodeId'     => $this->nodeId1,
            'scenarioId' => $this->scenarioId);
        $assignment = new Application_Model_ScenarioAssignment($assignmentArray);
        $assignmentId = $this->formManager->saveObject($assignment);
        $this->assertTrue(is_int($assignmentId));
        // Template
        $templateArray = array(
            'templateName' => 'test template',
            'language'     => 'ua',
            'type'         => 'approved_owner',
            'body'         => '<!DOCTYPE html>
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
</html>',
            'domainId'     => 1);
        $template = new Application_Model_Template($templateArray);
        $id = $this->formManager->saveObject($template);
        $this->assertTrue(is_int($id));
        $template->type = 'approved_next';
        $template->templateName = 'test template 2';
        $template->templateId = NULL;
        $id1 = $this->formManager->saveObject($template);
        $this->assertNotEquals($id1, $id);
        $this->assertTrue(is_int($id1));
        $templateArray = array(
            'templateName' => 'test template',
            'language'     => 'ua',
            'type'         => 'approved_subj_owner',
            'body'         => 'Your invoice "%fname%" was approved.',
            'domainId'     => 1);
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
        $itemArray1 = array(
            'itemName'  => 'item1',
            'domainId'  => 1,
            'value'     => 55.4,
            'userId'    => $this->userId,
            'elementId' => $this->elementId1
        );
        $item1 = new Application_Model_Item($itemArray1);
        $this->assertTrue($item1->isValid());
        $itemArray2 = array(
            'itemName'  => 'item2',
            'domainId'  => 1,
            'value'     => 22.1,
            'userId'    => $this->userId,
            'elementId' => $this->elementId1);
        $item2 = new Application_Model_Item($itemArray2);
        $this->assertTrue($item2->isValid());

        // Create and save form
        $formArray1 = array(
            'userId'       => $this->userId,
            'formName'     => 'fName1',
            'nodeId'       => $this->nodeId,
            'items'        => array(
                0 => $item1,
                1 => $item2),
            'domainId'     => 1,
            'active'       => true,
            'contragentId' => $this->contragentId,
            'expgroup'     => 'OPEX');
        $form1 = new Application_Model_Form($formArray1, $this->userId);
        $this->assertTrue($form1->isValid());
        $this->formId1 = $this->formManager->saveObject($form1);

        $formArray2 = array(
            'userId'       => $this->userId,
            'formName'     => 'fName2',
            'nodeId'       => $this->nodeId,
            'items'        => array(
                0 => $item1,
                1 => $item2),
            'domainId'     => 1,
            'active'       => true,
            'contragentId' => $this->contragentId,
            'expgroup'     => 'OPEX');
        $form2 = new Application_Model_Form($formArray2, $this->userId);
        $this->assertTrue($form2->isValid());
        $this->formId2 = $this->formManager->saveObject($form2);


        $formArray3 = array(
            'userId'       => $this->userId1,
            'formName'     => 'fName3',
            'nodeId'       => $this->nodeId1,
            'items'        => array(
                0 => $item1,
                1 => $item2),
            'domainId'     => 1,
            'active'       => true,
            'contragentId' => $this->contragentId,
            'expgroup'     => 'CAPEX',
            'date'         => '2013-08-23');
        $form3 = new Application_Model_Form($formArray3, $this->userId1);
        $this->assertTrue($form3->isValid());
        $this->formId3 = $this->formManager->saveObject($form3);

        $formArray4 = array(
            'userId'       => $this->userId,
            'formName'     => 'fName4',
            'nodeId'       => $this->nodeId1,
            'items'        => array(
                0 => $item1,
                1 => $item2),
            'domainId'     => 1,
            'active'       => true,
            'contragentId' => $this->contragentId,
            'expgroup'     => 'CAPEX',
            'date'         => '2013-08-23');
        $form4 = new Application_Model_Form($formArray4, $this->userId1);
        $this->assertTrue($form4->isValid());
        $this->formId4 = $this->formManager->saveObject($form4);

        parent::setUp();
    }

    public function tearDown()
    {
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
    }

    public function testGetOwnFormsCurrentMonth()
    {
        $dashboard = new Application_Model_DashboardManager($this->formManager->getObject('user', $this->userId));
        $forms = $dashboard->getOwnFormsCurrentMonth();
        $this->assertEquals(2, count($forms));
        $this->assertEquals('fName1', $forms[0]->formName);
        $this->assertEquals('fName2', $forms[1]->formName);
    }

    public function testGetOwnFormsPrevMonth()
    {
        $dashboard = new Application_Model_DashboardManager($this->formManager->getObject('user', $this->userId));
        $forms = $dashboard->getOwnFormsPrevMonth();
        $this->assertEquals(1, count($forms));
        $this->assertEquals('fName4', $forms[0]->formName);
    }

    public function testGetOwnFormsCurrentYear()
    {
        $dashboard = new Application_Model_DashboardManager($this->formManager->getObject('user', $this->userId));
        $forms = $dashboard->getOwnFormsCurrentYear();
        $this->assertEquals(3, count($forms));
        $this->assertEquals('fName4', $forms[2]->formName);
        $this->assertEquals('fName2', $forms[1]->formName);
        $this->assertEquals('fName1', $forms[0]->formName);
    }

    public function testGetFormsForApproval()
    {
        $dashboard = new Application_Model_DashboardManager($this->formManager->getObject('user', $this->userId));
        $forms = $dashboard->getFormsForApproval();
        $this->assertEquals(1, count($forms));
        $this->assertEquals('fForm3', $forms[0]->formName);
    }

    public function testGetApprovedCurrentMonth()
    {
        $dashboard = new Application_Model_DashboardManager($this->formManager->getObject('user', $this->userId));
        $forms = $dashboard->getFormsForApproval();
        $this->assertEquals(1, count($forms));
        $this->assertEquals('fForm3', $forms[0]->formName);
        $this->formManager->approveForm($this->formId3, $this->userId, 'approve');
        $approved = $dashboard->getApprovedCurrentMonth();
        $this->assertEquals(1, count($approved));
        $this->assertEquals('fForm3', $approved[0]->formName);
    }

    public function testGetApprovedPrevMonth()
    {
        $dashboard = new Application_Model_DashboardManager($this->formManager->getObject('user', $this->userId));
        $forms = $dashboard->getFormsForApproval();
        $this->assertEquals(1, count($forms));
        $this->assertEquals('fForm3', $forms[0]->formName);
        $this->formManager->approveForm($this->formId3, $this->userId, 'approve');
        $approved = $dashboard->getApprovedPrevMonth();
        $this->assertEquals(0, count($approved));
        
    }

    public function testGetApprovedCurrentYear()
    {
        $dashboard = new Application_Model_DashboardManager($this->formManager->getObject('user', $this->userId));
        $forms = $dashboard->getFormsForApproval();
        $this->assertEquals(1, count($forms));
        $this->assertEquals('fForm3', $forms[0]->formName);
        $this->formManager->approveForm($this->formId3, $this->userId, 'approve');
        $approved = $dashboard->getApprovedCurrentYear();
        $this->assertEquals(1, count($approved));
        $this->assertEquals('fForm3', $approved[0]->formName);
        
    }

}