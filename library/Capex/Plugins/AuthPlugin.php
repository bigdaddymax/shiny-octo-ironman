<?php

class Capex_Plugins_AuthPlugin extends Zend_Controller_Plugin_Abstract {

    private $config;
    private $session;
    private $navigation;

    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        // Access to Index page, Errors and Authentification is granted to all
        $this->session = new Zend_Session_Namespace('Auth');
        $controller = $request->getControllerName();
        if ('index' == $controller || 'auth' == $controller || 'error' == $controller) {
            return;
        }
        // Prepare variables
        $this->config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');
        $nav = new Zend_Config_Xml(APPLICATION_PATH . '/configs/navigation.xsd', 'nav');
        $this->navigation = new Zend_Navigation($nav);
        $resource = $this->getResource($request);
        $accessMapper = new Application_Model_AccessMapper($this->session->userId, $this->session->domainId);
        if ($accessMapper->isAllowed($resource) && $resource) {
            return;
        }
        $request->setControllerName('index');
        $request->setActionName('index');
    }

    public function getResource(Zend_Controller_Request_Abstract $request) {
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        $objectType = $request->getParam('objectType', false);

        $pages = $this->navigation->findBy('controller', $controller);

        if ($objectType && $controller == 'objects') {
            $page = $this->navigation->findByresource($objectType);
            if (!($page instanceof Zend_Navigation_Page_Mvc)) {
                return false;
            }
        } else {
            $pages = $this->navigation->findAllBy('controller', $controller);
//                Zend_Debug::dump($page);
            if (count($pages) == 1) {
                $page = array_pop($pages);
                if (!($page instanceof Zend_Navigation_Page_Mvc)) {
                    return false;
                }
            } else {
                $container = new Zend_Navigation($pages);
                $page = $container->findOneByAction($action);
                if (!($page instanceof Zend_Navigation_Page_Mvc)) {
                    return false;
                }
            }
        }
        return $page->resource;
    }

}

?>
