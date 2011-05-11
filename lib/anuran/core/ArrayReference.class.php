<?php

require_once PATH_CORE .'/IReference.class.php';

class ArrayReference implements IReference
{
    private $value;
    
    public function addValue($value){
        equal(is_array($value));
        $this->value = $value;
        return $this;
    }

    public function getValue(){
        return $this->value;
    }
    
    /**
     * @return StringReference 
     */
    static public function create(){
        return new ArrayReference();
    }
}