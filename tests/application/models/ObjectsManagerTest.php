<?php

/**
 * Description of ObjectsManagerTest
 *
 * @author Max
 */

require_once APPLICATION_PATH . '/models/ObjectsManager.php';
require_once APPLICATION_PATH . '/models/DataMapper.php';
class ObjectsManagerTest extends TestCase {

    private $node1;
    private $nodeId1;
    private $node;
    private $nodeId;
    private $user;
    private $userId;
    private $form;
    private $formId;
    private $dataMapper;
    private $objectManager;
    private $contragentId;

    // We have to fill database with some date to proceed further
    public function setUp() {
        $nodeArray = array('nodeName' => 'lName', 'domainId' => 1, 'parentNodeId' => 0);
        $this->node = new Application_Model_Node($nodeArray);
        $this->assertTrue($this->node->isValid());
        $this->dataMapper = new Application_Model_DataMapper(1);
        $this->nodeId = $this->dataMapper->saveObject($this->node);
        $nodeArray1 = array('nodeName' => 'oName', 'parentNodeId' => $this->nodeId, 'domainId' => 1);
        $this->node1 = new Application_Model_Node($nodeArray1);
        $this->assertTrue($this->node1->isValid());
        $this->nodeId1 = $this->dataMapper->saveObject($this->node1);
        $positionArray = array('positionName' => 'position_omt', 'domainId' => 1, 'positionCode' => 4, 'nodeId' => $this->nodeId);
        $this->position = new Application_Model_Position($positionArray);
        $this->assertTrue($this->position->isValid());
        $this->positionId = $this->dataMapper->saveObject($this->position);
        $userArray = array('userName' => 'uName_omt', 'nodeId' => $this->nodeId, 'positionId' => $this->positionId, 'domainId' => 1, 'login' => 'login_omt', 'password' => 'rrrr');
        $this->user = new Application_Model_User($userArray);
        $this->assertTrue($this->user->isValid());
        $this->userId = $this->dataMapper->saveObject($this->user);
        $elementArray = array('elementName' => 'eName', 'domainId' => 1, 'elementCode' => 34);
        $this->element = new Application_Model_Element($elementArray);
        $this->assertTrue($this->element->isValid());
        $this->elementId = $this->dataMapper->saveObject($this->element);
        $this->objectManager = new Application_Model_ObjectsManager(1);
        $privilegeArray = array('objectType' => 'node', 'objectId' => $this->nodeId, 'userId' => $this->userId, 'privilege' => 'write', 'domainId' => 1);
        $privilege = new Application_Model_Privilege($privilegeArray);
        $this->dataMapper->saveObject($privilege);
        $privilegeArray1 = array('objectType' => 'node', 'objectId' => $this->nodeId, 'userId' => $this->userId, 'privilege' => 'read', 'domainId' => 1);
        $privilege1 = new Application_Model_Privilege($privilegeArray1);
        $this->dataMapper->saveObject($privilege1);
// CONTRAGENT
        $contragentArray = array('contragentName'=>'cName', 'domainId'=>1);
        $contragent = new Application_Model_Contragent($contragentArray);
        $this->assertTrue($contragent->isValid());
        $this->contragentId = $this->dataMapper->saveObject($contragent);
        $this->assertTrue($contragent instanceof Application_Model_Contragent);
        $this->assertTrue(is_int($this->contragentId));
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
        $this->dataMapper->dbLink->delete('contragent');
    }

    public function testFormSaveCorrect() {
        $itemArray1 = array('itemName' => 'item1', 'domainId' => 1, 'value' => 55.4, 'elementId' => $this->elementId, 'formId' => 1);
        $item1 = new Application_Model_Item($itemArray1);
        $this->assertTrue($item1->isValid());
        $itemArray2 = array('itemName' => 'item2', 'domainId' => 1, 'value' => 22.1, 'elementId' => $this->elementId, 'formId' => 1);
        $item2 = new Application_Model_Item($itemArray2);
        $this->assertTrue($item2->isValid());
        $formArray1 = array('userId' => $this->userId, 'formName' => 'fName1', 'nodeId' => $this->nodeId, 'items' => array(0 => $item1, 1 => $item2), 'domainId' => 1, 'active' => 1, 'public'=>1, 'contragentId'=>$this->contragentId);
        $this->form = new Application_Model_Form($formArray1);
        $formArray2 = $this->form->toArray();
        unset($formArray2['date']);
        $this->assertEquals($formArray1, $formArray2);
        $this->assertTrue($this->form->isValid());
        $this->formId = $this->objectManager->saveForm($formArray1,$this->userId);
        $this->assertTrue(is_int($this->formId));
        $formArray3 = array('userId' => $this->userId, 'formName' => 'fName2', 'nodeId' => $this->nodeId, 'items' => array(1 => $item1, 2 => $item2), 'domainId' => 1, 'contragentId'=>$this->contragentId);
        $form2 = new Application_Model_Form($formArray3);
        $this->assertTrue($form2->isValid());
        $formId = $this->objectManager->saveForm($form2,$this->userId);
        $this->assertTrue(is_int($formId));
    }

    

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSaveFormInvalid() {
        $formArray1 = array('userId' => $this->userId, 'formName' => 'fName1', 'nodeId' => $this->nodeId, 'domainId' => 1, 'active' => true);
        $form = new Application_Model_Form($formArray1);
        $formId = $this->objectManager->saveForm($formArray1, $this->userId);
    }
    
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Cannot create item from array within Form. Item is now valid
     */
    public function testSaveFormInvalidItems()
    {
        $itemArray1 = array('itemName' => 'item1', 'domainId' => 1, 'userId' => $this->userId, 'elementId' => $this->elementId, 'formId' => 1);
        $item1 = new Application_Model_Item($itemArray1);
        $this->assertFalse($item1->isValid());
        $itemArray2 = array('itemName' => 'item2', 'value' => 22.1, 'userId' => $this->userId, 'elementId' => $this->elementId, 'formId' => 1);
        $item2 = new Application_Model_Item($itemArray2);
        $this->assertFalse($item2->isValid());
        $formArray1 = array('userId' => $this->userId, 'formName' => 'fName1', 'nodeId' => $this->nodeId, 'items' => array(0 => $item1, 1 => $item2), 'domainId' => 1, 'active' => true);
        $form = new Application_Model_Form($formArray1);
        $formId = $this->objectManager->saveForm($formArray1, $this->userId);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSaveFormTotallyInvalid()
    {
        $formData = 888;
        $this->objectManager->saveForm($formData, $this->userId);
    }

    public function testSaveFormExisting()
    {
        $itemArray1 = array('itemName' => 'item1', 'domainId' => 1, 'value' => 55.4, 'userId' => $this->userId, 'elementId' => $this->elementId, 'formId' => 1);
        $item1 = new Application_Model_Item($itemArray1);
        $this->assertTrue($item1->isValid());
        $itemArray2 = array('itemName' => 'item2', 'domainId' => 1, 'value' => 22.1, 'userId' => $this->userId, 'elementId' => $this->elementId, 'formId' => 1);
        $item2 = new Application_Model_Item($itemArray2);
        $this->assertTrue($item2->isValid());
        $formArray1 = array('userId' => $this->userId, 'formName' => 'fName1', 'nodeId' => $this->nodeId, 'items' => array(0 => $item1, 1 => $item2), 'domainId' => 1, 'active' => true, 'contragentId'=>$this->contragentId);
        $form = new Application_Model_Form($formArray1, $this->userId);
        $this->assertTrue($form->isValid());
        $formId = $this->objectManager->saveForm($formArray1, $this->userId);
        $form2 = $this->objectManager->getForm($formId, $this->userId);

        
        
        $form2->formName = 'fName2';
        $itemArray3 = array('itemName' => 'item3', 'domainId' => 1, 'value' => 22.1, 'userId' => $this->userId, 'elementId' => $this->elementId, 'formId' => 1);
        $item3 = new Application_Model_Item($itemArray3);
        $form2->items = $item3;
//        Zend_Debug::dump($item3);
       // ++++++++++++++++++++++++ ????????????????????????? $form2 is changing its value for some reason here!!! +++++
        $formId2 = $this->objectManager->saveForm($form2, $this->userId);
        $this->assertTrue(is_int($formId2));
        // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        $form2->items = $item3;
        $this->assertEquals($formId, $formId);
        $form3 = $this->objectManager->getForm($formId2, $this->userId);
        $this->assertEquals($form3, $form2);
        
        $itemArray5 = array('itemName' => 'item5', 'domainId' => 1, 'value' => 222, 'userId' => $this->userId, 'elementId' => $this->elementId, 'formId' => 1);
        $item5 = new Application_Model_Item($itemArray5);
        $this->assertTrue($item5->isValid());
        $itemArray4 = array('itemName' => 'item4', 'domainId' => 1, 'value' => 333, 'userId' => $this->userId, 'elementId' => $this->elementId, 'formId' => 1);
        $item4 = new Application_Model_Item($itemArray4);
        $this->assertTrue($item4->isValid());
        $formArray5 = array('userId' => $this->userId, 'formName' => 'fName5', 'nodeId' => $this->nodeId, 'items' => array(0 => $item4, 1 => $item5), 'domainId' => 1, 'active' => true, 'contragentId'=>$this->contragentId);
        $form5 = new Application_Model_Form($formArray5, $this->userId);
        $this->assertTrue($form5->isValid());
        $formId5 = $this->objectManager->saveForm($form5, $this->userId);
        $formsave = $this->objectManager->getForm($formId5,$this->userId);
        $formsave->public = 1;
        $formId6 = $this->objectManager->saveForm($formsave, $this->userId);
        $formtest1 = $this->objectManager->getForm($formId6, $this->userId);
        $formtest2 = $this->objectManager->getForm($formId2, $this->userId);
        $this->assertNotEquals($formtest1->formName, $formtest2->formname);
        $this->assertEquals($formtest1->formName, 'fName5');
        $this->assertTrue($formtest2->isValid());
        $this->assertEquals($formtest2->formName, 'fName2');
        $this->assertEquals($formtest1->public, true);
        $this->assertEquals($formtest2->items, array(0=>$item3));
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetFormInvalidArgument()
    {
        $this->objectManager->getForm('test', $this->userId);
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetFormNoArgument()
    {
        $this->objectManager->getForm('', $this->userId);
    }
/**
 * @expectedException Exception
 * @expectedExceptionMessage Form with ID -1 doesnt exist
 */    
    public function testGetFormThatDoesntExist(){
        $this->objectManager->getForm(-1, $this->userId);
    }
    
    
    public function testGetFormCorruptedData(){
        $itemArray1 = array('itemName' => 'item1', 'domainId' => 1, 'value' => 55.4, 'userId' => $this->userId, 'elementId' => $this->elementId, 'formId' => 1);
        $item1 = new Application_Model_Item($itemArray1);
        $this->assertTrue($item1->isValid());
        $itemArray2 = array('itemName' => 'item2', 'domainId' => 1, 'value' => 22.1, 'userId' => $this->userId, 'elementId' => $this->elementId, 'formId' => 1);
        $item2 = new Application_Model_Item($itemArray2);
        $this->assertTrue($item2->isValid());
        $formArray1 = array('userId' => $this->userId, 'formName' => 'fName1', 'nodeId' => $this->nodeId, 'items' => array(0 => $item1, 1 => $item2), 'domainId' => 1, 'active' => true, 'contragentId'=>$this->contragentId);
        $form = new Application_Model_Form($formArray1);
        $this->assertTrue($form->isValid());
        $formId = $this->objectManager->saveForm($formArray1, $this->userId);
        $this->dataMapper->dbLink->update('form', array('formName'=>'', 'domainId'=>1), array('formId'=>$formId));
        $form2 = $this->objectManager->getForm($formId, $this->userId);
    }
            
    
    public function testGetForm() {
        $itemArray1 = array('itemName' => 'item1', 'domainId' => 1, 'value' => 55.4, 'userId' => $this->userId, 'elementId' => $this->elementId, 'formId' => 1);
        $item1 = new Application_Model_Item($itemArray1);
        $this->assertTrue($item1->isValid());
        $itemArray2 = array('itemName' => 'item2', 'domainId' => 1, 'value' => 22.1, 'userId' => $this->userId, 'elementId' => $this->elementId, 'formId' => 1);
        $item2 = new Application_Model_Item($itemArray2);
        $this->assertTrue($item2->isValid());
        $formArray1 = array('userId' => $this->userId, 'formName' => 'fName1', 'nodeId' => $this->nodeId, 'items' => array(0 => $item1, 1 => $item2), 'domainId' => 1, 'active' => 1, 'public'=>1, 'contragentId'=>$this->contragentId);
        $form = new Application_Model_Form($formArray1);
        $this->assertTrue($form->isValid());
        $formId = $this->objectManager->saveForm($formArray1, $this->userId);
        $form1 = $this->objectManager->getForm($formId, $this->userId);
        $this->assertTrue($form1 instanceof Application_Model_Form);
        $formArray2 = $form1->toArray();
        unset($formArray2['date']);
        unset($formArray2['formId']);
        $this->assertEquals($formArray1, $formArray2);
    }
    
    public function testCheckUserExustance(){
        $this->assertTrue($this->objectManager->checkLoginExistance('login_omt'));
        $this->assertFalse($this->objectManager->checkLoginExistance('login_non_existing'));
    }

}

?>
