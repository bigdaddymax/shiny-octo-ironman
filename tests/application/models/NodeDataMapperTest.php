<?php

require_once TESTS_PATH . '/application/TestCase.php';

class NodeDataMapperTest extends TestCase {

    private $dataMapper;

    public function setUp() {
        $nodeArray = array('nodeName' => 'lName', 'parentNodeId' => 3);
        $node = new Application_Model_Node($nodeArray);
        $this->dataMapper = new Application_Model_DataMapper($node);
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
    }

    public function testNodeSaveNew() {
        $nodeArray = array('nodeName' => 'lName', 'parentNodeId' => 8, 'active' => false, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $id = $this->dataMapper->saveObject($node);
        $this->assertTrue(is_int($id));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testNodeSaveNonValid() {
        $nodeArray = array('nodeName' => 'lName', 'active' => false);
        $node = new Application_Model_Node($nodeArray);
        $id = $this->dataMapper->saveObject($node);
    }

    public function testNodeSaveExisting() {
        $nodeArray = array('nodeName' => 'lName', 'parentNodeId' => 8, 'active' => false, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $id = $this->dataMapper->saveObject($node);
        $this->assertTrue(is_int($id));
        $nodeArray1 = array('nodeName' => 'lName1', 'parentNodeId' => 0, 'active' => false, 'domainId' => 1);
        $node1 = new Application_Model_Node($nodeArray1);
        $id1 = $this->dataMapper->saveObject($node1);
        $nodeArray2 = array('nodeName' => 'lName2', 'parentNodeId' => 2, 'active' => false, 'domainId' => 1);
        $node2 = new Application_Model_Node($nodeArray2);
        $id2 = $this->dataMapper->saveObject($node2);
        $node3 = $this->dataMapper->getObject($id2);
        $node3->active = true;
        $id3 = $this->dataMapper->saveObject($node3);
        $this->assertEquals($id3, $id2);
        $node4 = $this->dataMapper->getObject($id2);
        $this->assertEquals($node3, $node4);
    }

    public function testNodeCheckExistance() {
        $nodeArray = array('nodeName' => 'lName', 'parentNodeId' => 4, 'active' => false, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $id = $this->dataMapper->saveObject($node);
        $nodeArray1 = array('nodeName' => 'lName1', 'parentNodeId' => 0, 'active' => false, 'domainId' => 1);
        $node1 = new Application_Model_Node($nodeArray1);
        $id1 = $this->dataMapper->saveObject($node1);
        $nodeArray2 = array('nodeName' => 'lName2', 'parentNodeId' => 2, 'active' => false, 'domainId' => 1);
        $node2 = new Application_Model_Node($nodeArray2);
        $id2 = $this->dataMapper->saveObject($node2);
        $this->assertTrue(is_int($id2));
        $this->assertTrue(is_int($this->dataMapper->checkObjectExistance($node2)));
    }

    public function testNodeGet() {
        $nodeArray = array('nodeName' => 'lName', 'parentNodeId' => 4, 'active' => false, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $id = $this->dataMapper->saveObject($node);
        $this->assertTrue(is_int($id));
        $node2 = $this->dataMapper->getObject($id);
        $this->assertEquals($id, $node2->nodeId);
        $nodeArray2 = $node2->toArray();
        $nodeArray3 = $node->toArray();
        $this->assertEquals($nodeArray3, $nodeArray2);
    }

    public function testGetAllNodes() {
        $nodeArray = array('nodeName' => 'lName1', 'parentNodeId' => 0, 'active' => false, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $id = $this->dataMapper->saveObject($node);
        $nodeArray1 = array('nodeName' => 'lName2', 'parentNodeId' => 1, 'active' => false, 'domainId' => 1);
        $node1 = new Application_Model_Node($nodeArray1);
        $id = $this->dataMapper->saveObject($node1);
        $nodes = $this->dataMapper->getAllObjects();
        $this->assertEquals($nodes, array(0 => $node, 1 => $node1));
        $emptyObject = new Application_Model_DataMapper();
        $nodes2 = $emptyObject->getAllObjects('Application_Model_Node');
        $this->assertEquals($nodes, $nodes2);
        $this->assertEquals($nodes[0]->nodeName, 'lName1');
    }

    public function testNonexistingNodeGet() {
       $this->assertFalse($this->dataMapper->getObject(-1));
    }

    
    public function testCheckNodeDependent(){
        $nodeArray = array('nodeName' => 'lName1', 'parentNodeId' => 3, 'active' => false, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $id = $this->dataMapper->saveObject($node);
        $nodeArray1 = array('nodeName' => 'lName2', 'parentNodeId' => $id, 'active' => false, 'domainId' => 1);
        $node1 = new Application_Model_Node($nodeArray1);
        $id1 = $this->dataMapper->saveObject($node1);
        $this->assertEquals($this->dataMapper->objectParentIdName, 'parentNodeId');
        $this->assertEquals($this->dataMapper->className, 'Application_Model_Node');
        $dataMapper = new Application_Model_DataMapper();
        $node2 = $dataMapper->getObject($id, 'Application_Model_Node');
        $this->assertEquals($node2->nodeId, $node1->parentNodeId);
        $this->assertTrue(is_array($dataMapper->checkObjectDependencies($id, 'Application_Model_Node')));
        $this->assertFalse($this->dataMapper->checkObjectDependencies($id1));
    }
    
    
    public function testDeleteIndependentNode() {
        $nodeArray = array('nodeName' => 'lName1', 'parentNodeId' => 3, 'active' => false, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $id = $this->dataMapper->saveObject($node);
        $nodeArray1 = array('nodeName' => 'lName2', 'parentNodeId' => 3, 'active' => false, 'domainId' => 1);
        $node1 = new Application_Model_Node($nodeArray1);
        $id1 = $this->dataMapper->saveObject($node1);
        $nodes = $this->dataMapper->getAllObjects();
        $this->assertEquals($nodes, array(0=>$node, 1=>$node1));
        $this->dataMapper->deleteObject($id);
        $nodes1 = $this->dataMapper->getAllObjects();
        $this->assertEquals($nodes1, array(0=>$node1));
        $dataMapper = new Application_Model_DataMapper();
        $dataMapper->deleteObject($id1, 'Application_Model_Node');
        $nodes2 = $dataMapper->getAllObjects('Application_Model_Node');
        $this->assertTrue(empty($nodes2));
    }

    /**
     * 
     * @expectedException Exception
     * @expectedExceptionMessage Other objects has "node" as parent
     */
    public function testDeleteDependentNode() {
        $nodeArray = array('nodeName' => 'lName1', 'parentNodeId' => -1, 'active' => false, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $id = $this->dataMapper->saveObject($node);
        $nodeArray1 = array('nodeName' => 'lName2', 'parentNodeId' => $id, 'active' => false, 'domainId' => 1);
        $node1 = new Application_Model_Node($nodeArray1);
        $id1 = $this->dataMapper->saveObject($node1);
        $nodes = $this->dataMapper->getAllObjects();
        $this->assertEquals($nodes, array(0=>$node, 1=>$node1));
        $this->dataMapper->deleteObject($id);
        $nodes1 = $this->dataMapper->getAllObjects();
        $this->assertEquals(array(0=>$node, 1=>$node1), $nodes1 );
    }

}

?>
