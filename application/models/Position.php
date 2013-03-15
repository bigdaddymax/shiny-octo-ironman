<?php

/**
 * Description of Position
 *
 * @author Max
 */
class Application_Model_Position {

    private $_valid = true;
    private $_positionName;
    private $_orgobjectId;
    private $_positionId;
    private $_active = true;
    private $_domainId;

    public function __construct(array $positionArray = null) {
        if (isset($positionArray['positionName'])) {
            $this->_positionName = $positionArray['positionName'];
        }

        if (isset($positionArray['domainId'])) {
            $this->domainId = $positionArray['domainId'];
        }
        if (isset($positionArray['active'])) {
            $this->_active = (bool) $positionArray['active'];
        }

        if (isset($positionArray['positionId'])) {
            $this->_positionId = (int) $positionArray['positionId'];
        }
        if (isset($positionArray['orgobjectId'])) {
            $this->_orgobjectId = (int) $positionArray['orgobjectId'];
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
     *  Function that returns status of Element instance. We consider Element as valid if position 
     *  has correctly set positionName, positionCode
     * @return type
     */
    public function isValid() {
        if (isset($this->_positionName) && isset($this->_domainId) && isset($this->_orgobjectId)) {
            $this->_valid = true;
        } else {
            $this->_valid = false;
        }
        return $this->_valid;
    }

    /**
     * Returns array of properties of position.
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
