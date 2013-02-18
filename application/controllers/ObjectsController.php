<?php

/**
 * Description of AdminController
 * * @author Max
 */
class ObjectsController extends Zend_Controller_Action {

    private $dataMapper;
    private $redirector;
    private $className;
    private $objectIdName;
    private $objectName;
    private $subobjects;

    public function init() {
        $this->dataMapper = new Application_Model_DataMapper();
        $this->redirector = $this->_helper->getHelper('Redirector');
        switch ($this->_request->getParam('objectType')) {
            case 'level': $this->className = 'Application_Model_Level';
                $this->subobjects = array('levels' => $this->dataMapper->getAllObjects('Application_Model_Level'));
                break;
            case 'element': $this->className = 'Application_Model_Element';
                break;
            case 'position': $this->className = 'Application_Model_Position';
                $this->subobjects = array('orgobjects' => $this->dataMapper->getAllObjects('Application_Model_Orgobject'));
                break;
            case 'user': $this->className = 'Application_Model_User';
                $this->subobjects = array('positions' => $this->dataMapper->getAllObjects('Application_Model_Position'));
                break;
            case 'orgobject': $this->className = 'Application_Model_Orgobject';
                $this->subobjects = array('levels' => $this->dataMapper->getAllObjects('Application_Model_Level'));
                break;
            default : $this->className = 'Application_Model_Level';
        }
        $this->setClassAndTableName($this->className);
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
        $params = $this->getRequest()->getPost();
        $object = new $this->className($params);
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

}

?>
