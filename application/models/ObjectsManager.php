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
require_once APPLICATION_PATH . '/models/BaseDBAbstract.php';

class Application_Model_ObjectsManager extends BaseDBAbstract {

    private $dataMapper;
    private $session;

    public function __construct() {
        parent::__construct();
        $this->dataMapper = new Application_Model_DataMapper();
        $this->session = new Zend_Session_Namespace('Auth');
    }

    /**
     * 
     * @param Application_Model_Form $form
     * @param array() $form
     * @return boolean false if not succesful
     * @return int FormID if succesfull
     */
    public function saveForm($form) {
        $formId = false;
        $formData = array();
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
            $formId = $this->dataMapper->checkObjectExistance($form);
            if ($formId) {
                // We will update form data. Dont forget, that we have to update (or add new) items as well.
                unset($formData['formId']);
                $this->dbLink->update('form', $formData, array('formId' => $formId));
                $this->dbLink->delete('item', array('formId' => $formId));
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
                $this->dataMapper->saveObject($item);
            }
        } else {
            throw new InvalidArgumentException('Form data are not valid.');
        }
        return $formId;
    }

    /**
     * getForm() - returns form searched by ID
     * @param int $formId 
     * @return Application_Model_Form
     */
    public function getForm($formId) {
        if (!is_int($formId)) {
            throw new InvalidArgumentException('Form ID should be integer.');
        }
        $formArray = $this->dbLink->fetchRow($this->dbLink->quoteinto('SELECT * FROM form WHERE formId=?', $formId));
        if (!is_array($formArray)) {
            throw new Exception('Form with ID ' . $formId . ' doesnt exist.');
        }
        $form = new Application_Model_Form($formArray);
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

    public function prepareFormForOutput($formId) {
        if (!empty($formId)) {
            $form['form'] = $this->getForm($formId);
            $form['owner'] = $this->dataMapper->getObject($form['form']->userId, 'Application_Model_User');
            $form['node'] = $this->dataMapper->getObject($form['form']->nodeId, 'Application_Model_Node');
            if (-1 != $form['node']->parentNode) {
                $form['parentNode'] = $this->dataMapper->getObject($form['node']->parentNodeId, 'Application_Model_Node');
            }
            $form['total'] = 0;
            foreach ($form['form']->items as $item) {
                $item->element = $this->dataMapper->getObject($item->elementId, 'Application_Model_Element');
                $form['items'][] = $item;
                $form['total'] += $item->value;
            }
        } else {
            throw new InvalidArgumentException('No $formId provided.');
        }
        return $form;
    }

    /**
     * Return array of objects if there are any, false otherwise
     * @todo FILTER Functionality Use filter to limit forms in selection
     * @param type $filter
     */
    public function getAllForms($filter = null) {
        $formArray = $this->dataMapper->dbLink->fetchAll('SELECT * FROM form ' . $this->dataMapper->prepareFilter($filter));
        if (!empty($formArray) && is_array($formArray)) {
            foreach ($formArray as $form) {
                $form['items'] = $this->dataMapper->getAllObjects('Application_Model_Item', array(0 => array('column' => 'formId',
                        'operand' => $form['formId'])));
                $forms[] = new Application_Model_Form($form);
            }
            return $forms;
        } else {
            return false;
        }
    }

    public function ChangeUserPassword($user) {
        
    }

    public function grantPrivilege($privilege) {
        $id = $this->dataMapper->checkObjectExistance($privilege);
        if ($id) {
            // This privilege is already granted
            return;
        } else {
            // Save new privilege
            $this->dataMapper->saveObject($privilege);
        }
    }

    public function revokePrivilege($privilege) {
        $id = $this->dataMapper->checkObjectExistance($privilege);
        if ($id) {
            // This privilege is already granted
            $this->dataMapper->deleteObject($id, 'Application_Model_Privilege');
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
        $nodes = $this->dataMapper->getAllObjects('Application_Model_Node', array(0 => array('column' => 'parentNodeId', 'operand' => -1)));
        $output = '';
        foreach ($nodes as $node) {
            $output .= '<ul id="expList_' . $node->nodeId . '">' . $this->recursiveHTMLFormer($node, $userId) . '</ul>' . PHP_EOL;
        }
        return $output;
    }

    /**
     * privilegesTable2HTML() - forms HTNL code for further output.
     * 
     * @param type $privilegesTable
     */
    public function recursiveHTMLFormer($node, $userId) {
        $result = '';
        // Trying to get nodes that are dependent on $node
        $nodes = $this->dataMapper->getAllObjects('Application_Model_Node', array(0 => array('column' => 'parentNodeId',
                'operand' => $node->nodeId)));
        // Trying to get user's privileges for this $node
        $privileges = $this->dataMapper->getAllObjects('Application_Model_Privilege', array(0 => array('column' => 'userId',
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

    public function saveScenario($scenario) {
        $scenarioId = false;

        if (($scenario instanceof Application_Model_Scenario) && $scenario->isValid()) {
            $scenarioData = $scenario->toArray();
        } elseif (is_array($scenario)) {
            $scenarioTest = new Application_Model_Scenario($scenario);
            if (!$scenarioTest->isValid()) {
                throw new InvalidArgumentException('Argument should be array() or valid instance of Application_Model_Scenario class');
            }
        }
        $scenarioData = $scenario->toArray();
        // We have to handle Items saving separatly
        if (!empty($scenarioData['entries'])) {
            // Remove items data from Form array for storing in DB
            // We process Items and Form separatelly 
            $entries = $scenarioData['entries'];
            unset($scenarioData['entries']);
        }
        // Type casting before storing data to DB
        if (isset($scenarioData['active'])) {
            $scenarioData['active'] = (int) $scenarioData['active'];
        }
        $scenario->entries = null;
        $scenarioId = $this->dataMapper->checkObjectExistance($scenario);
        if ($scenarioId) {
            // We will update form data. Dont forget, that we have to update (or add new) items as well.
            unset($scenarioData['scenarioId']);
            $this->dbLink->update('scenario', $scenarioData, array('scenarioId' => $scenarioId));
            $this->dbLink->delete('scenario_entry', array('scenarioId' => $scenarioId));
        } else {
            // Creating new form
            $this->dbLink->insert('scenario', $scenarioData);
            $scenarioId = (int) $this->dbLink->lastInsertId();
        }
        foreach ($entries as $entry) {
            $entry->scenarioId = $scenarioId;
            $this->dataMapper->saveObject($entry);
        }

        return $scenarioId;
    }

    public function getScenario($scenarioId) {
        if (!is_int($scenarioId)) {
            throw new InvalidArgumentException('Invalid argumment. $scenarioId should be integer');
        }
        $scenarioArray = $this->dbLink->fetchRow($this->dbLink->quoteinto('SELECT * FROM scenario WHERE scenarioId = ?', $scenarioId));
        if (!$scenarioArray) {
            return false;
        }
        $scenarioArray['entries'] = $this->dataMapper->getAllObjects('Application_Model_ScenarioEntry', array(0 => array('column' => 'scenarioId', 'operand' => $scenarioId)));
        $scenario = new Application_Model_Scenario($scenarioArray);
        if ($scenario->isValid()) {
            return $scenario;
        } else {
            throw new Exception('Something wrong, cannot create valid instance of Application_Model_Scenario');
        }
    }

    public function getAllScenarios($filter = null) {
        $result = array();
        $scenarios = $this->dbLink->fetchAll('SELECT * FROM scenario ' . $this->dataMapper->prepareFilter($filter));
        foreach ($scenarios as $scenario) {
            $entries = $this->dataMapper->getAllObjects('Application_Model_ScenarioEntry', array(0 => array('column' => 'scenarioId',
                    'operand' => $scenario['scenarioId'])));
            $scenario['entries'] = $entries;
            $scenario = new Application_Model_Scenario($scenario);
            $result[] = $scenario;
        }
        return $result;
    }

    /**
     * saveObject() - unified method for saving new and/or modified objects to database
     *                from the web input.
     *                Main tasks - parse JSON or Array() representation of the input data
     *                and performing appropriate manipulation with object(s)
     * $inputData - array() of parameters passed from the web. Every array should contain 
     *              objectType item that actualy describes what kind of object we are dealing with.
     */
    public function saveObject($inputData) {
        if (!is_array($inputData)) {
            throw new InvalidArgumentException('Input data should be array for saveObject method');
        }
        switch ($inputData['objectType']) {
            case 'node':
                if ($inputData['nodeId']) {
                    $node = $this->dataMapper->getObject($inputData['nodeId'], 'Application_Model_Node');
                    $node->parentNodeId = $inputData['parentNodeId'];
                    $node->nodeName = $inputData['nodeName'];
                    $this->dataMapper->saveObject($node);
                    // Are we going to assign scenario to this node?
                    if (-1 != $inputData['scenarioId']) {
                        // Yes, lets check if scenarioId is from valid scenario
                        $scenario = $this->dataMapper->getObject($inputData['scenarioId'], 'Application_Model_Scenario');
                        if ($scenario) {
                            $assignmentArray = array('nodeId' => $node->nodeId,
                                'scenarioId' => $scenario->scenarioId,
                                'domainId' => $this->session->domainId);
                            $assignment = new Application_Model_ScenarioAssignment($assignmentArray);
                            $this->dataMapper->saveObject($assignment);
                        }
                    } else {
                        $assignment = $this->dataMapper->getAllObjects('Application_Model_ScenarioAssignment', array(0 => array('column' => 'nodeId',
                                'operand' => $node->nodeId)));
                        if ($assignment) {
                            $this->dataMapper->deleteObject($assignment[0]->scenarioAssignmentId, 'Application_Model_ScenarioAssignment');
                        }
                    }
                    return true;
                }

                break;
        }
        return false;
    }

    public function deleteScenario($scenarioId) {
//        if (!$this->dataMapper->checkObjectDependencies($scenarioId, 'Application_Model_Scenario')) {
        $entries = $this->dataMapper->getAllObjects('Application_Model_ScenarioEntry', array(0 => array('column' => 'scenarioId', 'operand' => $scenarioId)));
//            exit;
        if (is_array($entries)) {
            foreach ($entries as $entry) {
                $this->dbLink->delete('scenario_entry', $this->dbLink->quoteinto('scenarioEntryId =?', $entry->scenarioEntryId));
            }
        }
        $this->dbLink->delete('scenario', $this->dbLink->quoteinto('scenarioId =?', $scenarioId));
        //      } else {
        //        return false;
        //  }
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
        $form = $this->getForm($formId);
        // Lets find what scenarios are assigned to this form (node)
        $assignments = $this->dataMapper->getAllObjects('Application_Model_ScenarioAssignment', array(0 => array('column' => 'nodeId', 'operand' => $form->nodeId)));
        if ($assignments) {
            foreach ($assignments as $assignment) {
                $scenarios[] = $this->getScenario($assignment->scenarioId);
            }
        } else {
            // No scenarios found, we cannot process approvement for this form
            return false;
        }

        // Lets check if this user already performed any approve/decline actions on this form
        $entryArray = array('formId' => $formId, 'userId' => $userId, 'decision' => $decision, 'domainId'=>$this->session->domainId);
        $entry = new Application_Model_ApprovalEntry($entryArray);
        $myApproval = $this->dataMapper->getAllObjects('Application_Model_ApprovalEntry', array(0 => array('column' => 'formId',
                'operand' => $formId),
            1 => array('column' => 'userId',
                'operand' => $userId)));
        // If yes, we just modify previous entry. If user changed his mind about this form new decision should overwrite old one.
        // +++++++++ FIXME FIXME ++++++ 
        // We have to check also if form was approved by user that is next in scenario. If so - we cannot touch the decision.
        // Otherwise we would have to cancel all following decisions and start process again
        // ++++++++++++++++++++++++++++
        $existingApprovals = $this->dataMapper->getAllObjects('Application_Model_ApprovalEntry',
                                                                  array(0 => array('column' => 'formId',
                                                                                   'operand' => $formId)));
        echo count($existingApprovals) . PHP_EOL;
        if ($myApproval && count($existingApprovals) > $scenarios[0]->getUserOrder($userId)) {
            $entry->approvalEntryId = $myApproval[0]->approvalEntryId;
            $entryId = $this->dataMapper->saveObject($entry);
            return $entryId;
        } else {
            // If not, we have to determine if this is user's turn to do approval.
            // Lets check if there are other approvals
            if ($existingApprovals){
                // There are
                if ((count($existingApprovals) + 1) == $scenarios[0]->getUserOrder($userId)){
                    $entryId = $this->dataMapper->saveObject($entry);
                } 
            } else {
                // No others. Lets see if we are first in the line
                if ($scenarios[0]->getUserOrder($userId) == 1){
                    // Yes, we are.
                    $entryId = $this->dataMapper->saveObject($entry);
                }
            }
        }
        return $entryId;
    }

}

?>
