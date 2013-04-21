<?php

/**
 * Description of Domain
 *
 * "Domain" is a class that represent particular type of entries that could be processed by the system.
 * Examples of domains could be: capex, opex, salary, agreement, particular spending (car service expences, office rent, materials, office supplies etc)
 * 
 * @author Max
 */
class Application_Model_Domain {

    private $_valid = true;
    private $_domainName;
    private $_domainComment;
    private $_active = true;
    private $_domainId;

    public function __construct(array $domainArray = null) {
        if (is_array($domainArray)) {
            foreach ($domainArray as $key => $item) {
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
     *  Function that returns status of Domain instance. We consider Domain as valid if domain 
     *  has correctly set domainName, domainCode
     * @return type
     */
    public function isValid() {
        if (isset($this->_domainName)) {
            $this->_valid = true;
        } else {
            $this->_valid = false;
        }
        return $this->_valid;
    }

    /**
     * Returns array of properties of domain.
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
