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
require_once 'BaseDBAbstract.php';

class Application_Model_AccessMapper extends BaseDBAbstract {

    private $user;
    private $credentials; // credentials from DB
    // As we have nodes and orgobjects as subjects of conditinal access
    // we have to transform credentials from their form as they are stored
    // For ex. if node1 includes node2 and orgobject3 we have to transform
    // single record (node1, 'read') to (node2, 'read') (orgobject3, 'read')
    // then for node2 repeat this procedure.
    // Result we store in $orgobjectPrivileges array();
    public $acl;

    public function __construct($userId, $domainId) {
        parent::__construct();
        $dataMapper = new Application_Model_DataMapper($domainId);
        $this->acl = new Zend_Acl();
        $this->acl->addResource('admin');
        $this->acl->addResource('node', 'admin');
        $this->acl->addResource('element', 'admin');
        $this->acl->addResource('user', 'admin');
        $this->acl->addResource('position', 'admin');
        $this->acl->addResource('privilege', 'admin');
        $this->acl->addResource('scenario', 'admin');
        $this->acl->addResource('error');
        $this->acl->addResource('form');
        $this->acl->addResource('new-form', 'form');
        $this->acl->addResource('open-form', 'form');
        $this->acl->addResource('add-form', 'form');
        $this->acl->addResource('auth');
        $this->acl->addResource('home');
        $this->acl->addRole('guest');
        $this->acl->addRole('staff', 'guest');
        $this->acl->addRole('manager', 'staff');
        $this->acl->addRole('admin', 'guest');
        $this->acl->deny('guest', null);
        $this->acl->allow('guest', 'error');
        $this->acl->allow('guest', 'auth');
        $this->acl->allow('guest', 'home');
// ++++++++++++++++++++++++ FIX ME ++++++++++++++++++++
        $this->acl->allow('guest', 'form');
// ++++++++++++++++++++++++++++++++++++++++++++++++++++        
        $this->acl->allow('admin', 'admin');

        if ($userId) {
            $this->user = $dataMapper->getObject($userId, 'Application_Model_User');
        if ((!$this->user) || (!$this->user->isValid())) {
                // We have session variables set up but for some reason user doesnt exist
                $session = new Zend_Session_Namespace('Auth');
                $session->unsetAll();
                throw new Exception('Trying to initialize Access Mapper with userId = '.$userId);
           }
            $this->credentials = $dataMapper->getAllObjects('Application_Model_Privilege', array(0 => array('column' => 'userId',
                    'operand' => $userId)));

            // Create new role based on user login
            // Retrieve parent privilege group fo user. If no group, user's base group is 'guest'
            $userRole = $dataMapper->getAllObjects('Application_Model_UserGroup', array(0 => array('column' => 'userId',
                    'operand' => $userId)));
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
                        if (!$this->acl->has($resourceName)) {
                            $this->acl->addResource($resourceName);
                        }
                    }
                        $this->acl->allow($this->user->login, $resourceName, $credential->privilege);
                    }
            } else {
                return;
                //throw new Exception('No privileges loaded from DB');
            }
        } else {
            throw new InvalidArgumentException('No userId provided', 500);
        }
    }

    /**
     * Reinitialize credentials for user
     * 
     * @param type $userId
     */
    public function reinit($userId, $domainId) {
        self::__construct($userId, $domainId);
    }

    /**
     * Addon to standart ACL->isAllowed() procedure.
     * As we use dynamic resources table first we check if resource exists in user's table.
     * If not - user is denyed access to this resource. By default everything is denied.
     * If yes - we use standart acl->isAllowed method to determine user privilege.
     * 
     * @param type $user
     * @param type $resource
     * @param type $privilege
     * @return boolean
     * 
     */
    public function isAllowed($resourceType, $privilege = null, $resourceId = null) {
        if (empty($resourceId)) {
            $resource = $resourceType;
        } else {
            $resource = $resourceType . '_' . $resourceId;
        }
        if (!$this->acl->has($resource)) {
            throw new Exception('User ' . $this->user->login . ' Resourse ' . $resource . ' for privilege ' . $privilege . ' not found. ', 500);
        }
//        echo $user.' => ' .$privilege. ' for '.$resource.': '. $this->acl->isAllowed($user, $resource, $privilege).PHP_EOL;   
        return $this->acl->isAllowed($this->user->login, $resource, $privilege);
    }

    
    public function getAllowedObjectIds(){
        if (!empty($this->credentials)){
            foreach ($this->credentials as $credential){
                if ('node' == $credential->objectType){
                    $result[$credential->privilege][] = $credential->objectId;
                }
            }
        }
        return $result;
    }
    
    /**
     * getAllowedObjectsIds() method returns ids of objects of specified class that 
     *                        user can read, write or approve.
     * @param string $class
     * @param string $privilege
     * @return array          Array has following format: Array('privilege1'=> array(nodeId1, nodeId2, ....),
     *                                                          'privilege2'=> array(nodeId3, nodeId4, ....))
     * 
     */
    public function getAllowedObjectIds1() {
//        Zend_Debug::dump($this->credentials);
        if (is_array($this->credentials)) {
            foreach ($this->credentials as $credential) {
                if ('node' == $credential->objectType) {
                    if (empty($result[$credential->privilege])) {
                        $result[$credential->privilege] = $this->getNodeObjects($credential->objectId);
                    } else {
                        $result[$credential->privilege] = array_merge($result[$credential->privilege], $this->getNodeObjects($credential->objectId));
                    }
                }
            }
            return $result;
        } else {
            return false;
        }
    }

    /**
     * getNodeObjects - recursive function that works in connection with getAllowedOrgobjectIds()
     * @param type $nodeId
     * @return type
     */
    private function getNodeObjects($nodeId) {
        $dataMapper = new Application_Model_DataMapper($this->user->domainId);
        $result[] = $nodeId;
        $nodes = $dataMapper->getAllObjects('Application_Model_Node', array(0 => array('column' => 'parentNodeId',
                'operand' => $nodeId)));
        if (!empty($nodes)) {
            foreach ($nodes as $node) {
                if (!empty($result)) {
                    $result = array_merge($result, $this->getNodeObjects($node->nodeId));
                } else {
                    $result = $this->getNodeObjects($node->nodeId);
                }
            }
        }
        return $result;
    }

}

?>
