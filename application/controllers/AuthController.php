<?php

/**
 * Description of AuthController
 *
 * @author Olenka
 */
class AuthController extends Zend_Controller_Action {

    //put your code here
    private $session;
    private $auth;
    private $redirector;
    private $config;

    public function init() {
        $this->session = new Zend_Session_Namespace('Auth');
        $this->auth = new Application_Model_Auth($this->session->domainId);
        $this->redirector = $this->_helper->getHelper('Redirector');
        $this->config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
    }

    public function authAction() {
        $params = $this->getRequest()->getPost();
//        Zend_Debug::dump($params);
        if (!empty($params) && is_array($params)) {
            $user = $this->auth->findUser($params['login']);
            if (false !== $user) {
                if ($this->auth->checkUserPassword($params['login'], $params['password'])) {
                    // User authentificated successfully
                    $this->session->auth = 1;
                    $this->session->userId = $user->userId;
                    $this->session->userName = $user->userName;
                    $this->session->login = $user->login;
                    $this->session->domainId = $user->domainId;
                    $objectsManager = new Application_Model_ObjectsManager($user->domainId);
                    $this->session->role = $objectsManager->getUserGroupRole($user);
                    $this->session->records_per_page = $this->config->records->perpage;
                    $this->session->lang = $this->config->app->default->locale;
                }
            }
        }
        $this->redirector->gotoSimple('index', 'index');
    }

    public function logoffAction() {
        Zend_Session::destroy();
        $this->redirector->gotoSimple('index', 'index');
    }

}

?>
