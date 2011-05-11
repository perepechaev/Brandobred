<?php

require_once(dirname(__FILE__) . '/PollingCriteria.class.php');

class PollingAccessorComponent extends ObjectAccessorComponent
{
    
    public function getAnswerById($answer_id){
        $criteria   = new PollingCriteria();
        $criteria->setAnswerId($answer_id);
        
        return $this->getByCriteria($criteria);
    }
    
}

?>