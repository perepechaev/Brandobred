<?php

class TagCensure extends MysqlData implements ITag
{
    public function make(){
        $this->field('id',          'int',      array('auto'));
        $this->field('tag_id',      'int',      array('request', 'unique'));
        
        $this->field('status',      'enum',     array(
            'request', 
            'default' => Status::DIRTY, 
            'values'=> Status::getAvailable()
        ));
        
        $this->name('tag_censure');
    }
    
    public function expectOneRecord($count){
        if ($count === 0){
            return false;
        }
        parent::expectOneRecord($count);
    }
}

?>