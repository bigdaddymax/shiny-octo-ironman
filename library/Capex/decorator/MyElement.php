<?php
/**
 * Little add-on to standart HtmlTag decorator that anables to add Bootstrap highlighting
 * of correct and/or errored fieald after form validation.
 */
class Capex_Decorator_MyElement extends Zend_Form_Decorator_HtmlTag {
    
    public function render($content) {
        $element = $this->getElement();
        $messages = $element->getMessages();
        $value = $element->getValue();
        
        if ($messages) {
            $class = $this->getOption('class');
            $this->setOption('class', 'has-error '. $class);
        }
        
        if (!empty($value) && empty($messages) && !('Zend_Form_Element_Select' == $element->getType() && -1 == $value)) {
            $class = $this->getOption('class');
            $this->setOption('class', 'has-success ' . $class);
        }
       return parent::render($content);
    }
}