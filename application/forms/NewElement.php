<?php

class Application_Form_NewElement extends Zend_Form {

    public function __construct($expgroups) {
        $registry = Zend_Registry::getInstance();
        $translate = $registry->get('Zend_Translate');
        $this->setTranslator($translate);

        $expgroup = $this->createElement('select', 'expgroup');
        $expgroup->addMultiOption('-1', $translate->_('expgroup'));
        foreach ($expgroups['expgroup'] as $item) {
            $expgroup->addMultiOption($item, $item);
        }

        $elementName = $this->createElement('text', 'elementName');
        $elementValidator = new Zend_Validate_Callback(
                        array('callback' => function($user) {
                                $objectManager = new Application_Model_ObjectsManager(-1);
                                $userTest = $objectManager->checkUserExistance($user);
                                if ($userTest) {
                                    return false;
                                } else {
                                    return true;
                                }
                            }));
        $elementValidator->setMessage("Element '%value%' is already registered");

        $elementName->addValidator('alnum', true, array('allowWhiteSpace'=>true))
                ->addValidator($elementValidator)
                ->setRequired(true);
        
        $elementCode = $this->createElement('text', 'elementCode');
        $elementCode->addValidator('digits')
                ->setRequired(true);

        $objectType = $this->createElement('hidden', 'objectType');
        $objectType->setValue('element');
        $submit = $this->createElement('submit', 'signup');
        $submit->setIgnore(true);
        $this->addElement($expgroup)
                ->addElement($objectType)
                ->addElement($elementName)
                ->addElement($elementCode)
                ->addElement($submit);

        $this->expgroup->setLabel('expgroup')
                ->setAttrib('class', 'form-control')
                ->setValue(-1)
                ->setOptions(array('disable'=>array(-1)));
        $this->elementName->setLabel('name')
                ->setAttrib('class', 'form-control')
                ->setAttrib('placeholder', $translate->_('element'));
        
        $this->elementCode->setLabel('element code')
                ->setAttrib('class', 'form-control')
                ->setAttrib('placeholder', $translate->_('element code'));

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