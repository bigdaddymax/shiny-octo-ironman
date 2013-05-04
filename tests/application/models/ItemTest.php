<?php

/**
 * Description of ItemTest
 *
 * @author Max
 */
require_once TESTS_PATH . '/application/TestCase.php';

class ItemTest extends TestCase {

    public function testItemGetterSetter() {
        $item = new Application_Model_Item();
        $item->itemName = 'eName';
        $this->expectOutputString('Cant set value. Property itemStatus doesnt exist');
        $item->itemStatus = 'status';
        ob_clean();
//        $item->state = 'state';
//        $item->valid1 =4;
        $test = $item->itemState;
        $this->assertEquals('Cannot get value. Property itemState doesnt exist', $test);
        $this->expectOutputString('Cannot set value for "valid" property');
        $item->valid = 1;
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testItemConstructException() {
        $item = new Application_Model_Item(1);
    }

    public function testItemConstructCorrect() {
        $itemArray = array('itemName' => 'eName', 'itemId' => 3, 'formId' => 4, 'elementId' => 5, 'active' => false, 'domainId' => 5, 'value' => 100);
        $item = new Application_Model_Item($itemArray);
        $this->assertFalse($item->active);
        $this->assertEquals('eName', $item->itemName);
        $this->assertEquals(5, $item->elementId);
        $this->assertEquals(3, $item->itemId);
        $this->assertEquals(5, $item->domainId);
        $this->assertEquals(4, $item->formId);
    }

    public function testItemValidation() {
        $itemArray = array('itemName' => 'pName');
        $item = new Application_Model_Item($itemArray);
        $this->assertFalse($item->isValid());
        $item->itemName = 'eName';
        $this->assertFalse($item->isValid());
        $item->formId = 11;
        $this->assertFalse($item->isValid());
        $item->domainId = 3;
        $this->assertFalse($item->isValid());
        $item->itemId = 1;
        $this->assertFalse($item->isValid());
        $item->value = 100;
        $this->assertFalse($item->isValid());
        $item->elementId = 2;
        $this->assertTrue($item->isValid());
        $this->assertEquals('eName', $item->itemName);
        $this->assertEquals(11, $item->formId);
        $this->assertEquals(100, $item->value);
    }

    public function testItemToArray() {
        $itemArray = array('itemName' => 'eName', 'formId' => 12, 'itemId' => 3, 'elementId' => 4, 'active' => false, 'domainId' => 5, 'value' => 100.5);
        $item = new Application_Model_Item($itemArray);
        $itemArray2 = $item->toArray();
        $this->assertEquals($itemArray, $itemArray2);
    }

}
