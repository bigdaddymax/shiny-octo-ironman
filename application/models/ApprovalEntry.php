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

    public function __construct($entry) {
        if (is_array($entry)) {
            foreach ($entry as $key => $item) {
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