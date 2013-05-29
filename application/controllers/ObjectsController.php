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
    private $redirector;
    private $className;
    private $objectIdName;
    private $objectName;
    private $subobjects;
    private $session;
    private $params;

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
        $this->session = new Zend_Session_Namespace('Auth');
        $params = $this->getRequest()->getParams();
        if (!empty($params)) {
            $keys = array_keys($params);
            $values = array_values($params);
            //       $keys = array_keys($params);
            array_walk($keys, 'stripUnderscore');
            $this->params = array_combine($keys, $values);
        }
        //+++++++++++++++++++++++++++ END OF WORKAROUND ++++++++++++++++++++++++++++++++++++

        $this->objectManager = new Application_Model_ObjectsManager($this->session->domainId);
        $this->redirector = $this->_helper->getHelper('Redirector');
        switch ($this->_request->getParam('objectType')) {
            case 'node':
                $this->subobjects = array('nodes' => $this->objectManager->getAllObjects('node'));
                break;
            case 'element':
                break;
            case 'position':
                $this->subobjects = array('nodes' => $this->objectManager->getAllObjects('Node'));
                break;
            case 'user':
                $this->subobjects = array('positions' => $this->objectManager->getAllObjects('Position'));
                break;
        }
        $this->objectName = strtolower($this->_request->getParam('objectType'));
        $this->setClassAndTableName($this->_request->getParam('objectType'));
        $this->params['domainId'] = $this->session->domainId;
    }

    private function setClassAndTableName($object) {
        $this->className = 'Application_Model_' . ucfirst($object);
        $this->tableName = $this->objectName;
        $this->objectIdName = $this->objectName . 'Id';
        $this->view->objectName = $this->objectName;
        $this->view->objectIdName = $this->objectIdName;
    }

    public function indexAction() {
        $this->view->objects = $this->objectManager->getAllObjects($this->objectName);
        $this->view->subobjects = $this->subobjects;
    }

    public function addObjectAction() {
//        Zend_Debug::dump($params);
        $object = new $this->className($this->params);
        if ($object->isValid()) {
            $this->objectManager->saveObject($object);
            $this->view->objects = $this->objectManager->getAllObjects($this->objectName);
            $this->view->objectType = $this->objectName;
            $this->view->subobjects = $this->subobjects;
            $this->_helper->layout()->disableLayout();
        } else {
            //      Zend_Debug::dump($params);
            //    Zend_Debug::dump($object);
        }
    }

    public function deleteAction() {
        $objectId = (int) $this->_request->getParam($this->objectIdName);
        //try {
        $this->objectManager->deleteObject($this->objectName, $objectId);
        $this->redirector->gotoSimple('index', 'objects', null, array('objectType' => $this->objectName));
        //} catch (Zend_Exception $e) {
        //  $this->view->exceptionMessage = 'Got exception while trying to delete ' . $this->objectName . ': ' . $e->getMessage();
//        }
    }

    public function userPrivilegesAction() {
        $userId = (int) $this->_request->getParam('userId');
        if ($userId /* && $this->_request->isPost */) {
            $this->view->user = $this->objectManager->getObject('User', $userId);
            $this->view->userPrivileges = $this->objectManager->getPrivilegesTable($userId);
        }
    }

    public function editPrivilegesAction() {
        if ($this->_request->isPost()) {
            $privilege = new Application_Model_Privilege(array('userId' => $this->_request->getParam('userId'),
                        'objectType' => $this->_request->getParam('object'),
                        'objectId' => $this->_request->getParam('objectId'),
                        'privilege' => $this->_request->getParam('privilege'),
                        'domainId' => $this->session->domainId));
            if ((bool) $this->_request->getParam('state')) {
                $result = $this->objectManager->grantPrivilege($privilege);
            } else {
                $result = $this->objectManager->revokePrivilege($privilege);
            }
        }

        $this->_helper->json($result, true);
        exit;
    }

    public function openObjectAction() {
        $objectId = (int) $this->params[$this->objectIdName];
        switch ($this->_request->getParam('objectType')) {
            case 'node':
                $node = $this->objectManager->getObject($this->_request->getParam('objectType'), $objectId);
                $scenarios = $this->objectManager->getAllObjects('scenario');
                $assignment = $this->objectManager->getAllObjects('ScenarioAssignment', array(0 => array('column' => 'nodeId', 'operand' => $objectId)));
                $nodes = $this->objectManager->getAllObjects('Node');
                $this->view->objects = array('node' => $node,
                    'scenarios' => $scenarios,
                    'nodes' => $nodes, 'assignment' => ($assignment) ? $assignment[0] : NULL);
                $this->view->partialFile = 'open-node.phtml';
                break;
            case 'element':
                $element = $this->objectManager->getObject($this->_request->getParam('objectType'), $objectId);
                $this->view->objects = array('element' => $element);
                $this->view->partialFile = 'open-element.phtml';
                break;
        }
    }

    public function editObjectAction() {
        $this->objectManager->saveObject($this->params);
        $objectId = (int) $this->params[$this->objectIdName];
        switch ($this->objectName) {
            case 'node':
                $scenarioAssignment = $this->objectManager->getAllObjects('ScenarioAssignment', array(0 => array('column' => 'nodeId', 'operand' => $objectId)));
                if ($scenarioAssignment[0] instanceof Application_Model_ScenarioAssignment) {
                    $this->objectManager->deleteObject('scenarioAssignment', $scenarioAssignment[0]->scenarioAssignmentId);
                }
                if (1 < $this->params['scenarioId']) {
                    $scenarioAssignment = new Application_Model_ScenarioAssignment($this->params);
                    $scenarioAssignmentId = $this->objectManager->saveObject($scenarioAssignment);
                }
                $node = $this->objectManager->getObject($this->objectName, $objectId);
                $scenarios = $this->objectManager->getAllObjects('scenario');
                $assignment = $this->objectManager->getAllObjects('ScenarioAssignment', array(0 => array('column' => 'nodeId', 'operand' => $objectId)));
                $nodes = $this->objectManager->getAllObjects('Node');
                $this->view->objects = array('node' => $node,
                    'scenarios' => $scenarios,
                    'nodes' => $nodes, 'assignment' => ($assignment) ? $assignment[0] : NULL);
                $this->view->partialFile = 'edit-node.phtml';
                break;
        }
    }

}

?>
