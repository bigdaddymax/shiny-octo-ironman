<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Node
 * 
 * Node is element of organsational structure of the company.
 *  $nodeName  - Name of the node
 *  $nodeId    - Id of the DB record of node. Used for reference.
 *  $parentNodeId  - Id of parent node. If the node doesnt have upper node, parentNodeId = 0
 *
 * @author Max
 */
class Application_Model_Node {

    private $_nodeId;
    private $_nodeName;
    private $_parentNodeId;
    private $_active = 1;
    private $_valid = true;
    private $_domainId;

    public function __construct(array $node) {
        if (is_array($node)) {
            foreach ($node as $key => $item) {
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
     * Returns true if Node object is valid 
     * @return boolean
     */
    public function isValid() {
        if (isset($this->_nodeName) && isset($this->_parentNodeId) && isset($this->_domainId)) {
            return true;
        } else {
            return false;
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

}
