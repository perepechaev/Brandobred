<?php

require_once PATH_CORE .'/IReference.class.php';

class StringReference implements IReference
{
    private $value;
    
    public function addValue($value){
        equal(is_string($value) || is_int($value));
        $this->value = $value;
        return $this;
    }

    public function __toString(){
        return $this->getValue();
    }
    
    public function getValue(){
        return $this->value;
    }
    
    /**
     * @return StringReference 
     */
    static public function create(){
        return new StringReference();
    }
}