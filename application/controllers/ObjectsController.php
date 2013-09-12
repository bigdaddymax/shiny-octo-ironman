<?php

/**
 * Description of ObjectController
 * Takes care of objects with common structure (Node, Element, Position, Item, User)
 * Controller allows to create new objects, show list of existing ones, delete objects
 * and edit objects.
 * 
 * Controller allows also to deal with specific object Privilege via special actions:
 * userPrivilegesAction() and editPrivilegesAction()
 * 
 * * @author Max
 */
class ObjectsController extends Zend_Controller_Action {

    private $objectManager;
    private $tableName;
    private $redirector;
    private $className;
    private $objectIdName;
    private $objectName;
    private $subobjects;
    private $session;
    private $params;
    private $config;
    private $form;

    /**
     * init() performs all preparations needed to complete requested action. Its main task
     * is to determine which object we are dealing with and prepare necessery variables
     * and/or include correct files
     */
    public function init() {
        // Ugly workaround about strange reaction of JQuery on input elements with
        // name = "nodeName". Nevertheless "nodeName" is not reserved word JQuery throws
        // error that some element doesnt have HTMLToLower() method.
        // So all risky input field names are prefixed with underscore (_)
        // To maintain code consistancy before we proceed with received parameters
        // we remove underscore char from the begining of every parameter name
        // If parameter doesnt have undescore before its name we pass it without changes
        if (!function_exists('stripUnderscore')) {

            function stripUnderscore(&$value, $key) {
                $value = ltrim($value, '_');
            }

        }
        $this->config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        $this->session = new Zend_Session_Namespace('Auth');
        $params = $this->getRequest()->getParams();
        foreach ($params as $key => $param) {
            if (!$param) {
//                $param = -1;
            }
            $prms[$key] = $param;
        }
        if (!empty($prms)) {
            $keys = array_keys($prms);
            $values = array_values($prms);
            //       $keys = array_keys($params);
            array_walk($keys, 'stripUnderscore');
            $this->params = array_combine($keys, $values);
        }
        //+++++++++++++++++++++++++++ END OF WORKAROUND ++++++++++++++++++++++++++++++++++++

        $this->objectManager = new Application_Model_ObjectsManager($this->session->domainId);
        $this->redirector = $this->_helper->getHelper('Redirector');
        switch ($this->_request->getParam('objectType')) {
            case 'node':
                $this->form = new Application_Form_NewNode(array('nodes' => $this->objectManager->getAllObjects('node')));
                break;
            case 'element':
                $this->form = new Application_Form_NewElement(array('expgroup' => $this->config->expences->group));
                break;
            case 'position':
                $this->form = new Application_Form_NewPosition($this->objectManager->getAllObjects('Node'));
                break;
            case 'user':
                $this->form = new Application_Form_NewUser(array('position' => $this->objectManager->getAllObjects('Position')));
                break;
            case 'template':
                $this->form = new Application_Form_NewTemplate(array('templates' => $this->objectManager->getAllObjects('template'),
                            'types' => $this->config->template->types,
                            'locales' => $this->config->app->locales));
                break;
        }
        $this->objectName = strtolower($this->_request->getParam('objectType'));
        $this->setClassAndTableName($this->_request->getParam('objectType'));
        $this->params['domainId'] = $this->session->domainId;
    }

    /**
     * Kinda helper, takes object name and convert it to according table name, class name etc
     * @param string $object
     */
    private function setClassAndTableName($object) {
        $this->className = 'Application_Model_' . ucfirst($object);
        $this->tableName = $this->objectName;
        $this->objectIdName = $this->objectName . 'Id';
        $this->view->objectName = $this->objectName;
        $this->view->objectIdName = $this->objectIdName;
    }

    public function indexAction() {
        $this->view->objects = $this->objectManager->getAllObjects($this->objectName);
    }

    /**
     * Action that is invoked when user clicks submit button on Add object form
     */
    public function addObjectAction() {
        $this->form->setAction($this->view->url(array('controller' => 'objects', 'action' => 'add-object')));

        $this->view->form = $this->form;
        if ($this->_request->isPost()) {
            if ($this->form->isValid($this->_request->getParams())) {
                $params = $this->_request->getParams();
                if (isset($params['password'])) {
                    $auth = new Application_Model_Auth();
                    $params['password'] = $auth->hashPassword($params['password']);
                }
                $this->objectManager->saveObject($params);
                $this->_forward('index', 'objects', null, array('objectType' => $params['objectType']));
            }
        }
    }

    public function deleteAction() {
        $objectId = (int) $this->_request->getParam('id');
        try {
            $this->objectManager->deleteObject($this->objectName, $objectId);
            $this->redirector->gotoSimple('index', 'objects', null, array('objectType' => $this->objectName));
        } catch (DependantObjectDeletionAttempt $e) {
            // We catch only DependantObjectDeletionAttempt. All other (?) exceptions will be handled default way
            $this->_helper->json($e->errorToArray());
        }
    }

    public function openObjectAction() {
        $objectId = (int) $this->params[$this->objectIdName];
        $this->form->setAction($this->view->url(array('controller' => 'objects', 'action' => 'add-object')));
        switch ($this->_request->getParam('objectType')) {
            case 'node':
                $node = $this->objectManager->getObject($this->_request->getParam('objectType'), $objectId);
                $scenarios = $this->objectManager->getAllObjects('scenario');
                $assignment = $this->objectManager->getAllObjects('ScenarioAssignment', array(0 => array('column' => 'nodeId', 'operand' => $objectId)));
                $this->view->objects = array(
                    'node' => $node,
                    'scenarios' => $scenarios,
                    'assignment' => ($assignment) ? $assignment[0] : NULL);
                $this->form->setDefaults(array(
                    'nodeName'=>$node->nodeName,
                    'parentNodeId'=>$node->parentNodeId
                ));
                $nodeId = $this->form->createElement('hidden', 'nodeId');
                $nodeId->setValue($node->nodeId);
                $this->form->addElement($nodeId);
                $this->view->form = $this->form;
                $this->view->partialFile = 'open-node.phtml';
                break;
            case 'element':
                $element = $this->objectManager->getObject($this->_request->getParam('objectType'), $objectId);
                $this->form->setDefaults(array('elementName' => $element->elementName,
                    'expgroup' => $element->expgroup
                ));
                $this->view->form = $this->form;
                $this->view->partialFile = 'open-element.phtml';
                break;
            case 'position':
                $position = $this->objectManager->getObject('position', $objectId);
                $this->form->setDefaults(array(
                    'positionName' => $position->positionName,
                    'nodeId' => $position->nodeId
                ));
                $positionId = $this->form->createElement('hidden', 'positionId');
                $positionId->setValue($position->positionId);
                $this->form->addElement($positionId);
                $this->view->form = $this->form;
                $this->view->partialFile = 'open-position.phtml';
                break;
            case 'user':
                $user = $this->objectManager->getObject('user', $this->_request->getParam('userId'));
                $this->form->setDefaults(array(
                    'userName' => $user->userName,
                    'login' => $user->login,
                    'positionId' => $user->positionId
                ));
                $userId = $this->form->createElement('hidden', 'userId');
                $this->form->addElement($userId);
                $this->view->form = $this->form;
                $this->view->partialFile = 'open-user.phtml';
                break;
        }
    }

    public function editObjectAction() {
        $this->objectManager->saveObject($this->params);
        $objectId = (int) $this->params[$this->objectIdName];
        $result = array();
        switch ($this->objectName) {
            case 'node':
                $scenarioAssignment = $this->objectManager->getAllObjects('ScenarioAssignment', array(0 => array('column' => 'nodeId', 'operand' => $objectId)));
                try {
                    if ($scenarioAssignment[0] instanceof Application_Model_ScenarioAssignment) {
                        $this->objectManager->deleteObject('scenarioAssignment', $scenarioAssignment[0]->scenarioAssignmentId);
                    }
                    if (1 < $this->params['scenarioId']) {
                        $scenarioAssignment = new Application_Model_ScenarioAssignment($this->params);
                        $scenarioAssignmentId = $this->objectManager->saveObject($scenarioAssignment);
                    }
                } catch (Exception $e) {
                    $result = array('error' => 1, 'code' => 500, 'message' => $e->getMessage());
                }
                $node = $this->objectManager->getObject($this->objectName, $objectId);
                $scenarios = $this->objectManager->getAllObjects('scenario');
                $assignment = $this->objectManager->getAllObjects('ScenarioAssignment', array(0 => array('column' => 'nodeId', 'operand' => $objectId)));
                $nodes = $this->objectManager->getAllObjects('Node');
                $this->view->objects = array('node' => $node,
                    'scenarios' => $scenarios,
                    'nodes' => $nodes, 'assignment' => ($assignment) ? $assignment[0] : NULL);
                break;
        }
        $this->_helper->json($result);
    }

}

?>
