<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once APPLICATION_PATH . '/models/ObjectsManager.php';
require_once APPLICATION_PATH . '/models/DataMapper.php';
class OM_BasicTest extends TestCase {
    private $dataMapper;
    private $objectsManager;
    
    public function setUp() {
        $this->dataMapper = new Application_Model_DataMapper();
        $this->objectsManager = new Application_Model_ObjectsManager(1);
        parent::setUp();
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
    
    public function tearDown() {
        parent::tearDown();
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
    
    public function testSaveNewObject(){
        $nodeArray = array('nodeName' =>'test1Node', 'active'=>1, 'parentNodeId'=>-1);
        $node = new Application_Model_Node($nodeArray);
        $nodeId = $this->objectsManager->saveObject($node);
        $this->assertTrue(is_int($nodeId));
    }
    
        public function testGetObject(){
        $nodeArray = array('nodeName' =>'test1Node', 'active'=>1, 'parentNodeId'=>-1);
        $node = new Application_Model_Node($nodeArray);
        $nodeId = $this->objectsManager->saveObject($node);
        $node1 = $this->objectsManager->getObject('node', $nodeId);
        $nodeArray1 = $node1->toArray();
        $nodeArray['nodeId'] = $nodeId;
        $nodeArray['domainId'] = 1;
        $this->assertEquals($nodeArray, $nodeArray1);
    }

    public function testCheckObjectExistance() {
        $nodeArray = array('nodeName' =>'test1Node', 'active'=>1, 'parentNodeId'=>-1);
        $node = new Application_Model_Node($nodeArray);
        $nodeId = $this->objectsManager->saveObject($node);
        $this->assertTrue(is_int($this->dataMapper->checkObjectExistance('node', $nodeArray)));
    }
    
    public function testDeleteObject() {
        $nodeArray = array('nodeName' =>'test1Node', 'active'=>1, 'parentNodeId'=>-1);
        $node = new Application_Model_Node($nodeArray);
        $nodeId = $this->objectsManager->saveObject($node);
        $this->assertTrue(is_int($this->dataMapper->checkObjectExistance('node', $nodeArray)));
        $this->objectsManager->deleteObject('node', $nodeId);
        $this->assertEquals(0, $this->dataMapper->checkObjectExistance('node', $nodeArray));
    }
}
?>
