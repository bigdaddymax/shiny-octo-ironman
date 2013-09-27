<?php

class Application_Form_NewSignup extends Zend_Form {

    public function __construct($options = null) {
        parent::__construct($options);

        $registry = Zend_Registry::getInstance();
        $translator = $registry->get('Zend_Translate');
        $this->setTranslator($translator);

        $this->setAction('/index/new-domain')
                ->setAttrib('role', 'form')
                ->setAttrib('class', 'form-horisontal');
        
        $username = $this->createElement('text', 'userName');

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

        $company = $this->createElement('text', 'companyName');
        $company->addValidator('StringLength', 4)
                ->setRequired('true');

        $emailValidator = new Zend_Validate_Callback(
                array('callback' => function($email) {
                $objectManager = new Application_Model_ObjectsManager(-1);
                $emailTest = $objectManager->checkLoginExistance($email);
                if ($emailTest) {
                    return false;
                } else {
                    return true;
                }
            }));
        $emailValidator->setMessage("Email '%value%' is already registered");
        $email = $this->createElement('text', 'email');
        $email->addValidator('StringLength', false, array(6))
                ->addValidator($emailValidator)
                ->setRequired();

        $password = $this->createElement('password', 'password');
        $password->addValidator('StringLength', false, array(4))
                ->setRequired(true);

        $submit = $this->createElement('submit', 'signup');
        $submit->setIgnore(true);

        $this->addElement($username)
                ->addElement($company)
                ->addElement($email)
                ->addElement($password)
                ->addElement($submit);
        $this->userName->setLabel('name')
                ->setAttrib('class', 'form-control')
                ->setAttrib('id', 'userName')
                ->setAttrib('name', 'userName')
                ->setAttrib('placeholder', $translator->_('name'));
        $this->password->setLabel('password')
                ->setAttrib('class', 'form-control')
                ->setAttrib('id', 'password')
                ->setAttrib('name', 'password')
                ->setAttrib('placeholder', $translator->_('password'));
        $this->email->setLabel('email')
                ->setAttrib('class', 'form-control')
                ->setAttrib('id', 'email')
                ->setAttrib('name', 'email')
                ->setAttrib('placeholder', $translator->_('email'));
        $this->companyName->setLabel('company')
                ->setAttrib('class', 'form-control')
                ->setAttrib('id', 'companyName')
                ->setAttrib('name', 'companyName')
                ->setAttrib('placeholder', $translator->_('company'));
        $this->signup->setAttrib('class', 'btn btn-danger');

        $this->addElementPrefixPath('Capex_Decorator', 'Capex/decorator', 'decorator');
        $this->setElementDecorators(array('viewHelper',
            array('CapexFormErrors', array('placement' => 'prepend', 'class' => 'error')),
            array('label', array('class' => 'control-label')),
            array('MyElement', array('tag' => 'div', 'class' => 'form-group'))));
        $this->signup->setDecorators(array('viewHelper'))
                ->setAttrib('class', 'btn btn-danger');
    }

}