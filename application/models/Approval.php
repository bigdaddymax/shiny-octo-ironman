<?php

/**
 * Description of Element
 *
 * "Element" is a class that represent particular type of entries that could be processed by the system.
 * Examples of elements could be: capex, opex, salary, agreement, particular spending (car service expences, office rent, materials, office supplies etc)
 * 
 * @author Max
 */
class Application_Model_Approval {

    private $_valid = true;
    private $_approvalId;
    private $_active = true;
    private $_domainId;

    public function __construct(array $approvalArray = null) {
        foreach ($approvalArray as $property => $value) {
            if (strpos($property, 'Id')) {
                $this->{$property} = (int) $value;
            } else {
                $this->{$property} = $value;
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
     *  Function that returns status of Element instance. We consider Element as valid if element 
     *  has correctly set elementName, elementCode
     * @return type
     */
    public function isValid() {
        if (isset($this->_domainId)) {
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
