<?php

/**
 * Description of ContragentTest
 *
 * @author Max
 */
require_once TESTS_PATH . '/application/TestCase.php';

class ContragentTest extends TestCase {


    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testContragentConstructException() {
        $contragent = new Application_Model_Contragent(1);
    }

    public function testContragentConstructCorrect() {
        $contragentArray = array('contragentName' => 'eName', 'contragentId' => 3, 'active' => 0, 'domainId' => 5);
        $contragent = new Application_Model_Contragent($contragentArray);
        $this->assertTrue($contragent->isValid());
        $contragentArray1 = $contragent->toArray();
        unset($contragentArray['date']);
        unset($contragentArray1['date']);
        $this->assertEquals($contragentArray, $contragentArray1);
    }

    public function testContragentValidation() {
        $contragentArray = array('contragentName' => 'pName');
        $contragent = new Application_Model_Contragent($contragentArray);
        $this->assertFalse($contragent->isValid());
        $contragent->contragentName = 'eName';
        $this->assertFalse($contragent->isValid());
        $contragent->domainId = 3;
        $this->assertTrue($contragent->isValid());
        $contragent->contragentId = 1;
        $this->assertTrue($contragent->isValid());
        $this->assertEquals('eName', $contragent->contragentName);
        $this->assertEquals(1, $contragent->contragentId);
        $this->assertTrue($contragent->isValid());
    }



    
    public function testContragentToArray() {
        $contragentArray = array('contragentName' => 'eName', 'contragentId' => 3,'active' => 0, 'domainId' => 5);
        $contragent = new Application_Model_Contragent($contragentArray);
        $contragentArray2 = $contragent->toArray();
         $this->assertEquals($contragentArray, $contragentArray2);
    }
    
    public function testSaveContragent(){
        $objectManager = new Application_Model_ObjectsManager(1);
        $contragentArray = array('contragentName' => 'eName', 'contragentId' => 3,'active' => 0, 'domainId' => 1);
        $contragent = new Application_Model_Contragent($contragentArray);
        $this->assertTrue($contragent instanceof Application_Model_Contragent);
        $this->assertTrue($contragent->isValid());
        $id = $objectManager->saveObject($contragent);
        $this->assertTrue(is_int($id));
    }
}
