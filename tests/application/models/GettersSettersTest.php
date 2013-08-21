<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GettersSettersTest
 *
 * @author Max
 */
require_once 'PHPUnit/Extensions/OutputTestCase.php';

class GettersSettersTest extends PHPUnit_Extensions_OutputTestCase {

    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testNodeGetterAndSetter() {
        $nodeArray = array('parentNodeId' => 3);
        $node = new Application_Model_Node($nodeArray);
        $node->nodeName = 'lName';
        $this->assertEquals('lName', $node->nodeName);
        $node->parentNodeId = null;
        $this->assertEquals(0, $node->parentNodeId);
        $node->ttt = 'Test';
        $ttt = $node->ttt;
        $node->valid = 2;
    }

    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testPositionGetterSetter() {
        $position = new Application_Model_Position();
        $position->positionName = 'eName';
        $position->positionStatus = 'status';
//        $position->state = 'state';
//        $position->valid1 =4;
        $test = $position->positionState;
        $this->assertEquals('Cannot get value. Property positionState doesnt exist', $test);
        $position->valid = 1;
    }

    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testElementGetterSetter() {
        $element = new Application_Model_Element();
        $element->elementName = 'eName';
        $element->elementStatus = 'status';
//        $element->state = 'state';
//        $element->valid1 =4;
        $test = $element->elementState;
        $this->assertEquals('Cannot get value. Property elementState doesnt exist', $test);
        $element->valid = 1;
    }

    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testUserGetterAndSetter() {
        $userArray = array('parentLevelId' => 3);
        $user = new Application_Model_User($userArray);
        $user->userName = 'oName';
        $this->assertEquals('oName', $user->userName);
        $user->ttt = 'Test';

        $ttt = $user->ttt;
        $this->assertEquals('Cannot get value. Property ttt doesnt exist', $ttt);
        $user->valid = 2;
    }

    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testPrivilegeGetterAndSetter() {
        $privilegeArray = array('userId' => 3);
        $privilege = new Application_Model_privilege($privilegeArray);
        $privilege->objectId = 2;
        $this->assertEquals(2, $privilege->objectId);
        $privilege->ttt = 'Test';

        $ttt = $privilege->ttt;
        $this->assertEquals('Cannot get value. Property ttt doesnt exist', $ttt);
        $privilege->valid = 2;
    }

    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testDomainGetterSetter() {
        $domain = new Application_Model_Domain();
        $domain->domainName = 'dName';
        $domain->domainStatus = 'status';
        ob_clean();
//        $domain->state = 'state';
//        $domain->valid1 =4;
        $test = $domain->domainState;
        $this->assertEquals('Cannot get value. Property domainState doesnt exist', $test);
        $domain->valid = 1;
    }

    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testContragentGetterSetter() {
        $contragent = new Application_Model_Contragent();
        $contragent->contragentName = 'eName';
        $contragent->contragentStatus = 'status';
//        $contragent->state = 'state';
//        $contragent->valid1 =4;
        $test = $contragent->contragentState;
        $this->assertEquals('Cannot get value. Property contragentState doesnt exist', $test);
        $contragent->valid = 1;
    }

    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testScenarioGetterSetter() {
        $scenario = new Application_Model_Scenario();
        $scenario->scenarioName = 'eName';
        $scenario->scenarioStatus = 'status';
        $test = $scenario->scenarioState;
        $this->assertEquals('Cannot get value. Property scenarioState doesnt exist', $test);

        $scenario->valid = 1;
    }

    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testFormGetterSetter() {
        $form = new Application_Model_Form();
        $form->formName = 'eName';
        $form->formStatus = 'status';
//        $form->state = 'state';
//        $form->valid1 =4;
        $test = $form->formState;
        $this->assertEquals('Cannot get value. Property formState doesnt exist', $test);
        $form->valid = 1;
    }

    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testItemGetterSetter() {
        $item = new Application_Model_Item();
        $item->itemName = 'eName';
        $item->itemStatus = 'status';
        ob_clean();
//        $item->state = 'state';
//        $item->valid1 =4;
        $test = $item->itemState;
        $this->assertEquals('Cannot get value. Property itemState doesnt exist', $test);
        $item->valid = 1;
    }
    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testCommentGetterSetter() {
        $comment = new Application_Model_Comment();
        $comment->text = 'test test test test';
        $comment->commentStatus = 'status';
        ob_clean();
        $test = $comment->commentState;
        $this->assertEquals('Cannot get value. Property commentState doesnt exist', $test);
        $comment->valid = 1;
    }

    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testTemplateGetterSetter() {
        $template = new Application_Model_Template();
        $template->templateName = 'Test Template';
        $template->templateId  = 3;
        $this->assertEquals('Test Template', $template->templateName);
        $this->assertEquals(3, $template->templateId);
        $template->valid = 1;
        $ttt = $template->ttt;
    }
}

?>
