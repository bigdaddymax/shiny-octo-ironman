<?php

/**
 * Description of Template
 *
 * @author Max
 */
class Application_Model_Template {

    private $_valid = true;
    private $_templateName;
    private $_language;
    private $_body;
    private $_type;
    private $_templateId;
    private $_active = true;
    private $_domainId;

    public function __construct(array $templateArray = null) {
         if (is_array($templateArray)) {
            foreach ($templateArray as $key => $item) {
                if (strpos($key, 'Id')) {
                    $this->{$key} = (int) $item;
                } else {
                    $this->{$key} = $item;
                }
            }
        }
    }

    public function __set($name, $value) {
        if ('valid' == $name) {
            echo 'Cannot set value for "valid" property';
        } elseif (property_exists($this, '_' . $name)) {
            $name1 = '_' . $name;
            $this->$name1 = $value;
        } else {
            echo 'Cannot set value. Property ' . $name . ' doesnt exist';
        }
    }

    public function __get($name) {
        if (property_exists($this, '_' . $name)) {
            $name = '_' . $name;
            return $this->$name;
        } else {
            return 'Cannot get value. Property ' . $name . ' doesnt exist';
        }
    }

    /**
     *  Function that returns status of Element instance. We consider Element as valid if Template 
     *  has correctly set TemplateName, TemplateCode
     * @return type
     */
    public function isValid() {
        if (isset($this->_body) && isset($this->_domainId) && isset($this->_type) && isset($this->_language) && isset($this->_templateName)) {
            $this->_valid = true;
        } else {
            $this->_valid = false;
        }
        return $this->_valid;
    }

    /**
     * Returns array of properties of Template.
     * @return type
     */
    public function toArray() {
        $output = array();
        foreach ($this as $key => $value) {
            if ('_valid' != $key) {
                if (isset($value)) {
                    $output[str_replace('_', '', $key)] = $value;
                }
            }
        }
        return $output;
    }

}

?>
