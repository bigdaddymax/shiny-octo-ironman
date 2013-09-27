<?php

/**
 * class IndexController
 * 
 * Default entry point of application, allows customer to sign in, log in and browse 
 * for different info about the project
 * 
 */
class IndexController extends Zend_Controller_Action {

    private $redirector;
    private $translate;
    private $form;
    private $objectManager;

    /**
     * Init some staff, basically, the signup form
     */
    public function init() {
        $this->redirector = $this->_helper->getHelper('Redirector');
        $this->form = new Application_Form_NewSignup();
    }

    public function indexAction() {
        $this->view->form = $this->form;
    }

    /**
     * Lets create new domian
     * @throws Exception
     */
    public function newDomainAction() {

        if (!$this->form->isValid($this->_request->getParams())) {
            $this->view->form = $this->form;
        } else {
            $objectManager = new Application_Model_ObjectsManager(-1);
            if ($objectManager->checkLoginExistance($this->getRequest()->getParam('email'))) {
                throw new Exception('User with such email is already registered', 500);
            }
            // Create new domain
            $domain = new Application_Model_Domain(array('domainName' => $this->getRequest()->getParam('companyName') . ' domain', 'hash' => md5(time())));
            $domainId = $objectManager->saveObject($domain);
            
            // Update $objectManager with new domainId
            $objectManager->setDomainId($domainId);
            $node = new Application_Model_Node(array('nodeName' => $this->getRequest()->getParam('companyName'), 'domainId' => $domainId, 'parentNodeId' => -1));
            $nodeId = $objectManager->saveObject($node);
            $position = new Application_Model_Position(array('positionName' => 'administrator', 'nodeId' => $nodeId, 'domainId' => $domainId));
            $positionId = $objectManager->saveObject($position);
            $auth = new Application_Model_Auth();
            $user = new Application_Model_User(array('userName' => $this->getRequest()->getParam('userName'),
                'login' => $this->getRequest()->getParam('email'),
                'password' => $auth->hashPassword($this->getRequest()->getParam('password')),
                'positionId' => $positionId,
                'domainId' => $domainId));
            $userId = $objectManager->saveObject($user);
            $userGroup = new Application_Model_UserGroup(array('userId' => $userId, 'domainId' => $domainId, 'role' => 'admin', 'userGroupName' => 'admin'));
            $objectManager->saveObject($userGroup);
            $this->redirector->gotoSimple('index', 'index');
        }
    }

    public function changeLangAction() {
        switch ($this->getRequest()->getParam('lang')) {
            case 'ua';
            case 'en' : $session = new Zend_Session_Namespace('Auth');
                $session->lang = $this->getRequest()->getParam('lang');
                $langLocale = $this->getRequest()->getParam('lang');

                // Set up and load the translations (all of them!)
                $translate = new Zend_Translate(array(
                    'adapter' => 'array',
                    'content' => APPLICATION_PATH . '/../library/Capex/lang/' . $langLocale . '/translation.php',
                    'locale' => $langLocale
                ));

                $registry = Zend_Registry::getInstance();
                $registry->set('Zend_Translate', $translate);
                break;
        }
        $this->redirector->gotoSimple('index', 'index');
    }

}

