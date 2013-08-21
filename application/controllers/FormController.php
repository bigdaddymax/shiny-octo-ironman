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
        if (null != $this->_request->getParam('formId')) {
            $this->view->form = $formsManager->prepareFormForOutput($this->_request->getParam('formId'), $this->session->userId);
        }
        $access = new Application_Model_AccessMapper($this->session->userId, $this->session->domainId);
        $allowedObjects = $access->getAllowedObjectIds();
        $this->view->elements = $formsManager->getAllObjects('Element');
        $this->view->expgroup = $this->config->expences->group->toArray();
        if (!empty($allowedObjects['write'])) {
            $this->view->nodes = $formsManager->getAllObjects('Node', array(0 => array('column' => 'nodeId',
                    'condition' => 'IN',
                    'operand' => $allowedObjects['write'])));
        }
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
