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
    private $session;

    public function __construct($object = null) {
        parent::__construct();
        $this->session = new Zend_Session_Namespace('Auth');
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
        if (preg_match_all('/[A-Z]/', substr($this->className, strrpos($this->className, '_') + 1), $mathces, PREG_OFFSET_CAPTURE)) {
            if (2 == count($mathces[0])) {
                $this->tableName = substr(strtolower(substr($this->className, strrpos($this->className, '_') + 1)), 0, $mathces[0][1][1]) .
                        '_' .
                        substr(strtolower(substr($this->className, strrpos($this->className, '_') + 1)), $mathces[0][1][1]);
            } else {
                $this->tableName = $this->objectName;
            }
        }
        //       echo $this->tableName . PHP_EOL;
//        $this->objectIdName = $this->objectName . 'Id';

        $this->objectIdName = lcfirst(substr($this->className, strrpos($this->className, '_') + 1)) . 'Id';
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
            // We have object supplied
            $this->setClassAndTableName($object);
            $id = $object->{$this->objectIdName};
            if (!empty($id)) {
                // We have object with ID, JUST CHECK THIS id IN DATABASE
                $stmt = $this->dbLink->query($this->dbLink->quoteinto('SELECT ' . $this->objectIdName . ' FROM ' . $this->tableName . ' WHERE ' . $this->objectIdName . '=?', $object->{$this->objectIdName}));
            } else {
                // We have object whithout ID, lets have a look if database contains data for similar object
                $filter = ' WHERE 1=1 ';
                $parameters = $object->toArray();
                foreach ($parameters as $key => $parameter) {
                    if ($parameter === null) {
                        continue;
                    }
                    $filter.=$this->dbLink->quoteinto(" AND $key = ? ", $parameter);
                }
//                echo $this->tableName;
                //             Zend_Debug::dump($filter);
                $stmt = $this->dbLink->query("SELECT $this->objectIdName FROM $this->tableName " . $filter);
                $id = $stmt->fetchColumn();
                return (($id != 0) ? $id : false);
            }
        } elseif (is_int($object) && !empty($object)) {
            // We have object ID set up so we just check this ID in database
            $stmt = $this->dbLink->query($this->dbLink->quoteinto('SELECT ' . $this->objectIdName . ' FROM ' . $this->tableName . ' WHERE ' . $this->objectIdName . '=?', $object));
        }
        $row = $stmt->fetchColumn();
        return (($row != 0) ? (int) $row : false);
    }

    /**
     * 
     * @param array $filterArray - array(0=>array('condition'=>'AND', 'column'=> 'orgobject', 'comp'=>'=', 'operand'=>234))
     *                              You can omit 'comp' and 'condition' elements, default are '=' and 'AND'
     * @return string
     * @throws InvalidArgumentException
     */
    public function prepareFilter($filterArray) {
        $result = '';
        $limit = '';
        $order = '';
        if (is_array($filterArray)) {
            $result = ' WHERE 1 = 1 ';
            foreach ($filterArray as $key => $filterElement) {
                if (is_int($key)) {
                    if (!empty($filterElement['condition']) && 'IN' == $filterElement['condition']) {
                        $inString = 'AND ' . $filterElement['column'] . ' IN (';
                        if (empty($filterElement['operand']) || !is_array($filterElement['operand'])) {
                            throw new InvalidArgumentException('For IN filter operand should be an array() type');
                        }
                        foreach ($filterElement['operand'] as $element) {
                            $inString .= $this->dbLink->quoteinto('?', $element) . ',';
                        }
                        $inString = rtrim($inString, ',');
                        $inString .= ') ';
                        $result .= $inString;
                    } else {
                        if (empty($filterElement['comp'])) {
                            $comp = '=';
                        } else {
                            $comp = $filterElement['comp'];
                        }
                        if (empty($filterElement['condition'])) {
                            $condition = 'AND';
                        } else {
                            $condition = $filterElement['condition'];
                        }
                        $result .= $condition .
                                ' ' . $filterElement['column'] . ' ' .
                                $comp . ' ' .
                                $this->dbLink->quoteinto('?', $filterElement['operand']) .
                                ' ';
                    }
                } else {
                    if ('LIMIT' == (string)$key){
                echo $key . PHP_EOL;
                        $limit = ' LIMIT ' . ((int)$filterElement['start']) . ', ' . ((int)$filterElement['number']);
                    } elseif ('ORDER' == $key) {
                        $order = ' ORDER BY ' . $this->dbLink->quoteinto('?', $filterElement['column']) . ' ' . $filterElement['operand'];
                    }
                }
            }
            // Append Domain condition. Every company/customer should be "jailed" in its domain space
            $result .= ' AND domainId = ' . $this->session->domainId;
        } else {
            $result = ' WHERE domainId = ' . $this->session->domainId;
        }
        return $result . $limit . $order;
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
        $objectsArray = $this->dbLink->fetchAll('SELECT * FROM ' . $this->tableName . $this->prepareFilter($filter));
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
                $query = $this->dbLink->quoteinto("SELECT $this->objectIdName FROM $table  WHERE  $this->objectIdName = ?", $id);
                $query .= $this->dbLink->quoteinto(" AND domainId = ? LIMIT 0, 1", $this->session->domainId);
                $count = $this->dbLink->fetchOne($query);
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

    /**
     * getObjectsCount - returns number of entries in specific table after applying optional filter
     * @param type $class
     * @param type $filter
     * @return type
     */
    public function getObjectsCount($class, $filter = null) {
        $this->setClassAndTableName($class);
        return $this->dbLink->fetchOne("SELECT count($this->objectIdName) FROM $this->tableName");
    }
    
    
    
    public function getNodesAssigned(){
      /**
     * getNodesAssigned() method is a helper method that returns array of scenarios and node names and Ids 
     *                    to which these scenarios are assigned (if any).
     * @return type
     */
        return $this->dbLink->fetchAll('SELECT s.scenarioId, s.scenarioName, n.nodeId, n.nodeName FROM scenario s LEFT JOIN scenario_assignment a ON s.scenarioId = a.scenarioId LEFT JOIN node n ON n.nodeId = a.nodeId');
        
    }

}

?>
