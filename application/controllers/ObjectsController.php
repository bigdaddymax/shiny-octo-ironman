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

    private $dataMapper;
    private $redirector;
    private $className;
    private $objectIdName;
    private $objectName;
    private $subobjects;
    private $objectsManager;
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
        $params = $this->getRequest()->getParams();
        if (!empty($params)) {
            $keys = array_keys($params);
            $values = array_values($params);
            //       $keys = array_keys($params);
            array_walk($keys, 'stripUnderscore');
            $this->params = array_combine($keys, $values);
        }
        //+++++++++++++++++++++++++++ END OF WORKAROUND ++++++++++++++++++++++++++++++++++++

        $this->dataMapper = new Application_Model_DataMapper();
        $this->objectsManager = new Application_Model_ObjectsManager();
        $this->redirector = $this->_helper->getHelper('Redirector');
        switch ($this->_request->getParam('objectType')) {
            case 'node': $this->className = 'Application_Model_Node';
                $this->subobjects = array('nodes' => $this->dataMapper->getAllObjects('Application_Model_Node'));
                break;
            case 'element': $this->className = 'Application_Model_Element';
                break;
            case 'position': $this->className = 'Application_Model_Position';
                $this->subobjects = array('nodes' => $this->dataMapper->getAllObjects('Application_Model_Node'));
                break;
            case 'user': $this->className = 'Application_Model_User';
                $this->subobjects = array('positions' => $this->dataMapper->getAllObjects('Application_Model_Position'));
                break;
            default : $this->className = 'Application_Model_Node';
        }
        $this->setClassAndTableName($this->className);
        $this->session = new Zend_Session_Namespace('Auth');
        $this->params['domainId'] = $this->session->domainId;
    }

    private function setClassAndTableName() {
        $this->objectName = strtolower(substr($this->className, strrpos($this->className, '_') + 1));
        $this->tableName = $this->objectName . 's';
        $this->objectIdName = $this->objectName . 'Id';
        $this->view->objectName = $this->objectName;
        $this->view->objectIdName = $this->objectIdName;
    }

    public function indexAction() {
        $this->view->objects = $this->dataMapper->getAllObjects($this->className);
        $this->view->subobjects = $this->subobjects;
    }

    public function addObjectAction() {
//        Zend_Debug::dump($params);
        $object = new $this->className($this->params);
        if ($object->isValid()) {
            $this->dataMapper->saveObject($object);
            $this->view->objects = $this->dataMapper->getAllObjects($this->className);
            $this->view->objectType = $this->objectName;
            $this->view->subobjects = $this->subobjects;
            $this->_helper->layout()->disableLayout();
        } else {
            Zend_Debug::dump($params);
            Zend_Debug::dump($object);
        }
    }

    public function deleteAction() {
        $objectId = (int) $this->_request->getParam($this->objectIdName);
        try {
            $this->dataMapper->deleteObject($objectId, $this->className);
            $this->redirector->gotoSimple('index', 'objects', null, array('objectType' => $this->objectName));
        } catch (Zend_Exception $e) {
            $this->view->exceptionMessage = 'Got exception while trying to delete ' . $this->objectName . ': ' . $e->getMessage();
        }
    }

    public function userPrivilegesAction() {
        $userId = (int) $this->_request->getParam('userId');
        if ($userId /* && $this->_request->isPost */) {
            $this->view->user = $this->dataMapper->getObject($userId, 'Application_Model_User');
            $this->view->userPrivileges = $this->objectsManager->getPrivilegesTable($userId);
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
                $this->objectsManager->grantPrivilege($privilege);
            } else {
                $this->objectsManager->revokePrivilege($privilege);
            }
        }
        exit;
    }

    public function openObjectAction() {
        $objectId = (int) $this->params[$this->objectIdName];
        switch ($this->className) {
            case 'Application_Model_Node':
                $node = $this->dataMapper->getObject($objectId, $this->className);
                $scenarios = $this->objectsManager->getAllScenarios();
                $assignment = $this->dataMapper->getAllObjects('Application_Model_ScenarioAssignment', array(0 => array('column' => 'nodeId', 'operand' => $objectId)));
                $nodes = $this->dataMapper->getAllObjects('Application_Model_Node');
                $this->view->objects = array('node' => $node,
                    'scenarios' => $scenarios,
                    'nodes' => $nodes, 'assignment' => ($assignment)?$assignment[0]:NULL);
                $this->view->partialFile = 'open-node.phtml';
                break;
        }
    }

    public function editObjectAction(){
        $this->objectsManager->saveObject($this->params);
         $objectId = (int) $this->params[$this->objectIdName];
        switch ($this->className) {
            case 'Application_Model_Node':
                $node = $this->dataMapper->getObject($objectId, $this->className);
                $scenarios = $this->objectsManager->getAllScenarios();
                $assignment = $this->dataMapper->getAllObjects('Application_Model_ScenarioAssignment', array(0 => array('column' => 'nodeId', 'operand' => $objectId)));
                $nodes = $this->dataMapper->getAllObjects('Application_Model_Node');
                $this->view->objects = array('node' => $node,
                    'scenarios' => $scenarios,
                    'nodes' => $nodes, 'assignment' => ($assignment)?$assignment[0]:NULL);
                $this->view->partialFile = 'edit-node.phtml';
                break;
        }
//       $this->redirector->gotoSimple('open-object',
 //                                     'objects',
 //                                     null,
 //                                     array('objectType'=>$this->objectName,
 //                                           'objectId'=>$this->params[$this->objectIdName]));
    }
}

?>
