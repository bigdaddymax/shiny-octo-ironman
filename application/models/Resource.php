<?php

/**
 * Description of Element
 *
 * "Element" is a class that represent particular type of entries that could be processed by the system.
 * Examples of resources could be: capex, opex, salary, agreement, particular spending (car service expences, office rent, materials, office supplies etc)
 * 
 * @author Max
 */
class Application_Model_Resource {

    private $_valid = true;
    private $_resourceName;
    private $_resourceId;
    private $_active = true;
    private $_domainId;

    public function __construct(array $resourceArray = null) {
        if (isset($resourceArray['resourceName'])) {
            $this->_resourceName = $resourceArray['resourceName'];
        }

        if (isset($resourceArray['domainId'])) {
            $this->domainId = (int) $resourceArray['domainId'];
        }
        if (isset($resourceArray['active'])) {
            $this->_active = (bool) $resourceArray['active'];
        }

         if (isset($resourceArray['resourceId'])) {
            $this->_resourceId = (int) $resourceArray['resourceId'];
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
     *  Function that returns status of Element instance. We consider Element as valid if resource 
     *  has correctly set resourceName, resourceCode
     * @return type
     */
    public function isValid() {
        if (isset($this->_resourceName) && isset($this->_domainId)) {
            $this->_valid = true;
        } else {
            $this->_valid = false;
        }
        return $this->_valid;
    }

    /**
     * Returns array of properties of resource.
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
