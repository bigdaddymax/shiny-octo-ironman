<?php

require_once TESTS_PATH . '/application/TestCase.php';

class NodeDataMapperTest extends TestCase {

    private $objectManager;

    public function setUp() {
        $nodeArray = array('nodeName' => 'lName', 'parentNodeId' => 3);
        $node = new Application_Model_Node($nodeArray);
        $this->objectManager = new Application_Model_ObjectsManager(1, $node);
        $this->objectManager->dbLink->delete('item');
        $this->objectManager->dbLink->delete('form');
        $this->objectManager->dbLink->delete('element');
        $this->objectManager->dbLink->delete('scenario_entry');
        $this->objectManager->dbLink->delete('scenario');
        $this->objectManager->dbLink->delete('user_group');
        $this->objectManager->dbLink->delete('privilege');
        $this->objectManager->dbLink->delete('user');
        $this->objectManager->dbLink->delete('position');
        $this->objectManager->dbLink->delete('node');
        $session = new Zend_Session_Namespace('Auth');
        $session->domainId = 1;
    }

    public function tearDown() {
        $this->objectManager->dbLink->delete('item');
        $this->objectManager->dbLink->delete('form');
        $this->objectManager->dbLink->delete('element');
        $this->objectManager->dbLink->delete('scenario_entry');
        $this->objectManager->dbLink->delete('scenario');
        $this->objectManager->dbLink->delete('user_group');
        $this->objectManager->dbLink->delete('privilege');
        $this->objectManager->dbLink->delete('user');
        $this->objectManager->dbLink->delete('position');
        $this->objectManager->dbLink->delete('node');
    }

    public function testNodeSaveNew() {
        $nodeArray = array('nodeName' => 'lName', 'parentNodeId' => 8, 'active' => false, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $id = $this->objectManager->saveObject($node);
        $this->assertTrue(is_int($id));
    }

    /**
     * @expectedException InvalidArgumentException
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
        $this->assertTrue(is_int($this->objectManager->checkObjectExistance($node2)));
    }

    public function testNodeGet() {
        $nodeArray = array('nodeName' => 'lName', 'parentNodeId' => 4, 'active' => false, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $id = $this->objectManager->saveObject($node);
        $this->assertTrue(is_int($id));
        $node2 = $this->objectManager->getObject('node', $id);
        $this->assertEquals($id, $node2->nodeId);
        $nodeArray2 = $node2->toArray();
        $nodeArray3 = $node->toArray();
        $this->assertEquals($nodeArray3, $nodeArray2);
    }

    public function testGetAllNodes() {
        $nodeArray = array('nodeName' => 'lName1', 'parentNodeId' => 0, 'active' => false, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $id = $this->objectManager->saveObject($node);
        $nodeArray1 = array('nodeName' => 'lName2', 'parentNodeId' => 1, 'active' => false, 'domainId' => 1);
        $node1 = new Application_Model_Node($nodeArray1);
        $id = $this->objectManager->saveObject($node1);
        $nodes = $this->objectManager->getAllObjects();
        $this->assertEquals($nodes, array(0 => $node, 1 => $node1));
        $emptyObject = new Application_Model_ObjectsManager(1);
        $nodes2 = $emptyObject->getAllObjects('Node');
        $this->assertEquals($nodes, $nodes2);
        $this->assertEquals($nodes[0]->nodeName, 'lName1');
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
        $this->assertTrue(is_array($objectManager->checkObjectDependencies('Node',$id)));
        $this->assertFalse($this->objectManager->checkObjectDependencies('node',$id1));
    }
    
    
    public function testDeleteIndependentNode() {
        $nodeArray = array('nodeName' => 'lName1', 'parentNodeId' => 3, 'active' => false, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $id = $this->objectManager->saveObject($node);
        $nodeArray1 = array('nodeName' => 'lName2', 'parentNodeId' => 3, 'active' => false, 'domainId' => 1);
        $node1 = new Application_Model_Node($nodeArray1);
        $id1 = $this->objectManager->saveObject($node1);
        $nodes = $this->objectManager->getAllObjects();
        $this->assertEquals($nodes, array(0=>$node, 1=>$node1));
        $this->objectManager->deleteObject('node',$id);
        $nodes1 = $this->objectManager->getAllObjects('node');
        $this->assertEquals($nodes1, array(0=>$node1));
        $objectManager = new Application_Model_ObjectsManager(1);
        $objectManager->deleteObject('Node', $id1);
        $nodes2 = $objectManager->getAllObjects('Node');
        $this->assertTrue(empty($nodes2));
    }

    /**
     * 
     * @expectedException Exception
     * @expectedExceptionMessage Other objects has "Node" as parent
     */
    public function testDeleteDependentNode() {
        $nodeArray = array('nodeName' => 'lName1', 'parentNodeId' => -1, 'active' => false, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $id = $this->objectManager->saveObject($node);
        $nodeArray1 = array('nodeName' => 'lName2', 'parentNodeId' => $id, 'active' => false, 'domainId' => 1);
        $node1 = new Application_Model_Node($nodeArray1);
        $id1 = $this->objectManager->saveObject($node1);
        $nodes = $this->objectManager->getAllObjects();
        $this->assertEquals($nodes, array(0=>$node, 1=>$node1));
        $this->objectManager->deleteObject('node', $id);
        $nodes1 = $this->objectManager->getAllObjects('node');
        $this->assertEquals(array(0=>$node, 1=>$node1), $nodes1 );
    }

}

?>
