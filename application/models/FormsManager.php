<?php

class Application_Model_FormsManager extends Application_Model_ObjectsManager {

    private $userId;

    public function __construct($domainId, $userId = NULL) {
        parent::__construct($domainId, 'form');
        $this->userId = $userId;
    }

    /**
     * 
     * @param integer $formId
     * @param integer $userId
     * @return array
     * @throws InvalidArgumentException
     */
    public function prepareFormForOutput($formId, $userId) {
        if (!empty($formId)) {
            $form['form'] = $this->getObject('form', $formId, $userId);
            $form['owner'] = $this->getObject('User', $form['form']->userId);
            $form['node'] = $this->getObject('Node', $form['form']->nodeId);
            $form['contragent'] = $this->getObject('Contragent', $form['form']->contragentId);
            if (-1 != $form['node']->parentNodeId) {
                $form['parentNode'] = $this->getObject('Node', $form['node']->parentNodeId);
            }
            $form['total'] = 0;
            foreach ($form['form']->items as $item) {
                $item->element = $this->getObject('Element', $item->elementId);
                $form['items'][] = $item;
                $form['total'] += $item->value;
            }
        } else {
            throw new InvalidArgumentException('No $formId provided.');
        }
        return $form;
    }

    /**
     * approveForm() method is used to approve/decline forms
     * @param type $formId
     * @param type $userId
     * @param type $decision
     * @return boolean|null
     */
    public function approveForm($formId, $userId, $decision) {
        if ($this->isApprovalAllowed($formId, $userId)) {
            $savedEntry = $this->getAllObjects('approvalEntry', array(
                0 => array('column' => 'formId',
                    'operand' => $formId),
                1 => array('column' => 'userId',
                    'operand' => $userId))
            );
            if (empty($savedEntry)) {
                $appovalEntry = new Application_Model_ApprovalEntry(array('domainId' => $this->domainId,
                    'decision' => $decision,
                    'userId' => $userId,
                    'formId' => $formId));
            } else {
                $appovalEntry = new Application_Model_ApprovalEntry(array('domainId' => $this->domainId,
                    'decision' => $decision,
                    'userId' => $userId,
                    'formId' => $formId,
                    'approvalEntryId' => $savedEntry[0]->approvalEntryId));
            }
            return $this->saveObject($appovalEntry);
        }
        throw new WrongApprovalOrder('User ' . $userId . ' cannot approve.');
    }

    public function getFormOwner($formId) {
        if (!$formId) {
            throw new InvalidArgumentException('Form ID not provided.', 417);
        }
        return $this->getObject('user', $this->dataMapper->getFormOwner($formId));
    }

    /**
     * 
     * @param int $formId
     * @param string $actionType approve | decline | comment |confirm
     * @return array of string Return array of email which should be notified about 
     *                         last action with form
     */
    public function getEmailingList($formId, $actionType) {
        $approvalList = array_reverse($this->getApprovalStatus($formId));
        $owner = $this->getFormOwner($formId);
        $email['owner'] = $owner->login;
        if ('approve' == $actionType || 'decline' == $actionType) {
            foreach ($approvalList as $entry) {
                if ('decline' == $entry['decision']) {
                    break;
                }
                if (null == $entry['decision']) {
                    $newlist[] = $this->getObject('user', $entry['userId']);
                    break;
                }
            }
        } elseif ('comment' == $actionType) {
            foreach ($approvalList as $entry) {
                if (null != $entry['decision']) {
                    $newlist[] = $this->getObject('user', $entry['userId']);
                }
            }
        }
        if (is_array($newlist)) {
            foreach ($newlist as $item) {
                $email['other'][] = $item->login;
            }
        }
        return $email;
    }

    /**
     * Create email HTML body according to input parameters from template that is stared in database.
     * @param type $email Destination email (user)
     * @param type $emailType approved_next | declined_next | commented_next | approved_owner | declined_owner | commented_owner
     * @param type $lang
     * @param type $formId
     * @return string HTML code with body of email with %link% for further link addition
     */
    public function createEmailBody($email, $emailType, $lang, $formId) {
        $templateArray = $this->getAllObjects('template', array(0 => array('column' => 'language', 'operand' => $lang), 1 => array('column' => 'type', 'operand' => $emailType)));
        if (!$templateArray) {
            $template = $this->config->template->default->$emailType;
        } else {
            $template = $templateArray[0]->body;
        }
        if (!$template) {
            $template = file_get_contents(APPLICATION_PATH . '/../library/Capex/lang/' . $lang . '/templates/' . $emailType . '.html');
        }
        if (!$template) {
            throw new UnableToLoadMessageTemplate('Input parameters: email: ' . $email . '; type: ' . $emailType . '; language: ' . $lang . '; formId: ' . $formId);
        }
        $user = $this->getAllObjects('user', array(0 => array('column' => 'login', 'operand' => $email)));
        $form = $this->prepareFormForOutput($formId, $user[0]->userId);
        $body = str_replace('%name%', $user[0]->userName, $template);
        $body = str_replace('%total%', sprintf('$%01.2f', $form['total']), $body);
        $body = str_replace('%contragent%', $form['contragent']->contragentName, $body);
        $body = str_replace('%fname%', $form['form']->formName, $body);
        return $body;
    }

    public function sendEmail($email, $body, $subject) {

        $mail = new Zend_Mail();
        $mail->setBodyHtml($body);
        $mail->setSubject($subject);
        $mail->addTo($email);
        $mail->setFrom($this->config->app->default->from);
        $mail->send();

        return true;
    }

    public function prepareCommentsForOutput($formId) {
        $comments = $this->getAllObjects('comment', array(0 => array('column' => 'formId', 'operand' => $formId)));
        if ($comments) {
            foreach ($comments as $comment) {
                $author = $this->getObject('user', $comment->userId);
                $row[] = '<div id="form-item">' . PHP_EOL .
                        '<div class="row">' . PHP_EOL .
                        '<div class="float: left"><strong>' . $author->userName . '</strong></div>' . PHP_EOL .
                        '<div class="float: right">' . $comment->date . '</div>' . PHP_EOL .
                        '<div class="display: block; clear: both;"></div>' .
                        '</div>' . PHP_EOL .
                        '<div class="comment">' . $comment->comment . '</div>' . PHP_EOL .
                        '</div>' . PHP_EOL;
            }
        }
        return (isset($row)) ? $row : false;
    }

    public function getApprovalStatus($formId) {
        return $this->dataMapper->getApprovalStatus($formId);
    }

    /**
     * Check if given user can approve form based on his credentials and form state
     * @param integer $formId
     * @param integer $userId
     * @return boolean true | false
     * @throws Exception
     */
    public function isApprovalAllowed($formId, $userId) {

        /**
         * getApprovalStatus() - returns array of format:
         *  array(0=> array(
         *                  'userId'=>444,
         *                  'decision'=>NULL,
         *                  'formId'=> 2,
         *                  'login'=>'user login1',
         *                  'orderPos'=>4,
         *                  'date'=>NULL
         *                  ),
         *        1=>array(
         *                  'userId'=>222,
         *                  'decision'=>NULL,
         *                  'formId'=> 2,
         *                  'login'=>'user login1',
         *                  'orderPos'=>2,
         *                  'date'=>NULL
         *                  ),
         *        2=>array(
         *                  'userId'=>111,
         *                  'decision'=>'approve',
         *                  'formId'=> 2,
         *                  'login'=>'user login1',
         *                  'orderPos'=>1,
         *                  'date'=>'02-12-2013'
         *                  ),
         * 
         *       )
         *  Order - orderPos, means the position of user in queue of approval process, the lesser number - the sooner user should approve
         * If decision and date equals NULL, user didnt make his decision yet.
         */
        $approvalStatus = $this->dataMapper->getApprovalStatus($formId);
        if (!empty($approvalStatus)) {
            foreach ($approvalStatus as $key => $apEntry) {
                if (
                        ($userId == $apEntry['userId']) &&
                        (
                        (!isset($approvalStatus[$key + 1]) && NULL == $apEntry['decision'] ) ||
                        (isset($approvalStatus[$key + 1]) && 'approve' == $approvalStatus[$key + 1]['decision'] && NULL == $apEntry['decision']) ||
                        (NULL <> $apEntry['decision'] && isset($approvalStatus[$key - 1]) && NULL == $approvalStatus[$key - 1]['decision']) ||
                        (NULL <> $apEntry['decision'] && !isset($approvalStatus[$key - 1]))
                        )
                ) {
                    return true;
                }
            }
            return false;
        } else {
            throw new InvalidArgumentException('Cannot find approval status for form ' . $formId . ' and user ' . $userId);
        }
    }

    /**
     * createAccessFilterArray() function creates preformatted array in form that 
     *                           prepareFilter() method understands to add to all
     *                           database requests condition to restrict functions
     *                           access data that current user is not allowed to.
     *                              
     * @return array
     * 
     */
    public function createAccessFilterArray($userId) {
        $accessMapper = new Application_Model_AccessMapper($userId, $this->domainId);
        $accessibleIds = $accessMapper->getAllowedObjectIds();
        if (!empty($accessibleIds['read'])) {
            return array(0 => array('condition' => 'IN', 'column' => 'nodeId', 'operand' => $accessibleIds['read']));
        } else {
            return false;
        }
    }

    public function getNumberOfPages($object, $filterArray, $recordsPerPage) {
        $this->setClassAndTableName($object);
        return $this->dataMapper->getNumberOfPages($this->tableName, $filterArray, $recordsPerPage);
    }

}