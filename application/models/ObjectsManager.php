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
            $items = $form->items;

            // Remove items data from Form array for storing in DB
            unset($formData['items']);
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
     * 
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

    /**
     * Return array of objects if there are any, false otherwise
     * @todo FILTER Functionality Use filter to limit forms in selection
     * @param type $filter
     */
    public function getAllForms($filter = null) {
        $formArray = $this->dataMapper->dbLink->fetchAll('SELECT * FROM forms WHERE 1=1 ');
        if (!empty($formArray) && is_array($formArray)) {
            foreach ($formArray as $form) {
                $form['items'] = $this->dataMapper->getAllObjects('Application_Model_Item', array('formId' => $form['formId']));
                $forms[] = new Application_Model_Form($form);
            }
            return $forms;
        } else {
            return false;
        }
    }

    public function ChangeUserPassword($user) {
        
    }

    private function getLevelPrivileges($data, $levelId) {
        $level = $this->dataMapper->getAllObjects('Application_Model_Level', array('parentLevel' => -1));
    }

    public function formPrivilegesTable($userId) {
        $parentLevels = $this->dataMapper->getAllObjects('Application_Model_Level', array('parentLevelId' => -1));
        $result = array();
        $counter = 0;
        foreach ($parentLevels as $parentLevel) {
            $descendLevels = true;
            $parentLevelId = $parentLevel->levelId;
            $result[$counter]['objectType'] = 'level';
            $result[$counter]['objectId'] = $parentLevelId;
            $result[$counter]['objectName'] = $parentLevel->levelName;
            $privilege = $this->dataMapper->getAllObjects('Application_Model_Privilege', array('userId' => $userId,
                'objectType' => 'level',
                'objectId' => $parentLevelId));
            if ($privilege) {
                $result[$counter]['privilege'] = $privilege[0]->privilege;
            }
            $innerCounter = 0;
            while ($descendLevels = $this->dataMapper->getAllObjects('Application_Model_Level', array('parentLevelId' => $parentLevelId))) {
                foreach ($descendLevels as $descendLevel) {
                    $innerresult[$innerCounter]['objectType'] = 'level';
                    $innerresult[$innerCounter]['objectId'] = $descendLevel->levelId;
                    $innerresult[$innerCounter]['objectName'] = $descendLevel->levelName;
                    $innerprivilege = $this->dataMapper->getAllObjects('Application_Model_Privilege', array('userId' => $userId,
                        'objectType' => 'level',
                        'objectId' => $descendLevel->levelId)
                    );
                    if ($innerprivilege) {
                        $innerresult[$innerCounter]['privilege'] = $innerprivilege->privilege;
                    }
                    $innerCounter++;
                }
                $result[$counter]['objects'] = $innerresult;
                $parentLevelId = $descendLevel->levelId;
            }
            $counter++;
        }
        return ((empty($result)) ? false : $result);
    }

    /**
     * getPrivilegesTable() - function works with recursive iterator recursiveHTMLFormer() to form
     * multilevel HTML list of levels and orgobjects for output.
     * @param type $userId
     * @return type
     * 
     */
    public function getPrivilegesTable($userId) {
        // Start with selecting topmost levels (that are not dependent)
        $levels = $this->dataMapper->getAllObjects('Application_Model_Level', array('parentLevelId' => -1));
        $output = '';
        foreach ($levels as $level) {
            $output .= '<ul id="expList">' . $this->recursiveHTMLFormer($level, $userId) . '</ul>' . PHP_EOL;
        }
        return $output;
    }


    /**
     * privilegesTable2HTML() - forms HTNL code for further output.
     * 
     * @param type $privilegesTable
     */
    public function recursiveHTMLFormer($level, $userId) {
        $result = '';
        // Trying to get levels that are dependent on $level
        $descs = $this->dataMapper->getAllObjects('Application_Model_Level', array('parentLevelId' => $level->levelId));
        // Trying to get user's privileges for this $level
        $privilege = $this->dataMapper->getAllObjects('Application_Model_Privilege', array('userId' => $userId,
            'objectType' => 'level',
            'objectId' => $level->levelId));
        if ($privilege) {
            switch ($privilege[0]->privilege) {
                case 'read': $privType = 'read';
                    break;
                case 'write': $privType = 'write';
                    break;
                case 'approve': $privType = 'approve';
            }
        } else {
            $privType = '';
        }
        // Trying to get orgobjects that belong to this $level
        $orgobjects = $this->dataMapper->getAllObjects('Application_Model_Orgobject', array('levelId' => $level->levelId));
        // Form HTML output for level
        $result.= '<li>' . $level->levelName .
                "<input type='checkbox' id = 'read_level_$level->levelId' name = 'read_orgobject_$level->levelId' " .
                (($privType == 'read') ? 'checked' : '') . ">" .
                "<input type='checkbox' id = 'write_level_$level->levelId' name = 'read_orgobject_$level->levelId' " .
                (($privType == 'write') ? 'checked' : '') . ">" .
                "<input type='checkbox' id = 'approve_level_$level->levelId' name = 'read_orgobject_$level->levelId' " .
                (($privType == 'approve') ? 'checked' : '') . ">";
        // If level contains orgobjects or other levels start new included list
        if ($orgobjects || $descs) {
            $result .= '<ul>' . PHP_EOL;
        }
        
        // Form HTML for orgobjects
        if ($orgobjects) {
            foreach ($orgobjects as $orgobject) {
                $objectPriv = $this->dataMapper->getAllObjects('Application_Model_Privilege', array('userId' => $userId,
                    'objectType' => 'orgobject',
                    'objectId' => $orgobject->orgobjectId));
                if ($objectPriv) {
                    switch ($objectPriv[0]->privilege) {
                        case 'read': $privType = 'read';
                            break;
                        case 'write': $privType = 'write';
                            break;
                        case 'approve': $privType = 'approve';
                    }
                } else {
                    $privType = '';
                }
                $result .= '<li>' . $orgobject->orgobjectName .
                        "<input type='checkbox' id = 'read_orgobject_$orgobject->orgobjectId' name = 'read_orgobject_$orgobject->orgobjectId' " .
                        (($privType == 'read') ? 'checked' : '') . ">" .
                        "<input type='checkbox' id = 'write_orgobject_$orgobject->orgobjectId' name = 'read_orgobject_$orgobject->orgobjectId' " .
                        (($privType == 'write') ? 'checked' : '') . ">" .
                        "<input type='checkbox' id = 'approve_orgobject_$orgobject->orgobjectId' name = 'read_orgobject_$orgobject->orgobjectId' " .
                        (($privType == 'approve') ? 'checked' : '') . ">";
                '</li>' . PHP_EOL;
            }
        }
        // If we have dependent (included) levels do recursion
        if ($descs) {
            foreach ($descs as $desc) {
                $result.= $this->recursiveHTMLFormer($desc, $userId);
            }
        }
        return $result . '</li></ul>';
    }

}

?>
