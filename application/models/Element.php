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
    private $_active = true;
    private $_domainId;

    public function __construct(array $elementArray = null) {
        if (isset($elementArray['elementName'])) {
            $this->_elementName = $elementArray['elementName'];
        }

        if (isset($elementArray['domainId'])) {
            $this->domainId = (int) $elementArray['domainId'];
        }
        if (isset($elementArray['active'])) {
            $this->_active = (bool) $elementArray['active'];
        }

        if (isset($elementArray['elementComment']))
            $this->_elementComment = $elementArray['elementComment'];

        if (isset($elementArray['elementCode'])) {
            $this->_elementCode = (int) $elementArray['elementCode'];
        }

        if (isset($elementArray['elementId'])) {
            $this->_elementId = (int) $elementArray['elementId'];
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
     *  Function that returns status of Element instance. We consider Element as valid if element 
     *  has correctly set elementName, elementCode
     * @return type
     */
    public function isValid() {
        if (isset($this->_elementCode) && isset($this->_elementName) && isset($this->_domainId)) {
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
                    $output[str_replace('_', '', $key)] = $value;
                }
            }
        }
        return $output;
    }

}

?>
