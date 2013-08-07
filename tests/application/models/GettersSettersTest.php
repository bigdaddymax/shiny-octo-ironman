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

    public function testNodeGetterAndSetter() {
        $nodeArray = array('parentNodeId' => 3);
        $node = new Application_Model_Node($nodeArray);
        $node->nodeName = 'lName';
        $this->assertEquals('lName', $node->nodeName);
        $node->parentNodeId = null;
        $this->assertEquals(null, $node->parentNodeId);

        $this->expectOutputString('Cannot set value. Property ttt doesnt exist');
        $node->ttt = 'Test';

        $ttt = $node->ttt;
        $this->assertEquals('Cannot get value. Property ttt doesnt exist', $ttt);
        ob_clean();
        $this->expectOutputString('Cannot set value for "valid" property');
        $node->valid = 2;
    }

    public function testPositionGetterSetter() {
        $position = new Application_Model_Position();
        $position->positionName = 'eName';
        $this->expectOutputString('Cant set value. Property positionStatus doesnt exist');
        $position->positionStatus = 'status';
        ob_clean();
//        $position->state = 'state';
//        $position->valid1 =4;
        $test = $position->positionState;
        $this->assertEquals('Cannot get value. Property positionState doesnt exist', $test);
        $this->expectOutputString('Cannot set value for "valid" property');
        $position->valid = 1;
    }

    public function testElementGetterSetter() {
        $element = new Application_Model_Element();
        $element->elementName = 'eName';
        $this->expectOutputString('Cant set value. Property elementStatus doesnt exist');
        $element->elementStatus = 'status';
        ob_clean();
//        $element->state = 'state';
//        $element->valid1 =4;
        $test = $element->elementState;
        $this->assertEquals('Cannot get value. Property elementState doesnt exist', $test);
        $this->expectOutputString('Cannot set value for "valid" property');
        $element->valid = 1;
    }

    public function testUserGetterAndSetter() {
        $userArray = array('parentLevelId' => 3);
        $user = new Application_Model_User($userArray);
        $user->userName = 'oName';
        $this->assertEquals('oName', $user->userName);

        $this->expectOutputString('Cannot set value. Property ttt doesnt exist');
        $user->ttt = 'Test';

        $ttt = $user->ttt;
        $this->assertEquals('Cannot get value. Property ttt doesnt exist', $ttt);
        ob_clean();
        $this->expectOutputString('Cannot set value for "valid" property');
        $user->valid = 2;
    }

    public function testPrivilegeGetterAndSetter() {
        $privilegeArray = array('userId' => 3);
        $privilege = new Application_Model_privilege($privilegeArray);
        $privilege->objectId = 2;
        $this->assertEquals(2, $privilege->objectId);

        $this->expectOutputString('Cannot set value. Property ttt doesnt exist');
        $privilege->ttt = 'Test';

        $ttt = $privilege->ttt;
        $this->assertEquals('Cannot get value. Property ttt doesnt exist', $ttt);
        ob_clean();
        $this->expectOutputString('Cannot set value for "valid" property');
        $privilege->valid = 2;
    }

    public function testDomainGetterSetter() {
        $domain = new Application_Model_Domain();
        $domain->domainName = 'dName';
        $this->expectOutputString('Cant set value. Property domainStatus doesnt exist');
        $domain->domainStatus = 'status';
        ob_clean();
//        $domain->state = 'state';
//        $domain->valid1 =4;
        $test = $domain->domainState;
        $this->assertEquals('Cannot get value. Property domainState doesnt exist', $test);
        $this->expectOutputString('Cannot set value for "valid" property');
        $domain->valid = 1;
    }

    public function testContragentGetterSetter() {
        $contragent = new Application_Model_Contragent();
        $contragent->contragentName = 'eName';
        $this->expectOutputString('Cant set value. Property contragentStatus doesnt exist');
        $contragent->contragentStatus = 'status';
        ob_clean();
//        $contragent->state = 'state';
//        $contragent->valid1 =4;
        $test = $contragent->contragentState;
        $this->assertEquals('Cannot get value. Property contragentState doesnt exist', $test);
        $this->expectOutputString('Cannot set value for "valid" property');
        $contragent->valid = 1;
    }

    public function testScenarioGetterSetter() {
        $scenario = new Application_Model_Scenario();
        $scenario->scenarioName = 'eName';
        $this->expectOutputString('Cant set value. Property scenarioStatus doesnt exist');
        $scenario->scenarioStatus = 'status';
        ob_clean();
//        $scenario->state = 'state';
//        $scenario->valid1 =4;
        $test = $scenario->scenarioState;
        $this->assertEquals('Cannot get value. Property scenarioState doesnt exist', $test);
        $this->expectOutputString('Cannot set value for "valid" property');
        $scenario->valid = 1;
    }

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
    public function testCommentGetterSetter() {
        $comment = new Application_Model_Comment();
        $comment->text = 'test test test test';
        $this->expectOutputString('Cant set value. Property commentStatus doesnt exist');
        $comment->commentStatus = 'status';
        ob_clean();
        $test = $comment->commentState;
        $this->assertEquals('Cannot get value. Property commentState doesnt exist', $test);
        $this->expectOutputString('Cannot set value for "valid" property');
        $comment->valid = 1;
    }

    public function testTemplateGetterSetter() {
        $template = new Application_Model_Template();
        $template->templateName = 'Test Template';
        $template->templateId  = 3;
        $this->assertEquals('Test Template', $template->templateName);
        $this->assertEquals(3, $template->templateId);
        $this->expectOutputString('Cannot set value for "valid" property');
        $template->valid = 1;
    }
}

?>
