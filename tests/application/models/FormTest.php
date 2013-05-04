<?php

/**
 * Description of FormTest
 *
 * @author Max
 */
require_once TESTS_PATH . '/application/TestCase.php';

class FormTest extends TestCase {

    public function testFormGetterSetter() {
        $form = new Application_Model_Form();
        $form->formName = 'eName';
        $this->expectOutputString('Cant set value. Property formStatus doesnt exist');
        $form->formStatus = 'status';
        ob_clean();
//        $form->state = 'state';
//        $form->valid1 =4;
        $test = $form->formState;
        $this->assertEquals('Cannot get value. Property formState doesnt exist', $test);
        $this->expectOutputString('Cannot set value for "valid" property');
        $form->valid = 1;
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testFormConstructException() {
        $form = new Application_Model_Form(1);
    }

    public function testFormConstructCorrect() {
        $itemArray = array('itemName' => 'item1', 'domainId' => 1, 'value' => 55.4, 'userId' => 6, 'elementId' => 2, 'formId' => 1);
        $item = new Application_Model_Item($itemArray);
        $this->assertTrue($item->isValid());
        $formArray = array('formName' => 'eName', 'formId' => 3, 'userId' => 5, 'active' => false, 'domainId' => 5, 'contragentId'=>2, 'items' => array(0 => $item), 'nodeId' => 3,'public'=>1);
        $form = new Application_Model_Form($formArray);
        $this->assertTrue($form->isValid());
        $formArray1 = $form->toArray();
        unset($formArray['date']);
        unset($formArray1['date']);
        $this->assertEquals($formArray, $formArray1);
    }

    public function testFormValidation() {
        $itemArray = array('itemName' => 'item1', 'domainId' => 1, 'value' => 55.4, 'userId' => 6, 'elementId' => 2, 'formId' => 1);
        $item = new Application_Model_Item($itemArray);
        $itemArray1 = array('itemName' => 'item2', 'domainId' => 1, 'value' => 33.2, 'userId' => 6, 'elementId' => 1, 'formId' => 1);
        $item1 = new Application_Model_Item($itemArray1);
        $this->assertTrue($item->isValid());
        $formArray = array('formName' => 'pName');
        $form = new Application_Model_Form($formArray);
        $this->assertFalse($form->isValid());
        $form->formName = 'eName';
        $this->assertFalse($form->isValid());
        $form->domainId = 3;
        $this->assertFalse($form->isValid());
        $form->userId = 4;
        $this->assertFalse($form->isValid());
        $form->items = array(1 => $item, 2 => $item1);
        $this->assertFalse($form->isValid());
        $form->nodeId = 2;
        $this->assertFalse($form->isValid());
        $form->formId = 1;
        $this->assertFalse($form->isValid());
        $form->contragentId = 3;
        $this->assertTrue($form->isValid());
        $this->assertEquals('eName', $form->formName);
        $this->assertEquals(1, $form->formId);
        $this->assertEquals(4, $form->userId);
        $this->assertEquals(3, $form->contragentId);
        $this->assertEquals($form->items, array(0 => $item, 1 => $item1));
        $form->items = $item;
        $this->assertTrue($form->isValid());
    }

/**
 * @expectedException InvalidArgumentException
 * @expectedExceptionMessage Cannot create item from array within Form. Item is now valid
 */    
    public function testFormNotValidItemAssignment() {
        $itemArray = array('itemName' => 'item1', 'domainId' => 1, 'value' => 55.4, 'userId' => 6);
        $item = new Application_Model_Item($itemArray);
        $this->assertFalse($item->isValid());
        $formArray = array('formName' => 'eName', 'formId' => 3, 'userId' => 5, 'active' => false, 'domainId' => 5, 'items' => array(0 => $item), 'nodeId' => 3);
        $form = new Application_Model_Form($formArray);
        $this->assertFalse($form->isValid());
        
    }

    public function testFormItemsSetingGetting(){
        $itemArray = array('itemName' => 'item1', 'domainId' => 1, 'value' => 55.4, 'userId' => 6,'elementId' => 2, 'formId' => 4);
        $item = new Application_Model_Item($itemArray);
        $this->assertTrue($item->isValid());
        $formArray = array('formName' => 'eName', 'formId' => 3, 'userId' => 5, 'active' => false, 'domainId' => 5, 'items' => array(0 => $item), 'nodeId' => 3);
        $form = new Application_Model_Form($formArray);
        $itemArray2 = $form->items;
        $this->assertTrue(is_array($itemArray2));
        $this->assertTrue($itemArray2[0] instanceof Application_Model_Item);
        $this->assertEquals(array(0 => $item), $itemArray2);
        
    }

/**
 * @expectedException InvalidArgumentException
 * @expectedExceptionMessage One of items is neither of Application_Model_Item type nor Array().
 */    
    
    public function testFormItemInvalidAssignment(){
        $item = 'wrong item';
        $formArray = array('formName' => 'eName', 'formId' => 3, 'userId' => 5, 'active' => false, 'domainId' => 5, 'items' => array(0 => $item), 'nodeId' => 3);
        $form = new Application_Model_Form($formArray);
    }
    
    public function testFormToArray() {
        $formArray = array('formName' => 'eName', 'userId' => 12, 'formId' => 3, 'projectId' => 4, 'active' => 0, 'public'=>1, 'domainId' => 5, 'contragentId'=>3);
        $form = new Application_Model_Form($formArray);
        $formArray2 = $form->toArray();
        unset($formArray['date']);
        unset($formArray2['date']);
        $this->assertEquals($formArray, $formArray2);
    }
}
