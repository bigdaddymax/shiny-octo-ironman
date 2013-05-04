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

class PrivilegeDataMapperTest extends TestCase {

    private $dataMapper;
    private $userId;
    
    public function setUp()
    {
        $this->dataMapper = new Application_Model_DataMapper(1, 'Application_Model_Privilege');
        $nodeArray = array('nodeName' => 'First node', 'parentNodeId' => -1, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $nodeId = $this->dataMapper->saveObject($node);
        $positionArray = array('positionName' => 'First position', 'nodeId' => $nodeId, 'domainId' => 1);
        $position = new Application_Model_Position($positionArray);
        $positionId = $this->dataMapper->saveObject($position);
        $userArray = array('userName' => 'user1', 'domainId' => 1, 'login' => 'user login', 'password' => 'user password', 'positionId' => $positionId);
        $user = new Application_Model_User($userArray);
        $this->userId = $this->dataMapper->saveObject($user);
    }
    
    public function tearDown()
    {
        
    }
    
    public function testPrivilegeDataMapperSaveNew()
    {
        $dataMapperArray = array('userId'=>$this->userId,'aclId'=>3,'objectId'=>4, 'objectType' =>'node', 'domainId' => 1, 'privilege' => 'approve');
        $dataMapper = new Application_Model_Privilege($dataMapperArray);
        $this->assertTrue($dataMapper instanceof Application_Model_Privilege);
        $id = $this->dataMapper->saveObject($dataMapper);
        $this->assertTrue(is_int($id));
        $dataMapper2 = $this->dataMapper->getObject($id);
        $this->assertTrue($dataMapper2 instanceof Application_Model_Privilege);
        $dataMapperArray2 = $dataMapper2->toArray();
        $dataMapperArray1 = $dataMapper->toArray();
        $this->assertEquals($dataMapperArray1, $dataMapperArray2);
    }
    
    public function testObjectDataMapperSaveExisting()
    {
        $dataMapperArray = array('userId'=>$this->userId,'aclId'=>3,'objectId'=>4, 'objectType'=>'node', 'domainId' => 1, 'privilege' => 'write');
        $dataMapper = new Application_Model_Privilege($dataMapperArray);
        $id = $this->dataMapper->saveObject($dataMapper);
        $this->assertTrue(is_int($id));
        $dataMapper2 = $this->dataMapper->getObject($id);
        $this->assertTrue($dataMapper2 instanceof Application_Model_Privilege);
        $dataMapperArray2 = $dataMapper2->toArray();
        $dataMapperArray1 = $dataMapper->toArray();
        $this->assertEquals($dataMapperArray1, $dataMapperArray2);
        $id2 = $this->dataMapper->saveObject($dataMapper2);
        
    }
    
    
    /**
     * 
     * @expectedException Exception
     */
    public function testDeleteDependentObject() {
        $nodeArray = array('nodeName' => 'lName', 'parentNodeId' => 8, 'active' => false, 'domainId' => 5);
        $node = new Application_Model_Node($nodeArray);
        $nodeId = $this->dataMapper->saveObject($node);
        $dataMapperArray = array('aclName'=>'oName','aclId'=>3,'nodeId'=>$nodeId, 'domainId' => 4);
        $dataMapper = new Application_Model_Privilege($dataMapperArray);
        $dataMapperId = $this->dataMapper->saveObject($dataMapper);
        $dataMapper = new Application_Model_DataMapper();
        $dataMapper->deleteObject($nodeId, 'Application_Model_Node');
    }
}

?>
