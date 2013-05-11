<?php

/**
 * Description of ElementDataMapperTest
 *
 * @author Max
 */

require_once TESTS_PATH . '/application/TestCase.php';

class ElementDataMapperTest extends TestCase {

    private $object;

    public function setUp() {
        $this->object = new Application_Model_ObjectsManager(1, 'Application_Model_Element');
    }

    public function tearDown() {
 //       $this->object->dbLink->delete('elements');
    }

    public function testConstructor()
    {
 
        }

/**
 * Testing function that examine existance of element with id = $element['elementId']
 */    

    public function testElementCheckExistance()
    {
        $this->object->dbLink->insert('element', array('elementName'=>'cName', 'elementCode'=>12, 'active'=>true, 'domainId'=>1));
        $id = $this->object->dbLink->lastInsertId();
        $this->assertTrue(is_int($this->object->checkObjectExistance((int)$id)));
        $this->assertFalse($this->object->checkObjectExistance(-1));
    }
    
    
    public function testElementSave()
    {
        $elementArray = array('elementName' => 'eName', 'elementCode' => 55, 'active'=>true, 'elementComment'=>'test', 'domainId' =>1);
        $element = new Application_Model_Element($elementArray);
        $element->elementId = $this->object->saveObject($element);
        $this->assertTrue(is_int($element->elementId));
        $element->elementName = 'tName';
        $element->active = false;
        $id = $this->object->saveObject($element);
        $this->assertEquals($id, $element->elementId);
    }   
    
    
    
    public function testElementGet()
    {
        $elementArray = array('elementName' => 'eName', 'elementCode' => '55', 'active'=>true, 'elementComment'=>'test', 'domainId' => 1);
        $element = new Application_Model_Element($elementArray);
        $id = $this->object->saveObject($element);
        $this->assertTrue(is_int($id));
        $element2 = $this->object->getObject('element', $id);
        $this->assertEquals($id, $element2->elementId);
        $elementArray['elementId'] = $id;
        $elementArray2 = $element2->toArray();
        $this->assertEquals($elementArray, $elementArray2);
    }
    
    
/**
 *  @expectedException InvalidArgumentException
 */    
    public function testValidateElement()
    {
        $elementArray = array('elementCode' => '55', 'elementId'=>2, 'active'=>true, 'elementComment'=>'test');
        $element = new Application_Model_Element($elementArray);
        $this->assertFalse($element->isValid());
        $id = $this->object->saveObject($element);
    }
}
