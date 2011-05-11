<?php

require_once 'app/model/banner/Banner.class.php';
require_once 'app/model/banner/BannerCriteria.class.php';
require_once PATH_MODEL . '/object/ObjectAccessorComponent.class.php';


class BannerMap extends ObjectAccessorComponent
{
    public function getData(){
        return new Banner();
    }
    
    public function getCriteria(){
        return new BannerCriteria();
    }
    
    public function getRandomByPlace($place){
        $criteria  = $this->getCriteria();
        
        $criteria->setPlace($place);
        $criteria->setOrder('RAND()');
        $criteria->setPage(1);
        $criteria->setPageCount(1);
        
        return $this->getByCriteria($criteria);
    }
    
    /**
     * @return BannerMap
     */
    static public function instance(){
        return new self();
    }
}

?>