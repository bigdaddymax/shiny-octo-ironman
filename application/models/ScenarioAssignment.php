<?php

/**
 * Description of ScenarioAssignment
 * This class ties together scenarios of how approval process flows with nodes to 
 * which this scenario is appliable.
 * 
 * In general, one scenario could be assigned to different nodes and (in future)
 * Many scenarios may be assigned to one node with different conditions.
 * 
 * 
 * @author Max
 */
class Application_Model_ScenarioAssignment {

    private $_valid = true;
    private $_scenarioAssignmentId;
    private $_scenarioId;
    private $_nodeId;
    private $_active = true;
    private $_domainId;
    private $_condition;
    private $_operand;

    public function __construct(array $assignmentArray = null) {
        if (is_array($assignmentArray)) {
            foreach ($assignmentArray as $key => $item) {
                $this->{$key} = (strpos($key, 'Id') || 'active' == $key) ? (int) $item : $item;
            }
        }    }

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
            return (strpos($name, 'Id') || 'active' == $name) ? (int) $this->$name: $this->name;
        } else {
            throw new NonExistingObjectProperty('Trying to get "' . $name . ' Property doesnt exist');
        }
    }

    /**
     *  Function that returns status of Element instance. We consider Element as valid if assignment 
     *  has correctly set assignmentName, assignmentCode
     * @return type
     */
    public function isValid() {
        if (isset($this->_scenarioId) && isset($this->_nodeId) && isset($this->_domainId)) {
            $this->_valid = true;
        } else {
            $this->_valid = false;
        }
        return $this->_valid;
    }

    /**
     * Returns array of properties of assignment.
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
