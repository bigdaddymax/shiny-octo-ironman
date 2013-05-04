<?php

/**
 * Description of Scenario
 *
 * "Element" is a class that represent particular scenario of approval process.
 * It stores inscenarioation who is involved in approval process and in what sequence this process should run
 * 
 * @author Max
 */
class Application_Model_Scenario {

    private $_valid = true;
    private $_scenarioName;
    private $_scenarioId;
    private $_entries;
    private $_active = true;
    private $_domainId;

    public function __construct(array $scenarioArray = null) {
        if (isset($scenarioArray['scenarioName'])) {
            $this->_scenarioName = $scenarioArray['scenarioName'];
        }

        if (isset($scenarioArray['domainId'])) {
            $this->domainId = (int) $scenarioArray['domainId'];
        }
        if (isset($scenarioArray['active'])) {
            $this->_active = (bool) $scenarioArray['active'];
        }

        if (isset($scenarioArray['scenarioId'])) {
            $this->_scenarioId = (int) $scenarioArray['scenarioId'];
        }

        if (isset($scenarioArray['entries'])) {
            $this->setEntries($scenarioArray['entries']);
        }

        if (!isset($this->_entries) && is_array($scenarioArray)) {
            $keys = array_keys($scenarioArray);
            foreach ($keys as $key) {
                if (strpos($key, '_')) {
                    if ('orderPos' == substr($key, 0, strpos($key, '_'))) {
                        $entries[(int) $scenarioArray[$key]]['orderPos'] = (int) $scenarioArray[$key];
                        $entries[(int) $scenarioArray[$key]]['domainId'] = $this->_domainId;
                        $entries[(int) $scenarioArray[$key]]['userId'] = substr($key, strpos($key, '_') + 1);
                    }
                }
            }
            if (isset($entries)) {
                $this->setEntries($entries);
            }
        }
    }

    private function setEntries($entries) {
        // Collect valid entries here
        $checkedEntries = null;
        if (isset($entries) && is_array($entries)) {
            foreach ($entries as $entry) {
                // Item is of Application_Model_Item type
                if ($entry instanceof Application_Model_ScenarioEntry) {
                    if ($entry->isValid()) {
                        $checkedEntries[] = $entry;
                    } else {
                        throw new InvalidArgumentException('Cannot create entry from array within Scenario. Entry is not valid');
                    }
                }
                // Item is array, try to create valid Item from this array
                elseif (is_array($entry)) {
                    $entryObj = new Application_Model_ScenarioEntry($entry);
                    if (!$entryObj->isValid()) {
                        // Unsuccssfully
                        throw new InvalidArgumentException('Cannot create entry from array within Scenario. Entry is not valid');
                    } else {
                        // Successfully, add this entry to collection
                        $checkedEntries[] = $entryObj;
                    }
                } else {
                    // Found not valid data in input array
                    throw new InvalidArgumentException('One of entries is neither of Application_Model_ScenarioEntry type nor Array().');
                }
            }
        } elseif ($entries instanceof Application_Model_ScenarioEntry) {
            $checkedEntries[] = $entries;
        }
        $this->_entries = $checkedEntries;
    }

    // Method returns order in which $userId can do approval according to this scenario
    public function getUserOrder($userId) {
        if ($this->_entries){
            foreach ($this->_entries as $entry){
                if ($userId == $entry->userId){
                    return $entry->orderPos;
                }
            }
            return false;
        } else {
            throw new InvalidArgumentException('This scenario doesnt have entry with userId = '.$userId);
        }
    }

    public function __set($name, $value) {
        if ('valid' == $name) {
            echo 'Cannot set value for "valid" property';
        } elseif ('entries' == $name) {
            $this->setEntries($value);
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
     *  Function that returns status of Element instance. We consider Element as valid if scenario 
     *  has correctly set scenarioName, scenarioCode
     * @return type
     */
    public function isValid() {
        if (isset($this->_entries) && isset($this->_scenarioName) && isset($this->_domainId)) {
            $this->_valid = true;
        } else {
            $this->_valid = false;
        }
        return $this->_valid;
    }

    /**
     * Returns array of properties of scenario.
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
