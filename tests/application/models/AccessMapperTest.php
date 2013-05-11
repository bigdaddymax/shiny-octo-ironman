<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PrivilegeDataMapper
 *
 * @author Max
 */
require_once TESTS_PATH . '/application/TestCase.php';

class AccessMapperTest extends TestCase {

    private $object;
    private $objectManager;
    private $userId;
    private $userId1;
    private $nodeId;
    private $nodeId5;
    private $nodeId1;
    private $nodeId2;
    private $nodeId3;
    private $nodeId4;
    private $resource1;
    private $resource2;
    private $resource3;
    private $config;

    public function setUp() {
        /*
         * Lets clear all tables before we start
         */
        $this->config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
        $this->objectManager = new Application_Model_ObjectsManager(1);
        $this->objectManager->dbLink->delete('item');
        $this->objectManager->dbLink->delete('form');
        $this->objectManager->dbLink->delete('privilege');
        $this->objectManager->dbLink->delete('resource');
        $this->objectManager->dbLink->delete('user_group');
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

// LEVELS     
        $nodeArray = array('nodeName' => 'First node', 'parentNodeId' => -1, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $nodeId = $this->objectManager->saveObject($node);
        $nodeArray1 = array('nodeName' => 'Second node', 'parentNodeId' => $nodeId, 'domainId' => 1);
        $node1 = new Application_Model_Node($nodeArray1);
        $nodeId1 = $this->objectManager->saveObject($node1);
        $nodeArray2 = array('nodeName' => 'Third node', 'parentNodeId' => $nodeId1, 'domainId' => 1);
        $node2 = new Application_Model_Node($nodeArray2);
        $nodeId2 = $this->objectManager->saveObject($node2);
        $nodeArray4 = array('nodeName' => 'First object', 'parentNodeId' => -1, 'domainId' => 1);
        $node4 = new Application_Model_Node($nodeArray4);
        $nodeId4 = $this->objectManager->saveObject($node4);
        $nodeArray5 = array('nodeName' => 'Second object', 'parentNodeId' => $nodeId, 'domainId' => 1);
        $node5 = new Application_Model_Node($nodeArray5);
        $nodeId5 = $this->objectManager->saveObject($node5);
        $nodeArray6 = array('nodeName' => 'Third object', 'parentNodeId' => $nodeId4, 'domainId' => 1);
        $node6 = new Application_Model_Node($nodeArray6);
        $nodeId6 = $this->objectManager->saveObject($node6);
        $nodeArray3 = array('nodeName' => 'Forth object', 'parentNodeId' => $nodeId4, 'domainId' => 1);
        $node3 = new Application_Model_Node($nodeArray3);
        $nodeId3 = $this->objectManager->saveObject($node3);

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
        $resourceArray1 = array('resourceName' => 'element', 'domainId' => 1);
        $resource1 = new Application_Model_Resource($resourceArray1);
        $resourceId1 = $this->objectManager->saveObject($resource1);

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
        $privilegeArray4 = array('objectType' => 'resource', 'objectId' => $resourceId1, 'userId' => $this->userId1, 'privilege' => 'read', 'domainId' => 1);
        $privilege4 = new Application_Model_Privilege($privilegeArray4);
        $this->objectManager->saveObject($privilege4);
        $privilegeArray5 = array('objectType' => 'node', 'objectId' => $nodeId3, 'userId' => $this->userId1, 'privilege' => 'write', 'domainId' => 1);
        $privilege5 = new Application_Model_Privilege($privilegeArray5);
        $this->objectManager->saveObject($privilege5);

// USERGROUPS
        $usergroupArray = array('userId' => $this->userId, 'role' => 'admin', 'domainId' => 1, 'userGroupName' => 'administrators');
        $usergroup = new Application_Model_Usergroup($usergroupArray);
        $this->objectManager->saveObject($usergroup);
        $usergroupArray1 = array('userId' => $this->userId1, 'role' => 'manager', 'domainId' => 1, 'userGroupName' => 'managers');
        $usergroup1 = new Application_Model_Usergroup($usergroupArray1);
        $this->objectManager->saveObject($usergroup1);

        $this->nodeId = $nodeId;
        $this->nodeId1 = $nodeId1;
        $this->nodeId2 = $nodeId2;
        $this->nodeId3 = $nodeId3;
        $this->nodeId4 = $nodeId4;
        $this->nodeId5 = $nodeId5;
        $session = new Zend_Session_Namespace('Auth');
        $session->domainId = 1;
    }

    public function tearDown() {
        $this->objectManager->dbLink->delete('item');
        $this->objectManager->dbLink->delete('form');
        $this->objectManager->dbLink->delete('privilege');
        $this->objectManager->dbLink->delete('resource');
        $this->objectManager->dbLink->delete('user_group');
        $this->objectManager->dbLink->delete('approval_entry');
        $this->objectManager->dbLink->delete('user');
        $this->objectManager->dbLink->delete('position');
        $this->objectManager->dbLink->delete('node');
    }

    public function testGetAllowedObjectIds() {
        $this->object = new Application_Model_AccessMapper($this->userId, 1);
        $allowedObjects = $this->object->getAllowedObjectIds();
        $testObjects = array('approve' => array($this->nodeId));
        $this->assertEquals($allowedObjects, $testObjects);
    }

    public function testResourcesAccess() {
        $this->objectManager = new Application_Model_ObjectsManager(1);
        $this->object = new Application_Model_AccessMapper($this->userId, 1);
        $login1 = $this->objectManager->getObject('User', $this->userId);
        $login2 = $this->objectManager->getObject('User', $this->userId1);
        $this->assertTrue($login1 instanceof Application_Model_User);
        $this->assertTrue($login2 instanceof Application_Model_User);
        $this->assertTrue($this->object->isAllowed('admin', 'read'));
        $this->assertTrue($this->object->isAllowed('element', 'read'));
        $this->object->reinit($login2->userId, 1);
        $this->assertFalse($this->object->isAllowed('admin', 'read'));
        $this->assertTrue($this->object->isAllowed('element', 'read'));
//        $this->expectOutputString('ttt');
    }

    public function testACLPrivileges() {
        $this->objectManager = new Application_Model_ObjectsManager(1);
        $login1 = $this->objectManager->getObject('User', $this->userId);
        $login2 = $this->objectManager->getObject('User', $this->userId1);
        $privilege = new Application_Model_AccessMapper($login1->userId, 1);
//        echo $login1->login;
//        Zend_Debug::dump($privilege);
        $this->assertFalse($privilege->isAllowed('node', 'read', $this->nodeId));
        $this->assertTrue($privilege->isAllowed('node', 'approve', $this->nodeId));
        $this->assertFalse($privilege->isAllowed('node', 'write', $this->nodeId));

        $privilege->reinit($login2->userId, 1);
        $this->assertTrue($privilege->isAllowed('node', 'read', $this->nodeId));
        $this->assertFalse($privilege->isAllowed('node', 'approve', $this->nodeId));
        $this->assertFalse($privilege->isAllowed('node', 'write', $this->nodeId));
        $this->assertTrue($privilege->isAllowed('node', 'write', $this->nodeId1));
        $this->assertTrue($privilege->isAllowed('node', 'read', $this->nodeId));
        $this->assertTrue($privilege->isAllowed('element', 'read'));
        $this->assertTrue($privilege->isAllowed('node', 'write', $this->nodeId3));
    }

    public function testDefaultLoginPrivileges() {
        $acl = new Application_Model_AccessMapper($this->userId, 1);
        $this->assertTrue($acl->isAllowed('admin', 'read'));
    }

    public function testCredentialsRetrieval() {
        $this->objectManager = new Application_Model_ObjectsManager(1);
        $login1 = $this->objectManager->getObject('User', $this->userId);
        $login2 = $this->objectManager->getObject('User', $this->userId1);
        $privilege = new Application_Model_AccessMapper($login1->userId, 1);
        $node = $this->objectManager->getObject('Node', $this->nodeId4);
        //       $this->assertFalse($node);
        $this->assertEquals($node->nodeId, $this->nodeId4);
        $credentials1 = array('approve' => array($this->nodeId));
        $credentials2 = array('read' => array($this->nodeId), 'write' => array($this->nodeId1, $this->nodeId3));
        $this->assertEquals($credentials1, $privilege->getAllowedObjectIds());
        $privilege->reinit($login2->userId, 1);
//        Zend_Debug::dump($privilege->getAllowedOrgobjectIds());
        $this->assertEquals($credentials2, $privilege->getAllowedObjectIds());
//        $filterArray = $this->objectManager->createAccessFilterArray($login2->userId);
        //      $filter = $this->objectManager->prepareFilter($filterArray);
        //    $this->assertEquals($filter, ' WHERE domainId = 1  AND nodeId IN (' . $this->nodeId . ') ');
    }

}

?>
