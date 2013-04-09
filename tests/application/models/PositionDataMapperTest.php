<?php

require_once TESTS_PATH . '/application/TestCase.php';

class PositionDataMapperTest extends TestCase {

    private $dataMapper;
    private $nodeId;

    public function setUp() {
        $this->dataMapper = new Application_Model_DataMapper('Application_Model_Position');
        $nodeArray = array('nodeName' => 'First node', 'parentNodeId' => -1, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $this->nodeId = $this->dataMapper->saveObject($node);
        $session = new Zend_Session_Namespace('Auth');
        $session->domainId = 1;
    }

    public function tearDown() {
        $this->dataMapper->dbLink->delete('position');
    }

    public function testPositionSaveNew() {
        $positionArray = array('positionId' => 3, 'positionName' => 'oName', 'nodeId' => $this->nodeId, 'active' => false, 'domainId' => 1);
        $position = new Application_Model_Position($positionArray);
        $id = $this->dataMapper->saveObject($position);
        $this->assertTrue(is_int($id));

        $position2 = $this->dataMapper->getObject($id);
        $this->assertTrue($position2 instanceof Application_Model_Position);
        $positionArray2 = $position2->toArray();
        $positionArray1 = $position->toArray();
        $this->assertEquals($positionArray1, $positionArray2);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testPositionSaveNonValid() {
        $positionArray = array('positionName' => 'lName', 'active' => false);
        $position = new Application_Model_Position($positionArray);
        $id = $this->dataMapper->saveObject($position);
    }

    public function testPositionSaveExisting() {
        $positionArray = array('positionName' => 'oName', 'nodeId' => $this->nodeId, 'active' => false, 'domainId' => 1);
        $position = new Application_Model_Position($positionArray);
        $id = $this->dataMapper->saveObject($position);
        $this->assertTrue(is_int($id));
        $positionArray1 = array('positionName' => 'oName1', 'nodeId' => $this->nodeId, 'active' => false, 'domainId' => 1);
        $position1 = new Application_Model_Position($positionArray1);
        $id1 = $this->dataMapper->saveObject($position1);
        $this->assertTrue(is_int($id1));
        $position2 = $this->dataMapper->getObject($id1);
        $position2->active = true;
        $position2->positionName = 'position';
        $id2 = $this->dataMapper->saveObject($position2);
        $this->assertEquals($id1, $id2);
        $position_original = $this->dataMapper->getObject($id);
        $this->assertEquals($position, $position_original);
        $positionArray3 = $position2->toArray();
        $position3 = $this->dataMapper->getObject($id2);
        $positionArray4 = $position3->toArray();
        $this->assertEquals($positionArray3, $positionArray4);
        $positions = $this->dataMapper->getAllObjects();
        $this->assertEquals($positions, array(0=>$position, 1=>$position3));
    }

    public function testPositionCheckExistance() {
        $positionArray = array('positionId' => 3, 'positionName' => 'oName', 'nodeId' => $this->nodeId, 'active' => false, 'domainId' => 1);
        $position = new Application_Model_Position($positionArray);
        $id = $this->dataMapper->saveObject($position);
        $this->assertTrue(is_int($id));
        $this->assertTrue(is_int($this->dataMapper->checkObjectExistance($position)));
        $this->assertEquals($id, $this->dataMapper->checkObjectExistance($position));
    }

    public function testPositionGet() {
        $positionArray = array('positionId' => 3, 'positionName' => 'oName', 'nodeId' => $this->nodeId, 'active' => false, 'domainId' => 1);
        $position = new Application_Model_Position($positionArray);
        $id = $this->dataMapper->saveObject($position);
        $this->assertTrue(is_int($id));
        $position2 = $this->dataMapper->getObject($id);
        $this->assertEquals($id, $position2->positionId);
        $positionArray2 = $position2->toArray();
        $positionArray3 = $position->toArray();
        $this->assertEquals($positionArray3, $positionArray2);
    }

    public function testNonexistingPositionGet() {
        $this->assertFalse($this->dataMapper->getObject(-1));
    }

    public function testGetAllPositions() {
        $positionArray = array('positionName' => 'oName', 'nodeId' => $this->nodeId, 'active' => false, 'domainId' => 1);
        $position = new Application_Model_Position($positionArray);
        $id = $this->dataMapper->saveObject($position);
        $positionArray1 = array('positionName' => 'oName1', 'nodeId' => $this->nodeId, 'active' => false, 'domainId' => 1);
        $position1 = new Application_Model_Position($positionArray1);
        $id1 = $this->dataMapper->saveObject($position1);
        $positionArray2 = array('positionName' => 'oName2', 'nodeId' => $this->nodeId, 'active' => false, 'domainId' => 1);
        $position2 = new Application_Model_Position($positionArray2);
        $id2 = $this->dataMapper->saveObject($position2);
        $positions = $this->dataMapper->getAllObjects('Application_Model_Position');
        $this->assertEquals($positions, array(0 => $position, 1 =>$position1, 2=>$position2));
    }

}

?>
