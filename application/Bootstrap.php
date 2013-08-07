<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    protected function _initPlugins() {
        $frontController = Zend_Controller_Front::getInstance();
        $frontController->registerPlugin(new Capex_Plugins_AuthPlugin());
        Zend_Session::start();
    }

    protected function _initTranslate() {
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
        $locale = $config->app->default->locale;

        // Create Session block and save the locale
        $session = new Zend_Session_Namespace('Auth');
        $langLocale = isset($session->lang) ? $session->lang : $locale;

        // Set up and load the translations (all of them!)
        $translate = new Zend_Translate(array(
            'adapter'=>'array',
            'content'=> APPLICATION_PATH . '/../library/Capex/lang/' . $langLocale . '/translation.php',
            'locale'=>$langLocale
        ));

        //$translate->setLocale($langLocale); // Use this if you only want to load the translation matching current locale, experiment.
        // Save it for later
        $registry = Zend_Registry::getInstance();
        $registry->set('Zend_Translate', $translate);
    }

}

