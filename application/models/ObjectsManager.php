<?php

/**
 * Description of ObjectsManager
 *
 * @author Max
 * 
 * 
 */
require_once APPLICATION_PATH . '/models/BaseDBAbstract.php';

class Application_Model_ObjectsManager extends BaseDBAbstract {

    private $dataMapper;

    public function __construct() {
        parent::__construct();
        $this->dataMapper = new Application_Model_DataMapper();
    }

    /**
     * 
     * @param Application_Model_Form $form
     * @param array() $form
     * @return boolean false if not succesful
     * @return int FormID if succesfull
     */
    public function SaveFormData($form) {
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
            if ($this->dataMapper->checkObjectExistance($form)) {
                // We will update form data. Dont forget, that we have to update (or add new) items as well.
                unset($formData['formId']);
                $formId = (int) $form->formId;
                $this->dbLink->update('forms', $formData, array('formId' => $formId));
                $this->dbLink->delete('items', array('formId' => $formId));
            } else {
                // Creating new form
                if (!isset($formData['date'])) {
                    $formData['date'] = date('Y-m-d H:i:s');
                }
                $this->dbLink->insert('forms', $formData);
                $formId = (int) $this->dbLink->lastInsertId();
            }
            foreach ($items as $item) {
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
        $formArray = $this->dbLink->fetchRow($this->dbLink->quoteinto('SELECT * FROM forms WHERE formId=?', $formId));
        if (!is_array($formArray)) {
            throw new Exception('Form with ID ' . $formId . ' doesnt exist.');
        }
        $form = new Application_Model_Form($formArray);
        $itemsArray = $this->dbLink->fetchAll($this->dbLink->quoteinto('SELECT * FROM items WHERE formId=?', $formId));
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
        $formArray = $this->dataMapper->dbLink->fetchAll('SELECT * FROM forms WHERE 1=1 ');
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

}

?>
