<?php
class DFPollingAccessorComponent extends ObjectAccessorComponent
{
    /**
     * (non-PHPdoc)
     * @see model/object/ObjectAccessorComponent#getByCriteria()
     * @return DFPollingDataComponent
     */
    final public function getByCriteria(ICriteria $criteria){return parent::getByCriteria($criteria);}
}

class DFPollingAnswerDataComponent extends PollingAnswerDataComponent
{
    public $id;
    
    public $poll_id;
    
    public $answer;
    
    public $count;
    
    /**
     * @var AuctionUserList
     */
    public $users;
}

class DFPollingAnswerListComponent extends ObjectListComponent
{
    
}

class DFPollingDataComponent extends PollingDataComponent
{
    public $question;
    
    public $id;
    
    public $status;
    
    public $time;
    
    /**
     * @var DFPollingAnswerListComponent
     */
    public $answers;
    
    /**
     * (non-PHPdoc)
     * @see model/polling/PollingDataComponent#getAccessor()
     * @return DFPollingAccessorComponent
     */
    public function getAccessor(){}
}

class DFPollingUserDataComponent extends PollingUserDataComponent
{
    public $id;
    
    public $answer_id;
    
    public $user_id;
    
    public $date;
    
    /**
     * (non-PHPdoc)
     * @see model/object/ObjectDataComponent#createList()
     * @return ObjectListComponent
     */
    public function createList(){}
}




?>