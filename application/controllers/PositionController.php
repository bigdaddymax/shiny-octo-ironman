<?php

/**
 * Description of AdminController
 * * @author Max
 */
class PositionController extends Zend_Controller_Action {

    private $dataMapper;
    private $redirector;

    public function init() {
        $this->dataMapper = new Application_Model_DataMapper();
        $this->redirector = $this->_helper->getHelper('Redirector');
    }

    public function indexAction() {
        $this->view->positions = $this->dataMapper->getAllObjects('Application_Model_Position');
    }

    public function addPositionAction() {
        $params = $this->_request->getParams();
        $position = new Application_Model_Position($params);
        if ($position->isValid()) {
            $this->dataMapper->saveObject($position);
            $this->view->positions = $this->dataMapper->getAllObjects('Application_Model_Position');
            $this->_helper->layout()->disableLayout();
        } else {
            Zend_Debug::dump($params);
        }
    }

    public function deleteAction() {
        $positionId = (int) $this->_request->getParam('positionId');
        try {
            $this->dataMapper->deleteObject($positionId, 'Application_Model_Position');
            $this->redirector->gotoSimple('index','position');
        } catch (Zend_Exception $e) {
            echo 'Got exception while trying to delete Position: ' . $e->getMessage();
        }
    }

}

?>
