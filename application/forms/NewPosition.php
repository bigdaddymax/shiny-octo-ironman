<?php

class Application_Form_NewPosition extends Zend_Form {

    public function __construct($nodes) {
        $registry = Zend_Registry::getInstance();
        $translate = $registry->get('Zend_Translate');
        $this->setTranslator($translate);

        $nodeId = $this->createElement('select', 'nodeId');
        $nodeId->addMultiOption('-1', $translate->_('node name'));
        foreach ($nodes as $node) {
            $nodeId->addMultiOption($node->nodeId, $node->nodeName);
        }

        $positionName = $this->createElement('text', 'positionName');
        $positionValidator = new Zend_Validate_Callback(
                        array('callback' => function($user) {
                                $objectManager = new Application_Model_ObjectsManager(-1);
                                $userTest = $objectManager->checkUserExistance($user);
                                if ($userTest) {
                                    return false;
                                } else {
                                    return true;
                                }
                            }));
        $positionValidator->setMessage("Position '%value%' is already registered");

        $positionName->addValidator('alnum', true, array('allowWhiteSpace'=>true))
                ->addValidator($positionValidator)
                ->setRequired(true);

        $objectType = $this->createElement('hidden', 'objectType');
        $objectType->setValue('position');
        $submit = $this->createElement('submit', 'save');
        $submit->setIgnore(true);
        $this->addElement($nodeId)
                ->addElement($positionName)
                ->addElement($objectType)
                ->addElement($submit);

        $this->nodeId->setLabel('node name')
                ->setAttrib('class', 'form-control')
                ->setValue(-1)
                ->setOptions(array('disable'=>array(-1)))
                ->setRequired(true);
        $this->positionName->setLabel('position name')
                ->setAttrib('class', 'form-control')
                ->setAttrib('placeholder', $translate->_('position'));

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