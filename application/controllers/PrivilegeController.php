<?php

class PrivilegeController extends Zend_Controller_Action {

    private $objectManager;
    private $session;
    
    public function init() {
        parent::init();
        $this->session = new Zend_Session_Namespace('Auth');
        $this->objectManager = new Application_Model_ObjectsManager($this->session->domainId);
    }


    public function indexAction() {
        
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
    }

}