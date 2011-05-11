<?php

class TagMerge extends MysqlData
{
    protected function make(){
        $this->field('master_id', 'int', array('request'));
        $this->field('slave_id',  'int', array('request'));
        
        $this->unique(array('master_id', 'slave_id'));
        
        $this->name('tag_merge');
    }
}

?>