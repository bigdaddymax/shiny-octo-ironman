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
    
    public function testCheckObjectExistance(){
    $this->dataMapper->dbLink->insert('user', array('userName'=>'uName',
                                                     'login'=>'uLogin',
                                                     'password' =>'uPassword',
                                                     'domainId'=>1,
                                                     'active'=>1,
                                                     'positionId'=>$this->positionId));    
    $userArray = array('userName'=>'uName',
                                                     'login'=>'uLogin',
                                                     'password' =>'uPassword',
                                                     'domainId'=>1,
                                                     'active'=>1,
                                                     'positionId'=>$this->positionId);
    $userId = $this->dataMapper->dbLink->lastInsertId();
    $user = new Application_Model_User($userArray);
    $userIdGot = $this->dataMapper->checkObjectExistance($user, true);
    $this->assertEquals($userId, $userIdGot);
    }
    
    public function testPrepareFilter(){
        $dataMapper = new Application_Model_DataMapper(1);
        $filter = $dataMapper->prepareFilter(array(0=>array('column'=>'nodeId', 'operand'=>44)));
        $this->assertEquals($filter, ' WHERE domainId = 1 AND nodeId = 44 ');
    }
}
