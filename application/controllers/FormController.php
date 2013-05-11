<?php

/**
 * Description of FormController
 *
 * @author Olenka
 */
class FormController extends Zend_Controller_Action {

    private $session;
    private $redirector;

    public function init() {
        $this->session = new Zend_Session_Namespace('Auth');
        $this->redirector = $this->_helper->getHelper('Redirector');
    }

    public function indexAction() {
        $access = new Application_Model_AccessMapper($this->session->userId, $this->session->domainId);
        $allowedObjects = $access->getAllowedObjectIds();
        $objectManager = new Application_Model_ObjectsManager($this->session->domainId);
        $forms = $objectManager->getAllForms($objectManager->createAccessFilterArray($this->session->userId));
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
            $this->_helper->json(array('error' => 0, 'message' => 'Form created', 'formId' => $objectManager->saveForm($form, $this->session->userId)), true);
        } else {
            $this->_helper->json(array('error' => 1, 'message' => 'Form is not valid'), true);
        }

        $this->redirector->gotoSimple('index', 'form');
    }

    public function publishFormAction() {
        if (null != $this->_request->getParam('formId')) {
            $objectsManager = new Application_Model_ObjectsManager($this->session->domainId);
            $form = $objectsManager->getForm($this->_request->getParam('formId'), $this->session->userId);
            $form->public = true;
            $objectsManager->saveForm($form, $this->session->userId);
        }
        $this->redirector->gotoSimple('index', 'form');
    }

    public function openFormAction() {
        if ($this->_request->isGet()) {
            $objectManager = new Application_Model_ObjectsManager($this->session->domainId);
            $this->view->form = $objectManager->prepareFormForOutput((int) $this->getRequest()->getParam('formId'), $this->session->userId);
            $this->view->approved = $objectManager->getApprovalStatus((int) $this->getRequest()->getParam('formId'));
            $this->view->showApproval = $objectManager->isApprovalAllowed((int) $this->getRequest()->getParam('formId'), $this->session->userId);
        }
    }

    public function approveAction() {
        try {
            $objectsManager = new Application_Model_ObjectsManager($this->session->domainId);
            $objectsManager->approveForm($this->_request->getParam('formId'), $this->session->userId, 'approve');
        } catch (Exception $e) {
            echo $e->message;
        }
        $this->redirector->gotoSimple('index', 'form');
    }

    public function declineAction() {
        try {
            $objectsManager = new Application_Model_ObjectsManager($this->session->domainId);
            $objectsManager->approveForm($this->_request->getParam('formId'), $this->session->userId, 'decline');
        } catch (Exception $e) {
            echo $e->message;
        }
        $this->redirector->gotoSimple('index', 'form');
    }

}

?>
