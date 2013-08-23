<?php


class Capex_Decorator_CapexFormErrors extends Zend_Form_Decorator_Abstract {

    Public function render($content) {

        $element = $this->getElement();
        if (!$element instanceof Zend_Form_Element) {
            return $content;
        }

        if (null === $element->getView()) {
            return $content;
        }

        $seperator = $this->getSeparator();
        $placement = $this->getPlacement();
        $errors = $this->buildErrors();

        switch ($placement) {
            case (self::PREPEND):
                return $errors . $seperator . $content;
                break;
            case (self::APPEND):
            default:
                return $content . $seperator . $errors;
                break;
        }
    }

    function buildErrors() {

        $element = $this->getElement();
        $messages = $element->getMessages();
        $class = $this->getOption('class');
        if ($class) {
            $class = 'class="' . $class . '"';
        }

        if (empty($messages)) {
            return '';
        }
        $message = array_pop($messages);

        return '<div ' . $class . '>' . $message . '</div>';
    }

}