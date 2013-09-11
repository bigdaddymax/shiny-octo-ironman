<?php

class Application_Form_NewUser extends Zend_Form {

    public function __construct($positions) {
        $registry = Zend_Registry::getInstance();
        $translate = $registry->get('Zend_Translate');
        $this->setTranslator($translate);

        $position = $this->createElement('select', 'positionId');
        $position->addMultiOption('-1', $translate->_('position'));
        foreach ($positions['position'] as $item) {
            $position->addMultiOption($item->positionId, $item->positionName);
        }

        $userName = $this->createElement('text', 'userName');
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

        $userName->addValidator('alnum')
                ->addValidator($usernameValidator)
                ->setRequired(true);

        $password = $this->createElement('password', 'password');
        $password->addValidator('StringLength', false, array(4))
                ->setRequired(true);

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
        $email = $this->createElement('text', 'login');
        $email->addValidator('StringLength', false, array(6))
                ->addValidator($emailValidator)
                ->setRequired();

        $objectType = $this->createElement('hidden', 'objectType');
        $objectType->setValue('user');

        $submit = $this->createElement('submit', 'signup');
        $submit->setIgnore(true);
        $this->addElement($position)
                ->addElement($userName)
                ->addElement($email)
                ->addElement($password)
                ->addElement($objectType)
                ->addElement($submit);

        $this->positionId->setLabel('position')
                ->setAttrib('class', 'form-control')
                ->setValue(-1)
                ->setOptions(array('disable'=>array(-1)));
        $this->userName->setLabel('name')
                ->setAttrib('class', 'form-control')
                ->setAttrib('id', 'userName')
                ->setAttrib('name', 'userName')
                ->setAttrib('placeholder', $translate->_('name'));
        $this->password->setLabel('password')
                ->setAttrib('class', 'form-control')
                ->setAttrib('id', 'password')
                ->setAttrib('name', 'password')
                ->setAttrib('placeholder', $translate->_('password'));
        $this->login->setLabel('email')
                ->setAttrib('class', 'form-control')
                ->setAttrib('id', 'login')
                ->setAttrib('name', 'login')
                ->setAttrib('placeholder', $translate->_('email'));
        $this->signup->setAttrib('class', 'btn btn-danger');

        $this->addElementPrefixPath('Capex_Decorator', 'Capex/decorator', 'decorator');
        $this->setElementDecorators(array('viewHelper',
            array('CapexFormErrors', array('placement' => 'prepend', 'class' => 'error')),
            array('label', array('class' => 'control-label')),
            array('MyElement', array('tag' => 'div', 'class' => 'form-group'))));
        $this->signup->setDecorators(array('viewHelper'))
                ->setAttrib('class', 'btn btn-danger');
        $this->setAttrib('role', 'form')
                ->setAttrib('class', 'form-horisontal');
        $this->setDecorators(array('FormElements', 'Form'));
    }

}

?>