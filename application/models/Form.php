<?php

/**
 * Description of Element
 *
 * @author Max
 */
class Application_Model_Form {

    private $_valid = true;
    private $_formName;
    private $_formId;
    private $_userId;
//    private $_projectId;
    private $_active = 1;
    private $_domainId;
    private $_date;
    private $_items;
    private $_nodeId;
    private $_final = 0;
    private $_decision = '';
    private $_public = 0;
    private $_contragentId;
    private $_expgroup;

    public function __construct(array $formArray = null) {
        if (is_array($formArray)) {
            foreach ($formArray as $key => $item) {
                $this->{$key} = (strpos($key, 'Id') || 'active' == $key || 'public' == $key) ? (int) $item : $item;
            }
        }
        if (!$this->_date) {
            $this->_date = date('Y-m-d H:i:s');
        }
        // If items were not set from array, we assume that this is HTTP _POST array
        // Try to decode items from form 'itemName_3 = "ItemName"', 'value_3 = 44.3' etc, 
        // where '3' is item's number and 'itemName' or 'value' is name of property to be set
        if (!isset($this->items) && is_array($formArray)) {
            $keys = array_keys($formArray);
            foreach ($keys as $key) {
                if (strpos($key, '_')) {
                    if ('itemName' == substr($key, 0, strpos($key, '_'))) {
                        $items[substr($key, strpos($key, '_') + 1)]['itemName'] = $formArray[$key];
                        $items[substr($key, strpos($key, '_') + 1)]['domainId'] = $this->_domainId;
                    } elseif ('value' == substr($key, 0, strpos($key, '_'))) {
                        $items[substr($key, strpos($key, '_') + 1)]['value'] = (float) $formArray[$key];
                    } elseif ('elementId' == substr($key, 0, strpos($key, '_'))) {
                        $items[substr($key, strpos($key, '_') + 1)]['elementId'] = (int) $formArray[$key];
                    }
                }
            }
            if (isset($items)) {
                $this->setItems($items);
            }
        }
    }

    /**
     * Take array of items as input. Items could  be of Application_Model_Item type or
     * array that will allow to create valid item
     * 
     * @param array $items
     * @throws InvalidArgumentException
     * @throws InvalidArgumentException
     */
    private function setItems($items) {
        // Collect valid items here
        $checkedItems = null;
        if (isset($items) && is_array($items)) {
            foreach ($items as $item) {
                // Item is of Application_Model_Item type
                if ($item instanceof Application_Model_Item) {
                    if ($item->isValid()) {
                        $checkedItems[] = $item;
                    } else {
                        throw new InvalidArgumentException('Cannot create item from array within Form. Item is not valid');
                    }
                }
                // Item is array, try to create valid Item from this array
                elseif (is_array($item)) {
                    $itemObj = new Application_Model_Item($item);
                    if (!$itemObj->isValid()) {
                        // Unsuccssfully
                        throw new InvalidArgumentException('Cannot create item from array within Form. Item is now valid');
                    } else {
                        // Successfully, add this item to collection
                        $checkedItems[] = $itemObj;
                    }
                } else {
                    // Found not valid data in input array
                    throw new InvalidArgumentException('One of items is neither of Application_Model_Item type nor Array().');
                }
            }
        } elseif ($items instanceof Application_Model_Item) {
            $checkedItems[] = $items;
        }
        $this->_items = $checkedItems;
    }

    public function __set($name, $value) {
        if ('valid' == $name) {
            echo 'Cannot set value for "valid" property';
        } elseif ('items' == $name) {
            $this->setItems($value);
        } elseif (property_exists($this, '_' . $name)) {
            $name1 = '_' . $name;
            $this->$name1 = (strpos($name, 'Id') || 'active' == $name || 'public' == $name) ? (int) $value : $value;
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
     *  Function that returns status of Element instance. We consider Element as valid if form 
     *  has correctly set formName, formCode
     * @return type
     */
    public function isValid() {
        $this->_valid = true;
        if (isset($this->_formName) && isset($this->_domainId) && isset($this->_contragentId) && isset($this->_userId) && isset($this->_nodeId) && isset($this->_items) && isset($this->_expgroup)) {
            $this->_valid = true;
        } else {
            $this->_valid = false;
        }

        return $this->_valid;
    }

    /**
     * Returns array of properties of form.
     * We are stripping 'valid' property.
     * @return type
     */
    public function toArray() {
        $output = array();
        foreach ($this as $key => $value) {
            if (('_valid' != $key) && ('_decision' != $key) && ('_final' != $key)) {
                if (isset($value)) {
                    if (isset($value)) {
                        $output[str_replace('_', '', $key)] = (strpos($key, 'Id') || 'active' == $key || 'public' == $key) ? (int) $value : $value;
                    }
                }
            }
        }
        return $output;
    }

}

?>
