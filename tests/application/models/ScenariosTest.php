<?php

/**
 * Description of ScenarioTest
 *
 * @author Max
 */
require_once TESTS_PATH . '/application/TestCase.php';

class ScenarioTest extends TestCase {

    private $userId;
    private $nodeId;
    private $nodeId1;
    private $nodeId2;
    private $objectsManager;
    private $dataMapper;

    public function setUp() {
        parent::setUp();
        $this->objectsManager = new Application_Model_ObjectsManager();
        $this->dataMapper = new Application_Model_DataMapper();
        $nodeArray = array('nodeName' => 'First node', 'parentNodeId' => -1, 'domainId' => 1);
        $node = new Application_Model_Node($nodeArray);
        $this->nodeId = $this->dataMapper->saveObject($node);
        $nodeArray1 = array('nodeName' => 'Second node', 'parentNodeId' => -1, 'domainId' => 1);
        $node1 = new Application_Model_Node($nodeArray1);
        $this->nodeId1 = $this->dataMapper->saveObject($node1);
        $nodeArray2 = array('nodeName' => 'third node', 'parentNodeId' => -1, 'domainId' => 1);
        $node2 = new Application_Model_Node($nodeArray2);
        $this->nodeId2 = $this->dataMapper->saveObject($node2);
        $positionArray = array('positionName' => 'First position', 'nodeId' => $this->nodeId, 'domainId' => 1);
        $position = new Application_Model_Position($positionArray);
        $positionId = $this->dataMapper->saveObject($position);


        $userArray = array('userId' => 3, 'userName' => 'oName', 'active' => false, 'domainId' => 1, 'login' => 'tLogin', 'positionId' => $positionId, 'groupId' => 2, 'password' => 'testp');
        $user = new Application_Model_User($userArray);
        $this->userId = $this->dataMapper->saveObject($user);
        $session = new Zend_Session_Namespace('Auth');
        $session->domainId = 1;
    }

    public function tearDown() {
        $this->dataMapper->dbLink->delete('scenario_assignment');
        $this->dataMapper->dbLink->delete('scenario_entry');
        $this->dataMapper->dbLink->delete('scenario');
        $this->dataMapper->dbLink->delete('user');
        $this->dataMapper->dbLink->delete('position');
        $this->dataMapper->dbLink->delete('node');

        parent::tearDown();
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

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testScenarioConstructException() {
        $scenario = new Application_Model_Scenario(1);
    }

    public function testScenarioConstructCorrect() {
        $scenarioEntryArray = array('domainId' => 1, 'orderPos' => 1, 'userId' => 1);
        $scenarioEntry = new Application_Model_ScenarioEntry($scenarioEntryArray);
//        $this->assertTrue($scenarioEntry->isValid());
        $scenarioArray = array('scenarioName' => 'eName', 'active' => false, 'domainId' => 5, 'entries' => array(0 => $scenarioEntry));
        $scenario = new Application_Model_Scenario($scenarioArray);
        $this->assertTrue($scenario->isValid());
        $scenarioArray1 = $scenario->toArray();
        unset($scenarioArray['date']);
        unset($scenarioArray1['date']);
        $this->assertEquals($scenarioArray, $scenarioArray1);
    }

    public function testScenarioValidation() {
        $scenarioEntryArray = array('domainId' => 1, 'orderPos' => 1, 'userId' => 1);
        $scenarioEntry = new Application_Model_ScenarioEntry($scenarioEntryArray);
        $scenarioEntryArray1 = array('domainId' => 1, 'orderPos' => 1, 'userId' => 2);
        $scenarioEntry1 = new Application_Model_ScenarioEntry($scenarioEntryArray1);
        $this->assertTrue($scenarioEntry->isValid());
        $scenarioArray = array('scenarioName' => 'pName');
        $scenario = new Application_Model_Scenario($scenarioArray);
        $this->assertFalse($scenario->isValid());
        $scenario->scenarioName = 'eName';
        $this->assertFalse($scenario->isValid());
        $scenario->domainId = 1;
        $this->assertFalse($scenario->isValid());
        $scenario->entries = array(0 => $scenarioEntry, 1 => $scenarioEntry1);
        $this->assertTrue($scenario->isValid());
        $scenario->scenarioId = 1;
        $this->assertTrue($scenario->isValid());
        $this->assertEquals('eName', $scenario->scenarioName);
        $this->assertEquals(1, $scenario->scenarioId);
        $this->assertEquals($scenario->entries, array(0 => $scenarioEntry, 1 => $scenarioEntry1));
        $scenario->entries = $scenarioEntry;
        $this->assertTrue($scenario->isValid());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Cannot create entry from array within Scenario. Entry is not valid
     */
    public function testScenarioNotValidScenarioEntryAssignment() {
        $scenarioEntryArray = array('domainId' => 1, 'userId' => 1);
        $scenarioEntry = new Application_Model_ScenarioEntry($scenarioEntryArray);
        $this->assertFalse($scenarioEntry->isValid());
        $scenarioArray = array('scenarioName' => 'eName', 'active' => false, 'domainId' => 1, 'entries' => array(0 => $scenarioEntry));
        $scenario = new Application_Model_Scenario($scenarioArray);
        $this->assertFalse($scenario->isValid());
    }

    public function testScenarioScenarioEntrysSetingGetting() {
        $scenarioEntryArray = array('domainId' => 1, 'orderPos' => 1, 'userId' => 1);
        $scenarioEntry = new Application_Model_ScenarioEntry($scenarioEntryArray);
        $this->assertTrue($scenarioEntry->isValid());
        $scenarioArray = array('scenarioName' => 'eName', 'active' => false, 'domainId' => 5, 'entries' => array(0 => $scenarioEntry));
        $scenario = new Application_Model_Scenario($scenarioArray);
        $scenarioEntryArray2 = $scenario->entries;
        $this->assertTrue(is_array($scenarioEntryArray2));
        $this->assertTrue($scenarioEntryArray2[0] instanceof Application_Model_ScenarioEntry);
        $this->assertEquals(array(0 => $scenarioEntry), $scenarioEntryArray2);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage One of entries is neither of Application_Model_ScenarioEntry type nor Array().
     */
    public function testScenarioScenarioEntryInvalidAssignment() {
        $scenarioEntry = 77777;
        $scenarioArray = array('scenarioName' => 'eName', 'active' => false, 'domainId' => 5, 'entries' => array(0 => $scenarioEntry));
        $scenario = new Application_Model_Scenario($scenarioArray);
//        Zend_Debug::dump($scenario);
    }

    public function testScenarioToArray() {
        $scenarioArray = array('scenarioName' => 'eName', 'scenarioId' => 3, 'active' => false, 'domainId' => 5);
        $scenario = new Application_Model_Scenario($scenarioArray);
        $scenarioArray2 = $scenario->toArray();
        unset($scenarioArray['date']);
        unset($scenarioArray2['date']);
        $this->assertEquals($scenarioArray, $scenarioArray2);
    }

    public function testScenarioSave() {
        $scenarioEntryArray = array('domainId' => 1, 'orderPos' => 1, 'userId' => $this->userId);
        $scenarioEntry = new Application_Model_ScenarioEntry($scenarioEntryArray);
        $this->assertTrue($scenarioEntry->isValid());
        $scenarioArray = array('scenarioName' => 'eName', 'active' => false, 'domainId' => 1, 'entries' => array(0 => $scenarioEntry));
        $scenario = new Application_Model_Scenario($scenarioArray);
        $this->assertTrue($scenario->isValid());
        $scenarioId = $this->objectsManager->saveScenario($scenario);
        $this->assertTrue(is_int($scenarioId));
        $scenarioGot = $this->objectsManager->getScenario($scenarioId);
        $scenario = new Application_Model_Scenario($scenarioArray);
        $scenario->scenarioId = $scenarioId;
        $entries = $scenario->entries;
        $entries[0]->scenarioEntryId = $scenarioGot->entries[0]->scenarioEntryId;
        $scenario->entries = $entries;
        $this->assertEquals($scenario, $scenarioGot);
    }

    
    public function testGetAllScenarios(){
        $scenarioEntryArray = array('domainId' => 1, 'orderPos' => 1, 'userId' => $this->userId);
        $scenarioEntry = new Application_Model_ScenarioEntry($scenarioEntryArray);
        $this->assertTrue($scenarioEntry->isValid());
        $scenarioArray = array('scenarioName' => 'eName1', 'active' => false, 'domainId' => 1, 'entries' => array(0 => $scenarioEntry));
        $scenario = new Application_Model_Scenario($scenarioArray);
        $scenarioId = $this->objectsManager->saveScenario($scenario);
        $scenarioEntryArray1 = array('domainId' => 1, 'orderPos' => 1, 'userId' => $this->userId);
        $scenarioEntry1 = new Application_Model_ScenarioEntry($scenarioEntryArray1);
        $this->assertTrue($scenarioEntry1->isValid());
        $scenarioArray1 = array('scenarioName' => 'eName2', 'active' => false, 'domainId' => 1, 'entries' => array(0 => $scenarioEntry1));
        $scenario1 = new Application_Model_Scenario($scenarioArray1);
        $scenarioId1 = $this->objectsManager->saveScenario($scenario1);
        $scenarios = $this->objectsManager->getAllScenarios();
        $scenario2 = $this->objectsManager->getScenario($scenarioId);
        $scenario3 = $this->objectsManager->getScenario($scenarioId1);
        $this->assertEquals(array(0=>$scenario2, 1=>$scenario3), $scenarios);
    }
    
    public function testScenarioAssignmentCreation(){
        $scenarioEntryArray = array('domainId' => 1, 'orderPos' => 1, 'userId' => $this->userId);
        $scenarioEntry = new Application_Model_ScenarioEntry($scenarioEntryArray);
        $this->assertTrue($scenarioEntry->isValid());
        $scenarioArray = array('scenarioName' => 'eName1', 'active' => false, 'domainId' => 1, 'entries' => array(0 => $scenarioEntry));
        $scenario = new Application_Model_Scenario($scenarioArray);
        $scenarioId = $this->objectsManager->saveScenario($scenario);
        $assignmentArray = array('domainId'=>1, 'nodeId'=>$this->nodeId, 'scenarioId'=>$scenarioId);
        $assignment = new Application_Model_ScenarioAssignment($assignmentArray);
        $assignmentId = $this->dataMapper->saveObject($assignment);
        $this->assertTrue(is_int($assignmentId));
    }
    
    public function testGetNodesAssigned(){
        $scenarioEntryArray = array('domainId' => 1, 'orderPos' => 1, 'userId' => $this->userId);
        $scenarioEntry = new Application_Model_ScenarioEntry($scenarioEntryArray);
        $this->assertTrue($scenarioEntry->isValid());
        $scenarioArray = array('scenarioName' => 'eName1', 'active' => false, 'domainId' => 1, 'entries' => array(0 => $scenarioEntry));
        $scenario = new Application_Model_Scenario($scenarioArray);
        $scenarioId = $this->objectsManager->saveScenario($scenario);
        $assignmentArray = array('domainId'=>1, 'nodeId'=>$this->nodeId, 'scenarioId'=>$scenarioId);
        $assignment = new Application_Model_ScenarioAssignment($assignmentArray);
        $assignmentId = $this->dataMapper->saveObject($assignment);
        $this->assertTrue(is_int($assignmentId));
        $assignmentArray1 = array('domainId'=>1, 'nodeId'=>$this->nodeId1, 'scenarioId'=>$scenarioId);
        $assignment1 = new Application_Model_ScenarioAssignment($assignmentArray1);
        $assignmentId1 = $this->dataMapper->saveObject($assignment1);
        $assignmentArray2 = array('domainId'=>1, 'nodeId'=>$this->nodeId2, 'scenarioId'=>$scenarioId);
        $assignment2 = new Application_Model_ScenarioAssignment($assignmentArray2);
        $assignmentId2 = $this->dataMapper->saveObject($assignment2);
        $assignedNodes = $this->objectsManager->getNodesAssigned();
        Zend_Debug::dump($assignedNodes);
        $node = $this->dataMapper->getObject($this->nodeId, 'Application_Model_Node');
        $node1 = $this->dataMapper->getObject($this->nodeId1, 'Application_Model_Node');
        $node2 = $this->dataMapper->getObject($this->nodeId2, 'Application_Model_Node');
        $assignedNodesEx = array($scenarioId=>array( 0=> array('nodeId'=> (string) $this->nodeId, 'scenarioName'=>'eName1','nodeName'=>$node->nodeName),
                                 1=> array('nodeId'=> (string) $this->nodeId1, 'scenarioName'=>'eName1','nodeName'=>$node1->nodeName),
                                 2=> array('nodeId'=> (string) $this->nodeId2, 'scenarioName'=>'eName1','nodeName'=>$node2->nodeName)));
        $this->assertEquals($assignedNodes, $assignedNodesEx);
    }
}
