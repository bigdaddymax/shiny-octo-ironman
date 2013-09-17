<?php

class Application_Form_NewNode extends Zend_Form {

    public function __construct($nodes) {
        $registry = Zend_Registry::getInstance();
        $translate = $registry->get('Zend_Translate');
        $this->setTranslator($translate);

        $parentNodeId = $this->createElement('select', 'parentNodeId');
        $parentNodeId->addMultiOption('-1', $translate->_('no parent'));
        foreach ($nodes['nodes'] as $node) {
            $parentNodeId->addMultiOption($node->nodeId, $node->nodeName);
        }

        $nodeName = $this->createElement('text', '_nodeName');
        $nodeValidator = new Zend_Validate_Callback(
                        array('callback' => function($user) {
                                $objectManager = new Application_Model_ObjectsManager(-1);
                                $userTest = $objectManager->checkUserExistance($user);
                                if ($userTest) {
                                    return false;
                                } else {
                                    return true;
                                }
                            }));
        $nodeValidator->setMessage("Element '%value%' is already registered");

        $nodeName->addValidator('alnum', true, array('allowWhiteSpace'=>true))
                ->addValidator($nodeValidator)
                ->setRequired(true);

        $objectType = $this->createElement('hidden', 'objectType');
        $objectType->setValue('node');
        $submit = $this->createElement('submit', 'signup');
        $submit->setIgnore(true);
        $this->addElement($parentNodeId)
                ->addElement($nodeName)
                ->addElement($objectType)
                ->addElement($submit);

        $this->parentNodeId->setLabel('parent node')
                ->setAttrib('class', 'form-control')
                ->setValue(-1);
        $this->_nodeName->setLabel('node name')
                ->setAttrib('class', 'form-control')
                ->setAttrib('placeholder', $translate->_('node name'));

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