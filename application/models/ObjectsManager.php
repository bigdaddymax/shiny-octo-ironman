<?php

/**
 * Description of ObjectsManager
 * Class for objects manipulation. In contradiction to DataMapper doesnt deal with 
 * database itself.
 * Main task - provide save, get and delete methods for complex objects like 
 * Form and Scenario.
 *
 * @author Max
 * 
 * 
 */
require_once APPLICATION_PATH . '/models/DataMapper.php';

class Application_Model_ObjectsManager {

    protected $domainId;
    protected $dataMapper;
    private $objectType;
    protected $className;
    protected $tableName;
    protected $objectName;
    protected $objectIdName;
    protected $objectParentIdName;

    public function __construct($domainId, $objectType = null) {
        $this->dataMapper = new Application_Model_DataMapper();
        $this->domainId = $domainId;
        $this->setClassAndTableName($objectType);
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
     *  getObject() method - retrieve object from DB and initialize it
     *  throws Exception if object with specified ID is not found
     * @param string $objectName Name of the Object class - 'node', 'scenario', 'element' etc
     * @param object $objectId
     * @return object Particular initialized object
     */
    public function getObject($objectName, $objectId, $userId = null) {
        if (!$objectName) {
            throw new InvalidArgumentException('Object name is not set.', 417);
        }
        if (!$objectId) {
            throw new InvalidArgumentException('Object ID is not set.', 417);
        }
        $this->setClassAndTableName($objectName);

        // Add domainId to filter just in case...
        $filterArray = array(0 => array('column' => $this->objectIdName, 'operand' => $objectId), 1 => array('column' => 'domainId', 'operand' => $this->domainId));

        $objectArray = $this->dataMapper->getData($this->tableName, $filterArray);
        if (empty($objectArray)) {
            throw new InvalidArgumentException('Cannot find any data');
        }
        $object = new $this->className($objectArray[0]);
        switch ($this->objectName) {
            case 'Scenario': $entries = $this->getAllObjects('scenarioEntry', array(0 => array('column' => 'scenarioId',
                        'operand' => $object->scenarioId)));
                $object->entries = $entries;
                if (!$object->isValid()) {
                    throw new Exception('Couldnt retrive valid scenario with ID = ' . $objectId, 500);
                }
                break;
            case 'Form':
                if (!$userId) {
                    throw new InvalidArgumentException('UserId should be provided to access form data', 403);
                }
                $accessMapper = new Application_Model_AccessMapper($userId, $this->domainId);
                if (!$accessMapper->isAllowed('node', 'read', $object->nodeId) &&
                        !$accessMapper->isAllowed('node', 'write', $object->nodeId) &&
                        !$accessMapper->isAllowed('node', 'approve', $object->nodeId)
                ) {
                    // No read rights, throw exception
                    throw new Exception('User has no read access to forms wiht nodeId=' . $object->nodeId, 403);
                }
                $entries = $this->getAllObjects('item', array(0 => array('column' => 'formId',
                        'operand' => $object->formId)));
                $object->items = $entries;
        }
        return $object;
    }

    /**
     * 
     * @param string $objectName
     * @param array() $filter
     * @return array of objects
     */
    public function getAllObjects($objectName = null, $filter = null) {
        $this->setClassAndTableName($objectName);
        $objectArrays = $this->dataMapper->getData($this->tableName, $filter);
        $objects = array();
        if (!empty($objectArrays)) {
            foreach ($objectArrays as $objectArray) {
                $objects[] = new $this->className($objectArray);
            }
        }
        switch ($objectName) {
            case 'scenario':
                if (!empty($objects)) {
                    for ($i = 0; $i < count($objects); $i++) {
                        $objects[$i]->entries = $this->getAllObjects('scenarioEntry', array(0 => array('column' => 'domainId',
                                'operand' => $objects[$i]->domainId),
                            1 => array('column' => 'scenarioId',
                                'operand' => $objects[$i]->scenarioId)
                                )
                        );
                    }
                }
                break;
            case 'form': if (!empty($objects)) {
                    for ($i = 0; $i < count($objects); $i++) {
                        $objects[$i]->items = $this->getAllObjects('item', array(0 => array('column' => 'domainId',
                                'operand' => $objects[$i]->domainId),
                            1 => array('column' => 'formId',
                                'operand' => $objects[$i]->formId)
                                )
                        );
                    }
                }
                break;
            default :
        }
        return $objects;
    }

    /**
     * saveObject() - unified method for saving new and/or modified objects to database
     *                from the web input.
     *                Main tasks - parse JSON or Array() or Object representation of the input data
     *                and performing appropriate manipulation with object(s)
     * $inputData - array() or JSON string or Object  of parameters passed from the web. Every array should contain 
     *              objectType item that actualy describes what kind of object we are dealing with.
     */
    public function saveObject($inputData) {
        if (!is_array($inputData) && !is_object($inputData) && !is_object(json_decode($inputData))) {
            throw new InvalidArgumentException('Input data should be array or JSON or Object for saveObject method');
        }

        // Convert input data into object
        if (is_object($inputData)) {
            $this->setClassAndTableName($inputData);
            $object = $inputData;
        } elseif (is_array($inputData)) {
            $this->setClassAndTableName($inputData['objectType']);
            $object = new $this->className($inputData);
        } elseif (is_object(json_decode($inputData))) {
            $inputData = json_decode($inputData);
            $this->setClassAndTableName($inputData->objectType);
            $object = new $this->className($inputData);
        }

        // Override object's domainId with one that came from application
        $object->domainId = $this->domainId;

        if (!$object->isValid()) {
            throw new SaveObjectException($object);
        }

        $object->{$this->objectIdName} = $this->dataMapper->checkObjectExistance($this->tableName, $object->toArray());

//        }
        // Form and Scenario are special case - they contain 
        // a property that is array of other simple objects.
        // So we have to treat these arrays separately later
        if ('scenario' == strtolower($this->objectName)) {
            $entries = $object->entries;
            $object->entries = null;
        }
        if ('form' == strtolower($this->objectName)) {
            $entries = $object->items;
            $object->items = null;
        }


        // Save main object
        try {
        $mainObjectId = $this->dataMapper->saveData($this->tableName, $object->toArray());
        } catch (Exception $e) {
            throw new SaveObjectException($object);
        }
        $mainObjectIdName = $this->objectIdName;
        // Save objects that are included in main object
        if (isset($entries)) {
            $this->setClassAndTableName($entries[0]);
            // Delete old included objects first
            $old = $this->getAllObjects($this->objectName, array(0 => array('column' => $mainObjectIdName,
                    'operand' => $mainObjectId)));
            if (!empty($old)) {
                foreach ($old as $item) {
                    $this->deleteObject($this->objectName, $item->{$this->objectIdName});
                }
            }
            foreach ($entries as $entry) {
                $entry->{$mainObjectIdName} = $mainObjectId;
                $entry->{$this->objectIdName} = NULL;
                $this->saveObject($entry);
            }
        }
        return $mainObjectId;
    }

    public function ChangeUserPassword($user) {
        
    }

    /**
     * getUserGroupRole() returns role of user if it is assigned in user_group table.
     *                    So far there can be only 'admin' role assigned.
     *                    Returns empty string if no roles found
     * @param Application_Model_User $user
     * @return string
     */
    public function getUserGroupRole(Application_Model_User $user) {
        $userGroups = $this->getAllObjects('UserGroup', array(0 => array('column' => 'userId', 'operand' => $user->userId)));
        if (!empty($userGroups[0])) {
            return $userGroups[0]->role;
        } else {
            return '';
        }
    }

    public function grantPrivilege($privilege) {
        $this->setClassAndTableName($privilege);
        $id = $this->dataMapper->checkObjectExistance($this->tableName,$privilege->toArray());
        if ($id) {
            // This privilege is already granted
            return array('error' => 0, 'message' => 'This privilege is already granted', 'code' => 200);
        } else {
            // Save new privilege
            $id = $this->saveObject($privilege);
            return array('error' => 0, 'message' => 'Privilege granted', 'recordId' => $id, 'code' => 200);
        }
    }

    public function revokePrivilege($privilege) {
        $id = $this->dataMapper->checkObjectExistance('privilege', $privilege->toArray());
        if ($id) {
            // This privilege is already granted
            $this->deleteObject('Privilege', $id);
            return array('error' => 0, 'message' => 'Privilege revoked', 'code' => 200);
        } else {
            // This privilege doesnt exist already
            return array('error' => 0, 'mesage' => 'Was already deleted', 'code' => 200);
        }
    }

    /**
     * getPrivilegesTable() - function works with recursive iterator recursiveHTMLFormer() to form
     * multinode HTML list of nodes and nodes for output.
     * @param type $userId
     * @return type
     * 
     */
    public function getPrivilegesTable($userId) {
        // Start with selecting topmost nodes (that are not dependent)
        $nodes = $this->getAllObjects('Node', array(0 => array('column' => 'parentNodeId', 'operand' => -1)));
        $output = '<div id="listContainer">' . PHP_EOL;
        foreach ($nodes as $node) {
            $output .= '<ul id="expList_' . $node->nodeId . '">' . $this->recursiveHTMLFormer($node, $userId) . '</ul>' . PHP_EOL;
        }
        return $output . '</div>' . PHP_EOL;
    }

    /**
     * recursiveHTMLFormer() - iterator for getPrivilegesTable() 
     * @param type $node
     * @param type $userId
     * @return string
     */
    public function recursiveHTMLFormer($node, $userId) {
        $result = '';
        // Trying to get nodes that are dependent on $node
        $nodes = $this->getAllObjects('Node', array(0 => array('column' => 'parentNodeId',
                'operand' => $node->nodeId)));
        // Trying to get user's privileges for this $node
        $privileges = $this->getAllObjects('Privilege', array(0 => array('column' => 'userId',
                'operand' => $userId),
            1 => array('column' => 'objectType',
                'operand' => 'node'),
            2 => array('column' => 'objectId',
                'operand' => $node->nodeId)));
        $check = array('read' => null, 'write' => null, 'approve' => null);
        if ($privileges) {
            foreach ($privileges as $privilege) {
                $check[$privilege->privilege] = 'checked';
            }
        }
        // Form HTML output for node
        $result.= '<li>' . $node->nodeName .
                "<input type='checkbox' id = 'read_node_$node->nodeId' name = 'read_node_$node->nodeId' " .
                $check['read'] . ">" . PHP_EOL .
                "<input type='checkbox' id = 'write_node_$node->nodeId' name = 'write_node_$node->nodeId' " .
                $check['write'] . ">" . PHP_EOL .
                "<input type='checkbox' id = 'approve_node_$node->nodeId' name 'approve_node_$node->nodeId' " .
                $check['approve'] . ">" . PHP_EOL;
        // If node contains nodes or other nodes start new included list
        if ($nodes) {
            $result .= '<ul>' . PHP_EOL;
            foreach ($nodes as $node) {
                $result.= $this->recursiveHTMLFormer($node, $userId) . '</li>';
            }
            $result .= '</ul>';
        }
        return $result;
    }

    public function deleteScenario($scenarioId) {
        
    }

    /**
     * getNodesAssigned() method is a helper method that returns array of scenarios and node names and Ids 
     *                    to which these scenarios are assigned (if any).
     * @return type
     */
    public function getNodesAssigned() {
        $scenarios = $this->dataMapper->getNodesAssigned($this->domainId);

        $assignedNodes = array();
        foreach ($scenarios as $key => $scenario) {
            $assignedNodes[$scenario['scenarioId']][$key]['nodeId'] = $scenario['nodeId'];
            $assignedNodes[$scenario['scenarioId']][$key]['scenarioName'] = $scenario['scenarioName'];
            $assignedNodes[$scenario['scenarioId']][$key]['nodeName'] = $scenario['nodeName'];
        }
        return $assignedNodes;
    }

    public function setDomainId($domainId) {
        $this->domainId = $domainId;
    }

    public function getDomainId() {
        return $this->domainId;
    }

    public function checkObjectDependencies($class, $id) {
        return parent::checkParentObjects($class, $id);
    }

    public function getObjectsCount($class, $filter = null) {
        $this->setClassAndTableName($class);
        $filter[] = array('column' => 'domainId', 'operand' => $this->domainId);
        return $this->dataMapper->getObjectsCount($this->tableName, $filter);
    }

    public function deleteObject($class, $id) {
        $this->setClassAndTableName($class);
        switch ($this->tableName) {
            case 'form':
                $entries = $this->getAllObjects('item', array(0 => array('column' => 'formId', 'operand' => $id)));
                break;
            case 'scenario':
                $entries = $this->getAllObjects('ScenarioEntry', array(0 => array('column' => 'scenarioId', 'operand' => $id)));
                break;
            default: $entries = null;
        }
        if (is_array($entries)) {
            $this->setClassAndTableName($entries[0]);
            foreach ($entries as $entry) {
                $this->deleteObject($this->className, $entry->{$this->objectIdName});
            }
        }
        return $this->dataMapper->deleteData($this->tableName, $id);
    }

    public function createAccessFilterArray($userId) {
        return parent::createAccessFilterArray($userId);
    }

    public function checkLoginExistance($login) {
        return $this->dataMapper->checkEmailExistance($login);
    }

    public function checkUserExistance($userName) {
        return $this->dataMapper->checkUserExistance($userName);
    }

}

?>
