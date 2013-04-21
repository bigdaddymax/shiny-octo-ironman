<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AuthTest
 *
 * @author Olenka
 */
class AuthTest extends TestCase {

    //put your code here

    private $login;
    private $dataMapper;

    public function setup() {
        $this->dataMapper = new Application_Model_DataMapper(1);
        $nodeArray = array('nodeName' => 'First node', 'parentNodeId' => -1, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $nodeId = $this->dataMapper->saveObject($node);
        $positionArray = array('positionName' => 'First position', 'nodeId' => $nodeId, 'domainId' => 1);
        $position = new Application_Model_Position($positionArray);
        $positionId = $this->dataMapper->saveObject($position);
        $userArray = array('userName' => 'user1', 'domainId' => 1, 'login' => 'user login', 'password' => 'user password', 'positionId' => $positionId);
        $user = new Application_Model_User($userArray);
        $userId = $this->dataMapper->saveObject($user);
        $this->login = $user->login;
        $this->assertTrue(is_int($userId));
    }

    public function testCheckPassword() {
        $auth = new Application_Model_Auth();
        $this->assertFalse($auth->checkUserPassword($this->login, 'yyyyyyyy'));
        $this->assertFalse($auth->checkUserPassword($this->login, 'testpp'));
        $this->assertTrue($auth->checkUserPassword($this->login, 'user password'));
    }

}

?>
