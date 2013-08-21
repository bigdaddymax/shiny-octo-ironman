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

        // Somebody tries to access restricted pages but session variable is not set
        if (!$this->session->userName) {
            $request->setControllerName('index');
            $request->setActionName('index');
            return;
        }
        // Prepare variables
        $this->config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');
        $nav = new Zend_Config_Xml(APPLICATION_PATH . '/configs/navigation.xsd', 'nav');
        $this->navigation = new Zend_Navigation($nav);
        $resource = $this->getResource($request);
        try {
            $accessMapper = new Application_Model_AccessMapper($this->session->userId, $this->session->domainId);
        } catch (Exception $e) {
            $request->setControllerName('index');
            $request->setActionName('index');
            return;
        }
        if ($accessMapper->isAllowed($resource) && $resource) {
            return;
        }
        $request->setControllerName('index');
        $request->setActionName('index');
    }

    public function getResource(Zend_Controller_Request_Abstract $request) {
        // Get request parameters
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        $objectType = $request->getParam('objectType', false);

        // Try to find pages in navigation by controller used
//        $pages = $this->navigation->findBy('controller', $controller);
        // If we have general request served by Objects controller check if objectType is correct
        if ($objectType && $controller == 'objects') {
            $page = $this->navigation->findByresource($objectType);
            if (!($page instanceof Zend_Navigation_Page_Mvc)) {
                return false;
            }
        } else {
            // Other pages, other controllers
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
