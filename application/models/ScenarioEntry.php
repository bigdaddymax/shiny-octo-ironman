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
    private $_order;
    private $_userId;
    private $_active = true;
    private $_domainId;

    public function __construct(array $scenarioEntryArray = null) {
        if (isset($scenarioEntryArray['domainId'])) {
            $this->domainId = (int) $scenarioEntryArray['domainId'];
        }
        if (isset($scenarioEntryArray['active'])) {
            $this->_active = (bool) $scenarioEntryArray['active'];
        }
        if (isset($scenarioEntryArray['order'])) {
            $this->_order = (int)$scenarioEntryArray['order'];
        }
        if (isset($scenarioEntryArray['userId'])) {
            $this->_userId = (int) $scenarioEntryArray['userId'];
        }
        if (isset($scenarioEntryArray['scenarioEntryId'])) {
            $this->_scenarioEntryId = (int) $scenarioEntryArray['scenarioEntryId'];
        }
    }

    public function __set($name, $order) {
        if ('valid' == $name) {
            echo 'Cannot set order for "valid" property';
        } elseif (property_exists($this, '_' . $name)) {
            $name1 = '_' . $name;
            $this->$name1 = $order;
        } else {
            echo 'Cannot set order. Property ' . $name . ' doesnt exist';
        }
    }

    public function __get($name) {
        if (property_exists($this, '_' . $name)) {
            $name = '_' . $name;
            return $this->$name;
        } else {
            return 'Cannot get order. Property ' . $name . ' doesnt exist';
        }
    }

    /**
     *  Function that returns status of ScenarioEntry instance. We consider ScenarioEntry as valid if scenarioEntry 
     *  has correctly set scenarioEntryName, userId, userId, order, domainId, elementId
     * @return type
     */
    public function isValid() {
        if (isset($this->_domainId) && isset($this->_userId) &&
                isset($this->_order)) {
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
        foreach ($this as $key => $order) {
            if ('_valid' != $key) {
                if (isset($order)) {
                    $output[str_replace('_', '', $key)] = $order;
                }
            }
        }
        return $output;
    }

}

?>
