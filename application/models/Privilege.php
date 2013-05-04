<?php

/**
 * Description of Acl
 * 
 * Access control list - object that contains information about privileges of particular users 
 * on particular objects
 * Objects could be 'levels' or 'orgobjects'
 * Rights could be 'read', 'write', 'approve'. 
 * If user has some privileges on 'level' objectType, then hi has same privileges on all levels and orgobjects for which 
 * this particular level is parent.
 *
 * @author Max
 */
class Application_Model_Privilege {

    private $_valid = false;
    private $_privilegeId;
    private $_userId;
    private $_objectId;
    private $_objectType;
    private $_privilege;
    private $_active = true;
    private $_domainId;

    public function __construct(array $privilegeArray = null) {
        if (isset($privilegeArray['privilegeId'])) {
            $this->_privilegeId = (int) $privilegeArray['privilegeId'];
        }
        if (isset($privilegeArray['domainId'])) {
            $this->domainId = (int) $privilegeArray['domainId'];
        }
        if (isset($privilegeArray['active'])) {
            $this->_active = (bool) $privilegeArray['active'];
        }
        if (isset($privilegeArray['privilege'])) {
            $this->_privilege = $privilegeArray['privilege'];
        }
        if (isset($privilegeArray['objectId'])) {
            $this->_objectId = (int) $privilegeArray['objectId'];
        }
        if (isset($privilegeArray['objectType'])) {
            $this->_objectType = $privilegeArray['objectType'];
        }

        if (isset($privilegeArray['userId'])) {
            $this->_userId = (int) $privilegeArray['userId'];
        }
        $this->_valid = $this->isValid();
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

    /**
     *  Function that returns status of Element instance. We consider Element as valid if privilege 
     *  has correctly set privilegeName, privilegeCode
     * @return type
     */
    public function isValid() {
        if (isset($this->_privilege) && isset($this->_userId) &&
                isset($this->_domainId) &&
                isset($this->_objectId) &&
                isset($this->_objectType)) {
            $this->_valid = true;
        } else {
            $this->_valid = false;
        }
        return $this->_valid;
    }

    /**
     * Returns array of properties of privilege.
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
