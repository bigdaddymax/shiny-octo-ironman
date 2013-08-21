<?php

/**
 * Description of ObjectsManagerTest
 *
 * @author Max
 */
require_once APPLICATION_PATH . '/models/FormsManager.php';
require_once APPLICATION_PATH . '/models/DataMapper.php';

class OM_FormsTest extends TestCase {

    private $node1;
    private $nodeId1;
    private $node;
    private $nodeId;
    private $user;
    private $userId;
    private $form;
    private $formId;
    private $formsManager;
    private $dataMapper;
    private $contragentId;

    // We have to fill database with some date to proceed further
    public function setUp() {
        $this->dataMapper = new Application_Model_DataMapper();
        $nodeArray = array('nodeName' => 'lName', 'domainId' => 1, 'parentNodeId' => -1);
        $this->node = new Application_Model_Node($nodeArray);
        $this->assertTrue($this->node->isValid());
        $this->formsManager = new Application_Model_FormsManager(1);
        $this->nodeId = $this->formsManager->saveObject($this->node);
        $nodeArray1 = array('nodeName' => 'oName', 'parentNodeId' => $this->nodeId, 'domainId' => 1);
        $this->node1 = new Application_Model_Node($nodeArray1);
        $this->assertTrue($this->node1->isValid());
        $this->nodeId1 = $this->formsManager->saveObject($this->node1);
        $positionArray = array('positionName' => 'position_omt', 'domainId' => 1, 'positionCode' => 4, 'nodeId' => $this->nodeId);
        $this->position = new Application_Model_Position($positionArray);
        $this->assertTrue($this->position->isValid());
        $this->positionId = $this->formsManager->saveObject($this->position);
        $userArray = array('userName' => 'uName_omt', 'nodeId' => $this->nodeId, 'positionId' => $this->positionId, 'domainId' => 1, 'login' => 'login_omt', 'password' => 'rrrr');
        $this->user = new Application_Model_User($userArray);
        $this->assertTrue($this->user->isValid());
        $this->userId = $this->formsManager->saveObject($this->user);
        $this->user->userId = $this->userId;
        $elementArray = array('elementName' => 'eName', 'domainId' => 1, 'elementCode' => 34, 'expgroup' => 'CAPEX');
        $this->element = new Application_Model_Element($elementArray);
        $this->assertTrue($this->element->isValid());
        $this->elementId = $this->formsManager->saveObject($this->element);
        $privilegeArray = array('objectType' => 'node', 'objectId' => $this->nodeId, 'userId' => $this->userId, 'privilege' => 'write', 'domainId' => 1);
        $privilege = new Application_Model_Privilege($privilegeArray);
        $this->formsManager->saveObject($privilege);
        $privilegeArray1 = array('objectType' => 'node', 'objectId' => $this->nodeId, 'userId' => $this->userId, 'privilege' => 'read', 'domainId' => 1);
        $privilege1 = new Application_Model_Privilege($privilegeArray1);
        $this->formsManager->saveObject($privilege1);
// CONTRAGENT
        $contragentArray = array('contragentName' => 'cName', 'domainId' => 1);
        $contragent = new Application_Model_Contragent($contragentArray);
        $this->assertTrue($contragent->isValid());
        $this->contragentId = $this->formsManager->saveObject($contragent);
        $this->assertTrue($contragent instanceof Application_Model_Contragent);
        $this->assertTrue(is_int($this->contragentId));

        // Template
        $templateArray = array('templateName' => 'test template', 'language' => 'en', 'type' => 'approved_next', 'body' => '<!DOCTYPE html>
<html>
    <head>
        <title>Approval needed</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    </head>
    <body>
        <h3>Dear %name%</h3>
        <div>Invoice "%fname%" from %contragent% totally amounted %total% was just approved.</div>
        <div>Please review this invoice and take appropriate action</div>
        <div><a href="%link%">Click here to open invoice</a></div>
    </body>
</html>', 'domainId' => 1);
        $template = new Application_Model_Template($templateArray);
        $this->formsManager->saveObject($template);
        $templateArray = array('templateName' => 'test template', 'language' => 'en', 'type' => 'approved_subj_next', 'body' => 'Invoice "%fname%" needs your consideration.', 'domainId' => 1);
        $template = new Application_Model_Template($templateArray);
        $this->formsManager->saveObject($template);
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
        $this->dataMapper->dbLink->delete('template');
    }

    public function testFormSaveCorrect() {
        $itemArray1 = array('itemName' => 'item1', 'domainId' => 1, 'value' => 55.4, 'elementId' => $this->elementId, 'formId' => 1);
        $item1 = new Application_Model_Item($itemArray1);
        $this->assertTrue($item1->isValid());
        $itemArray2 = array('itemName' => 'item2', 'domainId' => 1, 'value' => 22.1, 'elementId' => $this->elementId, 'formId' => 1);
        $item2 = new Application_Model_Item($itemArray2);
        $this->assertTrue($item2->isValid());
        $formArray1 = array('userId' => $this->userId, 'formName' => 'fName1', 'nodeId' => $this->nodeId, 'items' => array(0 => $item1, 1 => $item2), 'domainId' => 1, 'active' => 1, 'public' => 1, 'contragentId' => $this->contragentId, 'expgroup' => 'OPEX');
        $this->form = new Application_Model_Form($formArray1);
        $formArray2 = $this->form->toArray();
        unset($formArray2['date']);
        $this->assertEquals($formArray1, $formArray2);
        $this->assertTrue($this->form->isValid());
        $this->formId = $this->formsManager->saveObject($this->form);
        $this->assertTrue(is_int($this->formId));
        $formArray3 = array('userId' => $this->userId, 'formName' => 'fName2', 'nodeId' => $this->nodeId, 'items' => array(1 => $item1, 2 => $item2), 'domainId' => 1, 'contragentId' => $this->contragentId, 'expgroup' => 'OPEX');
        $form2 = new Application_Model_Form($formArray3);
        $this->assertTrue($form2->isValid());
        $formId = $this->formsManager->saveObject($form2);
        $this->assertTrue(is_int($formId));
    }

/**
 * @expectedException SaveObjectException
 */
    public function testSaveFormInvalid() {
        $formArray1 = array('userId' => $this->userId, 'formName' => 'fName1', 'nodeId' => $this->nodeId, 'domainId' => 1, 'active' => true);
        $form = new Application_Model_Form($formArray1);
        $this->assertFalse($form->isValid());
        $formId = $this->formsManager->saveObject($form);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Cannot
     */
    public function testSaveFormInvalidItems() {
        $itemArray1 = array('itemName' => 'item1', 'domainId' => 1, 'userId' => $this->userId, 'elementId' => $this->elementId, 'formId' => 1);
        $item1 = new Application_Model_Item($itemArray1);
        $this->assertFalse($item1->isValid());
        $itemArray2 = array('itemName' => 'item2', 'value' => 22.1, 'userId' => $this->userId, 'elementId' => $this->elementId, 'formId' => 1);
        $item2 = new Application_Model_Item($itemArray2);
        $this->assertFalse($item2->isValid());
        $formArray1 = array('userId' => $this->userId, 'formName' => 'fName1', 'nodeId' => $this->nodeId, 'items' => array(0 => $item1, 1 => $item2), 'domainId' => 1, 'active' => true);
        $form = new Application_Model_Form($formArray1);
        $formId = $this->formsManager->saveObject($form);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Input data should be array or JSON or Object for saveObject method
     */
    public function testSaveFormTotallyInvalid() {
        $formData = 888;
        $this->formsManager->saveObject($formData);
    }

    public function testSaveFormExisting() {

        // Create form items
        $itemArray1 = array('itemName' => 'item1', 'domainId' => 1, 'value' => 55.4, 'userId' => $this->userId, 'elementId' => $this->elementId, 'formId' => 1);
        $item1 = new Application_Model_Item($itemArray1);
        $this->assertTrue($item1->isValid());
        $itemArray2 = array('itemName' => 'item2', 'domainId' => 1, 'value' => 22.1, 'userId' => $this->userId, 'elementId' => $this->elementId, 'formId' => 1);
        $item2 = new Application_Model_Item($itemArray2);
        $this->assertTrue($item2->isValid());

        // Create and save form
        $formArray1 = array('userId' => $this->userId, 'formName' => 'fName1', 'nodeId' => $this->nodeId, 'items' => array(0 => $item1, 1 => $item2), 'domainId' => 1, 'active' => 1, 'contragentId' => $this->contragentId, 'expgroup' => 'OPEX');
        $form = new Application_Model_Form($formArray1, $this->userId);
        $this->assertTrue($form->isValid());
        $items = $form->items;
        $formId = $this->formsManager->saveObject($form);
        $form->formId = $formId;

        // Retrieve form from DB and and check it
        $form2 = $this->formsManager->getObject('form', $formId, $this->userId);
        $items2 = $form2->items;
        $form2->items = null;

        foreach ($items2 as $i => $tmpItem) {
            $items[$i]->itemId = $tmpItem->itemId;
        }
        $this->assertEquals($items, $items2);
        $this->assertEquals($form, $form2);

        // Modify form and save it again
        $form2->formName = 'fName2';
        $itemArray3 = array('itemName' => 'item3', 'domainId' => 1, 'value' => 22.1, 'userId' => $this->userId, 'elementId' => $this->elementId);
        $item3 = new Application_Model_Item($itemArray3);
        $form2->items = $item3;
        $formId2 = $this->formsManager->saveObject($form2);
        $this->assertTrue(is_int($formId2));
        $this->assertEquals($formId2, $formId);


        // confirm that formId is the same
        $this->assertEquals($formId, $formId);

        // Retrive form from DB again and confirm that saved one and retrived are equal
        $form3 = $this->formsManager->getObject('form', $formId2, $this->userId);
        $items3 = $form3->items;
        $item3->itemId = $items3[0]->itemId;
        // Restore form2 after calling SaveObject()
        $form2->items = $item3;
        $this->assertEquals($form3, $form2);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetFormNoArgument() {
        $this->formsManager->getObject('form', '', $this->userId);
    }

/**
 * @expectedException InvalidArgumentException
 */
    public function testGetFormThatDoesntExist() {
       $this->formsManager->getObject('form', -1);
    }

    public function testGetFormCorruptedData() {
        $itemArray1 = array('itemName' => 'item1', 'domainId' => 1, 'value' => 55.4, 'userId' => $this->userId, 'elementId' => $this->elementId, 'formId' => 1);
        $item1 = new Application_Model_Item($itemArray1);
        $this->assertTrue($item1->isValid());
        $itemArray2 = array('itemName' => 'item2', 'domainId' => 1, 'value' => 22.1, 'userId' => $this->userId, 'elementId' => $this->elementId, 'formId' => 1);
        $item2 = new Application_Model_Item($itemArray2);
        $this->assertTrue($item2->isValid());
        $formArray1 = array('userId' => $this->userId, 'formName' => 'fName1', 'nodeId' => $this->nodeId, 'items' => array(0 => $item1, 1 => $item2), 'domainId' => 1, 'active' => true, 'contragentId' => $this->contragentId, 'expgroup' => 'OPEX');
        $form = new Application_Model_Form($formArray1);
        $this->assertTrue($form->isValid());
        $formId = $this->formsManager->saveObject($form);
        $this->dataMapper->dbLink->update('form', array('formName' => '', 'domainId' => 1), array('formId' => $formId));
        $form2 = $this->formsManager->getObject('form', $formId, $this->userId);
    }

    public function testGetForm() {
        // Create form items first
        $itemArray1 = array('itemName' => 'item1', 'domainId' => 1, 'value' => 55.4, 'userId' => $this->userId, 'elementId' => $this->elementId, 'formId' => 1);
        $item1 = new Application_Model_Item($itemArray1);
        $this->assertTrue($item1->isValid());
        $itemArray2 = array('itemName' => 'item2', 'domainId' => 1, 'value' => 22.1, 'userId' => $this->userId, 'elementId' => $this->elementId, 'formId' => 1);
        $item2 = new Application_Model_Item($itemArray2);
        $this->assertTrue($item2->isValid());

        // Create form
        $formArray1 = array('userId' => $this->userId, 'formName' => 'fName1', 'nodeId' => $this->nodeId, 'items' => array(0 => $item1, 1 => $item2), 'domainId' => 1, 'active' => 1, 'public' => 1, 'contragentId' => $this->contragentId, 'expgroup' => 'OPEX');
        $form = new Application_Model_Form($formArray1);
        $this->assertTrue($form->isValid());

        // Save form
        $formId = $this->formsManager->saveObject($form);
        $form1 = $this->formsManager->getObject('form', $formId, $this->userId);
        $this->assertTrue($form1 instanceof Application_Model_Form);
        $formArray2 = $form1->toArray();

        // Unset or set to '0' properties that we cannot compare: date of form saving and IDs of form items
        unset($formArray2['date']);
        unset($formArray2['formId']);
        $formArray2['items'][0]->itemId = 0;
        $formArray2['items'][1]->itemId = 0;
        $this->assertEquals($formArray1, $formArray2);
    }

    public function testCheckUserExistance() {
        $this->assertTrue(is_int($this->formsManager->checkLoginExistance('login_omt')));
        $this->assertEquals(0, $this->formsManager->checkLoginExistance('login_non_existing'));
    }

    public function testGetExistingUser() {
        $user = $this->formsManager->getObject('user', $this->userId);
        $user->password = 1;
        $this->user->password = 1;
        $this->assertEquals($user, $this->user);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetEmailListWrongForm() {
        $this->formsManager->getEmailingList(-1, 'approve');
    }

    public function testCreateEmailBodyAndSubject() {
        $itemArray1 = array('itemName' => 'item1', 'domainId' => 1, 'value' => 55.4, 'elementId' => $this->elementId, 'formId' => 1);
        $item1 = new Application_Model_Item($itemArray1);
        $itemArray2 = array('itemName' => 'item2', 'domainId' => 1, 'value' => 22.1, 'elementId' => $this->elementId, 'formId' => 1);
        $item2 = new Application_Model_Item($itemArray2);
        $formArray1 = array('userId' => $this->userId, 'formName' => 'fName1', 'nodeId' => $this->nodeId, 'items' => array(0 => $item1, 1 => $item2), 'domainId' => 1, 'active' => 1, 'public' => 1, 'contragentId' => $this->contragentId, 'expgroup' => 'OPEX');
        $form = new Application_Model_Form($formArray1);
        $formId = $this->formsManager->saveObject($form);

        $html = $this->formsManager->createEmailBody('login_omt', 'approved_next', 'en', $formId);
        $path = dirname(__FILE__);
        $example = file_get_contents($path . '/email_template_approve.html', true);
        $this->assertEquals($html, $example);

        $subj = $this->formsManager->createEmailBody('login_omt', 'approved_subj_next', 'en', $formId);
        $this->assertEquals($subj, 'Invoice "fName1" needs your consideration.');
    }

}

?>
