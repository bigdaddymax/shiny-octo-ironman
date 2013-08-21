<?php

/**
 * Description of Item
 * 
 * Item - smallest piece of a Form. Item describes single element of approval form, references to $formId, $userId
 *
 * @author Max
 */
class Application_Model_Item {

    private $_valid = true;
    private $_itemName;
    private $_itemId;
    private $_value;
    private $_elementId;
    private $_formId;
    private $_active = 1;
    private $_domainId;
    private $_element;

    public function __construct(array $itemArray = null) {
        if (is_array($itemArray)) {
            foreach ($itemArray as $key => $item) {
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
        }  {
            throw new NonExistingObjectProperty('Trying to get "' . $name . ' Property doesnt exist');
        }
    }

    /**
     *  Function that returns status of Item instance. We consider Item as valid if item 
     *  has correctly set itemName, userId, formId, value, domainId, elementId
     * @return type
     */
    public function isValid() {
        if (isset($this->_itemName) &&
                isset($this->_domainId) && isset($this->_elementId) &&
                isset($this->_value)) {
            $this->_valid = true;
        } else {
            $this->_valid = false;
        }
        return $this->_valid;
     }

    /**
     * Returns array of properties of item.
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
