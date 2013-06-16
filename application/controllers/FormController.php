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
        $objectManager = new Application_Model_ObjectsManager($this->session->domainId);
        $accessFilter = $objectManager->createAccessFilterArray($this->session->userId);
        $this->view->pages = $objectManager->getNumberOfPages('form',   $accessFilter, $this->session->records_per_page);
        if ($this->_request->getParam('page')){
            $accessFilter['LIMIT']['start'] =  ((int) $this->_request->getParam('page') - 1) * $this->session->records_per_page;
            $accessFilter['LIMIT']['number'] = $this->session->records_per_page;
            $this->view->currentPage = $this->_request->getParam('page');
        } else {
            $accessFilter['LIMIT']['start'] =  0;
            $accessFilter['LIMIT']['number'] = $this->session->records_per_page;
            
        }
        if (!$this->view->currentPage){
            $this->view->currentPage = 1;
        }
        $forms = $objectManager->getAllForms($accessFilter);
        ($forms === false) ? $this->view->forms = 'No forms' : $this->view->forms = $forms;
        $this->view->elements = $objectManager->getAllObjects('Element');
        if (!empty($allowedObjects['write'])) {
            $this->view->nodes = $objectManager->getAllObjects('Node', array(0 => array('column' => 'nodeId',
                    'condition' => 'IN',
                    'operand' => $allowedObjects['write'])));
        }
//               Zend_Debug::dump($this->view->nodes);
    }

    public function editFormAction() {
        $objectsManager = new Application_Model_ObjectsManager($this->session->domainId);
        if (null != $this->_request->getParam('formId')) {
            $this->view->form = $objectsManager->prepareFormForOutput($this->_request->getParam('formId'), $this->session->userId);
        }
        $access = new Application_Model_AccessMapper($this->session->userId, $this->session->domainId);
        $allowedObjects = $access->getAllowedObjectIds();
        $this->view->elements = $objectsManager->getAllObjects('Element');
        $this->view->expgroup = $this->config->expences->group->toArray();
        if (!empty($allowedObjects['write'])) {
            $this->view->nodes = $objectsManager->getAllObjects('Node', array(0 => array('column' => 'nodeId',
                    'condition' => 'IN',
                    'operand' => $allowedObjects['write'])));
        }
    }

    public function previewFormAction() {
        $objectManager = new Application_Model_ObjectsManager($this->session->domainId);
        $this->view->form = $objectManager->prepareFormForOutput((int) $this->getRequest()->getParam('formId'), $this->session->userId);
    }

    public function addFormAction() {
        $params = $this->getRequest()->getPost();
        $params['userId'] = $this->session->userId;
        $params['domainId'] = $this->session->domainId;
        $contragent = new Application_Model_Contragent(array('contragentName' => $this->_request->getParam('contragentName'), 'domainId' => $this->session->domainId));
        $objectManager = new Application_Model_ObjectsManager($this->session->domainid);
        $params['contragentId'] = $objectManager->saveObject($contragent);
        $form = new Application_Model_Form($params);
        if ($form->isValid()) {
            $this->_helper->json(array('error' => 0, 'message' => 'Form created', 'formId' => $objectManager->saveObject($form)), true);
        } else {
            $this->_helper->json(array('error' => 1, 'message' => 'Form is not valid'), true);
        }

        $this->redirector->gotoSimple('index', 'form');
    }

    public function publishFormAction() {
        if (null != $this->_request->getParam('formId')) {
            $objectsManager = new Application_Model_ObjectsManager($this->session->domainId);
            try {
                $form = $objectsManager->getObject('form', $this->_request->getParam('formId'), $this->session->userId);
                $form->public = 1;
                $id = $objectsManager->saveObject($form);
                $this->_helper->json(array('error' => 0,
                    'message' => 'Form published successfully',
                    'code' => 200,
                    'recordId' => $id));
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
            $objectManager = new Application_Model_ObjectsManager($this->session->domainId);
            $this->view->form = $objectManager->prepareFormForOutput((int) $this->getRequest()->getParam('formId'), $this->session->userId);
            $this->view->approved = $objectManager->getApprovalStatus((int) $this->getRequest()->getParam('formId'));
            $this->view->showApproval = $objectManager->isApprovalAllowed((int) $this->getRequest()->getParam('formId'), $this->session->userId);
            $this->_helper->layout()->disableLayout();
//            $this->_helper->viewRenderer->setNoRender(true);
//            $this->_helper->json(array('form'=>$this->view->form,
//                                       'approved'=>$this->view->approved,
//                                       'showApproval'=>$this->view->showApproval));
        }
    }

    public function approveAction() {
        try {
            $objectsManager = new Application_Model_ObjectsManager($this->session->domainId);
            $id = $objectsManager->approveForm($this->_request->getParam('formId'), $this->session->userId, 'approve');
            $this->_helper->json(array('error' => 0, 'message' => 'Approved successfully', 'code' => 200, 'recordId' => $id));
        } catch (Exception $e) {
            $this->_helper->json(array('error' => 1, 'message' => $e->getMessage(), 'code' => $e->getCode()));
        }
        $this->redirector->gotoSimple('index', 'form');
    }

    public function declineAction() {
        try {
            $objectsManager = new Application_Model_ObjectsManager($this->session->domainId);
            $id = $objectsManager->approveForm($this->_request->getParam('formId'), $this->session->userId, 'decline');
            $this->_helper->json(array('error' => 0, 'message' => 'Declined successfully', 'code' => 200, 'recordId' => $id));
        } catch (Exception $e) {
            $this->_helper->json(array('error' => 1, 'message' => $e->getMessage(), 'code' => $e->getCode()));
        }
        $this->redirector->gotoSimple('index', 'form');
    }

    function addCommentAction() {
        $objectsManager = new Application_Model_ObjectsManager($this->session->domainId);
        $comment = new Application_Model_Comment($this->_request->getParams());
        $comment->date = date('Y-m-d H:i');
        $comment->domainId = $this->session->domainId;
        $commentId = $objectsManager->saveObject($comment);
    }

    function updateElementsAction() {
        $expGroup = $this->_request->getParam('expgroup');
        $objectsManager = new Application_Model_ObjectsManager($this->session->domainId);
        $this->view->elements = $objectsManager->getAllObjects('element', array(0 => array('column' => 'expgroup', 'operand' => $expGroup)));
        $this->_helper->layout()->disableLayout();
    }

}

?>
