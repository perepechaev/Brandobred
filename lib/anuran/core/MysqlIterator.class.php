<?php

class MysqlIterator implements Iterator
{
    private $var        = array();

    public function __construct($array) {
        if (!is_array($array)){
            MysqlException::resultMustBeArray($array);
        }
        $this->var          = $array;
    }

    public function rewind() {
        reset($this->var);
    }

    public function current() {
        return current($this->var);
    }

    public function key() {
        return key($this->var);
    }

    public function next() {
        return next($this->var);
    }

    public function valid() {
        return $this->current() !== false;
    }

    public function getItem(){
        return $this->var;
    }

}

?>