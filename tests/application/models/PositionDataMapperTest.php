<?php

require_once TESTS_PATH . '/application/TestCase.php';

class PositionDataMapperTest extends TestCase {

    private $objectManager;
    private $dataMapper;
    private $nodeId;

    public function setUp() {
        $this->objectManager = new Application_Model_ObjectsManager(1, 'Application_Model_Position');
        $this->dataMapper = new Application_Model_DataMapper();
        $this->dataMapper->dbLink->delete('item');
        $this->dataMapper->dbLink->delete('form');
        $this->dataMapper->dbLink->delete('element');
        $this->dataMapper->dbLink->delete('scenario_entry');
        $this->dataMapper->dbLink->delete('scenario');
        $this->dataMapper->dbLink->delete('user_group');
        $this->dataMapper->dbLink->delete('privilege');
        $this->dataMapper->dbLink->delete('user');
        $this->dataMapper->dbLink->delete('position');
        $this->dataMapper->dbLink->delete('node');
        $this->dataMapper->dbLink->delete('contragent');
        $this->dataMapper->dbLink->delete('template'); 
        $nodeArray = array('nodeName' => 'First node', 'parentNodeId' => -1, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $this->nodeId = $this->objectManager->saveObject($node);
        $this->assertTrue(is_int($this->nodeId));
        $session = new Zend_Session_Namespace('Auth');
        $session->domainId = 1;
    }

    public function tearDown() {
        $this->dataMapper->dbLink->delete('item');
        $this->dataMapper->dbLink->delete('form');
        $this->dataMapper->dbLink->delete('element');
        $this->dataMapper->dbLink->delete('scenario_entry');
        $this->dataMapper->dbLink->delete('scenario');
        $this->dataMapper->dbLink->delete('user_group');
        $this->dataMapper->dbLink->delete('privilege');
        $this->dataMapper->dbLink->delete('user');
        $this->dataMapper->dbLink->delete('position');
        $this->dataMapper->dbLink->delete('node');
        $this->dataMapper->dbLink->delete('contragent');
        $this->dataMapper->dbLink->delete('template'); 
        }

    public function testPositionSaveNew() {
        $positionArray = array('positionName' => 'oName', 'nodeId' => $this->nodeId, 'active' => 0, 'domainId' => 1);
        $position = new Application_Model_Position($positionArray);
        $id = $this->objectManager->saveObject($position);
        $this->assertTrue(is_int($id));
        $position->positionId = $id;
        $position2 = $this->objectManager->getObject('position', $id);
        $this->assertTrue($position2 instanceof Application_Model_Position);
        $positionArray2 = $position2->toArray();
        $positionArray1 = $position->toArray();
        $this->assertEquals($positionArray1, $positionArray2);
    }

    /**
     * @expectedException SaveObjectException
     */
    public function testPositionSaveNonValid() {
        $positionArray = array('positionName' => 'lName', 'active' => false);
        $position = new Application_Model_Position($positionArray);
        $id = $this->objectManager->saveObject($position);
    }

    public function testPositionSaveExisting() {
        $positionArray = array('positionName' => 'oName', 'nodeId' => $this->nodeId, 'active' => false, 'domainId' => 1);
        $position = new Application_Model_Position($positionArray);
        $id = $this->objectManager->saveObject($position);
        $position->positionId = $id;
        $this->assertTrue(is_int($id));
        $positionArray1 = array('positionName' => 'oName1', 'nodeId' => $this->nodeId, 'active' => false, 'domainId' => 1);
        $position1 = new Application_Model_Position($positionArray1);
        $id1 = $this->objectManager->saveObject($position1);
        $this->assertTrue(is_int($id1));
        $position2 = $this->objectManager->getObject('position', $id1);
        $position2->active = true;
        $position2->positionName = 'position';
        $id2 = $this->objectManager->saveObject($position2);
        $this->assertEquals($id1, $id2);
        $position_original = $this->objectManager->getObject('position',$id);
        $this->assertEquals($position, $position_original);
        $positionArray3 = $position2->toArray();
        $position3 = $this->objectManager->getObject('position',$id2);
        $positionArray4 = $position3->toArray();
        $this->assertEquals($positionArray3, $positionArray4);
        $positions = $this->objectManager->getAllObjects('position');
        $this->assertEquals($positions, array(0=>$position, 1=>$position3));
    }

    public function testPositionCheckExistance() {
        $positionArray = array('positionName' => 'oName', 'nodeId' => $this->nodeId, 'active' => false, 'domainId' => 1);
        $position = new Application_Model_Position($positionArray);
        $id = $this->objectManager->saveObject($position);
        $this->assertTrue(is_int($id));
//        $this->assertTrue(is_int($this->dataMapper->checkObjectExistance($position)));
//        $this->assertEquals($id, $this->dataMapper->checkObjectExistance($position));
    }

    public function testPositionGet() {
        $positionArray = array('positionName' => 'oName', 'nodeId' => $this->nodeId, 'active' => false, 'domainId' => 1);
        $position = new Application_Model_Position($positionArray);
        $id = $this->objectManager->saveObject($position);
        $this->assertTrue(is_int($id));
        $position->positionId = $id;
        $position2 = $this->objectManager->getObject('position', $id);
        $this->assertEquals($id, $position2->positionId);
        $positionArray2 = $position2->toArray();
        $positionArray3 = $position->toArray();
        $this->assertEquals($positionArray3, $positionArray2);
    }

    
/**
 * @expectedException Exception
 */    
    public function testNonexistingPositionGet() {
        $this->assertFalse($this->objectManager->getObject('position', -1));
    }

    public function testGetAllPositions() {
        $positionArray = array('positionName' => 'oName', 'nodeId' => $this->nodeId, 'active' => 0, 'domainId' => 1);
        $position = new Application_Model_Position($positionArray);
        $id = $this->objectManager->saveObject($position);
        $position->positionId = $id;
        $positionArray1 = array('positionName' => 'oName1', 'nodeId' => $this->nodeId, 'active' => 0, 'domainId' => 1);
        $position1 = new Application_Model_Position($positionArray1);
        $id1 = $this->objectManager->saveObject($position1);
        $position1->positionId = $id1;
        $positionArray2 = array('positionName' => 'oName2', 'nodeId' => $this->nodeId, 'active' => 0, 'domainId' => 1);
        $position2 = new Application_Model_Position($positionArray2);
        $id2 = $this->objectManager->saveObject($position2);
        $position2->positionId = $id2;
        $positions = $this->objectManager->getAllObjects('position');
        $this->assertEquals($positions, array(0 => $position, 1 =>$position1, 2=>$position2));
    }

}

?>
