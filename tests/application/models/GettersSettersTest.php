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
        $node->valid = 1;
        $this->assertFalse($node->isValid());
        $node->nodeName = 'lName';
        $this->assertEquals('lName', $node->nodeName);
        $node->parentNodeId = null;
        $this->assertEquals(0, $node->parentNodeId);
        $node->ttt = 'Test';
        $ttt = $node->ttt;
    }

    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testPositionGetterSetter() {
        $position = new Application_Model_Position();
        $position->valid = 1;
        $this->assertFalse($position->isValid());
        $position->positionName = 'eName';
        $this->assertEquals('eName', $position->positionName);
        $position->positionStatus = 'status';
        $test = $position->positionState;
    }

    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testElementGetterSetter() {
        $element = new Application_Model_Element();
        $element->valid = 1;
        $this->assertFalse($element->isValid());
        $element->elementName = 'eName';
        $this->assertEquals('eName', $element->elementName);
        $element->elementStatus = 'status';
        $test = $element->elementState;
    }

    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testUserGroupGetterSetter() {
        $userGroup = new Application_Model_UserGroup();
        $userGroup->valid = 1;
        $this->assertFalse($userGroup->isValid());
        $userGroup->userGroupName = 'eName';
        $this->assertEquals('eName', $userGroup->userGroupName);
        $userGroup->elementStatus = 'status';
        $test = $userGroup->elementState;
    }

    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testUserGetterAndSetter() {
        $userArray = array('parentLevelId' => 3);
        $user = new Application_Model_User($userArray);
        $user->valid = 1;
        $this->assertFalse($user->isValid());
        $user->userName = 'oName';
        $this->assertEquals('oName', $user->userName);
        $user->ttt = 'Test';
        $ttt = $user->ttt;
    }

    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testPrivilegeGetterAndSetter() {
        $privilegeArray = array('userId' => 3);
        $privilege = new Application_Model_privilege($privilegeArray);
        $privilege->valid = 1;
        $this->assertFalse($privilege->isValid());
        $privilege->objectId = 2;
        $this->assertEquals(2, $privilege->objectId);
        $privilege->ttt = 'Test';

        $ttt = $privilege->ttt;
    }

    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testDomainGetterSetter() {
        $domain = new Application_Model_Domain();
        $domain->valid = 1;
        $this->assertFalse($domain->isValid());
        $domain->domainName = 'dName';
        $this->assertEquals('dName', $domain->domainName);
        $domain->domainStatus = 'status';
        $test = $domain->domainState;
    }

    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testContragentGetterSetter() {
        $contragent = new Application_Model_Contragent();
        $contragent->valid = 1;
        $this->assertFalse($contragent->isValid());
        $contragent->contragentName = 'eName';
        $this->assertEquals('eName', $contragent->contragentName);
        $contragent->contragentStatus = 'status';

        $test = $contragent->contragentState;
    }

    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testScenarioGetterSetter() {
        $scenario = new Application_Model_Scenario();
        $scenario->valid = 1;
        $this->assertFalse($scenario->isValid());
        $scenario->scenarioName = 'eName';
        $scenario->scenarioStatus = 'status';
        $test = $scenario->scenarioState;
    }

    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testScenarioEntryGetterSetter() {
        $scenario = new Application_Model_ScenarioEntry();
        $scenario->valid = 1;
        $this->assertFalse($scenario->isValid());
        $scenario->scenarioId = 44;
        $this->assertEquals(44, $scenario->scenarioId);
        $scenario->scenarioStatus = 'status';
        $test = $scenario->scenarioState;
    }

    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testScenarioAssignmentGetterSetter() {
        $scenario = new Application_Model_ScenarioAssignment();
        $scenario->valid = 1;
        $this->assertFalse($scenario->isValid());
        $scenario->scenarioId = 44;
        $this->assertEquals(44, $scenario->scenarioId);
        $scenario->scenarioStatus = 'status';
        $test = $scenario->scenarioState;
    }
    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testFormGetterSetter() {
        $form = new Application_Model_Form();
        $form->formName = 'eName';
        $form->valid = 1;
        $this->assertFalse($form->isValid());
        $form->formStatus = 'status';
        $test = $form->formState;
    }

    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testItemGetterSetter() {
        $item = new Application_Model_Item();
        $item->itemName = 'eName';
        $item->itemStatus = 'status';
        $item->valid = 1;
        $this->assertFalse($item->isValid());
        $test = $item->itemState;
    }

    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testCommentGetterSetter() {
        $comment = new Application_Model_Comment();
        $comment->valid = 1;
        $this->assertFalse($comment->isValid());
        $comment->text = 'test test test test';
        $this->assertEquals('test test test', $comment->text);
        $comment->commentStatus = 'status';
        $test = $comment->commentState;
    }

    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testTemplateGetterSetter() {
        $template = new Application_Model_Template();
        $template->valid = 1;
        $this->assertFalse($template->isValid());
        $template->templateName = 'Test Template';
        $template->templateId = 3;
        $this->assertEquals('Test Template', $template->templateName);
        $this->assertEquals(3, $template->templateId);

        $ttt = $template->ttt;
        $this->assertFalse($template->isValid());
    }

    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testApprovalEntryGetterSetter() {
        $appEntry = new Application_Model_ApprovalEntry();
        $appEntry->valid = 1;
        $this->assertFalse($appEntry->isValid());
        $appEntry->approvalEntryId = 3;
        $this->assertEquals(3, $appEntry->approvalEntryId);

        $ttt = $appEntry->ttt;
        $this->assertFalse($appEntry->isValid());
    }

    /**
     * @expectedException NonExistingObjectProperty
     */
    public function testResourceGetterSetter() {
        $resource = new Application_Model_Resource();
        $resource->valid = 1;
        $this->assertFalse($resource->isValid());
        $resource->resourceName = 'rName';
        $this->assertEquals('rName', $resource->resourceName);
        $ttt = $resource->ttt;
    }

}

?>
