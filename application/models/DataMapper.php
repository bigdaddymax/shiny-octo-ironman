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
require_once APPLICATION_PATH . '/models/ExceptionsMapper.php';

class Application_Model_DataMapper extends BaseDBAbstract {

    protected $className;
    protected $tableName;
    protected $objectName;
    protected $objectIdName;
    protected $objectParentIdName;
    protected $domainId;

    public function __construct($domainId, $object = null) {
        parent::__construct();
        $this->domainId = $domainId;
        if ($object) {
            $this->setClassAndTableName($object);
        }
    }

    protected function setDomainId($domainId) {
        if ($domainId) {
            $this->domainId = $domainId;
        } else {
            throw new InvalidArgumentException('DomainID cannot be NULL');
        }
    }

    /**
     * Helper function, creates names of properties, tables etc for particular object.
     * 
     * @param class $object
     * @param string $object
     */
    protected function setClassAndTableName($object) {
        if (is_object($object))
            $this->className = get_class($object);
        elseif (is_string($object))
            $this->className = 'Application_Model_' . ucfirst($object);
        $this->objectName = substr($this->className, strrpos($this->className, '_') + 1);

        // Transforming camelCase names of classes to underscored_names for MySQL tables
        if (preg_match_all('/[A-Z]/', substr($this->className, strrpos($this->className, '_') + 1), $matches, PREG_OFFSET_CAPTURE)) {
            if (2 == count($matches[0])) {
                $this->tableName = substr(strtolower(substr($this->className, strrpos($this->className, '_') + 1)), 0, $matches[0][1][1]) .
                        '_' .
                        substr(strtolower(substr($this->className, strrpos($this->className, '_') + 1)), $matches[0][1][1]);
            } else {
                $this->tableName = strtolower($this->objectName);
            }
        }

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
    protected function saveObject($object) {
        if ($object->isValid()) {
            // Prepare data for inserting to DB
            $objectArray = $object->toArray();

            unset($objectArray[$this->objectIdName]);
            $objectArray['active'] = (int) $objectArray['active'];
            if ('scenario' == strtolower($this->objectName)) {
                unset($objectArray['entries']);
            }
            if ('form' == strtolower($this->objectName)) {
                unset($objectArray['items']);
            }

            // For User we have to treat password property
            if (isset($objectArray['password'])) {
                $auth = new Application_Model_Auth();
                $objectArray['password'] = $auth->hashPassword($objectArray['password']);
            }
            if ($object->{$this->objectIdName}) {
                // Object exists, so we will update it
                $this->dbLink->update($this->tableName, $objectArray, array($this->objectIdName . ' = ?' => $object->{$this->objectIdName}));
            } else {
                // Object doesnt exist, so we are creating new
                $this->dbLink->insert($this->tableName, $objectArray);
                $object->{$this->objectIdName} = (int) $this->dbLink->lastInsertId();
            }
            return (int) $object->{$this->objectIdName};
        }
        else
            throw new InvalidArgumentException($this->objectName . ' data are not valid', 417);
    }

    /**
     * 
     * @param int $id
     * @param string $class Contains Class name for object to be searched and returned. If null than instance of DataDBMapper 
     *                      should be initialized with required class name at creation time.
     * @return object of type $this->className
     * @throws InvalidArgumentException
     */
    protected function _getObject($id) {
        $objectArray = $this->dbLink->fetchRow('SELECT * FROM ' .
                $this->tableName . ' WHERE ' .
                $this->dbLink->quoteinto($this->objectIdName . '=?', $id) .
                $this->dbLink->quoteinto(' AND domainId = ?', $this->domainId));
        if (is_array($objectArray)) {
            $object = new $this->className($objectArray);
            return $object;
        } else {
            throw new InvalidArgumentException("Cannot find $this->objectName in table '$this->tableName' whith ID=$id and domainId=$this->domainId", 417);
        }
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
                    if ($parameter === null || is_array($parameter)) {
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
     * createAccessFilterArray() function creates preformatted array in form that 
     *                           prepareFilter() method understands to add to all
     *                           database requests condition to restrict functions
     *                           access data that current user is not allowed to.
     *                              
     * @return array
     * 
     */
    protected function createAccessFilterArray($userId) {
        $accessMapper = new Application_Model_AccessMapper($userId, $this->domainId);
        $accessibleIds = $accessMapper->getAllowedObjectIds();
        if (!empty($accessibleIds['read'])) {
            return array(0 => array('condition' => 'IN', 'column' => 'nodeId', 'operand' => $accessibleIds['read']));
        } else {
            return false;
        }
    }

    /**
     * 
     * @param array $filterArray - array(0=>array('condition'=>'AND', 'column'=> 'orgobject', 'comp'=>'=', 'operand'=>234))
     *                              You can omit 'comp' and 'condition' elements, default are '=' and 'AND'
     *                              Handled conditions = ['AND', 'OR', 'IN']
     *                              Handled comp = [=, >, <, <>, <=, >=]
     *                              If you need to order output: array('ORDER'=>array('column'=>'columnOrder', 'operand'=>'ASC'))
     *                              If you need to limit nmber of results:
     *                              array('LIMIT'=>array('start'=>startId, 'number'=>numberOfItems))
     * @param int $domainId         ID of domain that this user is jailed to.
     * @return string
     * @throws InvalidArgumentException
     */
    protected function prepareFilter($filterArray) {
        $result = $this->dbLink->quoteinto(' WHERE domainId = ? ', $this->domainId);
        $limit = '';
        $order = '';
        if (is_array($filterArray)) {
            foreach ($filterArray as $key => $filterElement) {
                if (is_int($key)) {
                    if (!empty($filterElement['condition']) && 'IN' == $filterElement['condition']) {
                        $inString = ' AND ' . $filterElement['column'] . ' IN (';
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
                    if ('LIMIT' == (string) $key) {
                        $limit = ' LIMIT ' . ((int) $filterElement['start']) . ', ' . ((int) $filterElement['number']);
                    } elseif ('ORDER' == $key) {
                        $order = ' ORDER BY ' . $this->dbLink->quoteinto('?', $filterElement['column']) . ' ' . $filterElement['operand'];
                    }
                }
            }
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
    protected function getAllObjects($class = null, $filter = null) {
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
     * deleteObject - deletes object permanently from DB. Just brutal delete.
     * 
     * @param int $id
     * @param string $class
     * @return boolean
     * @throws InvalidArgumentException
     * @throws Exception
     */
    protected function deleteObject($class, $id) {
        if (isset($class)) {
            $this->setClassAndTableName($class);
        } elseif (!isset($this->className)) {
            throw new InvalidArgumentException('Class name is not set.');
        }
        if ($this->checkParentObjects($class, $id)) {
            throw new DependantObjectDeletionAttempt('This object is parent to others, cannot delete', 23000);
        }
        try {
            $this->dbLink->delete($this->tableName, array($this->objectIdName . '=?' => $id));
        } catch (Zend_Db_Exception $e) {

            throw new DependantObjectDeletionAttempt($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * getObjectsCount - returns number of entries in specific table after applying optional filter
     * @param type $class
     * @param type $filter
     * @return integer
     */
    protected function getObjectsCount($class, $filter = null) {
        $this->setClassAndTableName($class);
        return $this->dbLink->fetchOne($this->dbLink->quoteinto("SELECT count($this->objectIdName) FROM $this->tableName WHERE domainId = ?", $this->domainId));
    }

    protected function getNodesAssigned() {
        /**
         * getNodesAssigned() method is a helper method that returns array of scenarios and node names and Ids 
         *                    to which these scenarios are assigned (if any).
         * @return type
         */
        return $this->dbLink->fetchAll($this->dbLink->quoteinto('SELECT s.scenarioId, s.scenarioName, n.nodeId, n.nodeName
                                        FROM scenario s 
                                        LEFT JOIN scenario_assignment a ON s.scenarioId = a.scenarioId 
                                        LEFT JOIN node n ON n.nodeId = a.nodeId WHERE n.domainId = ?', $this->domainId));
    }

    protected function checkLoginExistance($login) {
        $user = $this->dbLink->fetchRow($this->dbLink->quoteinto('SELECT * FROM user WHERE login = ?', $login));
        return (!empty($user));
    }

    /**
     * getApprovalStatus() - returns array of users that are in queue to approve with status (empty/approved/declined)
     * @param type $formId
     * @return array 
     */
    protected function getApprovalStatus($formId) {
        return $this->dbLink->fetchAll($this->dbLink->quoteinto('select 
                                                ss.userId, ae.decision, ss.formId, ss.userName, ss.login, ss.orderPos,ae.date
                                            from
                                                (select se.userId, se.orderPos, f.formId, u.userName, u.login from scenario_entry se
                                            join scenario_assignment sa on se.scenarioId = sa.scenarioId
                                            join form f on f.nodeId=sa.nodeId 
                                            join user u on u.userId=se.userId
                                             ) ss
                                                    left join
                                                approval_entry ae ON ss.userId = ae.userId and ss.formId=ae.formId where ss.formId=? ORDER BY ss.orderPos DESC', $formId));
    }

    protected function getScenario($scenarioId) {
        $scenarioId = (int) $scenarioId;
        if (empty($scenarioId)) {
            throw new InvalidArgumentException('Invalid argumment. $scenarioId should be integer');
        }
        $this->setClassAndTableName('scenario');
        $scenario = $this->_getObject($scenarioId);
        $entries = $this->getAllObjects('scenarioEntry', array(0 => array('column' => 'scenarioId', 'operand' => $scenarioId)));
        $scenario->entries = $entries;
        if ($scenario->isValid()) {
            return $scenario;
        } else {
            throw new Exception('Something wrong, cannot create valid instance of Application_Model_Scenario');
        }
    }

    protected function getAllScenarios($filter = null) {
        $result = array();
        $scenarios = $this->dbLink->fetchAll('SELECT * FROM scenario ' . $this->prepareFilter($filter));
        foreach ($scenarios as $scenario) {
            $entries = $this->getAllObjects('ScenarioEntry', array(0 => array('column' => 'scenarioId',
                    'operand' => $scenario['scenarioId'])));
            $scenario['entries'] = $entries;
            $scenario = new Application_Model_Scenario($scenario);
            $result[] = $scenario;
        }
        return $result;
    }

    protected function getFormOwner($formId) {
        $userId = $this->dbLink->fetchRow($this->dbLink->quoteinto('SELECT userId FROM form WHERE formId = ?', $formId));
        $this->setClassAndTableName('user');
        return $this->_getObject($userId);
    }

    protected function getNumberOfPages($object, $filterArray, $recordsPerPage) {
        $this->setClassAndTableName($object);
        $filter = $this->prepareFilter($filterArray);
        $count = $this->dbLink->fetchOne('SELECT count(' . $this->objectIdName . ') FROM ' . $this->tableName . ' ' . $filter);
        return ceil($count / $recordsPerPage);
    }

    /**
     * In some cases we have parent - child relation between objects but cannot set these 
     * dependancies on database level. For exmple, when an object may have or may have not a parent.
     * In this case if we set foreign key for columns and we will want to create a record for
     * object that doesn't have parent we receive MySQL error about constrains violation.
     * For some objects that might have parents or be parent to other objects we will perform
     * checking befor allow to delete them.
     * 
     * @param string | object $class
     * @param integer $id
     * @return true | false Result of check
     */
    protected function checkParentObjects($class, $id) {
        $this->setClassAndTableName($class);
        $columns = $this->dbLink->fetchOne($this->dbLink->quoteinto('SHOW COLUMNS FROM ' . $this->tableName . ' WHERE field LIKE ?', $this->objectParentIdName));
        if ($columns) {
            $objects = $this->getAllObjects($this->objectName, array(0 => array('column'=>$this->objectParentIdName, 'operand' => $id)));
            if ($objects) {
                return true;
            }
        }
        return false;
    }
    
    public function checkUserExistance($userName) {
        return $this->dbLink->fetchOne($this->dbLink->quoteinto('SELECT * FROM user WHERE username=?', $userName));
    }

    public function checkEmailExistance($email) {
        return $this->dbLink->fetchOne($this->dbLink->quoteinto('SELECT * FROM user WHERE login=?', $email));
    }
}

?>
