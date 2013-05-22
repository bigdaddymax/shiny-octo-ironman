<?php

/**
 * Description of ElementTest
 *
 * @author Max
 */

require_once TESTS_PATH . '/application/TestCase.php';
require_once APPLICATION_PATH . '/models/Element.php';

class ElementTest extends TestCase {
    
    
    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testElementConstructException()
    {
        $element = new Application_Model_Element(1);
    }
    
    public function testElementConstructCorrect()
    {
        $elementArray = array('elementName'=>'eName', 'elementCode'=>12, 'elementId'=>3, 'elementComment'=>'test', 'active'=>false, 'domainId' => 5);
        $element = new Application_Model_Element($elementArray);
        $this->assertFalse($element->active);
        $this->assertEquals('eName', $element->elementName);
        $this->assertEquals('test', $element->elementComment);
        $this->assertEquals(12, $element->elementCode);
        $this->assertEquals(3, $element->elementId);
    }


    public function testElementValidation()
    {
        $elementArray = array('ele'=>33);
        $element = new Application_Model_Element($elementArray);
        $this->assertFalse($element->isValid());
        $element->elementName = 'eName';
        $this->assertFalse($element->isValid());
        $element->elementCode = 11;
        $this->assertFalse($element->isValid());
        $element->domainId = 3;
        $this->assertTrue($element->isValid());
        $element->elementComment = 'test';
        $this->assertTrue($element->isValid());
        $element->elementId = 1;
        $this->assertTrue($element->isValid());
        $this->assertEquals('eName', $element->elementName);
        $this->assertEquals(11, $element->elementCode);
        $this->assertEquals('test', $element->elementComment);
    }
    
    public function testElementToArray()
    {
        $elementArray = array('elementName'=>'eName', 'elementCode'=>12, 'elementId'=>3, 'elementComment'=>'test', 'active'=>false, 'domainId' =>5);
        $element = new Application_Model_Element($elementArray);
        $elementArray2 = $element->toArray();
        $this->assertEquals($elementArray, $elementArray2);
    }
}
