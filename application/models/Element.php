<?php

/**
 * Description of Element
 *
 * "Element" is a class that represent particular type of entries that could be processed by the system.
 * Examples of elements could be: capex, opex, salary, agreement, particular spending (car service expences, office rent, materials, office supplies etc)
 * 
 * @author Max
 */
class Application_Model_Element {

    private $_valid = true;
    private $_elementName;
    private $_elementCode;  // Code can be used for approvals categorisation, analizys and export to external software
    private $_elementComment;
    private $_elementId;
    private $_active = 1;
    private $_domainId;
    private $_expgroup;

    public function __construct(array $elementArray = null) {
         if (is_array($elementArray)) {
            foreach ($elementArray as $key => $item) {
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
     *  Function that returns status of Element instance. We consider Element as valid if element 
     *  has correctly set elementName, elementCode
     * @return type
     */
    public function isValid() {
        if (isset($this->_elementCode) && isset($this->_elementName) && isset($this->_domainId) && isset($this->_expgroup)) {
            $this->_valid = true;
        } else {
            $this->_valid = false;
        }
        return $this->_valid;
    }

    /**
     * Returns array of properties of element.
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
