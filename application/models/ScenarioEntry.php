<?php

/**
 * Description of ScenarioEntry
 * 
 * ScenarioEntry - smallest piece of a Form. ScenarioEntry describes single element of approval user, references to $userId, $userId
 *
 * @author Max
 */
class Application_Model_ScenarioEntry {

    private $_valid = true;
    private $_scenarioEntryId;
    private $_scenarioId;
    private $_orderPos;
    private $_userId;
    private $_active = 1;
    private $_domainId;

    public function __construct(array $scenarioEntryArray = null) {
        if (is_array($scenarioEntryArray)) {
            foreach ($scenarioEntryArray as $key => $item) {
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
     *  Function that returns status of ScenarioEntry instance. We consider ScenarioEntry as valid if scenarioEntry 
     *  has correctly set scenarioEntryName, userId, userId, order, domainId, elementId
     * @return type
     */
    public function isValid() {
        if (isset($this->_domainId) && isset($this->_userId) &&
                isset($this->_orderPos)) {
            $this->_valid = true;
        } else {
            $this->_valid = false;
        }
        return $this->_valid;
    }

    /**
     * Returns array of properties of scenarioEntry.
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
