<?php

/**
 * Description of Element
 *
 * @author Max
 */
class Application_Model_UserGroup {

    private $_valid = true;
    private $_usergroupName;
    private $_userId;
    private $_usergroupId;
    private $_role;
    private $_active = true;
    private $_domainId;

    public function __construct(array $userArray = null) {
        if (isset($userArray['usergroupName'])) {
            $this->_usergroupName = $userArray['usergroupName'];
        }
        if (isset($userArray['domainId'])) {
            $this->domainId = (int) $userArray['domainId'];
        }
        if (isset($userArray['active'])) {
            $this->_active = (bool) $userArray['active'];
        }
        if (isset($userArray['role'])) {
            $this->_role = $userArray['role'];
        }
        if (isset($userArray['usergroupId'])) {
            $this->_usergroupId = (int) $userArray['usergroupId'];
        }
        if (isset($userArray['userId'])) {
            $this->_userId = (int) $userArray['userId'];
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

    /**
     *  Function that returns status of Element instance. We consider Element as valid if user 
     *  has correctly set userName, userCode
     * @return type
     */
    public function isValid() {
        if (isset($this->_role) && isset($this->_usergroupName) &&
                isset($this->_domainId) &&
                 isset($this->_userId)) {
            $this->_valid = true;
        } else {
            $this->_valid = false;
        }
        return $this->_valid;
    }

    /**
     * Returns array of properties of user.
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
