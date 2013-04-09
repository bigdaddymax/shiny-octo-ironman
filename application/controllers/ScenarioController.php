<?php

/**
 * Description of ScenarioController
 * Manage approval scenarios
 * Why we need separate controller? Good question. Lets try this way, refactor later.
 * 
 * @author Olenka
 */

class ScenarioController extends Zend_Controller_Action {

    private $session;
    private $redirector;

    public function init() {
        $this->session = new Zend_Session_Namespace('Auth');
        $this->redirector = $this->_helper->getHelper('Redirector');
    }

/**
 * Index action. Here we output existing scenarios as a list. For this we pass to view 
 * following variables: $scenarios, $scenarioAssignments.
 * 
 * From Index page user has also access to creating new scenario. For this we pass
 * to view list of users in $users variable
 * 
 */    
    public function indexAction() {
        $objectsManager = new Application_Model_ObjectsManager();
        $dataMapper = new Application_Model_DataMapper();
        $this->view->scenarios = $objectsManager->getAllScenarios();
        $this->view->users = $dataMapper->getAllObjects('Application_Model_User');
        $this->view->assignments = $objectsManager->getNodesAssigned();
    }

/**
 * New scenario creation.
 * Input data have following form: scenarioName : 'Name', order_4354 : 1, order_4378 : 2, order_2231 : 3
 * Here scenarioName is scenario name; {order_4354 : 1} means order position for particular user
 * 4354 is userId and 1 is user's order in approval list
 */    
    public function addScenarioAction() {
        $params = $this->getRequest()->getPost();
        $params['domainId'] = $this->session->domainId;
        $scenario = new Application_Model_Scenario($params);
        if ($scenario->isValid()) {
            $objectsManager = new Application_Model_ObjectsManager();
            $this->view->newScenarioId = $objectsManager->SaveScenario($scenario);
        } else {
            $this->view->error = 'Cannot create form';
            $this->view->scenario = $scenario;
        }
//        $this->redirector->gotoSimple('index', 'scenario');
    }

    public function openScenarioAction() {
        if ($this->_request->isGet()) {
            $scenarioId = $this->getRequest()->getParam('scenarioId');
            $objectManager = new Application_Model_ObjectsManager();
            $dataMapper = new Application_Model_DataMapper();
            $this->view->assignments = $objectManager->getNodesAssigned();
            $this->view->scenario = $objectManager->getScenario($scenarioId);
        }
    }
    
    public function deleteScenarioAction(){
        $scenarioId = $this->_request->getParam('scenarioId');
        $objectsManager = new Application_Model_ObjectsManager();
        Zend_Debug::dump($scenarioId);
        $objectsManager->deleteScenario($scenarioId);
    }
}

?>
