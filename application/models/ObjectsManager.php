<?php

/**
 * Description of ObjectsManager
 *
 * @author Max
 * 
 * 
 */
class Application_Model_ObjectsManager extends BaseDBAbstract {

    private $_objectDBMapper;

    public function __construct() {
        parent::__construct();
        $this->_objectDBMapper = new Application_Model_DataMapper();
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
            if ($this->_objectDBMapper->checkObjectExistance($form)) {
                // We will update form data. Dont forget, that we have to update (or add new) items as well.
                unset($formData['formId']);
                $formId = (int) $form->formId;
                $this->dbLink->update('forms', $formData, array('formId' => $formId));
                $this->dbLink->delete('items', array('formId' => $formId));
            } else {
                // Creating new form
                if (!isset ($formData['date'])) {
                    $formData['date'] = date('Y-m-d H:i:s');
                }
                $this->dbLink->insert('forms', $formData);
                $formId = (int) $this->dbLink->lastInsertId();
            }
            foreach ($items as $item) {
                    $item->formId = $formId;
                    $this->_objectDBMapper->saveObject($item);
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

    public function ChangeUserPassword($user) {
        
    }

}

?>
