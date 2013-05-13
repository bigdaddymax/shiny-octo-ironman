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

class Application_Model_ObjectsManager extends Application_Model_DataMapper {

    public function __construct($domainId, $objectType = null) {
        parent::__construct($domainId, $objectType);
        $this->domainId = $domainId;
//        $this->dataMapper = new Application_Model_DataMapper($this->domainId);
    }

    /**
     *  getObject() method - retrieve object from DB and initialize it
     *  throws Exception if object with specified ID is not found
     * @param string $objectName Name of the Object class - 'node', 'scenario', 'element' etc
     * @param object $objectId
     * @return object Particular initialized object
     */
    public function getObject($objectName, $objectId) {
        $this->setClassAndTableName($objectName);
        switch ($this->tableName) {
            case 'scenario': return $this->getScenario($objectId);
                break;
            case 'form':
            default : return parent::_getObject($objectId);
        }
    }

    /**
     * 
     * @param string $objectName
     * @param array() $filter
     * @return array of objects
     */
    public function getAllObjects($objectName = null, $filter = null) {
        parent::setClassAndTableName($objectName);
        switch ($objectName) {
            case 'scenario': return parent::getAllScenarios($filter);
                break;
            case 'form': return parent::getAllForms($filter);
                break;
            default : return parent::getAllObjects($objectName, $filter);
        }
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

        // Do we deal with existing object?
        if (!$object->{$this->objectIdName}) {
            $object->{$this->objectIdName} = $this->checkObjectExistance($object);
        }
        // Cleaning columns before updating database
        // Form and Scenario are special case - they contain 
        // a property that is array of other simple objects.
        // So we have to treat these arrays separately later
        if ('scenario' == strtolower($this->objectName)) {
            $entries = $object->entries;
        }
        if ('form' == strtolower($this->objectName)) {
            $entries = $object->items;
        }

        // Save main object
        $mainObjectId = parent::saveObject($object);
        $mainObjectIdName = $this->objectIdName;
        // Save objects that are included in main object
        if (isset($entries)) {
            $this->setClassAndTableName($entries[0]);
            foreach ($entries as $entry) {
                $entry->{$mainObjectIdName} = $mainObjectId;
                parent::saveObject($entry);
            }
        }
        return $mainObjectId;
    }

    /**
     * 
     * @param Application_Model_Form $form
     * @param array() $form
     * @param int $userId Optional parameter that allows to setup userId that will
     *            be associated with the Form
     * @return boolean false if not succesful
     * @return int FormID if succesfull
     */
    public function saveForm($form, $userId) {
        if (!($form instanceof Application_Model_Form)) {
            throw new Exception("Form should be instance of Application_Model_Form", 500);
        }
        $formId = false;
        $formData = array();
        $this->domainId = $form->domainId;
        $user = $this->getObject('user', $userId);
        if ($user->domainId != $form->domainId) {
            throw new Exception("User from domain $user->domainId tries to save form for domainId $form->domainId", 500);
        }
        // If input parameter is object we derive array from it.
        // If input parameter is array we derive object from it.
        if ($form instanceof Application_Model_Form) {
            $formData = $form->toArray();
        } elseif (is_array($form)) {
            $formData = $form;
            $form = new Application_Model_Form($formData);
        } else {
            throw new InvalidArgumentException('Argument should be array() or instance of Application_Model_Form');
        }
        // OK, we have preliminary Form object, lets check if this user has credentials 
        // to create this form
        $accessMapper = new Application_Model_AccessMapper($userId, $this->domainId);
        if (!$accessMapper->isAllowed('node', 'write', $form->nodeId)) {
            //If user has no rights throw exception
            throw new Exception('User has no write access to forms wiht nodeId=' . $form->nodeId);
        }

        if ($form->isValid()) {
            // We have to handle Items saving separatly
            if (!empty($formData['items'])) {
                // Remove items data from Form array for storing in DB
                // We process Items and Form separatelly 
                $items = $formData['items'];
                unset($formData['items']);
                $form->items = NULL;
            }
            // Type casting before storing data to DB
            if (isset($formData['active'])) {
                $formData['active'] = (int) $formData['active'];
            }
            $formId = $this->checkObjectExistance($form);
            if ($formId) {
                // We will update form data. Dont forget, that we have to update (or add new) items as well.
                unset($formData['formId']);
                $this->dbLink->update('form', $formData, array('formId=?' => $formId));
                $this->dbLink->delete('item', array('formId=?' => $formId));
            } else {
                // Creating new form
                if (!isset($formData['date'])) {
                    $formData['date'] = date('Y-m-d H:i:s');
                }
                $this->dbLink->insert('form', $formData);
                $formId = (int) $this->dbLink->lastInsertId();
            }
            foreach ($items as $item) {
                if (is_array($item)) {
                    $item = new Application_Model_Item($item);
                }
                $item->formId = $formId;
                $this->saveObject($item);
            }
        } else {
            throw new InvalidArgumentException('Form data are not valid.');
        }
        return $formId;
    }

    /**
     * getForm() - returns form searched by ID
     * @param int $formId 
     * @param int $userId Optional parameter that allows to setup userId that will
     *            be associated with the Form
     * @return Application_Model_Form
     */
    public function getForm($formId, $userId) {
        $user = $this->getObject('User', $userId);
        $formId = (int) $formId;
        if (!$formId) {
            throw new InvalidArgumentException('Incorrect FormIID');
        }

        // Lets get Form data from the database

        $formArray = $this->dbLink->fetchRow($this->dbLink->quoteinto('SELECT * FROM form WHERE formId=?', $formId));
        if (!is_array($formArray)) {
            throw new Exception('Form with ID ' . $formId . ' doesnt exist.');
        }
        // Create preliminary form object
        $form = new Application_Model_Form($formArray);
        // Lets check if user has credentials to read this Form
        $accessMapper = new Application_Model_AccessMapper($userId, $this->domainId);
        if (!$accessMapper->isAllowed('node', 'read', $form->nodeId) &&
                !$accessMapper->isAllowed('node', 'write', $form->nodeId) &&
                !$accessMapper->isAllowed('node', 'approve', $form->nodeId)
        ) {
            // No read rights, throw exception
            throw new Exception('User has no read access to forms wiht nodeId=' . $form->nodeId);
        }
        $itemsArray = $this->dbLink->fetchAll($this->dbLink->quoteinto('SELECT * FROM item WHERE formId=?', $formId));
        $items = array();
        foreach ($itemsArray as $itemArray) {
            $items[] = new Application_Model_Item($itemArray);
        }
        $form->items = $items;
        if ($form->isValid) {
            return $form;
        } else {
            throw new Exception('Couldnt build Form instance of data retrived from database.');
        }
    }

    public function prepareFormForOutput($formId, $userId) {
        if (!empty($formId)) {
            $form['form'] = $this->getForm($formId, $userId);
            $form['owner'] = $this->getObject('User', $form['form']->userId);
            $form['node'] = $this->getObject('Node', $form['form']->nodeId);
            $form['contragent'] = $this->getObject('Contragent', $form['form']->contragentId);
            if (-1 != $form['node']->parentNodeId) {
                $form['parentNode'] = $this->getObject('Node', $form['node']->parentNodeId);
            }
            $form['total'] = 0;
            foreach ($form['form']->items as $item) {
                $item->element = $this->getObject('Element', $item->elementId);
                $form['items'][] = $item;
                $form['total'] += $item->value;
            }
        } else {
            throw new InvalidArgumentException('No $formId provided.');
        }
        return $form;
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
        $id = $this->checkObjectExistance($privilege);
        if ($id) {
            // This privilege is already granted
            return;
        } else {
            // Save new privilege
            $this->dataMapper->saveObject($privilege);
        }
    }

    public function revokePrivilege($privilege) {
        $id = $this->checkObjectExistance($privilege);
        if ($id) {
            // This privilege is already granted
            $this->deleteObject($id, 'Application_Model_Privilege');
            return;
        } else {
            // This privilege doesnt exist already
            return;
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
        $output = '';
        foreach ($nodes as $node) {
            $output .= '<ul id="expList_' . $node->nodeId . '">' . $this->recursiveHTMLFormer($node, $userId) . '</ul>' . PHP_EOL;
        }
        return $output;
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
        $entries = $this->getAllObjects('ScenarioEntry', array(0 => array('column' => 'scenarioId', 'operand' => $scenarioId)));
        if (is_array($entries)) {
            foreach ($entries as $entry) {
                $this->dbLink->delete('scenario_entry', $this->dbLink->quoteinto('scenarioEntryId =?', $entry->scenarioEntryId));
            }
        }
        $this->dbLink->delete('scenario', $this->dbLink->quoteinto('scenarioId =?', $scenarioId));
    }

    public function isApprovalAllowed($formId, $userId) {
        // If this form was already approved by somebody?
        $existingApprovals = $this->getAllObjects('ApprovalEntry', array(0 => array('column' => 'formId',
                'operand' => $formId)));
        // Get this form
        $form = $this->getForm($formId, $userId);
        if (!$form->isValid()) {
            // The form is not valid, probably wrong formId. No sense to continue
            throw new Exception('Form ' . $formId . ' is not valid, cannot approve');
        }
        // Lets retrieve all possible scenarios for this form (this node)
        $assignments = $this->getAllObjects('ScenarioAssignment', array(0 => array('column' => 'nodeId',
                'operand' => $form->nodeId)));
        if ($assignments) {
            foreach ($assignments as $assignment) {
                $scenarios[] = $this->getScenario($assignment->scenarioId);
            }
        } else {
            // No scenarios found, nothing to do here
            return false;
        }

        // Lets check if we deal with conditional scenarios or not
        if (count($scenarios) > 1) {
            throw new Exception('We cannot deal with conditional approval scenarios yet. Please come later.');
        } else {
            // Lets go
            // Are there any other approvals yet? 
            if (empty($existingApprovals)) {
                // No one approved this form yet
                // Lets check if we are the first in line
                if (1 == $scenarios[0]->getUserOrder($userId)) {
                    // Yes, we are the number one, we can approve
                    return true;
                } else {
                    // No, we have to wait for our turn
                    return false;
                }
            } else {
                //OK, there are approvals already
                // Did we make last decision but now changing it?
                if ((count($existingApprovals) == $scenarios[0]->getUserOrder($userId)) ||
                        (count($existingApprovals) + 1 == $scenarios[0]->getUserOrder($userId))) {
                    // Yes
                    return true;
                }
                // There are other approvals after ours
                return false;
            }
        }
    }

    /**
     * getNodesAssigned() method is a helper method that returns array of scenarios and node names and Ids 
     *                    to which these scenarios are assigned (if any).
     * @return type
     */
    public function getNodesAssigned() {
        $scenarios = parent::getNodesAssigned();

        $assignedNodes = array();
        foreach ($scenarios as $key => $scenario) {
            $assignedNodes[$scenario['scenarioId']][$key]['nodeId'] = $scenario['nodeId'];
            $assignedNodes[$scenario['scenarioId']][$key]['scenarioName'] = $scenario['scenarioName'];
            $assignedNodes[$scenario['scenarioId']][$key]['nodeName'] = $scenario['nodeName'];
        }
        return $assignedNodes;
    }

    /**
     * approveForm() method is used to approve/decline forms
     * @param type $formId
     * @param type $userId
     * @param type $decision
     * @return boolean|null
     */
    public function approveForm($formId, $userId, $decision) {
        $entryId = false;
        $form = $this->getForm($formId, $userId);
        // Lets find what scenarios are assigned to this form (node)
        $assignments = $this->getAllObjects('ScenarioAssignment', array(0 => array('column' => 'nodeId', 'operand' => $form->nodeId)));
        if ($assignments) {
            foreach ($assignments as $assignment) {
                $scenarios[] = $this->getScenario($assignment->scenarioId);
            }
        } else {
            // No scenarios found, we cannot process approvement for this form
            throw new Exception('There is no approval scenario, dont know how did you get here', 500);
            ;
        }

        // Lets check if this user already performed any approve/decline actions on this form
        $entryArray = array('formId' => $formId, 'userId' => $userId, 'decision' => $decision, 'domainId' => $this->domainId);
        $entry = new Application_Model_ApprovalEntry($entryArray);
        $myApproval = $this->getAllObjects('ApprovalEntry', array(0 => array('column' => 'formId',
                'operand' => $formId),
            1 => array('column' => 'userId',
                'operand' => $userId)));
        // If yes, we just modify previous entry. If user changed his mind about this form new decision should overwrite old one.
        // +++++++++ FIXME FIXME ++++++ 
        // We have to check also if form was approved by user that is next in scenario. If so - we cannot touch the decision.
        // Otherwise we would have to cancel all following decisions and start process again
        // ++++++++++++++++++++++++++++
        $existingApprovals = $this->getAllObjects('ApprovalEntry', array(0 => array('column' => 'formId',
                'operand' => $formId)));
        if ($myApproval && (count($existingApprovals) == $scenarios[0]->getUserOrder($userId))) {
            $entry->approvalEntryId = $myApproval[0]->approvalEntryId;
            $entryId = $this->saveObject($entry);
            return $entryId;
        } else {
            // If not, we have to determine if this is user's turn to do approval.
            // Lets check if there are other approvals
            if ($existingApprovals) {
                // There are
                if ((count($existingApprovals) + 1) == $scenarios[0]->getUserOrder($userId)) {
                    $entryId = $this->saveObject($entry);
                } else {
                    throw new Exception('It is not user ' . $userId . ' turn to approve the form', 500);
                }
            } else {
                // No others. Lets see if we are first in the line
                if ($scenarios[0]->getUserOrder($userId) == 1) {
                    // Yes, we are.
                    $entryId = $this->saveObject($entry);
                } else {
                    throw new Exception('It is not user ' . $userId . ' turn to approve the form', 500);
                }
            }
        }
        return $entryId;
    }

    public function getEmailingList($formId) {
        $form = $this->getObject('form', $formId);
        $approvalList = array_reverse($this->getApprovalStatus($formId));
        $owner = $this->getObject('user', $form->userId);
        $email['owner'] = $owner->login;
        foreach ($approvalList as $entry) {
            if ('decline' == $entry['decision']) {
                break;
            }
            if (null == $entry['decision']) {
                $newlist[] = $this->getObject('user', $entry['userId']);
                break;
            }
        }
        if (is_array($newlist)) {
            foreach ($newlist as $item) {
                $email['approval'] = $item->login;
            }
        }
        return $email;
    }

    public function setDomainId($domainId) {
        parent::setDomainId($domainId);
    }

    public function checkObjectDependencies($class, $id) {
        return parent::checkObjectDependencies($class, $id);
    }

    public function getObjectsCount($class, $filter = null) {
        return parent::getObjectsCount($class, $filter);
    }

    public function deleteObject($class, $id) {
        return parent::deleteObject($class, $id);
    }

    public function createAccessFilterArray($userId) {
        return parent::createAccessFilterArray($userId);
    }

    public function checkLoginExistance($login) {
        return parent::checkLoginExistance($login);
    }

    public function getApprovalStatus($formId) {
        return parent::getApprovalStatus($formId);
    }

}

?>
