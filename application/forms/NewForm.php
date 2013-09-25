<?php

class Application_Form_NewForm extends Zend_Form {

    public function __construct($params) {

        $registry = Zend_Registry::getInstance();
        $translator = $registry->get('Zend_Translate');
        $this->setTranslator($translator);

        $this->setMethod('post')
                ->setAttrib('role', 'form')
                ->setAttrib('class', 'col-lg-7')
                ->setDecorators(array('FormElements', 'Form'))
                ->setAttrib('id', 'new-form');

// Creating and setting main form elements
        $formName = $this->createElement('text', 'formName');
        $formName->addValidator('alnum', true, array('allowWhiteSpace'=>true))
                ->addValidator('StringLength', 4)
                ->setRequired(true)
                ->setAttrib('class', 'form-control')
                ->setAttrib('id', 'formName')
                ->setAttrib('name', 'formName')
                ->setAttrib('placeholder', $translator->translate('form name'))
                ->setLabel('form name');

        $contragentName = $this->createElement('text', 'contragentName');
        $contragentName->addValidator('alnum', true, array('allowWhiteSpace'=>true))
                ->addValidator('StringLength', 4)
                ->setRequired(true)
                ->setAttrib('class', 'form-control')
                ->setAttrib('id', 'contragentName')
                ->setAttrib('name', 'contragentName')
                ->setAttrib('placeholder', $translator->translate('contragent'))
                ->setLabel('contragent');

        $expgroup = $this->createElement('select', 'expgroup', array('multiOptions' => $params['groups'], 'disable' => array(-1)));
        $expgroup->setAttrib('class', 'form-control')
                ->setAttrib('id', 'expgroup')
                ->setAttrib('name', 'expgroup')
                ->setValue(-1)
                ->setLabel('expgroup')
                ->setRequired(true)
                ->setAttrib('onChange', 'setExpTypes()');

        $nodeId = $this->createElement('select', 'nodeId', array('multiOptions' => $params['nodes'], 'disable' => array(-1)));
        $nodeId->setAttrib('class', 'form-control')
                ->setAttrib('id', 'nodeId')
                ->setAttrib('name', 'nodeId')
                ->setValue(-1)
                ->setLabel('deptmnt')
                ->setRequired(true);

        $addForm = $this->createElement('button', 'addForm');
        $addForm->setIgnore(true)
                ->setLabel('add form');

        $this->addElement($formName)
                ->addElement($contragentName)
                ->addElement($expgroup)
                ->addElement($nodeId)
                ->setAttrib('role', 'form');

        $this->addElementPrefixPath('Capex_Decorator', 'Capex/decorator', 'decorator');
        $this->setElementDecorators(array('viewHelper',
            array('CapexFormErrors', array('placement' => 'prepend', 'class' => 'error')),
            array('label', array('class' => 'control-label')),
            array('MyElement', array('tag' => 'div', 'class' => 'form-group'))));

        // Create input elements and wrap them with <td></td>
        $itemName = $this->createElement('text', 'itemName', array('Decorators' => array('viewHelper',
                array('label', array('class' => 'control-label')),
                array('MyElement', array('tag' => 'td', 'class' => 'form-group col-lg-3')))));
        $itemName->setAttrib('class', 'form-control')
                ->setAttrib('id', 'itemName')
                ->setAttrib('name', 'itemName')
                ->setAttrib('placeholder', 'item');

        $expType = $this->createElement('select', 'expType', array('Decorators' => array('viewHelper',
                array('label', array('class' => 'control-label')),
                array('MyElement', array('tag' => 'td', 'class' => 'form-group col-lg-3')))));
        $expType->setOptions(array('multiOptions' => array('-1' => $translator->translate('element')), 'disable' => array(-1)))
                ->setAttrib('class', 'form-control')
                ->setRequired(FALSE)
                ->setValidators(array());

        $value = $this->createElement('text', 'value', array('Decorators' => array('viewHelper',
                array('label', array('class' => 'control-label')),
                array('MyElement', array('tag' => 'td', 'class' => 'form-group col-lg-3')))));
        $value->setAttrib('class', 'form-control')
                ->setAttrib('id', 'value')
                ->setAttrib('name', 'value')
                ->setAttrib('placeholder', 'value');

        // Create Add Item button
        $addItemBtn = $this->createElement('button', 'addItemBtn', array('decorators' => array('viewHelper', array('HtmlTag', array('tag' => 'td')))));
        $addItemBtn->setAttrib('class', 'btn btn-primary');
        $addItemBtn->setAttrib('onClick', 'AddItem()');

        // Create DisplayGroup for Items edition
        $this->addDisplayGroup(
                array(
                        $itemName,
                        $expType,
                        $value,
                        $addItemBtn,
                ),
                'items',
                array(
                        'decorators' => array(
                                                'formElements',
                                                array(array('rows' => 'htmlTag'),
                                                    array(
                                                        'tag' => 'tr',
                                                        'id' => 'itemsLoc')
                                                ),
                                                array(
                                                    'htmlTag',
                                                    array(
                                                        'tag' => 'table',
                                                        'class' => 'table table-hover'
                                                    )
                                                ),
                                            ),
                        'legend' => 'items'
                )
        );

        // Create hidden counter of Items for Javascript
        $counter = $this->createElement('hidden', 'counter', array('decorators' => array('viewHelper')));
        $this->addElement($counter);

        // Creating and adding submit button
        $this->addElement($addForm);
        $this->addForm->setDecorators(array('viewHelper'))
                ->setAttrib('class', 'btn btn-primary')
                ->setAttrib('onClick', 'addInvoice()');
    }

}