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
    private $_active = true;
    private $_valid = true;
    private $_domainId;

    public function __construct(array $node) {
        if (isset($node['nodeId'])) {
            $this->_nodeId = (int) $node['nodeId'];
        }
        if (isset($node['nodeName'])) {
            $this->_nodeName = $node['nodeName'];
        } else {
            $this->_valid = false;
        }
        if (isset($node['active'])) {
            $this->_active = (int) $node['active'];
        }
        if (isset($node['parentNodeId'])) {
            $this->_parentNodeId = (int) $node['parentNodeId'];
        }
        if (isset($node['domainId'])) {
            $this->_domainId = (int)$node['domainId'];
        } 
    }

    public function __set($name, $value) {
        if ('valid' == $name) {
            echo 'Cannot set value for "valid" property';
        } elseif (property_exists($this, '_' . $name)) {
            $name1 = '_' . $name;
            $this->$name1 = $value;
        }
        else
            echo 'Cannot set value. Property ' . $name . ' doesnt exist';
    }

    public function __get($name) {
        if (property_exists($this, '_' . $name)) {
            $name = '_' . $name;
            return $this->$name;
        }
        else
            return 'Cannot get value. Property ' . $name . ' doesnt exist';
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
        return array('nodeId' => (int) $this->_nodeId, 'nodeName' => $this->_nodeName,
            'parentNodeId' => $this->_parentNodeId, 'active' => (bool) $this->_active, 'domainId' => $this->_domainId);
    }

}
