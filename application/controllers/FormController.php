<?php

/**
 * Description of FormController
 *
 * @author Olenka
 */
class FormController extends Zend_Controller_Action {

    private $session;
    private $redirector;
    private $config;

    public function init() {
        $this->session = new Zend_Session_Namespace('Auth');
        $this->redirector = $this->_helper->getHelper('Redirector');
        $this->config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
    }

    public function indexAction() {
        $access = new Application_Model_AccessMapper($this->session->userId, $this->session->domainId);
        $allowedObjects = $access->getAllowedObjectIds();
        $formManager = new Application_Model_FormsManager($this->session->domainId);
        $accessFilter = $formManager->createAccessFilterArray($this->session->userId);
        if ($accessFilter) {
            $this->view->pages = $formManager->getNumberOfPages('form', $accessFilter, $this->session->records_per_page);
            if ($this->_request->getParam('page')) {
                $accessFilter['LIMIT']['start'] = ((int) $this->_request->getParam('page') - 1) * $this->session->records_per_page;
                $accessFilter['LIMIT']['number'] = $this->session->records_per_page;
                $this->view->currentPage = $this->_request->getParam('page');
            } else {
                $accessFilter['LIMIT']['start'] = 0;
                $accessFilter['LIMIT']['number'] = $this->session->records_per_page;
            }
            if (!$this->view->currentPage) {
                $this->view->currentPage = 1;
            }
            $res = $formManager->getAllObjects('form', $accessFilter);
            if (is_array($res)) {
                foreach ($res as $form) {
                    $forms[] = $formManager->prepareFormForOutput($form->formId, $this->session->userId);
                }
            }
        } else {
            $forms = false;
        }
        ($forms === false) ? $this->view->forms = 'No forms' : $this->view->forms = $forms;
        $this->view->elements = $formManager->getAllObjects('Element');
        if (!empty($allowedObjects['write'])) {
            $this->view->nodes = $formManager->getAllObjects('Node', array(0 => array('column' => 'nodeId',
                    'condition' => 'IN',
                    'operand' => $allowedObjects['write'])));
        }
//               Zend_Debug::dump($this->view->nodes);
    }

    public function editFormAction() {

        $formsManager = new Application_Model_FormsManager($this->session->domainId);

        $form = new Zend_Form();
        $form->addElementPrefixPath('Capex_Decorator', 'Capex/decorator', 'decorator');

        $registry = Zend_Registry::getInstance();
        $translator = $registry->get('Zend_Translate');
        $form->setTranslator($translator);

        // Prepare expences group array for Select element creation
        $expgroupArray = $this->config->expences->group->toArray();
        $groups = array(-1 => 'expgroup');
        foreach ($expgroupArray as $exptype) {
            $groups[$exptype] = $exptype;
        }

        // Prepare allowed nodes array for select element creation
        $access = new Application_Model_AccessMapper($this->session->userId, $this->session->domainId);
        $allowedObjects = $access->getAllowedObjectIds();
        if (!empty($allowedObjects['write'])) {
            $nodeArray = $formsManager->getAllObjects('Node', array(0 => array('column' => 'nodeId',
                    'condition' => 'IN',
                    'operand' => $allowedObjects['write'])));
        }
        $nodes = array(-1 => 'deptmnt');
        if (!empty($nodeArray)) {
            foreach ($nodeArray as $node) {
                $nodes[$node->nodeId] = $node->nodeName;
            }
        }

        // Creating and setting main form elements
        $formName = $form->createElement('text', 'formName');
        $formName->addValidator('alnum')
                ->addValidator('StringLength', 4)
                ->setRequired(true)
                ->setAttrib('class', 'form-control')
                ->setAttrib('id', 'formName')
                ->setAttrib('name', 'formName')
                ->setAttrib('placeholder', $translator->translate('form name'))
                ->setLabel('form name');
        $contragentName = $form->createElement('text', 'contragentName');
        $contragentName->addValidator('alnum')
                ->addValidator('StringLength', 4)
                ->setRequired(true)
                ->setAttrib('class', 'form-control')
                ->setAttrib('id', 'contragentName')
                ->setAttrib('name', 'contragentName')
                ->setAttrib('placeholder', $translator->translate('contragent'))
                ->setLabel('contragent');
        $expgroup = $form->createElement('select', 'expgroup', array('multiOptions' => $groups, 'disable' => array(-1)));
        $expgroup->setAttrib('class', 'form-control')
                ->setAttrib('id', 'expgroup')
                ->setAttrib('name', 'expgroup')
                ->setValue(-1)
                ->setLabel('expgroup')
                ->setRequired(true);
        $nodeId = $form->createElement('select', 'nodeId', array('multiOptions' => $nodes, 'disable' => array(-1)));
        $nodeId->setAttrib('class', 'form-control')
                ->setAttrib('id', 'nodeId')
                ->setAttrib('name', 'nodeId')
                ->setValue(-1)
                ->setLabel('deptmnt')
                ->setRequired(true);
        $addForm = $form->createElement('submit', 'addForm');
        $addForm->setIgnore(true)
                ->setLabel('add form');

        $form->addElement($formName)
                ->addElement($contragentName)
                ->addElement($expgroup)
                ->addElement($nodeId)
                ->setAttrib('role', 'form');

        $form->setElementDecorators(array('viewHelper',
            array('CapexFormErrors', array('placement' => 'prepend', 'class' => 'error')),
            array('label', array('class' => 'control-label')),
            array('MyElement', array('tag' => 'div', 'class' => 'form-group'))));

        // Creating and adding fieldset for Items
        // Prepare placeholder for Items - we will add items to this table with Javascript
        $itemsPlace = $form->createElement('text', 'itemsLoc', array('decorators' => array(
                array('Callback',
                    array('callback' => function() {
                            return '<tr><th class="col-lg-2">item name</th><th class="col-lg-2">expence type</th><th class="col-lg-2">value</th><th></th></tr>';
                        }
                    )
                )
            )
                )
        );
        $itemName = $form->createElement('text', 'itemName', array('Decorators' => array('viewHelper',
                array('CapexFormErrors', array('placement' => 'prepend', 'class' => 'error')),
                array('label', array('class' => 'control-label')),
                array('MyElement', array('tag' => 'td', 'class' => 'form-group col-lg-2')))));
        $itemName->setAttrib('class', 'form-control')
                ->setAttrib('id', 'itemName')
                ->setAttrib('name', 'itemName')
                ->setAttrib('placeholder', 'item');
        $expType = $form->createElement('select', 'expType', array('Decorators' => array('viewHelper',
                array('CapexFormErrors', array('placement' => 'prepend', 'class' => 'error')),
                array('label', array('class' => 'control-label')),
                array('MyElement', array('tag' => 'td', 'class' => 'form-group col-lg-2')))));
        $expType->setOptions(array('multiOptions' => array('test' => 'test')))
                ->setAttrib('class', 'form-control');
        $value = $form->createElement('text', 'value', array('Decorators' => array('viewHelper',
                array('CapexFormErrors', array('placement' => 'prepend', 'class' => 'error')),
                array('label', array('class' => 'control-label')),
                array('MyElement', array('tag' => 'td', 'class' => 'form-group col-lg-2')))));
        $value->setAttrib('class', 'form-control')
                ->setAttrib('id', 'value')
                ->setAttrib('name', 'value')
                ->setAttrib('placeholder', 'value');
        $addItem = $form->createElement('button', 'addItem', array('decorators'=> array('viewHelper', array('HtmlTag', array('tag'=>'td')))));
        $form->addDisplayGroup(array($itemsPlace, $itemName, $expType, $value, $addItem), 'items', array('decorators' => array('formElements', array('htmlTag', array('tag'=>'<table>', 'class'=>'table table-hover')),  'fieldset'),
            'legend' => 'items'));

        // Creating and adding submit button
        $form->addElement($addForm);
        $form->addForm->setDecorators(array('viewHelper'))
                ->setAttrib('class', 'btn btn-primary');

        $form->setMethod('post');
        $form->setAction($this->view->url(array('controller' => 'form', 'action' => 'edit-form'), null, true));
        // We edit existion form, populate values
        if (null != $this->_request->getParam('formId')) {
//            $this->view->form = $formsManager->prepareFormForOutput($this->_request->getParam('formId'), $this->session->userId);
        }
        if ($this->_request->isPost()) {
            // $form->isValid($this->_request->getParams());
            foreach ($form->getElements() as $element) {
                if ($element instanceof Zend_Form_Element) {
                    if (!$element->isValid($this->_request->getParam($element->getName()))) {
                        if ($element instanceof Zend_Form_Element_Select) {
                            $element->setValue(-1);
                        }
                    }
                }
            }
        }
        $this->view->form = $form;
    }

    public function previewFormAction() {
        $formManager = new Application_Model_FormsManager($this->session->domainId);
        $this->view->form = $formManager->prepareFormForOutput((int) $this->getRequest()->getParam('formId'), $this->session->userId);
    }

    public function addFormAction() {
        $params = $this->getRequest()->getPost();
        $params['userId'] = $this->session->userId;
        $params['domainId'] = $this->session->domainId;
        $contragent = new Application_Model_Contragent(array('contragentName' => $this->_request->getParam('contragentName'), 'domainId' => $this->session->domainId));
        $formManager = new Application_Model_FormsManager($this->session->domainId);
        $params['contragentId'] = $formManager->saveObject($contragent);
        $form = new Application_Model_Form($params);
        if ($form->isValid()) {
            $this->_helper->json(array('error' => 0, 'message' => 'Form created', 'formId' => $formManager->saveObject($form)), true);
        } else {
            $this->_helper->json(array('error' => 1, 'message' => 'Form is not valid'), true);
        }

        $this->redirector->gotoSimple('index', 'form');
    }

    public function publishFormAction() {
        if (null != $this->_request->getParam('formId')) {
            $formsManager = new Application_Model_FormsManager($this->session->domainId);
            try {
                $form = $formsManager->getObject('form', $this->_request->getParam('formId'), $this->session->userId);
                $form->public = 1;
                $id = $formsManager->saveObject($form);
                $this->_helper->json(array('error' => 0,
                    'message' => 'Form published successfully',
                    'code' => 200,
                    'formId' => $id));
            } catch (Exception $e) {
                $this->_helper->json(array('error' => 1,
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(), 'userId' => $this->session->userId, 'trace' => $e->getTrace()));
            }
        }
        $this->redirector->gotoSimple('index', 'form');
    }

    public function openFormAction() {
        if ($this->_request->isGet()) {
            $formManager = new Application_Model_FormsManager($this->session->domainId);
            $this->view->form = $formManager->prepareFormForOutput((int) $this->getRequest()->getParam('formId'), $this->session->userId);
            $this->view->approved = $formManager->getApprovalStatus((int) $this->getRequest()->getParam('formId'));
            try {
                $this->view->showApproval = $formManager->isApprovalAllowed((int) $this->getRequest()->getParam('formId'), $this->session->userId);
            } catch (Exception $e) {
                $this->view->showApproval = false;
            }
            $this->view->comments = $formManager->prepareCommentsForOutput((int) $this->getRequest()->getParam('formId'));
            $this->_helper->layout()->disableLayout();
//            $this->_helper->viewRenderer->setNoRender(true);
//            $this->_helper->json(array('form'=>$this->view->form,
//                                       'approved'=>$this->view->approved,
//                                       'showApproval'=>$this->view->showApproval));
        }
    }

    public function approveAction() {
        try {
            $formsManager = new Application_Model_FormsManager($this->session->domainId);
            $id = $formsManager->approveForm($this->_request->getParam('formId'), $this->session->userId, 'approve');
            $emails = $formsManager->getEmailingList($this->_request->getParam('formId'), 'approve');
            if (isset($emails['owner'])) {
                $body = $formsManager->createEmailBody($emails['owner'], 'approved_owner', $this->session->lang, $this->_request->getParam('formId'));
                $body = str_replace('%link%', $this->_helper->url(array('controller' => 'forms',
                            'action' => 'open-form',
                            'formId' => $this->_request->getParam('formId')
                                )
                        ), $body
                );
                $subject = $formsManager->createEmailBody($emails['owner'], 'approved_owner_subj', $this->session->lang, $this->_request->getParam('formId'));
                $formsManager->sendEmail($emails['owner'], $body, $subject);
            }
            if (isset($emails['other'])) {
                $body = $formsManager->createEmailBody($emails['other'][0], 'approved_next', $this->session->lang, $this->_request->getParam('formId'));
                $body = str_replace('%link%', $this->_helper->url(array('controller' => 'forms',
                            'action' => 'open-form',
                            'formId' => $this->_request->getParam('formId')
                                )
                        ), $body
                );
                $subject = $formsManager->createEmailBody($emails['other'][0], 'approved_next_subj', $this->session->lang, $this->_request->getParam('formId'));
                $formsManager->sendEmail($emails['other'][0], $body, $subject);
            }

            $this->_helper->json(array('error' => 0, 'message' => 'Approved successfully', 'code' => 200, 'recordId' => $id));
        } catch (Exception $e) {
            $this->_helper->json(array('error' => 1, 'message' => $e->getMessage(), 'code' => $e->getCode(), 'trace' => $e->getTraceAsString()));
        }
        $this->redirector->gotoSimple('index', 'form');
    }

    public function declineAction() {
        try {
            $formsManager = new Application_Model_FormsManager($this->session->domainId);
            $id = $formsManager->approveForm($this->_request->getParam('formId'), $this->session->userId, 'decline');
            $emails = $formsManager->getEmailingList($id);
            $formsManager->sendEmails($emails, 'declined');
            $this->_helper->json(array('error' => 0, 'message' => 'Declined successfully', 'code' => 200, 'recordId' => $id));
        } catch (Exception $e) {
            $this->_helper->json(array('error' => 1, 'message' => $e->getMessage(), 'code' => $e->getCode()));
        }
        $this->redirector->gotoSimple('index', 'form');
    }

    function addCommentAction() {
        $formsManager = new Application_Model_FormsManager($this->session->domainId);
        $params = $this->_request->getParams();
        $params['userId'] = $this->session->userId;
        $params['parentCommentId'] = -1;
        $comment = new Application_Model_Comment($params);
        $comment->date = date('Y-m-d H:i');
        $comment->domainId = $this->session->domainId;
        $commentId = $formsManager->saveObject($comment);
    }

    function updateElementsAction() {
        $expGroup = $this->_request->getParam('expgroup');
        $formsManager = new Application_Model_FormsManager($this->session->domainId);
        $this->view->elements = $formsManager->getAllObjects('element', array(0 => array('column' => 'expgroup', 'operand' => $expGroup)));
        $this->_helper->layout()->disableLayout();
    }

}

?>
