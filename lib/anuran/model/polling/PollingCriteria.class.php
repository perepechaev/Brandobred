<?php

require_once(PATH_MODEL. '/object/ObjectCriteriaComponent.class.php');

class PollingCriteria extends ObjectCriteriaComponent
{
    private $answerId;
    
    private $pollingId;
    
    public function setQuestion($question){
        $this->setTitle($question);
    }
    
    public function setAnswerId($answer_id){
        equal(is_numeric($answer_id));
        $this->answerId = $answer_id;
    }
    
    public function setAnswer(PollingAnswerDataComponent $answer){
        $this->answerId = $answer->id;
    }
    
    public function getAnswerId(){
        return $this->answerId;
    }
    
    public function setPolling(PollingDataComponent $polling){
        $this->pollingId    = $polling->id;
    }
    
    public function getPollingId(){
        return $this->pollingId;
    }
    
    protected function build(){
        parent::build();
        
        $this->where(
            $this->isEqual('answer_id', $this->getAnswerId()),
            $this->isEqual('poll_id', $this->getPollingId())
        );
    }
    
    /**
     * @return PollingCriteria
     */
    static public function create(){
        return new self();
    }
}

?>