<?php

/**
 * Description of PositionTest
 *
 * @author Max
 */

require_once TESTS_PATH . '/application/TestCase.php';

class PositionTest extends TestCase {
    
    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testPositionConstructException()
    {
        $position = new Application_Model_Position(1);
    }
    
    public function testPositionConstructCorrect()
    {
        $positionArray = array('positionName'=>'eName', 'positionId'=>3, 'nodeId'=>5, 'active'=>false, 'domainId' => 5);
        $position = new Application_Model_Position($positionArray);
        $this->assertFalse($position->active);
        $this->assertEquals('eName', $position->positionName);
        $this->assertEquals(5, $position->nodeId);
        $this->assertEquals(3, $position->positionId);
    }


    public function testPositionValidation()
    {
        $positionArray = array('positionName'=>'pName');
        $position = new Application_Model_Position($positionArray);
        $this->assertFalse($position->isValid());
        $position->positionName = 'eName';
        $this->assertFalse($position->isValid());
        $position->domainId = 3;
        $this->assertFalse($position->isValid());
        $position->nodeId = 4;
        $this->assertTrue($position->isValid());
        $position->positionId = 1;
        $this->assertTrue($position->isValid());
        $this->assertEquals('eName', $position->positionName);
        $this->assertEquals(4, $position->nodeId);
    }
    
    public function testPositionToArray()
    {
        $positionArray = array('positionName'=>'eName', 'positionId'=>3, 'nodeId'=>4, 'active'=>false, 'domainId' =>5);
        $position = new Application_Model_Position($positionArray);
        $positionArray2 = $position->toArray();
        $this->assertEquals($positionArray, $positionArray2);
    }
}
