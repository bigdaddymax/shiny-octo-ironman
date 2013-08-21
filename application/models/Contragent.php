<?php

/**
 * Description of Element
 *
 * @author Max
 */
class Application_Model_Contragent {

    private $_valid = true;
    private $_contragentName;
    private $_contragentId;
    private $_active = 1;
    private $_domainId;

    public function __construct(array $contragentArray = null) {
        if (is_array($contragentArray)) {
            foreach ($contragentArray as $key => $item) {
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
     *  Function that returns status of Element instance. We consider Element as valid if contragent 
     *  has correctly set contragentName, contragentCode
     * @return type
     */
    public function isValid() {
        $this->_valid = true;
        if (isset($this->_contragentName) && isset($this->_domainId)) {
            $this->_valid = true;
        } else {
            $this->_valid = false;
        }

        return $this->_valid;
    }

    /**
     * Returns array of properties of contragent.
     * We are stripping 'valid' property.
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
