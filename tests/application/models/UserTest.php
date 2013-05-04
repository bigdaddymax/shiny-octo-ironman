<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserTest
 *
 * @author Max
 */
class UserTest extends TestCase {

    
/**
 * 
 * @group user
 */    
    public function testUserConstructor()
    {
        $UserArray = array('userName'=>'oName',  'active' => false, 'userId' =>5);
        $User = new Application_Model_User($UserArray);
        $this->assertTrue($User instanceof Application_Model_User);
        $this->assertEquals($User->userName, 'oName');
        $this->assertEquals($User->active, false);
        $this->assertEquals($User->userId, 5);
    }
    
/**
 * 
 * @group user
 */    
    public function testUserGetterAndSetter()
    {
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
    
    
/**
 * 
 * @group user
 */    
    public function testUserToArray()
    {
        $userArray = array('userId' => 3, 'userName' => 'oName',  'active' => false, 'domainId' => 4, 'login' => 'tLogin', 'positionId' => 4, 'password' => 'testp');
        $user = new Application_Model_User($userArray);
        $userArray2 = $user->toArray();
        $this->assertEquals($userArray, $userArray2);
    }
    
/**
 * 
 * @group user
 */    
    public function testObjectValidation()
    {
        $userArray = array('userName' => 'oName', 'active' => true, 'userId' => 1);
        $user = new Application_Model_User($userArray);
        $this->assertFalse($user->isValid());
        $userArray2 = $user->toArray();
//        unset($userArray2['userId']);
        
        $this->assertEquals($userArray, $userArray2);
        $user->domainId = 4;
        $user->password = 'tetet';
        $user->login = 'ttt';
        $user->positionId = 5;
        $this->assertTrue($user->isValid());
        $userArray3 = $user->toArray();
        $userArray['domainId'] = 4;
        $userArray['positionId'] = 5;
        $userArray['login'] = 'ttt';
        $userArray['password'] = 'tetet';
        $this->assertEquals($userArray, $userArray3);
    }
}

?>
