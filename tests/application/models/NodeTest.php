<?php

require_once APPLICATION_PATH . '/models/Node.php';
require_once TESTS_PATH . '/application/TestCase.php';


class NodeTest extends TestCase {

    public function setUp() {
        
    }

    public function tearDown() {
        
    }

    public function testNodeConstructor() {
        $nodeArray = array('nodeName' => 'lName', 'nodeId' => 2, 'parentNodeId' => 4, 'active' => true);
        $node = new Application_Model_Node($nodeArray);
        $this->assertTrue($node instanceof Application_Model_Node);
    }


    
    public function testNodeValidatior()
    {
        $nodeArray = array();
        $node = new Application_Model_Node($nodeArray);
        $this->assertFalse($node->isValid());
        $nodeArray1 = array('nodeName'=> 'lName', 'parentNodeId' => 0, 'nodeId' => 2, 'domainId' => 4);
        $node1 = new Application_Model_Node($nodeArray1);
        $this->assertTrue($node1->isValid());
    }
    
    public function testNodeToArray()
    {
        $nodeArray = array('nodeName'=> 'lName', 'parentNodeId' => 0, 'nodeId' => 2, 'active'=>0, 'domainId' => 2);
        $node = new Application_Model_Node($nodeArray);
        $nodeArray1 = $node->toArray();
        $this->assertEquals($nodeArray, $nodeArray1);
    }
}
