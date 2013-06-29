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
    public function getObject($objectName, $objectId, $userId = null) {
        if (!$objectName) {
            throw new InvalidArgumentException('Object name is not set.', 417);
        }
        if (!$objectId) {
            throw new InvalidArgumentException('Object ID is not set.', 417);
        }
        $this->setClassAndTableName($objectName);
        $object = parent::_getObject($objectId);
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
        parent::setClassAndTableName($objectName);
        switch ($objectName) {
            case 'scenario': return parent::getAllScenarios($filter);
                break;
            case 'form': return $this->getAllForms($filter);
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
//        if (!$object->{$this->objectIdName}) {
            $object->{$this->objectIdName} = $this->checkObjectExistance($object);
//        }
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
                parent::saveObject($entry);
            }
        }
        return $mainObjectId;
    }

    /**
     * Return array of objects if there are any, false otherwise
     * @todo FILTER Functionality Use filter to limit forms in selection
     * @param type $filter
     */
    public function getAllForms($filter = null) {
        $formArray = $this->dbLink->fetchAll('SELECT * FROM form ' . $this->prepareFilter($filter));
        if (!empty($formArray) && is_array($formArray)) {
            foreach ($formArray as $form) {
                $form['items'] = $this->getAllObjects('Item', array(0 => array('column' => 'formId',
                        'operand' => $form['formId'])));
                $f = new Application_Model_Form($form);
                $decisions = $this->getApprovalStatus($form['formId']);
                if (!empty($decisions)) {
                    $f->final = (null === $decisions[0]['decision']) ? false : true;
                    $dec = array_reverse($decisions);
                    foreach ($dec as $decision) {
                        if (!empty($decision['decision'])) {
                            $f->decision = $decision['decision'];
                        }
                    }
                }
                $forms[$f->formId]['form'] = $f;
                $forms[$f->formId]['owner'] = $this->getFormOwner($f->formId);
                $forms[$f->formId]['contragent'] = $this->getObject('contragent', $f->contragentId);
                $forms[$f->formId]['node'] = $this->getObject('node', $f->nodeId);
                if (-1 <> $forms[$f->formId]['node']->parentNodeId) {
                    $forms[$f->formId]['parentNode'] = $this->getObject('node', $forms[$f->formId]['node']->parentNodeId);
                }
                $forms[$f->formId]['total'] = 0;
                foreach ($f->items as $item) {
                    $forms[$f->formId]['total'] += $item->value;
                }
            }
            return $forms;
        } else {
            return false;
        }
    }

    public function prepareFormForOutput($formId, $userId) {
        if (!empty($formId)) {
            $form['form'] = $this->getObject('form', $formId, $userId);
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
            return array('error' => 0, 'message' => 'This privilege is already granted', 'code' => 200);
        } else {
            // Save new privilege
            $id = $this->saveObject($privilege);
            return array('error' => 0, 'message' => 'Privilege granted', 'recordId' => $id, 'code' => 200);
        }
    }

    public function revokePrivilege($privilege) {
        $id = $this->checkObjectExistance($privilege);
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
        $form = $this->getObject('form', $formId, $userId);
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
        $form = $this->getObject('form', $formId, $userId);
        // Lets find what scenarios are assigned to this form (node)
        $assignments = $this->getAllObjects('ScenarioAssignment', array(0 => array('column' => 'nodeId', 'operand' => $form->nodeId)));
        if ($assignments) {
            foreach ($assignments as $assignment) {
                $scenarios[] = $this->getObject('scenario', $assignment->scenarioId);
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
                    throw new Exception('It is not user ' . $userId . ' turn to approve the form', 403);
                }
            } else {
                // No others. Lets see if we are first in the line
                if ($scenarios[0]->getUserOrder($userId) == 1) {
                    // Yes, we are.
                    $entryId = $this->saveObject($entry);
                } else {
                    throw new Exception('It is not user ' . $userId . ' turn to approve the form', 403);
                }
            }
        }
        return $entryId;
    }

    public function getFormOwner($formId) {
        if (!$formId) {
            throw new InvalidArgumentException('Form ID not provided.', 417);
        }
        return parent::getFormOwner($formId);
    }

    /**
     * 
     * @param int $formId
     * @return array of string Return array of email which should be notified about 
     *                         last action with form
     */
    public function getEmailingList($formId) {
        $approvalList = array_reverse($this->getApprovalStatus($formId));
        $owner = $this->getFormOwner($formId);
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

    public function prepareCommentsForOutput($formId){
        $comments = $this->getAllObjects('comment', array(0=>array('column'=>'formId', 'operand'=>$formId)));
        if ($comments){
            foreach ($comments as $comment) {
                $author = $this->getObject('user', $comment->userId);
                $row[] = '<div id="form-item">'. PHP_EOL .
                            '<div class="row">'. PHP_EOL .
                                '<div class="float: left"><strong>' . $author->userName. '</strong></div>'. PHP_EOL .
                                '<div class="float: right">' . $comment->date. '</div>'. PHP_EOL .
                                '<div class="display: block; clear: both;"></div>' .
                            '</div>'. PHP_EOL .
                            '<div class="comment">' . $comment->comment . '</div>' . PHP_EOL .
                        '</div>' . PHP_EOL;
            }
        }
        return (isset($row))? $row: false;
    }
    
    
    public function getNumberOfPages($object, $filterArray, $recordsPerPage){
        return parent::getNumberOfPages($object, $filterArray, $recordsPerPage);
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
