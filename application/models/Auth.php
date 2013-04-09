<?php

/**
 * Description of Auth
 *
 * @author Olenka
 */
class Application_Model_Auth extends Application_Model_DataMapper {

    //put your code here
    private $passwordChecker;

    public function __construct($object = null) {
        $this->passwordChecker = new Capex_PasswordHash(8, TRUE);
        parent::__construct($object);
    }

    public function hashPassword($password) {
        return $this->passwordChecker->HashPassword($password);
    }

    public function checkUserPassword($userId, $password) {
        $user = $this->getObject($userId, 'Application_Model_User');
        return $this->passwordChecker->CheckPassword($password, $user->password);
    }

    public function findUser($login) {
        if (!empty($login)) {
            $userArray = $this->dbLink->fetchRow($this->dbLink->quoteinto('SELECT * FROM user WHERE login = ?', $login));
            if ($userArray) {
                $user = new Application_Model_User($userArray);
            } else {
                return false;
            }
            return $user;
        } else {
            throw new InvalidArgumentException('Login not provided');
        }
    }

}

?>
