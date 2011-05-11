<?php

require_once PATH_CORE . '/IReference.class.php';

class ReferenceFactory
{
    static public function create($var){
        $type    = gettype($var);
        if ($type === 'integer'){
            $var    = (string) $var;
            $type   = 'string';
        }
        
        if ($type === 'NULL'){
            $type = 'null';
        }
        
        $class   = strtoupper(substr($type, 0, 1)) . substr($type, 1) . 'Reference'; 
        require_once PATH_CORE . '/' . $class . '.class.php';
        $obj     = call_user_func(array($class, 'create'));
        $obj->addValue($var);
        return $obj;
    }
}


?>