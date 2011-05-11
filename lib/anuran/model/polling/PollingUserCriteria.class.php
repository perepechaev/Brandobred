<?php

require_once(PATH_MODEL . '/object/ObjectCriteriaComponent.class.php');

class PollingUserCriteria extends ObjectCriteriaComponent
{
    private $userIds;
    
    public function setUser(UserDataComponent $user){
        $this->userIds  = array($user->id);
    }
    
    public function setAnswer(PollingAnswerDataComponent $answer){
        PollingComponentException::create()->objectInstance($answer);
        equal(empty($this->userIds));
        
        $criteria   = PollingCriteria::create();
        $criteria->setCriteriaHead('user_id');
        $criteria->setAnswer($answer);
        
        $this->userIds = $answer->getComponent()->getUserData()->getAccessor()->arrayByCriteria( $criteria, Mysql::FETCH_COLUMN );
    }
    
    public function getUserIds(){
        return $this->userIds;
    }
    
    protected function build(){
        $this->where(
            $this->isIn('user_id', $this->getUserIds())
        );
    }

    /**
     * @return PollingUserCriteria
     */
    static public function create(){
        return new self();
    }
}

?>