<?php

class Application_Model_ApprovalEntry {

    private $_domainId;
    private $_active = true;
    private $_approvalEntryId;
    private $_userId;
    private $_formId;
    private $_decision;
    private $_valid;
    private $_date;

    public function __construct(array $entry = null) {
        if (is_array($entry)) {
            foreach ($entry as $key => $item) {
                $this->{$key} = (strpos($key, 'Id') || 'active' == $key) ? (int) $item : $item;
            }
        }
        if (!$this->_date) {
            $this->_date = date('Y-m-d H:i:s');
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

    public function isValid() {
        if (isset($this->_userId) &&
                isset($this->_domainId) && isset($this->_formId) &&
                isset($this->_decision)) {
            $this->_valid = true;
        } else {
            $this->_valid = false;
        }
        return $this->_valid;
    }

}