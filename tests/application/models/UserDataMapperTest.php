<?php

require_once TESTS_PATH . '/application/TestCase.php';

class UserDataMapperTest extends TestCase {

    private $dataMapper;
    private $userId;
    private $userId1;
    private $nodeId10;
    private $nodeId9;
    private $nodeId8;
    private $nodeId1;
    private $nodeId2;
    private $nodeId3;
    private $nodeId4;
    private $elementId1;
    private $elementId2;
    private $resourceId;
    private $positionId;

    public function setUp() {
        $this->dataMapper = new Application_Model_DataMapper(1, 'Application_Model_User');
        $this->dataMapper->dbLink->delete('item');
        $this->dataMapper->dbLink->delete('form');
        $this->dataMapper->dbLink->delete('privilege');
        $this->dataMapper->dbLink->delete('resource');
        $this->dataMapper->dbLink->delete('scenario_entry');
        $this->dataMapper->dbLink->delete('scenario_assignment');
        $this->dataMapper->dbLink->delete('scenario');
        $this->dataMapper->dbLink->delete('user_group');
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
//LEVELS
        $nodeArray = array('nodeName' => 'First independent node', 'parentNodeId' => -1, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $nodeId1 = $this->dataMapper->saveObject($node);
        $nodeArray2 = array('nodeName' => 'First dependent node', 'parentNodeId' => $nodeId1, 'domainId' => 1);
        $node2 = new Application_Model_Node($nodeArray2);
        $nodeId2 = $this->dataMapper->saveObject($node2);
        $nodeArray3 = array('nodeName' => 'Second independent node', 'parentNodeId' => -1, 'domainId' => 1);
        $node3 = new Application_Model_Node($nodeArray3);
        $nodeId3 = $this->dataMapper->saveObject($node3);
        $nodeArray4 = array('nodeName' => 'Second dependent node', 'parentNodeId' => $nodeId2, 'domainId' => 1);
        $node4 = new Application_Model_Node($nodeArray4);
        $nodeId4 = $this->dataMapper->saveObject($node4);
        $nodeArray5 = array('nodeName' => 'Third dependent node', 'parentNodeId' => $nodeId3, 'domainId' => 1);
        $node5 = new Application_Model_Node($nodeArray5);
        $nodeId5 = $this->dataMapper->saveObject($node5);
        $nodeArray6 = array('nodeName' => 'Third dependent node', 'parentNodeId' => $nodeId4, 'domainId' => 1);
        $node6 = new Application_Model_Node($nodeArray6);
        $nodeId6 = $this->dataMapper->saveObject($node6);

//ORGOBJECTS
        $nodeArray7 = array('nodeName' => 'First object', 'parentNodeId' => $nodeId1, 'domainId' => 1);
        $node7 = new Application_Model_Node($nodeArray7);
        $nodeId7 = $this->dataMapper->saveObject($node7);
        $nodeArray8 = array('nodeName' => 'Second object', 'parentNodeId' => $nodeId2, 'domainId' => 1);
        $node8 = new Application_Model_Node($nodeArray8);
        $nodeId8 = $this->dataMapper->saveObject($node8);
        $nodeArray9 = array('nodeName' => 'Third bject', 'parentNodeId' => $nodeId2, 'domainId' => 1);
        $node9 = new Application_Model_Node($nodeArray3);
        $nodeId9 = $this->dataMapper->saveObject($node3);
        $nodeArray10 = array('nodeName' => 'Forth bject', 'parentNodeId' => $nodeId3, 'domainId' => 1);
        $node10 = new Application_Model_Node($nodeArray10);
        $nodeId10 = $this->dataMapper->saveObject($node10);

// ELEMENTS
        $elementArray = array('elementName' => 'eName', 'domainId' => 1, 'elementCode' => 34);
        $element = new Application_Model_Element($elementArray);
        $this->assertTrue($element->isValid());
        $this->elementId1 = $this->dataMapper->saveObject($element);
        $this->objectManager = new Application_Model_ObjectsManager(1);
        $elementArray1 = array('elementName' => 'eName', 'domainId' => 1, 'elementCode' => 34);
        $element1 = new Application_Model_Element($elementArray1);
        $this->assertTrue($element1->isValid());
        $this->elementId2 = $this->dataMapper->saveObject($element1);


// POSITIONS        
        $positionArray = array('positionName' => 'First position', 'nodeId' => $nodeId1, 'domainId' => 1);
        $position = new Application_Model_Position($positionArray);
        $positionId = $this->dataMapper->saveObject($position);
        $positionArray1 = array('positionName' => 'First position', 'nodeId' => $nodeId2, 'domainId' => 1);
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
        $this->resourceId = $this->dataMapper->saveObject($resource);

// PRIVILEGES        
        $privilegeArray = array('objectType' => 'node', 'objectId' => $nodeId1, 'userId' => $this->userId, 'privilege' => 'approve', 'domainId' => 1);
        $privilege = new Application_Model_Privilege($privilegeArray);
        $this->dataMapper->saveObject($privilege);
        $privilegeArray1 = array('objectType' => 'node', 'objectId' => $nodeId1, 'userId' => $this->userId1, 'privilege' => 'read', 'domainId' => 1);
        $privilege1 = new Application_Model_Privilege($privilegeArray1);
        $this->dataMapper->saveObject($privilege1);
        $privilegeArray2 = array('objectType' => 'node', 'objectId' => $nodeId2, 'userId' => $this->userId1, 'privilege' => 'write', 'domainId' => 1);
        $privilege2 = new Application_Model_Privilege($privilegeArray2);
        $this->dataMapper->saveObject($privilege2);
        $privilegeArray3 = array('objectType' => 'resource', 'objectId' => $this->resourceId, 'userId' => $this->userId, 'privilege' => 'read', 'domainId' => 1);
        $privilege3 = new Application_Model_Privilege($privilegeArray3);
        $this->dataMapper->saveObject($privilege3);

// USERGROUPS        
        $usergroupArray = array('userId' => $this->userId, 'role' => 'admin', 'domainId' => 1, 'userGroupName' => 'administrators');
        $usergroup = new Application_Model_Usergroup($usergroupArray);
        $this->dataMapper->saveObject($usergroup);
        $usergroupArray1 = array('userId' => $this->userId1, 'role' => 'manager', 'domainId' => 1, 'userGroupName' => 'managers');
        $usergroup1 = new Application_Model_Usergroup($usergroupArray1);
        $this->dataMapper->saveObject($usergroup1);

        $this->nodeId1 = $nodeId1;
        $this->nodeId2 = $nodeId2;
        $this->nodeId3 = $nodeId3;
        $this->nodeId1 = $nodeId1;
        $this->nodeId2 = $nodeId2;
        $this->nodeId3 = $nodeId3;
        $this->nodeId4 = $nodeId4;
        $this->positionId = $positionId;
        parent::setUp();
    }

    public function tearDown() {
        $this->dataMapper->dbLink->delete('item');
        $this->dataMapper->dbLink->delete('form');
        $this->dataMapper->dbLink->delete('privilege');
        $this->dataMapper->dbLink->delete('resource');
        $this->dataMapper->dbLink->delete('scenario_assignment');
        $this->dataMapper->dbLink->delete('scenario_entry');
        $this->dataMapper->dbLink->delete('scenario');
        $this->dataMapper->dbLink->delete('user_group');
        $this->dataMapper->dbLink->delete('user');
        $this->dataMapper->dbLink->delete('position');
        $this->dataMapper->dbLink->delete('node');
    }

    /**
     * 
     * @group userMapper
     */
    public function testUserSaveNew() {
        $userArray = array('userName' => 'oName', 'nodeId' => $this->nodeId1, 'active' => false, 'domainId' => 1, 'login' => 'tLogin', 'positionId' => $this->positionId, 'password' => 'testp');
        $user = new Application_Model_User($userArray);
        $id = $this->dataMapper->saveObject($user);
        $this->assertTrue(is_int($id));

        $user2 = $this->dataMapper->getObject($id);
        $this->assertTrue($user2 instanceof Application_Model_User);
        $auth = new Application_Model_Auth();
        $userArray2 = $user2->toArray();
        unset($userArray2['password']);
        $userArray1 = $user->toArray();
        unset($userArray1['password']);
        $this->assertEquals($userArray1, $userArray2);
        $this->assertTrue($auth->checkUserPassword($user2->login, $user->password));
    }

    /**
     * @expectedException InvalidArgumentException
     * @group userMapper
     */
    public function testUserSaveNonValid() {
        $userArray = array('userName' => 'lName', 'active' => false);
        $user = new Application_Model_User($userArray);
        $id = $this->dataMapper->saveObject($user);
    }

    /**
     * 
     * @group userMapper
     */
    public function testUserSaveExisting() {
        $userArray = array('userName' => 'oName', 'active' => false, 'domainId' => 1, 'login' => 'tLogin', 'positionId' => $this->positionId, 'password' => 'testp');
        $user = new Application_Model_User($userArray);
        $id = $this->dataMapper->saveObject($user);
        $this->assertTrue(is_int($id));
        $user1 = $this->dataMapper->getObject($id);
        $user1->active = true;
        $user1->password = 'testp';
        $id2 = $this->dataMapper->saveObject($user1);
        $this->assertEquals($id, $id2);
        $auth = new Application_Model_Auth();
//        $this->assertTrue($auth->checkUserPassword($id, 'testp'));
        $this->assertTrue($auth->checkUserPassword($user1->login, 'testp'));
        $userArray1 = $user1->toArray();
        unset($userArray1['password']);
        $user2 = $this->dataMapper->getObject($id2);
        $userArray2 = $user2->toArray();
        unset($userArray2['password']);
        $this->assertEquals($userArray1, $userArray2);
    }

    /**
     * 
     * @group userMapper
     */
    public function testUserCheckExistance() {
        $userArray = array('userName' => 'oName', 'nodeId' => $this->nodeId1, 'active' => false, 'domainId' => 1, 'login' => 'tLogin', 'positionId' => $this->positionId, 'password' => 'testp');
        $user = new Application_Model_User($userArray);
        $id = $this->dataMapper->saveObject($user);
        $this->assertTrue(is_int($id));
        $this->assertTrue(is_int($this->dataMapper->checkObjectExistance($user)));
    }

    /**
     * 
     * @group userMapper
     */
    public function testUserGet() {
        $userArray = array('userName' => 'oName', 'active' => false, 'domainId' => 1, 'login' => 'tLogin', 'positionId' => $this->positionId, 'password' => 'testp');
        $user = new Application_Model_User($userArray);
        $id = $this->dataMapper->saveObject($user);
        $this->assertTrue(is_int($id));
        $user2 = $this->dataMapper->getObject($id);
        $this->assertEquals($id, $user2->userId);
        $userArray2 = $user2->toArray();
        $userArray3 = $user->toArray();
        unset($userArray2['password']);
        unset($userArray3['password']);
        $this->assertEquals($userArray3, $userArray2);
        $auth = new Application_Model_Auth();
        $this->assertTrue($auth->checkUserPassword($user2->login, 'testp'));
        $this->assertTrue($auth->checkUserPassword($user->login, 'testp'));
        $dataMapper = new Application_Model_DataMapper(1);
        $user3 = $dataMapper->getObject($id, 'Application_Model_User');
        $userArray4 = $user3->toArray();
        unset($userArray['userId']);
        unset($userArray4['userId']);

        unset($userArray['password']);
        unset($userArray4['password']);
        $this->assertTrue($auth->checkUserPassword($user3->login, 'testp'));
        $this->assertEquals($userArray, $userArray4);
    }

    /**
     * 
     * @group userMapper
     */
    public function testNonexistingUserGet() {
        $this->assertFalse($this->dataMapper->getObject(-1));
    }

    /**
     * @expectedException InvalidArgumentException
     * 
     * @group userMapper
     */
    public function testGetInvalidArguments() {
        $dataMapper = new Application_Model_DataMapper(1);
        $dataMapper->getObject(3);
        $dataMapper->getObject();
    }

}
