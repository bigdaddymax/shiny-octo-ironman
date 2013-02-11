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
    private $_projectId;
    private $_active = true;
    private $_domainId;
    private $_date;
    private $_items;
    private $_orgobjectId;

    public function __construct(array $formArray = null) {
        if (isset($formArray['formName'])) {
            $this->_formName = $formArray['formName'];
        }
        if (isset($formArray['domainId'])) {
            $this->domainId = (int) $formArray['domainId'];
        }
        if (isset($formArray['active'])) {
            $this->_active = (bool) $formArray['active'];
        }
        if (isset($formArray['userId'])) {
            $this->_userId = (int) $formArray['userId'];
        }
        if (isset($formArray['projectId'])) {
            $this->_projectId = (int) $formArray['projectId'];
        }
        if (isset($formArray['orgobjectId'])) {
            $this->_orgobjectId = (int) $formArray['orgobjectId'];
        }
        if (isset($formArray['formId'])) {
            $this->_formId = (int) $formArray['formId'];
        }
        if (isset($formArray['items'])) {
            $this->setItems($formArray['items']);
        }
        if (isset($formArray['date'])) {
            $this->_date = $formArray['date'];
        } else {
            $this->_date = date('Y-m-d H:i:s');
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
        $checkedItems = array();
        if (isset($items) && is_array($items)) {
            foreach ($items as $item) {
                // Item is of Application_Model_Item type
                if ($item instanceof Application_Model_Item) {
                    if ($item->isValid()) {
                        $checkedItems[] = $item;
                    } else {
                        throw new InvalidArgumentException('Cannot create item from array within Form. Item is now valid');
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
     *  Function that returns status of Element instance. We consider Element as valid if form 
     *  has correctly set formName, formCode
     * @return type
     */
    public function isValid() {
        $this->_valid = true;
        if (isset($this->_formName) && isset($this->_domainId) && isset($this->_userId) && isset($this->_orgobjectId) && isset($this->_items)) {
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
