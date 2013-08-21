<?php

/**
 * Description of Position
 *
 * @author Max
 */
class Application_Model_Position {

    private $_valid = true;
    private $_positionName;
    private $_nodeId;
    private $_positionId = 0;
    private $_active = 1;
    private $_domainId;

    public function __construct(array $positionArray = null) {
        if (is_array($positionArray)) {
            foreach ($positionArray as $key => $item) {
                $this->{$key} = (strpos($key, 'Id')  || 'active' == $key) ? (int) $item : $item;
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
     *  Function that returns status of Element instance. We consider Element as valid if position 
     *  has correctly set positionName, positionCode
     * @return type
     */
    public function isValid() {
        if (isset($this->_positionName) && isset($this->_domainId) && isset($this->_nodeId)) {
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
                    $output[str_replace('_', '', $key)] = (strpos($key, 'Id')  || 'active' == $key) ? (int) $value : $value;
                }
            }
        }
        return $output;
    }

}

?>
