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
    private $_active = true;
    private $_domainId;

    public function __construct(array $contragentArray = null) {
        if (isset($contragentArray['contragentName'])) {
            $this->_contragentName = $contragentArray['contragentName'];
        }
        if (isset($contragentArray['domainId'])) {
            $this->domainId = (int) $contragentArray['domainId'];
        }
        if (isset($contragentArray['active'])) {
            $this->_active = (bool) $contragentArray['active'];
        }
        if (isset($contragentArray['contragentId'])) {
            $this->_contragentId = (int) $contragentArray['contragentId'];
        }
        // Items are set from normal array
    }

    /**
     * Take array of items as input. Items could  be of Application_Model_Item type or
     * array that will allow to create valid item
     * 
     * @param array $items
     * @throws InvalidArgumentException
     * @throws InvalidArgumentException
     */

    public function __set($name, $value) {
        if ('valid' == $name) {
            echo 'Cannot set value for "valid" property';
        } elseif ('items' == $name) {
            $this->setItems($value);
        } elseif (property_exists($this, '_' . $name)) {
            $name1 = '_' . $name;
            if ('active' == $name || 'public' == $name) {
                $this->$name1 = (bool) $value;
            } else {
                $this->$name1 = $value;
            }
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
            if (('_valid' != $key) && ('_decsion' != $key) && ('_final' != $key)) {
                if (isset($value)) {
                    if ('_active' == $key || '_public' == $key) {
                        $output[str_replace('_', '', $key)] = (int) $value;
                    } else {
                        $output[str_replace('_', '', $key)] = $value;
                    }
                }
            }
        }
        return $output;
    }

}

?>
