<?php

class IndexController extends Zend_Controller_Action {

    private $redirector;

    public function init() {
        $this->redirector = $this->_helper->getHelper('Redirector');
    }

    public function indexAction() {
        // action body
    }

    public function newDomainAction() {
        if ('' == $this->getRequest()->getParam('userName')) {
            throw new Exception('No user name provided', 500);
        }
        if ('' == $this->getRequest()->getParam('email')) {
            throw new Exception('No email provided', 500);
        }
        if ('' == $this->getRequest()->getParam('password')) {
            throw new Exception('No password provided', 500);
        }
        if ('' == $this->getRequest()->getParam('companyName')) {
            throw new Exception('No company name provided', 500);
        }
        $objectManager = new Application_Model_ObjectsManager(-1);
        if ($objectManager->checkLoginExistance($this->getRequest()->getParam('email'))){
            throw new Exception('User with such email is already registered', 500);
        }
        $domain = new Application_Model_Domain(array('domainName' => $this->getRequest()->getParam('companyName') . ' domain', 'hash' => md5(time())));
        $domainId = $objectManager->saveObject($domain);
        $objectManager->setDomainId($domainId);
        $node = new Application_Model_Node(array('nodeName' => $this->getRequest()->getParam('companyName'), 'domainId' => $domainId, 'parentNodeId' => -1));
        $nodeId = $objectManager->saveObject($node);
        $position = new Application_Model_Position(array('positionName' => 'administrator', 'nodeId' => $nodeId, 'domainId' => $domainId));
        $positionId = $objectManager->saveObject($position);
        $user = new Application_Model_User(array('userName' => $this->getRequest()->getParam('userName'),
                    'login' => $this->getRequest()->getParam('email'),
                    'password' => $this->getRequest()->getParam('password'),
                    'positionId' => $positionId,
                    'domainId' => $domainId));
        $userId = $objectManager->saveObject($user);
        $userGroup = new Application_Model_UserGroup(array('userId' => $userId, 'domainId' => $domainId, 'role' => 'admin', 'userGroupName' => 'admin'));
        $objectManager->saveObject($userGroup);
        $this->redirector->gotoSimple('index', 'index');
    }

    public function newDomain1Action() {
        if (!$this->getRequest()->getParam('domainName')) {
            throw new Exception('No domain name provided', 400);
        }
        $session = new Zend_Session_Namespace('Auth');
        if ($this->getRequest()->getParam('domainName')) {
            $session->newNode['domainName'] = $this->getRequest()->getParam('domainName');
            $session->newNode['nodeName'] = $this->getRequest()->getParam('domainName');
        } else {
            throw new Exception('Domain data not complete', 400);
        }
    }

    public function saveNewDomainAction() {
        $domain = new Application_Model_Domain(array('domainName' => $this->getRequest()->getParam('domainName')));
        $domainId = $objectManager->saveObject($domain);
        $user = new Application_Model_User(array('userName' => $session->newUser['userName'],
                    'login' => $session->newUser['login'],
                    'password' => $session->newUser['password'],
                    'domainId' => $domainId,
                    'positionId' => -1));
        $userId = $objectManager->saveObject($user);
        $userGroup = new Application_Model_UserGroup(array('userId' => $userId,
                    'userGroupName' => 'admin',
                    'role' => 'admin',
                    'domainId' => $domainId));
        $userGroupId = $objectManager->saveObject($userGroup);
        $domainOwner = new Application_Model_DomainOwner(array('domainId' => $domainId, 'userId' => $userId));
        $objectManager->saveObject($domainOwner);
    }

}

