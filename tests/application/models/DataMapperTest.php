<?php

require_once TESTS_PATH . '/application/TestCase.php';

class DataMapperTest extends TestCase {

    private $dataMapper;
    private $positionId;

    public function setUp() {
        $this->dataMapper = new Application_Model_DataMapper();
        $this->dataMapper->dbLink->delete('item');
        $this->dataMapper->dbLink->delete('form');
        $this->dataMapper->dbLink->delete('scenario_entry');
        $this->dataMapper->dbLink->delete('scenario');
        $this->dataMapper->dbLink->delete('privilege');
        $this->dataMapper->dbLink->delete('user');
        $this->dataMapper->dbLink->delete('contragent');
        $this->dataMapper->dbLink->delete('position');
        $this->dataMapper->dbLink->delete('node');
    }

    public function tearDown() {
        $this->dataMapper->dbLink->delete('item');
        $this->dataMapper->dbLink->delete('form');
        $this->dataMapper->dbLink->delete('element');
        $this->dataMapper->dbLink->delete('scenario_entry');
        $this->dataMapper->dbLink->delete('scenario');
        $this->dataMapper->dbLink->delete('privilege');
        $this->dataMapper->dbLink->delete('user');
        $this->dataMapper->dbLink->delete('contragent');
        $this->dataMapper->dbLink->delete('position');
        $this->dataMapper->dbLink->delete('node');
        parent::tearDown();
    }

    public function testSaveData() {
        $nodeArray = array('nodeName' => 'testNode', 'parentNodeId' => -1, 'active' => 1, 'domainId' => 1);
        $nodeId = $this->dataMapper->saveData('node', $nodeArray);
        $this->assertTrue(is_int($nodeId));
    }

    public function testGetData() {
        $nodeArray = array('nodeName' => 'testNode', 'parentNodeId' => -1, 'active' => 1, 'domainId' => 1);
        $nodeId = $this->dataMapper->saveData('node', $nodeArray);
        $nodeArray1 = $this->dataMapper->getData('node', array(0 => array('column' => 'domainId', 'operand' => 1)));
        $nodeArray['nodeId'] = $nodeId;
        $this->assertEquals($nodeArray, $nodeArray1[0]);
    }

    public function testGetFilteredData() {
        $nodeArray = array('nodeName' => 'testNode', 'parentNodeId' => -1, 'active' => 1, 'domainId' => 1);
        $nodeId = $this->dataMapper->saveData('node', $nodeArray);
        $nodeArray1 = $this->dataMapper->getData('node', array(0 => array('column' => 'domainId', 'operand' => 1), 1=>array('column'=>'nodeName', 'operand'=>'testNode')));
        $nodeArray['nodeId'] = $nodeId;
        $this->assertEquals($nodeArray, $nodeArray1[0]);
        
    }
    
    

    public function testGetNonExistingData() {
        $node = $this->dataMapper->getData('node', array(0=>array('column'=>'domainId', 'operand'=>-1)));
        $this->assertFalse($node instanceof Application_Model_Node);
        $this->assertTrue(is_array($node));
        $this->assertTrue(empty($node));
    }
}