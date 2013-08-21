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

    public function __construct() {
        parent::__construct();
    }

    /**
     * Helper function to generate tableId or tableNameId column name from table or table_name argumments.
     * @param type $tableName table | table_name
     * @return string $objectIdName tableId | tableNameId
     */
    private function createObjectIdName($tableName) {
        if (strpos($tableName, '_')) {
            return substr($tableName, 0, strpos($tableName, '_')) . ucfirst(substr($tableName, strpos($tableName, '_') + 1)) . 'Id';
        } else {
            return $tableName . 'Id';
        }
    }

    /**
     *  Save data to DB, return ID  of the entry
     * 
     * @param string $tableName
     * @param array $data Data to be saved
     * @return int ID of saved object
     */
    public function saveData($tableName, $data) {
        $objectIdName = $this->createObjectIdName($tableName);
        if (array_key_exists($objectIdName, $data) && !empty($data[$objectIdName])) {
            $this->dbLink->update($tableName, $data, array($objectIdName . ' = ?' => $data[$objectIdName]));
            return $data[$objectIdName];
        }
        $this->dbLink->insert($tableName, $data);
        return (int) $this->dbLink->lastInsertId();
    }

    /**
     * Retrive data from DB, for particular table and satisfying search criterias
     * @param string $tableName
     * @param array $filter Description of filter array you can find below at the prepareFilter() function
     * @return array
     * @throws InvalidArgumentException
     */
    public function getData($tableName, $filter) {
        $filterString = $this->prepareFilter($filter);
        $objectArray = $this->dbLink->fetchAll('SELECT * FROM ' .
                $tableName . $filterString);
        return $objectArray;
    }

    /**
     * Searches in DB for particular object (row with data equal to supplied $data)
     * @param string $table Where to look
     * @data array | int Either array of values that we will look for in table or particular ID
     * @return true if record exists, false otherwise. If ID is not supplied we will look for 100% equivalent of $data and row data.
     */
    public function checkObjectExistance($tableName, $data) {
        $objectIdName = $this->createObjectIdName($tableName);
        if (is_array($data)) {
            $id = (!empty($data[$objectIdName])) ? $data[$objectIdName] : NULL;
            if (!empty($id)) {
                // We have object with ID, just quick and simple search
                $stmt = $this->dbLink->query($this->dbLink->quoteinto('SELECT ' . $objectIdName . ' FROM ' . $tableName . ' WHERE ' . $objectIdName . '=?', $data[$objectIdName]));
            } else {
                // We have object whithout ID, lets have a look if database contains data for similar object
                $filter = ' WHERE 1=1 ';
                foreach ($data as $key => $parameter) {
                    if ($parameter === null || is_array($parameter)) {
                        continue;
                    }
                    $filter.=$this->dbLink->quoteinto(" AND $key = ? ", $parameter);
                }
                try {
                    $stmt = $this->dbLink->query('SELECT ' . $objectIdName . ' FROM ' . $tableName . $filter);
                } catch (Exception $e) {
                    throw new Exception('SELECT ' . $objectIdName . ' FROM ' . $tableName . $filter . PHP_EOL);
                }
            }
        } elseif (is_int($data) && !empty($data)) {
            // We have object ID set up so we just check this ID in database
            $stmt = $this->dbLink->query($this->dbLink->quoteinto('SELECT ' . $tableName . 'Id FROM ' . $tableName . ' WHERE ' . $objectIdName . '=?', $data));
        }
        $id = $stmt->fetchColumn();
        return (($id != 0) ? (int) $id : 0);
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
//        $result = $this->dbLink->quoteinto(' WHERE domainId = ? ', $this->domainId);
        $result = ' WHERE 1=1 ';
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

    public function deleteData($tableName, $id) {
        $objecIdName = $this->createObjectIdName($tableName);
        if ($this->checkParentObjects($tableName, $id)) {
            throw new DependantObjectDeletionAttempt();
        }
        try {
            $this->dbLink->delete($tableName, array($objecIdName . '=?' => $id));
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
    public function getObjectsCount($tableName, $filterArray = null) {
        $objectId = $this->createObjectIdName($tableName);
        $filter = $this->prepareFilter($filterArray);
        return $this->dbLink->fetchOne('SELECT count(' . $objectId . ') FROM ' . $tableName . $filter);
    }

    /**
     * getNodesAssigned() method is a helper method that returns array of scenarios and node names and Ids 
     *                    to which these scenarios are assigned (if any).
     * @return type
     */
    public function getNodesAssigned($domainId) {

        return $this->dbLink->fetchAll($this->dbLink->quoteinto('SELECT s.scenarioId, s.scenarioName, n.nodeId, n.nodeName
                                        FROM scenario s 
                                        LEFT JOIN scenario_assignment a ON s.scenarioId = a.scenarioId 
                                        LEFT JOIN node n ON n.nodeId = a.nodeId WHERE n.domainId = ?', $domainId));
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
    public function getApprovalStatus($formId) {
        return $this->dbLink->fetchAll($this->dbLink->quoteinto('select 
                                                ss.userId, ae.decision, ss.formId, ss.userName, ss.login, ss.orderPos,ae.date
                                            from
                                                (select se.userId, se.orderPos, f.formId, u.userName, u.login, p.privilege 
                                            from scenario_entry se
                                            join scenario_assignment sa on se.scenarioId = sa.scenarioId
                                            join form f on f.nodeId=sa.nodeId 
                                            join user u on u.userId=se.userId
                                            join privilege p on u.userId=p.userId
                                            where p.objectId=f.nodeId
                                             ) ss
                                               left join
                                                approval_entry ae ON ss.userId = ae.userId and ss.formId=ae.formId where ss.formId=? AND ss.privilege="approve" ORDER BY ss.orderPos DESC', $formId));
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

    public function getFormOwner($formId) {
        return $this->dbLink->fetchRow($this->dbLink->quoteinto('SELECT userId FROM form WHERE formId = ?', $formId));
    }

    public function getNumberOfPages($tableName, $filterArray, $recordsPerPage) {
        $filter = $this->prepareFilter($filterArray);
        $objectIdName = $this->createObjectIdName($tableName);
        $count = $this->dbLink->fetchOne('SELECT count(' . $objectIdName . ') FROM ' . $tableName . ' ' . $filter);
        return ceil($count / $recordsPerPage);
    }

    /**
     * In some cases we have parent - child relation between objects but cannot set these 
     * dependencies on database level. For exmple, when an object may have or may have not a parent.
     * In this case if we set foreign key for columns and we will want to create a record for
     * object that doesn't have parent we receive MySQL error about constrains violation.
     * For some objects that might have parents or be parent to other objects we will perform
     * checking befor allow to delete them.
     * 
     * @param string | object $class
     * @param integer $id
     * @return true | false Result of check
     */
    protected function checkParentObjects($tableName, $id) {
        $objectIdName = $this->createObjectIdName($tableName);
        $columns = $this->dbLink->fetchOne($this->dbLink->quoteinto('SHOW COLUMNS FROM ' . $tableName . ' WHERE field LIKE ?', 'parent' . $objectIdName));
        if ($columns) {
            $objects = $this->getData($tableName, array(0 => array('column' => 'parent' . $objectIdName, 'operand' => $id)));
            if (!empty($objects)) {
                return true;
            }
        }
        return false;
    }

    public function checkUserExistance($userName) {
        return (int) $this->dbLink->fetchOne($this->dbLink->quoteinto('SELECT userId FROM user WHERE username=?', $userName));
    }

    public function checkEmailExistance($email) {
        return (int) $this->dbLink->fetchOne($this->dbLink->quoteinto('SELECT userId FROM user WHERE login=?', $email));
    }

}

?>
