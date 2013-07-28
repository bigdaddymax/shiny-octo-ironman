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

    public function init() {
        $this->redirector = $this->_helper->getHelper('Redirector');
        $registry = Zend_Registry::getInstance();

        $this->translate = $registry->get('Zend_Translate');

        $form = new Zend_Form();
        $form->setAction('/index/new-domain');
        $form->setTranslator($this->translate);

        $username = $form->createElement('text', 'userName');
        $username->addValidator('alnum')
                ->setRequired(true);

        $company = $form->createElement('text', 'companyName');
        $company->addValidator('StringLength', 4)
                ->setRequired('true');

        $email = $form->createElement('text', 'email');
        $email->addValidator('StringLength', false, array(6))
                ->setRequired();

        $password = $form->createElement('password', 'password');
        $password->addValidator('StringLength', false, array(4))
                ->setRequired(true);

        $submit = $form->createElement('submit', 'signup');
        $submit->setIgnore(true);

        $form->addElement($username)
                ->addElement($company)
                ->addElement($email)
                ->addElement($password)
                ->addElement($submit);
        $form->userName->setLabel('name')
                ->setAttrib('id', 'userName')
                ->setAttrib('name', 'userName')
                ->setAttrib('placeholder', $this->translate->_('name'));
        $form->password->setLabel('password')
                ->setAttrib('id', 'password')
                ->setAttrib('name', 'password')
                ->setAttrib('placeholder', $this->translate->_('password'));
        $form->email->setLabel('email')
                ->setAttrib('id', 'email')
                ->setAttrib('name', 'email')
                ->setAttrib('placeholder', $this->translate->_('email'));
        $form->companyName->setLabel('company')
                ->setAttrib('id', 'companyName')
                ->setAttrib('name', 'companyName')
                ->setAttrib('placeholder', $this->translate->_('company'));
        $form->signup->setAttrib('class', 'signup-button button');

        $form->addElementPrefixPath('Capex_Decorator', 'Capex/decorator', 'decorator');
        $form->setElementDecorators(array('viewHelper',
            array('CapexFormErrors', array('placement' => 'prepend')),
            'label',
            array('htmlTag', array('tag' => 'div'))));
        $form->signup->setDecorators(array('viewHelper'));
        
        $this->form = $form;
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

