<?php

/**
 * Description of DataMapper
 * Universal class for data manipulation for basic classes
 * 
 * We have basic classes and tables in DB. Naming convention (example): 
 *      class:                  Application_Model_OrgObject
 *      table:                  orgobjects
 *      column in table (ID):   orgobjectId
 * 
 * Every basic class has the same basic methods: 
 *      isValid() - returns true if object is valid
 *      toArray() - returns array() of properties
 * 
 * This gives possibility to create universal methods for manipulating databases
 * 
 * @author Max
 */
require_once APPLICATION_PATH . '/models/BaseDBAbstract.php';

class Application_Model_DataMapper extends BaseDBAbstract {

    public $className;
    private $tableName;
    private $objectName;
    private $objectIdName;
    public $objectParentIdName;

    public function __construct($object = null) {
        parent::__construct();
        if ($object) {
            $this->setClassAndTableName($object);
        }
    }

    /**
     * Helper function, creates names of properties, tables etc for particular object.
     * 
     * @param class $object
     * @param string $object
     */
    private function setClassAndTableName($object) {
        if (is_object($object))
            $this->className = get_class($object);
        elseif (is_string($object))
            $this->className = $object;
        $this->objectName = strtolower(substr($this->className, strrpos($this->className, '_') + 1));
        $this->tableName = $this->objectName . 's';
        $this->objectIdName = $this->objectName . 'Id';
        $this->objectParentIdName = 'parent' . ucwords($this->objectIdName);
    }

    /**
     * 
     * saveObject($object) If object is new - save it in DB, if object exists - update it in DB
     * 
     * @param type $object
     * @return type
     * @throws InvalidArgumentException
     * 
     */
    public function saveObject($object) {
        $this->setClassAndTableName($object);
        if ($object->isValid()) {
            if ($this->checkObjectExistance($object)) {
                // Object exists, so we will update it
                $objectArray = $object->toArray();
                // Cleaning columns before updating database
                unset($objectArray[$this->objectIdName]);
                $objectArray['active'] = (int) $objectArray['active'];
                if (isset($objectArray['password'])) {
                    $auth = new Application_Model_Auth();
                    $objectArray['password'] = $auth->hashPassword($objectArray['password']);
                }
                $this->dbLink->update($this->tableName, $objectArray, array($this->objectIdName . ' = ?' => $object->{$this->objectIdName}));
            } else {
                // Object doesnt exist, so we are creating new
                $objectArray = $object->toArray();
                // Cleaning columns before inserting data in database
                unset($objectArray[$this->objectIdName]);
                $objectArray['active'] = (int) $objectArray['active'];
                if (isset($objectArray['password'])) {
                    $auth = new Application_Model_Auth();
                    $objectArray['password'] = $auth->hashPassword($objectArray['password']);
                }
                $this->dbLink->insert($this->tableName, $objectArray);
                $object->{$this->objectIdName} = (int) $this->dbLink->lastInsertId();
            }
            return $object->{$this->objectIdName};
        } else
            throw new InvalidArgumentException($this->objectName . ' data are not valid');
    }

    /**
     * 
     * @param int $id
     * @param string $class Contains Class name for object to be searched and returned. If null than instance of DataDBMapper 
     *                      should be initialized with required class name at creation time.
     * @return object of type $this->className
     * @throws InvalidArgumentException
     */
    public function getObject($id, $class = null) {
        if (isset($class)) {
            $this->setClassAndTableName($class);
        } elseif (!isset($this->className)) {
            throw new InvalidArgumentException('Class name is not set.');
        }
        $objectArray = $this->dbLink->fetchRow($this->dbLink->quoteinto('SELECT * FROM ' . $this->tableName . ' WHERE ' . $this->objectIdName . '=?', $id));
        if (is_array($objectArray)) {
            $object = new $this->className($objectArray);
            return $object;
        } else
            return false;
    }

    /**
     * Searches in DB for particular object (basically for object with specified ID)
     * @param type $object
     * @return true if record exists, false otherwise
     */
    public function checkObjectExistance($object) {

        if (is_object($object)) {
            $this->setClassAndTableName($object);
            $id = $object->{$this->objectIdName};
            if (!empty($id)) {
                $stmt = $this->dbLink->query($this->dbLink->quoteinto('SELECT COUNT(' . $this->objectIdName . ') FROM ' . $this->tableName . ' WHERE ' . $this->objectIdName . '=?', $object->{$this->objectIdName}));
            } else {
                return false;
            }
        } elseif (is_int($object) && !empty($object)) {
            $stmt = $this->dbLink->query($this->dbLink->quoteinto('SELECT COUNT(' . $this->objectIdName . ') FROM ' . $this->tableName . ' WHERE ' . $this->objectIdName . '=?', $object));
        }
        $row = $stmt->fetchColumn();
        return (($row != 0) ? true : false);
    }

    /**
     * Returns array of objects of $class. All data from $this->tableName are selected.
     * If $filter is set in form of array('userId'=>4) return all entries that has userId=4 in their properties
     * @param string $class
     * @param array $filter
     * @return array className
     * @throws Exception
     */
    public function getAllObjects($class = null, $filter = null) {
        if (isset($class)) {
            $this->setClassAndTableName($class);
        } elseif (!isset($this->className)) {
            throw new InvalidArgumentException('Class name is not set.');
        }
        if (empty($filter)) {
            $objectsArray = $this->dbLink->fetchAll('SELECT * FROM ' . $this->tableName . ' ');
        } else {
            if (is_array($filter)) {
                $selectFilter = ' WHERE 1=1 ';
                foreach ($filter as $item => $value) {
                    $selectFilter.= $this->dbLink->quoteinto(' AND ' . $item . ' = ? ', $value);
                }
                $objectsArray = $this->dbLink->fetchAll('SELECT * FROM ' . $this->tableName . $selectFilter);
            } else {
                throw new InvalidArgumentException('$filter should be of Array()');
            }
        }
        $output = array();
        foreach ($objectsArray as $object) {
            $output[] = new $this->className($object);
        }
        return ((empty($output)) ? false : $output);
    }

    /**
     * Checks if object is related to other objects in database. 
     * 
     * @param int $id
     * @param object $class
     * @return boolean Returns true is other objects are dependent on this object with $id, false otherwise
     */
    public function checkObjectDependencies($id, $class = null) {
        if (isset($class)) {
            $this->setClassAndTableName($class);
        } elseif (!isset($this->className)) {
            throw new InvalidArgumentException('Class name is not set.');
        }
        // Get all tables in our database schema that contain column $this->objectIdName (for example, "positionId" or "userId")
        $tables = $this->dbLink->fetchCol($this->dbLink->quoteinto('SELECT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME = ? AND TABLE_SCHEMA="supercapex"', $this->objectIdName));
        $count = null;
        foreach ($tables as $table) {
            if ($table != $this->tableName) {
                $count = $this->dbLink->fetchOne($this->dbLink->quoteinto('SELECT ' . $this->objectIdName . ' FROM ' . $table . ' WHERE ' . $this->objectIdName . ' = ? LIMIT 0,1', $id));
                if ($count == $id) {
                    return array('dependentTable' => $table, 'ID' => $id);
                }
            }
        }
        // Now check in the same table for entries in ID and ParentId columns. For example,
        // levelId == parentLevelId
        // First check if parentObjectId exists
        $object = $this->getObject($id, $this->className);
        if (false !== $object) {
            $objectArray = $object->toArray();
            if ($object->isValid()) {
                if (isset($objectArray[$this->objectParentIdName])) {
                    // Parent Id exists
                    $topId = $this->dbLink->fetchOne($this->dbLink->quoteinto('SELECT ' . $this->objectIdName . ' FROM ' . $this->tableName .
                                    ' WHERE ' . $this->objectParentIdName . ' = ?', $id));
                    if (!empty($topId)) {
                        return array('ID' => $topId);
                    }
                }
            }
        }
        return false;
    }

    /**
     * deleteObject - deletes object permanently from DB
     * 
     * @param int $id
     * @param string $class
     * @return boolean
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function deleteObject($id, $class = null) {
        if (isset($class)) {
            $this->setClassAndTableName($class);
        } elseif (!isset($this->className)) {
            throw new InvalidArgumentException('Class name is not set.');
        }
        $dependentTable = $this->checkObjectDependencies($id, $this->className);
        if ($dependentTable === false) {
            $this->dbLink->delete($this->tableName, array($this->objectIdName . '=?' => $id));
            return true;
        } else {
            if (isset($dependentTable['dependentTable'])) {
                throw new Exception('Other objects depend on "' . $this->objectName . '" with ID ' . $dependentTable['ID'] . ' in table ' . $dependentTable['dependentTable']);
            } else {
                throw new Exception('Other objects has "' . $this->objectName . '" as parent.');
            }
        }
    }

}

?>
