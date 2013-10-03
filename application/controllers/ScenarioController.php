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
    private $objectsManager;
    private $form;

    public function init() {
        $this->session = new Zend_Session_Namespace('Auth');
        $this->redirector = $this->_helper->getHelper('Redirector');
        $this->objectsManager = new Application_Model_ObjectsManager($this->session->domainId);
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
        $this->view->scenarios = $this->objectsManager->getAllObjects('scenario');
        $this->view->users = $this->objectsManager->getAllObjects('User');
        $this->view->assignments = $this->objectsManager->getNodesAssigned();
    }

    /**
     * New scenario creation.
     * Input data have following form: scenarioName : 'Name', order_4354 : 1, order_4378 : 2, order_2231 : 3
     * Here scenarioName is scenario name; {order_4354 : 1} means order position for particular user
     * 4354 is userId and 1 is user's order in approval list
     */
    public function editScenarioAction() {
        $scenarioId = $this->_request->getParam('scenarioId');
        $scenario = NULL;
        if (!empty($scenarioId)) {
            $scenario = $this->objectsManager->getObject('scenario', $scenarioId);
        }

        $this->form = new Application_Form_NewScenario(array(
            'users' => $this->objectsManager->getAllObjects('user'),
            'scenario' => $scenario
                )
        );
        $this->view->form = $this->form;

        if ($this->_request->isPost() && $this->view->form->isValid($this->_request->getParams())) {
            $scenario = new Application_Model_Scenario($this->_request->getParams());
            //           try {
            $this->objectsManager->saveObject($scenario);
            //           } catch (SaveObjectException $e) {
            //           }
            $this->_forward('index');
        }
    }

    public function addScenarioAction() {
        $this->_forward('edit-scenario');
    }

    public function openScenarioAction() {
        if ($this->_request->isGet()) {
            $scenarioId = $this->getRequest()->getParam('scenarioId');
            $this->view->assignments = $this->objectsManager->getNodesAssigned();
            $this->view->scenario = $this->objectsManager->getObject('scenario', $scenarioId);
            foreach ($this->view->scenario->entries as $entry) {
                $scenarioEntry[$entry->orderPos]['user'] = $this->objectsManager->getObject('user', $entry->userId);
            }
            $this->view->entries = $scenarioEntry;
        }
    }

    public function deleteScenarioAction() {
        $scenarioId = $this->_request->getParam('scenarioId');
        $this->objectsManager->deleteObject('scenario',$scenarioId);
    }

    public function saveScenarioAction() {
        $params = $this->_request->getParams();
        $params['domainId'] = $this->session->domainId;
        $scenario = new Application_Model_Scenario($params);
        $this->objectsManager->saveObject($scenario);
        $this->redirector->gotoSimple('index', 'scenario');
    }

}

?>
