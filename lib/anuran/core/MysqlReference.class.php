<?php

require_once PATH_CORE . '/IReference.class.php';
require_once PATH_CORE . '/MysqlData.class.php';

class MysqlReference extends MysqlData implements IReference
{

    private $value;
    
    protected function make(){
//        $this->field('value', 'string');
    }
    
    public function addValue($value){
        $this->value = $value;
    }
    
    public function getValue(){
        return $this->value;
    }
    
    public function __toString(){
        if (!$this->value){
            // XXX: непораядок
            return '0';
        }
        return (string) $this->value;
    }


    public function createList(){

    }

}