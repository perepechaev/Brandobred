<?php

require_once PATH_MODEL . '/object/ObjectCriteriaComponent.class.php';

class BannerCriteria extends ObjectCriteriaComponent
{
    private $place;
    
    public function setPlace($place){
        $this->place = $place;
    }
    
    public function getPlace(){
        return $this->place;
    }
    
    protected function onbuild(){
        $this->where(
            $this->isEqual('place', $this->getPlace())
        );
    }
}

?>