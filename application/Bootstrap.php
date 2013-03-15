<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initPlugins() {
        $frontController = Zend_Controller_Front::getInstance();
        $frontController->registerPlugin(new Capex_Plugins_AuthPlugin());
        Zend_Session::start();
    }


}

