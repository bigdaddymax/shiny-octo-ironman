<?php

class Application_Model_DashboardManager {

    private $formManager;
    private $user;

    public function __construct(Application_Model_User $user)
    {
        $this->user = $user;
        $this->formManager = new Application_Model_FormsManager($user->domainId);
    }

    public function getOwnFormsCurrentMonth()
    {
        return $this->formManager->getAllObjects('form', array(
                    0 => array(
                        'column'  => 'userId',
                        'operand' => $this->user->userId
                    ),
                    1 => array(
                        'column'  => 'date_format(date, "%m%Y" )',
                        'operand' => new Zend_Db_Expr('date_format( now(), "%m%Y")')
                    )
                        )
        );
    }

    public function getOwnFormsPrevMonth()
    {
        return $this->formManager->getAllObjects('form', array(
                    0 => array(
                        'column'  => 'userId',
                        'operand' => $this->user->userId
                    ),
                    1 => array(
                        'column'  => 'date_format(date, "%m%Y" )',
                        'operand' => new Zend_Db_Expr('date_format( now() - INTERVAL 1 MONTH, "%m%Y")')
                    )
                        )
        );
    }

    public function getOwnFormsCurrentYear()
    {
        return $this->formManager->getAllObjects('form', array(
                    0 => array(
                        'column'  => 'userId',
                        'operand' => $this->user->userId
                    ),
                    1 => array(
                        'column'  => 'date_format(date, "%Y" )',
                        'operand' => new Zend_Db_Expr('date_format( now(), "%Y")')
                    )
                        )
        );
    }

    public function getFormsForApproval()
    {
        
    }

    public function getApprovedCurrentMonth()
    {
        
    }

    public function getApprovedPrevMonth()
    {
        
    }

    public function getApprovedCurrentYear()
    {
        
    }

}
