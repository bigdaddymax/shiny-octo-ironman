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
 * getPrivilegesTable() - function works with recursive iterator recursiveLevelGetter() to form
 * multilevel array of levels and orgobjects for HTML output.
 * @param type $userId
 * @return type
 * 
 */    
    public function getPrivilegesTable($userId) {
        // Start with selecting topmost levels (that are not dependent)
        $levels = $this->dataMapper->getAllObjects('Application_Model_Level', array('parentLevelId' => -1));
        $count = 0;
        foreach ($levels as $level) {
            $output[] = $this->recursiveLevelGetter($level, $userId);
            $count++;
        }
        return $output;
    }

/**
 * recursiveLevelGetter() - our recursive iterator.
 * @param type $level
 * @param type $userId
 * @return type
 * 
 */    
    private function recursiveLevelGetter($level, $userId) {
        // Trying to get levels that are dependent on $level
        $descs = $this->dataMapper->getAllObjects('Application_Model_Level', array('parentLevelId' => $level->levelId));
        // Trying to get user's privileges for this $level
        $privilege = $this->dataMapper->getAllObjects('Application_Model_Privilege', array('userId' => $userId,
            'objectType' => 'level',
            'objectId' => $level->levelId));
        // Trying to get orgobjects that belong to this $level
        $orgobjects = $this->dataMapper->getAllObjects('Application_Model_Orgobject', array('levelId' => $level->levelId));
        // If we have dependent levels do recursion
        if ($descs) {
            foreach ($descs as $desc) {
                $result['levels'] = $this->recursiveLevelGetter($desc, $userId);
            }
        }
        // Fill in output array
        $result['objectName'] = $level->levelName;
        $result['objectType'] = 'level';
        $result['objectId'] = $level->levelId;
        if ($orgobjects) {
            $count = 0;
            foreach ($orgobjects as $orgobject) {
                $result['orgobjects'][$count]['objectName'] = $orgobject->orgobjectName;
                $result['orgobjects'][$count]['objectType'] = 'orgobject';
                $result['orgobjects'][$count]['objectId'] = $orgobject->orgobjectId;
                $objectPriv = $this->dataMapper->getAllObjects('Application_Model_Privilege', array('userId' => $userId,
                    'objectType' => 'orgobject',
                    'objectId' => $orgobject->orgobjectId));
                if ($objectPriv){
                    $result['orgobjects'][$count]['privilege'] = $objectPriv[0]->privilege;
                }
            }
        }
        if ($privilege) {
            $result['privilege'] = $privilege[0]->privilege;
        }
        return $result;
    }
    
/**
 * privilegesTable2HTML() - forms HTNL code for further output.
 * 
 * @param type $privilegesTable
 */    
    
    public function privilegesTable2HTML($privilegesTable){
        
    }

}

?>
