<?php

class Capex_Plugins_AuthPlugin extends Zend_Controller_Plugin_Abstract {

    private $session;

    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        // Access to Index page, Errors and Authentification is granted to all
        $this->session = new Zend_Session_Namespace('Auth');
        $controller = $request->getControllerName();
        $objectType = $request->getParam('objectType');

        if ('index' == $controller || 'auth' == $controller || 'error' == $controller) {
            return;
        }

        // Somebody tries to access restricted pages but session variable is not set
        if (!$this->session->userName) {
            $request->setControllerName('index');
            $request->setActionName('index');
            return;
        }

        $xml = simplexml_load_file(APPLICATION_PATH . '/configs/navigation.xsd');
        $xpath = '/config/resources/' . $controller;
        if ($objectType) {
            $xpath .= '/objectType/' . $objectType;
        }
        $resource = $xml->xpath($xpath . '/resource');

        try {
            $accessMapper = new Application_Model_AccessMapper($this->session->userId, $this->session->domainId);
        } catch (Exception $e) {
            $request->setControllerName('index');
            $request->setActionName('index');
            return;
        }
        if (!empty($resource[0]) && $accessMapper->isAllowed($resource[0])) {
            return;
        }
        $request->setControllerName('index');
        $request->setActionName('index');
    }

}

?>
