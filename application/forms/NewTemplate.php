<?php

class Application_Form_NewTemplate extends Zend_Form {

    public function __construct($args) {
        $registry = Zend_Registry::getInstance();
        $translate = $registry->get('Zend_Translate');
        $this->setTranslator($translate);

        $type = $this->createElement('select', 'type');
        $type->addMultiOption('-1', $translate->_('template type'));
        foreach ($args['types'] as $item) {
            $type->addMultiOption($item, $translate->_($item));
        }

        $language = $this->createElement('select', 'language');
        $language->addMultiOption('-1', $translate->_('language'));
        foreach ($args['locales'] as $locale) {
            $language->addMultiOption($locale, $translate->_($locale));
        }

        $templateName = $this->createElement('text', 'templateName');
        $templateValidator = new Zend_Validate_Callback(
                        array('callback' => function($user) {
                                $objectManager = new Application_Model_ObjectsManager(-1);
                                $userTest = $objectManager->checkUserExistance($user);
                                if ($userTest) {
                                    return false;
                                } else {
                                    return true;
                                }
                            }));
        $templateValidator->setMessage("Element '%value%' is already registered");

        $templateName->addValidator('alnum')
                ->addValidator($templateValidator)
                ->setRequired(true);
        
        $body = $this->createElement('textarea', 'body');

        $objectType = $this->createElement('hidden', 'objectType');
        $objectType->setValue('template');
        $submit = $this->createElement('submit', 'save');
        $submit->setIgnore(true);
        $this->addElement($type)
                ->addElement($language)
                ->addElement($templateName)
                ->addElement($body)
                ->addElement($objectType)
                ->addElement($submit);

        $this->type->setLabel('type')
                ->setAttrib('class', 'form-control')
                ->setValue(-1)
                ->setOptions(array('disable' => array(-1)));
        $this->language->setLabel('language')
                ->setAttrib('class', 'form-control')
                ->setValue(-1)
                ->setOptions(array('disable' => array(-1)));
        $this->templateName->setLabel('name')
                ->setAttrib('class', 'form-control')
                ->setAttrib('id', 'userName')
                ->setAttrib('name', 'userName')
                ->setAttrib('placeholder', $translate->_('template'));
        $this->body->setLabel('template body')
                ->setAttrib('class', 'form-control');

        $this->save->setAttrib('class', 'btn btn-danger');

        $this->addElementPrefixPath('Capex_Decorator', 'Capex/decorator', 'decorator');
        $this->setElementDecorators(array('viewHelper',
            array('CapexFormErrors', array('placement' => 'prepend', 'class' => 'error')),
            array('label', array('class' => 'control-label')),
            array('MyElement', array('tag' => 'div', 'class' => 'form-group'))));
        $this->save->setDecorators(array('viewHelper'))
                ->setAttrib('class', 'btn btn-danger');
        $this->setAttrib('role', 'form')
                ->setAttrib('class', 'form-horisontal');
        $this->setDecorators(array('FormElements', 'Form'));
    }

}

?>