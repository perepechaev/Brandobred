<?php
require_once PATH_CORE .'/IReference.class.php';

class NullReference implements IReference
{
    public function addValue($value){
        
    }
    
    public function getValue(){
        return NULL;
    }
    
    static public function create(){
        return new NullReference();
    }
    
}
?>