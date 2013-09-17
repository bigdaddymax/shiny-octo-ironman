<?php

class Application_Form_NewScenario extends Zend_Form {

    public function __construct($params) {
        $registry = Zend_Registry::getInstance();
        $translate = $registry->get('Zend_Translate');
        $this->setTranslator($translate);

        $userId = $this->createElement('select', 'userId');
        $userId->addMultiOption('-1', $translate->_('user name'));
        foreach ($params['users'] as $user) {
            $userId->addMultiOption($user->userId, $user->userName);
        }
        $scenarioName = $this->createElement('text', 'scenarioName');
        $scenarioValidator = new Zend_Validate_Callback(
                array('callback' => function($user) {
                $objectManager = new Application_Model_ObjectsManager(-1);
                $userTest = $objectManager->checkUserExistance($user);
                if ($userTest) {
                    return false;
                } else {
                    return true;
                }
            }));
        $scenarioValidator->setMessage("Element '%value%' is already registered");

        $scenarioName->addValidator('alnum')
                ->addValidator($scenarioValidator)
                ->setRequired(true);

        $submit = $this->createElement('submit', 'signup');
        $submit->setIgnore(true);
        $this->addElement($scenarioName)
                ->addElement($userId)
                ->addElement($submit);

        $this->userId->setLabel('user name')
                ->setAttrib('class', 'form-control')
                ->setValue(-1)
                ->setOptions(array('disable' => array(-1)));
        $this->scenarioName->setLabel('scenario name')
                ->setAttrib('class', 'form-control')
                ->setAttrib('placeholder', $translate->_('scenario name'));

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