<?php

/**
 * Description of FormController
 *
 * @author Olenka
 */
class FormController extends Zend_Controller_Action {
    //put your code here
    
    public function indexAction() {
        $objectsManager = new Application_Model_ObjectsManager();
        $forms = $objectsManager->getAllForms();
        ($forms === false)?$this->view->forms = 'No forms':$this->view->forms = $forms;
        $dataMapper = new Application_Model_DataMapper();
        $this->view->elements = $dataMapper->getAllObjects('Application_Model_Element');
        $this->view->orgobjects = $dataMapper->getAllObjects('Application_Model_Orgobject');
    }
    
    public function addFormAction(){
        $params = $this->getRequest()->getPost();
        $params['userId'] = 1;
        $form = new Application_Model_Form($params);
        if ($form->isValid()){
            $objectsManager = new Application_Model_ObjectsManager();
            $this->view->newFormId = $objectsManager->SaveFormData($form);
        } else {
            $this->view->error = 'Cannot create form';
            $this->view->form = $form;
        }
    }
    
    public function openFormAction(){
        
    }
}

?>
