<?php

/**
 * Description of DomainTest
 *
 * @author Max
 */

require_once TESTS_PATH . '/application/TestCase.php';
require_once APPLICATION_PATH . '/models/Domain.php';

class DomainTest extends TestCase {
    
     
    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testDomainConstructException()
    {
        $domain = new Application_Model_Domain(1);
    }
    
    public function testDomainConstructCorrect()
    {
        $domainArray = array('domainName'=>'eName','domainComment'=>'test', 'active'=>0);
        $domain = new Application_Model_Domain($domainArray);
        $this->assertEquals($domain->active, 0);
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
        $domainArray = array('domainName'=>'eName', 'domainComment'=>'test', 'active'=>0);
        $domain = new Application_Model_Domain($domainArray);
        $domainArray2 = $domain->toArray();
        $this->assertEquals($domainArray, $domainArray2);
    }
    
}
