<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }
    
    public function newUserAction(){
        $session = new Zend_Session_Namespace('Auth');
        if ($this->getRequest()->getParam('userName')){
            $session->newUser['userName'] = $this->getRequest()->getParam('userName');
        } else {
            throw new Exception('No user name provided', 400);
        }
        if ($this->getRequest()->getParam('login')){
            $session->newUser['login'] = $this->getRequest()->getParam('login');
        } else {
            throw new Exception('No login provided', 400);
        }
        if ($this->getRequest()->getParam('email')){
            $session->newUser['email'] = $this->getRequest()->getParam('email');
        } else {
            throw new Exception('No email provided', 400);
        }
        if ($this->getRequest()->getParam('password')){
            $session->newUser['password'] = $this->getRequest()->getParam('password');
        } else {
            throw new Exception('No password provided', 400);
        }
        
    }

    public function newDomainAction(){
        if (!$this->getRequest()->getParam('domainName')){
            throw new Exception('No domain name provided', 400);
        }
        $session = new Zend_Session_Namespace('Auth');
        $dataMapper = new Application_Model_DataMapper(-1);
        if ($session->newUser['userName'] && $session->newUser['login'] && $session->newUser['email'] && $session->newUser['password']){
            $domain = new Application_Model_Domain($this->getRequest()->getParams());
            $domainId = $dataMapper->saveObject($domain);
            $user = new Application_Model_User(array('userName'=>$session->newUser['userName'],
                                                     'login'=>$session->newUser['login'],
                                                     'password'=>$session->newUser['password'],
                                                     'domainId'=>$domainId, 
                                                     'positionId'=>-1));
            $userId = $dataMapper->saveObject($user);
            $userGroup = new Application_Model_UserGroup(array('userId'=>$userId,
                                                               'userGroupName'=>'admin',
                                                               'role'=>'admin',
                                                               'domainId'=>$domainId));
            $userGroupId = $dataMapper->saveObject($userGroup);
            $data['domainId'] = $domainId;
            $data['userId'] = $userId;
            $data['userGroup'] = $userGroupId;
            $this->view->data = $data;
        } else {
            throw new Exception('User data not complete', 400);
        }
    }

}

