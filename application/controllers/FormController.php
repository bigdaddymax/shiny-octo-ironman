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
        $objectsManager = new Application_Model_ObjectsManager();
        $access = new Application_Model_AccessMapper($this->session->userId);
        $allowedObjects = $access->getAllowedObjectIds();
        $forms = $objectsManager->getAllForms();
        ($forms === false) ? $this->view->forms = 'No forms' : $this->view->forms = $forms;
        $dataMapper = new Application_Model_DataMapper();
        $this->view->elements = $dataMapper->getAllObjects('Application_Model_Element');
        if (!empty($allowedObjects['write'])) {
            $this->view->nodes = $dataMapper->getAllObjects('Application_Model_Node', array(0 => array('column' => 'nodeId',
                    'condition' => 'IN',
                    'operand' => $allowedObjects['write'])));
        }
//               Zend_Debug::dump($this->view->nodes);
    }

    public function addFormAction() {
        $params = $this->getRequest()->getPost();
        $params['userId'] = $this->session->userId;
        $form = new Application_Model_Form($params);
        if ($form->isValid()) {
            $objectsManager = new Application_Model_ObjectsManager();
            $this->view->newFormId = $objectsManager->SaveFormData($form);
        } else {
            $this->view->error = 'Cannot create form';
            $this->view->form = $form;
        }
        $this->redirector->gotoSimple('index', 'form');
    }

    public function openFormAction() {
        if ($this->_request->isGet()) {
            $param = $this->getRequest()->getParam('formId');
            $objectManager = new Application_Model_ObjectsManager();
            $this->view->form = $objectManager->prepareFormForOutput((int)$param);
        }
    }

}

?>
