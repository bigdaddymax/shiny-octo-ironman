<?php

require_once TESTS_PATH . '/application/TestCase.php';

class NodeDataMapperTest extends TestCase {

    private $objectManager;
    private $dataMapper;

    public function setUp() {
        $nodeArray = array('nodeName' => 'lName', 'parentNodeId' => 3);
        $node = new Application_Model_Node($nodeArray);
        $this->objectManager = new Application_Model_ObjectsManager(1);
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
        $id = $this->objectManager->saveObject($node);
        $this->assertTrue(is_int($id));
    }

    /**
     * @expectedException SaveObjectException
     */
    public function testNodeSaveNonValid() {
        $nodeArray = array('nodeName' => 'lName', 'active' => false);
        $node = new Application_Model_Node($nodeArray);
        $id = $this->objectManager->saveObject($node);
    }

    public function testNodeSaveExisting() {
        $nodeArray = array('nodeName' => 'lName', 'parentNodeId' => 8, 'active' => false, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $id = $this->objectManager->saveObject($node);
        $this->assertTrue(is_int($id));
        $nodeArray1 = array('nodeName' => 'lName1', 'parentNodeId' => 0, 'active' => false, 'domainId' => 1);
        $node1 = new Application_Model_Node($nodeArray1);
        $id1 = $this->objectManager->saveObject($node1);
        $nodeArray2 = array('nodeName' => 'lName2', 'parentNodeId' => 2, 'active' => false, 'domainId' => 1);
        $node2 = new Application_Model_Node($nodeArray2);
        $id2 = $this->objectManager->saveObject($node2);
        $node3 = $this->objectManager->getObject('node', $id2);
        $node3->active = true;
        $id3 = $this->objectManager->saveObject($node3);
        $this->assertEquals($id3, $id2);
        $node4 = $this->objectManager->getObject('node', $id2);
        $this->assertTrue($node4 instanceof Application_Model_Node);
        $this->assertEquals($node4->toArray(),$node3->toArray());
    }

    public function testNodeCheckExistance() {
        $nodeArray = array('nodeName' => 'lName', 'parentNodeId' => 4, 'active' => false, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $id = $this->objectManager->saveObject($node);
        $nodeArray1 = array('nodeName' => 'lName1', 'parentNodeId' => 0, 'active' => false, 'domainId' => 1);
        $node1 = new Application_Model_Node($nodeArray1);
        $id1 = $this->objectManager->saveObject($node1);
        $nodeArray2 = array('nodeName' => 'lName2', 'parentNodeId' => 2, 'active' => false, 'domainId' => 1);
        $node2 = new Application_Model_Node($nodeArray2);
        $id2 = $this->objectManager->saveObject($node2);
        $this->assertTrue(is_int($id2));
    }

    public function testNodeGet() {
        $nodeArray = array('nodeName' => 'lName', 'parentNodeId' => 4, 'active' => 0, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $id = $this->objectManager->saveObject($node);
        $this->assertTrue(is_int($id));
        $node->nodeId = $id;
        $node2 = $this->objectManager->getObject('node', $id);
        $this->assertEquals($id, $node2->nodeId);
        $nodeArray2 = $node2->toArray();
        $nodeArray3 = $node->toArray();
        $this->assertEquals($nodeArray3, $nodeArray2);
    }

    public function testGetAllNodes() {
        // Create node and save node
        $nodeArray = array('nodeName' => 'lName1', 'parentNodeId' => 0, 'active' => 0, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $id = $this->objectManager->saveObject($node);
        $node->nodeId = $id;
        
        // Create and save another node
        $nodeArray1 = array('nodeName' => 'lName2', 'parentNodeId' => 1, 'active' => 0, 'domainId' => 1);
        $node1 = new Application_Model_Node($nodeArray1);
        $id = $this->objectManager->saveObject($node1);
        $node1->nodeId = $id;
        
        // Get all nodes from DB
        $nodes = $this->objectManager->getAllObjects();
        $this->assertEquals($nodes, array(0 => $node, 1 => $node1));
        
        // Trying to get all nodes using another constructor of ObjectsManager
        $emptyObject = new Application_Model_ObjectsManager(1);
        $nodes2 = $emptyObject->getAllObjects('Node');
        $this->assertEquals($nodes, $nodes2);
    }

/**
 * @expectedException Exception
 */    
    public function testNonexistingNodeGet() {
       $this->assertFalse($this->objectManager->getObject('node', -1));
    }

    
    public function testCheckNodeDependent(){
        $nodeArray = array('nodeName' => 'lName1', 'parentNodeId' => 3, 'active' => false, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $id = $this->objectManager->saveObject($node);
        $nodeArray1 = array('nodeName' => 'lName2', 'parentNodeId' => $id, 'active' => false, 'domainId' => 1);
        $node1 = new Application_Model_Node($nodeArray1);
        $id1 = $this->objectManager->saveObject($node1);
//        $this->assertEquals($this->objectManager->objectParentIdName, 'parentNodeId');
//        $this->assertEquals($this->objectManager->className, 'Application_Model_Node');
        $objectManager = new Application_Model_ObjectsManager(1);
        $node2 = $objectManager->getObject('Node', $id);
        $this->assertEquals($node2->nodeId, $node1->parentNodeId);
    }
    
    
    public function testDeleteIndependentNode() {
        // Create and save node
        $nodeArray = array('nodeName' => 'lName1', 'parentNodeId' => 3, 'active' => false, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $id = $this->objectManager->saveObject($node);
        $node->nodeId = $id;
        // Create and save another node
        $nodeArray1 = array('nodeName' => 'lName2', 'parentNodeId' => 3, 'active' => false, 'domainId' => 1);
        $node1 = new Application_Model_Node($nodeArray1);
        $id1 = $this->objectManager->saveObject($node1);
        $node1->nodeId = $id1;
        // Retrive all nodes from DB
        $nodes = $this->objectManager->getAllObjects();
        $this->assertEquals($nodes, array(0=>$node, 1=>$node1));
        
        // Try to delete independant node (no nodes has this node as parent
        $this->objectManager->deleteObject('node',$id);
        
        // Retrive all nodes from DB, expect one node less
        $nodes1 = $this->objectManager->getAllObjects('node');
        $this->assertEquals($nodes1, array(0=>$node1));
        $this->objectManager->deleteObject('Node', $id1);
        $nodes2 = $this->objectManager->getAllObjects('Node');
        $this->assertTrue(empty($nodes2));
    }

    /**
     * 
     * @expectedException DependantObjectDeletionAttempt
     */
    public function testDeleteDependentNode() {
        
        // Create and save dependent nodes 
        $nodeArray = array('nodeName' => 'lName1', 'parentNodeId' => -1, 'active' => false, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $id = $this->objectManager->saveObject($node);
        $node->nodeId = $id;
        
        $nodeArray1 = array('nodeName' => 'lName2', 'parentNodeId' => $id, 'active' => false, 'domainId' => 1);
        $node1 = new Application_Model_Node($nodeArray1);
        $id1 = $this->objectManager->saveObject($node1);
        $node1->nodeId = $id1;
        
        // Get all nodes from DB
        $nodes = $this->objectManager->getAllObjects();
        $this->assertEquals($nodes, array(0=>$node, 1=>$node1));
        
        // Try to delete node when other node is dependent on it
        $this->objectManager->deleteObject('node', $id);
        
        // Get nodes from DB, nodes should be untouched
        $nodes1 = $this->objectManager->getAllObjects('node');
        $this->assertEquals(2, count($nodes1));
        $this->assertEquals(array(0=>$node, 1=>$node1), $nodes1 );
    }

}

?>
