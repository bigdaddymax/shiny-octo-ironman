<?php

/**
 * Description of CommentTest
 *
 * @author Max
 */
require_once TESTS_PATH . '/application/TestCase.php';

class CommentTest extends TestCase {


    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testCommentConstructException() {
        $comment = new Application_Model_Comment(1);
    }

    public function testCommentConstructCorrect() {
        $commentArray = array('comment' => 'eName', 'parentCommentId'=>-1,'commentId' => 3, 'active' => 0, 'userId'=>3, 'formId'=>2, 'domainId' => 5, 'date'=>date('%d-%m-%Y'));
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
        $commentArray = array('comment' => 'eName', 'commentId' => 3,'active' => 0, 'domainId' => 5);
        $comment = new Application_Model_Comment($commentArray);
        $commentArray2 = $comment->toArray();
         $this->assertEquals($commentArray, $commentArray2);
    }
    
    public function testSaveComment(){
        $objectManager = new Application_Model_ObjectsManager(5);
        $commentArray = array('comment' => 'eName', 'parentCommentId'=>-1,'commentId' => 3, 'active' => 0, 'userId'=>3, 'formId'=>2, 'domainId' => 5, 'date'=>date('%d-%m-%Y'));
        $comment = new Application_Model_Comment($commentArray);
        $this->assertTrue($comment instanceof Application_Model_Comment);
        $this->assertTrue($comment->isValid());
        $id = $objectManager->saveObject($comment);
        $this->assertTrue(is_int($id));
    }
}
