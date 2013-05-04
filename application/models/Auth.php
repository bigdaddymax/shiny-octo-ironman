<?php

/**
 * Description of Auth
 *
 * @author Olenka
 */
require_once 'BaseDBAbstract.php';
class Application_Model_Auth extends BaseDBAbstract {

    //put your code here
    private $passwordChecker;

    public function __construct() {
        $this->passwordChecker = new Capex_PasswordHash(8, TRUE);
        parent::__construct();
    }

    public function hashPassword($password) {
        return $this->passwordChecker->HashPassword($password);
    }

    public function checkUserPassword($login, $password) {
        $user = $this->findUser($login);
        if (!$user){
            throw new Exception('User ' . $login . ' not found.');
        }
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
