<?php

require_once PATH_MODEL . '/object/ObjectAccessorComponent.class.php';

class BrandIndustry extends MysqlData
{
    protected function make(){
        $this->field('id',          'int',      array('auto'));
        $this->field('title',       'string',   array('request'));
        
        $this->name('brand_industry');
    }
}

class BrandIndustryMap extends ObjectAccessorComponent
{
    public function getData(){
        return new BrandIndustry();
    }
    
    /**
     * @return BrandIndustryMap
     */
    static public function instance(){
        return new self();
    } 
}

?>