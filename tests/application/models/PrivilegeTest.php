<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of privilegeTest
 *
 * @author Max
 */
require_once TESTS_PATH . '/application/TestCase.php';

class PrivilegeTest extends TestCase {

    public function testPrivilegeConstructor()
    {
        $privilegeArray = array('userId'=>2,  'active' => 0, 'objectId' =>5, 'privileges'=>'read');
        $privilege = new Application_Model_privilege($privilegeArray);
        $this->assertTrue($privilege instanceof Application_Model_privilege);
        $this->assertEquals($privilege->userId, 2);
        $this->assertEquals($privilege->active, 0);
        $this->assertEquals($privilege->objectId, 5);
    }
    
   
    
    public function testprivilegeToArray()
    {
        $privilegeArray = array('privilegeId' => 3, 'userId' => 2,  'active' => 0, 'domainId' => 4, 'objectId' => 6, 'privilege' => 'read', 'objectType' => 'level');
        $privilege = new Application_Model_privilege($privilegeArray);
        $privilegeArray2 = $privilege->toArray();
        $this->assertEquals($privilegeArray, $privilegeArray2);
    }
    
    public function testObjectValidation()
    {
        $privilegeArray = array('userId' => 2, 'active' => 1, 'privilegeId' => 1);
        $privilege = new Application_Model_privilege($privilegeArray);
        $this->assertFalse($privilege->isValid());
        $privilegeArray2 = $privilege->toArray();
//        unset($privilegeArray2['privilegeId']);
        
        $this->assertEquals($privilegeArray, $privilegeArray2);
        $privilege->domainId = 4;
        $privilege->objectId = 2;
        $privilege->privilege = 'approve';
        $privilege->objectType = 'level';
        $this->assertTrue($privilege->isValid());
        $privilegeArray3 = $privilege->toArray();
        $privilegeArray['domainId'] = 4;
        $privilegeArray['objectId'] = 2;
        $privilegeArray['privilege'] = 'approve';
        $privilegeArray['objectType'] = 'level';
        $this->assertEquals($privilegeArray, $privilegeArray3);
    }
}

?>
