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
                $this->{$key} = (strpos($key, 'Id') || 'active' == $key) ? (int) $item : $item;
            }
        }
    }

    public function __set($name, $value) {
        if ('valid' == $name) {
            echo 'Cannot set value for "valid" property';
        } elseif (property_exists($this, '_' . $name)) {
            $name1 = '_' . $name;
            $this->$name1 = (strpos($name, 'Id') || 'active' == $name) ? (int) $value : $value;
        }
    }

    public function __get($name) {
        if (property_exists($this, '_' . $name)) {
            $name = '_' . $name;
            return (strpos($name, 'Id')) ? (int) $this->$name : $this->$name;
        } else {
            throw new NonExistingObjectProperty('Trying to get "' . $name . ' Property doesnt exist');
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
                    $output[str_replace('_', '', $key)] = (strpos($key, 'Id') || 'active' == $key) ? (int) $value : $value;
                }
            }
        }
        return $output;
    }

}

?>
