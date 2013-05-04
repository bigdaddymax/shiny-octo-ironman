<?php

/**
 * Description of DomainTest
 *
 * @author Max
 */

require_once TESTS_PATH . '/application/TestCase.php';
require_once APPLICATION_PATH . '/models/Domain.php';

class DomainTest extends TestCase {
    
    public function testDomainGetterSetter()
    {
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
    
    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testDomainConstructException()
    {
        $domain = new Application_Model_Domain(1);
    }
    
    public function testDomainConstructCorrect()
    {
        $domainArray = array('domainName'=>'eName','domainComment'=>'test', 'active'=>false);
        $domain = new Application_Model_Domain($domainArray);
        $this->assertFalse($domain->active);
        $this->assertEquals('eName', $domain->domainName);
        $this->assertEquals('test', $domain->domainComment);

    }


    public function testDomainValidation()
    {
        $domainArray = array('ele'=>33);
        $domain = new Application_Model_Domain($domainArray);
        $this->assertFalse($domain->isValid());
        $domain->domainName = 'eName';
        $this->assertFalse($domain->isValid());
        $domain->hash = md5(time());
        $this->assertTrue($domain->isValid());
        $this->assertEquals('eName', $domain->domainName);
    }
    
    public function testDomainToArray()
    {
        $domainArray = array('domainName'=>'eName', 'domainComment'=>'test', 'active'=>false);
        $domain = new Application_Model_Domain($domainArray);
        $domainArray2 = $domain->toArray();
        $this->assertEquals($domainArray, $domainArray2);
    }
    
}
