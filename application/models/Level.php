<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Level
 * 
 * Level is element of organsational structure of the company.
 *  $levelName  - Name of the level
 *  $levelId    - Id of the DB record of level. Used for reference.
 *  $parentLevelId  - Id of parent level. If the level doesnt have upper level, parentLevelId = 0
 *
 * @author Max
 */
class Application_Model_Level {

    private $_levelId;
    private $_levelName;
    private $_parentLevelId;
    private $_active = true;
    private $_valid = true;
    private $_domainId;

    public function __construct(array $level) {
        if (isset($level['levelId'])) {
            $this->_levelId = (int) $level['levelId'];
        }
        if (isset($level['levelName'])) {
            $this->_levelName = $level['levelName'];
        } else {
            $this->_valid = false;
        }
        if (isset($level['active'])) {
            $this->_active = (int) $level['active'];
        }
        if (isset($level['parentLevelId'])) {
            $this->_parentLevelId = (int) $level['parentLevelId'];
        }
        if (isset($level['domainId'])) {
            $this->_domainId = (int)$level['domainId'];
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
     * Returns true if levelName is set, 
     * @return boolean
     */
    public function isValid() {
        if (isset($this->_levelName) && isset($this->_parentLevelId) && isset($this->_domainId)) {
            return true;
        } else {
            return false;
        }
    }

    public function toArray() {
        return array('levelId' => (int) $this->_levelId, 'levelName' => $this->_levelName,
            'parentLevelId' => $this->_parentLevelId, 'active' => (bool) $this->_active, 'domainId' => $this->_domainId);
    }

}
