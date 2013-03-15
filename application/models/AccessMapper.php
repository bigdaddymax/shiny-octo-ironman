<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AccessMapper
 *
 * @author Olenka
 */
class Application_Model_AccessMapper extends BaseDBAbstract {

    private $user;
    private $credentials;
    private $acl;

    public function __construct($userId = null) {
        parent::__construct();

        $dataMapper = new Application_Model_DataMapper();
        if (empty($userId)) {
            $session = new Zend_Session_Namespace('auth');
            $userId = $session->userId;
        }
        $this->acl = new Zend_Acl();
        $this->acl->addResource('admin');
        $this->acl->addResource('levels', 'admin');
        $this->acl->addResource('orgobjects', 'admin');
        $this->acl->addResource('elements', 'admin');
        $this->acl->addResource('users', 'admin');
        $this->acl->addRole('guest');
        $this->acl->addRole('staff', 'guest');
        $this->acl->addRole('manager', 'staff');
        $this->acl->addRole('admin', 'manager');
        $this->acl->deny('guest', null);
        $this->acl->allow('admin', 'admin');
        $this->user = $dataMapper->getObject($userId, 'Application_Model_User');
        $this->credentials = $dataMapper->getAllObjects('Application_Model_Privilege', array('userId' => $userId));

        // Create new role based on user login
        // Retrieve parent privilege group fo user. If no group, user's base group is 'guest'
        $userRole = $dataMapper->getAllObjects('Application_Model_UserGroup', array('userId' => $userId));
        if (!($userRole[0] instanceof Application_Model_UserGroup)) {
            $role = 'guest';
        } else {
            $role = $userRole[0]->role;
        }
        
        // Add new role based on user login with parent role
        $this->acl->addRole($this->user->login, $role);
        
        // Check for user's credentials
        if (is_array($this->credentials) && !empty($this->credentials)) {
            foreach ($this->credentials as $credential) {
                // If user is granted some additional privileges to resources access besides his usergroup lets add them
                if ('resource' == $credential->objectType) {
                    $resource = $dataMapper->getObject($credential->objectId, 'Application_Model_Resource');
                    $resourceName = $resource->resourceName;
                } else {
                    // If user is granted privileges to some actions 
                    $resourceName = $credential->objectType . '_' . $credential->objectId;
                    $this->acl->addResource($resourceName);
                }
                $this->acl->allow($this->user->login, $resourceName, $credential->privilege);
            }
        } else {
            throw new Exception('No privileges loaded from DB');
        }
    }

    /**
     * Reinitialize credentials for user
     * 
     * @param type $userId
     */
    public function reinit($userId = null) {
        self::__construct($userId);
    }

    /**
     * Addon to standart ACL->isAllowed() procedure.
     * As we use dynamic resources table first we check if resource exists in user's table.
     * If not - user is denyed access to this resource.
     * If yes - we use standart acl->isAllowed method to determine user privilege.
     * 
     * @param type $user
     * @param type $resource
     * @param type $privilege
     * @return boolean
     * 
     */
    public function isAllowed($user, $resourceType, $privilege, $resourceId = null) {
        if (empty($resourceId)) {
            $resource = $resourceType;
        } else {
            $resource = $resourceType . '_' . $resourceId;
        }
        if (!$this->acl->has($resource)) {
            return false;
        }
        return $this->acl->isAllowed($user, $resource, $privilege);
    }

}

?>
