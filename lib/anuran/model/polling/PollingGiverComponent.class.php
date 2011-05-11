<?php

require_once(dirname(__FILE__) . '/PollingUserCriteria.class.php');

class PollingGiverComponent extends ObjectGiverComponent
{
    public function answers(){
        PollingComponentException::create()->objectInstance($this->data);
        $criteria   = new PollingCriteria();
        $criteria->setPolling( $this->data ) ;
        
        return $this->getComponent()->getAnswerData()->getAccessor()->listByCriteria($criteria);
    }
    
    public function isAnswers(){
        if ($this->data->isClean()){
            return $this->data->createList();
        }
        
        return $this->answers();
    }
    
    public function saveUrl(){
        equal(UserAuthorize::instance()->isModerator());
        $id = $this->data->id ? $this->data->id : 'add';
        return Router::instance()->makeUrl(array('manager', 'polling', $id, 'save'));
    }
    
    public function voteUrl(){
        equal(false);
        return $this->contr->getController()->makeUrl($this->data, 'vote');
    }

    public function resultUrl(){
        equal(false);
        return $this->contr->getController()->makeUrl($this->data, 'result');
    }

    public function questionHtml(){
        equal(false);
        return htmlspecialchars($this->data->question);
    }

    public function htmlResultFoot(){
        equal(false);
        $contr  = clone $this->contr->getController();
        $contr->controlResult($this->data);
        return $contr->getHtml();
    }
}

class PollingAnswerGiverComponent extends ObjectGiverComponent
{
    public function users(){
        $criteria   = PollingCriteria::create();
        $criteria->setCriteriaHead('user_id');
        $criteria->setAnswer($this->data);
        
        $userIds    = $this->getComponent()->getUserData()->getAccessor()->arrayByCriteria( $criteria, Mysql::FETCH_COLUMN );

        $criteria   = ObjectCriteriaComponent::create();
        $criteria->setIds( $userIds ? $userIds : array(0) );
        
        return $this->getComponent()->getUserComponent()->getData()->getAccessor()->listByCriteria( $criteria );
    }
    
    /**
     * (non-PHPdoc)
     * @see model/object/ObjectGiverComponent#getComponent()
     * @return PollingComponent
     */
    public function getComponent(){
        return parent::getComponent();
    }
}


?>