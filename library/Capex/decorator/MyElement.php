<?php

class Capex_Decorator_MyElement extends Zend_Form_Decorator_HtmlTag {
    
    public function render($content) {
        $element = $this->getElement();
        $messages = $element->getMessages();
        if ($messages) {
            $class = $this->getOption('class');
            $this->setOption('class', 'has-error '. $class);
        }
        
        if ($element->getValue()) {
            $class = $this->getOption('class');
            $this->setOption('class', 'has-success ' . $class);
        }
       return parent::render($content);
    }
}