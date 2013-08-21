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

    private $_valid = 0;
    private $_privilegeId;
    private $_userId;
    private $_objectId;
    private $_objectType;
    private $_privilege;
    private $_active = 1;
    private $_domainId;

    public function __construct(array $privilegeArray = null) {
        if (is_array($privilegeArray)) {
            foreach ($privilegeArray as $key => $item) {
                $this->{$key} = (strpos($key, 'Id') || 'active' == $key) ? (int) $item : $item;
            }
        }
        $this->isValid();
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
                    $output[str_replace('_', '', $key)] = (strpos($key, 'Id') || 'active' == $key) ? (int) $value : $value;
                }
            }
        }
        return $output;
    }

}

?>
