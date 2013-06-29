<?php

/**
 * Description of CommentTest
 *
 * @author Max
 */
require_once TESTS_PATH . '/application/TestCase.php';

class CommentTest extends TestCase {

    public $objectManager;
    public $userId;
    public $formId;
    public $nodeId;
    public $elementId1;
    public $elementId2;

    public function setUp() {
        parent::setUp();
        $this->objectManager = new Application_Model_ObjectsManager(1);
        $this->objectManager->dbLink->delete('user');
        $this->objectManager->dbLink->delete('position');
        $this->objectManager->dbLink->delete('node');
        $nodeArray = array('nodeName' => 'First node', 'parentNodeId' => -1, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $this->nodeId = $this->objectManager->saveObject($node);
        $nodeArray1 = array('nodeName' => 'Second node', 'parentNodeId' => -1, 'domainId' => 1);
        $node1 = new Application_Model_Node($nodeArray1);
        $this->nodeId1 = $this->objectManager->saveObject($node1);
        $nodeArray2 = array('nodeName' => 'third node', 'parentNodeId' => -1, 'domainId' => 1);
        $node2 = new Application_Model_Node($nodeArray2);
        $this->nodeId2 = $this->objectManager->saveObject($node2);
        $positionArray = array('positionName' => 'First position', 'nodeId' => $this->nodeId, 'domainId' => 1);
        $position = new Application_Model_Position($positionArray);
        $positionId = $this->objectManager->saveObject($position);


        $userArray = array('userId' => 3, 'userName' => 'oName', 'active' => false, 'domainId' => 1, 'login' => 'tLogin', 'positionId' => $positionId, 'groupId' => 2, 'password' => 'testp');
        $user = new Application_Model_User($userArray);
        $this->assertTrue($user->isValid());
        $this->userId = $this->objectManager->saveObject($user);

        //ELEMENT
        $elementArray = array('elementName' => 'eName', 'domainId' => 1, 'elementCode' => 34, 'expgroup' => 'OPEX');
        $element = new Application_Model_Element($elementArray);
        $this->assertTrue($element->isValid());
        $this->elementId1 = $this->objectManager->saveObject($element);
        $elementArray1 = array('elementName' => 'eName1', 'domainId' => 1, 'elementCode' => 44, 'expgroup' => 'OPEX');
        $element1 = new Application_Model_Element($elementArray1);
        $this->assertTrue($element1->isValid());
        $this->elementId2 = $this->objectManager->saveObject($element1);

        // CONTRAGENT
        $contragentArray = array('contragentName' => 'cName', 'domainId' => 1);
        $contragent = new Application_Model_Contragent($contragentArray);
        $this->assertTrue($contragent->isValid());
        $this->contragentId = $this->objectManager->saveObject($contragent);
        $this->assertTrue($contragent instanceof Application_Model_Contragent);
        $this->assertTrue(is_int($this->contragentId));

        // FORM
        $itemArray1 = array('itemName' => 'item1', 'domainId' => 1, 'value' => 55.4, 'elementId' => $this->elementId1, 'active' => true);
        $itemArray2 = array('itemName' => 'item2', 'domainId' => 1, 'value' => 22.1, 'elementId' => $this->elementId2, 'active' => true);
        $formArray1 = array('userId' => $this->userId1, 'formName' => 'fName1', 'nodeId' => $this->nodeId, 'items' => array(0 => $itemArray1, 1 => $itemArray2), 'domainId' => 1, 'active' => true, 'contragentId' => $this->contragentId, 'expgroup' => 'CAPEX');
        $form = new Application_Model_Form($formArray1);
//        Zend_Debug::dump($form);
        $this->assertTrue($form->isValid());
        $this->formId = $this->objectManager->saveObject($form);
    }

    public function tearDown() {
        $this->objectManager = new Application_Model_ObjectsManager(1);
        $this->objectManager->dbLink->delete('user');
        $this->objectManager->dbLink->delete('position');
        $this->objectManager->dbLink->delete('node');

        parent::tearDown();
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testCommentConstructException() {
        $comment = new Application_Model_Comment(1);
    }

    public function testCommentConstructCorrect() {
        $commentArray = array('comment' => 'eName', 'parentCommentId' => -1, 'commentId' => 3, 'active' => 0, 'userId' => 3, 'formId' => 2, 'domainId' => 5, 'date' => date('%d-%m-%Y'));
        $comment = new Application_Model_Comment($commentArray);
        $this->assertTrue($comment->isValid());
        $commentArray1 = $comment->toArray();
        unset($commentArray['date']);
        unset($commentArray1['date']);
        $this->assertEquals($commentArray, $commentArray1);
    }

    public function testCommentValidation() {
        $commentArray = array('commentName' => 'pName');
        $comment = new Application_Model_Comment($commentArray);
        $this->assertFalse($comment->isValid());
        $comment->comment = 'eName';
        $this->assertFalse($comment->isValid());
        $comment->domainId = 3;
        $this->assertFalse($comment->isValid());
        $comment->userId = 3;
        $this->assertFalse($comment->isValid());
        $comment->date = date('%m%d%Y');
        $this->assertFalse($comment->isValid());
        $comment->commentId = 1;
        $this->assertFalse($comment->isValid());
        $comment->parentCommentId = -1;
        $this->assertFalse($comment->isValid());
        $comment->formId = 1;
        $this->assertTrue($comment->isValid());
        $this->assertEquals('eName', $comment->comment);
        $this->assertEquals(1, $comment->commentId);
    }

    public function testCommentToArray() {
        $commentArray = array('comment' => 'eName', 'commentId' => 3, 'active' => 0, 'domainId' => 5);
        $comment = new Application_Model_Comment($commentArray);
        $commentArray2 = $comment->toArray();
        $this->assertEquals($commentArray, $commentArray2);
    }

    public function testSaveComment() {
        $objectManager = new Application_Model_ObjectsManager(5);
        $commentArray = array('comment' => 'eName', 'parentCommentId' => -1, 'commentId' => 3, 'active' => 0, 'userId' => $this->userId, 'formId' => $this->formId, 'domainId' => 1, 'date' => date('Y-m-d H:i:s'));
        $comment = new Application_Model_Comment($commentArray);
        $this->assertTrue($comment instanceof Application_Model_Comment);
        $this->assertTrue($comment->isValid());
        $id = $objectManager->saveObject($comment);
        $this->assertTrue(is_int($id));
    }

}
