<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Comment
 * 
 * Comment is element of organsational structure of the company.
 *  $commentName  - Name of the comment
 *  $commentId    - Id of the DB record of comment. Used for reference.
 *  $parentCommentId  - Id of parent comment. If the comment doesnt have upper comment, parentCommentId = 0
 *
 * @author Max
 */
class Application_Model_Comment {

    private $_commentId;
    private $_comment;
    private $_parentCommentId;
    private $_active = 1;
    private $_valid = true;
    private $_domainId;
    private $_formId;
    private $_userId;
    private $_date;

    public function __construct(array $comment = null) {
        if (is_array($comment)) {
            foreach ($comment as $key => $item) {
                $this->{$key} = (strpos($key, 'Id') || 'active' == $key) ? (int) $item : $item;
            }
        }
        if (!$this->_date) {
            $this->_date = date('Y-m-d H:i:s');
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
     * Returns true if commentName is set, 
     * @return boolean
     */
    public function isValid() {
        if (isset($this->_comment) && isset($this->_parentCommentId) && isset($this->_userId) && isset($this->_domainId) && isset($this->_formId) && isset($this->_date)) {
            $this->_valid = true;
        } else {
            $this->_valid = false;
        }
        return $this->_valid;
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
