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
        $this->auth = new Application_Model_Auth();
        $this->session = new Zend_Session_Namespace('Auth');
        $this->redirector = $this->_helper->getHelper('Redirector');
        $this->config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini','production');
     }

    public function authAction() {
        $params = $this->getRequest()->getPost();
//        Zend_Debug::dump($params);
        if (!empty($params) && is_array($params)) {
            $user = $this->auth->findUser($params['login']);
            if (false !== $user) {
                if ($this->auth->checkUserPassword($user->userId, $params['password'])) {
                    // User authentificated successfully
                    $this->session->auth = 1;
                    $this->session->userId = $user->userId;
                    $this->session->userName = $user->userName;
                    $this->session->login = $user->login;
                    $this->session->domainId = $user->domainId;
                    $this->session->admin = 0;
                    $this->redirector->gotoSimple('index', 'objects');
                }
            } else {
                // User login not found, lets check if this is not default admin login attempt
                        $this->session->domainId = 1;
                $checkGroups = $this->auth->getAllObjects('Application_Model_UserGroup', array(0=>array('column'=>'role',
                                                                                               'operand'=> 'admin')));
                if (is_array($checkGroups) && !empty($checkGroups)) {
                    // There are users with admin privileges in database, default admin login disabled
                    Zend_Session::destroy();
                } else {
                    if ($this->config->default->adminlogin == $params['login'] && $this->config->default->adminpass == $params['password']) {
                        // Fresh configuration, default admin login allowed
                        $this->session->auth = 1;
                        $this->session->userName = 'Default Admin';
                        $this->session->login = $this->config->default->adminlogin;
//+++++++++++++++++++ FIXME   FIXME FIXME FIXME FIXME ++++++++++++++++++++++++++++++++++++++
                        $this->session->domainId = 1;
                        $this->session->admin = 1;
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                    }
                }
            }
//            exit;
        }
    }

    public function logoffAction() {
        Zend_Session::destroy();
        $this->redirector->gotoSimple('index', 'index');
    }

}

?>
