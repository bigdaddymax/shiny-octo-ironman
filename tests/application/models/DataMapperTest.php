<?php

require_once TESTS_PATH . '/application/TestCase.php';

class DataMapperTest extends TestCase {

    private $dataMapper;
    private $positionId;

    public function setUp() {
        $this->dataMapper = new Application_Model_DataMapper(1);
        $this->dataMapper->dbLink->delete('scenario_entry');
        $this->dataMapper->dbLink->delete('scenario');
        $this->dataMapper->dbLink->delete('privilege');
        $this->dataMapper->dbLink->delete('user');
        $this->dataMapper->dbLink->delete('contragent');
        $nodeArray = array('nodeName' => 'First node', 'parentNodeId' => -1, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $nodeId = $this->dataMapper->saveObject($node);
        $positionArray = array('positionName' => 'First position', 'nodeId' => $nodeId, 'domainId' => 1);
        $position = new Application_Model_Position($positionArray);
        $this->positionId = $this->dataMapper->saveObject($position);
        $session = new Zend_Session_Namespace('Auth');
        $session->domainId = 1;
    }

    public function tearDown() {
        $this->dataMapper->dbLink->delete('element');

        $this->dataMapper->dbLink->delete('scenario_entry');
        $this->dataMapper->dbLink->delete('scenario');
        $this->dataMapper->dbLink->delete('privilege');
        $this->dataMapper->dbLink->delete('user');
        $this->dataMapper->dbLink->delete('contragent');
        parent::tearDown();
    }

    /**
     * 
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Class name is not set.
     */
    public function testCheckDeleteObjectNoClass() {
        $dataMapper = new Application_Model_DataMapper(1);
        $dataMapper->deleteObject(1);
    }

    /**
     * 
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Class name is not set.
     */
    public function testCheckObjectDependenciesNoClass() {
        $dataMapper = new Application_Model_DataMapper(1);
        $dataMapper->checkObjectDependencies(1, null);
    }

    /**
     * 
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Class name is not set.
     */
    public function testCheckGetAllObjectsNoClass() {
        $dataMapper = new Application_Model_DataMapper(1);
        $dataMapper->getAllObjects();
    }

    public function testCheckObjectExistance() {
        $this->dataMapper->dbLink->insert('user', array('userName' => 'uName',
            'login' => 'uLogin',
            'password' => 'uPassword',
            'domainId' => 1,
            'active' => 1,
            'positionId' => $this->positionId));
        $userArray = array('userName' => 'uName',
            'login' => 'uLogin',
            'password' => 'uPassword',
            'domainId' => 1,
            'active' => 1,
            'positionId' => $this->positionId);
        $userId = $this->dataMapper->dbLink->lastInsertId();
        $user = new Application_Model_User($userArray);
        $userIdGot = $this->dataMapper->checkObjectExistance($user, true);
        $this->assertEquals($userId, $userIdGot);
    }

    public function testPrepareFilter() {
        $dataMapper = new Application_Model_DataMapper(1);
        $filter = $dataMapper->prepareFilter(array(0 => array('column' => 'nodeId', 'operand' => 44)));
        $this->assertEquals($filter, ' WHERE domainId = 1 AND nodeId = 44 ');
    }

    public function testSaveExisting() {
        $this->dataMapper->dbLink->insert('user', array('userName' => 'uName',
            'login' => 'uLogin',
            'password' => 'uPassword',
            'domainId' => 1,
            'active' => 1,
            'positionId' => $this->positionId));
        $userId = $this->dataMapper->dbLink->lastInsertId();
        $this->dataMapper->dbLink->insert('user', array('userName' => 'uName1',
            'login' => 'uLogin1',
            'password' => 'uPassword1',
            'domainId' => 1,
            'active' => 1,
            'positionId' => $this->positionId));
        $userArray = array('userName' => 'uName',
            'login' => 'uLogin',
            'password' => 'uPassword',
            'domainId' => 1,
            'active' => 1,
            'positionId' => $this->positionId);
        $userId1 = $this->dataMapper->dbLink->lastInsertId();
        $user = new Application_Model_User($userArray);
        $user1 = $this->dataMapper->getObject($userId, 'Application_Model_User');
        $user->userId = $userId;
        $this->assertEquals($user, $user1);
        $user->userName = 'testName';
        $userGotId = $this->dataMapper->saveObject($user);
        $this->assertEquals($userGotId, $userId);
        $user2 = $this->dataMapper->getObject($userId, 'Application_Model_User');
        $user5 = $this->dataMapper->getObject($userGotId, 'Application_Model_User');
        $this->assertEquals($user2, $user5);
        $user3 = $this->dataMapper->getObject($userId1, 'Application_Model_User');
        $this->assertEquals($user2->userName, 'testName');
        $this->assertEquals($user3->toArray(), array('userName' => 'uName1',
            'userId' => $userId1,
            'login' => 'uLogin1',
            'password' => 'uPassword1',
            'domainId' => 1,
            'active' => true,
            'positionId' => $this->positionId));
        $this->assertEquals($userGotId, $userId);

        $contragentArray = array('contragentName' => 'cName', 'domainId' => 1);
        $contragent = new Application_Model_Contragent($contragentArray);
        $this->assertTrue($contragent->isValid());
        $contragentId = $this->dataMapper->saveObject($contragent);

        $contragentArray1 = array('contragentName' => 'cName', 'domainId' => 1);
        $contragent1 = new Application_Model_Contragent($contragentArray1);
        $this->assertTrue($contragent1->isValid());
        $contragentId1 = $this->dataMapper->saveObject($contragent1);
        $this->assertEquals($contragentId, $contragentId1);
    }

}
