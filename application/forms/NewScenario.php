<?php

class Application_Form_NewScenario extends Zend_Form {

    public function __construct($params) {
        $registry = Zend_Registry::getInstance();
        $translate = $registry->get('Zend_Translate');
        $this->setTranslator($translate);

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
        $scenarioValidator->setMessage("Scenario '%value%' is already registered");

        $scenarioName->addValidator('alnum', true, array('allowWhiteSpace' => true))
                ->addValidator($scenarioValidator)
                ->setRequired(true);

        $this->addElement($scenarioName);

        $this->scenarioName->setLabel('scenario name')
                ->setAttrib('class', 'form-control')
                ->setAttrib('placeholder', $translate->_('scenario name'));


        $this->addElementPrefixPath('Capex_Decorator', 'Capex/decorator', 'decorator');
        $this->setElementDecorators(array('viewHelper',
            array('CapexFormErrors', array('placement' => 'prepend', 'class' => 'error')),
            array('label', array('class' => 'control-label')),
            array('MyElement', array('tag' => 'div', 'class' => 'form-group'))));


        $userId = $this->createElement('select', 'userId');
        $userId->addMultiOption('-1', $translate->_('user name'));
        foreach ($params['users'] as $user) {
            $userId->addMultiOption($user->userId, $user->userName);
        }
        $userId->setLabel('user name')
                ->setAttrib('class', 'form-control')
                ->setValue(-1)
                ->setOptions(array('disable' => array(-1)))
                ->setDecorators(array('ViewHelper', array('htmlTag', array('tag' => 'td'))));


        $addUserButton = $this->createElement('button', 'addUserButton')
                ->setAttrib('class', 'btn btn-info')
                ->setAttrib('onClick', 'addUser()')
                ->setDecorators(array('ViewHelper', array('htmlTag', array('tag' => 'td'))));

        // If we deal with existing scenario open table and add users before <select>
        $closeOnly = false;
        if (isset($params['scenario'])) {
            foreach ($params['users'] as $user) {
                $userArray[$user->userId] = $user->userName;
            }
            foreach ($params['scenario']->entries as $entry) {
                $displayGroup[] = $this->createElement('text', $entry->orderPos, array(
                    'decorators' => array(
                        array('Callback', array('callback' => function() use ($entry, $userArray) {
                                    return '<tr id="' . $entry->orderPos . '"><td><a href="">&times</a></td><td>' . $userArray[$entry->userId] . '<input type="hidden" name="orderPos_' . $entry->userId . '" value="' . $entry->orderPos . '"></td></tr>';
                                })
                        )
                    )
                        )
                );
            }
            $this->addDisplayGroup($displayGroup, 'currentUsers', array(
                'decorators' => array(
                    'formElements',
                    array(
                        'HtmlTag',
                        array(
                            'tag' => 'table',
                            'class' => 'table table-hover',
                            'openOnly' => true
                        )
                    )
                )
                    )
            );
            $closeOnly = true;
        }

        // Insert into form subform for adding users to scenario
        $this->addDisplayGroup(array($userId, $addUserButton), 'users', array(
            'decorators' => array(
                'formElements',
                array(array('rows' => 'htmlTag'),
                    array(
                        'tag' => 'tr',
                        'id' => 'usersLoc')
                ),
                array(
                    'htmlTag',
                    array(
                        'tag' => 'table',
                        'class' => 'table table-hover',
                        'closeOnly' => $closeOnly
                    )
                ),
            )
                )
        );

        $counter = $this->createElement('hidden', 'counter', array('decorators' => array('ViewHelper')));
        $this->addElement($counter);

        $save = $this->createElement('button', 'save');
        $save->setIgnore(true);
        $this->addElement($save);
        $this->save->setAttrib('class', 'btn btn-danger')
                ->setAttrib('onClick', 'addScenario()');

        $this->save->setDecorators(array('viewHelper'))
                ->setAttrib('class', 'btn btn-danger');
        $this->setAttrib('role', 'form')
                ->setAttrib('class', 'form-horisontal')
                ->setDecorators(array('FormElements', 'Form'))
                ->setAttrib('id', 'new-scenario');

        if (isset($params['scenario'])) {
            $scenarioId = $this->createElement('hidden', 'scenarioId', array('decorators' => array('ViewHelper')));
            $this->addElement($scenarioId);
            $this->setDefaults(array(
                'scenarioName' => $params['scenario']->scenarioName,
                'scenarioId' => $params['scenario']->scenarioId,
                'counter' => count($params['scenario']->entries)
                    )
            );
        }
    }

}

?>