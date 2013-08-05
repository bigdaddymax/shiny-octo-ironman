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
        $registry = Zend_Registry::getInstance();

        $this->translate = $registry->get('Zend_Translate');

        $form = new Zend_Form();
        $form->setAction('/index/new-domain');
        $form->setTranslator($this->translate);

        $username = $form->createElement('text', 'userName');

        $usernameValidator = new Zend_Validate_Callback(
                array('callback' => function($user) {
                $objectManager = new Application_Model_ObjectsManager(-1);
                $userTest = $objectManager->checkUserExistance($user);
                if ($userTest) {
                    return false;
                } else {
                    return true;
                }
            }));
        $usernameValidator->setMessage("User '%value%' is already registered");

        $username->addValidator('alnum')
                ->addValidator($usernameValidator)
                ->setRequired(true);

        $company = $form->createElement('text', 'companyName');
        $company->addValidator('StringLength', 4)
                ->setRequired('true');

        $emailValidator = new Zend_Validate_Callback(
                array('callback' => function($email) {
                $objectManager = new Application_Model_ObjectsManager(-1);
                $emailTest = $objectManager->checkEmailExistance($email);
                if ($emailTest) {
                    return false;
                } else {
                    return true;
                }
            }));
        $emailValidator->setMessage("Email '%value%' is already registered");
        $email = $form->createElement('text', 'email');
        $email->addValidator('StringLength', false, array(6))
                ->addValidator($emailValidator)
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
            array('CapexFormErrors', array('placement' => 'prepend', 'class' => 'error')),
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

